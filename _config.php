<?php

Director::addRules(50, array(
	'createecommercevariations/$Action/$ProductID' => 'CreateEcommerceVariations',
	'createecommercevariationsbatch/$Action' => 'CreateEcommerceVariations_Batch'
));

Buyable::add_class("ProductVariation");
Object::add_extension("Product", "ProductWithVariationDecorator");
Object::add_extension("Product_Controller", "ProductWithVariationDecorator_Controller");
Object::add_extension("ProductBulkLoader","ProductVariationBulkLoader");

Product_Controller::$allowed_actions[] = 'VariationForm';
Product_Controller::$allowed_actions[] = 'addvariation';
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery/jquery.js");
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
LeftAndMain::require_javascript("ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js");
LeftAndMain::require_themed_css("CreateEcommerceVariationsField");

ProductsAndGroupsModelAdmin::$model_importers['ProductVariation'] = null;

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
// __________________________________ START ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
//____________HIGHLY RECOMMENDED
//ProductsAndGroupsModelAdmin::add_managed_model("ProductAttributeValue");
//ProductsAndGroupsModelAdmin::add_managed_model("ProductAttributeType");
//____________ADVANCED OPTIONS
//LeftAndMain::require_javascript("mysite/javascript/MyCreateEcommerceVariationsField.js");
//Object::add_extension("ProductAttributeValue", "ProductAttributeDecoratorColour_Value");
//Object::add_extension("ProductAttributeType", "ProductAttributeDecoratorColour_Type");
//ProductAttributeDecoratorColour_Value::set_default_contrast_colour("FFFFFF");
//ProductAttributeDecoratorColour_Value::set_default_colour("000000");
//ProductWithVariationDecorator_Controller::set_use_js_validation(false);
//ProductWithVariationDecorator_Controller::set_alternative_validator_class_name("MyValidatorClass");
// ____________ IMPORTANT NAME CHANGES....
//$lang['en_US']['ProductVariation']['PRODUCTVARIATION']  = "Product Variation";
//$lang['en_US']['ProductVariation']['PRODUCTVARIATIONS']  = "Product Variations";
//$lang['en_US']['ProductAttributeValue']['ATTRIBUTEVALUE']  = "Product Attribute";
//$lang['en_US']['ProductAttributeValue']['ATTRIBUTEVALUES']  = "Product Attributes";
//$lang['en_US']['ProductAttributeType']['ATTRIBUTETYPE']  = "Product Attribute Type";
//$lang['en_US']['ProductAttributeType']['ATTRIBUTETYPES']  = "Product Attribute Types";
// __________________________________ END ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
