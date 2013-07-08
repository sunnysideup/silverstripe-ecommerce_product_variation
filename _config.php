<?php


Object::add_extension("Product", "ProductWithVariationDecorator");
Object::add_extension("Product_Controller", "ProductWithVariationDecorator_Controller");
Object::add_extension("EcommerceDatabaseAdmin","EcommerceProductVariationTaskDeleteVariations_EXT");


LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery/jquery.js");
//Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
LeftAndMain::require_javascript("ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js");
LeftAndMain::require_themed_css("CreateEcommerceVariationsField", "ecommerce_product_variation");

SS_Report::register("SideReport", "EcommerceSideReport_ProductsWithVariations");

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings

// __________________________________ START ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
//TO ADD TO ECOMMERCE.YAML FILE
/*
ProductsAndGroupsModelAdmin:
  managed_modules:
    - ProductVariation
    - ProductAttributeValue
    - ProductAttributeType

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
//




// __________________________________ END ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
