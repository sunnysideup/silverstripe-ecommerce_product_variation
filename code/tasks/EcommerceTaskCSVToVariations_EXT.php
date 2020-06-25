<?php

class EcommerceTaskCSVToVariations_EXT extends Extension
{
    private static $allowed_actions = array(
        "ecommercetaskcsvtovariations" => true
    );

    //NOTE THAT updateEcommerceDevMenuConfig adds to Config options
    //but you can als have: updateEcommerceDevMenuDebugActions
    public function updateEcommerceDevMenuRegularMaintenance($buildTasks)
    {
        $buildTasks[] = "ecommercetaskcsvtovariations";
        return $buildTasks;
    }

    public function ecommercetaskcsvtovariations($request)
    {
        $this->owner->runTask("ecommercetaskcsvtovariations", $request);
    }
}
