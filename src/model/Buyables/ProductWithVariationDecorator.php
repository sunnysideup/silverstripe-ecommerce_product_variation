<?php

/**
 * adds variation functionality to the product.
 */
class ProductWithVariationDecorator extends DataExtension
{
    /**
     * standard SS Var.
     */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $has_many = (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $has_many = (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'ProductWithVariationDecorator';

    private static $has_many = array(
        'Variations' => 'ProductVariation',
    );

    /**
     * standard SS Var.
     */
    private static $many_many = array(
        'VariationAttributes' => 'ProductAttributeType',
    );

    /**
     * standard SS Var.
     */
    private static $many_many_extraFields = array(
        'VariationAttributes' => array(
            'Notes' => 'Varchar(200)',
        ),
    );

    /**
     * standard SS Var.
     */
    private static $casting = array(
        'LowestVariationPrice' => 'Currency',
        'LowestVariationPriceAsMoney' => 'Money',
    );

    /**
     * what class do we use for Variations.
     * This class has to extend ProductVariation.
     *
     * @var string
     */
    protected $classNameOfVariations = 'ProductVariation';

    /**
     * returns what class do we use for Variations.
     * In general, that is ProductVariation, but you can change it to something else!
     *
     * @return string
     */
    public function getClassNameOfVariations()
    {
        if (method_exists($this->owner, 'classNameOfVariationsSetInProduct')) {
            return $this->owner->classNameOfVariationsSetInProduct();
        } elseif (!empty($this->owner->classNameOfVariations)) {
            return $this->owner->classNameOfVariations;
        } else {
            return $this->classNameOfVariations;
        }
    }

    /**
     * standard SS method.
     *
     * @param Member $member
     *
     * @return bool
     */
    public function canDelete($member = null)
    {
        if ($this->owner->Variations()->count()) {
            return false;
        }
    }

    /**
     * tells you the number of variations this product has.
     *
     * @param bool $mustBeForSale - only count variations that are for sale
     *
     * @return int
     */
    public function NumberOfVariations($mustBeForSale = false)
    {
        if ($mustBeForSale) {
            $count = 0;
            $variations = $this->owner->Variations();
            foreach ($variations as $variation) {
                if ($variation->canPurchase()) {
                    ++$count;
                }
            }

            return $count;
        } else {
            return $this->owner->Variations()->count();
        }
    }

    /**
     * tells you whether the product has any variations.
     *
     * @param bool $mustBeForSale - only count variations that are for sale
     *
     * @return bool
     */
    public function HasVariations($mustBeForSale = false)
    {
        return $this->owner->NumberOfVariations($mustBeForSale) ? true : false;
    }

    /**
     * this method is really useful when you mix Products and Product Variations
     * That is, in a template, you might have something like $Buyable.Product
     * With the method below, this will work BOTH if the Buyable is a Product
     * and a product Varation.
     *
     * @return DataObject (Product)
     **/
    public function Product()
    {
        return $this->owner;
    }

    /**
     * tells you whether the current object is a product
     * seems a bit silly, but it can be useful as other buyables
     * can return false from this method.
     *
     * @return bool
     */
    public function IsProduct()
    {
        return true;
    }

    /**
     * standard SS method.
     */
    public function updateCMSFields(FieldList $fields)
    {
        $tabName = singleton('ProductVariation')->plural_name();
        $priceField = $fields->dataFieldByName("Price");
        $fields->addFieldToTab(
            'Root',
            $tab = new Tab(
                $tabName,
                new GridField(
                    'VariationAttributes',
                    singleton('ProductAttributeType')->plural_name(),
                    $this->owner->VariationAttributes(),
                    $variationAttributesConfig = GridFieldConfig_RecordEditor::create()
                ),
                $this->owner->getVariationsTable(),
                new CreateEcommerceVariationsField('VariationMaker', '', $this->owner->ID)
            )
        );
        $variationAttributesConfig->removeComponentsByType('GridFieldAddNewButton');
        $variations = $this->owner->Variations();
        if ($variations && $variations->Count()) {
            $productVariationName = singleton('ProductVariation')->plural_name();
            $fields->addFieldToTab(
                'Root.Details',
                new LabelField(
                    'variationspriceinstructions',
                    sprintf(
                        _t('ProductVariation.PRICE_EXPLANATION', 'Price - Because you have one or more variations, you can vary the price in the <strong>%s</strong> tab. You set the default price here.'),
                        $productVariationName
                    )
                )
            );
            $link = EcommerceProductVariationTaskDeleteVariations::create_link($this->owner);
            if ($link) {
                $tab->insertAfter(
                    new LiteralField(
                        'DeleteVariations',
                        "<p class=\"bad message\"><a href=\"$link\"  class=\"action ss-ui-button\" id=\"DeleteEcommerceVariationsInner\" data-confirm=\"".
                                Convert::raw2att(
                                    _t(
                                        'Product.ARE_YOU_SURE_YOU_WANT_TO_DELETE_ALL_VARIATIONS',
                                    'are you sure you want to delete all variations from this product? '
                                    )
                                ).
                            '">'
                            ._t('Product.DELETE_ALL_VARIATIONS_FROM', 'Delete all variations from <i>').$this->owner->Title.'</i>'.
                        '</a></p>'
                    ),
                    'ProductVariations'
                );
            }
            if (class_exists('DataObjectOneFieldUpdateController')) {
                $linkForAllowSale = DataObjectOneFieldUpdateController::popup_link(
                    'ProductVariation',
                    'AllowPurchase',
                    "ProductID = {$this->owner->ID}",
                    '',
                    _t('ProductVariation.QUICK_UPDATE_VARIATION_ALLOW_PURCHASE', 'for sale')
                );
                $linkForPrice = DataObjectOneFieldUpdateController::popup_link(
                    'ProductVariation',
                    'Price',
                    "ProductID = {$this->owner->ID}",
                    '',
                    _t('ProductVariation.QUICK_UPDATE_VARIATION_PRICES', 'prices')
                );
                $linkForProductCodes = DataObjectOneFieldUpdateController::popup_link(
                    'ProductVariation',
                    'InternalItemID',
                    "ProductID = {$this->owner->ID}",
                    '',
                    _t('ProductVariation.QUICK_UPDATE_VARIATION_PRODUCT_CODES', 'product codes')
                );
                $tab->insertAfter(
                    new LiteralField(
                        'QuickActions',
                        '<p class="message good">'
                            ._t('ProductVariation.QUICK_UPDATE', 'Quick update')
                            .': '
                            ."<span class=\"action ss-ui-button\">$linkForAllowSale</span> "
                            ."<span class=\"action ss-ui-button\">$linkForPrice</span>"
                            ."<span class=\"action ss-ui-button\">$linkForProductCodes</span>"
                            .'</p>'
                    ),
                    'ProductVariations'
                );
            }
        }
    }

    /**
     * Field to add and edit product variations.
     *
     * @return GridField
     */
    public function getVariationsTable()
    {
        if (class_exists('GridFieldEditableColumns')) {
            $oldSummaryFields = Config::inst()->get('ProductVariation', 'summary_fields');
            $oldSummaryFields['AllowPurchase'] = $oldSummaryFields['AllowPurchaseNice'];
            unset($oldSummaryFields['AllowPurchaseNice']);
            Config::inst()->Update('ProductVariation', 'summary_fields', $oldSummaryFields);
            $gridFieldConfig = GridFieldConfig::create();
            $gridFieldConfig->addComponent(new GridFieldToolbarHeader());
            $gridFieldConfig->addComponent($sort = new GridFieldSortableHeader());
            $gridFieldConfig->addComponent($filter = new GridFieldFilterHeader());
            $gridFieldConfig->addComponent(new GridFieldEditButton());
            $gridFieldConfig->addComponent($pagination = new GridFieldPaginator(100));
            $gridFieldConfig->addComponent(new GridFieldDetailForm());
            //add the editable columns.
            $gridFieldConfig->addComponent(new GridFieldEditableColumns());
        } else {
            $gridFieldConfig = GridFieldConfig_RecordEditor::create();
            $gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
        }
        $source = $this->owner->Variations();
        $types = $this->owner->VariationAttributes();
        if ($types && $types->count()) {
            $title = _t('ProductVariation.PLURALNAME', 'Product Variations').
                    ' '._t('ProductVariation.by', 'by').': '.
                    implode(' '._t('ProductVariation.TIMES', '/').' ', $types->map('ID', 'Title')->toArray());
        } else {
            $title = _t('ProductVariation.PLURALNAME', 'Product Variations');
        }
        $gridField = new GridField('ProductVariations', $title, $source, $gridFieldConfig);

        return $gridField;
    }

    /**
     * tells us if any of the variations, related to this product,
     * are currently in the cart.
     *
     * @return bool
     */
    public function VariationIsInCart()
    {
        $variations = $this->owner->Variations();
        if ($variations) {
            foreach ($variations as $variation) {
                if ($variation->OrderItem() && $variation->OrderItem()->Quantity > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * tells us if any of the variations, related to this product,
     * OR the product itself, is currently in the cart.
     *
     * @return bool
     */
    public function VariationOrProductIsInCart()
    {
        return $this->owner->IsInCart() || $this->VariationIsInCart();
    }

    /**
     * returns lowest cost variation price
     * for use in FROM XXX.
     *
     * @return float
     */
    public function LowestVariationPrice()
    {
        $currentPrice = 99999999;
        $variations = $this->owner->Variations();
        if ($variations && $variations->count()) {
            foreach ($variations as $variation) {
                if ($variation->canPurchase()) {
                    $variationPrice = $variation->getCalculatedPrice();
                    if ($variationPrice < $currentPrice) {
                        $currentPrice = $variationPrice;
                    }
                }
            }
        }
        if ($currentPrice < 99999999) {
            return $currentPrice;
        }
    }

    /**
     * @see self::LowestVariationPrice
     *
     * @return Money
     */
    public function LowestVariationPriceAsMoney()
    {
        return EcommerceCurrency::get_money_object_from_order_currency($this->LowestVariationPrice());
    }

    /**
     * returns a list of variations for sale as JSON.
     * the output is as follows:
     *   VariationID: [
     *     AttributeValueID: AttributeValueID,
     *     AttributeValueID: AttributeValueID
     *   ].
     *
     * @param bool $showCanNotPurchaseAsWell - show all variations, evens the ones that can not be purchased.
     *
     * @return string (JSON)
     */
    public function VariationsForSaleJSON($showCanNotPurchaseAsWell = false)
    {
        //todo: change JS so that we dont have to add this default array element (-1 => -1)
        $varArray = array(-1 => -1);
        if ($variations = $this->owner->Variations()) {
            foreach ($variations as $variation) {
                if ($showCanNotPurchaseAsWell || $variation->canPurchase()) {
                    $varArray[$variation->ID] = $variation->AttributeValues()->map('ID', 'ID')->toArray();
                }
            }
        }
        $json = json_encode($varArray);

        return $json;
    }

    /**
     * The array provided needs to be
     *     TypeID => arrayOfValueIDs
     *     TypeID => arrayOfValueIDs
     *     TypeID => arrayOfValueIDs
     * you can also make it:
     *     NameOfAttritbuteType => arrayOfValueIDs
     * OR:
     *     NameOfAttritbuteType => arrayOfValueNames
     * e.g.
     *     Colour => array(Red, Orange, Blue )
     *     Size => array(S, M, L )
     *     Foo => array(
     *         1 => 1,
     *         3 => 3
     *     ).
     *
     * TypeID is the ID of the ProductAttributeType.  You can also make
     * it a string in which case it will be found / created
     * arrayOfValueIDs is an array of IDs of the already created ProductAttributeValue
     * (key and value need to be the same)
     * You can also make it an array of strings in which case they will be found / created...
     *
     * @param array $values
     *
     * @return int
     */
    public function generateVariationsFromAttributeValues(array $values)
    {
        set_time_limit(600);
        $count = 0;
        $valueCombos = array();
        foreach ($values as $typeID => $typeValues) {
            $typeObject = $this->owner->addAttributeType($typeID);
            //we use the copy variations to merge all of them together...
            $copyVariations = $valueCombos;
            $valueCombos = array();
            if ($typeObject) {
                foreach ($typeValues as $valueKey => $valueValue) {
                    $findByID = false;
                    if (strlen($valueKey) == strlen($valueValue) && intval($valueKey) == intval($valueValue)) {
                        $findByID = true;
                    }
                    $obj = ProductAttributeValue::find_or_make(
                        $typeObject,
                        $valueValue,
                        $create = true,
                        $findByID
                    );
                    $valueID = $obj->write();
                    if ($valueID = intval($valueID)) {
                        $valueID = array($valueID);
                        if (count($copyVariations) > 0) {
                            foreach ($copyVariations as $copyVariation) {
                                $valueCombos[] = array_merge($copyVariation, $valueID);
                            }
                        } else {
                            $valueCombos[] = $valueID;
                        }
                    }
                }
            }
        }
        foreach ($valueCombos as $valueArray) {
            sort($valueArray);
            $str = implode(',', $valueArray);
            $add = true;
            $productVariationIDs = DB::query("SELECT \"ID\" FROM \"ProductVariation\" WHERE \"ProductID\" = '{$this->owner->ID}'")->column();
            if (count($productVariationIDs) > 0) {
                $productVariationIDs = implode(',', $productVariationIDs);
                $variationValues = DB::query("SELECT GROUP_CONCAT(\"ProductAttributeValueID\" ORDER BY \"ProductAttributeValueID\" SEPARATOR ',') FROM \"ProductVariation_AttributeValues\" WHERE \"ProductVariationID\" IN ($productVariationIDs) GROUP BY \"ProductVariationID\"")->column();
                if (in_array($str, $variationValues)) {
                    $add = false;
                }
            }
            if ($add) {
                ++$count;
                $className = $this->owner->getClassNameOfVariations();
                $newVariation = $className::create(
                    array(
                        'ProductID' => $this->owner->ID,
                        'Price' => $this->owner->Price,
                    )
                );
                $newVariation->setSaveParentProduct(false);
                $newVariation->write();
                $newVariation->AttributeValues()->addMany($valueArray);
            }
        }

        return $count;
    }

    /**
     * returns the matching variation if any.
     *
     * @param array        $attributes formatted as (TypeID => ValueID, TypeID => ValueID)
     * @param bool         $searchAllProducts - show results from any variation matching the combination
     *                                          this will return a DataList
     *
     * @return ProductVariation | null | Datalist
     */
    public function getVariationByAttributes(array $attributes, $searchAllProducts = false)
    {
        if (!is_array($attributes) || !count($attributes)) {
            user_error('attributes must be provided as an array of numeric keys and values IDs...', E_USER_NOTICE);

            return;
        }
        if ($searchAllProducts) {
            $variations = ProductVariation::get();
        } else {
            $variations = ProductVariation::get()
                ->filter(
                    array('ProductID' => $this->owner->ID)
                );
        }
        foreach ($attributes as $typeid => $valueid) {
            if (!is_numeric($typeid) || !is_numeric($valueid)) {
                user_error('key and value ID must be numeric', E_USER_NOTICE);

                return;
            }
            $alias = "Alias$typeid";
            $variations = $variations->where(
                "\"$alias\".\"ProductAttributeValueID\" = $valueid"
            )
            ->innerJoin(
                'ProductVariation_AttributeValues',
                "\"ProductVariation\".\"ID\" = \"$alias\".\"ProductVariationID\"",
                 $alias
            );
        }
        if ($searchAllProducts) {
            return $variations;
        }
        if ($variation = $variations->First()) {
            return $variation;
        }
    }

    public function addAttributeValue($attributeValue)
    {
        die('not completed');
        $existingVariations = $this->owner->Variations();
        $existingVariations->add($attributeTypeObject);
    }

    public function removeAttributeValue($attributeValue)
    {
        die('not completed');
        $existingVariations = $this->owner->Variations();
        $existingVariations->remove($attributeTypeObject);
    }

    /**
     * add an attribute type to the product.
     *
     * @param string | Int | ProductAttributeType $attributeTypeObject
     *
     * @return ProductAttributeType
     */
    public function addAttributeType($attributeTypeObject)
    {
        if (is_numeric($attributeTypeObject) && intval($attributeTypeObject) === $attributeTypeObject) {
            $attributeTypeObject = ProductAttributeType::get()->byID(intval($attributeTypeObject));
        }
        if (is_string($attributeTypeObject)) {
            $attributeTypeObject = ProductAttributeType::find_or_make($attributeTypeObject);
        }
        if ($attributeTypeObject && $attributeTypeObject instanceof ProductAttributeType) {
            $existingTypes = $this->owner->VariationAttributes();
            $existingTypes->add($attributeTypeObject);

            return $attributeTypeObject;
        } else {
            user_error($attributeTypeObject.' is broken');
        }
    }

    /**
     * @param ProductAttributeType $attributeTypeObject
     *
     * @return bool
     */
    public function canRemoveAttributeType($attributeTypeObject)
    {
        $variations = $this->owner->getComponents(
            'Variations',
            "\"TypeID\" = '$attributeTypeObject->ID'"
        );
        $variations = $variations->innerJoin('ProductVariation_AttributeValues', '"ProductVariationID" = "ProductVariation"."ID"');
        $variations = $variations->innerJoin('ProductAttributeValue', '"ProductAttributeValue"."ID" = "ProductAttributeValueID"');

        return $variations->Count() == 0;
    }

    /**
     * @param ProductAttributeType $attributeTypeObject
     */
    public function removeAttributeType($attributeTypeObject)
    {
        $existingTypes = $this->owner->VariationAttributes();
        $existingTypes->remove($attributeTypeObject);
    }

    /**
     * return an array of IDs of the Attribute Types linked to this product.
     *
     * @return array
     */
    public function getArrayOfLinkedProductAttributeTypeIDs()
    {
        return $this->owner->VariationAttributes()->map('ID', 'ID')->toArray();
        //old way...
        $sql = '
            Select "ProductAttributeTypeID"
            FROM "Product_VariationAttributes"
            WHERE "ProductID" = '.$this->owner->ID;
        $data = DB::query($sql);
        $array = $data->keyedColumn();

        return $array;
    }

    /**
     * return an array of IDs of the Attribute Types linked to this product.
     *
     * @return array
     */
    public function getArrayOfLinkedProductAttributeValueIDs()
    {
        $sql = '
            Select "ProductAttributeValueID"
            FROM "ProductVariation"
                INNER JOIN "ProductVariation_AttributeValues"
                    ON "ProductVariation_AttributeValues"."ProductVariationID" = "ProductVariation"."ID"
            WHERE "ProductVariation"."ProductID" = '.$this->owner->ID;
        $data = DB::query($sql);
        $array = $data->keyedColumn();

        return $array;
    }

    /**
     * set price to lowest variation if no price.
     */
    public function onBeforeWrite()
    {
        return;
        if ($this->owner->HasVariations()) {
            $price = $this->owner->getCalculatedPrice();
            if ($price == 0) {
                $this->owner->Price = $this->owner->LowestVariationPrice();
            }
        }
    }

    public function onAfterWrite()
    {
        //check for the attributes used so that they can be added to VariationAttributes
        parent::onAfterWrite();
        $this->cleaningUpVariationData();
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        if (Versioned::get_by_stage('Product', 'Stage', 'Product.ID ='.$this->owner->ID)->count() == 0) {
            $variations = $this->owner->Variations();
            foreach ($variations as $variation) {
                if ($variation->canDelete()) {
                    $variation->delete();
                }
            }
        }
    }

    public function onAfterDelete()
    {
        parent::onAfterDelete();
        $this->cleaningUpVariationData();
    }

    /**
     * based on the ProductVariations for the products
     * removing non-existing Product_VariationAttributes
     * adding existing Product_VariationAttributes.
     *
     * @param bool $verbose - output outcome
     */
    public function cleaningUpVariationData($verbose = false)
    {
        $changes = false;
        $productID = $this->owner->ID;
        $sql = '
            SELECT "ProductAttributeValue"."TypeID"
            FROM "ProductVariation"
                INNER JOIN "ProductVariation_AttributeValues"
                    ON "ProductVariation_AttributeValues"."ProductVariationID" = "ProductVariation"."ID"
                INNER JOIN "ProductAttributeValue"
                    ON "ProductVariation_AttributeValues"."ProductAttributeValueID" = "ProductAttributeValue"."ID"
            WHERE "ProductVariation"."ProductID" = '.$productID;
        $arrayOfTypesToKeepForProduct = array();
        $data = DB::query($sql);
        $array = $data->keyedColumn();
        if (is_array($array) && count($array)) {
            foreach ($array as $key => $productAttributeTypeID) {
                $arrayOfTypesToKeepForProduct[$productAttributeTypeID] = $productAttributeTypeID;
            }
        }
        if (count($arrayOfTypesToKeepForProduct)) {
            $deleteCounter = DB::query('
                SELECT COUNT(ID)
                FROM "Product_VariationAttributes"
                WHERE
                    "ProductAttributeTypeID" NOT IN ('.implode(',', $arrayOfTypesToKeepForProduct).")
                    AND \"ProductID\" = '$productID'
            ");
            if ($deleteCounter->value()) {
                $changes = true;
                if ($verbose) {
                    DB::alteration_message('DELETING Attribute Type From '.$this->owner->Title, 'deleted');
                }
                DB::query('
                    DELETE FROM "Product_VariationAttributes"
                    WHERE
                        "ProductAttributeTypeID" NOT IN ('.implode(',', $arrayOfTypesToKeepForProduct).")
                        AND \"ProductID\" = '$productID'
                ");
            }
            foreach ($arrayOfTypesToKeepForProduct as $productAttributeTypeID) {
                $addCounter = DB::query("
                    SELECT COUNT(ID)
                    FROM \"Product_VariationAttributes\"
                    WHERE
                        \"ProductAttributeTypeID\" = '$productAttributeTypeID'
                        AND \"ProductID\" = $productID
                ");
                if (!$addCounter->value()) {
                    $changes = true;
                    if ($verbose) {
                        DB::alteration_message('ADDING Attribute Type From '.$this->owner->Title, 'created');
                    }
                    DB::query("
                        INSERT INTO \"Product_VariationAttributes\" (
                            \"ProductID\" ,
                            \"ProductAttributeTypeID\"
                        )
                        VALUES (
                            '$productID', '$productAttributeTypeID'
                        )
                    ");
                }
            }
        } else {
            $deleteAllCounter = DB::query("
                SELECT COUNT(ID)
                FROM \"Product_VariationAttributes\"
                WHERE \"ProductID\" = '$productID'
            ");
            if ($deleteAllCounter->value()) {
                $changes = true;
                if ($verbose) {
                    DB::alteration_message('DELETING ALL Attribute Types From '.$this->owner->Title, 'deleted');
                }
                DB::query("
                    DELETE FROM \"Product_VariationAttributes\"
                    WHERE \"ProductID\" = '$productID'
                ");
            }
        }

        return $changes;
    }
}

