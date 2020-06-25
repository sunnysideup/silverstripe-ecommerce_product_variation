<?php



/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD:  extends Extension (ignore case)
  * NEW:  extends Extension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
class ProductWithVariationDecoratorController extends Extension
{
    /**
     * standard SS Var.
     */
    private static $allowed_actions = array(
        'selectvariation',
        'VariationForm',
        'filterforvariations',
    );

    /**
     * tells us if Javascript should be used in validating
     * the product variation form.
     *
     * @var bool
     */
    private static $use_js_validation = true;

    /**
     * array of IDs of variations that should be shown
     * if count(array) == 0 then all of them will be shown.
     *
     * @var array
     */
    protected $variationFilter = [];

    /**
     * return the variations and apply filter if one has been set.
     *
     * @return DataList
     */
    public function Variations()
    {
        $variations = $this->owner->dataRecord->Variations();
        if ($this->variationFilter && count($this->variationFilter)) {
            $variations = $variations->filter(array('ID' => $this->variationFilter));
        }

        return $variations;
    }

    /**
     * returns a form of the product if it can be purchased.
     *
     * @return Form | NULL
     */
    public function VariationForm()
    {
        if ($this->owner->canPurchase(null, true)) {
            if ($this->owner->HasVariations()) {
                $farray = [];
                $requiredfields = [];
                $attributes = $this->owner->VariationAttributes();
                if ($attributes) {
                    foreach ($attributes as $attribute) {
                        $options = $this->possibleValuesForAttributeType($attribute);
                        if ($options && $options->count()) {
                            $farray[] = $attribute->getDropDownField(_t('ProductWithVariationDecorator.CHOOSE', 'choose')." $attribute->Label "._t('ProductWithVariationDecorator.DOTDOTDOT', '...'), $options);//new DropdownField("Attribute_".$attribute->ID,$attribute->Name,);
                            $requiredfields[] = "ProductAttributes[$attribute->ID]";
                        }
                    }
                }
                $fields = FieldList::create($farray);
            } else {
                $fields = FieldList::create();
            }

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: new NumericField (case sensitive)
  * NEW: NumericField::create (COMPLEX)
  * EXP: check the number of decimals required and add as ->setScale(2)
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: NumericField::create (case sensitive)
  * NEW: NumericField::create (COMPLEX)
  * EXP: check the number of decimals required and add as ->setScale(2)
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            $fields->push(NumericField::create('Quantity', 'Quantity', 1)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)

            $actions = FieldList::create(
                new FormAction(
                    'addVariation',
                    _t('ProductWithVariationDecorator.ADDLINK', 'Add to cart')
                )
            );
            $requiredfields[] = 'Quantity';
            $requiredFieldsClass = 'RequiredFields';
            $validator = $requiredFieldsClass::create($requiredfields);
            $form = Form::create(
                $this->owner,
                'VariationForm',
                $fields,
                $actions,
                $validator
            );
            Requirements::themedCSS('variationsform', 'ecommerce_product_variation');
            //variation options json generation
            if (
                Config::inst()->get('ProductWithVariationDecoratorController', 'use_js_validation')
                && $this->owner->HasVariations()
            ) {
                Requirements::javascript('ecommerce_product_variation/javascript/SelectEcommerceProductVariations.js');
                $jsObjectName = $form->FormName().'Object';
                Requirements::customScript(
                    '
                    var SelectEcommerceProductVariationsOptions = {};
                    SelectEcommerceProductVariationsOptions[\''.$form->FormName().'\'] = '.$this->owner->VariationsForSaleJSON().';'
                );
            }

            return $form;
        }
    }

    public function addVariation($data, $form)
    {
        if ($this->owner->HasVariations() && isset($data['ProductAttributes'])) {
            $data['ProductAttributes'] = Convert::raw2sql($data['ProductAttributes']);
            $variation = $this->owner->getVariationByAttributes($data['ProductAttributes']);
            if ($variation) {
                if ($variation->canPurchase()) {
                    $quantity = round($data['Quantity'], $variation->QuantityDecimals());
                    if (!$quantity) {
                        $quantity = 1;
                    }
                    ShoppingCart::singleton()->addBuyable($variation, $quantity);
                    if ($variation->IsInCart()) {
                        $msg = _t('ProductWithVariationDecorator.SUCCESSFULLYADDED', 'Added to cart.');
                        $status = 'good';
                    } else {
                        $msg = _t('ProductWithVariationDecorator.NOTSUCCESSFULLYADDED', 'Not added to cart.');
                        $status = 'bad';
                    }
                } else {
                    $msg = _t('ProductWithVariationDecorator.VARIATIONNOTAVAILABLE', 'That option is not available.');
                    $status = 'bad';
                }
            } else {
                $msg = _t('ProductWithVariationDecorator.VARIATIONNOTAVAILABLE', 'That option is not available.');
                $status = 'bad';
            }
        } elseif (! $this->owner->HasVariations()) {
            $quantity = round($data['Quantity'], $this->owner->QuantityDecimals());
            if (!$quantity) {
                $quantity = 1;
            }
            ShoppingCart::singleton()->addBuyable($this->owner->dataRecord, $quantity);
            if ($this->owner->IsInCart()) {
                $msg = _t('ProductWithVariationDecorator.SUCCESSFULLYADDED', 'Added to cart.');
                $status = 'good';
            } else {
                $msg = _t('ProductWithVariationDecorator.NOTSUCCESSFULLYADDED', 'Not added to cart.');
                $status = 'bad';
            }
        } else {
            $msg = _t('ProductWithVariationDecorator.VARIATIONNOTFOUND', 'The item(s) you are looking for are not available.');
            $status = 'bad';
        }
        if (Director::is_ajax()) {
            return ShoppingCart::singleton()->setMessageAndReturn($msg, $status);
        } else {
            ShoppingCart::singleton()->setMessageAndReturn($msg, $status, $form);
            $this->owner->redirectBack();
        }
    }

    /**
     * returns a list of VariationAttributes (e.g. colour, size)
     * and the possible Atrribute Values for each type (e.g. RED, ORANGE, XL).
     *
     * @return ArrayList
     */
    public function AttributeValuesPerAttributeType()
    {
        $types = $this->owner->VariationAttributes();
        $arrayListOuter = new ArrayList();
        if ($types->count()) {
            foreach ($types as $type) {
                $values = $this->possibleValuesForAttributeType($type);
                $arrayListInner = new ArrayList();
                foreach ($values as $value) {
                    $arrayListInner->push($value);
                }
                $type->AttributeValues = $arrayListInner;
                $arrayListOuter->push($type);
            }
        }

        return $arrayListOuter;
    }

    /**
     * @param int | ProductAttributeType           $type
     *
     * @return DataList of ProductAttributeValues
     */
    public function possibleValuesForAttributeType($type)
    {
        if ($type instanceof ProductAttributeType) {
            $typeID = $type->ID;
        } elseif ($type = ProductAttributeType::get()->byID(intval($type))) {
            $typeID = $type->ID;
        } else {
            return;
        }
        $vals = ProductAttributeValue::get()
            ->where(
                "\"TypeID\" = $typeID AND \"ProductVariation\".\"ProductID\" = ".$this->owner->ID.'  AND "ProductVariation"."AllowPurchase" = 1'
            )
            ->sort(
                array(
                    'ProductAttributeValue.Sort' => 'ASC',
                )
            )
            ->innerJoin(
                'ProductVariation_AttributeValues',
                '"ProductAttributeValue"."ID" = "ProductVariation_AttributeValues"."ProductAttributeValueID"'
            )
            ->innerJoin(
                'ProductVariation',
                '"ProductVariation_AttributeValues"."ProductVariationID" = "ProductVariation"."ID"'
            );
        if ($this->variationFilter) {
            $vals = $vals->filter(array('ProductVariation.ID' => $this->variationFilter));
        }

        return $vals;
    }

    /**
     * action!
     * this action is for selecting product variations.
     *
     * @param HTTPRequest $request
     */
    public function selectvariation($request)
    {
        if (Director::is_ajax() || 1 == 1) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: ->RenderWith( (ignore case)
  * NEW: ->RenderWith( (COMPLEX)
  * EXP: Check that the template location is still valid!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            return $this->owner->RenderWith('SelectVariationFromProductGroup');
        } else {
            $this->owner->redirect($this->owner->Link());
        }

        return array();
    }

    /**
     * You can specificy one or MORE.
     *
     * @param HTTPRequest $request
     */
    public function filterforvariations($request)
    {
        $array = explode(',', $request->param('ID'));
        if (is_array($array) && count($array)) {
            $this->variationFilter = array_map('intval', $array);
        }

        return array();
    }

    /**
     * @return bool
     */
    public function HasFilterForVariations()
    {
        return $this->variationFilter && count($this->variationFilter) ? true : false;
    }
}

