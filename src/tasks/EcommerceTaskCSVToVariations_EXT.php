<?php


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD:  extends Extension (ignore case)
  * NEW:  extends Extension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
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
