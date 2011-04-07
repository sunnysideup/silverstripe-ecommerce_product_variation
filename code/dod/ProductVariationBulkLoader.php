<?php

/**
 * Variations-specific bulk loading.
 * 
 * Variations can be specified in a "Variation" column this format:
 * Type:value,value,value
 * eg: Color: red, green, blue , yellow
 * up to 6 other variation columns can be specified by adding a number to the end, eg Variation2,$Variation3
 */
class ProductVariationBulkLoader extends Extension{
		
	function updateColumnMap(&$columnmap){		
		
		$columnmap['Variation'] = '->processVariation';
		$columnmap['Variation1'] = '->processVariation1';
		$columnmap['Variation2'] = '->processVariation2';
		$columnmap['Variation3'] = '->processVariation3';
		$columnmap['Variation4'] = '->processVariation4';
		$columnmap['Variation5'] = '->processVariation5';
		$columnmap['Variation6'] = '->processVariation6';
		
		$columnmap['VariationID'] = '->variationRow';
		$columnmap['Variation ID'] = '->variationRow';
		$columnmap['SubID'] = '->variationRow';
		$columnmap['Sub ID'] = '->variationRow';
		
	}
	
	function processVariation($obj, $val, $record){
			
		if(isset($record['->variationRow']) && $record['->variationRow'] != "") return; //don't use this technique for variation rows
		
		$parts = explode(":",$val);
		if(count($parts) == 2){
			$attributetype = trim($parts[0]);
			$attributevalues = explode(",",$parts[1]);
			
			//get rid of empty values
			foreach($attributevalues as $key => $value){
				if(!$value || trim($value) == ""){
					unset($attributevalues[$key]);
				}
			}
			
			if(count($attributevalues) >= 1){
				$attributetype = ProductAttributeType::find_or_make($attributetype);
				foreach($attributevalues as $key => $value){
					$val = trim($value);
					if($val != "" && $val != null)
						$attributevalues[$key] = $val; //remove outside spaces from values
				}
				$attributetype->addValues($attributevalues);
				$obj->VariationAttributes()->add($attributetype);
				//only generate variations if none exist yet
				if(!$obj->Variations()->exists() || $obj->WeAreBuildingVariations){
					//either start new variations, or multiply existing ones by new variations
					$obj->generateVariationsFromAttributes($attributetype,$attributevalues);
					$obj->WeAreBuildingVariations = true;
				}
			}
		}
		
		
	}
	//work around until I can figure out how to allow calling processVariation multiple times
	function processVariation1($obj, $val, $record){
		$this->processVariation($obj, $val, $record);
	}
	function processVariation2($obj, $val, $record){
		$this->processVariation($obj, $val, $record);
	}
	function processVariation3($obj, $val, $record){
		$this->processVariation($obj, $val, $record);
	}
	function processVariation4($obj, $val, $record){
		$this->processVariation($obj, $val, $record);
	}
	function processVariation5($obj, $val, $record){
		$this->processVariation($obj, $val, $record);
	}
	function processVariation6($obj, $val, $record){
		$this->processVariation($obj, $val, $record);
	}
	
	function variationRow($obj, $val, $record){
		
		$obj->write(); //make sure product is in DB
		
		//find existing variation
		$variation = DataObject::get_one('ProductVariation',"InternalItemID = '$val'");
		if(!$variation){
			$variation = new ProductVariation();
			$variation->InternalItemID = $val;
			$variation->ProductID = $obj->ID; //link to product
			$variation->write();
		}
				
		$varcols = array(
			'->processVariation',
			'->processVariation1',
			'->processVariation2',
			'->processVariation3',
			'->processVariation4',
			'->processVariation5',
			'->processVariation6',
		);
		
		foreach($varcols as $col){
			if(isset($record[$col])){
				$parts = explode(":",$record[$col]);
				if(count($parts) == 2){
					
					$attributetype = trim($parts[0]);
					$attributevalues = explode(",",$parts[1]);
					//get rid of empty values
					foreach($attributevalues as $key => $value){
						if(!$value || trim($value) == ""){
							unset($attributevalues[$key]);
						}
					}
					
					if(count($attributevalues) == 1){
				
						$attributetype = ProductAttributeType::find_or_make($attributetype);
						foreach($attributevalues as $key => $value){
							$val = trim($value);
							if($val != "" && $val != null)
								$attributevalues[$key] = $val; //remove outside spaces from values
						}
						
						$attributetype->addValues($attributevalues); //create and add values to attribute type
						$obj->VariationAttributes()->add($attributetype); //add variation attribute type to product
						
						//TODO: if existing variation, then remove current values
						//record vairation attribute values (variation1, 2 etc)	
						foreach($attributetype->convertArrayToValues($attributevalues) as $value){
							$variation->AttributeValues()->add($value);
							break;								
						}
					}
				}
			}
		}
		
		//copy db values into variation (InternalItemID, Price, Stock, etc) ...there will be unknowns from extensions.
		$dbfields = $variation->db();
		foreach($record as $field => $value){
			if(isset($dbfields[$field])){
				$variation->$field = $value;
			}
		}

		$variation->write();
	}
	
}

?>
