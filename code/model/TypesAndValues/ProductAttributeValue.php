<?php

class ProductAttributeValue extends DataObject implements EditableEcommerceObject
{

    /**
     * Standard SS variable.
     */
    private static $api_access = array(
        'view' => array(
            "Value",
            "Type"
        )
    );

    private static $db = array(
        'Code' => 'Varchar(255)',
        'Value' => 'Varchar(255)',
        'Sort' => 'Int',
        'MergeIntoNote' => 'Varchar(255)'
    );

    private static $has_one = array(
        'Type' => 'ProductAttributeType',
        'MergeInto' => 'ProductAttributeValue'
    );

    private static $belongs_many_many = array(
        'ProductVariation' => 'ProductVariation'
    );

    private static $summary_fields = array(
        'Type.FullName' => 'Type',
        'Value' => 'Value'
    );

    private static $searchable_fields = array(
        'Value' => 'PartialMatchFilter'
    );

    private static $casting = array(
        'Title' => 'HTMLText',
        'FullTitle' => 'Varchar',
        'ValueForDropdown' => "HTMLText",
        'ValueForTable' => "HTMLText"
    );

    private static $indexes = array(
        'Sort' => true,
        'Code' => true
    );

    /**
     * finds or makes a ProductAttributeType, based on the lower case Name.
     *
     * @param productAttributeType | int $type
     * @param string $value
     * @param boolean $create
     * @param boolean $findByID
     *
     * @return ProductAttributeType
     */
    public static function find_or_make($type, $value, $create = true, $findByID = false)
    {
        if ($type instanceof ProductAttributeType) {
            $type = $type->ID;
        }
        $cleanedValue = strtolower($value);
        if ($findByID) {
            $intValue = intval($value);
            $valueObj = ProductAttributeValue::get()
                ->filter(array("ID" => $intValue, "TypeID" => intval($type)))
                ->first();
                //debug::log("INT VALUE:" .$intValue."-".$type);
        } else {
            $valueObj = DataObject::get_one(
                'ProductAttributeValue',
                "(LOWER(\"Code\") = '$cleanedValue' OR LOWER(\"Value\") = '$cleanedValue') AND TypeID = ".intval($type),
                $cacheDataObjectGetOne = false
            );
        }
        if ($valueObj) {

            return $valueObj;
        }
        $valueObj = ProductAttributeValue::create();
        $valueObj->Code = $cleanedValue;
        $valueObj->Value = $value;
        $valueObj->TypeID = $type;
        if ($create) {
            $valueObj->write();
        }
        return $valueObj;
    }

    private static $default_sort = "\"TypeID\" ASC, \"Sort\" ASC";

    private static $singular_name = "Variation Attribute Value";
    public function i18n_singular_name()
    {
        return _t("ProductAttributeValue.ATTRIBUTEVALUE", "Variation Attribute Value");
    }

    private static $plural_name = "Variation Attribute Values";
    public function i18n_plural_name()
    {
        return _t("ProductAttributeValue.ATTRIBUTEVALUES", "Variation Attribute Values");
    }

    public function canDelete($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        if (DB::query("
            SELECT COUNT(*)
            FROM \"ProductVariation_AttributeValues\"
                INNER JOIN \"ProductVariation\"
                    ON  \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
            WHERE \"ProductAttributeValueID\" = ".$this->ID
        )->value() == 0) {
            return parent::canDelete($member);
        }
        return false;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $variationField = $fields->dataFieldByName('ProductVariation');
        if($variationField) {
            $variationField->setConfig(new GridFieldConfigForOrderItems());
        }
        $fields->AddFieldToTab(
            "Root.Advanced",
            DropdownField::create(
                'MergeIntoID',
                _t('ProductAttributeType.MERGE_INTO', 'Merge into ...'),
                array(0 => _t('ProductAttributeType.DO_NOT_MERGE', '-- do not merge --')) +
                    ProductAttributeValue::get()
                        ->filter(array('TypeID' => $this->TypeID))
                        ->exclude(array("ID" => $this->ID))
                        ->map('ID', 'FullTitle')->toArray()
            )
        );
        $fields->AddFieldToTab("Root.Advanced", new ReadOnlyField("MergeIntoNote", "Merge Results Notes"));
        return $fields;
    }

    /**
     * link to edit the record
     * @param String | Null $action - e.g. edit
     * @return String
     */
    public function CMSEditLink($action = null)
    {
        return Controller::join_links(
            Director::baseURL(),
            "/admin/product-config/".$this->ClassName."/EditForm/field/".$this->ClassName."/item/".$this->ID."/",
            $action
        );
    }

    /**
     * casted variable
     * returns the value for the option in the select dropdown box.
     * @return String (HTML)
     **/
    public function ValueForDropdown()
    {
        return $this->getValueForDropdown();
    }
    public function getValueForDropdown()
    {
        $value = $this->Value;
        $extensionValue = $this->extend("updateValueForDropdown");
        if ($extensionValue !== null && is_array($extensionValue) && count($extensionValue)) {
            $value = implode("", $extensionValue);
        }
        return $value;
    }

    /**
     * casted variable
     * returns the value for the variations table
     * @return String (HTML)
     **/
    public function ValueForTable()
    {
        return $this->getValueForTable();
    }
    public function getValueForTable()
    {
        $value = $this->Value;
        $extensionValue = $this->extend("updateValueForTable");
        if ($extensionValue !== null && is_array($extensionValue) && count($extensionValue)) {
            $value = implode("", $extensionValue);
        }
        return $value;
    }

    /**
     * casted variable
     * returns the value for the variations table
     * @return String
     **/
    public function Title()
    {
        return $this->getTitle();
    }
    public function getTitle()
    {
        return $this->getValueForTable();
    }

    /**
     * casted variable
     * returns the value for the variations table
     * @return String
     **/
    public function FullTitle()
    {
        return $this->getFullTitle();
    }
    public function getFullTitle()
    {
        if($type = $this->Type()) {
            $typeName = $type->Name;
        } else {
            $typeName = _t('ProductAttributeValue.NO_TYPE_NAME', 'NO TYPE');
        }
        return $typeName.': '.$this->Value.' ('.$this->Code.')';
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        //delete ProductVariation_AttributeValues were the Attribute Value does not exist.
        DB::query("DELETE FROM \"ProductVariation_AttributeValues\" WHERE \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\" = ".$this->ID);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Value) {
            $this->Value = $this->i18n_singular_name();
            $i = 0;
            $className = $this->ClassName;
            while( DataObject::get_one($className, array("Value" => $this->Value), $cacheDataObjectGetOne = false) ) {
                $this->Value = $this->i18n_singular_name()."_".$i;
                $i++;
            }
        }
        // No Need To Remove Variations because of onBeforeDelete
        /*$variations = $this->ProductVariation();
        foreach($variations as $variation) $variation->delete();*/
    }


    /**
     * Event handler called after writing to the database.
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->MergeIntoID) {
            $newAttributeValue = $this->MergeInto();
            if($newAttributeValue && $newAttributeValue->exists()) {
                $newID = $this->MergeIntoID;
                $oldID = $this->ID;
                $oldTypeID = $this->TypeID;
                $newTypeID = $newAttributeValue->TypeID;
                DB::query("
                    UPDATE \"ProductVariation_AttributeValues\"
                    SET \"ProductAttributeValueID\" = ".$newID."
                    WHERE \"ProductAttributeValueID\" = ".$oldID.";
                ");
                DB::query("
                    UPDATE \"Product_VariationAttributes\"
                    SET \"ProductAttributeTypeID\" = ".$newTypeID."
                    WHERE \"ProductAttributeTypeID\" = ".$oldTypeID.";
                ");
                $mergedInto = _t('ProductAttributeValue.MERGED_INTO', 'Merged successfully into');
                $this->MergeIntoNote = $mergedInto.' '.$newAttributeValue->FullTitle();
                $toBeDeleted = _t('ProductAttributeValue.TO_BE_DELETED', 'To be deleted');
                $this->Value = $toBeDeleted.' '.$this->Value;
                $this->MergeIntoID = 0;
                $this->write();
            }
        }
    }

}
