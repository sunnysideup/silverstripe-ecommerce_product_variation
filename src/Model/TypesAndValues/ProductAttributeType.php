<?php

namespace Sunnysideup\EcommerceProductVariation\Model\TypesAndValues;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use Sunnysideup\Ecommerce\Forms\Fields\OptionalTreeDropdownField;
use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldConfigForOrderItems;
use Sunnysideup\Ecommerce\Interfaces\EditableEcommerceObject;
use Sunnysideup\Ecommerce\Pages\Product;

/**
 * This class contains list items such as "size", "colour"
 * Not XL, Red, etc..., but the lists that contain the
 * ProductAttributeValues.
 * For a clothing store you will have two entries:
 * - Size
 * - Colour
 */

class ProductAttributeType extends DataObject implements EditableEcommerceObject
{
    /**
     * Standard SS variable.
     */
    private static $api_access = [
        'view' => [
            'Name',
            'Label',
            'Values',
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
    private static $table_name = 'ProductAttributeType';

    private static $db = [
        'Name' => 'Varchar', //for back-end use
        'Label' => 'Varchar', //for front-end use
        'Sort' => 'Int', //for front-end use
        'MergeIntoNote' => 'Varchar(255)',
        //'Unit' => 'Varchar' //TODO: for future use
    ];

    /**
     * Standard SS variable.
     */
    private static $has_one = [
        'MoreInfoLink' => SiteTree::class,
        'MergeInto' => ProductAttributeType::class,
    ];

    /**
     * Standard SS variable.
     */
    private static $has_many = [
        'Values' => ProductAttributeValue::class,
    ];

    /**
     * Standard SS variable.
     */
    private static $summary_fields = [
        'FullName' => 'Type',
    ];

    /**
     * Standard SS variable.
     */
    private static $searchable_fields = [
        'Name' => 'PartialMatchFilter',
        'Label' => 'PartialMatchFilter',
    ];

    /**
     * Standard SS variable.
     */
    private static $belongs_many_many = [
        'Products' => Product::class,
    ];

    /**
     * Standard SS variable.
     */
    private static $casting = [
        'FullName' => 'Varchar',
    ];

    /**
     * Standard SS variable.
     */
    private static $indexes = [
        'Sort' => true,
    ];

    /**
     * Standard SS variable.
     */
    private static $default_sort = '"Sort" ASC, "Name"';

    private static $dropdown_field_for_orderform = DropdownField::class;

    /**
     * Standard SS variable.
     */
    private static $singular_name = 'Variation Attribute Type';

    /**
     * Standard SS variable.
     */
    private static $plural_name = 'Variation Attribute Types';

    private static $_drop_down_values = [];

    public function i18n_singular_name()
    {
        return _t('ProductAttributeType.ATTRIBUTETYPE', 'Variation Attribute Type');
    }

    public function i18n_plural_name()
    {
        return _t('ProductAttributeType.ATTRIBUTETYPES', 'Variation Attribute Types');
    }

    public static function get_plural_name()
    {
        $obj = Singleton(ProductAttributeType::class);
        return $obj->i18n_plural_name();
    }

    /**
     * finds or makes a ProductAttributeType, based on the lower case Name.
     *
     * @param string $name
     * @param boolean $create
     *
     * @return ProductAttributeType
     */
    public static function find_or_make($name, $create = true)
    {
        $name = strtolower($name);
        $type = DataObject::get_one(
            ProductAttributeType::class,
            'LOWER("Name") = \'' . $name . '\'',
            $cacheDataObjectGetOne = false
        );
        if ($type) {
            return $type;
        }
        $type = ProductAttributeType::create();
        $type->Name = $name;
        $type->Label = $name;
        if ($create) {
            $type->write();
        }
        return $type;
    }

    /**
     * Standard SS Methodd.
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $nameField = $fields->dataFieldByName('Name');
        $nameField->SetRightTitle(_t('ProductAttributeType.NAME_RIGHT_TITLE', 'Mainly used for easy recognition in the CMS'));
        $valueField = $fields->dataFieldByName('Label');
        $valueField->SetRightTitle(_t('ProductAttributeType.VALUE_RIGHT_TITLE', 'Mainly used for site users'));
        $variationField = $fields->dataFieldByName('Values');
        if ($variationField) {
            $variationField->setConfig(new GridFieldConfigForOrderItems());
        }
        $fields->addFieldToTab(
            'Root.Main',
            new OptionalTreeDropdownField(
                'MoreInfoLinkID',
                _t('ProductAttributeType.MORE_INFO_LINK', 'More info page'),
                SiteTree::class
            )
        );
        //TODO: make this a really fast editing interface. Table list field??
        //$fields->removeFieldFromTab('Root.Values','Values');
        $fields->AddFieldToTab(
            'Root.Advanced',
            DropdownField::create(
                'MergeIntoID',
                _t('ProductAttributeType.MERGE_INTO', 'Merge into ...'),
                [0 => _t('ProductAttributeType.DO_NOT_MERGE', '-- do not merge --')] +
                    ProductAttributeType::get()->exclude(['ID' => $this->ID])->map()->toArray()
            )
        );
        $fields->AddFieldToTab('Root.Advanced', new ReadonlyField('MergeIntoNote', 'Merge Results Notes'));
        return $fields;
    }

    /**
     * link to edit the record
     * @param string | Null $action - e.g. edit
     * @return string
     */
    public function CMSEditLink($action = null)
    {
        return Controller::join_links(
            Director::baseURL(),
            /**
             * ### @@@@ START REPLACEMENT @@@@ ###
             * WHY: automated upgrade
             * OLD: $this->ClassName (case sensitive)
             * NEW: $this->ClassName (COMPLEX)
             * EXP: Check if the class name can still be used as such
             * ### @@@@ STOP REPLACEMENT @@@@ ###
             */
            '/admin/product-config/' . $this->ClassName . '/EditForm/field/' . $this->ClassName . '/item/' . $this->ID . '/',
            $action
        );
    }

    /**
     * add more values to a type
     * array should be an something like red, blue, orange (strings NOT objects)
     * @param array $values
     */
    public function addValues(array $values)
    {
        $avalues = $this->convertArrayToValues($values);
        $this->Values()->addMany($values);
    }

    /**
     * takes an array of values
     * and finds them or creates them.
     *
     * @param array $values
     * @return ArrayList
     */
    public function convertArrayToValues(array $values)
    {
        $set = new ArrayList();
        foreach ($values as $value) {
            $val = $this->Values()->find('Value', $value);
            if (! $val) {  //TODO: ignore case, if possible
                $val = new ProductAttributeValue();
                $val->Value = $value;
                $val->write();
            }
            $set->push($val);
        }
        return $set;
    }

    /**
     * @param string $emptystring
     * @param DataList $values
     *
     * @return DropdownField | HiddenField
     */
    public function getDropDownField($emptystring = null, $values = null)
    {
        //to do, why do switch to "all" the options if there are no values?
        $values = $this->getValuesForDropdown($values);
        if ($values && is_array($values) && count($values)) {
            $fieldType = $this->Config()->get('dropdown_field_for_orderform');
            $field = $fieldType::create('ProductAttributes[' . $this->ID . ']', $this->Label, $values);
            if ($emptystring && count($values) > 1) {
                $field->setEmptyString($emptystring);
            }
        } else {
            $field = new HiddenField('ProductAttributes[' . $this->ID . ']', 0);
        }
        $this->extend('updateDropDownField', $field);
        return $field;
    }

    /**
     * @param DataList $values
     *
     * @return array
     */
    public function getValuesForDropdown($values = null)
    {
        if (! isset(self::$_drop_down_values[$this->ID])) {
            $values = $values ?: $this->Values();
            $count = $values->count();
            if ($count > 0) {
                if ($count > 100) {
                    $values = $values->limit(1000);
                    self::$_drop_down_values[$this->ID] = $values->map('ID', 'Value')->toArray();
                } else {
                    self::$_drop_down_values[$this->ID] = $values->map('ID', 'ValueForDropdown')->toArray();
                }
            } else {
                self::$_drop_down_values[$this->ID] = [];
            }
        }

        return self::$_drop_down_values[$this->ID];
    }

    /**
     * It can be deleted if all its Values can be deleted only...
     *
     * @return boolean
     */
    public function canDelete($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        $values = $this->Values()->limit(20)->sort('RAND()');
        foreach ($values as $value) {
            if (! $value->canDelete()) {
                return false;
            }
        }
        return parent::canDelete($member);
    }

    /**
     * standard SS method
     * Adds a name if there is no name.
     * Adds a label is there is no label.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $i = 0;

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $this->ClassName (case sensitive)
         * NEW: $this->ClassName (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $className (case sensitive)
         * NEW: $className (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        $className = $this->ClassName;

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: $className (case sensitive)
         * NEW: $className (COMPLEX)
         * EXP: Check if the class name can still be used as such
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        while (! $this->Name || DataObject::get_one($className, 'Name = \'' . $this->Name . '\' AND ID != \'' . $this->ID . '\'', $cacheDataObjectGetOne = false)) {
            $this->Name = $this->i18n_singular_name();
            if ($i) {
                $this->Name .= '_' . $i;
            }
            $i++;
        }
        if (! $this->Label) {
            $this->Label = $this->Name;
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->MergeIntoID) {
            $newAttributeType = $this->MergeInto();
            $canDoMerge = true;
            if ($this->Values()->count() !== $newAttributeType->Values()->count()) {
                $canDoMerge = false;
                $this->MergeIntoNote = 'NON-MATCHING VALUE COUNTS';
            } else {
                $mergeMapArray_OLD = [];
                $mergeMapArray_NEW = [];
                $mergeMapArrayGO = [];
                foreach ($this->Values() as $value) {
                    $mergeMapArray_OLD[] = $value->ID;
                }
                foreach ($newAttributeType->Values() as $value) {
                    $mergeMapArray_NEW[] = $value->ID;
                }
                foreach ($mergeMapArray_OLD as $key => $id_OLD) {
                    $id_NEW = $mergeMapArray_NEW[$key];
                    $obj_OLD = ProductAttributeValue::get()->byID($id_OLD);
                    $obj_NEW = ProductAttributeValue::get()->byID($id_NEW);
                    if ($obj_OLD && $obj_NEW) {
                        if ($obj_OLD->Code === $obj_NEW->Code || $obj_OLD->Value === $obj_NEW->Value || 1 === 1) {
                            $mergeMapArrayGO[$obj_OLD->ID] = $obj_NEW->ID;
                        } else {
                            $this->MergeIntoNote = 'NON-MATCHINGE VALUES: ' . $obj_OLD->Code . '!=' . $obj_NEW->Code . ' AND ' . $obj_OLD->Value . '!=' . $obj_NEW->Value;
                            $canDoMerge = false;
                        }
                    } else {
                        $this->MergeIntoNote = 'MISSING OLD OR NEW OBJECT';
                        $canDoMerge = false;
                    }
                }
            }
            if ($canDoMerge) {
                foreach ($mergeMapArrayGO as $id_OLD => $id_NEW) {
                    DB::query('
                        UPDATE "ProductVariation_AttributeValues"
                        SET "ProductAttributeValueID" = ' . $id_NEW . '
                        WHERE "ProductAttributeValueID" = ' . $id_OLD . ';
                    ');
                }
                DB::query('
                    UPDATE "Product_VariationAttributes"
                    SET "ProductAttributeTypeID" = ' . $this->MergeIntoID . '
                    WHERE "ProductAttributeTypeID" = ' . $this->ID . ';
                ');
                $values = ProductAttributeValue::get()->filter(['TypeID' => $this->ID]);
                foreach ($values as $value) {
                    $value->delete();
                }
                $this->MergeIntoNote = 'Merged successfully into ' . $this->MergeInto()->Name . ' ...';
                $this->Name = 'TO BE DELETED ' . $this->Name;
                $this->Label = 'TO BE DELETED ' . $this->Label;
            }
            $this->MergeIntoID = 0;
            $this->write();
        }
    }

    /**
     * Delete all the values
     * that are related to this type.
     */
    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        $values = $this->Values();
        foreach ($values as $value) {
            if ($value->canDelete()) {
                $value->delete();
                $value->destroy();
            }
        }
        DB::query('DELETE FROM "Product_VariationAttributes" WHERE "ProductAttributeTypeID" = ' . $this->ID);
    }

    public function cleanup()
    {
        $sql = '
            Select "ProductAttributeTypeID"
            FROM "Product_VariationAttributes"
            WHERE "ProductID" = ' . $this->owner->ID;
        $data = DB::query($sql);
        $array = $data->keyedColumn();
        if (is_array($array) && count($array)) {
            foreach ($array as $key => $productAttributeTypeID) {
                //attribute type does not exist.
                if (! ProductAttributeType::get()->byID($productAttributeTypeID)) {
                    //delete non-existing combinations of Product_VariationAttributes (where the attribute does not exist)
                    //DB::query("DELETE FROM \"Product_VariationAttributes\" WHERE \"ProductAttributeTypeID\" = $productAttributeTypeID");
                    //non-existing product attribute values.
                    $productAttributeValues = ProductAttributeValue::get()->filter(['TypeID' => $productAttributeTypeID]);
                    if ($productAttributeValues->count()) {
                        foreach ($productAttributeValues as $productAttributeValue) {
                            $productAttributeValue->delete();
                        }
                    }
                }
            }
        }
    }

    /**
     * useful for GridField
     * @return string
     */
    public function getFullName()
    {
        $fieldLabels = $this->FieldLabels();
        return $this->Name . ', ' .
            $this->Label .
            ' (' .
                $this->Values()->count() . ' ' . Injector::inst()->get(ProductAttributeValue::class)->i18n_plural_name() . ', ' .
                $this->Products()->count() . ' ' . Injector::inst()->get(Product::class)->i18n_plural_name() .
            ')';
    }
}
