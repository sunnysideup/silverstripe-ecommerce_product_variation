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

class ProductAttributeType extends DataObject implements EditableEcommerceObject{

	/**
	 * Standard SS variable.
	 */
	private static $api_access = array(
		'view' => array(
			"Name",
			"Label",
			"Values"
		)
	);

	/**
	 * Standard SS variable.
	 */
	private static $db = array(
		'Name' => 'Varchar', //for back-end use
		'Label' => 'Varchar', //for front-end use
		'Sort' => 'Int', //for front-end use
		'MergeIntoNote' => 'Varchar(255)' //for front-end use
		//'Unit' => 'Varchar' //TODO: for future use
	);

	/**
	 * Standard SS variable.
	 */
	private static $has_one = array(
		'MoreInfoLink' => 'SiteTree',
		'MergeInto' => 'ProductAttributeType'
	);

	/**
	 * Standard SS variable.
	 */
	private static $has_many = array(
		'Values' => 'ProductAttributeValue'
	);

	/**
	 * Standard SS variable.
	 */
	private static $summary_fields = array(
		'FullName' => 'Type'
	);

	/**
	 * Standard SS variable.
	 */
	private static $searchable_fields = array(
		'Name' => 'PartialMatchFilter',
		'Label' => 'PartialMatchFilter'
	);

	/**
	 * Standard SS variable.
	 */
	private static $belongs_many_many = array(
		'Products' => 'Product'
	);

	/**
	 * Standard SS variable.
	 */
	private static $casting = array(
		'FullName' => 'Varchar'
	);

	/**
	 * Standard SS variable.
	 */
	private static $indexes = array(
		"Sort" => true
	);

	/**
	 * Standard SS variable.
	 */
	private static $default_sort = "\"Sort\" ASC, \"Name\"";

	/**
	 * Standard SS variable.
	 */
	private static $singular_name = "Variation Attribute Type";
		function i18n_singular_name() { return _t("ProductAttributeType.ATTRIBUTETYPE", "Variation Attribute Type");}

	/**
	 * Standard SS variable.
	 */
	private static $plural_name = "Variation Attribute Types";
		function i18n_plural_name() { return _t("ProductAttributeType.ATTRIBUTETYPES", "Variation Attribute Types");}
		public static function get_plural_name(){
			$obj = Singleton("ProductAttributeType");
			return $obj->i18n_plural_name();
		}

	/**
	 * finds or makes a ProductAttributeType, based on the lower case Name.
	 *
	 * @param String $name
	 * @param Boolean $create
	 *
	 * @return ProductAttributeType
	 */
	public static function find_or_make($name, $create = true){
		$name = strtolower($name);
		if($type = ProductAttributeType::get()->where("LOWER(\"Name\") = '$name'")->First()) {
			return $type;
		}
		$type = ProductAttributeType::create();
		$type->Name = $name;
		$type->Label = $name;
		if($create) {
			$type->write();
		}
		return $type;
	}

	/**
	 * Standard SS Methodd.
	 */
	function getCMSFields(){
		$fields = parent::getCMSFields();
		$nameField = $fields->dataFieldByName("Name");
		$nameField->SetRightTitle(_t("ProductAttributeType.NAME_RIGHT_TITLE", "Mainly used for easy recognition in the CMS"));
		$valueField = $fields->dataFieldByName("Label");
		$valueField->SetRightTitle(_t("ProductAttributeType.VALUE_RIGHT_TITLE", "Mainly used for site users"));
		$fields->addFieldToTab(
			"Root.Main",
			new OptionalTreeDropdownField(
				"MoreInfoLinkID",
				_t("ProductAttributeType.MORE_INFO_LINK", "More info page"),
				"SiteTree"
			)
		);
		//TODO: make this a really fast editing interface. Table list field??
		//$fields->removeFieldFromTab('Root.Values','Values');
		$fields->AddFieldToTab("Root.Advanced", new DropdownField("MergeIntoID", "Merge into ...", array(0 => "-- do not merge --") + ProductAttributeType::get()->exclude(array("ID" => $this->ID))->map()->toArray()));
		$fields->AddFieldToTab("Root.Advanced", new ReadOnlyField("MergeIntoNote", "Merge Results Notes"));
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
	 * add more values to a type
	 * array should be an something like red, blue, orange (strings NOT objects)
	 * @param Array
	 */
	function addValues(array $values){
		$avalues = $this->convertArrayToValues($values);
		$this->Values()->addMany($values);
	}

	/**
	 * takes an array of values
	 * and finds them or creates them.
	 *
	 * @param Array $values
	 * @return ArrayList
	 */
	function convertArrayToValues(array $values){
		$set = new ArrayList();
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

	/**
	 *
	 * @param String $emptyString
	 * @param DataList $values
	 *
	 * @return DropdownField | HiddenField
	 */
	function getDropDownField($emptystring = null, $values = null) {
		//to do, why do switch to "all" the options if there are no values?
		$values = $this->getValuesForDropdown($values);
		if($values && is_array($values) && count($values)){
			$field = new DropdownField('ProductAttributes['.$this->ID.']', $this->Name, $values);
			if($emptystring && $values->count() > 1) {
				$field->setEmptyString($emptystring);
			}
		}
		else {
			$field = new HiddenField('ProductAttributes['.$this->ID.']', 0);
		}
		$this->extend("updateDropDownField", $field);
		return $field;
	}

	/**
	 *
	 * @param String $emptyString
	 * @param DataList $values
	 *
	 * @return array
	 */
	function getValuesForDropdown($values = null) {
		$values = ($values) ? $values : $this->Values();
		if($values && $values->count() > 0){
			return $values->map('ID','ValueForDropdown')->toArray();
		}
		else {
			return array();
		}
	}

	/**
	 * It can be deleted if all its Values can be deleted only...
	 *
	 * @return Boolean
	 */
	public function canDelete($member = null) {
		$values = $this->Values();
		foreach($values as $value) {
			if(! $value->canDelete()) {
				return false;
			}
		}
		return true;
	}

	/**
	 * standard SS method
	 * Adds a name if there is no name.
	 * Adds a label is there is no label.
	 *
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		$i = 0;
		$className = $this->ClassName;
		while(!$this->Name || $className::get()->filter(array("Name" => $this->Name))->exclude("ID", $this->ID)->First() ){
			$this->Name = $this->i18n_singular_name();
			if($i) {
				$this->Name .= "_".$i;
			}
			$i++;
		}
		if(!$this->Label) {
			$this->Label = $this->Name;
		}
	}

	function onAfterWrite(){
		parent::onAfterWrite();
		if($this->MergeIntoID) {
			$newAttributeType = $this->MergeInto();
			$canDoMerge = true;
			if($this->Values()->count() != $newAttributeType->Values()->count()) {
				$canDoMerge = false;
				$this->MergeIntoNote = "NON-MATCHINGE VALUE COUNTS";
			}
			else {

				$mergeMapArray_OLD = array();
				$mergeMapArray_NEW = array();
				$mergeMapArrayGO = array();
				foreach($this->Values() as $value) {
					$mergeMapArray_OLD[] = $value->ID;
				}
				foreach($newAttributeType->Values() as $value) {
					$mergeMapArray_NEW[] = $value->ID;
				}
				foreach($mergeMapArray_OLD as $key => $id_OLD) {
					$id_NEW = $mergeMapArray_NEW[$key];
					$obj_OLD = ProductAttributeValue::get()->byID($id_OLD);
					$obj_NEW = ProductAttributeValue::get()->byID($id_NEW);
					if($obj_OLD && $obj_NEW) {
						if($obj_OLD->Code == $obj_NEW->Code || $obj_OLD->Value == $obj_NEW->Value || 1 == 1) {
							$mergeMapArrayGO[$obj_OLD->ID] = $obj_NEW->ID;
						}
						else {
							$this->MergeIntoNote = "NON-MATCHINGE VALUES: ".$obj_OLD->Code."!=".$obj_NEW->Code." AND ".$obj_OLD->Value."!=".$obj_NEW->Value;
							$canDoMerge = false;
						}
					}
					else {
						$this->MergeIntoNote = "MISSING OLD OR NEW OBJECT";
						$canDoMerge = false;
					}
				}
			}
			if($canDoMerge) {
				foreach($mergeMapArrayGO as $id_OLD => $id_NEW) {
					DB::query("
						UPDATE \"ProductVariation_AttributeValues\"
						SET \"ProductAttributeValueID\" = ".$id_NEW."
						WHERE \"ProductAttributeValueID\" = ".$id_OLD.";
					");

				}
				DB::query("
					UPDATE \"Product_VariationAttributes\"
					SET \"ProductAttributeTypeID\" = ".$this->MergeIntoID."
					WHERE \"ProductAttributeTypeID\" = ".$this->ID.";
				");
				$values = ProductAttributeValue::get()->filter(array("TypeID" => $this->ID));
				foreach($values as $value) {
					$value->delete();
				}
				$this->MergeIntoNote = "Merged successfully into ".$this->MergeInto()->Name." ...";
				$this->Name = "TO BE DELETED ".$this->Name;
				$this->Label = "TO BE DELETED ".$this->Label;
			}
			$this->MergeIntoID = 0;
			$this->write();
		}

	}

	/**
	 * Delete all the values
	 * that are related to this type.
	 */
	function onBeforeDelete() {
		parent::onBeforeDelete();
		$values = $this->Values();
		foreach($values as $value) {
			if($value->canDelete()) {
				$value->delete();
				$value->destroy();
			}
		}
		DB::query("DELETE FROM \"Product_VariationAttributes\" WHERE \"ProductAttributeTypeID\" = ".$this->ID);
	}

	function cleanup(){
		$sql = "
			Select \"ProductAttributeTypeID\"
			FROM \"Product_VariationAttributes\"
			WHERE \"ProductID\" = ".$this->owner->ID;
		$data = DB::query($sql);
		$array = $data->keyedColumn();
		if(is_array($array) && count($array) ) {
			foreach($array as $key => $productAttributeTypeID) {
				//attribute type does not exist.
				if( ! ProductAttributeType::get()->byID($productAttributeTypeID) ) {
					//delete non-existing combinations of Product_VariationAttributes (where the attribute does not exist)
					//DB::query("DELETE FROM \"Product_VariationAttributes\" WHERE \"ProductAttributeTypeID\" = $productAttributeTypeID");
					//non-existing product attribute values.
					$productAttributeValues = ProductAttributeValue::get()->filter(array("TypeID" => $productAttributeTypeID));
					if($productAttributeValues->count()) {
						foreach($productAttributeValues as $productAttributeValue) {
							$productAttributeValue->delete();
						}
					}
				}
			}
		}
	}

	/**
	 * useful for GridField
	 * @return String
	 */
	function getFullName(){
		return $this->Name." (".$this->Values()->count()."), label: ".$this->Label;
	}
}


