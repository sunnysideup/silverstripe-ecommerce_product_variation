<?php

class EcommerceProductVariationTaskDeleteAll_EXT extends Extension
{
    private static $allowed_actions = array(
        "ecommerceproductvariationtaskdeletevariations" => true
    );

    //NOTE THAT updateEcommerceDevMenuConfig adds to Config options
    //but you can als have: updateEcommerceDevMenuDebugActions
    public function updateEcommerceDevMenuRegularMaintenance($buildTasks)
    {
        $buildTasks[] = "ecommerceproductvariationtaskdeleteall";
        return $buildTasks;
    }

    public function ecommerceproductvariationtaskdeleteall($request)
    {
        $this->owner->runTask("ecommerceproductvariationtaskdeleteall", $request);
    }
}
