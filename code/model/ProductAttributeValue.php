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
	static $default_sort = "\"TypeID\" ASC, \"Sort\" ASC";

	public static $singular_name = "Attribute Value";

	public static $plural_name = "Attribute Values";

	public function canDelete($member = null) {
		$alreadyUsed = DB::query("
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
		return true;
	}

}

