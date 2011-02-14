<?php

Director::addRules(50, array(
	'createecommercevariations/$Action/$ProductID' => 'CreateEcommerceVariations',
	'createecommercevariationsbatch/$Action' => 'CreateEcommerceVariations_Batch'
));

Object::add_extension("ProductVariation", "Buyable");
Object::add_extension("Product", "ProductWithVariationDecorator");
Object::add_extension("Product_Controller", "ProductWithVariationDecorator_Controller");
Product_Controller::$allowed_actions[] = 'VariationForm';
Product_Controller::$allowed_actions[] = 'addvariation';
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery/jquery.js");
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
LeftAndMain::require_javascript("ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js");
LeftAndMain::require_themed_css("CreateEcommerceVariationsField");

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
// __________________________________ START ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
//MAY SET
//ProductVariation::set_singular_name("Variation");
//ProductVariation::set_plural_name("Variations");
//ProductVariation::add_title_style_option($code = "minimal", $showType = true, $betweenTypeAndValue = ": ", $betweenVariations  =" / ");
//ProductAttributeType::set_singular_name("Types");
//ProductAttributeType::set_plural_name("Types");
//ProductAttributeValue::set_singular_name("Value");
//ProductAttributeValue::set_plural_name("Values");
//ProductsAndGroupsModelAdmin::add_managed_model("ProductAttributeValue");
//ProductsAndGroupsModelAdmin::add_managed_model("ProductAttributeType");
//LeftAndMain::require_javascript("mysite/javascript/MyCreateEcommerceVariationsField.js");
//Object::add_extension("ProductAttributeValue", "ProductAttributeDecoratorColour_Value");
//Object::add_extension("ProductAttributeType", "ProductAttributeDecoratorColour_Type");
//ProductAttributeDecoratorColour_Value::set_default_contrast_colour("FFFFFF");
//ProductAttributeDecoratorColour_Value::set_default_colour("000000");
// __________________________________ END ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
