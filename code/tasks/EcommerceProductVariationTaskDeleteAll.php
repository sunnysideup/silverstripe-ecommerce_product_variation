<?php

class EcommerceProductVariationTaskDeleteAll extends BuildTask{

	protected $title = "Deletes all the variations and associated data";

	protected $description = "Deletes ALL variations and all associated data, careful.";

	protected $tableArray = array(
		"ProductVariation",
		"ProductVariation_AttributeValues",
		"Product_VariationAttributes",
		"ProductAttributeType",
		"ProductAttributeValue"
	);

	function run($request){
		$productVariationArrayID = array();
		if(empty($_GET["live"])) {
			$live = false;
		}
		else {
			$live = intval($_GET["live"]) == 1 ? true : false;
		}
		if($live) {
			DB::alteration_message("this is a live task", "deleted");
		}
		else {
			DB::alteration_message("this is a test only. If you add a live=1 get variable then you can make it for real ;-)", "created");
		}
		foreach($this->tableArray as $table) {
			$sql = "DELETE FROM \"$table\"";
			DB::alteration_message("<pre>DELETING FROM $table: <br /><br />".$sql."</pre>");
			if($live) {
				DB::query($sql);
			}
		}
		DB::alteration_message(" **** COMPLETED **** ", "created");
	}

}

class EcommerceProductVariationTaskDeleteVariations_EXT extends Extension {

	private static $allowed_actions = array(
		"ecommerceproductvariationtaskdeletevariations" => true
	);

	//NOTE THAT updateEcommerceDevMenuConfig adds to Config options
	//but you can als have: updateEcommerceDevMenuDebugActions
	function updateEcommerceDevMenuRegularMaintenance($buildTasks){
		$buildTasks[] = "ecommerceproductvariationtaskdeletevariations";
		return $buildTasks;
	}

	function ecommerceproductvariationtaskdeletevariations($request){
		$this->owner->runTask("EcommerceProductVariationTaskDeleteVariations", $request);
	}

}
