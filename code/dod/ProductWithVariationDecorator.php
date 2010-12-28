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
		}else{
			return null; //ignore this decorator function if there are no variations
		}
		return $allowpurchase;
	}

	function updateCMSFields(FieldSet &$fields) {
		$fields->addFieldToTab('Root.Content.Variations',new HeaderField("Variations"));
		$fields->addFieldToTab('Root.Content.Variations',$this->owner->getVariationsTable());
		$fields->addFieldToTab('Root.Content.Variations',new HeaderField("Variation Attribute Types"));
		$fields->addFieldToTab('Root.Content.Variations',$this->owner->getVariationAttributesTable());

		if($this->owner->Variations()->exists()){
			$fields->addFieldToTab('Root.Content.Main',new LabelField('variationspriceinstructinos','Price - Because you have one or more variations, the price can be set in the "Variations" tab.'),'Price');
			$fields->removeFieldsFromTab('Root.Content.Main',array('Price','InternalItemID'));
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

	function getVariationAttributesTable(){
		$mmctf = new ManyManyComplexTableField($this->owner,'VariationAttributes','ProductAttributeType');

		return $mmctf;
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

		if(!is_array($attributes)) return null;
		$keyattributes = array_keys($attributes);
		$id = $keyattributes[0];
		$where = "\"ProductID\" = ".$this->owner->ID;
		$join = "";

		foreach($attributes as $typeid => $valueid){
			if(!is_numeric($typeid) || !is_numeric($valueid)) return null; //ids MUST be numeric

			$alias = "A$typeid";
			$where .= " AND $alias.ProductAttributeValueID = $valueid";
			$join .= "INNER JOIN ProductVariation_AttributeValues AS $alias ON ProductVariation.ID = $alias.ProductVariationID ";
		}
		$variation = DataObject::get('ProductVariation',$where,"",$join);

		if($variation)
			return $variation->First();

		return null;

	}

}


class ProductWithVariationDecorator_Controller extends DataObjectDecorator {

	function variationform(){
		//TODO: cache this form so it doesn't need to be regenerated all the time?

		$farray = array();
		$requiredfields = array();
		$attributes = $this->owner->VariationAttributes();

		foreach($this->owner->Variations() as $variation){

		}

		foreach($attributes as $attribute){
			$farray[] = $attribute->getDropDownField("choose $attribute->Label ...",$this->possibleValuesForAttributeType($attribute));//new DropDownField("Attribute_".$attribute->ID,$attribute->Name,);
			$requiredfields[] = "ProductAttributes[$attribute->ID]";
		}

		$fields = new FieldSet($farray);
		$fields->push(new NumericField('Quantity','Quantity',1)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)

		$actions = new FieldSet(
			new FormAction('addVariation', _t("Product.ADDLINK","Add this item to cart"))
		);


		$requiredfields[] = 'Quantity';
		$validator = new RequiredFields($requiredfields);

		$form = new Form($this->owner,'VariationForm',$fields,$actions,$validator);
		return $form;

	}

	function addVariation($data,$form){

		//TODO: save form data to session so selected values are not lost

		if(isset($data['ProductAttributes']) && $variation = $this->owner->getVariationByAttributes($data['ProductAttributes'])){

			$quantity = (isset($data['Quantity']) && is_numeric($data['Quantity'])) ? (int) $data['Quantity'] : 1;

			//add this one to cart
			ShoppingCart::add_buyable($variation,$quantity);

			$form->sessionMessage("Successfully added to cart.","good");

		}else{
			//validation fail
			$form->sessionMessage(_t("ProductVariation.VARIATIONNOTAVAILABLE","That variation combination is not available."),"bad");
		}

		if(!Director::is_ajax()){
			Director::redirectBack();
		}
	}

	function possibleValuesForAttributeType($type){
		if(!is_numeric($type))
			$type = $type->ID;

		if(!$type) return null;

		$where = "TypeID = $type AND ProductVariation.ProductID = ".$this->owner->ID;
		//TODO: is there a better place to obtain these joins?
		$join = "INNER JOIN ProductVariation_AttributeValues ON ProductAttributeValue.ID = ProductVariation_AttributeValues.ProductAttributeValueID" .
				" INNER JOIN ProductVariation ON ProductVariation_AttributeValues.ProductVariationID = ProductVariation.ID";

		$vals = DataObject::get('ProductAttributeValue',$where,$sort = "ProductAttributeValue.Sort,ProductAttributeValue.Value",$join);

		return $vals;
	}



}
