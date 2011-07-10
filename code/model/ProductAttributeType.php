<?php

/**
 * This class contains list items such as "size", "colour"
 * Not XL, Red, etc..., but the lists that contain the
 * ProductAttributeValues.
 * For a clothing store you will have two entries:
 * - Size
 * - Colour
 *
 *
 */
 
class ProductAttributeType extends DataObject{

	public static $db = array(
		'Name' => 'Varchar', //for back-end use
		'Label' => 'Varchar', //for front-end use
		'Sort' => 'Int' //for front-end use
		//'Unit' => 'Varchar' //TODO: for future use
	);

	static $has_one = array();

	static $has_many = array(
		'Values' => 'ProductAttributeValue'
	);

	static $summary_fields = array(
		'Name' => 'Name'
	);


	static $indexes = array(
		"Sort" => true
	);

	static $default_sort = "\"Sort\" ASC, \"Name\"";

	//We need this to make certain templates work (see ProductWithVariationDecorator::VariationsPerVariationType)
	public $Variations = null;

	public static $singular_name = "Attribute Type";
		function i18n_single_name() { return _t("ProductAttributeType.ATTRIBUTETYPE", "Attribute Type");}

	public static $plural_name = "Attribute Types";
		function i18n_plural_name() { return _t("ProductAttributeType.ATTRIBUTETYPES", "Attribute Types");}
		public static function get_plural_name(){
			$obj = Singleton("ProductAttributeType");
			return $obj->i18n_plural_name();
		}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		//TODO: make this a really fast editing interface. Table list field??
		//$fields->removeFieldFromTab('Root.Values','Values');
		if(class_exists("DataObjectSorterController")) {
			$sortLink = DataObjectSorterController::popup_link($className = "ProductAttributeType", $filterField = "", $filterValue = "", $linkText = "Sort Types");
			$fields->addFieldToTab("Root.Sort", new LiteralField("SortTypes", $sortLink));
		}
		return $fields;
	}

	static function find_or_make($name){
		$name = strtolower($name);
		if($type = DataObject::get_one('ProductAttributeType',"LOWER(\"Name\") = '$name'"))
			return $type;

		$type = new ProductAttributeType();
		$type->Name = $name;
		$type->Label = $name;
		$type->write();

		return $type;
	}

	function addValues(array $values){
		$avalues = $this->convertArrayToValues($values);
		$this->Values()->addMany($avalues);
	}

	function convertArrayToValues(array $values){
		$set = new DataObjectSet();
		foreach($values as $value){
			$val = $this->Values()->find('Value',$value);
			if(!$val){  //TODO: ignore case, if possible
				$val = new ProductAttributeValue();
				$val->Value = $value;
				$val->write();
			}
			$set->push($val);
		}
		return $set;
	}

	function getDropDownField($emptystring = null, $values = null) {
		$values = ($values) ? $values : $this->Values('',"\"Sort\" ASC, \"Value\" ASC");
		if($values->exists() && $values->count() > 0){
			$field = new DropdownField('ProductAttributes['.$this->ID.']',$this->Name,$values->map('ID','ValueForDropdown'));
			if($emptystring && $values->count() > 1) {
				$field->setEmptyString($emptystring);
			}
			return $field;
		}
		return null;
	}



	public function canDelete($member = null) {
		$values = $this->Values();
		foreach($values as $value) {
			if(! $value->canDelete()) return false;
		}
		return true;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		$i = 0;
		while(!$this->Name || DataObject::get_one($this->ClassName, "\"Name\" = '".$this->Name."' AND \"".$this->ClassName."\".\"ID\" <> ".intval($this->ID))) {
			$this->Name = $this->i18n_single_name();
			if($i) {
				$this->Name .= "_".$i;
			}
			$i++;
		}
		if(!$this->Label) {
			$this->Label = $this->Name;
		}
	}

	function onBeforeDelete() {
		parent::onBeforeDelete();
		$values = $this->Values();
		foreach($values as $value) {
			if($value->canDelete()) {
				$value->delete();
				$value->destroy();
			}
		}
	}
}


