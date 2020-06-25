2020-06-26 07:45

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_product_variation
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/ecommerce_product_variation/ecommerce_product_variation  --root-dir=/var/www/upgrades/ecommerce_product_variation --write -vvv
Writing changes for 18 files
Running upgrades on "/var/www/upgrades/ecommerce_product_variation/ecommerce_product_variation"
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceProductVariationTest.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceProductVariationTest.php...
[2020-06-26 07:45:34] Applying UpdateConfigClasses to config.yml...
[2020-06-26 07:45:34] Applying UpdateConfigClasses to routes.yml...
[2020-06-26 07:45:34] Applying RenameClasses to CreateEcommerceVariations.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to CreateEcommerceVariations.php...
[2020-06-26 07:45:34] Applying RenameClasses to ProductWithVariationDecoratorController.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to ProductWithVariationDecoratorController.php...
[2020-06-26 07:45:34] Applying RenameClasses to CreateEcommerceVariationsField.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to CreateEcommerceVariationsField.php...
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceSideReport_ProductsWithVariations.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceSideReport_ProductsWithVariations.php...
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceProductVariationTaskDeleteAll.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceProductVariationTaskDeleteAll.php...
[2020-06-26 07:45:34] Applying RenameClasses to ProductVariationsFromAttributeCombinations.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to ProductVariationsFromAttributeCombinations.php...
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceTaskCSVToVariations_EXT.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceTaskCSVToVariations_EXT.php...
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceProductVariationTaskDeleteVariations_EXT.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceProductVariationTaskDeleteVariations_EXT.php...
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceProductVariationTaskDeleteVariations.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceProductVariationTaskDeleteVariations.php...
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceTaskCSVToVariations.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceTaskCSVToVariations.php...
[2020-06-26 07:45:34] Applying RenameClasses to EcommerceProductVariationTaskDeleteAll_EXT.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to EcommerceProductVariationTaskDeleteAll_EXT.php...
[2020-06-26 07:45:34] Applying RenameClasses to ProductAttributeType.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to ProductAttributeType.php...
[2020-06-26 07:45:34] Applying RenameClasses to ProductAttributeValue.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to ProductAttributeValue.php...
[2020-06-26 07:45:34] Applying RenameClasses to ProductVariation_OrderItem.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to ProductVariation_OrderItem.php...
[2020-06-26 07:45:34] Applying RenameClasses to ProductWithVariationDecorator.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to ProductWithVariationDecorator.php...
[2020-06-26 07:45:34] Applying RenameClasses to ProductVariation.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to ProductVariation.php...
[2020-06-26 07:45:34] Applying RenameClasses to _config.php...
[2020-06-26 07:45:34] Applying ClassToTraitRule to _config.php...
modified:	tests/EcommerceProductVariationTest.php
@@ -1,4 +1,6 @@
 <?php
+
+use SilverStripe\Dev\SapphireTest;

 class EcommerceProductVariationTest extends SapphireTest
 {

modified:	_config/config.yml
@@ -3,19 +3,15 @@
 Before: 'app/*'
 After: ['#coreconfig', '#cmsextensions', '#ecommerce']
 ---
-
-Product:
+Sunnysideup\Ecommerce\Pages\Product:
   extensions:
-    - ProductWithVariationDecorator
-
-ProductController:
+    - Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductWithVariationDecorator
+Sunnysideup\Ecommerce\Pages\ProductController:
   extensions:
-    - ProductWithVariationDecoratorController
+    - Sunnysideup\EcommerceProductVariation\Control\ProductWithVariationDecoratorController
   allowed_actions:
     - VariationForm
     - addvariation
-
-
 SilverStripe\Admin\LeftAndMain:
   extra_requirements_javascript:
     - framework/thirdparty/jquery/jquery.js
@@ -23,32 +19,26 @@
     - ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js
     - ecommerce_product_variation/javascript/DeleteEcommerceVariations.js
   extra_requirements_themedCss:
-    CreateEcommerceVariationsField:
+    Sunnysideup\EcommerceProductVariation\Form\CreateEcommerceVariationsField:
       media: ecommerce_product_variation
-
-
-EcommerceDatabaseAdmin:
+Sunnysideup\Ecommerce\Cms\Dev\EcommerceDatabaseAdmin:
   extensions:
-    - EcommerceProductVariationTaskDeleteVariations_EXT
-    - EcommerceProductVariationTaskDeleteAll_EXT
-    - EcommerceTaskCSVToVariations_EXT
-
-ProductConfigModelAdmin:
+    - Sunnysideup\EcommerceProductVariation\Tasks\EcommerceProductVariationTaskDeleteVariations_EXT
+    - Sunnysideup\EcommerceProductVariation\Tasks\EcommerceProductVariationTaskDeleteAll_EXT
+    - Sunnysideup\EcommerceProductVariation\Tasks\EcommerceTaskCSVToVariations_EXT
+Sunnysideup\Ecommerce\Cms\ProductConfigModelAdmin:
   managed_models:
-    - ProductVariation
-    - ProductAttributeType
-    - ProductAttributeValue
-
+    - Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation
+    - Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType
+    - Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue
 ---
 Only:
   classexists: 'DataObjectSorterDOD'
 ---
-
-ProductAttributeValue:
+Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue:
+  extensions:
+    - DataObjectSorterDOD
+Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType:
   extensions:
     - DataObjectSorterDOD

-ProductAttributeType:
-  extensions:
-    - DataObjectSorterDOD
-

modified:	_config/routes.yml
@@ -4,6 +4,6 @@
 ---
 SilverStripe\Control\Director:
   rules:
-    'createecommercevariations//$Action/$ProductID' : 'CreateEcommerceVariations'
-    'createecommercevariationsbatch//$Action' : 'CreateEcommerceVariations_Batch'
+    createecommercevariations//$Action/$ProductID: Sunnysideup\EcommerceProductVariation\Control\CreateEcommerceVariations
+    createecommercevariationsbatch//$Action: CreateEcommerceVariations_Batch


modified:	src/Control/CreateEcommerceVariations.php
@@ -2,17 +2,31 @@

 namespace Sunnysideup\EcommerceProductVariation\Control;

-use Controller;
-use Versioned;
-use EcommerceConfig;
-use Permission;
-use Security;
-use Product;
-use Director;
-use ProductAttributeType;
-use Convert;
-use ProductVariation;
-use DB;
+
+
+
+
+
+
+
+
+
+
+
+use SilverStripe\Versioned\Versioned;
+use Sunnysideup\Ecommerce\Model\Extensions\EcommerceRole;
+use Sunnysideup\Ecommerce\Config\EcommerceConfig;
+use SilverStripe\Security\Permission;
+use SilverStripe\Security\Security;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\Control\Director;
+use SilverStripe\Control\Controller;
+use SilverStripe\Core\Convert;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\ORM\DB;
+


 /**
@@ -138,7 +152,7 @@
     {
         parent::init();
         Versioned::set_reading_mode("Stage.Stage");
-        $shopAdminCode = EcommerceConfig::get("EcommerceRole", "admin_permission_code");
+        $shopAdminCode = EcommerceConfig::get(EcommerceRole::class, "admin_permission_code");
         if (!Permission::check("CMS_ACCESS_CMSMain") && !Permission::check($shopAdminCode)) {
             return Security::permissionFailure($this, _t('Security.PERMFAILURE', ' This page is secured and you need CMS rights to access it. Enter your credentials below and we will send you right along.'));
         }
@@ -155,10 +169,10 @@
             $this->_position = intval($_GET["_position"]);
         }
         if ($this->_typeorvalue == "type") {
-            $this->_classname = 'ProductAttributeType';
+            $this->_classname = ProductAttributeType::class;
             $this->_namefield = 'Name';
         } else {
-            $this->_classname = 'ProductAttributeValue';
+            $this->_classname = ProductAttributeValue::class;
             $this->_namefield = 'Value';
         }


Warnings for src/Control/CreateEcommerceVariations.php:
 - src/Control/CreateEcommerceVariations.php:318 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 318

 - src/Control/CreateEcommerceVariations.php:385 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 385

modified:	src/Control/ProductWithVariationDecoratorController.php
@@ -2,19 +2,36 @@

 namespace Sunnysideup\EcommerceProductVariation\Control;

-use Extension;
-use FieldList;
-use NumericField;
-use FormAction;
-use Form;
-use Requirements;
-use Config;
-use Convert;
-use ShoppingCart;
-use Director;
-use ArrayList;
-use ProductAttributeType;
-use ProductAttributeValue;
+
+
+
+
+
+
+
+
+
+
+
+
+
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Forms\NumericField;
+use SilverStripe\Forms\FormAction;
+use SilverStripe\Forms\RequiredFields;
+use SilverStripe\Forms\Form;
+use SilverStripe\View\Requirements;
+use SilverStripe\Core\Config\Config;
+use Sunnysideup\EcommerceProductVariation\Control\ProductWithVariationDecoratorController;
+use SilverStripe\Core\Convert;
+use Sunnysideup\Ecommerce\Api\ShoppingCart;
+use SilverStripe\Control\Director;
+use SilverStripe\ORM\ArrayList;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\Core\Extension;
+



@@ -121,7 +138,7 @@
                 )
             );
             $requiredfields[] = 'Quantity';
-            $requiredFieldsClass = 'RequiredFields';
+            $requiredFieldsClass = RequiredFields::class;
             $validator = $requiredFieldsClass::create($requiredfields);
             $form = Form::create(
                 $this->owner,
@@ -133,7 +150,7 @@
             Requirements::themedCSS('sunnysideup/ecommerce_product_variation: variationsform', 'ecommerce_product_variation');
             //variation options json generation
             if (
-                Config::inst()->get('ProductWithVariationDecoratorController', 'use_js_validation')
+                Config::inst()->get(ProductWithVariationDecoratorController::class, 'use_js_validation')
                 && $this->owner->HasVariations()
             ) {
                 Requirements::javascript('sunnysideup/ecommerce_product_variation: ecommerce_product_variation/javascript/SelectEcommerceProductVariations.js');
@@ -254,7 +271,7 @@
                 '"ProductAttributeValue"."ID" = "ProductVariation_AttributeValues"."ProductAttributeValueID"'
             )
             ->innerJoin(
-                'ProductVariation',
+                ProductVariation::class,
                 '"ProductVariation_AttributeValues"."ProductVariationID" = "ProductVariation"."ID"'
             );
         if ($this->variationFilter) {

Warnings for src/Control/ProductWithVariationDecoratorController.php:
 - src/Control/ProductWithVariationDecoratorController.php:125 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 125

modified:	src/Form/CreateEcommerceVariationsField.php
@@ -2,12 +2,22 @@

 namespace Sunnysideup\EcommerceProductVariation\Form;

-use LiteralField;
-use Requirements;
-use Convert;
-use CheckboxField;
-use TextField;
+
+
+
+
+
 use DataObjectSorterController;
+use SilverStripe\View\Requirements;
+use Sunnysideup\EcommerceProductVariation\Form\CreateEcommerceVariationsField;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\Core\Convert;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use SilverStripe\Forms\CheckboxField;
+use SilverStripe\Forms\TextField;
+use SilverStripe\Forms\LiteralField;
+



@@ -25,22 +35,22 @@
   * EXP: Check that the template location is still valid!
   * ### @@@@ STOP REPLACEMENT @@@@ ###
   */
-        $additionalContent .= $this->RenderWith("CreateEcommerceVariationsField");
+        $additionalContent .= $this->RenderWith(CreateEcommerceVariationsField::class);
         parent::__construct($name, $additionalContent);
     }

     public function ProductVariationGetPluralName()
     {
-        return Convert::raw2att(singleton("ProductVariation")->plural_name());
+        return Convert::raw2att(singleton(ProductVariation::class)->plural_name());
     }

     public function ProductAttributeTypeGetPluralName()
     {
-        return Convert::raw2att(singleton("ProductAttributeType")->plural_name());
+        return Convert::raw2att(singleton(ProductAttributeType::class)->plural_name());
     }
     public function ProductAttributeValueGetPluralName()
     {
-        return Convert::raw2att(singleton("ProductAttributeValue")->plural_name());
+        return Convert::raw2att(singleton(ProductAttributeValue::class)->plural_name());
     }

     public function CheckboxField($name, $title)
@@ -54,7 +64,7 @@

     public function AttributeSorterLink()
     {
-        $singleton = singleton("ProductAttributeType");
+        $singleton = singleton(ProductAttributeType::class);
         if (class_exists("DataObjectSorterController") && $singleton->hasExtension("DataObjectSorterDOD")) {

 /**
@@ -65,12 +75,12 @@
   * EXP: Check if the class name can still be used as such
   * ### @@@@ STOP REPLACEMENT @@@@ ###
   */
-            return DataObjectSorterController::popup_link($className = "ProductAttributeType", $filterField = "", $filterValue = "", $linkText = "Sort Types");
+            return DataObjectSorterController::popup_link($className = ProductAttributeType::class, $filterField = "", $filterValue = "", $linkText = "Sort Types");
         }
     }
     public function ValueSorterLink()
     {
-        $singleton = singleton("ProductAttributeValue");
+        $singleton = singleton(ProductAttributeValue::class);
         if (class_exists("DataObjectSorterController") && $singleton->hasExtension("DataObjectSorterDOD")) {

 /**
@@ -81,7 +91,7 @@
   * EXP: Check if the class name can still be used as such
   * ### @@@@ STOP REPLACEMENT @@@@ ###
   */
-            return DataObjectSorterController::popup_link($className = "ProductAttributeValue", $filterField = "TypeChangeToId", $filterValue = "ID", $linkText = "Sort Values");
+            return DataObjectSorterController::popup_link($className = ProductAttributeValue::class, $filterField = "TypeChangeToId", $filterValue = "ID", $linkText = "Sort Values");
         }
     }
 }

modified:	src/Reports/EcommerceSideReport_ProductsWithVariations.php
@@ -2,10 +2,16 @@

 namespace Sunnysideup\EcommerceProductVariation\Reports;

-use SS_Report;
-use Versioned;
-use Product;
-use FieldList;
+
+
+
+
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\Versioned\Versioned;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Reports\Report;
+



@@ -17,13 +23,13 @@
  * @sub-package: reports
  * @inspiration: Silverstripe Ltd, Jeremy
  **/
-class EcommerceSideReport_ProductsWithVariations extends SS_Report
+class EcommerceSideReport_ProductsWithVariations extends Report
 {
     /**
      * The class of object being managed by this report.
      * Set by overriding in your subclass.
      */
-    protected $dataClass = 'Product';
+    protected $dataClass = Product::class;

     /**
      * @return string
@@ -63,11 +69,11 @@
         if (Versioned::current_stage() == 'Live') {
             $stage = '_Live';
         }
-        if (class_exists('ProductVariation')) {
+        if (class_exists(ProductVariation::class)) {
             return Product::get()
                 ->where('"ProductVariation"."ID" IS NULL ')
                 ->sort('FullSiteTreeSort')
-                ->leftJoin('ProductVariation', '"ProductVariation"."ProductID" = "Product'.$stage.'"."ID"');
+                ->leftJoin(ProductVariation::class, '"ProductVariation"."ProductID" = "Product'.$stage.'"."ID"');
         } else {
             return Product::get();
         }

modified:	src/Tasks/EcommerceProductVariationTaskDeleteAll.php
@@ -2,8 +2,14 @@

 namespace Sunnysideup\EcommerceProductVariation\Tasks;

-use BuildTask;
-use DB;
+
+
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use SilverStripe\ORM\DB;
+use SilverStripe\Dev\BuildTask;
+


 class EcommerceProductVariationTaskDeleteAll extends BuildTask
@@ -13,11 +19,11 @@
     protected $description = "Deletes ALL variations and all associated data, careful.";

     protected $tableArray = array(
-        "ProductVariation",
+        ProductVariation::class,
         "ProductVariation_AttributeValues",
         "Product_VariationAttributes",
-        "ProductAttributeType",
-        "ProductAttributeValue"
+        ProductAttributeType::class,
+        ProductAttributeValue::class
     );

     public function run($request)

modified:	src/Tasks/EcommerceTaskCSVToVariations_EXT.php
@@ -2,7 +2,9 @@

 namespace Sunnysideup\EcommerceProductVariation\Tasks;

-use Extension;
+
+use SilverStripe\Core\Extension;
+




modified:	src/Tasks/EcommerceProductVariationTaskDeleteVariations_EXT.php
@@ -2,7 +2,10 @@

 namespace Sunnysideup\EcommerceProductVariation\Tasks;

-use Extension;
+
+use Sunnysideup\EcommerceProductVariation\Tasks\EcommerceProductVariationTaskDeleteVariations;
+use SilverStripe\Core\Extension;
+



@@ -30,7 +33,7 @@

     public function ecommerceproductvariationtaskdeletevariations($request)
     {
-        $this->owner->runTask("EcommerceProductVariationTaskDeleteVariations", $request);
+        $this->owner->runTask(EcommerceProductVariationTaskDeleteVariations::class, $request);
     }
 }


modified:	src/Tasks/EcommerceProductVariationTaskDeleteVariations.php
@@ -2,11 +2,17 @@

 namespace Sunnysideup\EcommerceProductVariation\Tasks;

-use BuildTask;
-use Product;
-use DB;
-use ArrayList;
-use ProductVariation;
+
+
+
+
+
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\ORM\DB;
+use SilverStripe\ORM\ArrayList;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\Dev\BuildTask;
+


 class EcommerceProductVariationTaskDeleteVariations extends BuildTask

modified:	src/Tasks/EcommerceTaskCSVToVariations.php
@@ -2,14 +2,24 @@

 namespace Sunnysideup\EcommerceProductVariation\Tasks;

-use BuildTask;
-use DataObject;
+
+
 use ProductPage;
-use ProductAttributeType;
-use ProductAttributeValue;
-use ProductVariation;
-use Director;
-use DB;
+
+
+
+
+
+use SilverStripe\ORM\DataObject;
+use Sunnysideup\Ecommerce\Pages\ProductGroup;
+use Sunnysideup\Ecommerce\Pages\Product;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\Control\Director;
+use SilverStripe\ORM\DB;
+use SilverStripe\Dev\BuildTask;
+



@@ -277,7 +287,7 @@
                     $this->defaultProductParentID = $product->ParentID;
                 } elseif (!$this->defaultProductParentID) {
                     $this->defaultProductParentID = DataObject::get_one(
-                        'ProductGroup',
+                        ProductGroup::class,
                         null,
                         $cacheDataObjectGetOne = false
                     )->ID;
@@ -317,7 +327,7 @@
     {
         $this->alterationMessage("================================================ FINDING VARIATIONS ================================================", "show");
         foreach ($this->data as $productKey => $data) {
-            $product = $data["Product"];
+            $product = $data[Product::class];
             $title = $product->Title;
             $internalItemID = $product->InternalItemID;
             foreach ($this->csv as $key => $row) {
@@ -354,12 +364,12 @@
     {
         echo "<h2>Variation Summary</h2>";
         foreach ($this->data as $productKey => $value) {
-            if (isset($value["Product"]) && $value["Product"]) {
-                $this->data[$productKey]["Product"] = $value["Product"]->Title.", ID: ".$value["Product"]->ID;
+            if (isset($value[Product::class]) && $value[Product::class]) {
+                $this->data[$productKey][Product::class] = $value[Product::class]->Title.", ID: ".$value[Product::class]->ID;
             } else {
-                $this->data[$productKey]["Product"] = "Not found";
-            }
-            $this->alterationMessage($this->data[$productKey]["Product"].", variations: ".count($this->data[$productKey]["VariationRows"]), "created");
+                $this->data[$productKey][Product::class] = "Not found";
+            }
+            $this->alterationMessage($this->data[$productKey][Product::class].", variations: ".count($this->data[$productKey]["VariationRows"]), "created");
         }
         echo "<h2>Products without variations</h2>";
         foreach ($this->soleProduct as $productKey => $value) {
@@ -382,7 +392,7 @@
         foreach ($this->data as $data) {
             $types = [];
             $values = [];
-            $product = $data["Product"];
+            $product = $data[Product::class];
             $arrayForCreation = [];
             $variationFilter = [];
             $this->alterationMessage("<h1>Working out variations for ".$product->Title."</h1>");
@@ -390,10 +400,10 @@
             $this->alterationMessage("....Creating attribute types");
             foreach ($this->Config()->get("attribute_type_field_names") as $fieldKey => $fieldName) {
                 $startMessage = "........Checking field $fieldName";
-                $attributeTypeName = trim($data["Product"]->Title)."_".$fieldName;
+                $attributeTypeName = trim($data[Product::class]->Title)."_".$fieldName;
                 $filterArray = array("Name" => $attributeTypeName);
                 $type = DataObject::get_one(
-                    'ProductAttributeType',
+                    ProductAttributeType::class,
                     $filterArray,
                     $cacheDataObjectGetOne = false
                 );
@@ -427,7 +437,7 @@
                     $attributeValueName = $row["Data"][$fieldName];
                     $filterArray = array("Code" => $attributeValueName, "TypeID" => $types[$fieldName]->ID);
                     $value = DataObject::get_one(
-                        'ProductAttributeValue',
+                        ProductAttributeValue::class,
                         $filterArray,
                         $cacheDataObjectGetOne = false
                     );
@@ -527,7 +537,7 @@
     {
         $this->alterationMessage("================================================ ADDING EXTRA DATA ================================================", "show");
         foreach ($this->data as $productData) {
-            $product = $productData["Product"];
+            $product = $productData[Product::class];
             $this->alterationMessage("<h1>Adding extra data for ".$product->Title." with ".(count($productData["VariationRows"]))."</h1>"." Variations");
             foreach ($productData["VariationRows"] as $key => $row) {
                 $variation = $row["Variation"];

Warnings for src/Tasks/EcommerceTaskCSVToVariations.php:
 - src/Tasks/EcommerceTaskCSVToVariations.php:499 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 499

modified:	src/Tasks/EcommerceProductVariationTaskDeleteAll_EXT.php
@@ -2,7 +2,9 @@

 namespace Sunnysideup\EcommerceProductVariation\Tasks;

-use Extension;
+
+use SilverStripe\Core\Extension;
+




modified:	src/Model/TypesAndValues/ProductAttributeType.php
@@ -2,18 +2,35 @@

 namespace Sunnysideup\EcommerceProductVariation\Model\TypesAndValues;

-use DataObject;
-use EditableEcommerceObject;
-use GridFieldConfigForOrderItems;
-use OptionalTreeDropdownField;
-use DropdownField;
-use ReadonlyField;
-use Controller;
-use Director;
-use ArrayList;
-use HiddenField;
-use DB;
-use Injector;
+
+
+
+
+
+
+
+
+
+
+
+
+use SilverStripe\CMS\Model\SiteTree;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\Forms\DropdownField;
+use SilverStripe\ORM\DataObject;
+use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldConfigForOrderItems;
+use Sunnysideup\Ecommerce\Forms\Fields\OptionalTreeDropdownField;
+use SilverStripe\Forms\ReadonlyField;
+use SilverStripe\Control\Director;
+use SilverStripe\Control\Controller;
+use SilverStripe\ORM\ArrayList;
+use SilverStripe\Forms\HiddenField;
+use SilverStripe\ORM\DB;
+use SilverStripe\Core\Injector\Injector;
+use Sunnysideup\Ecommerce\Interfaces\EditableEcommerceObject;
+


 /**
@@ -70,15 +87,15 @@
      * Standard SS variable.
      */
     private static $has_one = array(
-        'MoreInfoLink' => 'SiteTree',
-        'MergeInto' => 'ProductAttributeType'
+        'MoreInfoLink' => SiteTree::class,
+        'MergeInto' => ProductAttributeType::class
     );

     /**
      * Standard SS variable.
      */
     private static $has_many = array(
-        'Values' => 'ProductAttributeValue'
+        'Values' => ProductAttributeValue::class
     );

     /**
@@ -100,7 +117,7 @@
      * Standard SS variable.
      */
     private static $belongs_many_many = array(
-        'Products' => 'Product'
+        'Products' => Product::class
     );

     /**
@@ -122,7 +139,7 @@
      */
     private static $default_sort = "\"Sort\" ASC, \"Name\"";

-    private static $dropdown_field_for_orderform = 'DropdownField';
+    private static $dropdown_field_for_orderform = DropdownField::class;

     /**
      * Standard SS variable.
@@ -143,7 +160,7 @@
     }
     public static function get_plural_name()
     {
-        $obj = Singleton("ProductAttributeType");
+        $obj = Singleton(ProductAttributeType::class);
         return $obj->i18n_plural_name();
     }

@@ -159,7 +176,7 @@
     {
         $name = strtolower($name);
         $type = DataObject::get_one(
-            'ProductAttributeType',
+            ProductAttributeType::class,
             'LOWER("Name") = \''.$name.'\'',
             $cacheDataObjectGetOne = false
         );
@@ -194,7 +211,7 @@
             new OptionalTreeDropdownField(
                 "MoreInfoLinkID",
                 _t("ProductAttributeType.MORE_INFO_LINK", "More info page"),
-                "SiteTree"
+                SiteTree::class
             )
         );
         //TODO: make this a really fast editing interface. Table list field??
@@ -506,8 +523,8 @@
             $this->Name.', '.
             $this->Label.
             ' ('.
-                $this->Values()->count().' '.Injector::inst()->get('ProductAttributeValue')->i18n_plural_name().', '.
-                $this->Products()->count().' '.Injector::inst()->get('Product')->i18n_plural_name().
+                $this->Values()->count().' '.Injector::inst()->get(ProductAttributeValue::class)->i18n_plural_name().', '.
+                $this->Products()->count().' '.Injector::inst()->get(Product::class)->i18n_plural_name().
             ')';
     }
 }

Warnings for src/Model/TypesAndValues/ProductAttributeType.php:
 - src/Model/TypesAndValues/ProductAttributeType.php:284 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 284

modified:	src/Model/TypesAndValues/ProductAttributeValue.php
@@ -2,14 +2,26 @@

 namespace Sunnysideup\EcommerceProductVariation\Model\TypesAndValues;

-use DataObject;
-use EditableEcommerceObject;
-use DB;
-use GridFieldConfigForOrderItems;
-use DropdownField;
-use ReadonlyField;
-use Controller;
-use Director;
+
+
+
+
+
+
+
+
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\ORM\DataObject;
+use SilverStripe\ORM\DB;
+use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldConfigForOrderItems;
+use SilverStripe\Forms\DropdownField;
+use SilverStripe\Forms\ReadonlyField;
+use SilverStripe\Control\Director;
+use SilverStripe\Control\Controller;
+use Sunnysideup\Ecommerce\Interfaces\EditableEcommerceObject;
+


 class ProductAttributeValue extends DataObject implements EditableEcommerceObject
@@ -47,12 +59,12 @@
     );

     private static $has_one = array(
-        'Type' => 'ProductAttributeType',
-        'MergeInto' => 'ProductAttributeValue'
+        'Type' => ProductAttributeType::class,
+        'MergeInto' => ProductAttributeValue::class
     );

     private static $belongs_many_many = array(
-        'ProductVariation' => 'ProductVariation'
+        'ProductVariation' => ProductVariation::class
     );

     private static $summary_fields = array(
@@ -101,7 +113,7 @@
                 ->first();
         } else {
             $valueObj = DataObject::get_one(
-                'ProductAttributeValue',
+                ProductAttributeValue::class,
                 "(LOWER(\"Code\") = '$cleanedValue' OR LOWER(\"Value\") = '$cleanedValue') AND TypeID = ".intval($type),
                 $cacheDataObjectGetOne = false
             );
@@ -155,7 +167,7 @@
     public function getCMSFields()
     {
         $fields = parent::getCMSFields();
-        $variationField = $fields->dataFieldByName('ProductVariation');
+        $variationField = $fields->dataFieldByName(ProductVariation::class);
         if ($variationField) {
             $variationField->setConfig(new GridFieldConfigForOrderItems());
         }

modified:	src/Model/Process/ProductVariation_OrderItem.php
@@ -2,7 +2,9 @@

 namespace Sunnysideup\EcommerceProductVariation\Model\Process;

-use ProductOrderItem;
+
+use Sunnysideup\Ecommerce\Model\ProductOrderItem;
+




modified:	src/Model/Buyables/ProductWithVariationDecorator.php
@@ -2,31 +2,58 @@

 namespace Sunnysideup\EcommerceProductVariation\Model\Buyables;

-use DataExtension;
-use FieldList;
-use Tab;
-use GridField;
-use GridFieldConfig_RecordEditor;
-use CreateEcommerceVariationsField;
-use LabelField;
-use EcommerceProductVariationTaskDeleteVariations;
-use LiteralField;
-use Convert;
+
+
+
+
+
+
+
+
+
+
 use DataObjectOneFieldUpdateController;
-use Config;
-use GridFieldConfig;
-use GridFieldToolbarHeader;
-use GridFieldSortableHeader;
-use GridFieldFilterHeader;
-use GridFieldEditButton;
-use GridFieldPaginator;
-use GridFieldDetailForm;
+
+
+
+
+
+
+
+
 use GridFieldEditableColumns;
-use EcommerceCurrency;
-use ProductAttributeValue;
-use DB;
-use ProductAttributeType;
-use Versioned;
+
+
+
+
+
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
+use SilverStripe\Forms\GridField\GridField;
+use Sunnysideup\EcommerceProductVariation\Form\CreateEcommerceVariationsField;
+use SilverStripe\Forms\Tab;
+use SilverStripe\Forms\GridField\GridFieldAddNewButton;
+use SilverStripe\Forms\LabelField;
+use Sunnysideup\EcommerceProductVariation\Tasks\EcommerceProductVariationTaskDeleteVariations;
+use SilverStripe\Core\Convert;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Core\Config\Config;
+use SilverStripe\Forms\GridField\GridFieldConfig;
+use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
+use SilverStripe\Forms\GridField\GridFieldSortableHeader;
+use SilverStripe\Forms\GridField\GridFieldFilterHeader;
+use SilverStripe\Forms\GridField\GridFieldEditButton;
+use SilverStripe\Forms\GridField\GridFieldPaginator;
+use SilverStripe\Forms\GridField\GridFieldDetailForm;
+use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use SilverStripe\ORM\DB;
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\Versioned\Versioned;
+use SilverStripe\ORM\DataExtension;
+


 /**
@@ -61,14 +88,14 @@
     private static $table_name = 'ProductWithVariationDecorator';

     private static $has_many = array(
-        'Variations' => 'ProductVariation',
+        'Variations' => ProductVariation::class,
     );

     /**
      * standard SS Var.
      */
     private static $many_many = array(
-        'VariationAttributes' => 'ProductAttributeType',
+        'VariationAttributes' => ProductAttributeType::class,
     );

     /**
@@ -103,7 +130,7 @@
   * EXP: This has been replaced to avoid confusions with replacements of className / class
   * ### @@@@ STOP REPLACEMENT @@@@ ###
   */
-    protected $MyClassnameOfVariations = 'ProductVariation';
+    protected $MyClassnameOfVariations = ProductVariation::class;

     /**
      * returns what class do we use for Variations.
@@ -247,7 +274,7 @@
      */
     public function updateCMSFields(FieldList $fields)
     {
-        $tabName = singleton('ProductVariation')->plural_name();
+        $tabName = singleton(ProductVariation::class)->plural_name();
         $priceField = $fields->dataFieldByName("Price");
         $fields->addFieldToTab(
             'Root',
@@ -255,7 +282,7 @@
                 $tabName,
                 new GridField(
                     'VariationAttributes',
-                    singleton('ProductAttributeType')->plural_name(),
+                    singleton(ProductAttributeType::class)->plural_name(),
                     $this->owner->VariationAttributes(),
                     $variationAttributesConfig = GridFieldConfig_RecordEditor::create()
                 ),
@@ -263,10 +290,10 @@
                 new CreateEcommerceVariationsField('VariationMaker', '', $this->owner->ID)
             )
         );
-        $variationAttributesConfig->removeComponentsByType('GridFieldAddNewButton');
+        $variationAttributesConfig->removeComponentsByType(GridFieldAddNewButton::class);
         $variations = $this->owner->Variations();
         if ($variations && $variations->Count()) {
-            $productVariationName = singleton('ProductVariation')->plural_name();
+            $productVariationName = singleton(ProductVariation::class)->plural_name();
             $fields->addFieldToTab(
                 'Root.Details',
                 new LabelField(
@@ -298,21 +325,21 @@
             }
             if (class_exists('DataObjectOneFieldUpdateController')) {
                 $linkForAllowSale = DataObjectOneFieldUpdateController::popup_link(
-                    'ProductVariation',
+                    ProductVariation::class,
                     'AllowPurchase',
                     "ProductID = {$this->owner->ID}",
                     '',
                     _t('ProductVariation.QUICK_UPDATE_VARIATION_ALLOW_PURCHASE', 'for sale')
                 );
                 $linkForPrice = DataObjectOneFieldUpdateController::popup_link(
-                    'ProductVariation',
+                    ProductVariation::class,
                     'Price',
                     "ProductID = {$this->owner->ID}",
                     '',
                     _t('ProductVariation.QUICK_UPDATE_VARIATION_PRICES', 'prices')
                 );
                 $linkForProductCodes = DataObjectOneFieldUpdateController::popup_link(
-                    'ProductVariation',
+                    ProductVariation::class,
                     'InternalItemID',
                     "ProductID = {$this->owner->ID}",
                     '',
@@ -343,10 +370,10 @@
     public function getVariationsTable()
     {
         if (class_exists('GridFieldEditableColumns')) {
-            $oldSummaryFields = Config::inst()->get('ProductVariation', 'summary_fields');
+            $oldSummaryFields = Config::inst()->get(ProductVariation::class, 'summary_fields');
             $oldSummaryFields['AllowPurchase'] = $oldSummaryFields['AllowPurchaseNice'];
             unset($oldSummaryFields['AllowPurchaseNice']);
-            Config::inst()->Update('ProductVariation', 'summary_fields', $oldSummaryFields);
+            Config::inst()->Update(ProductVariation::class, 'summary_fields', $oldSummaryFields);
             $gridFieldConfig = GridFieldConfig::create();
             $gridFieldConfig->addComponent(new GridFieldToolbarHeader());
             $gridFieldConfig->addComponent($sort = new GridFieldSortableHeader());
@@ -358,7 +385,7 @@
             $gridFieldConfig->addComponent(new GridFieldEditableColumns());
         } else {
             $gridFieldConfig = GridFieldConfig_RecordEditor::create();
-            $gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
+            $gridFieldConfig->removeComponentsByType(GridFieldAddNewButton::class);
         }
         $source = $this->owner->Variations();
         $types = $this->owner->VariationAttributes();
@@ -678,7 +705,7 @@
             "\"TypeID\" = '$attributeTypeObject->ID'"
         );
         $variations = $variations->innerJoin('ProductVariation_AttributeValues', '"ProductVariationID" = "ProductVariation"."ID"');
-        $variations = $variations->innerJoin('ProductAttributeValue', '"ProductAttributeValue"."ID" = "ProductAttributeValueID"');
+        $variations = $variations->innerJoin(ProductAttributeValue::class, '"ProductAttributeValue"."ID" = "ProductAttributeValueID"');

         return $variations->Count() == 0;
     }
@@ -754,7 +781,7 @@
     public function onBeforeDelete()
     {
         parent::onBeforeDelete();
-        if (Versioned::get_by_stage('Product', 'Stage', 'Product.ID ='.$this->owner->ID)->count() == 0) {
+        if (Versioned::get_by_stage(Product::class, 'Stage', 'Product.ID ='.$this->owner->ID)->count() == 0) {
             $variations = $this->owner->Variations();
             foreach ($variations as $variation) {
                 if ($variation->canDelete()) {

Warnings for src/Model/Buyables/ProductWithVariationDecorator.php:
 - src/Model/Buyables/ProductWithVariationDecorator.php:567 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 567

modified:	src/Model/Buyables/ProductVariation.php
@@ -2,39 +2,79 @@

 namespace Sunnysideup\EcommerceProductVariation\Model\Buyables;

-use DataObject;
-use BuyableModel;
-use EditableEcommerceObject;
-use Injector;
-use Config;
-use Product;
-use CMSEditLinkField;
-use ReadonlyField;
-use DropdownField;
-use FieldList;
-use TabSet;
-use Tab;
-use NumericField;
-use CheckboxField;
-use TextField;
-use ProductProductImageUploadField;
-use LiteralField;
-use Controller;
-use Director;
-use ArrayList;
-use Convert;
-use ProductImage;
-use OrderItem;
-use ShoppingCart;
-use Order;
-use EcommerceConfig;
-use ShoppingCartController;
-use CheckoutPage;
-use EcomQuantityField;
-use EcommerceConfigAjax;
-use EcommerceDBConfig;
-use EcommerceCurrency;
-use Member;
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+use Sunnysideup\Ecommerce\Pages\Product;
+use Sunnysideup\Ecommerce\Filesystem\ProductImage;
+use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
+use SilverStripe\Core\Injector\Injector;
+use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
+use SilverStripe\Core\Config\Config;
+use Sunnysideup\CmsEditLinkField\Forms\Fields\CMSEditLinkField;
+use SilverStripe\Forms\ReadonlyField;
+use SilverStripe\Forms\DropdownField;
+use SilverStripe\Forms\NumericField;
+use SilverStripe\Forms\CheckboxField;
+use SilverStripe\Forms\Tab;
+use SilverStripe\Forms\TextField;
+use SilverStripe\Assets\Image;
+use Sunnysideup\Ecommerce\Forms\Fields\ProductProductImageUploadField;
+use SilverStripe\Forms\TabSet;
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Control\Director;
+use SilverStripe\Control\Controller;
+use SilverStripe\ORM\ArrayList;
+use SilverStripe\Core\Convert;
+use SilverStripe\ORM\DataObject;
+use SilverStripe\ErrorPage\ErrorPage;
+use Sunnysideup\Ecommerce\Model\OrderItem;
+use Sunnysideup\Ecommerce\Api\ShoppingCart;
+use Sunnysideup\EcommerceProductVariation\Model\Process\ProductVariation_OrderItem;
+use Sunnysideup\Ecommerce\Model\Order;
+use Sunnysideup\Ecommerce\Model\OrderAttribute;
+use Sunnysideup\Ecommerce\Control\ShoppingCartController;
+use Sunnysideup\Ecommerce\Config\EcommerceConfig;
+use Sunnysideup\Ecommerce\Pages\CheckoutPage;
+use Sunnysideup\Ecommerce\Forms\Fields\EcomQuantityField;
+use Sunnysideup\Ecommerce\Config\EcommerceConfigAjax;
+use Sunnysideup\Ecommerce\Model\Config\EcommerceDBConfig;
+use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;
+use SilverStripe\Security\Member;
+use Sunnysideup\Ecommerce\Interfaces\BuyableModel;
+use Sunnysideup\Ecommerce\Interfaces\EditableEcommerceObject;
+


 /**
@@ -96,15 +136,15 @@
      * Standard SS variable.
      */
     private static $has_one = array(
-        'Product' => 'Product',
-        'Image' => 'ProductImage',
+        'Product' => Product::class,
+        'Image' => ProductImage::class,
     );

     /**
      * Standard SS variable.
      */
     private static $many_many = array(
-        'AttributeValues' => 'ProductAttributeValue',
+        'AttributeValues' => ProductAttributeValue::class,
     );

     /**
@@ -219,7 +259,7 @@
     }
     public static function get_plural_name()
     {
-        $obj = Injector::inst()->get('ProductVariation');
+        $obj = Injector::inst()->get(ProductVariation::class);

         return $obj->i18n_plural_name();
     }
@@ -251,7 +291,7 @@
                 'BetweenTypeAndValue' => $betweenTypeAndValue,
                 'BetweenVariations' => $betweenVariations,
             );
-        Config::modify()->update('ProductVariation', 'current_style_option_code', $code);
+        Config::modify()->update(ProductVariation::class, 'current_style_option_code', $code);
     }

     /**
@@ -267,7 +307,7 @@

     public static function get_current_style_option_array()
     {
-        return self::$title_style_option[Config::inst()->get('ProductVariation', 'current_style_option_code')];
+        return self::$title_style_option[Config::inst()->get(ProductVariation::class, 'current_style_option_code')];
     }

     /**
@@ -291,7 +331,7 @@
   * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
   * ### @@@@ STOP REPLACEMENT @@@@ ###
   */
-        if (class_exists('CMSEditLinkField')) {
+        if (class_exists(CMSEditLinkField::class)) {

 /**
   * ### @@@@ START REPLACEMENT @@@@ ###
@@ -312,11 +352,11 @@
                 user_error('We recommend you install https://github.com/briceburg/silverstripe-pickerfield');
                 $productField = ReadonlyField::create(
                     'ProductIDTitle',
-                    _t('ProductVariation.PRODUCT', 'Product'),
+                    _t('ProductVariation.PRODUCT', Product::class),
                     $this->Product() ? $this->Product()->Title : _t('ProductVariation.NO_PRODUCT', 'none')
                 );
             } else {
-                $productField = new DropdownField('ProductID', _t('ProductVariation.PRODUCT', 'Product'), Product::get()->map('ID', 'Title')->toArray());
+                $productField = new DropdownField('ProductID', _t('ProductVariation.PRODUCT', Product::class), Product::get()->map('ID', 'Title')->toArray());
                 $productField->setEmptyString('(Select one)');
             }
         }
@@ -363,7 +403,7 @@
                     new TextField('Description', _t('ProductVariation.DESCRIPTION', 'Description (optional)'))
                 ),
                 new Tab(
-                    'Image',
+                    Image::class,

 /**
   * ### @@@@ START REPLACEMENT @@@@ ###
@@ -373,7 +413,7 @@
   * EXP: make sure that Image does not end up as Image::class where this is not required
   * ### @@@@ STOP REPLACEMENT @@@@ ###
   */
-                    new ProductProductImageUploadField('Image')
+                    new ProductProductImageUploadField(Image::class)
                 )
             )
         );
@@ -464,7 +504,7 @@
   * EXP: You may need to run the following class: https://github.com/sunnysideup/silverstripe-migration-task/blob/master/src/Tasks/FixSheaDawsonLink.php
   * ### @@@@ STOP REPLACEMENT @@@@ ###
   */
-                        if (class_exists('CMSEditLinkField')) {
+                        if (class_exists(CMSEditLinkField::class)) {

 /**
   * ### @@@@ START REPLACEMENT @@@@ ###
@@ -863,7 +903,7 @@
             $this->redirect($product->Link('viewversion/'.$product->ID.'/'.$version.'/'));
         } else {
             $page = DataObject::get_one(
-                'ErrorPage',
+                ErrorPage::class,
                 array('ErrorCode' => '404')
             );
             if ($page) {
@@ -940,7 +980,7 @@
     /**
      * @var string
      */
-    protected $defaultClassNameForOrderItem = 'ProductVariation_OrderItem';
+    protected $defaultClassNameForOrderItem = ProductVariation_OrderItem::class;

     /**
      * you can overwrite this function in your buyable items (such as Product).
@@ -993,8 +1033,8 @@
     public function getHasBeenSold()
     {
         $dataList = Order::get_datalist_of_orders_with_submit_record($onlySubmittedOrders = true, $includeCancelledOrders = false);
-        $dataList = $dataList->innerJoin('OrderAttribute', '"OrderAttribute"."OrderID" = "Order"."ID"');
-        $dataList = $dataList->innerJoin('OrderItem', '"OrderAttribute"."ID" = "OrderItem"."ID"');
+        $dataList = $dataList->innerJoin(OrderAttribute::class, '"OrderAttribute"."OrderID" = "Order"."ID"');
+        $dataList = $dataList->innerJoin(OrderItem::class, '"OrderAttribute"."ID" = "OrderItem"."ID"');
         $dataList = $dataList->filter(
             array(
                 'BuyableID' => $this->ID,
@@ -1043,7 +1083,7 @@
     {
         return Controller::join_links(
              Director::baseURL(),
-             EcommerceConfig::get('ShoppingCartController', 'url_segment'),
+             EcommerceConfig::get(ShoppingCartController::class, 'url_segment'),
              'submittedbuyable',

 /**

Warnings for src/Model/Buyables/ProductVariation.php:
 - src/Model/Buyables/ProductVariation.php:366 Renaming ambiguous string Image to SilverStripe\Assets\Image

 - src/Model/Buyables/ProductVariation.php:376 Renaming ambiguous string Image to SilverStripe\Assets\Image

Writing changes for 18 files
✔✔✔