<?php

Director::addRules(50, array(
	'createecommercevariations/$Action/$ProductID' => 'CreateEcommerceVariations'
));

Object::add_extension("ProductVariation", "Buyable");
Object::add_extension("Product", "ProductWithVariationDecorator");
Object::add_extension("Product_Controller", "ProductWithVariationDecorator_Controller");
Product_Controller::$allowed_actions[] = 'VariationForm';
Product_Controller::$allowed_actions[] = 'addvariation';

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
// __________________________________ START ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
//MAY SET
//ProductsAndGroupsModelAdmin::set_managed_models(array("Product", "ProductGroup","ProductVariation"));
//ProductVariation::set_singular_name("Variation");
//ProductVariation::set_plural_name("Variations");
//ProductAttributeType::set_singular_name("Types");
//ProductAttributeType::set_plural_name("Types");
//ProductAttributeValue::set_singular_name("Value");
//ProductAttributeValue::set_plural_name("Values");
//ProductsAndGroupsModelAdmin::add_managed_model("ProductAttributeValue");
//ProductsAndGroupsModelAdmin::add_managed_model("ProductAttributeType");
// __________________________________ END ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
