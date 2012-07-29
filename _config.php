<?php

Director::addRules(50, array(
	'createecommercevariations/$Action/$ProductID' => 'CreateEcommerceVariations',
	'createecommercevariationsbatch/$Action' => 'CreateEcommerceVariations_Batch'
));

//Buyable::add_class("ProductVariation");
Object::add_extension("Product", "ProductWithVariationDecorator");
Object::add_extension("Product_Controller", "ProductWithVariationDecorator_Controller");
Object::add_extension("ProductBulkLoader","ProductVariationBulkLoader");

Product_Controller::$allowed_actions[] = 'VariationForm';
Product_Controller::$allowed_actions[] = 'addvariation';
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery/jquery.js");
//Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
LeftAndMain::require_javascript("ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js");
LeftAndMain::require_themed_css("CreateEcommerceVariationsField");

ProductsAndGroupsModelAdmin::$model_importers['ProductVariation'] = null;

SS_Report::register("SideReport", "EcommerceSideReport_ProductsWithVariations");

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings

// __________________________________ START ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
//____________HIGHLY RECOMMENDED
/**
 * ADD TO ECOMMERCE.YAML:
ProductsAndGroupsModelAdmin:
	managed_modules: [
		...
		ProductVariation,
		ProductAttributeValue,
		ProductAttributeType
	]
*/

//____________ADD TO CART FORM INTERACTION
//ProductWithVariationDecorator_Controller::set_use_js_validation(false);
//ProductWithVariationDecorator_Controller::set_alternative_validator_class_name("MyValidatorClass");
//____________EASY SORTING - REQUIRES: http://sunny.svnrepository.com/svn/sunny-side-up-general/dataobjectsorter
//Object::add_extension('ProductAttributeValue', 'DataObjectSorterDOD');
//Object::add_extension('ProductAttributeType', 'DataObjectSorterDOD');
//____________CUSTOMISED CMS INTERACTION
//LeftAndMain::require_javascript("mysite/javascript/MyCreateEcommerceVariationsField.js");
// ____________ IMPORTANT NAME CHANGES....
//$lang['en_US']['ProductVariation']['PRODUCTVARIATION']  = "Product Variation";
//$lang['en_US']['ProductVariation']['PRODUCTVARIATIONS']  = "Product Variations";
//$lang['en_US']['ProductAttributeValue']['ATTRIBUTEVALUE']  = "Product Attribute";
//$lang['en_US']['ProductAttributeValue']['ATTRIBUTEVALUES']  = "Product Attributes";
//$lang['en_US']['ProductAttributeType']['ATTRIBUTETYPE']  = "Product Attribute Type";
//$lang['en_US']['ProductAttributeType']['ATTRIBUTETYPES']  = "Product Attribute Types";
// __________________________________ END ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
