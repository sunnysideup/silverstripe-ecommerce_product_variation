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
		'Title' => 'Text'
	);

	function getTitle() {
		return $this->Value;
	}

	function Title() {
		return $this->getTitle();
	}

	static $default_sort = "\"TypeID\" ASC, \"Sort\" ASC";

	public static $singular_name = "Attribute Value";
		static function set_singular_name($v) {self::$singular_name = $v;}
		static function get_singular_name() {return self::$singular_name;}


	public static $plural_name = "Attribute Values";
		static function set_plural_name($v) {self::$plural_name = $v;}
		static function get_plural_name() {return self::$plural_name;}

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
		return DB::query("SELECT COUNT(*) FROM `ProductVariation_AttributeValues` WHERE `ProductAttributeValueID` = '$this->ID'")->value() == 0;
	}

	function onBeforeDelete() {
		parent::onBeforeDelete();
		if(!$this->Value) {
			$this->Value = self::get_singular_name();
			$i = 0;
			while(DataObject::get_one($this->ClassName, "\"Value\" = '".$this->Value."'")) {
				$this->Value = self::get_singular_name()."_".$i;
			}
		}
		// No Need To Remove Variations because of onBeforeDelete
		/*$variations = $this->ProductVariation();
		foreach($variations as $variation) $variation->delete();*/
	}
}

