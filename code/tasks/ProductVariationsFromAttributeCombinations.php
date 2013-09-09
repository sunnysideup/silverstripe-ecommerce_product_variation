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
		$link = "dataobjectsorter/";
		if($action) {
			$link .= "$action/";
		}
		return $link;
	}

}

