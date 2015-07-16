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

class EcommerceTaskCSVToVariations extends BuildTask {

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

	function getDescription(){
		if($this->csvSeparator == "\t") {
			$this->csvSeparatorName = "[TAB]";
		}
		else {
			$this->csvSeparatorName = $this->csvSeparator;
		}
		return $this->description .". The file to be used is: ".$this->Config()->get("file_location").". The columns need to be separated by '".$this->csvSeparatorName."'";
	}

	/**
	 *
	 */
	public function run($request){
		increase_time_limit_to(3600);
		increase_memory_limit_to('512M');
		if($request->param("forreal") || (isset($_GET["forreal"]) && $_GET["forreal"] == 1)) {
			$this->forreal = true;
		}
		if($this->forreal) {
			$this->reset();
		}
		$this->readFile();
		$this->createProducts();
		$this->findVariations();
		if($this->forreal) {
			$this->createVariations();
			$this->getExtraDataForVariations();
		}
		else {
			$this->showData();
		}
	}

	/**
	 * do more with Product
	 * @param Product $product
	 * @param Array $row
	 */
	protected function addMoreProduct($product, $row){
		//overwrite in an extension of this task
	}

	/**
	 * do more with Product that does have any variations
	 * @param Product $product
	 * @param Array $row
	 */
	protected function addMoreProductForProductWithoutVariations($product, $row){
		//overwrite in an extension of this task
	}

	/**
	 * do more with Product Variation
	 * @param ProductAttributeType $attributeType
	 * @param String $fieldName
	 * @param Product $product
	 */
	protected function addMoreAttributeType($attributeType, $fieldName, $product){
		//overwrite in an extension of this task
	}

	/**
	 * do more with Product Variation
	 * @param ProductAttributeType $attributeValue
	 * @param ProductAttributeType $attributeType
	 * @param Product $product
	 */
	protected function addMoreToAttributeValue($attributeValue, $attributeType, $product){
		//overwrite in an extension of this task
	}

	/**
	 * do more with Product Variation
	 * @param ProductVariation $variation
	 * @param Array $variationData
	 * @param Product $product
	 */
	protected function addMoreToVariation($variation, $variationData, $product){
		//overwrite in an extension of this task
	}


	protected function reset(){
		//to do...
	}

	protected function readFile(){
		DB::alteration_message("================================================ READING FILE ================================================"); ob_start();
		flush(); ob_end_flush(); DB::alteration_message("<h3>".$this->getDescription()."</h3>", "created");ob_start();
		$rowCount = 1;
		$rows = array();
		$fileLocation = $this->config()->get("file_location");
		flush(); ob_end_flush(); DB::alteration_message("$fileLocation is the file we are reading", "created");ob_start();
		if (($handle = fopen($fileLocation, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 100000, $this->csvSeparator)) !== FALSE) {
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
			if(count($header) != count($row)) {
				flush(); ob_end_flush(); DB::alteration_message("I am trying to merge ".implode(", ", $header)." with ".implode(", ", $row)." but the column count does not match!", "deleted");ob_start();
				die("STOPPED");
			}
			$this->csv[] = array_combine($header, $row);
			$rowCount++;
		}
		//data fixes
		foreach($this->csv as $key => $row) {
			if(!isset($row["ProductTitle"])) {$this->csv[$key]["ProductTitle"] = "";}
			if(!isset($row["ProductInternalItemID"])) {$this->csv[$key]["ProductInternalItemID"] = $row["ProductTitle"];}
		}
		flush(); ob_end_flush(); DB::alteration_message("Imported ".count($this->csv)." rows with ".count($header)." cells each");ob_start();
		flush(); ob_end_flush(); DB::alteration_message("Fields are: ".implode(", ", $header));ob_start();
		flush(); ob_end_flush(); DB::alteration_message("================================================");ob_start();
	}

	/**
	 *
	 *
	 */
	protected function createProducts(){
		flush(); ob_end_flush(); DB::alteration_message("================================================ CREATING PRODUCTS ================================================");ob_start();
		$productsCompleted = array();
		foreach($this->csv as $row) {
			if(!isset($productsCompleted[$row["ProductTitle"]])) {
				$filterArray = array(
					"Title" => $row["ProductTitle"],
					"InternalItemID" => $row["ProductInternalItemID"]
				);
				$product = ProductPage::get()->filterAny($filterArray)->first();
				if($product && $product->ParentID) {
					$this->defaultProductParentID = $product->ParentID;
				}
				elseif(!$this->defaultProductParentID) {
					$this->defaultProductParentID = ProductGroup::get()->first()->ID;
				}
				if(!$product) {
					$product = ProductPage::create($filterArray);
					$product->MenuTitle = $row["ProductTitle"];

					flush(); ob_end_flush(); DB::alteration_message("Creating Product: ".$row["ProductTitle"], "created");ob_start();
				}
				else {
					flush(); ob_end_flush(); DB::alteration_message("Product: ".$row["ProductTitle"]." already exists");ob_start();
				}
				if(!$product->ParentID) {
					$product->ParentID = $this->defaultProductParentID;
				}
				$product->Title = $row["ProductTitle"];
				$product->InternalItemID = $row["ProductInternalItemID"];
				if($this->forreal) {
					$this->addMoreProduct($product, $row);
					$product->write("Stage");
					if($product->IsPublished()) {
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
		flush(); ob_end_flush(); DB::alteration_message("================================================");ob_start();
	}


	protected function findVariations(){
		flush(); ob_end_flush(); DB::alteration_message("================================================ FINDING VARIATIONS ================================================");ob_start();
		foreach($this->data as $productKey => $data) {
			$product = $data["Product"];
			$title = $product->Title;
			$internalItemID = $product->InternalItemID;
			foreach($this->csv as $key => $row) {
				if(strtolower(trim($title)) == strtolower(trim($row["ProductTitle"])) || strtolower(trim($internalItemID)) == strtolower(trim($row["ProductInternalItemID"]))) {
					$this->data[$product->ID]["VariationRows"][$key] = array(
						"Data" => $row,
						"Variation" => null
					);
				}
			}
			if(count($this->data[$product->ID]["VariationRows"]) < 2) {
				$varData = array_shift($this->data[$product->ID]["VariationRows"]);
				$varDataRow = $varData["Data"];
				$this->addFieldToObject($product, $data, "Price", "");
				$this->addFieldToObject($product, $data, "InternalItemID", "");
				if($this->forreal) {
					$this->addMoreProductForProductWithoutVariations($product, $varDataRow);
					$product->write("Stage");
					if($product->IsPublished()) {
						$product->Publish('Stage', 'Live');
					}
				}
				$this->soleProduct[$product->ID] = $product->Title.", ID: ".$product->ID;
				unset($this->data[$productKey]);
				flush(); ob_end_flush(); DB::alteration_message("Removing data for ".$product->Title." because there is only ONE variation. ", "deleted");ob_start();
			}
			else {
				flush(); ob_end_flush(); DB::alteration_message("Found ".count($this->data[$product->ID]["VariationRows"])." Variations for ".$product->Title);ob_start();
			}
		}
		flush(); ob_end_flush(); DB::alteration_message("================================================");ob_start();
	}

	protected function showData(){
		echo "<h2>Variation Summary</h2>";
		foreach($this->data as $productKey => $value) {
			if(isset($value["Product"]) && $value["Product"]) {
				$this->data[$productKey]["Product"] = $value["Product"]->Title.", ID: ".$value["Product"]->ID;
			}
			else {
				$this->data[$productKey]["Product"] = "Not found";
			}
			DB::alteration_message($this->data[$productKey]["Product"].", variations: ".count($this->data[$productKey]["VariationRows"]), "created");
		}
		echo "<h2>Products without variations</h2>";
		foreach($this->soleProduct as $productKey => $value) {
			DB::alteration_message($value, "created");
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

	protected function createVariations(){
		flush(); ob_end_flush(); DB::alteration_message("================================================ CREATING VARIATIONS ================================================");ob_start();
		foreach($this->data as $data) {
			$types = array();
			$values = array();
			$product = $data["Product"];
			$arrayForCreation = array();
			$variationFilter = array();
			flush(); ob_end_flush(); DB::alteration_message("Working out variations for ".$product->Title);ob_start();
			//create attribute types for one product
			flush(); ob_end_flush(); DB::alteration_message("....Creating attribute types");ob_start();
			foreach($this->Config()->get("attribute_type_field_names") as $fieldKey => $fieldName) {
				flush(); ob_end_flush(); DB::alteration_message("........Checking field $fieldName");ob_start();
				$attributeTypeName = trim($data["Product"]->Title)."_".$fieldName;
				$filterArray = array("Name" => $attributeTypeName);
				$type = ProductAttributeType::get()->filter($filterArray)->first();
				if(!$type) {
					flush(); ob_end_flush(); DB::alteration_message("............creating new attribute type: ".$attributeTypeName, "created");ob_start();
					$type = new ProductAttributeType($filterArray);
					$type->Label = $attributeTypeName;
					$type->Sort = $fieldKey;
				}
				else {
					flush(); ob_end_flush(); DB::alteration_message("............found existing attribute type: ".$attributeTypeName);ob_start();
				}
				$this->addMoreAttributeType($type, $fieldName, $product);
				$type->write();
				$types[$fieldName] = $type;
				$product->VariationAttributes()->add($type);
			}
			//go through each variation to make the values
			flush(); ob_end_flush(); DB::alteration_message("....Creating attribute values");ob_start();
			foreach($data["VariationRows"] as $key => $row) {
				//go through each value
				foreach($this->Config()->get("attribute_type_field_names") as $fieldName) {
					flush(); ob_end_flush(); DB::alteration_message("........Checking field $fieldName");ob_start();
					//create attribute value
					$attributeValueName = $row["Data"][$fieldName];
					$filterArray = array("Code" => $attributeValueName, "TypeID" => $types[$fieldName]->ID);
					$value = ProductAttributeValue::get()->filter($filterArray)->first();
					if(!$value) {
						flush(); ob_end_flush(); DB::alteration_message("............creating new attribute value: ".$attributeValueName." for ".$types[$fieldName]->Name, "created");ob_start();
						$value = ProductAttributeValue::create($filterArray);
						$value->Code = $attributeValueName;
						$value->Value = $attributeValueName;
					}
					else {
						flush(); ob_end_flush(); DB::alteration_message("............found existing attribute value: ".$attributeValueName." for ".$types[$fieldName]->Name);ob_start();
					}
					$this->addMoreAttributeType($value, $types[$fieldName], $product);
					$value->write();
					$values[$fieldName] = $value;

					//add at arrays for creation...
					if(!isset($arrayForCreation[$types[$fieldName]->ID])) {
						$arrayForCreation[$types[$fieldName]->ID] = array();
					}
					$arrayForCreation[$types[$fieldName]->ID][] = $value->ID;
					if(!isset($variationFilters[$key])) {
						$variationFilters[$key] = array();
					}
					$variationFilters[$key][$types[$fieldName]->ID] = $value->ID;
				}
			}
			flush(); ob_end_flush(); DB::alteration_message("....Creating Variations ///");ob_start();
			//flush(); ob_end_flush(); DB::alteration_message("....Creating Variations From: ".print_r(array_walk($arrayForCreation, array($this, 'implodeWalk'))));ob_start();
			//generate variations
			$product->generateVariationsFromAttributeValues($arrayForCreation);

			//find variations and add to VariationsRows
			foreach($data["VariationRows"] as $key => $row) {
				$variation = $product->getVariationByAttributes($variationFilters[$key]);
				if($variation instanceof ProductVariation) {
					flush(); ob_end_flush(); DB::alteration_message("........Created variation, ".$variation->getTitle(), "created");ob_start();
					$this->data[$product->ID]["VariationRows"][$key]["Variation"] = $variation;
				}
				else {
					flush(); ob_end_flush(); DB::alteration_message("........Could not find variation", "deleted");ob_start();
				}
			}
		}
		flush(); ob_end_flush(); DB::alteration_message("================================================");ob_start();
	}

	protected function getExtraDataForVariations() {
		flush(); ob_end_flush(); DB::alteration_message("================================================ ADDING EXTRA DATA ================================================");ob_start();
		foreach($this->data as $productData) {
			$product = $productData["Product"];
			flush(); ob_end_flush(); DB::alteration_message("Adding extra data for ".$product->Title." with ".count($productData["VariationRows"])." Variations");ob_start();
			foreach($productData["VariationRows"] as $key => $row) {
				$variation = $row["Variation"];
				$variationData = $row["Data"];
				if($variation instanceof ProductVariation) {
					flush(); ob_end_flush(); DB::alteration_message("....Updating ".$variation->getTitle());ob_start();
					if(isset($variationData["Price"])) {
						if($price = floatval($variationData["Price"]) - 0) {
							flush(); ob_end_flush(); DB::alteration_message("........Price = ".$price, "created");ob_start();
							$variation->Price = $price;
						}
						else {
							flush(); ob_end_flush(); DB::alteration_message("........NO Price", "deleted");ob_start();
						}
					}
					else {
						flush(); ob_end_flush(); DB::alteration_message("........NO Price field", "deleted");ob_start();
					}
					$this->addFieldToObject($variation, $variationData, "Price", "");
					$this->addFieldToObject($variation, $variationData, "InternalItemID", "");
					$this->addMoreToVariation($variation, $variationData, $product);
					$variation->write();
				}
				else {
					flush(); ob_end_flush(); DB::alteration_message("....Could not find variation for ".print_r($row), "deleted");ob_start();
				}
			}
		}
		flush(); ob_end_flush(); DB::alteration_message("================================================");ob_start();
	}

	/**
	 * adds a field to the variation
	 * @param ProductVariation | Product $variation
	 * @param array $variationData - the array of data
	 * @param String $objectField - the name of the field on the variation itself
	 * @param String $arrayField - the name of the field in the variationData
	 *
	 */
	protected function addFieldToObject($variation, $variationData, $objectField, $arrayField = "") {
		if(!$arrayField) {
			$arrayField = $objectField;
		}
		if(isset($variationData[$arrayField])) {
			if($value = $variationData[$arrayField]) {
				flush(); ob_end_flush(); DB::alteration_message("........$objectField = ".$value, "changed");ob_start();
				$variation->$objectField = $value;
			}
			else {
				flush(); ob_end_flush(); DB::alteration_message("........NO $arrayField value", "deleted");ob_start();
			}
		}
		else {
			flush(); ob_end_flush(); DB::alteration_message("........NO $arrayField field", "deleted");ob_start();
		}
	}


}


class EcommerceTaskCSVToVariations_EXT extends Extension {

	private static $allowed_actions = array(
		"ecommercetaskcsvtovariations" => true
	);

	//NOTE THAT updateEcommerceDevMenuConfig adds to Config options
	//but you can als have: updateEcommerceDevMenuDebugActions
	function updateEcommerceDevMenuRegularMaintenance($buildTasks){
		$buildTasks[] = "ecommercetaskcsvtovariations";
		return $buildTasks;
	}

	function ecommercetaskcsvtovariations($request){
		$this->owner->runTask("ecommercetaskcsvtovariations", $request);
	}

}
