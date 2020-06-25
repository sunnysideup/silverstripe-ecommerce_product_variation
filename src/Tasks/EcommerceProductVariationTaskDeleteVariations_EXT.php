<?php

namespace Sunnysideup\EcommerceProductVariation\Tasks;


use Sunnysideup\EcommerceProductVariation\Tasks\EcommerceProductVariationTaskDeleteVariations;
use SilverStripe\Core\Extension;




/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD:  extends Extension (ignore case)
  * NEW:  extends Extension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
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
        $this->owner->runTask(EcommerceProductVariationTaskDeleteVariations::class, $request);
    }
}
