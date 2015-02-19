<?php

class ProductAttributeValue extends DataObject implements EditableEcommerceObject{

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
		'Sort' => 'Int'
	);

	private static $has_one = array(
		'Type' => 'ProductAttributeType'
	);

	private static $belongs_many_many = array(
		'ProductVariation' => 'ProductVariation'
	);

	private static $summary_fields = array(
		'Type.Title' => 'Type',
		'Value' => 'Value'
	);

	private static $searchable_fields = array(
		'Value' => 'PartialMatchFilter'
	);

	private static $casting = array(
		'Title' => 'HTMLText',
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
	 * @param ProductAttributeType | Int $type
	 * @param String $value
	 * @param Boolean $create
	 *
	 * @return ProductAttributeType
	 */
	public static function find_or_make($type, $value, $create = true){
		if($type instanceof ProductAttributeType) {
			$type = $type->ID;
		}
		$value = strtolower($value);
		if($valueObj = ProductAttributeValue::get()->where("(LOWER(\"Code\") = '$value' OR LOWER(\"Value\") = '$value') AND TypeID = ".intval($type))->First()) {
			return $valueObj;
		}
		$valueObj = new ProductAttributeValue();
		$valueObj->Code = $value;
		$valueObj->Value = $value;
		$valueObj->TypeID = $type;
		if($create) {
			$valueObj->write();
		}
		return $valueObj;
	}

	private static $default_sort = "\"TypeID\" ASC, \"Sort\" ASC";

	private static $singular_name = "Variation Attribute Value";
		function i18n_singular_name() { return _t("ProductAttributeValue.ATTRIBUTEVALUE", "Variation Attribute Value");}

	private static $plural_name = "Variation Attribute Values";
		function i18n_plural_name() { return _t("ProductAttributeValue.ATTRIBUTEVALUES", "Variation Attribute Values");}

	public function canDelete($member = null) {
		return DB::query("
			SELECT COUNT(*)
			FROM \"ProductVariation_AttributeValues\"
				INNER JOIN \"ProductVariation\" ON  \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
			WHERE \"ProductAttributeValueID\" = ".$this->ID
		)->value() == 0;
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		//TODO: make this a really fast editing interface. Table list field??
		//$fields->removeFieldFromTab('Root.Values','Values');
		// TO DO: the code below does not work...
		//$fields->removeFieldFromTab("Root.Product Variation", "ProductVariation");
		//$fields->removeFieldFromTab("Root", "Product Variation");
		$table = $fields->fieldByName("ProductVariation");
		if($table) {
			$table->setPermissions("edit", "view");
		}
		return $fields;
	}

	/**
	 * link to edit the record
	 * @param String | Null $action - e.g. edit
	 * @return String
	 */
	public function CMSEditLink($action = null) {
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
	function ValueForDropdown() {return $this->getValueForDropdown();}
	function getValueForDropdown() {
		$value = $this->Value;
		$extensionValue = $this->extend("updateValueForDropdown");
		if($extensionValue !== null && is_array($extensionValue) && count($extensionValue)) {
			$value = implode("", $extensionValue);
		}
		return $value;
	}

	/**
	 * casted variable
	 * returns the value for the variations table
	 * @return String (HTML)
	 **/
	function ValueForTable() {return $this->getValueForTable();}
	function getValueForTable() {
		$value = $this->Value;
		$extensionValue = $this->extend("updateValueForTable");
		if($extensionValue !== null && is_array($extensionValue) && count($extensionValue)) {
			$value = implode("", $extensionValue);
		}
		return $value;
	}

	/**
	 * casted variable
	 * returns the value for the variations table
	 * @return String
	 **/
	function Title() {return $this->getTitle();}
	function getTitle() {
		return $this->getValueForTable();
	}

	function onBeforeDelete() {
		parent::onBeforeDelete();
		if(!$this->Value) {
			$this->Value = $this->i18n_singular_name();
			$i = 0;
			$className = $this->ClassName;
			while( $className::get()->filter(array("Value" => $this->Value))->First() ) {
				if($i) {
					$this->Value = $this->i18n_singular_name()."_".$i;
				}
				$i++;
			}
		}
		//delete ProductVariation_AttributeValues were the Attribute Value does not exist.
		DB::query("DELETE FROM \"ProductVariation_AttributeValues\" WHERE \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\" = ".$this->ID);
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->Value) {
			$this->Value = $this->i18n_single_name();
			$i = 0;
			$className = $this->ClassName;
			while($className::get()->filter(array("Value" => $this->Value))->First() ) {
				$this->Value = $this->i18n_singular_name()."_".$i;
				$i++;
			}
		}
		// No Need To Remove Variations because of onBeforeDelete
		/*$variations = $this->ProductVariation();
		foreach($variations as $variation) $variation->delete();*/
	}


}

