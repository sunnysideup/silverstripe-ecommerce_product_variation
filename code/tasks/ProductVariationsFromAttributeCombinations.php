<?php

class ProductVariationsFromAttributeCombinations extends CliController{

	function process(){
		$products = Product::get();
		if(!$products->count()) {
			return;
		}
		else {
			foreach($products as $product){
				$product->generateVariationsFromAttributes();
			}
		}
	}

}

