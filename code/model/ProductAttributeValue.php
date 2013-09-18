<?php

class ProductAttributeValue extends DataObject{

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
		'Value' => 'Value',
		'Type.Title' => 'Value'
	);

	private static $searchable_fields = array(
		'Value' => 'PartialMatchFilter'
	);

	private static $casting = array(
		'Title' => 'Varchar',
		'ValueForDropdown' => "HTMLText"
	);

	function Title() {return $this->getTitle();}
	function getTitle() {
		return $this->Value;
	}

private static $default_sort = "\"TypeID\" ASC, \"Sort\" ASC, \"Value\" ASC";

	private static $singular_name = "Attribute Value";
		function i18n_singular_name() { return _t("ProductAttributeValue.ATTRIBUTEVALUE", "Attribute Value");}

	private static $plural_name = "Attribute Values";
		function i18n_plural_name() { return _t("ProductAttributeValue.ATTRIBUTEVALUES", "Attribute Values");}
		public static function get_plural_name(){
			$obj = Singleton("ProductAttributeValue");
			return $obj->i18n_plural_name();
		}

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
		if(class_exists("DataObjectSorterController") && $this->hasExtension("DataObjectSorterDOD")) {
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

	/**
	 * casted variable
	 * returns the value for the option in the select dropdown box.
	 *@return String
	 **/
	function ValueForDropdown() {return $this->getValueForDropdown();}
	function getValueForDropdown() {
		$this->ValueForDropdownFinal = $this->Value;
		$this->extend("updateValueForDropdown");
		return $this->ValueForDropdownFinal;
	}

	/**
	 * casted variable
	 * returns the value for the variations table
	 *@return String
	 **/
	function ValueForTable() {return $this->getValueForTable();}
	function getValueForTable() {
		$this->ValueForTableFinal = $this->Value;
		$this->extend("updateValueForTable");
		return $this->ValueForTableFinal;
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

