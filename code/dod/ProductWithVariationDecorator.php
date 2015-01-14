<?php

/**
 * TODO: integrate with Product and Rewrite!
 *
 *
 *
 */



class ProductWithVariationDecorator extends DataExtension {

	/**
	 * standard SS Var
	 */
	private static $has_many = array(
		'Variations' => 'ProductVariation'
	);

	/**
	 * standard SS Var
	 */
	private static $many_many = array(
		'VariationAttributes' => 'ProductAttributeType'
	);

	/**
	 * standard SS Var
	 */
	private static $many_many_extraFields = array(
		'VariationAttributes' => array('Notes' => 'Varchar(200)')
	);

	/**
	 * standard SS Var
	 */
	private static $casting = array(
		'LowestVariationPrice' => 'Currency',
		'LowestVariationPriceAsMoney' => 'Money'
	);

	/**
	 * what class do we use for Variations.
	 * This class has to extend ProductVariation.
	 *
	 * @var String
	 */
	protected $classNameOfVariations = "ProductVariation";

	/**
	 * returns what class do we use for Variations.
	 * In general, that is ProductVariation, but you can change it to something else!
	 * @return String
	 */
	public function getClassNameOfVariations(){
		return $this->owner->classNameOfVariations;
	}

	/**
	 * standard SS method
	 * @param Member $member
	 * @return Boolean
	 */
	function canDelete($member  = null) {
		return (bool)!$this->owner->Variations();
	}

	/**
	 * tells you the number of variations this product has
	 * @return Int
	 */
	function NumberOfVariations() {
		return $this->owner->Variations()->count();
	}

	/**
	 * tells you whether the product has any variations
	 * @return Boolean
	 */
	function HasVariations() {
		return $this->owner->NumberOfVariations() ? true : false;
	}

	/**
	 * this method is really useful when you mix Products and Product Variations
	 * That is, in a template, you might have something like $Buyable.Product
	 * With the method below, this will work BOTH if the Buyable is a Product
	 * and a product Varation
	 * @return DataObject (Product)
	 **/
	function Product() {
		return $this->owner;
	}


	/**
	 * tells you whether the current object is a product
	 * seems a bit silly, but it can be useful as other buyables
	 * can return false from this method.
	 * @return Boolean
	 */
	function IsProduct() {
		return true;
	}


	/**
	 * standard SS method
	 */
	function updateCMSFields(FieldList $fields) {
		$tabName = singleton("ProductVariation")->plural_name();
		$fields->addFieldToTab('Root', $tab = new Tab($tabName,
			new HeaderField("$tabName for {$this->owner->Title}"),
			$this->owner->getVariationsTable(),
			new CreateEcommerceVariations_Field('VariationMaker', '', $this->owner->ID)
		));
		$variations = $this->owner->Variations();
		if($variations && $variations->Count()){
			$productVariationName = singleton("ProductVariation")->plural_name();
			$fields->addFieldToTab('Root.Main',new LabelField('variationspriceinstructions','Price - Because you have one or more variations, you can vary the price in the "'.$productVariationName.'" tab. You set the default price here.'), 'Price');
			if(class_exists('DataObjectOneFieldUpdateController')) {
				$linkForPrice = DataObjectOneFieldUpdateController::popup_link('ProductVariation', 'Price', "ProductID = {$this->owner->ID}", '', 'quick update variation prices ...');
				$linkForAllowSale = DataObjectOneFieldUpdateController::popup_link('ProductVariation', 'AllowPurchase', "ProductID = {$this->owner->ID}", '', 'quick update allow purchase settings ...');
				$tab->insertBefore(new LiteralField('PriceUpdateLink', '<p class="message good"> ' . $linkForPrice . '</p>'), 'VariationMaker');
				$tab->insertBefore(new LiteralField('AllowSaleUpdateLink', '<p class="message good"> ' . $linkForAllowSale . '</p>'), 'VariationMaker');
			}
			$link = EcommerceProductVariationTaskDeleteVariations::create_link($this->owner);
			if($link) {
				$tab->insertBefore(
					new LiteralField(
						"DeleteVariations",
						"<p class=\"bad message\"><a href=\"$link\" id=\"DeleteEcommerceVariationsInner\" data-confirm=\"".
								Convert::raw2att(
									_t("Product.ARE_YOU_SURE_YOU_WANT_TO_DELETE_ALL_VARIATIONS",
									"are you sure you want to delete all variations from this product? ")
								).
							"\">"
							._t("Product.DELETE_ALL_VARIATIONS_FROM", "Delete all variations from <i>").$this->owner->Title. "</i>".
						"</a></p>"
					),
					"VariationMaker"
				);
			}

		}
	}

	/**
	 * Field to add and edit product variations
	 * @return GridField
	 */
	function getVariationsTable() {
		if(class_exists("GridFieldEditableColumns")) {
			$oldSummaryFields = Config::inst()->get("ProductVariation", "summary_fields");
			$oldSummaryFields["AllowPurchase"] = $oldSummaryFields["AllowPurchaseNice"];
			unset($oldSummaryFields["AllowPurchaseNice"]);
			Config::inst()->Update("ProductVariation", "summary_fields", $oldSummaryFields);
			$gridFieldConfig = GridFieldConfig::create();
			$gridFieldConfig->addComponent(new GridFieldToolbarHeader());
			$gridFieldConfig->addComponent($sort = new GridFieldSortableHeader());
			$gridFieldConfig->addComponent($filter = new GridFieldFilterHeader());
			$gridFieldConfig->addComponent(new GridFieldEditButton());
			$gridFieldConfig->addComponent($pagination = new GridFieldPaginator(100));
			$gridFieldConfig->addComponent(new GridFieldDetailForm());
			//add the editable columns.
			$gridFieldConfig->addComponent(new GridFieldEditableColumns());
		}
		else {
			$gridFieldConfig = GridFieldConfig_RecordEditor::create();
			$gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
		}
		$source = $this->owner->Variations();
		$types = $this->owner->VariationAttributes();
		if($types && $types->count()) {
			$title = _t("ProductVariation.PLURALNAME", "Product Variations").
					" "._t("ProductVariation.by", "by").": ".
					implode(" "._t("ProductVariation.TIMES", "/")." ", $types->map("ID", "Title")->toArray());
		}
		else {
			$title = _t("ProductVariation.PLURALNAME", "Product Variations");
		}
		$gridField = new GridField("ProductVariations", $title, $source , $gridFieldConfig);
		return $gridField;
	}

	/**
	 * tells us if any of the variations, related to this product,
	 * are currently in the cart.
	 * @return Boolean
	 */
	function VariationIsInCart() {
		$variations = $this->owner->Variations();
		if($variations) {
			foreach($variations as $variation) {
				if($variation->OrderItem() && $variation->OrderItem()->Quantity > 0) {
					return true;
				}
			}
		}
		return false;
	}


	/**
	 * tells us if any of the variations, related to this product,
	 * OR the product itself, is currently in the cart.
	 * @return Boolean
	 */
	function VariationOrProductIsInCart() {
		return ($this->owner->IsInCart() || $this->VariationIsInCart());
	}

	/**
	 * returns lowest cost variation price
	 * for use in FROM XXX.
	 * @return Float
	 */
	public function LowestVariationPrice(){
		$currentPrice = 9999999999999999999;
		$variations = $this->owner->Variations();
		if($variations && $variations->count()) {
			foreach($variations as $variation) {
				if($variation->canPurchase()) {
					$variationPrice = $variation->getCalculatedPrice();
					if($variationPrice < $currentPrice) {
						$currentPrice = $variationPrice;
					}
				}
			}
		}
		if($currentPrice < 9999999999999999999) {
			return $currentPrice;
		}
	}


	/**
	 * @see self::LowestVariationPrice
	 * @return Money
	 */
	public function LowestVariationPriceAsMoney(){
		return EcommerceCurrency::get_money_object_from_order_currency($this->LowestVariationPrice());
	}

	/*
	 * Generates variations based on selected attributes.
	 * TODO: work out how it works!
	 * @param ProductAttributeType $attributetype
	 * @param Array $values
	 */
	function generateVariationsFromAttributes(ProductAttributeType $attributetype, array $values){
		die("this needs to be completed");
		//TODO: introduce transactions here, in case objects get half made etc

		//if product has variation attribute types
		if(is_array($values)){
			//TODO: get values dataobject set
			$arrayValues = $attributetype->convertArrayToValues($values);
			$existingvariations = $this->owner->Variations();
			if($existingvariations->count()){
				//delete old variation, and create new ones - to prevent modification of exising variations
				foreach($existingvariations as $oldvariation){
					$oldvalues = $oldvariation->AttributeValues();
					if($oldvalues) {
						foreach($arrayValues as $attributeValueObject){
							$newvariation = $oldvariation->duplicate();
							$newvariation->InternalItemID = $this->owner->InternalItemID.'-'.$newvariation->ID;
							$newvariation->AttributeValues()->addMany($oldvalues);
							$newvariation->AttributeValues()->add($attributeValueObject);
							$newvariation->write();
							$existingvariations->add($newvariation);
						}
					}
					$existingvariations->remove($oldvariation);
					$oldvariation->AttributeValues()->removeAll();
					$oldvariation->delete();
					$oldvariation->destroy();
					//TODO: check that old variations actually stick around,
					//as they will be needed for past orders etc
				}
			}
			else {
				if($arrayValues) {
					foreach($arrayValues as $attributeValueObject){
						$variation = new ProductVariation();
						$variation->ProductID = $this->owner->ID;
						$variation->Price = $this->owner->Price;
						$variation->write();
						$variation->InternalItemID = $this->owner->InternalItemID.'-'.$variation->ID;
						$variation->AttributeValues()->add($attributeValueObject);
						$variation->write();
						$existingvariations->add($variation);
					}
				}
			}
		}
	}

	/**
	 * The array provided needs to be
	 *     TypeID => arrayOfValueIDs
	 *     TypeID => arrayOfValueIDs
	 *     TypeID => arrayOfValueIDs
	 * you can also make it:
	 *     NameOfAttritbuteType => arrayOfValueIDs
	 * OR:
	 *     NameOfAttritbuteType => arrayOfValueNames
	 * e.g.
	 *     Colour => array(Red, Orange, Blue )
	 *
	 * TypeID is the ID of the ProductAttributeType.  You can also make
	 * it a string in which case it will be found / created
	 * arrayOfValueIDs is an array of IDs of the already created ProductAttributeValue.
	 * You can also make it an array of strings in which case they will be found / created...
	 *
	 * @param array $values
	 * @return Int
	 */
	function generateVariationsFromAttributeValues(array $values) {
		set_time_limit(0);
		$count = 0;
		$valueCombos = array();
		foreach($values as $typeID => $typeValues) {
			$typeObject = $this->owner->addAttributeType($typeID);
			//we use the copy variations to merge all of them together...
			$copyVariations = $valueCombos;
			$valueCombos = array();
			if($typeObject) {
				foreach($typeValues as $valueID) {
					$obj = ProductAttributeValue::get()->byID(intval($valueID));
					if(!$obj) {
						$obj = ProductAttributeValue::find_or_make($typeObject, $valueID);
						$valueID = $obj->write();
					}
					if($valueID = intval($valueID)) {
						$valueID = array($valueID);
						if(count($copyVariations) > 0) {
							foreach($copyVariations as $copyVariation) {
								$valueCombos[] = array_merge($copyVariation, $valueID);
							}
						}
						else {
							$valueCombos[] = $valueID;
						}
					}
				}
			}
		}
		foreach($valueCombos as $valueArray) {
			sort($valueArray);
			$str = implode(',', $valueArray);
			$add = true;
			$productVariationIDs = DB::query("SELECT \"ID\" FROM \"ProductVariation\" WHERE \"ProductID\" = '{$this->owner->ID}'")->column();
			if(count($productVariationIDs) > 0) {
				$productVariationIDs = implode(',', $productVariationIDs);
				$variationValues = DB::query("SELECT GROUP_CONCAT(\"ProductAttributeValueID\" ORDER BY \"ProductAttributeValueID\" SEPARATOR ',') FROM \"ProductVariation_AttributeValues\" WHERE \"ProductVariationID\" IN ($productVariationIDs) GROUP BY \"ProductVariationID\"")->column();
				if(in_array($str, $variationValues)) {
					$add = false;
				}
			}
			if($add) {
				$count++;
				$newVariation = new ProductVariation(array(
					'ProductID' => $this->owner->ID,
					'Price' => $this->owner->Price
				));
				$newVariation->setSaveParentProduct(false);
				$newVariation->write();
				$newVariation->AttributeValues()->addMany($valueArray);
			}
		}
		return $count;
	}

	/**
	 * TO DO: work out how it works...
	 * Get a
	 * @param array $attributes
	 * @return ProductVariation $variation
	 */
	function getVariationByAttributes(array $attributes){
		if(!is_array($attributes) || !count($attributes)) {
			user_error("attributes must be provided as an array of numeric keys and values IDs...", E_USER_NOTICE);
			return null;
		}
		$variations = ProductVariation::get()->filter(
			array("ProductID" => $this->owner->ID)
		);
		$keyattributes = array_keys($attributes);
		$id = $keyattributes[0];
		foreach($attributes as $typeid => $valueid){
			if(!is_numeric($typeid) || !is_numeric($valueid)) {
				user_error("key and value ID must be numeric", E_USER_NOTICE);
				return null;
			}
			$alias = "A$typeid";
			$variations = $variations->where(
				"\"$alias\".\"ProductAttributeValueID\" = $valueid"
			)
			->innerJoin(
				"ProductVariation_AttributeValues",
				"\"ProductVariation\".\"ID\" = \"$alias\".\"ProductVariationID\"",
				 $alias
			);
		}
		if($variation = $variations->First()) {
			return $variation;
		}
		return null;
	}


	function addAttributeValue($attributeValue) {
		die("not completed");
		$existingVariations = $this->owner->Variations();
		$existingVariations->add($attributeTypeObject);
	}

	function removeAttributeValue($attributeValue) {
		die("not completed");
		$existingVariations = $this->owner->Variations();
		$existingVariations->remove($attributeTypeObject);
	}

	/**
	 * add an attribute type to the product
	 *
	 * @param String | Int | ProductAttributeType $attributeTypeObject
	 *
	 * @return ProductAttributeType
	 */
	function addAttributeType($attributeTypeObject) {
		if(intval($attributeTypeObject) === $attributeTypeObject ) {
			$attributeTypeObject = ProductAttributeType::get()->byID(intval($attributeTypeObject));
		}
		if(is_string($attributeTypeObject)) {
			$attributeTypeObject = ProductAttributeType::find_or_make($attributeTypeObject);
		}
		if($attributeTypeObject && $attributeTypeObject instanceof ProductAttributeType) {
			$existingTypes = $this->owner->VariationAttributes();
			$existingTypes->add($attributeTypeObject);
			return $attributeTypeObject;
		}
		else {
			user_error($attributeTypeObject ." is broken");
		}
	}

	/**
	 *
	 * @param ProductAttributeType $attributeTypeObject
	 * @return Boolean
	 */
	function canRemoveAttributeType($attributeTypeObject) {
		$variations = $this->owner->getComponents(
			'Variations',
			"\"TypeID\" = '$attributeTypeObject->ID'");
		$variations = $variations->innerJoin("ProductVariation_AttributeValues", "\"ProductVariationID\" = \"ProductVariation\".\"ID\"");
		$variations = $variations->innerJoin("ProductAttributeValue", "\"ProductAttributeValue\".\"ID\" = \"ProductAttributeValueID\"");
		return $variations->Count() == 0;
	}

	/**
	 *
	 * @param ProductAttributeType $attributeTypeObject
	 */
	function removeAttributeType($attributeTypeObject) {
		$existingTypes = $this->owner->VariationAttributes();
		$existingTypes->remove($attributeTypeObject);
	}

	/**
	 * return an array of IDs of the Attribute Types linked to this product.
	 * @return Array
	 */
	function getArrayOfLinkedProductAttributeTypeIDs() {
		return $this->owner->VariationAttributes()->map("ID", "ID")->toArray();
		//old way...
		$sql = "
			Select \"ProductAttributeTypeID\"
			FROM \"Product_VariationAttributes\"
			WHERE \"ProductID\" = ".$this->owner->ID;
		$data = DB::query($sql);
		$array = $data->keyedColumn();
		return $array;
	}

	/**
	 * return an array of IDs of the Attribute Types linked to this product.
	 * @return Array
	 */
	function getArrayOfLinkedProductAttributeValueIDs() {
		$sql = "
			Select \"ProductAttributeValueID\"
			FROM \"ProductVariation\"
				INNER JOIN \"ProductVariation_AttributeValues\"
					ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
			WHERE \"ProductVariation\".\"ProductID\" = ".$this->owner->ID;
		$data = DB::query($sql);
		$array = $data->keyedColumn();
		return $array;
	}



	function onAfterWrite(){
		//check for the attributes used so that they can be added to VariationAttributes
		parent::onAfterWrite();
		$this->cleaningUpVariationData();
	}

	function onAfterDelete(){
		parent::onAfterDelete();
		$this->cleaningUpVariationData();
	}

	/**
	 * based on the ProductVariations for the products
	 * removing non-existing Product_VariationAttributes
	 * adding existing Product_VariationAttributes
	 * @param Boolean $verbose - output outcome
	 */
	public function cleaningUpVariationData($verbose = false) {
		$changes = false;
		$productID = $this->owner->ID;
		$sql = "
			SELECT \"ProductAttributeValue\".\"TypeID\"
			FROM \"ProductVariation\"
				INNER JOIN \"ProductVariation_AttributeValues\"
					ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
				INNER JOIN \"ProductAttributeValue\"
					ON \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\" = \"ProductAttributeValue\".\"ID\"
			WHERE \"ProductVariation\".\"ProductID\" = ".$productID;
		$arrayOfTypesToKeepForProduct = array();
		$data = DB::query($sql);
		$array = $data->keyedColumn();
		if(is_array($array) && count($array) ) {
			foreach($array as $key => $productAttributeTypeID) {
				$arrayOfTypesToKeepForProduct[$productAttributeTypeID] = $productAttributeTypeID;
			}
		}
		if(count($arrayOfTypesToKeepForProduct)) {
			$deleteCounter = DB::query("
				SELECT COUNT(ID)
				FROM \"Product_VariationAttributes\"
				WHERE
					\"ProductAttributeTypeID\" NOT IN (".implode(",", $arrayOfTypesToKeepForProduct).")
					AND \"ProductID\" = '$productID'
			");
			if($deleteCounter->value()) {
				$changes = true;
				if($verbose) {
					DB::alteration_message("DELETING Attribute Type From ".$this->owner->Title, "deleted");
				}
				DB::query("
					DELETE FROM \"Product_VariationAttributes\"
					WHERE
						\"ProductAttributeTypeID\" NOT IN (".implode(",", $arrayOfTypesToKeepForProduct).")
						AND \"ProductID\" = '$productID'
				");
			}
			foreach($arrayOfTypesToKeepForProduct as $productAttributeTypeID) {
				$addCounter = DB::query("
					SELECT COUNT(ID)
					FROM \"Product_VariationAttributes\"
					WHERE
						\"ProductAttributeTypeID\" = '$productAttributeTypeID'
						AND \"ProductID\" = $productID
				");
				if(!$addCounter->value()) {
					$changes = true;
					if($verbose) {
						DB::alteration_message("ADDING Attribute Type From ".$this->owner->Title, "created");
					}
					DB::query("
						INSERT INTO \"Product_VariationAttributes\" (
							\"ProductID\" ,
							\"ProductAttributeTypeID\"
						)
						VALUES (
							'$productID', '$productAttributeTypeID'
						)
					");
				}
			}
		}
		else {
			$deleteAllCounter = DB::query("
				SELECT COUNT(ID)
				FROM \"Product_VariationAttributes\"
				WHERE \"ProductID\" = '$productID'
			");
			if($deleteAllCounter->value()) {
				$changes = true;
				if($verbose) {
					DB::alteration_message("DELETING ALL Attribute Types From ".$this->owner->Title, "deleted");
				}
				DB::query("
					DELETE FROM \"Product_VariationAttributes\"
					WHERE \"ProductID\" = '$productID'
				");
			}
		}
		return $changes;
	}
}

class ProductWithVariationDecorator_Controller extends Extension {

	/**
	 * standard SS Var
	 */
	private static $allowed_actions = array(
		"selectvariation",
		"VariationForm",
		'filterforvariations'
	);

	/**
	 * tells us if Javascript should be used in validating
	 * the product variation form.
	 * @var Boolean
	 */
	private static $use_js_validation = true;

	/**
	 * Alternative class name for Validation of Form
	 * in PHP
	 * @var String
	 */
	private static $alternative_validator_class_name = "";

	/**
	 * array of IDs of variations that should be shown
	 * if count(array) == 0 then all of them will be shown
	 * @var Array
	 */
	protected $variationFilter = array();

	/**
	 * return the variations and aplu filter if one has been set.
	 * @return DataList
	 */
	function Variations(){
		$variations = $this->owner->dataRecord->Variations();
		if($this->variationFilter && count($this->variationFilter)) {
			$variations = $variations->filter(array("ID" => $this->variationFilter));
		}
		return $variations;
	}

	/**
	 * returns a form of the product if it can be purchased.
	 *
	 * @return Form | NULL
	 */
	function VariationForm(){
		if($this->owner->canPurchase(null, true)) {
			$farray = array();
			$requiredfields = array();
			$attributes = $this->owner->VariationAttributes();
			if($attributes) {
				foreach($attributes as $attribute){
					$options = $this->possibleValuesForAttributeType($attribute);
					if($options && $options->count()) {
						$farray[] = $attribute->getDropDownField(_t("ProductWithVariationDecorator.CHOOSE","choose")." $attribute->Label "._t("ProductWithVariationDecorator.DOTDOTDOT","..."),$options);//new DropDownField("Attribute_".$attribute->ID,$attribute->Name,);
						$requiredfields[] = "ProductAttributes[$attribute->ID]";
					}
				}
			}
			$fields = new FieldList($farray);
			$fields->push(new NumericField('Quantity','Quantity',1)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)

			$actions = new FieldList(
				new FormAction('addVariation', _t("ProductWithVariationDecorator.ADDLINK","Add to cart"))
			);
			$requiredfields[] = 'Quantity';
			$requiredFieldsClass = "RequiredFields";
			if(Config::inst()->get('ProductWithVariationDecorator_Controller', 'alternative_validator_class_name')) {
				$requiredFieldsClass = Config::inst()->get('ProductWithVariationDecorator_Controller', 'alternative_validator_class_name');
			}
			$validator = new $requiredFieldsClass($requiredfields);
			//variation options json generation
			if(Config::inst()->get('ProductWithVariationDecorator_Controller', 'use_js_validation')){ //TODO: make javascript json inclusion optional
				if(Config::inst()->get('ProductWithVariationDecorator_Controller', 'alternative_validator_class_name')) {
					Requirements::javascript(Config::inst()->get('ProductWithVariationDecorator_Controller', 'alternative_validator_class_name'));
				}
				//todo: change JS so that we dont have to add this default array.
				$varArray = array(-1 => -1);
				if($vars = $this->Variations()){
					foreach($vars as $var){
						if($var->canPurchase()) {
							$varArray[$var->ID] = $var->AttributeValues()->map('ID','ID')->toArray();
						}
					}
				}
				$json = json_encode($varArray);
				$jsonscript = "var variationsjson = $json";
				Requirements::customScript($jsonscript,'variationsjson');
				Requirements::javascript('ecommerce_product_variation/javascript/variationsvalidator.js');
			}
			Requirements::themedCSS('variationsform', "ecommerce_product_variation");
			$form = new Form($this->owner,'VariationForm',$fields,$actions,$validator);
			return $form;
		}
	}

	function addVariation($data, $form){
		//TODO: save form data to session so selected values are not lost
		if(isset($data['ProductAttributes'])){
			$data['ProductAttributes'] = Convert::raw2sql($data['ProductAttributes']);
			$variation = $this->owner->getVariationByAttributes($data['ProductAttributes']);
			if($variation) {
				if($variation->canPurchase()) {
					$quantity = round($data['Quantity'], $variation->QuantityDecimals());
					if(!$quantity) {
						$quantity = 1;
					}
					ShoppingCart::singleton()->addBuyable($variation,$quantity, $form);
					if($variation->IsInCart()) {
						$msg = _t("ProductWithVariationDecorator.SUCCESSFULLYADDED","Added to cart.");
						$status = "good";
					}
					else {
						$msg = _t("ProductWithVariationDecorator.NOTSUCCESSFULLYADDED","Not added to cart.");
						$status = "bad";
					}
				}
				else{
					$msg = _t("ProductWithVariationDecorator.VARIATIONNOTAVAILABLE","That option is not available.");
					$status = "bad";
				}
			}
			else {
				$msg = _t("ProductWithVariationDecorator.VARIATIONNOTAVAILABLE","That option is not available.");
				$status = "bad";
			}
		}
		else {
			$msg = _t("ProductWithVariationDecorator.VARIATIONNOTFOUND","The item(s) you are looking for are not available.");
			$status = "bad";
		}
		if(Director::is_ajax()){
			return ShoppingCart::singleton()->setMessageAndReturn($msg, $status);
		}
		else {
			ShoppingCart::singleton()->setMessageAndReturn($msg, $status, $form);
			$this->owner->redirectBack();
		}
	}

	function VariationsPerVariationType() {
		$types = $this->owner->VariationAttributes();
		if($types) {
			foreach($types as $type) {
				$type->Variations = $this->possibleValuesForAttributeType($type);
			}
		}
		return $types;
	}


	function possibleValuesForAttributeType($type){
		if($type instanceOf ProductAttributeType) {
			$typeID = $type->ID;
		}
		elseif($type = ProductAttributeType::get()->byID(intval($type))) {
			$typeID = $type->ID;
		}
		else {
			return null;
		}
		$vals = ProductAttributeValue::get()
			->where(
				"\"TypeID\" = $typeID AND \"ProductVariation\".\"ProductID\" = ".$this->owner->ID."  AND \"ProductVariation\".\"AllowPurchase\" = 1"
			)
			->sort(
				array(
					"ProductAttributeValue.Sort" => "ASC",
					"ProductAttributeValue.Value" => "ASC"
				)
			)
			->innerJoin(
				"ProductVariation_AttributeValues",
				"\"ProductAttributeValue\".\"ID\" = \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\""
			)
			->innerJoin(
				"ProductVariation",
				"\"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\""
			);
		return $vals;
	}



	/**
	 * action!
	 * this action is for selecting product variations
	 * @param HTTPRequest $request
	 */
	function selectvariation($request){
		if(Director::is_ajax()) {
			return $this->owner->renderWith("SelectVariationFromProductGroup");
		}
		else {
			$this->owner->redirect($this->owner->Link());
		}
		return array();
	}

	/**
	 * You can specificy one or MORE
	 *
	 * @param HTTPRequest $request
	 */
	function filterforvariations($request){
		$array = explode(",", $request->param("ID"));
		if(is_array($array) && count($array)) {
			$this->variationFilter = array_map("intval", $array);
		}
		return array();
	}


}
