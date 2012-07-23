<?php

/**
 * TODO: integrate with Product and Rewrite!
 *
 *
 *
 */



class ProductWithVariationDecorator extends DataObjectDecorator {

	/**
	 * standard SS method
	 *
	 */
	function extraStatics(){
		return array(
			"has_many" => array(
				'Variations' => 'ProductVariation'
			),
			"many_many" => array(
				'VariationAttributes' => 'ProductAttributeType'
			),
			"many_many_extraFields" => array(
				'VariationAttributes' => array(
					'Notes' => 'Varchar(200)'
				)
			)
		);
	}

	/**
	 * standard SS method
	 *
	 */
	function canDelete($member  = null) {
		return (bool)!$this->owner->Variations();
	}

	/**
	 * tells you the number of variations this product has
	 * @return Int
	 */
	function NumberOfVariations() {
		$vars = $this->owner->Variations();
		if($vars) {
			return count($vars);
		}
		return 0;
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
	 *
	 */
	function updateCMSFields(FieldSet &$fields) {
		$fields->addFieldToTab('Root.Content', new Tab(ProductVariation::get_plural_name(),
			new HeaderField(ProductVariation::get_plural_name() . " for {$this->owner->Title}"),
			$this->owner->getVariationsTable(),
			new CreateEcommerceVariations_Field('VariationMaker', '', $this->owner->ID)
		));
		if($this->owner->Variations() && $this->owner->Variations()->count()){
			$fields->addFieldToTab('Root.Content.Main',new LabelField('variationspriceinstructions','Price - Because you have one or more variations, you can vary the price in the "'.ProductVariation::get_plural_name().'" tab. You set the default price here.'), 'Price');
			$fields->addFieldToTab('Root.Content.Details', new LiteralField('UpdateVariationsPrices', "<p class=\"message good\">Click <a href=\"{$this->owner->Link('updatevariationpricefromproduct')}\">here</a> to update all the variations with the price above (SAVE AND PUBLISH THE PRODUCT FIRST).</p>"), 'InternalItemID');
		}
	}


	/**
	 * standard SS method
	 * @return Object (ComplexTableField)
	 */
	function getVariationsTable() {
		$singleton = singleton('ProductVariation');
		$query = $singleton->buildVersionSQL("\"ProductID\" = '{$this->owner->ID}'");
		$variations = $singleton->buildDataObjectSet($query->execute());
		$filter = $variations ? "\"ID\" IN ('" . implode("','", $variations->column('RecordID')) . "') " : "\"ID\" < '0'";
		//$filter = "\"ProductID\" = '{$this->owner->ID}'";

		$summaryfields = array();

		$attributes = $this->owner->VariationAttributes();
		foreach($attributes as $attribute){
			$summaryfields["AttributeProxy.Val$attribute->Name"] = $attribute->Title;
		}

		$summaryfields = array_merge($summaryfields, $singleton->summaryFields());
		unset($summaryfields["Product.Title"]);
		unset($summaryfields["Title"]);

		$tableField = new ComplexTableField(
			$this->owner,
			'Variations',
			'ProductVariation',
			$summaryfields,
			null,
			$filter
		);
		if(method_exists($tableField, 'setRelationAutoSetting')) {
			$tableField->setRelationAutoSetting(true);
		}
		$tableField->setPermissions(array('edit', 'delete', 'export', 'show'));
		return $tableField;
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

	/*
	 * Generates variations based on selected attributes.
	 * TODO: work out how it works!
	 */
	function generateVariationsFromAttributes(ProductAttributeType $attributetype, array $values){

		//TODO: introduce transactions here, in case objects get half made etc

		//if product has variation attribute types
		if(is_array($values)){
			//TODO: get values dataobject set
			$avalues = $attributetype->convertArrayToValues($values);
			$existingvariations = $this->owner->Variations();
			if($existingvariations->exists()){
				//delete old variation, and create new ones - to prevent modification of exising variations
				foreach($existingvariations as $oldvariation){
					$oldvalues = $oldvariation->AttributeValues();
					if($oldvalues) {
						foreach($avalues as $value){
							$newvariation = $oldvariation->duplicate();
							$newvariation->InternalItemID = $this->owner->InternalItemID.'-'.$newvariation->ID;
							$newvariation->AttributeValues()->addMany($oldvalues);
							$newvariation->AttributeValues()->add($value);
							$newvariation->write();
							$existingvariations->add($newvariation);
						}
					}
					$existingvariations->remove($oldvariation);
					$oldvariation->AttributeValues()->removeAll();
					$oldvariation->delete();
					$oldvariation->destroy();
					//TODO: check that old variations actually stick around, as they will be needed for past orders etc
				}
			}
			else {
				if($avalues) {
					foreach($avalues as $value){
						$variation = new ProductVariation();
						$variation->ProductID = $this->owner->ID;
						$variation->Price = $this->owner->Price;
						$variation->write();
						$variation->InternalItemID = $this->owner->InternalItemID.'-'.$variation->ID;
						$variation->AttributeValues()->add($value); //TODO: find or create actual value
						$variation->write();
						$existingvariations->add($variation);
					}
				}
			}
		}
	}

	/**
	 * TO DO: work out how it works...
	 *
	 */

	function generateVariationsFromAttributeValues(array $values) {
		$cpt = 0;
		$variations = array();
		foreach($values as $typeID => $typeValues) {
			$this->owner->addAttributeType($typeID);
			$copyVariations = $variations;
			$variations = array();
			foreach($typeValues as $value) {
				$value = array($value);
				if(count($copyVariations) > 0) {
					foreach($copyVariations as $variation) {
						$variations[] = array_merge($variation, $value);
					}
				}
				else {
					$variations[] = $value;
				}
			}
		}
		foreach($variations as $variation) {
			sort($variation);
			$str = implode(',', $variation);
			$add = true;
			$productVariationIDs = DB::query("SELECT \"ID\" FROM \"ProductVariation\" WHERE \"ProductID\" = '{$this->owner->ID}'")->column();
			if(count($productVariationIDs) > 0) {
				$productVariationIDs = implode(',', $productVariationIDs);
				$variationValues = DB::query("SELECT GROUP_CONCAT(\"ProductAttributeValueID\" ORDER BY \"ProductAttributeValueID\" SEPARATOR ',') FROM \"ProductVariation_AttributeValues\" WHERE \"ProductVariationID\" IN ($productVariationIDs) GROUP BY \"ProductVariationID\"")->column();
				if(in_array($str, $variationValues)) $add = false;
			}
			if($add) {
				$cpt++;
				$newVariation = new ProductVariation(array(
					'ProductID' => $this->owner->ID,
					'Price' => $this->owner->Price
				));
				$newVariation->write();
				$newVariation->AttributeValues()->addMany($variation);
			}
		}
		return $cpt;
	}

	/**
	 * TO DO: work out how it works...
	 *
	 */
	function getVariationByAttributes(array $attributes){
		if(!is_array($attributes) || !count($attributes)) {
			user_error("attributes must be provided as an array of numeric keys and values IDs...", E_USER_NOTICE);
			return null;
		}
		$keyattributes = array_keys($attributes);
		$id = $keyattributes[0];
		$where = "\"ProductID\" = ".$this->owner->ID;
		$join = "";
		foreach($attributes as $typeid => $valueid){
			if(!is_numeric($typeid) || !is_numeric($valueid)) {
				user_error("key and value ID must be numeric", E_USER_NOTICE);
				return null;
			}
			$alias = "A$typeid";
			$where .= " AND \"$alias\".\"ProductAttributeValueID\" = $valueid";
			$join .= " INNER JOIN \"ProductVariation_AttributeValues\" AS \"$alias\" ON \"ProductVariation\".\"ID\" = \"$alias\".\"ProductVariationID\" ";
		}
		$variations = DataObject::get('ProductVariation',$where, $sort = null, $join, $limit = "1");
		if($variations) {
			$variation = $variations->First();
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
	function addAttributeType($attributeTypeObject) {
		$existingTypes = $this->owner->VariationAttributes();
		$existingTypes->add($attributeTypeObject);
	}

	function canRemoveAttributeType($type) {
		$variations = $this->owner->getComponents('Variations', "\"TypeID\" = '$type->ID'", '', "INNER JOIN \"ProductVariation_AttributeValues\" ON \"ProductVariationID\" = \"ProductVariation\".\"ID\" INNER JOIN \"ProductAttributeValue\" ON \"ProductAttributeValue\".\"ID\" = \"ProductAttributeValueID\"");
		return $variations->Count() == 0;
	}

	function removeAttributeType($attributeTypeObject) {
		$existingTypes = $this->owner->VariationAttributes();
		$existingTypes->remove($attributeTypeObject);
	}

	function getArrayOfLinkedProductAttributeTypeIDs() {
		/*
		PROPER WAY - SLOW
		$components = $this->owner->getManyManyComponents('VariationAttributes');
		if($components && $components->count()) {
			return $components->column("ID");
		}
		else {
			return array();
		}
		*/
		$sql = "
			Select \"ProductAttributeTypeID\"
			FROM \"Product_VariationAttributes\"
			WHERE \"ProductID\" = ".$this->owner->ID;
		$data = DB::query($sql);
		$array = $data->keyedColumn();
		return $array;
		/*$array = array();
		if($data && count($data)) {
			foreach($data as $key => $row) {
				$id = $row["ProductAttributeTypeID"];
				$array[$id] = $id;
			}
		}
		if(is_array($array) && count($array) ) {
			foreach($array as $key => $id) {
				if(!DataObject::get_by_id("ProductAttributeType", $id)) {
					//DB::query("DELETE FROM \"ProductVariation_AttributeValues\" WHERE \"ProductAttributeTypeID\" = $id");
					//unset($array[$key]);
				}
			}
		}
		return $array;*/
	}

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
		if(is_array($array) && count($array) ) {
			foreach($array as $key => $id) {
				if(!DataObject::get_by_id("ProductAttributeType", $id)) {
					//DB::query("DELETE FROM \"ProductVariation_AttributeValues\" WHERE \"ProductAttributeValueID\" = $id");
					//unset($array[$key]);
				}
			}
		}
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
	 * tells us if Javascript should be used in validating
	 * the product variation form.
	 * @var Boolean
	 */
	protected static $use_js_validation = true;
		static function set_use_js_validation($b) {self::$use_js_validation = $b;}
		static function get_use_js_validation() {return self::$use_js_validation;}


	/**
	 * tells us if Javascript should be used in validating
	 * the product variation form.
	 * @var String
	 */
	protected static $alternative_validator_class_name = "";
		static function set_alternative_validator_class_name($s) {self::$alternative_validator_class_name = $s;}
		static function get_alternative_validator_class_name() {return self::$alternative_validator_class_name;}

	/**
	 * standard SS variable
	 * @var Array
	 */
	public static $allowed_actions = array(
		'updatevariationpricefromproduct',
		'selectvariation'
	);

	function updatevariationpricefromproduct() {
		$variations = $this->owner->Variations();
		foreach($variations as $variation) {
			$variation->Price = $this->owner->Price;
			$variation->writeToStage('Stage');
		}
		return Director::redirectBack();
	}

	function VariationForm(){
		$farray = array();
		$requiredfields = array();
		$attributes = $this->owner->VariationAttributes();
		if($attributes) {
			foreach($attributes as $attribute){
				$options = $this->possibleValuesForAttributeType($attribute);
				if($options) {
					$farray[] = $attribute->getDropDownField(_t("ProductWithVariationDecorator.CHOOSE","choose")." $attribute->Label "._t("ProductWithVariationDecorator.DOTDOTDOT","..."),$options);//new DropDownField("Attribute_".$attribute->ID,$attribute->Name,);
					$requiredfields[] = "ProductAttributes[$attribute->ID]";
				}
			}
		}
		$fields = new FieldSet($farray);
		$fields->push(new NumericField('Quantity','Quantity',1)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)

		$actions = new FieldSet(
			new FormAction('addVariation', _t("ProductWithVariationDecorator.ADDLINK","Add to cart"))
		);
		$requiredfields[] = 'Quantity';
		$requiredFieldsClass = "RequiredFields";
		if(self::get_alternative_validator_class_name()) {
			$requiredFieldsClass = self::get_alternative_validator_class_name();
		}
		$validator = new $requiredFieldsClass($requiredfields);
		//variation options json generation
		if(self::get_use_js_validation()){ //TODO: make javascript json inclusion optional
			$validator->setJavascriptValidationHandler("none");
			if(self::get_alternative_validator_class_name()) {
				Requirements::javascript(self::get_alternative_validator_class_name());
			}

			$vararray = array();
			if($vars = $this->owner->Variations()){
				foreach($vars as $var){
					if($var->canPurchase()) {
						$vararray[$var->ID] = $var->AttributeValues()->map('ID','ID');
					}
				}
			}

			$json = json_encode($vararray);
			$jsonscript = "var variationsjson = $json";
			Requirements::customScript($jsonscript,'variationsjson');
			Requirements::javascript('ecommerce_product_variation/javascript/variationsvalidator.js');
		}
		Requirements::themedCSS('variationsform');
		$form = new Form($this->owner,'VariationForm',$fields,$actions,$validator);
		return $form;
	}

	function addVariation($data,$form){
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
					ShoppingCart::singleton()->addBuyable($variation,$quantity);
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
			$form->sessionMessage($msg,$status);
			Director::redirectBack();
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
		elseif($type = DataObject::get_by_id("ProductAttributeType", intval($type))) {
			$typeID = $type->ID;
		}
		else {
			return null;
		}
		$where = "\"TypeID\" = $typeID AND \"ProductVariation\".\"ProductID\" = ".$this->owner->ID."  AND \"ProductVariation\".\"AllowPurchase\" = 1";
		//TODO: is there a better place to obtain these joins?
		$join = "INNER JOIN \"ProductVariation_AttributeValues\" ON \"ProductAttributeValue\".\"ID\" = \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\"" .
				" INNER JOIN \"ProductVariation\" ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"";
		//die("Select * FROM ProductAttributeValue $join WHERE $where");
		$vals = DataObject::get('ProductAttributeValue', $where, $sort = "\"ProductAttributeValue\".\"Sort\",\"ProductAttributeValue\".\"Value\"", $join);

		return $vals;
	}


	/**
	 * action!
	 * this action is for selecting product variations
	 *
	 */
	function selectvariation(){
		if(Director::is_ajax()) {
			return $this->owner->renderWith("SelectVariationFromProductGroup");
		}
		else {
			Director::redirect($this->owner->Link());
		}
		return array();
	}

}
