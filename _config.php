<?php


/**
 * @author Nicolaas modules [at] sunnysideup.co.nz
 *
 **/


//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START ecommerce_products MODULE ----------------===================
//MUST SET
Object::add_extension("Product", "ProductWithVariationDecorator");

//MAY SET
//ProductsAndGroupsModelAdmin::set_managed_models(array(("Product", "ProductGroup","ProductVariation"));
//===================---------------- END ecommerce_products MODULE ----------------===================
