<?php


/**
 * allows the creation of variations from a CSV
 * CSV will have the following fields:
 * ProductTitle,
 * Size,
 * Colour,
 * Price
 * If you like to add more fields, then it is recommended that you extend this BuildTask
 * to your own BuildTask.
 *
 */

class EcommerceTaskCSVToVariations extends BuildTask
{

    protected $forreal = false;

    protected $title = "Create variations from a Spreadsheets (comma separated file CSV)";

    protected $description = "
		Does not delete any record, it only updates and adds.
		The minimum recommend columns are: ProductTitle (or ProductInternalItemID), Size, Colour, Price, InternalItemID.
		You can add ?forreal=1 to the URL to run the task for real.";

    /**
     * excluding base folder
     *
     * e.g. assets/files/mycsv.csv
     * @var String
     */
    private static $file_location = "";

    /**
     * Cell entry for a price that is not available
     * @var String
     */
    private static $no_price_available = "POA";

    /**
     * @var Array
     */
    private static $attribute_type_field_names = array(
        "Size",
        "Colour"
    );

    /**
     * Is the CSV separated by , or ; or [tab]?
     */
    protected $csvSeparator = ",";


    /**
     * @var Boolean
     */
    protected $debug = true;


    /**
     * the original data from the CVS
     * @var Array
     */
    protected $csv = array();

    /**
     * Structure will be as follows:
     *
     *     ProductID => array(
     *         "Product" => $product,
     *         "VariationRows" => array(
     *             [1] => array(
     *                 "Data" => array(),
     *                 "Variation" => $variation
     *             )
     *         )
     *     ),
     *     ProductID => array(
     *         "Product" => $product,
     *         "VariationRows" => array(
     *             [1] => array(
     *                 "Data" => array(),
     *                 "Variation" => $variation
     *             ),
     *             [2] => array(
     *                 "Data" => array(),
     *                 "Variation" => $variation
     *             )
     *         )
     *     )
     *
     * @var Array
     */
    protected $data = array();

    /**
     * list of products without variations
     * @return Array
     */
    protected $soleProduct = array();

    /**
     * The default page of where the products are added.
     * @var Int
     */
    protected $defaultProductParentID = 0;

    public function getDescription()
    {
        if ($this->csvSeparator == "\t") {
            $this->csvSeparatorName = "[TAB]";
        } else {
            $this->csvSeparatorName = $this->csvSeparator;
        }
        return $this->description .". The file to be used is: ".$this->Config()->get("file_location").". The columns need to be separated by '".$this->csvSeparatorName."'";
    }

    /**
     *
     */
    public function run($request)
    {
        increase_time_limit_to(3600);
        increase_memory_limit_to('512M');
        if ($request->param("forreal") || (isset($_GET["forreal"]) && $_GET["forreal"] == 1)) {
            $this->forreal = true;
        }
        if ($this->forreal) {
            $this->reset();
        }
        $this->readFile();
        $this->createProducts();
        $this->findVariations();
        if ($this->forreal) {
            $this->createVariations();
            $this->getExtraDataForVariations();
        } else {
            $this->showData();
        }
    }

    /**
     * do more with Product
     * @param Product $product
     * @param Array $row
     */
    protected function addMoreProduct($product, $row)
    {
        //overwrite in an extension of this task
    }

    /**
     * do more with Product that does have any variations
     * @param Product $product
     * @param Array $row
     */
    protected function addMoreProductForProductWithoutVariations($product, $row)
    {
        //overwrite in an extension of this task
    }

    /**
     * do more with Product Variation
     * @param ProductAttributeType $attributeType
     * @param String $fieldName
     * @param Product $product
     */
    protected function addMoreAttributeType($attributeType, $fieldName, $product)
    {
        //overwrite in an extension of this task
    }

    /**
     * do more with Product Variation
     * @param ProductAttributeType $attributeValue
     * @param ProductAttributeType $attributeType
     * @param Product $product
     */
    protected function addMoreToAttributeValue($attributeValue, $attributeType, $product)
    {
        //overwrite in an extension of this task
    }

    /**
     * do more with Product Variation
     * @param ProductVariation $variation
     * @param Array $variationData
     * @param Product $product
     */
    protected function addMoreToVariation($variation, $variationData, $product)
    {
        //overwrite in an extension of this task
    }


    protected function reset()
    {
        //to do...
    }

    protected function readFile()
    {
        echo "================================================ READING FILE ================================================";
        $this->alterationMessage("<h3>".$this->getDescription()."</h3>", "created");
        $rowCount = 1;
        $rows = array();
        $fileLocation = $this->config()->get("file_location");
        $this->alterationMessage("$fileLocation is the file we are reading", "created");
        if (($handle = fopen($fileLocation, "r")) !== false) {
            while (($data = fgetcsv($handle, 100000, $this->csvSeparator)) !== false) {
                $rows[] = $data;
                $rowCount++;
            }
            fclose($handle);
        }
        //$rows = str_getcsv(file_get_contents(, ",", '"');

        $header = array_shift($rows);

        $this->csv = array();
        $rowCount = 1;
        foreach ($rows as $row) {
            if (count($header) != count($row)) {
                $this->alterationMessage("I am trying to merge ".implode(", ", $header)." with ".implode(", ", $row)." but the column count does not match!", "deleted");
                die("STOPPED");
            }
            $this->csv[] = array_combine($header, $row);
            $rowCount++;
        }
        //data fixes
        foreach ($this->csv as $key => $row) {
            if (!isset($row["ProductTitle"])) {
                $this->csv[$key]["ProductTitle"] = "";
            }
            if (!isset($row["ProductInternalItemID"])) {
                $this->csv[$key]["ProductInternalItemID"] = $row["ProductTitle"];
            }
        }
        $this->alterationMessage("Imported ".count($this->csv)." rows with ".count($header)." cells each");
        $this->alterationMessage("Fields are: ".implode("<br /> - ............ ", $header));
        $this->alterationMessage("================================================", "show");
    }

    /**
     *
     *
     */
    protected function createProducts()
    {
        $this->alterationMessage("================================================ CREATING PRODUCTS ================================================", "show");
        $productsCompleted = array();
        foreach ($this->csv as $row) {
            if (!isset($productsCompleted[$row["ProductTitle"]])) {
                $filterArray = array(
                    "Title" => $row["ProductTitle"],
                    "InternalItemID" => $row["ProductInternalItemID"]
                );
                $product = ProductPage::get()->filterAny($filterArray)->first();
                if ($product && $product->ParentID) {
                    $this->defaultProductParentID = $product->ParentID;
                } elseif (!$this->defaultProductParentID) {
                    $this->defaultProductParentID = ProductGroup::get()->first()->ID;
                }
                if (!$product) {
                    $product = ProductPage::create($filterArray);
                    $product->MenuTitle = $row["ProductTitle"];

                    $this->alterationMessage("Creating Product: ".$row["ProductTitle"], "created");
                } else {
                    $this->alterationMessage("Product: ".$row["ProductTitle"]." already exists");
                }
                if (!$product->ParentID) {
                    $product->ParentID = $this->defaultProductParentID;
                }
                $product->Title = $row["ProductTitle"];
                $product->InternalItemID = $row["ProductInternalItemID"];
                if ($this->forreal) {
                    $this->addMoreProduct($product, $row);
                    $product->write("Stage");
                    if ($product->IsPublished()) {
                        $product->Publish('Stage', 'Live');
                    }
                }
                $productsCompleted[$row["ProductTitle"]] = $product->ID;
                $this->data[$product->ID] = array(
                    "Product" => $product,
                    "VariationRows" => array()
                );
            }
        }
        $this->alterationMessage("================================================", "show");
    }


    protected function findVariations()
    {
        $this->alterationMessage("================================================ FINDING VARIATIONS ================================================", "show");
        foreach ($this->data as $productKey => $data) {
            $product = $data["Product"];
            $title = $product->Title;
            $internalItemID = $product->InternalItemID;
            foreach ($this->csv as $key => $row) {
                if (strtolower(trim($title)) == strtolower(trim($row["ProductTitle"])) || strtolower(trim($internalItemID)) == strtolower(trim($row["ProductInternalItemID"]))) {
                    $this->data[$product->ID]["VariationRows"][$key] = array(
                        "Data" => $row,
                        "Variation" => null
                    );
                }
            }
            if (count($this->data[$product->ID]["VariationRows"]) < 2) {
                $varData = array_shift($this->data[$product->ID]["VariationRows"]);
                $varDataRow = $varData["Data"];
                $this->addFieldToObject($product, $data, "Price", "");
                $this->addFieldToObject($product, $data, "InternalItemID", "");
                if ($this->forreal) {
                    $this->addMoreProductForProductWithoutVariations($product, $varDataRow);
                    $product->write("Stage");
                    if ($product->IsPublished()) {
                        $product->Publish('Stage', 'Live');
                    }
                }
                $this->soleProduct[$product->ID] = $product->Title.", ID: ".$product->ID;
                unset($this->data[$productKey]);
                $this->alterationMessage("Removing data for ".$product->Title." because there is only ONE variation. ", "deleted");
            } else {
                $this->alterationMessage("Found ".count($this->data[$product->ID]["VariationRows"])." Variations for ".$product->Title);
            }
        }
        $this->alterationMessage("================================================", "show");
    }

    protected function showData()
    {
        echo "<h2>Variation Summary</h2>";
        foreach ($this->data as $productKey => $value) {
            if (isset($value["Product"]) && $value["Product"]) {
                $this->data[$productKey]["Product"] = $value["Product"]->Title.", ID: ".$value["Product"]->ID;
            } else {
                $this->data[$productKey]["Product"] = "Not found";
            }
            $this->alterationMessage($this->data[$productKey]["Product"].", variations: ".count($this->data[$productKey]["VariationRows"]), "created");
        }
        echo "<h2>Products without variations</h2>";
        foreach ($this->soleProduct as $productKey => $value) {
            $this->alterationMessage($value, "created");
        }
        echo "<h2>Variation data</h2>";
        echo "<pre>";
        print_r($this->data);
        echo "</pre>";
        echo "<h2>CSV Data</h2>";
        echo "<pre>";
        print_r($this->csv);
        echo "</pre>";
        die("====================================================== STOPPED - add ?forreal=1 to run for real. ======================================");
    }

    protected function createVariations()
    {
        $this->alterationMessage("================================================ CREATING VARIATIONS ================================================", "show");
        foreach ($this->data as $data) {
            $types = array();
            $values = array();
            $product = $data["Product"];
            $arrayForCreation = array();
            $variationFilter = array();
            $this->alterationMessage("<h1>Working out variations for ".$product->Title."</h1>");
            //create attribute types for one product
            $this->alterationMessage("....Creating attribute types");
            foreach ($this->Config()->get("attribute_type_field_names") as $fieldKey => $fieldName) {
                $startMessage = "........Checking field $fieldName";
                $attributeTypeName = trim($data["Product"]->Title)."_".$fieldName;
                $filterArray = array("Name" => $attributeTypeName);
                $type = ProductAttributeType::get()->filter($filterArray)->first();
                if (!$type) {
                    $this->alterationMessage($startMessage." ... creating new attribute type: ".$attributeTypeName, "created");
                    $type = new ProductAttributeType($filterArray);
                    $type->Label = $attributeTypeName;
                    $type->Sort = $fieldKey;
                } else {
                    $this->alterationMessage($startMessage." ... 	found existing attribute type: ".$attributeTypeName);
                }
                $this->addMoreAttributeType($type, $fieldName, $product);
                $type->write();
                $types[$fieldName] = $type;
                $product->VariationAttributes()->add($type);
            }
            //go through each variation to make the values
            $this->alterationMessage("....Creating attribute values");
            foreach ($data["VariationRows"] as $key => $row) {
                //go through each value
                foreach ($this->Config()->get("attribute_type_field_names") as $fieldName) {
                    if (!isset($row["Data"][$fieldName])) {
                        $this->alterationMessage("ERROR; $fieldName not set at all....", "deleted");
                        continue;
                    } elseif (!trim($row["Data"][$fieldName])) {
                        $this->alterationMessage("skipping $fieldName as there are no entries...");
                        continue;
                    }
                    $startMessage = "........Checking field $fieldName";
                    //create attribute value
                    $attributeValueName = $row["Data"][$fieldName];
                    $filterArray = array("Code" => $attributeValueName, "TypeID" => $types[$fieldName]->ID);
                    $value = ProductAttributeValue::get()->filter($filterArray)->first();
                    if (!$value) {
                        $this->alterationMessage($startMessage."............creating new attribute value:  <strong>".$attributeValueName."</strong> for ".$types[$fieldName]->Name, "created");
                        $value = ProductAttributeValue::create($filterArray);
                        $value->Code = $attributeValueName;
                        $value->Value = $attributeValueName;
                    } else {
                        $this->alterationMessage($startMessage."............found existing attribute value: <strong>".$attributeValueName."</strong> for ".$types[$fieldName]->Name);
                    }
                    $this->addMoreAttributeType($value, $types[$fieldName], $product);
                    $value->write();
                    $values[$fieldName] = $value;

                    //add at arrays for creation...
                    if (!isset($arrayForCreation[$types[$fieldName]->ID])) {
                        $arrayForCreation[$types[$fieldName]->ID] = array();
                    }
                    $arrayForCreation[$types[$fieldName]->ID][] = $value->ID;
                    if (!isset($variationFilters[$key])) {
                        $variationFilters[$key] = array();
                    }
                    $variationFilters[$key][$types[$fieldName]->ID] = $value->ID;
                }
            }
            //remove attribute types without values... (i.e. product only has size of colour)
            foreach ($product->VariationAttributes() as $productTypeToBeDeleted) {
                if ($productTypeToBeDeleted->Values()->count() == 0) {
                    $this->alterationMessage("....deleting attribute type with no values: ".$productTypeToBeDeleted->Title);
                    $product->VariationAttributes()->remove($productTypeToBeDeleted);
                }
            }
            $this->alterationMessage("....Creating Variations ///");
            //$this->alterationMessage("....Creating Variations From: ".print_r(array_walk($arrayForCreation, array($this, 'implodeWalk'))));
            //generate variations
            $variationAttributeValuesPerVariation = array();
            foreach ($arrayForCreation as $typeID => $variationEntry) {
                foreach ($variationEntry as $positionOfVariation => $attributeValueID) {
                    $variationAttributeValuesPerVariation[$positionOfVariation][$typeID] = $attributeValueID;
                }
            }

            foreach ($variationAttributeValuesPerVariation as $variationAttributes) {
                $variation = $product->getVariationByAttributes($variationAttributes);
                if ($variation instanceof ProductVariation) {
                    $this->alterationMessage(".... Variation " . $variation->FullName . " Already Exists ///");
                } else {
                    //2. if not, create variation with attributes
                    $className = $product->getClassNameOfVariations();
                    $newVariation = new $className(
                        array(
                            'ProductID' => $product->ID,
                            'Price' => $product->Price
                        )
                    );
                    $newVariation->setSaveParentProduct(false);
                    $newVariation->write();
                    $newVariation->AttributeValues()->addMany($variationAttributes);
                    $this->alterationMessage(".... Variation " . $newVariation->FullName . " created ///", "created");
                }
            }

            //find variations and add to VariationsRows
            foreach ($data["VariationRows"] as $key => $row) {
                $variation = $product->getVariationByAttributes($variationFilters[$key]);
                if ($variation instanceof ProductVariation) {
                    $this->alterationMessage("........Created variation, ".$variation->getTitle());
                    $this->data[$product->ID]["VariationRows"][$key]["Variation"] = $variation;
                } else {
                    $this->alterationMessage("........Could not find variation", "deleted");
                }
            }
        }
        $this->alterationMessage("================================================", "show");
    }

    protected function getExtraDataForVariations()
    {
        $this->alterationMessage("================================================ ADDING EXTRA DATA ================================================", "show");
        foreach ($this->data as $productData) {
            $product = $productData["Product"];
            $this->alterationMessage("<h1>Adding extra data for ".$product->Title." with ".(count($productData["VariationRows"]))."</h1>"." Variations");
            foreach ($productData["VariationRows"] as $key => $row) {
                $variation = $row["Variation"];
                $variationData = $row["Data"];
                if ($variation instanceof ProductVariation) {
                    $this->alterationMessage("<h3>....Updating ".$variation->getTitle()."</h3>", "show");
                    if (isset($variationData["Price"])) {
                        if ($price = floatval($variationData["Price"]) - 0) {
                            if (floatval($variation->Price) != floatval($price)) {
                                $this->alterationMessage("........Price = ".$price, "created");
                                $variation->Price = $price;
                            }
                        } else {
                            $this->alterationMessage("........NO Price", "deleted");
                        }
                    } else {
                        $this->alterationMessage("........NO Price field", "deleted");
                    }
                    $this->addFieldToObject($variation, $variationData, "Price", "");
                    $this->addFieldToObject($variation, $variationData, "InternalItemID", "");
                    $this->addMoreToVariation($variation, $variationData, $product);
                    $variation->write();
                } else {
                    $this->alterationMessage("....Could not find variation for ".print_r($row), "deleted");
                }
            }
        }
        $this->alterationMessage("================================================", "show");
    }

    /**
     * adds a field to the variation
     * @param ProductVariation | Product $variation
     * @param array $variationData - the array of data
     * @param String $objectField - the name of the field on the variation itself
     * @param String $arrayField - the name of the field in the variationData
     *
     */
    protected function addFieldToObject($variation, $variationData, $objectField, $arrayField = "")
    {
        if (!$arrayField) {
            $arrayField = $objectField;
        }
        if (isset($variationData[$arrayField])) {
            if ($value = $variationData[$arrayField]) {
                if ($variation->$objectField != $value) {
                    $this->alterationMessage("........$objectField = ".$value, "changed");
                }
                $variation->$objectField = $value;
            } else {
                $this->alterationMessage("........NO $arrayField value", "deleted");
            }
        } else {
            $this->alterationMessage("........NO $arrayField field", "deleted");
        }
    }

    /*
     * @param string $message
     * @param string $style
     */
    protected function alterationMessage($message, $style = "")
    {
        if (!Director::isDev() || $style) {
            DB::alteration_message($message, $style);
            ob_start();
            ob_end_flush();
        } else {
            echo ".";
            ob_start();
            ob_end_flush();
        }
    }
}


class EcommerceTaskCSVToVariations_EXT extends Extension
{

    private static $allowed_actions = array(
        "ecommercetaskcsvtovariations" => true
    );

    //NOTE THAT updateEcommerceDevMenuConfig adds to Config options
    //but you can als have: updateEcommerceDevMenuDebugActions
    public function updateEcommerceDevMenuRegularMaintenance($buildTasks)
    {
        $buildTasks[] = "ecommercetaskcsvtovariations";
        return $buildTasks;
    }

    public function ecommercetaskcsvtovariations($request)
    {
        $this->owner->runTask("ecommercetaskcsvtovariations", $request);
    }
}
