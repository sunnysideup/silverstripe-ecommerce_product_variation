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

	protected $title = "Create variations from a Spreadsheets (comma separated file CSV)";

	protected $description = "
		Does not delete any record, it only updates and adds.
		The minimum recommend columns are: ProductTitle (or ProductInternalItemID), Size, Colour, Price, InternalItemID";

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
	 * The default page of where the products are added.
	 * @var Int
	 */
	protected $defaultProductParentID = 0;

	/**
	 *
	 */
	public function run($request){
		increase_time_limit_to(3600);
		increase_memory_limit_to('512M');
		$this->reset();
		$this->readFile();
		$this->createProducts();
		$this->findVariations();
		$this->createVariations();
		$this->getExtraDataForVariations();
	}

	/**
	 * do more with Product Variation
	 * @param Product $product
	 * @param Array $row
	 */
	protected function addMoreProduct($product, $row){
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
		DB::alteration_message("================================================ READING FILE ================================================");
		$rowCount = 1;
		$rows = array();
		$fileLocation = Director::baseFolder()."/".$this->config()->get("file_location");
		if (($handle = fopen($fileLocation, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 100000, ",")) !== FALSE) {
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
				DB::alteration_message("I am trying to merge ".implode(", ", $header)." with ".implode(", ", $row)." but the column count does not match!", "deleted");
				die("STOPPED");
			}
			$this->csv[] = array_combine($header, $row);
			$rowCount++;
		}
		DB::alteration_message("Imported ".count($this->csv)." rows with ".count($header)." cells each");
		DB::alteration_message("Fields are: ".implode(", ", $header));
		DB::alteration_message("================================================");
	}

	/**
	 *
	 *
	 */
	protected function createProducts(){
		DB::alteration_message("================================================ CREATING PRODUCTS ================================================");
		$productsCompleted = array();
		foreach($this->csv as $row) {
			if(!isset($productsCompleted[$row["ProductTitle"]])) {
				if(!isset($row["ProductInternalItemID"])) {$row["ProductInternalItemID"] = "";}
				if(!isset($row["ProductTitle"])) {$row["ProductTitle"] = "";}
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

					DB::alteration_message("Creating Product: ".$row["ProductTitle"], "created");
				}
				else {
					DB::alteration_message("Product: ".$row["ProductTitle"]." already exists");
				}
				if(!$product->ParentID) {
					$product->ParentID = $this->defaultProductParentID;
				}
				$this->addMoreProduct($product, $row);
				$product->writeToStage("Stage");
				$product->Publish('Stage', 'Live');
				$productsCompleted[$row["ProductTitle"]] = $product->ID;
				$this->data[$product->ID] = array(
					"Product" => $product,
					"VariationRows" => array()
				);
			}
		}
		DB::alteration_message("================================================");
	}


	protected function findVariations(){
		DB::alteration_message("================================================ FINDING VARIATIONS ================================================");
		foreach($this->data as $productKey => $data) {
			$product = $data["Product"];
			$title = $product->Title;
			foreach($this->csv as $key => $row) {
				if($title == $row["ProductTitle"]) {
					$this->data[$product->ID]["VariationRows"][$key] = array(
						"Data" => $row,
						"Variation" => null
					);
				}
			}
			if(count($this->data[$product->ID]["VariationRows"]) < 2) {
				unset($this->data[$productKey]);
				DB::alteration_message("Removing data for ".$product->Title." because there is only ONE variation. Please create these in the CMS", "deleted");
			}
			else {
				DB::alteration_message("Found ".count($this->data[$product->ID]["VariationRows"])." Variations for ".$product->Title);
			}
		}
		DB::alteration_message("================================================");
	}

	protected function createVariations(){
		DB::alteration_message("================================================ CREATING VARIATIONS ================================================");
		foreach($this->data as $data) {
			$types = array();
			$values = array();
			$product = $data["Product"];
			$arrayForCreation = array();
			$variationFilter = array();
			DB::alteration_message("Working out variations for ".$product->Title);
			//create attribute types for one product
			DB::alteration_message("....Creating attribute types");
			foreach($this->Config()->get("attribute_type_field_names") as $fieldName) {
				DB::alteration_message("........Checking field $fieldName");
				$attributeTypeName = $data["Product"]->Title."_".$fieldName;
				$filterArray = array("Name" => $attributeTypeName);
				$type = ProductAttributeType::get()->filter($filterArray)->first();
				if(!$type) {
					DB::alteration_message("............creating new attribute type: ".$attributeTypeName, "created");
					$type = new ProductAttributeType($filterArray);
					$type->Label = $attributeTypeName;

				}
				else {
					DB::alteration_message("............found existing attribute type: ".$attributeTypeName);
				}
				$this->addMoreAttributeType($type, $fieldName, $product);
				$type->write();
				$types[$fieldName] = $type;
				$product->VariationAttributes()->add($type);
			}
			//go through each variation to make the values
			DB::alteration_message("....Creating attribute values");
			foreach($data["VariationRows"] as $key => $row) {
				//go through each value
				foreach($this->Config()->get("attribute_type_field_names") as $fieldName) {
					DB::alteration_message("........Checking field $fieldName");
					//create attribute value
					$attributeValueName = $row["Data"][$fieldName];
					$filterArray = array("Code" => $attributeValueName, "TypeID" => $types[$fieldName]->ID);
					$value = ProductAttributeValue::get()->filter($filterArray)->first();
					if(!$value) {
						DB::alteration_message("............creating new attribute value: ".$attributeValueName." for ".$types[$fieldName]->Name, "created");
						$value = ProductAttributeValue::create($filterArray);
						$value->Code = $attributeValueName;
						$value->Value = $attributeValueName;
					}
					else {
						DB::alteration_message("............found existing attribute value: ".$attributeValueName." for ".$types[$fieldName]->Name);
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
			DB::alteration_message("....Creating Variations ///");
			//DB::alteration_message("....Creating Variations From: ".print_r(array_walk($arrayForCreation, array($this, 'implodeWalk'))));
			//generate variations
			$product->generateVariationsFromAttributeValues($arrayForCreation);

			//find variations and add to VariationsRows
			foreach($data["VariationRows"] as $key => $row) {
				$variation = $product->getVariationByAttributes($variationFilters[$key]);
				if($variation instanceof ProductVariation) {
					DB::alteration_message("........Created variation, ".$variation->getTitle(), "created");
					$this->data[$product->ID]["VariationRows"][$key]["Variation"] = $variation;
				}
				else {
					DB::alteration_message("........Could not find variation", "deleted");
				}
			}
		}
		DB::alteration_message("================================================");
	}

	protected function getExtraDataForVariations() {
		DB::alteration_message("================================================ ADDING EXTRA DATA ================================================");
		foreach($this->data as $productData) {
			$product = $productData["Product"];
			DB::alteration_message("Adding extra data for ".$product->Title." with ".count($productData["VariationRows"])." Variations");
			foreach($productData["VariationRows"] as $key => $row) {
				$variation = $row["Variation"];
				$variationData = $row["Data"];
				if($variation instanceof ProductVariation) {
					DB::alteration_message("....Updating ".$variation->getTitle());
					if(isset($variationData["Price"])) {
						if($price = floatval($variationData["Price"]) - 0) {
							DB::alteration_message("........Price = ".$price, "created");
							$variation->Price = $price;
						}
						else {
							DB::alteration_message("........NO Price", "deleted");
						}
					}
					else {
						DB::alteration_message("........NO Price field", "deleted");
					}
					$this->addFieldToVariation($variation, "Price", "");
					$this->addFieldToVariation($variation, "InternalItemID", "");
					$this->addMoreToVariation($variation, $variationData, $product);
					$variation->write();
				}
				else {
					DB::alteration_message("....Could not find variation for ".print_r($row), "deleted");
				}
			}
		}
		DB::alteration_message("================================================");
	}

	protected function addFieldToVariation($variation, $objectField, $arrayField = "") {
		if(!$arrayField) {
			$arrayField = $objectField;
		}
		if(isset($variationData[$arrayField])) {
			if($value = $variationData[$arrayField]) {
				DB::alteration_message("........$objectField = ".$value, "created");
				$variation->$objectField = $value;
			}
			else {
				DB::alteration_message("........NO $arrayField value", "deleted");
			}
		}
		else {
			DB::alteration_message("........NO $arrayField field", "deleted");
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
