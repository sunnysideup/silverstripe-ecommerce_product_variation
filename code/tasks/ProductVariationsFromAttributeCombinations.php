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

	public function Link($action = null) {
		return Controller::join_links(
			Director::baseURL(), 
			"dataobjectsorter",
			$action
		);
	}

}

