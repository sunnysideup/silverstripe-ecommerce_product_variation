2020-06-26 07:45

# running php upgrade inspect see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_product_variation
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code inspect /var/www/upgrades/ecommerce_product_variation/ecommerce_product_variation/src  --root-dir=/var/www/upgrades/ecommerce_product_variation --write -vvv
Writing changes for 1 files
Running post-upgrade on "/var/www/upgrades/ecommerce_product_variation/ecommerce_product_variation/src"
[2020-06-26 07:45:56] Applying ApiChangeWarningsRule to CreateEcommerceVariations.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to CreateEcommerceVariations.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to ProductWithVariationDecoratorController.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to ProductWithVariationDecoratorController.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to CreateEcommerceVariationsField.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to CreateEcommerceVariationsField.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to EcommerceSideReport_ProductsWithVariations.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to EcommerceSideReport_ProductsWithVariations.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteAll.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteAll.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to ProductVariationsFromAttributeCombinations.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to ProductVariationsFromAttributeCombinations.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to EcommerceTaskCSVToVariations_EXT.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to EcommerceTaskCSVToVariations_EXT.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteVariations_EXT.php...
[2020-06-26 07:45:57] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteVariations_EXT.php...
[2020-06-26 07:45:57] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteVariations.php...
[2020-06-26 07:45:58] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteVariations.php...
[2020-06-26 07:45:58] Applying ApiChangeWarningsRule to EcommerceTaskCSVToVariations.php...
[2020-06-26 07:45:58] Applying UpdateVisibilityRule to EcommerceTaskCSVToVariations.php...
[2020-06-26 07:45:59] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteAll_EXT.php...
[2020-06-26 07:45:59] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteAll_EXT.php...
[2020-06-26 07:45:59] Applying ApiChangeWarningsRule to ProductAttributeType.php...
[2020-06-26 07:45:59] Applying UpdateVisibilityRule to ProductAttributeType.php...
[2020-06-26 07:45:59] Applying ApiChangeWarningsRule to ProductAttributeValue.php...
[2020-06-26 07:45:59] Applying UpdateVisibilityRule to ProductAttributeValue.php...
[2020-06-26 07:45:59] Applying ApiChangeWarningsRule to ProductVariation_OrderItem.php...
[2020-06-26 07:45:59] Applying UpdateVisibilityRule to ProductVariation_OrderItem.php...
[2020-06-26 07:45:59] Applying ApiChangeWarningsRule to ProductWithVariationDecorator.php...
[2020-06-26 07:46:00] Applying UpdateVisibilityRule to ProductWithVariationDecorator.php...
[2020-06-26 07:46:00] Applying ApiChangeWarningsRule to ProductVariation.php...
[2020-06-26 07:46:01] Applying UpdateVisibilityRule to ProductVariation.php...
modified:	Reports/EcommerceSideReport_ProductsWithVariations.php
@@ -66,7 +66,7 @@
     public function sourceRecords($params = null)
     {
         $stage = '';
-        if (Versioned::current_stage() == 'Live') {
+        if (Versioned::get_stage() == 'Live') {
             $stage = '_Live';
         }
         if (class_exists(ProductVariation::class)) {

Warnings for Reports/EcommerceSideReport_ProductsWithVariations.php:
 - Reports/EcommerceSideReport_ProductsWithVariations.php:69 SilverStripe\Versioned\Versioned::current_stage(): Moved to SilverStripe\Versioned\Versioned::get_stage()
Writing changes for 1 files
✔✔✔
# running php upgrade inspect see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_product_variation
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code inspect /var/www/upgrades/ecommerce_product_variation/ecommerce_product_variation/src  --root-dir=/var/www/upgrades/ecommerce_product_variation --write -vvv
Writing changes for 0 files
Running post-upgrade on "/var/www/upgrades/ecommerce_product_variation/ecommerce_product_variation/src"
[2020-06-26 07:46:34] Applying ApiChangeWarningsRule to CreateEcommerceVariations.php...
[2020-06-26 07:46:34] Applying UpdateVisibilityRule to CreateEcommerceVariations.php...
[2020-06-26 07:46:34] Applying ApiChangeWarningsRule to ProductWithVariationDecoratorController.php...
[2020-06-26 07:46:34] Applying UpdateVisibilityRule to ProductWithVariationDecoratorController.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to CreateEcommerceVariationsField.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to CreateEcommerceVariationsField.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to EcommerceSideReport_ProductsWithVariations.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to EcommerceSideReport_ProductsWithVariations.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteAll.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteAll.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to ProductVariationsFromAttributeCombinations.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to ProductVariationsFromAttributeCombinations.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to EcommerceTaskCSVToVariations_EXT.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to EcommerceTaskCSVToVariations_EXT.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteVariations_EXT.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteVariations_EXT.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteVariations.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteVariations.php...
[2020-06-26 07:46:35] Applying ApiChangeWarningsRule to EcommerceTaskCSVToVariations.php...
[2020-06-26 07:46:35] Applying UpdateVisibilityRule to EcommerceTaskCSVToVariations.php...
[2020-06-26 07:46:36] Applying ApiChangeWarningsRule to EcommerceProductVariationTaskDeleteAll_EXT.php...
[2020-06-26 07:46:36] Applying UpdateVisibilityRule to EcommerceProductVariationTaskDeleteAll_EXT.php...
[2020-06-26 07:46:36] Applying ApiChangeWarningsRule to ProductAttributeType.php...
[2020-06-26 07:46:36] Applying UpdateVisibilityRule to ProductAttributeType.php...
[2020-06-26 07:46:36] Applying ApiChangeWarningsRule to ProductAttributeValue.php...
[2020-06-26 07:46:37] Applying UpdateVisibilityRule to ProductAttributeValue.php...
[2020-06-26 07:46:37] Applying ApiChangeWarningsRule to ProductVariation_OrderItem.php...
[2020-06-26 07:46:37] Applying UpdateVisibilityRule to ProductVariation_OrderItem.php...
[2020-06-26 07:46:37] Applying ApiChangeWarningsRule to ProductWithVariationDecorator.php...
[2020-06-26 07:46:37] Applying UpdateVisibilityRule to ProductWithVariationDecorator.php...
[2020-06-26 07:46:38] Applying ApiChangeWarningsRule to ProductVariation.php...
[2020-06-26 07:46:38] Applying UpdateVisibilityRule to ProductVariation.php...
Writing changes for 0 files
✔✔✔