<?php

class ProductAttributeValue extends DataObject{

	static $db = array(
		'Value' => 'Varchar(255)',
		'Sort' => 'Int'
	);

	static $has_one = array(
		'Type' => 'ProductAttributeType'
	);

	static $has_many = array();

	static $belongs_to = array(

	);

	static $belongs_many_many = array(
		'ProductVariation' => 'ProductVariation'
	);

	static $summary_fields = array(
		'Value' => 'Value',
	);
	static $casting = array(
		'Title' => 'Text',
		'ValueForDropdown' => "HTMLText"
	);

	function Title() {return $this->getTitle();}
	function getTitle() {
		return $this->Value;
	}

	static $default_sort = "\"TypeID\" ASC, \"Sort\" ASC, \"Value\" ASC";

	public static $singular_name = "Attribute Value";
		function i18n_single_name() { return _t("ProductAttributeValue.ATTRIBUTEVALUE", "Attribute Value");}

	public static $plural_name = "Attribute Values";
		function i18n_plural_name() { return _t("ProductAttributeValue.ATTRIBUTEVALUES", "Attribute Values");}
		public static function get_plural_name(){
			$obj = Singleton("ProductAttributeValue");
			return $obj->i18n_plural_name();
		}

	public function canDelete($member = null) {
		/*$alreadyUsed = DB::query("
			SELECT COUNT(\"ProductAttributeValueID\")
			FROM \"ProductVariation_AttributeValues\"
				INNER JOIN \"OrderItem\" ON \"OrderItem\".\"BuyableID\" = \"ProductVariation_AttributeValues\" .\"ProductVariationID\"
				INNER JOIN \"OrderAttribute\" ON \"OrderAttribute\".\"ID\" = \"OrderItem\" .\"ID\"
			WHERE
				\"ProductAttributeValueID\" = ".$this->ID."
				AND \"OrderAttribute\".\"ClassName\" = 'ProductVariation_OrderItem'"
		)->value();
		if($alreadyUsed) {
			return false;
		}
		return true;*/
		return DB::query("SELECT COUNT(*) FROM \"ProductVariation_AttributeValues\" WHERE \"ProductAttributeValueID\" = '$this->ID'")->value() == 0;
	}



	function getCMSFields(){
		$fields = parent::getCMSFields();
		//TODO: make this a really fast editing interface. Table list field??
		//$fields->removeFieldFromTab('Root.Values','Values');
		if(class_exists("DataObjectSorterController")) {
			$sortLink = DataObjectSorterController::popup_link($className = "ProductAttributeValue", $filterField = "TypeID", $filterValue = $this->TypeID, $linkText = "Sort Values");
			$fields->addFieldToTab("Root.Sort", new LiteralField("SortValues", $sortLink));
		}
		// TO DO: the code below does not work...
		//$fields->removeFieldFromTab("Root.Product Variation", "ProductVariation");
		//$fields->removeFieldFromTab("Root", "Product Variation");
		$table = $fields->fieldByName("ProductVariation");
		if($table) {
			$table->setPermissions("edit", "view");
		}
		return $fields;
	}

	function ValueForDropdown() {return $this->getValueForDropdown();}
	function getValueForDropdown() {
		$v = $this->Value;
		$update = $this->extend("updateValueForDropdown", $v);
		if(is_array($update) && count($update) == 1) {
			$v = $update[0];
		}
		return $v;
	}

	function onBeforeDelete() {
		parent::onBeforeDelete();
		if(!$this->Value) {
			$this->Value = $this->i18n_single_name();
			$i = 0;
			while(DataObject::get_one($this->ClassName, "\"Value\" = '".$this->Value."'")) {
				if($i) {
					$this->Value = $this->i18n_single_name()."_".$i;
				}
				$i++;
			}
		}
		// No Need To Remove Variations because of onBeforeDelete
		/*$variations = $this->ProductVariation();
		foreach($variations as $variation) $variation->delete();*/
	}
	function onBeforeWrite() {
		parent::onBeforeWrite();
		if(!$this->Value) {
			$this->Value = $this->i18n_single_name();
			$i = 0;
			while(DataObject::get_one($this->ClassName, "\"Value\" = '".$this->Value."'")) {
				$this->Value = $this->i18n_single_name()."_".$i;
				$i++;
			}
		}
		// No Need To Remove Variations because of onBeforeDelete
		/*$variations = $this->ProductVariation();
		foreach($variations as $variation) $variation->delete();*/
	}


	function dodataobjectsort() {
		if(!class_exists("DataObjectSorterDOD")) {
			USER_ERROR("you have not installed the dataobjectsorter module - either hide the sort option OR install it: http://sunny.svnrepository.com/svn/sunny-side-up-general/dataobjectsorter");
		}
		else {
			$this->extend("dodataobjectsort");
		}
	}

}

