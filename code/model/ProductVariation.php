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
		'Sort' => "Int",
		'Description' => "Varchar(255)"
	);

	public static $has_one = array(
		'Product' => 'Product',
		'Image' => 'ProductVariation_Image'
	);

	static $many_many = array(
		'AttributeValues' => 'ProductAttributeValue'
	);

	public static $casting = array(
		'Title' => 'HTMLText',
		'Link' => 'Text',
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

	public static $field_labels = array(
		"Description" => "Title (optional)"
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

	/**
	*@param
	**/
	protected static $title_style_option = array(
		"default" => array(
			"ShowType" => true,
			"BetweenTypeAndValue" => ": ",
			"BetweenVariations" => ", "
		)
	);
		public static function add_title_style_option($code, $showType, $betweenTypeAndValue, $betweenVariations) {
			self::$title_style_option[$code] = array(
				"ShowType" => $showType,
				"BetweenTypeAndValue" => $betweenTypeAndValue,
				"BetweenVariations" => $betweenVariations
			);
			self::set_current_style_option_code($code);
		}
		public static function remove_title_style_option($code) {unset(self::$title_style_option[$code]);}

	protected static $current_style_option_code = "default";
		public static function set_current_style_option_code($v) {self::$current_style_option_code = $v;}
		public static function get_current_style_option_code() {return self::$current_style_option_code;}

	public static function get_current_style_option_array() {
		return self::$title_style_option[self::get_current_style_option_code()];
	}

	function getCMSFields() {
		$product = $this->Product();
		$fields = new FieldSet(new TabSet('Root',
			new Tab('Main',
				new NumericField('Price'),
				new CheckboxField('AllowPurchase', 'Allow Purchase ?'),
				new TextField('InternalItemID', 'Internal Item ID'),
				new ImageField('Image')
			)
		));
		$types = $product->VariationAttributes();
		if($this->ID) {
			$purchased = $this->getPurchasedTotal();
			$values = $this->AttributeValues();
			foreach($types as $type) {
				$field = $type->getDropDownField();
				if($field) {
					$value = $values->find('TypeID', $type->ID);
					if($value) {
						$field->setValue($value->ID);
						if($purchased) {
							$field = $field->performReadonlyTransformation();
							$field->setName("Type{$type->ID}");
						}
					}
					else {
						if($purchased) {
							$field = new ReadonlyField("Type{$type->ID}", $type->Name, 'You can not select a value because it has already been purchased.');
						}
						else {
							$field->setEmptyString('');
						}
					}
				}
				else {
					$field = new ReadonlyField("Type{$type->ID}", $type->Name, 'No values to select');
				}
				$fields->addFieldToTab('Root.Attributes', $field);
			}
			$fields->addFieldToTab('Root.Orders',
				new ComplexTableField(
					$this,
					'OrderItems',
					'ProductVariation_OrderItem',
					array(
						'Order.ID' => '#',
						'Order.Created' => 'When',
						'Order.Member.Name' => 'Member',
						'Quantity' => 'Quantity',
						'Total' => 'Total'
					),
					new FieldSet(),
					"\"BuyableID\" = '$this->ID'",
					"\"Created\" DESC"
				)
			);
		}
		else {
			foreach($types as $type) {
				$field = $type->getDropDownField();
				$fields->addFieldToTab('Root.Attributes', $field);
			}
		}
		$this->extend('updateCMSFields', $fields);
		return $fields;
	}

	function getRequirementsForPopup() {
		$purchased = $this->getPurchasedTotal();
		if(! $this->ID || ! $purchased) {
			Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
			Requirements::javascript('ecommerce_product_variation/javascript/productvariation.js');
			Requirements::customScript("ProductVariation.set_url('createecommercevariations')", 'CreateEcommerceVariationsField_set_url');
			Requirements::customCSS('#ComplexTableField_Popup_AddForm input.loading {background: url("cms/images/network-save.gif") no-repeat scroll left center #FFFFFF; padding-left: 16px;}');
		}
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		if(isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes'])){
			$this->AttributeValues()->setByIDList(array_values($_POST['ProductAttributes']));
		}
		unset($_POST['ProductAttributes']);
	}

	function onBeforeDelete() {
		parent::onBeforeDelete();
		$this->AttributeValues()->removeAll();
	}

	function Link(){
		return $this->Product()->Link();
	}

	function getTitle($withSpan = false){
		if($this->Description) {
			$title = $this->Description;
			if($withSpan) {
				$title = "<span>".$title."</span>";
			}
			return $title;
		}
		$styleArray = self::get_current_style_option_array();
		$values = $this->AttributeValues();
		if($values->exists()){
			$labelvalues = array();
			if(count($values)) {
				foreach($values as $value){
					$v = '';
					if($withSpan) {
						$v = '<span>';
					}
					if($styleArray["ShowType"]) {
						$v .= $value->Type()->Label.$styleArray["BetweenTypeAndValue"];
					}
					$v .= $value->Value;
					if($withSpan) {
						$v .= '</span>';
					}
					$labelvalues[] = $v;
				}
			}
			$title = implode($styleArray["BetweenVariations"],$labelvalues);
			return $title;
		}
		return $this->InternalItemID;
	}

	function getAllowPuchaseText() {
		return $this->AllowPurchase ? 'Yes' : 'No';
	}

	function getPurchasedTotal() {
		return DB::query("SELECT COUNT(*) FROM \"OrderItem\" WHERE \"BuyableID\" = '$this->ID'")->value();
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

	function canDelete() {
		return $this->getPurchasedTotal() == 0;
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
			$allowpurchase = $this->Price > 0;
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

	// ProductVariation Access Function
	public function ProductVariation($current = false) {
		//TO DO: the line below does not work because it does NOT get the right version
		return $this->Buyable(true);
		//THIS WORKS
		return DataObject::get_by_id("ProductVariation", $this->BuyableID);
	}

	function hasSameContent($orderItem) {
		$parentIsTheSame = parent::hasSameContent($orderItem);
		return $parentIsTheSame && $orderItem instanceof ProductVariation_OrderItem;
	}

	function UnitPrice() {
		$unitPrice = $this->ProductVariation()->Price;
		$this->extend('updateUnitPrice',$unitPrice);
		return $unitPrice;
	}

	function TableTitle() {
		$tabletitle = $this->ProductVariation()->Product()->Title;
		$this->extend('updateTableTitle',$tabletitle);
		return $tabletitle;
	}

	function TableSubTitle() {
		$tablesubtitle = $this->ProductVariation()->getTitle(true);
		$this->extend('updateTableSubTitle',$tablesubtitle);
		return $tablesubtitle;
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
		if($db->hasTable("Product_OrderItem")) {
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


}
