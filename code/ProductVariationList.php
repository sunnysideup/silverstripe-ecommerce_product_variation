<?php

class ProductVariationList extends ProductPage {



}

class ProductVariationList_Controller extends ProductPage_Controller {


	function Products(){
		$products = Product::get();
		foreach($products as $product) {
			if(!$product->canPurchase()) {
			}
		}
	}

	function Variations(){
		return ProductVariation::get();
	}

}
