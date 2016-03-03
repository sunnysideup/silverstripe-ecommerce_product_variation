<?php

/**
 *
 * @package ecommerce
 * @subpackage buyables
 */
class ProductVariation extends DataObject implements BuyableModel, EditableEcommerceObject{

	/**
	 * Standard SS variable.
	 */
	private static $api_access = array(
		'view' => array(
			"Title",
			"Description",
			"FullName",
			"AllowPurchase",
			"InternalItemID",
			"NumberSold",
			"Price",
			"Weight",
			"Model",
			"Quantifier",
			"Version"
		)
	);

	/**
	 * Standard SS variable.
	 */
	private static $db = array(
		'InternalItemID' => 'Varchar(30)',
		'Price' => 'Currency',
		'Weight' => 'Float',
		'Model' => 'Varchar(30)',
		'Quantifier' => 'Varchar(30)',
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
	private static $has_one = array(
		'Product' => 'Product',
		'Image' => 'Product_Image'
	);

	/**
	 * Standard SS variable.
	 */
	private static $many_many = array(
		'AttributeValues' => 'ProductAttributeValue'
	);

	/**
	 * Standard SS variable.
	 */
	private static $casting = array(
		'Parent' => 'Product',
		'Title' => 'HTMLText',
		'Link' => 'Text',
		'AllowPurchaseNice' => 'Varchar',
		'CalculatedPrice' => 'Currency',
		'CalculatedPriceAsMoney' => 'Money'
	);

	/**
	 * Standard SS variable.
	 */
	private static $defaults = array(
		"AllowPurchase" => 1
	);

	/**
	 * Standard SS variable.
	 */
	private static $versioning = array(
		'Stage'
	);

	/**
	 * Standard SS variable.
	 */
	private static $extensions = array(
		"Versioned('Stage')"
	);

	/**
	 * Standard SS variable.
	 */
	private static $indexes = array(
		"Sort" => true,
		"FullName" => true,
		"FullSiteTreeSort" => true
	);

	/**
	 * Standard SS variable.
	 */
	private static $field_labels = array(
		"Description" => "Title (optional)"
	);

	/**
	 * Standard SS variable.
	 */
	private static $summary_fields = array(
		'CMSThumbnail' => 'Image',
		'Title' => 'Title',
		'Price' => 'Price',
		'AllowPurchaseNice' => 'For Sale'
	);

	/**
	 * Standard SS variable.
	 */
	private static $searchable_fields = array(
		"FullName" => array(
			'title' => 'Keyword',
			'field' => 'TextField',
			'filter' => 'PartialMatchFilter'
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
	private static $default_sort = "\"AllowPurchase\" DESC, \"FullSiteTreeSort\" ASC, \"Sort\" ASC, \"InternalItemID\" ASC, \"Price\" ASC";

	/**
	 * Standard SS variable.
	 */
	private static $singular_name = "Product Variation";
		function i18n_singular_name() { return _t("ProductVariation.PRODUCTVARIATION", "Product Variation");}

	/**
	 * Standard SS variable.
	 */
	private static $plural_name = "Product Variations";
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
	private static $title_style_option = array(
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
			Config::inst()->update("ProductVariation", "current_style_option_code", $code);
		}
		public static function remove_title_style_option($code) {unset(self::$title_style_option[$code]);}

	private static $current_style_option_code = "default";

	public static function get_current_style_option_array() {
		return self::$title_style_option[Config::inst()->get('ProductVariation', "current_style_option_code")];
	}

	/**
	 * Standard SS method
	 * @return FieldSet
	 */
	function getCMSFields() {
		//backup in case there are no products.
		if(Product::get()->count() == 0) {
			return parent::getCMSFields();
		}
		$product = $this->Product();
		$productCount = Product::get()->count();
		if($productCount > 500) {
			if(class_exists("HasOnePickerField")) {
				$productField = HasOnePickerField::create(
					$this,
					'ProductID',
					_t("ProductVariation.PRODUCT", 'Product'),
					$this->Product(),
					_t("ProductVariation.SELECT_A_PRODUCT", 'Select a Product')
				);
			}
			else {
				user_error("We recommend you install https://github.com/briceburg/silverstripe-pickerfield");
				$productField = ReadonlyField::create(
					"ProductIDTitle",
					_t("ProductVariation.PRODUCT", 'Product'),
					$this->Product() ? $this->Product()->Title : _t("ProductVariation.NO_PRODUCT", 'none')
				);
			}
		}
		else {
			$productField = new DropdownField('ProductID', _t("ProductVariation.PRODUCT", 'Product'), Product::get()->map('ID', 'Title')->toArray());
		}

		$productField->setEmptyString('(Select one)');
		$fields = new FieldList(
			new TabSet('Root',
				new Tab('Main',
					$productField,
					$fullNameLinkField = ReadOnlyField::create('FullNameLink', _t("ProductVariation.FULLNAME", 'Full Name'), "<a href=\"".$this->Link()."\">".$this->FullName."</a>"),
					new NumericField('Price', _t("ProductVariation.PRICE", 'Price')),
					new CheckboxField('AllowPurchase', _t("ProductVariation.ALLOWPURCHASE", 'Allow Purchase ?'))
				),
				new Tab('Details',
					new TextField('InternalItemID', _t("ProductVariation.INTERNALITEMID", 'Internal Item ID')),
					new TextField('Description', _t("ProductVariation.DESCRIPTION", "Description (optional)"))
				),
				new Tab('Image',
					new Product_ProductImageUploadField('Image')
				)
			)
		);
		$fullNameLinkField->dontEscape = true;
		if($this->EcomConfig()->ProductsHaveWeight) {
			$fields->addFieldToTab('Root.Details', new NumericField('Weight', _t('ProductVariation.WEIGHT', 'Weight')));
		}
		if($this->EcomConfig()->ProductsHaveModelNames) {
			$fields->addFieldToTab('Root.Details',new TextField('Model', _t('ProductVariation.MODEL', 'Model')));
		}
		if($this->EcomConfig()->ProductsHaveQuantifiers) {
			$fields->addFieldToTab('Root.Details',new TextField('Quantifier', _t('ProductVariation.QUANTIFIER', 'Quantifier (e.g. per kilo, per month, per dozen, each)')));
		}
		$fields->addFieldToTab('Root.Details',new ReadOnlyField('FullSiteTreeSort', _t('Product.FULLSITETREESORT', 'Full sort index')));
		if($product) {
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
			}
			else {
				foreach($types as $type) {
					$field = $type->getDropDownField();
					$fields->addFieldToTab('Root.Attributes', $field);
				}
			}
		}
		$fields->addFieldToTab(
			'Root.Main',
			new LiteralField(
				'AddToCartLink',
				"<p class=\"message good\"><a href=\"".$this->AddLink()."\">"._t("Product.ADD_TO_CART", "add to cart")."</a></p>"
			)
		);
		$this->extend('updateCMSFields', $fields);
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
	 * Use the sort order of the variation attributes to order the attribute values.
	 * This ensures that when VariationAttributes is used for a table header
	 * and AttributeValues are used for the table rows then the columns will be
	 * in the same order.
	 * @return DataObjectSet
	 */
	public function AttributeValuesSorted(){
		$values = parent::AttributeValues();
		$types = $this->Product()->VariationAttributes();
		$result = new ArrayList();
		foreach($types as $type) {
			$result->push($values->find('TypeID', $type->ID));
		}
		return $result;
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
	function TitleWithHTML($noProductTitle = false){
		return $this->getTitle(TRUE, $noProductTitle);
	}
	function getTitle($withHTML = false, $noProductTitle = false){
		$array = array(
			"Values" => $this->AttributeValues(),
			"Product" => $this->Product(),
			"Description" => $this->Description,
			"InternalItemID" => $this->InternalItemID,
			"Price" => $this->Price,
			"WithProductTitle" => $noProductTitle ? false : true
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
	 * @return String
	 */
	function FullDescription(){
		return $this->Title(true, false);
	}

	/**
	 * shorthand
	 * @return String
	 */
	function ImgAltTag(){
		return $this->Title(false, false);
	}

	/**
	 * returns YES or NO for the CMS Fields
	 * @return String
	 */
	function AllowPurchaseNice() {
		return $this->obj("AllowPurchase")->Nice();
	}

	protected $currentStageOfRequest = "";

	/**
	 * when we save this object, should we save the parent
	 * as well?
	 *
	 * @var Boolean
	 */
	protected $saveParentProduct = false;

	/**
	 * By setting this to TRUE
	 * the parent (product) will be save when this object will be saved.
	 *
	 * @param Boolean $b
	 */
	function setSaveParentProduct($b) {$this->saveParentProduct = $b;}

	/**
	 * standard SS method
	 * sets the FullName + FullSiteTreeSort of the variation
	 */
	function onBeforeWrite(){
		//$this->currentStageOfRequest = Versioned::current_stage();
		//Versioned::set_reading_mode("");
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
		if($this->EcomConfig()->ProductsHaveWeight) {
			if(!$this->Weight) {
				if($product && $product->Weight) {
					$this->Weight = $product->Weight;
				}
			}
		}
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
		//clean up data???
		//todo: what is this for?
		if(isset($_POST['ProductAttributes']) && is_array($_POST['ProductAttributes'])){
			$productAttributesArray = array();
			foreach($_POST['ProductAttributes'] as $key => $value) {
				$productAttributesArray[$key] = intval($value);
			}
			$this->AttributeValues()->setByIDList(array_values($productAttributesArray));
		}
		unset($_POST['ProductAttributes']);
		if($this->saveParentProduct) {
			if($product = $this->Product()) {
				$product->write();
				$product->publish('Stage', 'Live');
			}
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
				$do->{'Val'.$value->Type()->ID} = $value->Value;
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
		return ProductVariation::get()->Filter(array("ProductID" => $this->ProductID));
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
	 * Little hack to show thumbnail in summary fields in modeladmin in CMS.
	 * @return String (HTML = formatted image)
	 */
	function CMSThumbnail(){
		if($image = $this->Image()) {
			if($image->exists()) {
				return $image->Thumbnail();
			}
		}
		return "["._t("product.NOIMAGE", "no image")."]";
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
			$this->redirect($product->Link("viewversion/".$product->ID."/".$version."/"));
		}
		else {
			$page = ErrorPage::get()->Filter(array("ErrorCode" => '404'))->First();
			if($page) {
				$this->redirect($page->Link());
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
		$updatedFilter = $this->extend('updateItemFilter', $filter);
		if($updatedFilter!== null && is_array($updatedFilter) && count($updatedFilter)) {
			$filter = $updatedFilter[0];
		}
		//make the item and extend
		$item = ShoppingCart::singleton()->findOrMakeItem($this, $filter);
		$this->extend('updateDummyItem', $item);
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
		$updatedClassName = $this->extend("updateClassNameForOrderItem", $className);
		if($updatedClassName != null && is_array($updatedClassName) && count($updatedClassName)) {
			$className = $updatedClassName[0];
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
	 * Takes you to the Product and filters
	 * for the provided variation.
	 *
	 * @param String $action - OPTIONAL
	 *
	 * @return String
	 */
	function Link($action = null){
		if(!$action) {
			$action = "filterforvariations/".$this->ID."/";
		}
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
	 *
	 * @return String
	 */
	function AddToCartAndGoToCheckoutLink(){
		$array = $this->linkParameters();
		$array["BackURL"] = urlencode(CheckoutPage::find_link());
		return ShoppingCart_Controller::add_item_link($this->ID, $this->ClassName, $array);
	}

	/**
	 * Here you can add additional information to your product
	 * links such as the AddLink and the RemoveLink.
	 * One useful parameter you can add is the BackURL link.
	 *
	 * Usage would be by means of
	 * 1. decorating product
	 * 2. adding a updateLinkParameters method
	 * 3. adding items to the array.
	 *
	 * You can also extend Product and override this method...
	 *
	 * @return Array
	 **/
	protected function linkParameters(){
		$array = array();
		$extendedArray = $this->extend('updateLinkParameters', $array, $type);
		if($extendedArray !== null && is_array($extendedArray) && count($extendedArray)) {
			foreach($extendedArray as $extendedArrayUpdate) {
				$array += $extendedArrayUpdate;
			}
		}
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
	 * @casted
	 * @return Float
	 */
	function CalculatedPrice() {return $this->getCalculatedPrice();}
	function getCalculatedPrice() {
		$price = $this->Price;
		$updatedPrice = $this->extend('updateCalculatedPrice',$price);
		if($updatedPrice !== null && is_array($updatedPrice) && count($updatedPrice)) {
			$price = $updatedPrice[0];
		}
		return $price;
	}

	/**
	 * How do we display the price?
	 * @return Money
	 */
	function CalculatedPriceAsMoney() {return $this->getCalculatedPriceAsMoney();}
	function getCalculatedPriceAsMoney() {
		return EcommerceCurrency::get_money_object_from_order_currency($this->CalculatedPrice());
	}



	//CRUD SETTINGS

	/**
	 * Is the product for sale?
	 * @return Boolean
	 */
	function canPurchase(Member $member = null, $checkPrice = true) {
		$config = $this->EcomConfig();
		if($config->ShopClosed) {
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
		$price = $this->getCalculatedPrice();
		if($price == 0 && ! $config->AllowFreeProductPurchase) {
			return false;
		}
		$extended = $this->extendedCan('canPurchase', $member);
		if($extended !== null) {
			$allowpurchase = min($extended);
		}
		return $allowpurchase;
	}


	/**
	 * standard SS Method
	 * we explicitely set this to give access in the API
	 * @return Boolean
	 */
	function canView($member = null){
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if($extended !== null) {
			return $extended;
		}
		if($this->ProductID && $this->Product()->exists()) {
			return $this->Product()->canEdit($member);
		}
		return $this->canEdit($member);
	}

	/**
	 * Shop Admins can edit
	 * @return Boolean
	 */
	function canEdit($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if($extended !== null) {
			return $extended;
		}
		if($member && $member->IsShopAdmin()) {
			return true;
		}
		return parent::canEdit($member);
	}

	/**
	 * Standard SS method
	 * @return Boolean
	 */
	public function canDelete($member = null) {
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if($extended !== null) {
			return $extended;
		}
		return $this->canEdit($member);
	}

	/**
	 * Standard SS method
	 * //check if it is in a current cart?
	 * @return Boolean
	 */
	public function canDeleteFromLive($member = null) {
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if($extended !== null) {
			return $extended;
		}
		return $this->canEdit($member);
	}


	/**
	 * Standard SS method
	 * @return Boolean
	 */
	public function canCreate($member = null) {
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if($extended !== null) {
			return $extended;
		}
		return $this->canEdit($member);
	}


	/**
	 * finds similar ("siblings") variations where one
	 * attribute value is NOT the same.
	 *
	 * @return DataList
	 */
	public function MostLikeMe(){
		$idArray = array();
		foreach($this->AttributeValues() as $excludeValue) {
			unset($getAnyArray);
			$getAnyArray = array();
			foreach($this->AttributeValues() as $innerValue) {
				if($excludeValue->ID != $innerValue->ID) {
					$getAnyArray[$innerValue->ID] = $innerValue->ID;
				}
				//find a product variation that has the getAnyArray Values
				$items = ProductVariation::get()
					->innerJoin("ProductVariation_AttributeValues", "\"ProductVariation\".\"ID\" = \"ProductVariationID\" ")
					->filter(array("ProductAttributeValueID" => $getAnyArray, "ProductID" => $this->ProductID))
					->exclude(array("ID" => $this->ID));
				$idArray += $items->map("ID", "ID")->toArray();
			}
		}
		return ProductVariation::get()->filter(array("ID" => $idArray));
	}

}

