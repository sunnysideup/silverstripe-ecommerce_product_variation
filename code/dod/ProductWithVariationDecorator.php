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

	function updateCMSFields(FieldSet &$fields) {
		$tabName = 'Root.Content.'.ProductVariation::get_plural_name();
		$fields->addFieldToTab($tabName, new CreateEcommerceVariations_Field("VariationMaker", "", $this->owner->ID));
		$fields->addFieldToTab($tabName,new HeaderField(ProductVariation::get_plural_name().' for '.$this->owner->Title));
		$fields->addFieldToTab($tabName,$this->owner->getVariationsTable());
		if($this->owner->Variations()->exists()){
			$fields->addFieldToTab('Root.Content.Main',new LabelField('variationspriceinstructinos','Price - Because you have one or more variations, the price can be set in the "Variations" tab.'),'Price');
			//$fields->removeFieldsFromTab('Root.Content.Main',array('Price','InternalItemID'));
		}
	}

	function getVariationsTable() {
		$singleton = singleton('ProductVariation');
		$query = $singleton->buildVersionSQL("\"ProductID\" = '{$this->owner->ID}'");
		$variations = $singleton->buildDataObjectSet($query->execute());
		$filter = $variations ? "\"ID\" IN ('" . implode("','", $variations->column('RecordID')) . "')" : "\"ID\" < '0'";
		//$filter = "\"ProductID\" = '{$this->owner->ID}'";

		$summaryfields= $singleton->summaryFields();

		if($this->owner->VariationAttributes()->exists())
			foreach($this->owner->VariationAttributes() as $attribute){
				$summaryfields["AttributeProxy.Val".$attribute->Name] = $attribute->Title;
			}

		$tableField = new HasManyComplexTableField(
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
		$array = array();
		if($data && count($data)) {
			foreach($data as $key => $row) {
				$id = $row["ProductAttributeTypeID"];
				$array[$id] = $id;
			}
		}
		return $array;
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

	function VariationForm(){
		//TODO: cache this form so it doesn't need to be regenerated all the time?

		$farray = array();
		$requiredfields = array();
		$attributes = $this->owner->VariationAttributes();
		if($attributes) {
			foreach($attributes as $attribute){
				$farray[] = $attribute->getDropDownField(_t("ProductWithVariationDecorator.CHOOSE","choose")."$attribute->Label ...",$this->possibleValuesForAttributeType($attribute));//new DropDownField("Attribute_".$attribute->ID,$attribute->Name,);
				$requiredfields[] = "ProductAttributes[$attribute->ID]";
			}
		}
		$fields = new FieldSet($farray);
		$fields->push(new NumericField('Quantity','Quantity',1)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)

		$actions = new FieldSet(
			new FormAction('addVariation', _t("ProductWithVariationDecorator.ADDLINK","Add this item to cart"))
		);


		$requiredfields[] = 'Quantity';
		$validator = new RequiredFields($requiredfields);

		$form = new Form($this->owner,'VariationForm',$fields,$actions,$validator);
		return $form;

	}

	function addVariation($data,$form){
		//TODO: save form data to session so selected values are not lost
		$data['ProductAttributes'] = Convert::raw2sql($data['ProductAttributes']);
		if(isset($data['ProductAttributes']) && $variation = $this->owner->getVariationByAttributes($data['ProductAttributes'])){
			$quantity = intval($data['Quantity']);
			if(!$quantity) {
				$quantity = 1;
			}
			ShoppingCart::add_buyable($variation,$quantity);
			$form->sessionMessage(_t("ProductWithVariationDecorator.SUCCESSFULLYADDED","Successfully added to cart."),"good");
		}
		else{
			$form->sessionMessage(_t("ProductWithVariationDecorator.VARIATIONNOTAVAILABLE","That variation combination is not available."),"bad");
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

  static $many_many = array("Categories" => "Category");



}
