<?php
/**
 * @todo How does this class work in relation to Product?
 *
 * @package ecommerce
 */
class ProductVariation extends DataObject {

	public static $db = array(
		'InternalItemID' => 'Varchar(30)',
		'Price' => 'Currency',
		'AllowPurchase' => 'Boolean',
		'Sort' => "Int"
	);

	public static $has_one = array(
		'Product' => 'Product',
		"Image" => "ProductVariation_Image"
	);

	static $many_many = array(
		'AttributeValues' => 'ProductAttributeValue'
	);

	public static $casting = array(
		'Title' => 'Text',
		'AllowPuchaseText' => 'Text',
		'PurchasedTotal' => 'Int'
	);

	public static $versioning = array(
		'Stage'
	);

	public static $extensions = array(
		"Versioned('Stage')",
		"Buyable"
	);

	public static $indexes = array(
		"Sort" => true
	);

	public static $defaults = array(
		"AllowPurchase" => 1
	);

	public static $summary_fields = array(
		'Price' => 'Price',
		'AllowPuchaseText' => 'Buyable',
		'PurchasedTotal' => 'Purchased Total'
	);

	public static $default_sort = "Sort ASC, InternalItemID ASC";


	public static $singular_name = "Product Variation";
		static function set_singular_name($v) {self::$singular_name = $v;}
		static function get_singular_name() {return self::$singular_name;}
		function i18n_singular_name() { return _t("Order.PRODUCTVARIATION", self::get_singular_name());}

	public static $plural_name = "Product Variations";
		static function set_plural_name($v) {self::$plural_name = $v;}
		static function get_plural_name() {return self::$plural_name;}
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeFieldFromTab("Root.Main", "Version");
		//add attributes dropdowns
		if($this->Product()->VariationAttributes()->exists() && $attributes = $this->Product()->VariationAttributes()){
			foreach($attributes as $attribute){
				if($field = $attribute->getDropDownField()){
					if($value = $this->AttributeValues()->find('TypeID',$attribute->ID)) {
						$field->setValue($value->ID);
					}
					$fields->addFieldToTab("Root.Specifications", $field);
				}
				//TODO: allow setting custom value, rather than visiting the products section
			}
		}
		$this->extend('updateCMSFields', $fields);
		return $fields;
	}

	function onBeforeWrite(){
		parent::onBeforeWrite();
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		if(isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes'])){
			$this->AttributeValues()->setByIDList(array_values($_POST['ProductAttributes']));
		}
		unset($_POST['ProductAttributes']);
		//not sure if this second write is required....
		//$this->write();
	}

	function onBeforeDelete() {
		parent::onBeforeDelete();
		$this->AttributeValues()->removeAll();
	}

	function getTitle(){
		$values = $this->AttributeValues();
		if($values->exists()){
			$labelvalues = array();
			foreach($values as $value){
				$labelvalues[] = $value->Type()->Label.':'.$value->Value;
			}
			return implode(', ',$labelvalues);
		}
		return $this->InternalItemID;
	}
	
	function getAllowPuchaseText() {
		return $this->AllowPurchase ? 'Yes' : 'No';
	}
	
	function getPurchasedTotal() {
		return DB::query("SELECT COUNT(*) FROM `ProductVariation_OrderItem` WHERE `ProductVariationID` = '$this->ID'")->value();
	}
	
	//this is used by TableListField to access attribute values.
	function AttributeProxy(){
		$do = new DataObject();
		if($this->AttributeValues()->exists()){
			foreach($this->AttributeValues() as $value){
				$do->{'Val'.$value->Type()->Name} = $value->Value;
			}
		}
		return $do;
	}

	function canPurchase($member = null) {
		if($this->ShopClosed()) {
			return false;
		}
		$allowpurchase = false;
		if(!$this->AllowPurchase) {
			return false;
		}
		if($product = $this->Product()) {
			$allowpurchase = ($this->Price > 0) && $product->AllowPurchase;
		}
		$extended = $this->extendedCan('canPurchase', $member);
		if($allowpurchase && $extended !== null) {
			$allowpurchase = $extended;
		}
		return $allowpurchase;
	}

	function populateDefaults() {
		$this->AllowPurchase = 1;
	}

}



class ProductVariation_Image extends Image {

}


class ProductVariation_OrderItem extends Product_OrderItem {

	static $db = array(
		'KeepMeTwo' => 'Boolean'
	);
	// ProductVariation Access Function

	public function ProductVariation($current = false) {
		return $this->Buyable($current);
	}


	function hasSameContent($orderItem) {
		$parentIsTheSame = parent::hasSameContent($orderItem);
		return $parentIsTheSame && $orderItem instanceof ProductVariation_OrderItem;
	}

	function UnitPrice() {
		return $this->ProductVariation()->Price;
	}

	function TableTitle() {
		$tabletitle = $this->ProductVariation()->Product()->Title . ' (' . $this->ProductVariation()->Title . ')';
		$this->extend('updateTableTitle',$tabletitle);
		return $tabletitle;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}

	public function debug() {
		$title = $this->TableTitle();
		$productVariationID = $this->BuyableID;
		$productVariationVersion = $this->Version;
		return parent::debug() .<<<HTML
			<h3>ProductVariation_OrderItem class details</h3>
			<p>
				<b>Title : </b>$title<br/>
				<b>ProductVariation ID : </b>$productVariationID<br/>
				<b>ProductVariation Version : </b>$productVariationVersion<br/>
			</p>
HTML;
	}


	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		// we must check for individual database types here because each deals with schema in a none standard way
		//can we use Table::has_field ???
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ){
      $exist = DB::query("SELECT column_name FROM information_schema.columns WHERE table_name ='Product_OrderItem' AND column_name = 'ProductVariationVersion'")->numRecords();
		}
		else{
			// default is MySQL - broken for others, each database conn type supported must be checked for!
      $exist = DB::query("SHOW COLUMNS FROM \"Product_OrderItem\" LIKE 'ProductVariationVersion'")->numRecords();
		}
 		if($exist > 0) {
			DB::query("
				UPDATE \"OrderItem\", \"ProductVariation_OrderItem\"
					SET \"OrderItem\".\"Version\" = \"ProductVariation_OrderItem\".\"ProductVariationVersion\"
				WHERE \"OrderItem\".\"ID\" = \"ProductVariation_OrderItem\".\"ID\"
			");
			DB::query("
				UPDATE \"OrderItem\", \"ProductVariation_OrderItem\"
					SET \"OrderItem\".\"BuyableID\" = \"ProductVariation_OrderItem\".\"ProductVariationID\"
				WHERE \"OrderItem\".\"ID\" = \"ProductVariation_OrderItem\".\"ID\"
			");
 			DB::query("ALTER TABLE \"ProductVariation_OrderItem\" CHANGE COLUMN \"ProductVariationVersion\" \"_obsolete_ProductVariationVersion\" Integer(11)");
 			DB::query("ALTER TABLE \"ProductVariation_OrderItem\" CHANGE COLUMN \"ProductVariationID\" \"_obsolete_ProductVariationID\" Integer(11)");
 			DB::alteration_message('made ProductVariationVersion and ProductVariationID obsolete in ProductVariation_OrderItem', 'obsolete');
		}

	}


}
