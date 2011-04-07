<?php


class ProductWithVariationDecorator extends DataObjectDecorator {

	function extraStatics(){
		return array(
			"has_many" => array(
				'Variations' => 'ProductVariation'
			),
			"many_many" => array(
				'VariationAttributes' => 'ProductAttributeType'
			)
		);
	}

	function canPurchase($member = null) {
		$allowpurchase = false;
		if($this->owner->Variations()->exists()){
			foreach($this->owner->Variations() as $variation){
				if($variation->canPurchase()){
					$allowpurchase = true;
					break;
				}
			}
		}
		else{
			return null; //ignore this decorator function if there are no variations
		}
		return $allowpurchase;
	}

	function NumberOfVariations() {
		$vars = $this->owner->Variations();
		if($vars) {
			return count($vars);
		}
		return 0;
	}

	function HasVariations() {
		return $this->NumberOfVariations() ? true : false;
	}

	function updateCMSFields(FieldSet &$fields) {
		$fields->addFieldToTab('Root.Content', new Tab(ProductVariation::get_plural_name(),
			new HeaderField(ProductVariation::get_plural_name() . " for {$this->owner->Title}"),
			$this->owner->getVariationsTable(),
			new CreateEcommerceVariations_Field('VariationMaker', '', $this->owner->ID)
		));
		if($this->owner->Variations() && $this->owner->Variations()->count()){
			$fields->addFieldToTab('Root.Content.Main',new LabelField('variationspriceinstructions','Price - Because you have one or more variations, you can vary the price in the "'.ProductVariation::get_plural_name().'" tab. You set the default price here.'), 'Price');
			$fields->addFieldToTab('Root.Content.Details', new LiteralField('UpdateVariations', "<p class=\"message good\">Click <a href=\"{$this->owner->Link('updatevariationpricefromproduct')}\">here</a> to update all the variations with the price above.</p>"), 'InternalItemID');
		}
	}

	function getVariationsTable() {
		$singleton = singleton('ProductVariation');
		$query = $singleton->buildVersionSQL("\"ProductID\" = '{$this->owner->ID}'");
		$variations = $singleton->buildDataObjectSet($query->execute());
		$filter = $variations ? "\"ID\" IN ('" . implode("','", $variations->column('RecordID')) . "')" : "\"ID\" < '0'";
		//$filter = "\"ProductID\" = '{$this->owner->ID}'";

		$summaryfields = array();

		$attributes = $this->owner->VariationAttributes();
		foreach($attributes as $attribute){
			$summaryfields["AttributeProxy.Val$attribute->Name"] = $attribute->Title;
		}

		$summaryfields = array_merge($summaryfields, $singleton->summaryFields());

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
		return $tableField;
	}

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

	function VariationOrProductIsInCart() {
		return ($this->owner->IsInCart() || $this->VariationIsInCart());
	}

	/*
	 * Generates variations based on selected attributes.
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

	function getVariationByAttributes(array $attributes){
		if(!is_array($attributes) || !count($attributes)) {
			return null;
		}
		$keyattributes = array_keys($attributes);
		$id = $keyattributes[0];
		$where = "\"ProductID\" = ".$this->owner->ID;
		$join = "";
		foreach($attributes as $typeid => $valueid){
			if(!is_numeric($typeid) || !is_numeric($valueid)) {
				return null; //ids MUST be numeric
			}
			$alias = "A$typeid";
			$where .= " AND \"$alias\".\"ProductAttributeValueID\" = $valueid";
			$join .= " INNER JOIN \"ProductVariation_AttributeValues\" AS \"$alias\" ON \"ProductVariation\".\"ID\" = \"$alias\".\"ProductVariationID\" ";
		}
		$variations = DataObject::get('ProductVariation',$where, $sort = null, $join);
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
		return $data->keyedColumn();
		/*$array = array();
		if($data && count($data)) {
			foreach($data as $key => $row) {
				$id = $row["ProductAttributeTypeID"];
				$array[$id] = $id;
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
		$array = array();
		if($data && count($data)) {
			foreach($data as $key => $row) {
				$id = $row["ProductAttributeValueID"];
				$array[$id] = $id;
			}
		}
		return $array;
	}


}

class ProductWithVariationDecorator_Controller extends DataObjectDecorator {

	protected static $use_js_validation = true;
		static function set_use_js_validation($b) {self::$use_js_validation = $b;}
		static function get_use_js_validation() {return self::$use_js_validation;}

	protected static $alternative_validator_class_name = "";
		static function set_alternative_validator_class_name($s) {self::$alternative_validator_class_name = $s;}
		static function get_alternative_validator_class_name() {return self::$alternative_validator_class_name;}

	function VariationForm(){

		$farray = array();

		$requiredfields = array();
		$attributes = $this->owner->VariationAttributes();
		if($attributes) {
			foreach($attributes as $attribute){
				$farray[] = $attribute->getDropDownField(_t("ProductWithVariationDecorator.CHOOSE","choose")." $attribute->Label "._t("ProductWithVariationDecorator.DOTDOTDOT","..."),$this->possibleValuesForAttributeType($attribute));//new DropDownField("Attribute_".$attribute->ID,$attribute->Name,);
				if(self::get_use_js_validation()) {
					$requiredfields[] = "ProductAttributes[$attribute->ID]";
				}
			}
		}
		$fields = new FieldSet($farray);
		$fields->push(new NumericField('Quantity','Quantity',1)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)

		//variation options json generation
		if(true){ //TODO: make javascript json inclusion optional

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
			if(self::get_alternative_validator_class_name()) {
				Requirements::javascript(self::get_alternative_validator_class_name());
			}
			Requirements::themedCSS('variationsform');
		}

		$actions = new FieldSet(
			new FormAction('addVariation', _t("ProductWithVariationDecorator.ADDLINK","Add this item to cart"))
		);


		$requiredfields[] = 'Quantity';
		if(self::get_alternative_validator_class_name()) {
			$requiredFieldsClass = self::get_alternative_validator_class_name();
		}
		else {
			$requiredFieldsClass = "RequiredFields";
		}
		$validator = new $requiredFieldsClass($requiredfields);
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
					$quantity = intval($data['Quantity']);
					if(!$quantity) {
						$quantity = 1;
					}
					ShoppingCart::add_buyable($variation,$quantity);
					$form->sessionMessage(_t("ProductWithVariationDecorator.SUCCESSFULLYADDED","Successfully added to cart."),"good");
				}
				else{
					$form->sessionMessage(_t("ProductWithVariationDecorator.VARIATIONNOTAVAILABLE","That option is not available."),"bad");
				}
			}
			else {
				$form->sessionMessage(_t("ProductWithVariationDecorator.VARIATIONNOTAVAILABLE","That option is not available."),"bad");
			}
		}
		else {
			$form->sessionMessage(_t("ProductWithVariationDecorator.VARIATIONNOTFOUND","The items you are looking for is not found."),"bad");
		}
		if(!Director::is_ajax()){
			Director::redirectBack();
		}
	}

	function possibleValuesForAttributeType($type){
		if(!is_numeric($type)) {
			$type = $type->ID;
		}
		if(!$type) {
			return null;
		}
		$where = "\"TypeID\" = $type AND \"ProductVariation\".\"ProductID\" = ".$this->owner->ID;
		//TODO: is there a better place to obtain these joins?
		$join = "INNER JOIN \"ProductVariation_AttributeValues\" ON \"ProductAttributeValue\".\"ID\" = \"ProductVariation_AttributeValues\".\"ProductAttributeValueID\"" .
				" INNER JOIN \"ProductVariation\" ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"";

		$vals = DataObject::get('ProductAttributeValue',$where,$sort = "\"ProductAttributeValue\".\"Sort\",\"ProductAttributeValue\".\"Value\"",$join);

		return $vals;
	}

	public static $allowed_actions = array('updatevariationpricefromproduct');

	function updatevariationpricefromproduct() {
		$variations = $this->owner->Variations();
		foreach($variations as $variation) {
			$variation->Price = $this->owner->Price;
			$variation->writeToStage('Stage');
		}
		return Director::redirectBack();
	}
}
