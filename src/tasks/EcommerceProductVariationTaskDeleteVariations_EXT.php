<?php

class EcommerceProductVariationTaskDeleteVariations_EXT extends Extension
{
    private static $allowed_actions = array(
        "ecommerceproductvariationtaskdeletevariations" => true
    );

    //NOTE THAT updateEcommerceDevMenuConfig adds to Config options
    //but you can als have: updateEcommerceDevMenuDebugActions
    public function updateEcommerceDevMenuRegularMaintenance($buildTasks)
    {
        $buildTasks[] = "ecommerceproductvariationtaskdeletevariations";
        return $buildTasks;
    }

    public function ecommerceproductvariationtaskdeletevariations($request)
    {
        $this->owner->runTask("EcommerceProductVariationTaskDeleteVariations", $request);
    }
}
