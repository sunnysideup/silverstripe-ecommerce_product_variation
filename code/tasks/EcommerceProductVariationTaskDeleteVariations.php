<?php

class EcommerceProductVariationTaskDeleteVariations extends BuildTask
{

    protected $verbose = true;

    public static function create_link($product)
    {
        if (is_numeric($product)) {
            $product = Product::get()->byID($product);
        } elseif ($product instanceof Product) {
            //do nothing
        }
        if ($product) {
            return "dev/tasks/EcommerceProductVariationTaskDeleteVariations/?productid=".$product->ID."&live=1&silent=1";
        }
    }

    protected $title = "Deletes all the variations and associated data from a product";

    protected $description = "CAREFUL: the developer will need to supply the ID as a get variable (?productid=XXX) as well as a test / live flag (?live=1, default is test) for the product and variations will be deleted without keeping a history.";

    public function run($request)
    {
        $productVariationArrayID = array();
        if (empty($_GET["silent"])) {
            $this->verbose = true;
        } else {
            $this->verbose = intval($_GET["silent"]) == 1 ? false : true;
        }
        if (empty($_GET["productid"])) {
            $productID = 0;
        } elseif ($_GET["productid"] == 'all') {
            $productID = -1;
        } else {
            $productID = intval($_GET["productid"]);
        }
        if (empty($_GET["live"])) {
            $live = false;
        } else {
            $live = intval($_GET["live"]) == 1 ? true : false;
        }
        if ($live) {
            if ($this->verbose) {
                DB::alteration_message("this is a live task", "deleted");
            }
        } else {
            if ($this->verbose) {
                DB::alteration_message("this is a test only. If you add a live=1 get variable then you can make it for real ;-)", "created");
            }
        }
        if ($productID == -1) {
            $products = Product::get();
        } else {
            $products = null;
            $product = Product::get()->byID($productID);
            if ($product) {
                $products= new ArrayList();
                $products->push($product);
            }
        }
        if ($products && $products->count()) {
            foreach ($products as $product) {
                $productID = $product->ID;
                if ($products->count()) {
                    if ($this->verbose) {
                        DB::alteration_message("Deleting variations for ".$product->Title, "deleted");
                    }
                    $variations = ProductVariation::get()->filter(array("ProductID" => $productID))->limit(100);
                    if ($variations->count()) {
                        if ($this->verbose) {
                            DB::alteration_message("PRE DELETE COUNT: ".$variations->count());
                        }
                        foreach ($variations as $variation) {
                            if ($this->verbose) {
                                DB::alteration_message("&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Deleting Variation: ".$variation->Title(), "deleted");
                            }
                            if ($live) {
                                $variation->delete();
                            }
                            $productVariationArrayID[$variation->ID] = $variation->ID;
                        }
                        $variations = ProductVariation::get()->filter(array("ProductID" => $productID))->limit(100);
                        if ($live) {
                            if ($variations->count()) {
                                if ($this->verbose) {
                                    DB::alteration_message("POST DELETE COUNT: ".$variations->count());
                                }
                            } else {
                                if ($this->verbose) {
                                    DB::alteration_message("All variations have been deleted: ", "created");
                                }
                            }
                        } else {
                            if ($this->verbose) {
                                DB::alteration_message("This was a test only", "created");
                            }
                        }
                    } else {
                        if ($this->verbose) {
                            DB::alteration_message("There are no variations to delete", "created");
                        }
                    }
                    if ($this->verbose) {
                        DB::alteration_message("Starting cleanup", "created");
                    }
                    if ($live) {
                        $sql = "
									DELETE
									FROM \"Product_VariationAttributes\"
									WHERE \"ProductID\" = ".$productID;
                        if ($this->verbose) {
                            DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
                        }
                        DB::query($sql);
                        $sql = "
									DELETE \"ProductVariation_AttributeValues\"
									FROM \"ProductVariation_AttributeValues\"
										LEFT JOIN \"ProductVariation\"
											ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
									WHERE \"ProductVariation\".\"ID\" IS NULL";
                        if ($this->verbose) {
                            DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
                        }
                        DB::query($sql);
                    } else {
                        $sql = "
									SELECT COUNT(Product_VariationAttributes.ID)
									FROM \"Product_VariationAttributes\"
									WHERE \"ProductID\" = ".$productID;
                        if ($this->verbose) {
                            DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
                        }
                        $result = DB::query($sql);
                        if ($this->verbose) {
                            DB::alteration_message("Would have deleted ".$result->value()." rows");
                        }
                        $sql = "
									SELECT COUNT (\"ProductVariation_AttributeValues\".\"ID\")
									FROM \"ProductVariation_AttributeValues\"
										LEFT JOIN \"ProductVariation\"
											ON \"ProductVariation_AttributeValues\".\"ProductVariationID\" = \"ProductVariation\".\"ID\"
									WHERE
										\"ProductVariation\".\"ID\" IS NULL OR
										\"ProductVariation\".\"ID\" IN(".implode(",", $productVariationArrayID).") ";
                        if ($this->verbose) {
                            DB::alteration_message("<pre>RUNNING<br />".$sql."</pre>");
                        }
                        $result = DB::query($sql);
                        if ($this->verbose) {
                            DB::alteration_message("Would have deleted ".$result->value()." rows");
                        }
                    }
                }
            }
        } else {
            if ($this->verbose) {
                DB::alteration_message("Product does not exist. You can set the product by adding it productid=XXX as a GET variable.  You can also add <i>all</i> to delete ALL product Variations.", "deleted");
            }
        }
        DB::alteration_message("Completed", "created");
    }
}

class EcommerceProductVariationTaskDeleteVariations_EXT extends Extension
{

    private static $allowed_actions = array(
        "ecommerceproductvariationtaskdeletevariations" => true
    );

    //NOTE THAT updateEcommerceDevMenuConfig adds to Config options
    //but you can als have: updateEcommerceDevMenuDebugActions
    public function updateEcommerceDevMenuRegularMaintenance($buildTasks)
    {
        $buildTasks[] = "ecommerceproductvariationtaskdeletevariations";
        return $buildTasks;
    }

    public function ecommerceproductvariationtaskdeletevariations($request)
    {
        $this->owner->runTask("EcommerceProductVariationTaskDeleteVariations", $request);
    }
}
