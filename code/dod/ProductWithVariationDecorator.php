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

}
