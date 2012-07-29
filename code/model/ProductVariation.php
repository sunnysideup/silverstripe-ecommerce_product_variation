<?php
/**
 * @todo How does this class work in relation to Product?
 *
 * @package ecommerce
 */
class ProductVariation extends DataObject implements BuyableModel{

	/**
	 * Standard SS variable.
	 */
	public static $api_access = array(
		'view' => array(
			"Title",
			"Description",
			"FullName",
			"AllowPurchase",
			"InternalItemID",
			"NumberSold",
			"Price",
			"Version"
		)
	);

	/**
	 * Standard SS variable.
	 */
	public static $db = array(
		'InternalItemID' => 'Varchar(30)',
		'Price' => 'Currency',
		'AllowPurchase' => 'Boolean',
		'Sort' => 'Int',
		'NumberSold' => 'Int',
		'Description' => 'Varchar(255)',
		'FullName' => 'Varchar(255)',
		'FullSiteTreeSort' => 'Varchar(110)'
	);

	/**
	 * Standard SS variable.
	 */
	public static $has_one = array(
		'Product' => 'Product',
		'Image' => 'Product_Image'
	);

	/**
	 * Standard SS variable.
	 */
	static $many_many = array(
		'AttributeValues' => 'ProductAttributeValue'
	);

	/**
	 * Standard SS variable.
	 */
	public static $casting = array(
		'Parent' => 'Product',
		'Title' => 'HTMLText',
		'Link' => 'Text',
		'AllowPuchaseText' => 'Text',
		'CalculatedPrice' => 'Currency'
	);

	/**
	 * Standard SS variable.
	 */
	public static $defaults = array(
		"AllowPurchase" => 1
	);

	/**
	 * Standard SS variable.
	 */
	public static $versioning = array(
		'Stage'
	);

	/**
	 * Standard SS variable.
	 */
	public static $extensions = array(
		"Versioned('Stage')"
	);

	/**
	 * Standard SS variable.
	 */
	public static $indexes = array(
		"Sort" => true,
		"FullName" => true,
		"FullSiteTreeSort" => true
	);

	/**
	 * Standard SS variable.
	 */
	public static $field_labels = array(
		"Description" => "Title (optional)"
	);

	/**
	 * Standard SS variable.
	 */
	public static $summary_fields = array(
		'Product.Title' => 'Product',
		'Title' => 'Title',
		'InternalItemID' => 'InternalItemID',
		'Price' => 'Price',
		'AllowPuchaseText' => 'Buyable'
	);

	/**
	 * Standard SS variable.
	 */
	public static $searchable_fields = array(
		"FullName" => array(
			'title' => 'Keyword',
			'field' => 'TextField'
		),
		"Price" => array(
			'title' => 'Price',
			'field' => 'NumericField'
		),
		"InternalItemID" => array(
			'title' => 'Internal Item ID',
			'filter' => 'PartialMatchFilter'
		),
		'AllowPurchase'
	);

	/**
	 * Standard SS variable.
	 */
	public static $default_sort = "\"FullSiteTreeSort\" ASC, \"Sort\" ASC, \"InternalItemID\" ASC, \"Price\" ASC";

	/**
	 * Standard SS variable.
	 */
	public static $singular_name = "Product Variation";
		function i18n_singular_name() { return _t("ProductVariation.PRODUCTVARIATION", "Product Variation");}

	/**
	 * Standard SS variable.
	 */
	public static $plural_name = "Product Variations";
		function i18n_plural_name() { return _t("ProductVariation.PRODUCTVARIATIONS", "Product Variations");}
		public static function get_plural_name(){
			$obj = Singleton("ProductVariation");
			return $obj->i18n_plural_name();
		}

	/**
	 * How is the title build up?
	 *
	 * @var Array
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

	/**
	 * Standard SS method
	 * @return FieldSet
	 */
	function getCMSFields() {
		$product = $this->Product();
		$fields = new FieldSet(new TabSet('Root',
			new Tab('Main',
				new ReadOnlyField('FullName', _t("ProductVariation.FULLNAME", 'Full Name')),
				new NumericField('Price', _t("ProductVariation.PRICE", 'Price')),
				new CheckboxField('AllowPurchase', _t("ProductVariation.ALLOWPURCHASE", 'Allow Purchase ?')),
				new TextField('InternalItemID', _t("ProductVariation.INTERNALITEMID", 'Internal Item ID')),
				new TextField('Description', _t("ProductVariation.DESCRIPTION", "Description (optional)")),
				new ImageField('Image')
			)
		));
		$types = $product->VariationAttributes();
		if($this->ID) {
			$hasBeenSold = $this->HasBeenSold();
			$values = $this->AttributeValues();
			foreach($types as $type) {
				$field = $type->getDropDownField();
				if($field) {
					$value = $values->find('TypeID', $type->ID);
					if($value) {
						$field->setValue($value->ID);
						if($hasBeenSold) {
							$field = $field->performReadonlyTransformation();
							$field->setName("Type{$type->ID}");
						}
					}
					else {
						if($hasBeenSold) {
							$field = new ReadonlyField("Type{$type->ID}", $type->Name, _t("ProductVariation.ALREADYPURCHASED", 'NOT SET (you can not select a value now because it has already been purchased).'));
						}
						else {
							$field->setEmptyString('');
						}
					}
				}
				else {
					$field = new ReadonlyField("Type{$type->ID}", $type->Name, _t("ProductVariation.NOVALUESTOSELECT", 'No values to select'));
				}
				$fields->addFieldToTab('Root.Attributes', $field);
			}
			$fields->addFieldToTab('Root.Orders',
				new ComplexTableField(
					$this,
					'OrderItems',
					'OrderItem',
					array(
						'Order.ID' => '#',
						'Order.Created' => 'When',
						'Quantity' => 'Quantity'
					),
					new FieldSet(),
					"\"BuyableID\" = '".$this->ID."' AND \"BuyableClassName\" = '".$this->ClassName."'",
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

	/**
	 * Use the sort order of the variation attributes to order the attribute values.
	 * This ensures that when VariationAttributes is used for a table header
	 * and AttributeValues are used for the table rows then the columns will be
	 * in the same order.
	 * @return DataObjectSet
	 */
	public function AttributeValuesSorted(){
		$values = parent::AttributeValues();
		$types = $this->Product()->VariationAttributes();
		$result = new DataObjectSet();
		foreach($types as $type) {
			$result->push($values->find('TypeID', $type->ID));
		}
		return $result;
	}


	/**
	 * add requirements for pop-up
	 * TODO: what this is all about?
	 */
	function getRequirementsForPopup() {
		$hasBeenSold = $this->HasBeenSold();
		if(! $this->ID || ! $hasBeenSold) {
			Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
			//Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
			//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
			Requirements::javascript('ecommerce_product_variation/javascript/productvariation.js');
			Requirements::customScript("ProductVariation.set_url('createecommercevariations')", 'CreateEcommerceVariationsField_set_url');
			Requirements::customCSS('#ComplexTableField_Popup_AddForm input.loading {background: url("cms/images/network-save.gif") no-repeat scroll left center #FFFFFF; padding-left: 16px;}');
		}
	}

	/**
	 * standard SS method
	 */
	function populateDefaults() {
		parent::populateDefaults();
		$this->AllowPurchase = 1;
	}

	/**
	 * Puts together a title for the Product Variation
	 * @return String
	 */
	function Title(){return $this->getTitle();}
	function TitleWithHTML(){return $this->getTitle(TRUE);}
	function getTitle($withHTML = false, $noProductTitle = false){
		$this->WithProductTitle = $noProductTitle ? false : true;
		$array = array(
			"Values" => $this->AttributeValues(),
			"Product" => $this->Product(),
			"Description" => $this->Description,
			"InternalItemID" => $this->InternalItemID,
			"Price" => $this->Price
		);
		$html = $this->customise($array)->renderWith("ProductVariationItem");
		if($withHTML) {
			return $html;
		}
		else {
			//@todo: reverse the ampersands, etc...
			return Convert::raw2att(trim(preg_replace( '/\s+/', ' ', strip_tags($html))));
		}
	}

	/**
	 * shorthand
	 */
	function FullDescription(){
		return $this->Title(true, false);
	}

	/**
	 * shorthand
	 */
	function ImgAltTag(){
		return $this->Title(false, false);
	}

	/**
	 * returns YES or NO for the CMS Fields
	 * @return String
	 */
	function AllowPuchaseText() {return $this->getAllowPuchaseText();}
	function getAllowPuchaseText() {
		return $this->AllowPurchase ? 'Yes' : 'No';
	}

	/**
	 * standard SS method
	 * sets the FullName + FullSiteTreeSort of the variation
	 */
	function onBeforeWrite(){
		$this->prepareFullFields();
		parent::onBeforeWrite();
	}

	/**
	 * sets the FullName and FullSiteTreeField to the latest values
	 * This can be useful as you can compare it to the ones saved in the database.
	 * Returns true if the value is different from the one in the database.
	 * @return Boolean
	 */
	public function prepareFullFields(){
		$fullName = "";
		if($this->InternalItemID) {
			$fullName .= $this->InternalItemID.": ";
		}
		$fullName .= $this->getTitle(false, true);
		if($product = $this->MainParentGroup()) {
			$product->prepareFullFields();
			$fullName .= " (".$product->FullName.")";
			$this->FullSiteTreeSort = $product->FullSiteTreeSort.",".$this->Sort;
		}
		$this->FullName = strip_tags($fullName);
		if(($this->dbObject("FullName") != $this->FullName) || ($this->dbObject("FullSiteTreeSort") != $this->FullSiteTreeSort)) {
			return true;
		}
		return false;
	}

	/**
	 * Standard SS Method
	 */
	function onAfterWrite() {
		parent::onAfterWrite();
		if(isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes'])){
			$this->AttributeValues()->setByIDList(array_values($_POST['ProductAttributes']));
		}
		unset($_POST['ProductAttributes']);
		if($product = $this->Product()) {
			$product->writeToStage('Stage');
			$product->publish('Stage', 'Live');
		}
	}

	/**
	 * Standard SS Method
	 * Remove links to Attribute Values
	 */
	function onBeforeDelete() {
		parent::onBeforeDelete();
		$this->AttributeValues()->removeAll();
	}

	/**
	 * this is used by TableListField to access attribute values.
	 * @return DataObject
	 */
	function AttributeProxy(){
		$do = new DataObject();
		if($this->AttributeValues()->exists()){
			foreach($this->AttributeValues() as $value){
				$do->{'Val'.$value->Type()->Name} = $value->Value;
			}
		}
		return $do;
	}






	//GROUPS AND SIBLINGS


	/**
	 * We use this function to make it more universal.
	 * For a buyable, a parent could refer to a ProductGroup OR a Product
	 * @return DataObject | Null
	 **/
	function Parent(){return $this->getParent();}
	function getParent(){
		return $this->Product();
	}

	/**
	 * Returns the direct parent (group) for the product.
	 *
	 * @return Null | DataObject(ProductGroup)
	 **/
	function MainParentGroup(){
		return $this->Product();
	}

	/**
	 * Returns Buybales in the same group
	 * @return Null | DataObjectSet
	 **/
	function Siblings(){
		return DataObject::get("ProductVariation", "\"ProductID\" = ".$this->ProductID);
	}




	//IMAGES
	/**
	 * returns a "BestAvailable" image if the current one is not available
	 * In some cases this is appropriate and in some cases this is not.
	 * For example, consider the following setup
	 * - product A with three variations
	 * - Product A has an image, but the variations have no images
	 * With this scenario, you want to show ONLY the product image
	 * on the product page, but if one of the variations is added to the
	 * cart, then you want to show the product image.
	 * This can be achieved bu using the BestAvailable image.
	 * @return Image | Null
	 */
	public function BestAvailableImage() {
		$image = $this->Image();
		if($image && $image->exists()) {
			return $image;
		}
		if($product = $this->Product()) {
			return $product->BestAvailableImage();
		}
	}

	/**
	 * Returns a link to a default image.
	 * If a default image is set in the site config then this link is returned
	 * Otherwise, a standard link is returned
	 * @return String
	 */
	function DefaultImageLink() {
		$this->EcomConfig()->DefaultImageLink();
	}

	/**
	 * returns the default image of the product
	 * @return Image | Null
	 */
	public function DefaultImage() {
		return $this->Product()->DefaultImage();
	}


	/**
	 * returns a product image for use in templates
	 * e.g. $DummyImage.Width();
	 * @return Product_Image
	 */
	function DummyImage(){
		return new Product_Image();
	}




	// VERSIONING

	/**
	 * Action to return specific version of a product.
	 * This is really useful for sold products where you want to retrieve the actual version that you sold.
	 * @TODO: this is not correct yet, as the versions of product and productvariation are muddled up!
	 * @param HTTPRequest $request
	 */
	function viewversion($request){
		$version = intval($request->param("ID"));
		$product = $this->Product();
		if($product) {
			Director::redirect($product->Link("viewversion/".$product->ID."/".$version."/"));
		}
		else {
			$page = DataObject::get_one("ErrorPage", "ErrorCode = '404'");
			if($page) {
				Director::redirect($page->Link());
				return;
			}
		}
		return array();
	}

	/**
	 * Action to return specific version of a product variation.
	 * This can be any product to enable the retrieval of deleted products.
	 * This is really useful for sold products where you want to retrieve the actual version that you sold.
	 * @param Int $id
	 * @param Int $version
	 * @return DataObject | Null
	 */
	function getVersionOfBuyable($id = 0, $version = 0){
		if(!$id) {
			$id = $this->ID;
		}
		if(!$version) {
			$version = $this->Version;
		}
		return OrderItem::get_version($this->ClassName, $id, $version);
	}


	//ORDER ITEM

	/**
	 * returns the order item associated with the buyable.
	 * ALWAYS returns one, even if there is none in the cart.
	 * Does not write to database.
	 * @return OrderItem (no kidding)
	 **/
	public function OrderItem() {
		//work out the filter
		$filter = "";
		$this->extend('updateItemFilter',$filter);
		//make the item and extend
		$item = ShoppingCart::singleton()->findOrMakeItem($this, $filter);
		$this->extend('updateDummyItem',$item);
		return $item;
	}

	/**
	 *
	 * @var String
	 */
	protected $defaultClassNameForOrderItem = "ProductVariation_OrderItem";


	/**
	 * you can overwrite this function in your buyable items (such as Product)
	 * @return String
	 **/
	public function classNameForOrderItem() {
		$className = $this->defaultClassNameForOrderItem;
		$update = $this->extend("updateClassNameForOrderItem", $className);
		if(is_string($update) && class_exists($update)) {
			$className = $update;
		}
		return $className;
	}

	/**
	 * You can set an alternative class name for order item using this method
	 * @param String $ClassName
	 **/
	public function setAlternativeClassNameForOrderItem($className){
		$this->defaultClassNameForOrderItem = $className;
	}

	/**
	 * When purchasing this buyable, how many decimals can it have?
	 * @return Int
	 */
	function QuantityDecimals(){
		return 0;
	}


	/**
	 * Number of variations sold
	 * @TODO: check if we need to use other class names
	 * @return Int
	 */
	function HasBeenSold() {return $this->getHasBeenSold();}
	function getHasBeenSold() {
		return DB::query("
			SELECT COUNT(*)
			FROM \"OrderItem\"
				INNER JOIN \"OrderAttribute\" ON \"OrderAttribute\".\"ID\" = \"OrderItem\".\"ID\"
			WHERE
				\"BuyableID\" = '".$this->ID."' AND
				\"BuyableClassName\" = '".$this->ClassName."'
			LIMIT 1
			"
		)->value();
	}




	//LINKS

	/**
	 *
	 * @return String
	 */
	function Link($action = null){
		return $this->Product()->Link($action);
	}

	/**
	 * passing on shopping cart links ...is this necessary?? ...why not just pass the cart?
	 * @return String
	 */
	function AddLink() {
		return ShoppingCart_Controller::add_item_link($this->ID, $this->ClassName, $this->linkParameters());
	}

	/**
	 * link use to add (one) to cart
	 *@return String
	 */
	function IncrementLink() {
		//we can do this, because by default add link adds one
		return ShoppingCart_Controller::add_item_link($this->ID, $this->ClassName, $this->linkParameters());
	}

	/**
	 * Link used to remove one from cart
	 * we can do this, because by default remove link removes one
	 * @return String
	 */
	function DecrementLink() {
		return ShoppingCart_Controller::remove_item_link($this->ID, $this->ClassName, $this->linkParameters());
	}

	/**
	 * remove one buyable's orderitem from cart
	 * @return String (Link)
	 */
	function RemoveLink() {
		return ShoppingCart_Controller::remove_item_link($this->ID, $this->ClassName, $this->linkParameters());
	}

	/**
	 * remove all of this buyable's orderitem from cart
	 * @return String (Link)
	 */
	function RemoveAllLink() {
		return ShoppingCart_Controller::remove_all_item_link($this->ID, $this->ClassName, $this->linkParameters());
	}

	/**
	 * remove all of this buyable's orderitem from cart and go through to this buyble to add alternative selection.
	 * @return String (Link)
	 */
	function RemoveAllAndEditLink() {
		return ShoppingCart_Controller::remove_all_item_and_edit_link($this->ID, $this->ClassName, $this->linkParameters());
	}

	/**
	 * set new specific new quantity for buyable's orderitem
	 * @param double
	 * @return String (Link)
	 */
	function SetSpecificQuantityItemLink($quantity) {
		return ShoppingCart_Controller::set_quantity_item_link($this->ID, $this->ClassName, array_merge($this->linkParameters(), array("quantity" => $quantity)));
	}

	/**
	 * @todo: do we still need this?
	 * @return Array
	 **/
	protected function linkParameters(){
		$array = array();
		$this->extend('updateLinkParameters',$array);
		return $array;
	}




	//TEMPLATE STUFF

	/**
	 *
	 * @return boolean
	 */
	public function IsInCart(){
		return ($this->OrderItem() && $this->OrderItem()->Quantity > 0) ? true : false;
	}

	/**
	 *
	 * @return EcomQuantityField
	 */
	public function EcomQuantityField() {
		$obj = new EcomQuantityField($this);
		return $obj;
	}

	/**
	 * returns the instance of EcommerceConfigAjax for use in templates.
	 * In templates, it is used like this:
	 * $EcommerceConfigAjax.TableID
	 *
	 * @return EcommerceConfigAjax
	 **/
	public function AJAXDefinitions() {
		return EcommerceConfigAjax::get_one($this);
	}

	/**
	 * @return EcommerceDBConfig
	 **/
	function EcomConfig() {
		return EcommerceDBConfig::current_ecommerce_db_config();
	}

	/**
	 * Is it a variation?
	 * @return Boolean
	 */
	function IsProductVariation() {
		return true;
	}

	/**
	 * returns the actual price worked out after discounts, currency conversions, etc...
	 * TODO: return as Money
	 * @return Money
	 */
	function CalculatedPrice() {return $this->getCalculatedPrice();}
	function getCalculatedPrice() {
		$price = $this->Price;
		$updatedPrice = $this->extend('updateCalculatedPrice',$price);
		if($updatedPrice !== null) {
			if(is_array($updatedPrice) && count($updatedPrice)) {
				$price = $updatedPrice[0];
			}

		}
		return $price;
	}


	/**
	 * How do we display the price?
	 * @return Money | Null
	 */
	function DisplayPrice() {return $this->getDisplayPrice();}
	function getDisplayPrice() {
		return EcommerceCurrency::display_price($this->CalculatedPrice());
	}



	//CRUD SETTINGS

	/**
	 * Is the product for sale?
	 * @return Boolean
	 */
	function canPurchase($member = null) {
		if($this->EcomConfig()->ShopClosed) {
			return false;
		}
		$allowpurchase = $this->AllowPurchase;
		if(!$allowpurchase) {
			return false;
		}
		if($product = $this->Product()) {
			$allowpurchase = $product->canPurchase($member);
			if(!$allowpurchase) {
				return false;
			}
		}
		$extended = $this->extendedCan('canPurchase', $member);
		if($extended !== null) {
			$allowpurchase = $extended;
		}
		return $allowpurchase;
	}


	/**
	 * standard SS Method
	 * we explicitely set this to give access in the API
	 * @return Boolean
	 */
	function canView($member = null){
		return $this->Parent()->canEdit($member);
	}

	/**
	 * Shop Admins can edit
	 * @return Boolean
	 */
	function canEdit($member = null) {
		if(!$member) {
			$member == Member::currentUser();
		}
		$shopAdminCode = EcommerceConfig::get("EcommerceRole", "admin_permission_code");
		if($member && Permission::checkMember($member, $shopAdminCode)) {
			return true;
		}
		return parent::canEdit($member);
	}

	/**
	 * Standard SS method
	 * @return Boolean
	 */
	public function canDelete($member = null) {
		return $this->canEdit($member);
	}

	/**
	 * Standard SS method
	 * //check if it is in a current cart?
	 * @return Boolean
	 */
	public function canDeleteFromLive($member = null) {
		return $this->canEdit($member);
	}

	/**
	 * Standard SS method
	 * @return Boolean
	 */
	public function canCreate($member = null) {
		return $this->canEdit($member);
	}


}


class ProductVariation_OrderItem extends Product_OrderItem {

	// ProductVariation Access Function
	public function ProductVariation($current = false) {
		return $this->Buyable($current);
	}

	/**
	 * price per item
	 *@return Float
	 **/
	function UnitPrice($recalculate = false) {return $this->getUnitPrice($recalculate);}
	function getUnitPrice($recalculate = false) {
		$unitprice = 0;
		if($this->priceHasBeenFixed() && !$recalculate) {
			return parent::getUnitPrice($recalculate);
		}
		elseif($productVariation = $this->ProductVariation()){
			$unitprice = $productVariation->getCalculatedPrice();
			$this->extend('updateUnitPrice',$unitprice);
		}
		return $unitprice;
	}

	/**
	 *@decription: we return the product name here -
	 * leaving the Table Sub Title for the name of the variation
	 *@return String - title in cart.
	 **/
	public function TableTitle(){return $this->getTableTitle();}
	function getTableTitle() {
		$tableTitle = _t("Product.UNKNOWN", "Unknown Product");
		if($variation = $this->ProductVariation()) {
			if($product = $variation->Product()) {
				$tableTitle = $product->Title;
			}
		}
		$this->extend('updateTableTitle',$tableTitle);
		return $tableTitle;
	}

	/**
	 *@decription: we return the product variation name here
	 * the Table Title will return the name of the Product.
	 *@return String - sub title in cart.
	 **/
	function TableSubTitle() {return $this->getTableSubTitle();}
	function getTableSubTitle() {
		$tableSubTitle = _t("Product.VARIATIONNOTFOUND", "Variation Not Found");
		if($variation = $this->ProductVariation()) {
			if($variation->exists()) {
				$tableSubTitle = $variation->getTitle(true, true);
			}
		}
		$this->extend('updateTableSubTitle',$tableSubTitle);
		return $tableSubTitle;
	}

}
