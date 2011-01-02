<?php

class ProductAttributeType extends DataObject{

	static $db = array(
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

	public static $singular_name = "Attribute Type";
		static function set_singular_name($v) {self::$singular_name = $v;}
		static function get_singular_name() {return self::$singular_name;}

	public static $plural_name = "Attribute Types";
		static function set_plural_name($v) {self::$plural_name = $v;}
		static function get_plural_name() {return self::$plural_name;}


	function getCMSFields(){
		$fields = parent::getCMSFields();
		//TODO: make this a really fast editing interface. Table list field??
		//$fields->removeFieldFromTab('Root.Values','Values');
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

	function getDropDownField($emptystring = null,$values = null){

		$values = ($values) ? $values : $this->Values('','Sort ASC, Value ASC');

		if($values->exists()){
			$field = new DropdownField('ProductAttributes['.$this->ID.']',$this->Name,$values->map('ID','Value'));
			if($emptystring)
				$field->setEmptyString($emptystring);
			return $field;
		}
		return null;
	}



	public function canDelete($member = null) {
		$objects = DataObject::get("ProductAttributeValue", "TypeID = ".$this->ID);
		if(!$objects) {
			return true;
		}
		else {
			$canDelete = true;
			foreach($objects as $obj) {
				if($obj->canDelete()) {
					$canDelete = false;
				}
			}
			return $canDelete;
		}
		return false;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->Name) {
			$this->Name = self::get_singular_name();
			$i = 0;
			while(DataObject::get_one($this->ClassName, "\"Name\" = '".$this->Name."'")) {
				$this->Name = self::get_singular_name()."_".$i;
			}
		}
		if(!$this->Label) {
			$this->Label = $this->Name;
		}
	}

}


