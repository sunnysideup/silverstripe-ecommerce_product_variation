<?php

class ProductVariationsFromAttributeCombinations extends CliController{

	function process(){

		$products = DataObject::get('Product');
		if(!$products) {
			return;
		}
		else {
			foreach($products as $product){
				$product->generateVariationsFromAttributes();
			}
		}
	}

}

