<?php



LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery/jquery.js");
//Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
LeftAndMain::require_javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
LeftAndMain::require_javascript("ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js");
LeftAndMain::require_themed_css("CreateEcommerceVariationsField", "ecommerce_product_variation");


//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings

// __________________________________ START ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________

//____________CUSTOMISED CMS INTERACTION
//LeftAndMain::require_javascript("mysite/javascript/MyCreateEcommerceVariationsField.js");

// ____________ IMPORTANT NAME CHANGES....
//

// __________________________________ END ECOMMERCE PRODUCT VARIATIONS MODULE CONFIG __________________________________
