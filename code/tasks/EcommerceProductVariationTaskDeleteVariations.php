<?php

class EcommerceProductVariationTaskDeleteVariations extends BuildTask{

	protected $title = "Deletes all the variations and associated data from a product";

	protected $description = "CAREFUL: the developer will need to supply the ID as a get variable (?productid=XXX) as well as a test / live flag (?live=1, default is test) for the product and variations will be deleted without keeping a history.";

	function run($request){
		$productVariationArrayID = array();
		if(empty($_GET["productid"])) {
			$productID = 0;
		}
		elseif($_GET["productid"] == 'all') {
			$productID = -1;
		}
		else {
			$productID = intval($_GET["productid"]);
		}
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
		if($productID == -1) {
			$products = Product::get();
		}
		else {
			$products = null;
			$product = Product::get()->byID($productID);
			if($product) {
				$products= new ArrayList();
				$products->push($product);
			}
		}
		if($products && $products->count()) {
			foreach($products as $product) {
				$productID = $product->ID;
				if($products->count()) {
					DB::alteration_message("Deleting variations for ".$product->Title, "deleted");
					$variations = ProductVariation::get()->filter(array("ProductID" => $productID))->limit(100);
					if($variations->count()) {
						DB::alteration_message("PRE DELETE COUNT: ".$variations->count());
						foreach($variations as $variation) {
							DB::alteration_message("&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Deleting Variation: ".$variation->Title(), "deleted");
							if($live) {
								$variation->delete();
							}
							$productVariationArrayID[$variation->ID] = $variation->ID;
						}
						$variations = ProductVariation::get()->filter(array("ProductID" => $productID))->limit(100);
						if($live) {
							if($variations->count()) {
								DB::alteration_message("POST DELETE COUNT: ".$variations->count());
							}
							else {
								DB::alteration_message("All variations have been deleted: ", "created");
							}
						}
						else {
							DB::alteration_message("This was a test only", "created");
						}
					}
					else {
						DB::alteration_message("There are no variations to delete", "created");
					}
					DB::alteration_message("Starting cleanup", "created");
					if($live) {
						$sql = "
									DELETE
									FROM \"Product_VariationAttributes\"
									WHERE \"ProductID\" = ".$productID;
						DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
						DB::query($sql);
						$sql = "
									DELETE \"ProductVariation_AttributeValues\"
									FROM \"ProductVariation_AttributeValues\"
										LEFT JOIN \"ProductVariation\"
											ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
									WHERE \"ProductVariation\".\"ID\" IS NULL";
						DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
						DB::query($sql);
					}
					else {
						$sql = "
									SELECT COUNT(Product_VariationAttributes.ID)
									FROM \"Product_VariationAttributes\"
									WHERE \"ProductID\" = ".$productID;
						DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
						$result = DB::query($sql);
						DB::alteration_message("Would have deleted ".$result->value()." rows");
						$sql = "
									SELECT COUNT (\"ProductVariation_AttributeValues\".\"ID\")
									FROM \"ProductVariation_AttributeValues\"
										LEFT JOIN \"ProductVariation\"
											ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
									WHERE
										\"ProductVariation\".\"ID\" IS NULL OR
										\"ProductVariation\".\"ID\" IN(".implode(",", $productVariationArrayID).") ";
						DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
						$result = DB::query($sql);
						DB::alteration_message("Would have deleted ".$result->value()." rows");
					}
				}
			}
		}
		else {
			DB::alteration_message("Product does not exist. You can set the product by adding it productid=XXX as a GET variable.  You can also add <i>all</i> to delete ALL product Variations.", "deleted");
		}
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
