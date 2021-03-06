<?php

namespace Sunnysideup\EcommerceProductVariation\Model\Buyables;

use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\CmsEditLinkField\Forms\Fields\CMSEditLinkField;
use Sunnysideup\Ecommerce\Api\ShoppingCart;
use Sunnysideup\Ecommerce\Config\EcommerceConfig;
use Sunnysideup\Ecommerce\Config\EcommerceConfigAjax;
use Sunnysideup\Ecommerce\Control\ShoppingCartController;
use Sunnysideup\Ecommerce\Filesystem\ProductImage;
use Sunnysideup\Ecommerce\Forms\Fields\EcomQuantityField;
use Sunnysideup\Ecommerce\Forms\Fields\ProductProductImageUploadField;
use Sunnysideup\Ecommerce\Interfaces\BuyableModel;
use Sunnysideup\Ecommerce\Interfaces\EditableEcommerceObject;
use Sunnysideup\Ecommerce\Model\Config\EcommerceDBConfig;
use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\OrderAttribute;
use Sunnysideup\Ecommerce\Model\OrderItem;
use Sunnysideup\Ecommerce\Pages\CheckoutPage;
use Sunnysideup\Ecommerce\Pages\Product;
use Sunnysideup\EcommerceProductVariation\Model\Process\ProductVariation_OrderItem;
use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;

class ProductVariation extends DataObject implements BuyableModel, EditableEcommerceObject
{
    protected $currentStageOfRequest = '';

    /**
     * when we save this object, should we save the parent
     * as well?
     *
     * @var bool
     */
    protected $saveParentProduct = false;

    /**
     * @var string
     */
    protected $defaultClassNameForOrderItem = ProductVariation_OrderItem::class;

    /**
     * Standard SS variable.
     */
    private static $api_access = [
        'view' => [
            'Title',
            'Description',
            'FullName',
            'AllowPurchase',
            'InternalItemID',
            'NumberSold',
            'Price',
            'Weight',
            'Model',
            'Quantifier',
            'Version',
        ],
    ];

    /**
     * Standard SS variable.
     */

    /**
     * ### @@@@ START REPLACEMENT @@@@ ###
     * OLD: private static $db (case sensitive)
     * NEW:
    private static $db (COMPLEX)
     * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
     * ### @@@@ STOP REPLACEMENT @@@@ ###
     */
    private static $table_name = 'ProductVariation';

    private static $db = [
        'InternalItemID' => 'Varchar(30)',
        'Price' => 'Currency',
        'Weight' => 'Float',
        'Model' => 'Varchar(30)',
        'Quantifier' => 'Varchar(30)',
        'AllowPurchase' => 'Boolean',
        'Sort' => 'Int',
        'NumberSold' => 'Int',
        'Description' => 'Varchar(255)',
        'FullName' => 'Varchar(255)',
        'FullSiteTreeSort' => 'Varchar(110)',
    ];

    /**
     * Standard SS variable.
     */
    private static $has_one = [
        'Product' => Product::class,
        'Image' => ProductImage::class,
    ];

    /**
     * Standard SS variable.
     */
    private static $many_many = [
        'AttributeValues' => ProductAttributeValue::class,
    ];

    /**
     * Standard SS variable.
     */
    private static $casting = [
        'Parent' => 'Product',
        'Title' => 'HTMLText',
        'Link' => 'Text',
        'AllowPurchaseNice' => 'Varchar',
        'CalculatedPrice' => 'Currency',
        'CalculatedPriceAsMoney' => 'Money',
    ];

    /**
     * Standard SS variable.
     */
    private static $defaults = [
        'AllowPurchase' => 1,
    ];

    /**
     * Standard SS variable.
     */
    private static $versioning = [
        'Stage',
    ];

    /**
     * Standard SS variable.
     */
    private static $extensions = [
        Versioned::class . '.versioned',
    ];

    /**
     * Standard SS variable.
     */
    private static $indexes = [
        'Sort' => true,
        'FullName' => true,
        'FullSiteTreeSort' => true,
    ];

    /**
     * Standard SS variable.
     */
    private static $field_labels = [
        'Description' => 'Title (optional)',
    ];

    /**
     * Standard SS variable.
     */
    private static $summary_fields = [

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD:  => 'Image' (case sensitive)
         * NEW:  => 'Image' (COMPLEX)
         * EXP: you may want to add ownership (owns)
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        'CMSThumbnail' => 'Image',
        'Title' => 'Title',
        'Price' => 'Price',
        'AllowPurchaseNice' => 'For Sale',
    ];

    /**
     * Standard SS variable.
     */
    private static $searchable_fields = [
        'FullName' => [
            'title' => 'Keyword',
            'field' => 'TextField',
            'filter' => 'PartialMatchFilter',
        ],
        'Price' => [
            'title' => 'Price',
            'field' => 'NumericField',
        ],
        'InternalItemID' => [
            'title' => 'Internal Item ID',
            'filter' => 'PartialMatchFilter',
        ],
        'AllowPurchase',
    ];

    /**
     * Standard SS variable.
     */
    private static $default_sort = '"AllowPurchase" DESC, "FullSiteTreeSort" ASC, "Sort" ASC, "InternalItemID" ASC, "Price" ASC';

    /**
     * Standard SS variable.
     */
    private static $singular_name = 'Product Variation';

    /**
     * Standard SS variable.
     */
    private static $plural_name = 'Product Variations';

    /**
     * How is the title build up?
     *
     * @var array
     **/
    private static $title_style_option = [
        'default' => [
            'ShowType' => true,
            'BetweenTypeAndValue' => ': ',
            'BetweenVariations' => ', ',
        ],
    ];

    private static $current_style_option_code = 'default';

    public function i18n_singular_name()
    {
        return _t('ProductVariation.PRODUCTVARIATION', 'Product Variation');
    }

    public function i18n_plural_name()
    {
        return _t('ProductVariation.PRODUCTVARIATIONS', 'Product Variations');
    }

    public static function get_plural_name()
    {
        $obj = Injector::inst()->get(ProductVariation::class);

        return $obj->i18n_plural_name();
    }

    /**
     * change the way the title of the variation is displayed
     * @param string $code                key
     * @param string $showType            do we show the type (e.g. colour, size)?
     * @param string $betweenTypeAndValue e.g. a semi-colon (:)
     * @param string $betweenVariations   e.g. a comma (,)
     */
    public static function add_title_style_option($code, $showType, $betweenTypeAndValue, $betweenVariations)
    {
        self::$title_style_option[$code] = [
            'ShowType' => $showType,
            'BetweenTypeAndValue' => $betweenTypeAndValue,
            'BetweenVariations' => $betweenVariations,
        ];
        Config::modify()->update(ProductVariation::class, 'current_style_option_code', $code);
    }

    /**
     * remove style option by key
     * @param  string $code               key
     */
    public static function remove_title_style_option($code)
    {
        unset(self::$title_style_option[$code]);
    }

    public static function get_current_style_option_array()
    {
        return self::$title_style_option[Config::inst()->get(ProductVariation::class, 'current_style_option_code')];
    }

    /**
     * Standard SS method.
     *
     * @return FieldSet
     */
    public function getCMSFields()
    {
        //backup in case there are no products.
        if (Product::get()->count() === 0) {
            return parent::getCMSFields();
        }
        $product = $this->Product();

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: LinkField (case sensitive)
         * NEW: LinkField (COMPLEX)
         * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        if (class_exists(CMSEditLinkField::class)) {

            /**
             * ### @@@@ START REPLACEMENT @@@@ ###
             * WHY: automated upgrade
             * OLD: LinkField (case sensitive)
             * NEW: LinkField (COMPLEX)
             * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
             * ### @@@@ STOP REPLACEMENT @@@@ ###
             */
            $productField = CMSEditLinkField::create(
                'ProductID',
                $this->Product()->i18n_singular_name(),
                $this->Product()
            );
        } else {
            $productCount = Product::get()->count();
            if ($productCount > 500) {
                user_error('We recommend you install https://github.com/briceburg/silverstripe-pickerfield');
                $productField = ReadonlyField::create(
                    'ProductIDTitle',
                    _t('ProductVariation.PRODUCT', Product::class),
                    $this->Product() ? $this->Product()->Title : _t('ProductVariation.NO_PRODUCT', 'none')
                );
            } else {
                $productField = new DropdownField('ProductID', _t('ProductVariation.PRODUCT', Product::class), Product::get()->map('ID', 'Title')->toArray());
                $productField->setEmptyString('(Select one)');
            }
        }
        $fields = new FieldList(
            new TabSet(
                'Root',
                new Tab(
                    'Main',
                    $productField,
                    /**
                     * ### @@@@ START REPLACEMENT @@@@ ###
                     * WHY: automated upgrade
                     * OLD: LinkField (case sensitive)
                     * NEW: LinkField (COMPLEX)
                     * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
                     * ### @@@@ STOP REPLACEMENT @@@@ ###
                     */
                    $fullNameLinkField = ReadonlyField::create('FullNameLink', _t('ProductVariation.FULLNAME', 'Full Name'), '<a href="' . $this->Link() . '">' . $this->FullName . '</a>'),
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
                    NumericField::create('Price', _t('ProductVariation.PRICE', 'Price')),
                    new CheckboxField('AllowPurchase', _t('ProductVariation.ALLOWPURCHASE', 'Allow Purchase ?'))
                ),
                new Tab(
                    'Details',
                    new TextField('InternalItemID', _t('ProductVariation.INTERNALITEMID', 'Internal Item ID')),
                    new TextField('Description', _t('ProductVariation.DESCRIPTION', 'Description (optional)'))
                ),
                new Tab(
                    Image::class,
                    /**
                     * ### @@@@ START REPLACEMENT @@@@ ###
                     * WHY: automated upgrade
                     * OLD: UploadField('Image (case sensitive)
                     * NEW: UploadField('Image (COMPLEX)
                     * EXP: make sure that Image does not end up as Image::class where this is not required
                     * ### @@@@ STOP REPLACEMENT @@@@ ###
                     */
                    new ProductProductImageUploadField(Image::class)
                )
            )
        );

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: LinkField (case sensitive)
         * NEW: LinkField (COMPLEX)
         * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: ->dontEscape (case sensitive)
         * NEW: ->dontEscape (COMPLEX)
         * EXP: dontEscape is not longer in use for form fields, please use HTMLReadonlyField (or similar) instead.
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        $fullNameLinkField->dontEscape = true;
        if ($this->EcomConfig()->ProductsHaveWeight) {

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
            $fields->addFieldToTab('Root.Details', NumericField::create('Weight', _t('ProductVariation.WEIGHT', 'Weight')));
        }
        if ($this->EcomConfig()->ProductsHaveModelNames) {
            $fields->addFieldToTab('Root.Details', new TextField('Model', _t('ProductVariation.MODEL', 'Model')));
        }
        if ($this->EcomConfig()->ProductsHaveQuantifiers) {
            $fields->addFieldToTab('Root.Details', new TextField('Quantifier', _t('ProductVariation.QUANTIFIER', 'Quantifier (e.g. per kilo, per month, per dozen, each)')));
        }
        $fields->addFieldToTab('Root.Details', new ReadonlyField('FullSiteTreeSort', _t('Product.FULLSITETREESORT', 'Full sort index')));
        if ($product) {
            $types = $product->VariationAttributes();
            if ($this->ID) {
                $values = $this->AttributeValues();
                foreach ($types as $type) {
                    $isReadonlyField = false;
                    $rightTitle = '';
                    $field = $type->getDropDownField();
                    if ($field) {
                        $value = $values->find('TypeID', $type->ID);
                        if ($value) {
                            $isReadonlyField = true;
                        } else {
                            if ($this->HasBeenSold()) {
                                $isReadonlyField = true;
                                $rightTitle = _t(
                                    'ProductVariation.ALREADYPURCHASED',
                                    'NOT SET (you can not select a value now because it has already been purchased).'
                                );
                            } else {
                                $field = $type->getDropDownField();
                                if ($field instanceof DropdownField) {
                                    $field->setEmptyString('');
                                }
                            }
                        }
                    } else {
                        $isReadonlyField = true;
                        $rightTitle = _t('ProductVariation.NOVALUESTOSELECT', 'No values to select');
                    }
                    if ($isReadonlyField) {

                        /**
                         * ### @@@@ START REPLACEMENT @@@@ ###
                         * WHY: automated upgrade
                         * OLD: LinkField (case sensitive)
                         * NEW: LinkField (COMPLEX)
                         * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
                         * ### @@@@ STOP REPLACEMENT @@@@ ###
                         */
                        if (class_exists(CMSEditLinkField::class)) {

                            /**
                             * ### @@@@ START REPLACEMENT @@@@ ###
                             * WHY: automated upgrade
                             * OLD: LinkField (case sensitive)
                             * NEW: LinkField (COMPLEX)
                             * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
                             * ### @@@@ STOP REPLACEMENT @@@@ ###
                             */
                            $field = CMSEditLinkField::create("Type{$type->ID}", $type->Name, $value)
                                ->setDescription($rightTitle);
                        } else {
                            $field->setValue($value->ID);
                            $field = $field->performReadonlyTransformation();
                            $field->setName("Type{$type->ID}");
                            $field->setDescription($rightTitle);
                        }
                    }
                    $fields->addFieldToTab('Root.Attributes', $field);
                }
            } else {
                foreach ($types as $type) {
                    $field = $type->getDropDownField();
                    $fields->addFieldToTab('Root.Attributes', $field);
                }
            }
        }
        $fields->addFieldToTab(
            'Root.Main',
            new LiteralField(
                'AddToCartLink',
                '<p class="message good"><a href="' . $this->AddLink() . '">' . _t('Product.ADD_TO_CART', 'add to cart') . '</a></p>'
            )
        );
        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * link to edit the record.
     *
     * @param string | Null $action - e.g. edit
     *
     * @return string
     */
    public function CMSEditLink($action = null)
    {
        return Controller::join_links(
            Director::baseURL(),
            '/admin/product-config/ProductVariation/EditForm/field/ProductVariation/item/' . $this->ID . '/',
            $action
        );
    }

    /**
     * Use the sort order of the variation attributes to order the attribute values.
     * This ensures that when VariationAttributes is used for a table header
     * and AttributeValues are used for the table rows then the columns will be
     * in the same order.
     *
     * @return DataObjectSet
     */
    public function AttributeValuesSorted()
    {
        $values = parent::AttributeValues();
        $types = $this->Product()->VariationAttributes();
        $result = ArrayList::create();
        foreach ($types as $type) {
            $result->push($values->find('TypeID', $type->ID));
        }

        return $result;
    }

    /**
     * standard SS method.
     */
    public function populateDefaults()
    {
        parent::populateDefaults();
        $this->AllowPurchase = 1;
    }

    /**
     * Puts together a title for the Product Variation.
     *
     * @return string
     */
    public function Title()
    {
        return $this->getTitle();
    }

    public function TitleWithHTML($noProductTitle = false)
    {
        return $this->getTitle(true, $noProductTitle);
    }

    public function getTitle($withHTML = false, $noProductTitle = false)
    {
        $array = [
            'Values' => $this->AttributeValues(),
            'Product' => $this->Product(),
            'Description' => $this->Description,
            'InternalItemID' => $this->InternalItemID,
            'Price' => $this->Price,
            'WithProductTitle' => $noProductTitle ? false : true,
        ];

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: ->RenderWith( (ignore case)
         * NEW: ->RenderWith( (COMPLEX)
         * EXP: Check that the template location is still valid!
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        $html = $this->customise($array)->RenderWith('ProductVariationItem');
        if ($withHTML) {
            return $html;
        }
        //@todo: reverse the ampersands, etc...
        return Convert::raw2att(trim(preg_replace('/\s+/', ' ', strip_tags($html))));
    }

    /**
     * shorthand.
     *
     * @return string
     */
    public function FullDescription()
    {
        return $this->Title(true, false);
    }

    /**
     * shorthand.
     *
     * @return string
     */
    public function ImgAltTag()
    {
        return $this->Title(false, false);
    }

    /**
     * returns YES or NO for the CMS Fields.
     *
     * @return string
     */
    public function AllowPurchaseNice()
    {
        return $this->obj('AllowPurchase')->Nice();
    }

    /**
     * By setting this to TRUE
     * the parent (product) will be save when this object will be saved.
     *
     * @param bool $b
     */
    public function setSaveParentProduct($b)
    {
        $this->saveParentProduct = $b;
    }

    /**
     * standard SS method
     * sets the FullName + FullSiteTreeSort of the variation.
     */
    public function onBeforeWrite()
    {
        //$this->currentStageOfRequest = Versioned::current_stage();
        //Versioned::set_reading_mode("");
        $this->prepareFullFields();
        parent::onBeforeWrite();
    }

    /**
     * sets the FullName and FullSiteTreeField to the latest values
     * This can be useful as you can compare it to the ones saved in the database.
     * Returns true if the value is different from the one in the database.
     *
     * @return bool
     */
    public function prepareFullFields()
    {
        $fullName = '';
        if ($this->InternalItemID) {
            $fullName .= $this->InternalItemID . ': ';
        }
        $fullName .= $this->getTitle(false, true);
        if ($product = $this->MainParentGroup()) {
            $product->prepareFullFields();
            $fullName .= ' (' . $product->FullName . ')';
            $this->FullSiteTreeSort = $product->FullSiteTreeSort . ',' . $this->Sort;
        }
        $this->FullName = strip_tags($fullName);
        if ($this->EcomConfig()->ProductsHaveWeight) {
            if (! $this->Weight) {
                if ($product && $product->Weight) {
                    $this->Weight = $product->Weight;
                }
            }
        }
        if (($this->dbObject('FullName') !== $this->FullName) || ($this->dbObject('FullSiteTreeSort') !== $this->FullSiteTreeSort)) {
            return true;
        }

        return false;
    }

    /**
     * Standard SS Method.
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        //clean up data???
        //todo: what is this for?
        if (isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes']) && $this->canEdit()) {
            $productAttributesArray = [];
            foreach ($_POST['ProductAttributes'] as $key => $value) {
                $productAttributesArray[$key] = intval($value);
            }
            $this->AttributeValues()->setByIDList(array_values($productAttributesArray));
        }
        unset($_POST['ProductAttributes']);
        if ($this->saveParentProduct) {
            if ($product = $this->Product()) {
                $product->write();
                $product->publish('Stage', 'Live');
            }
        }
    }

    /**
     * Standard SS Method
     * Remove links to Attribute Values.
     */
    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        $this->AttributeValues()->removeAll();
    }

    /**
     * this is used by TableListField to access attribute values.
     *
     * @return DataObject
     */
    public function AttributeProxy()
    {
        $do = new DataObject();
        if ($this->AttributeValues()->exists()) {
            foreach ($this->AttributeValues() as $value) {
                $do->{'Val' . $value->Type()->ID} = $value->Value;
            }
        }

        return $do;
    }

    //GROUPS AND SIBLINGS

    /**
     * We use this function to make it more universal.
     * For a buyable, a parent could refer to a ProductGroup OR a Product.
     *
     * @return DataObject | Null
     **/
    public function Parent()
    {
        return $this->getParent();
    }

    public function getParent()
    {
        return $this->Product();
    }

    /**
     * Returns the direct parent (group) for the product.
     **/
    public function MainParentGroup()
    {
        return $this->Product();
    }

    /**
     * Returns Buybales in the same group.
     **/
    public function Siblings()
    {
        return self::get()->Filter(['ProductID' => $this->ProductID]);
    }

    //IMAGES

    /**
     * returns a "BestAvailable" image if the current one is not available
     * In some cases this is appropriate and in some cases this is not.
     * For example, consider the following setup
     * - product A with three variations
     * - Product A has an image, but the variations have no images
     * With this scenario, you want to show ONLY the product image
     * on the product page, but if one of the variations is added to the
     * cart, then you want to show the product image.
     * This can be achieved bu using the BestAvailable image.
     *
     * @return Image | Null
     */
    public function BestAvailableImage()
    {
        $image = $this->Image();
        if ($image && $image->exists()) {
            return $image;
        }
        if ($product = $this->Product()) {
            return $product->BestAvailableImage();
        }
    }

    /**
     * Little hack to show thumbnail in summary fields in modeladmin in CMS.
     *
     * @return string (HTML = formatted image)
     */
    public function CMSThumbnail()
    {
        if ($image = $this->Image()) {
            if ($image->exists()) {
                return $image->Thumbnail();
            }
        }

        return '[' . _t('product.NOIMAGE', 'no image') . ']';
    }

    /**
     * Returns a link to a default image.
     * If a default image is set in the site config then this link is returned
     * Otherwise, a standard link is returned.
     *
     * @return string
     */
    public function DefaultImageLink()
    {
        $this->EcomConfig()->DefaultImageLink();
    }

    /**
     * returns the default image of the product.
     *
     * @return Image | Null
     */
    public function DefaultImage()
    {
        return $this->Product()->DefaultImage();
    }

    /**
     * returns a product image for use in templates
     * e.g. $DummyImage.Width();.
     *
     * @return Product_Image
     */
    public function DummyImage()
    {
        return new ProductImage();
    }

    // VERSIONING

    /**
     * Action to return specific version of a product.
     * This is really useful for sold products where you want to retrieve the actual version that you sold.
     *
     * @TODO: this is not correct yet, as the versions of product and productvariation are muddled up!
     *
     * @param HTTPRequest $request
     */
    public function viewversion($request)
    {
        $version = intval($request->param('ID'));
        $product = $this->Product();
        if ($product) {
            $this->redirect($product->Link('viewversion/' . $product->ID . '/' . $version . '/'));
        } else {
            $page = DataObject::get_one(
                ErrorPage::class,
                ['ErrorCode' => '404']
            );
            if ($page) {
                $this->redirect($page->Link());

                return;
            }
        }

        return [];
    }

    /**
     * Action to return specific version of a product variation.
     * This can be any product to enable the retrieval of deleted products.
     * This is really useful for sold products where you want to retrieve the actual version that you sold.
     *
     * @param int $id
     * @param int $version
     *
     * @return DataObject | Null
     */
    public function getVersionOfBuyable($id = 0, $version = 0)
    {
        if (! $id) {
            $id = $this->ID;
        }
        if (! $version) {
            $version = $this->Version;
        }

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return OrderItem::get_version($this->ClassName, $id, $version);
    }

    //ORDER ITEM

    /**
     * returns the order item associated with the buyable.
     * ALWAYS returns one, even if there is none in the cart.
     * Does not write to database.
     *
     * @return OrderItem (no kidding)
     **/
    public function OrderItem()
    {
        //work out the filter
        $filter = [];
        $updatedFilters = $this->extend('updateItemFilter', $filter);
        if ($updatedFilters !== null && is_array($updatedFilters) && count($updatedFilters)) {
            foreach ($updatedFilters as $updatedFilter) {
                if (is_array($updatedFilter)) {
                    $filter = array_merge($filter, $updatedFilter);
                } else {
                    $filter[] = $updatedFilter;
                }
            }
        }
        //make the item and extend
        $item = ShoppingCart::singleton()->findOrMakeItem($this, $filter);
        $this->extend('updateDummyItem', $item);

        return $item;
    }

    /**
     * you can overwrite this function in your buyable items (such as Product).
     *
     * @return string
     **/
    public function classNameForOrderItem()
    {
        $className = $this->defaultClassNameForOrderItem;
        $updatedClassName = $this->extend('updateClassNameForOrderItem', $className);
        if ($updatedClassName !== null && is_array($updatedClassName) && count($updatedClassName)) {
            $className = $updatedClassName[0];
        }

        return $className;
    }

    /**
     * You can set an alternative class name for order item using this method.
     *
     * @param string $className
     **/
    public function setAlternativeClassNameForOrderItem($className)
    {
        $this->defaultClassNameForOrderItem = $className;
    }

    /**
     * When purchasing this buyable, how many decimals can it have?
     *
     * @return int
     */
    public function QuantityDecimals()
    {
        return 0;
    }

    /**
     * Number of variations sold.
     *
     * @TODO: check if we need to use other class names
     *
     * @return int
     */
    public function HasBeenSold()
    {
        return $this->getHasBeenSold();
    }

    public function getHasBeenSold()
    {
        $dataList = Order::get_datalist_of_orders_with_submit_record($onlySubmittedOrders = true, $includeCancelledOrders = false);
        $dataList = $dataList->innerJoin(OrderAttribute::class, '"OrderAttribute"."OrderID" = "Order"."ID"');
        $dataList = $dataList->innerJoin(OrderItem::class, '"OrderAttribute"."ID" = "OrderItem"."ID"');
        $dataList = $dataList->filter(
            [
                'BuyableID' => $this->ID,

                /**
                 * ### @@@@ START REPLACEMENT @@@@ ###
                 * WHY: automated upgrade
                 * OLD: $this->ClassName (case sensitive)
                 * NEW: $this->ClassName (COMPLEX)
                 * EXP: Check if the class name can still be used as such
                 * ### @@@@ STOP REPLACEMENT @@@@ ###
                 */
                'buyableClassName' => $this->ClassName,
            ]
        );

        return $dataList->count();
    }

    //LINKS

    /**
     * Takes you to the Product and filters
     * for the provided variation.
     *
     * @param string $action - OPTIONAL
     *
     * @return string
     */
    public function Link($action = null)
    {
        if (! $action) {
            $action = 'filterforvariations/' . $this->ID . '/';
        }

        return $this->Product()->Link($action);
    }

    /**
     * @todo TEST!!!!
     * @return string
     */
    public function VersionedLink()
    {
        return Controller::join_links(
            Director::baseURL(),
            EcommerceConfig::get(ShoppingCartController::class, 'url_segment'),
            'submittedbuyable',
             /**
              * ### @@@@ START REPLACEMENT @@@@ ###
              * WHY: automated upgrade
              * OLD: $this->ClassName (case sensitive)
              * NEW: $this->ClassName (COMPLEX)
              * EXP: Check if the class name can still be used as such
              * ### @@@@ STOP REPLACEMENT @@@@ ###
              */
             $this->ClassName,
            $this->ID,
            $this->Version
        );
    }

    /**
     * passing on shopping cart links ...is this necessary?? ...why not just pass the cart?
     *
     * @return string
     */
    public function AddLink()
    {

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::add_item_link($this->ID, $this->ClassName, $this->linkParameters());
    }

    /**
     * link use to add (one) to cart.
     *
     *@return string
     */
    public function IncrementLink()
    {
        //we can do this, because by default add link adds one

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::add_item_link($this->ID, $this->ClassName, $this->linkParameters());
    }

    /**
     * Link used to remove one from cart
     * we can do this, because by default remove link removes one.
     *
     * @return string
     */
    public function DecrementLink()
    {

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::remove_item_link($this->ID, $this->ClassName, $this->linkParameters());
    }

    /**
     * remove one buyable's orderitem from cart.
     *
     * @return string (Link)
     */
    public function RemoveLink()
    {

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::remove_item_link($this->ID, $this->ClassName, $this->linkParameters());
    }

    /**
     * remove all of this buyable's orderitem from cart.
     *
     * @return string (Link)
     */
    public function RemoveAllLink()
    {

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::remove_all_item_link($this->ID, $this->ClassName, $this->linkParameters());
    }

    /**
     * remove all of this buyable's orderitem from cart and go through to this buyble to add alternative selection.
     *
     * @return string (Link)
     */
    public function RemoveAllAndEditLink()
    {

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::remove_all_item_and_edit_link($this->ID, $this->ClassName, $this->linkParameters());
    }

    /**
     * set new specific new quantity for buyable's orderitem.
     *
     * @param float $quantity
     *
     * @return string (Link)
     */
    public function SetSpecificQuantityItemLink($quantity)
    {

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::set_quantity_item_link($this->ID, $this->ClassName, array_merge($this->linkParameters(), ['quantity' => $quantity]));
    }

    /**
     * @return string
     */
    public function AddToCartAndGoToCheckoutLink()
    {
        $array = $this->linkParameters();
        $array['BackURL'] = urlencode(CheckoutPage::find_link());

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return ShoppingCartController::add_item_link($this->ID, $this->ClassName, $array);
    }

    //TEMPLATE STUFF

    /**
     * @return bool
     */
    public function IsInCart()
    {
        return $this->OrderItem() && $this->OrderItem()->Quantity > 0 ? true : false;
    }

    /**
     * @return EcomQuantityField
     */
    public function EcomQuantityField()
    {
        return new EcomQuantityField($this);
    }

    /**
     * returns the instance of EcommerceConfigAjax for use in templates.
     * In templates, it is used like this:
     * $EcommerceConfigAjax.TableID.
     *
     * @return EcommerceConfigAjax
     **/
    public function AJAXDefinitions()
    {
        return EcommerceConfigAjax::get_one($this);
    }

    /**
     * @return EcommerceDBConfig
     **/
    public function EcomConfig()
    {
        return EcommerceDBConfig::current_ecommerce_db_config();
    }

    /**
     * Is it a variation?
     *
     * @return bool
     */
    public function IsProductVariation()
    {
        return true;
    }

    /**
     * returns the actual price worked out after discounts, currency conversions, etc...
     *
     * @casted
     *
     * @return float
     */
    public function CalculatedPrice()
    {
        return $this->getCalculatedPrice();
    }

    public function getCalculatedPrice()
    {
        $price = $this->Price;
        $updatedPrice = $this->extend('updateCalculatedPrice', $price);
        if ($updatedPrice !== null && is_array($updatedPrice) && count($updatedPrice)) {
            $price = $updatedPrice[0];
        }

        return $price;
    }

    /**
     * How do we display the price?
     *
     * @return Money
     */
    public function CalculatedPriceAsMoney()
    {
        return $this->getCalculatedPriceAsMoney();
    }

    public function getCalculatedPriceAsMoney()
    {
        return EcommerceCurrency::get_money_object_from_order_currency($this->CalculatedPrice());
    }

    //CRUD SETTINGS

    /**
     * Is the product for sale?
     *
     * @return bool
     */
    public function canPurchase(Member $member = null, $checkPrice = true)
    {
        $config = $this->EcomConfig();
        if ($config->ShopClosed) {
            return false;
        }
        $allowpurchase = $this->AllowPurchase;
        if (! $allowpurchase) {
            return false;
        }
        if ($product = $this->Product()) {
            $allowpurchase = $product->canPurchase($member);
            if (! $allowpurchase) {
                return false;
            }
        }
        $price = $this->getCalculatedPrice();
        if ($price === 0 && ! $config->AllowFreeProductPurchase) {
            return false;
        }
        $extended = $this->extendedCan('canPurchase', $member);
        if ($extended !== null) {
            $allowpurchase = $extended;
        }

        return $allowpurchase;
    }

    /**
     * standard SS Method
     * we explicitely set this to give access in the API.
     *
     * @return bool
     */
    public function canView($member = null, $context = [])
    {
        if ($this->ProductID && $this->Product()->exists()) {
            return $this->Product()->canEdit($member);
        }

        return $this->canEdit($member);
    }

    /**
     * Shop Admins can edit.
     *
     * @return bool
     */
    public function canEdit($member = null, $context = [])
    {
        if (! $member) {
            $member = Member::currentUser();
        }
        if ($member && $member->IsShopAdmin()) {
            return true;
        }

        return parent::canEdit($member);
    }

    /**
     * Standard SS method.
     *
     * @return bool
     */
    public function canDelete($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return $this->canEdit($member);
    }

    /**
     * Standard SS method
     * //check if it is in a current cart?
     *
     * @return bool
     */
    public function canDeleteFromLive($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return $this->canEdit($member);
    }

    /**
     * Standard SS method.
     *
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return $this->canEdit($member);
    }

    /**
     * finds similar ("siblings") variations where one
     * attribute value is NOT the same.
     *
     * @return DataList
     */
    public function MostLikeMe()
    {
        $idArray = [];
        foreach ($this->AttributeValues() as $excludeValue) {
            unset($getAnyArray);
            $getAnyArray = [];
            foreach ($this->AttributeValues() as $innerValue) {
                if ($excludeValue->ID !== $innerValue->ID) {
                    $getAnyArray[$innerValue->ID] = $innerValue->ID;
                }
                //find a product variation that has the getAnyArray Values
                $items = ProductVariation::get()
                    ->innerJoin(
                        'ProductVariation_AttributeValues',
                        '"ProductVariation"."ID" = "ProductVariationID"'
                    )
                    ->filter(
                        [
                            'ProductAttributeValueID' => $getAnyArray,
                            'ProductID' => $this->ProductID,
                        ]
                    )
                    ->exclude(['ID' => $this->ID]);
                if ($items->count()) {
                    $idArray = array_merge($idArray, $items->column('ID'));
                }
            }
        }

        return ProductVariation::get()->filter(['ID' => $idArray]);
    }

    /**
     * Here you can add additional information to your product
     * links such as the AddLink and the RemoveLink.
     * One useful parameter you can add is the BackURL link.
     *
     * Usage would be by means of
     * 1. decorating product
     * 2. adding a updateLinkParameters method
     * 3. adding items to the array.
     *
     * You can also extend Product and override this method...
     *
     * @return array
     **/
    protected function linkParameters()
    {
        $array = [];
        $extendedArray = $this->extend('updateLinkParameters', $array, $type);
        if ($extendedArray !== null && is_array($extendedArray) && count($extendedArray)) {
            foreach ($extendedArray as $extendedArrayUpdate) {
                $array = array_merge($array, $extendedArrayUpdate);
            }
        }

        return $array;
    }
}
