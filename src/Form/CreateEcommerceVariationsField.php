<?php

namespace Sunnysideup\EcommerceProductVariation\Form;






use DataObjectSorterController;
use SilverStripe\View\Requirements;
use Sunnysideup\EcommerceProductVariation\Form\CreateEcommerceVariationsField;
use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
use SilverStripe\Core\Convert;
use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;




class CreateEcommerceVariationsField extends LiteralField
{
    public function __construct($name, $additionalContent = '', $productID)
    {
        Requirements::themedCSS("sunnysideup/ecommerce_product_variation: CreateEcommerceVariationsField", "ecommerce_product_variation");

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: ->RenderWith( (ignore case)
  * NEW: ->RenderWith( (COMPLEX)
  * EXP: Check that the template location is still valid!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $additionalContent .= $this->RenderWith(CreateEcommerceVariationsField::class);
        parent::__construct($name, $additionalContent);
    }

    public function ProductVariationGetPluralName()
    {
        return Convert::raw2att(singleton(ProductVariation::class)->plural_name());
    }

    public function ProductAttributeTypeGetPluralName()
    {
        return Convert::raw2att(singleton(ProductAttributeType::class)->plural_name());
    }
    public function ProductAttributeValueGetPluralName()
    {
        return Convert::raw2att(singleton(ProductAttributeValue::class)->plural_name());
    }

    public function CheckboxField($name, $title)
    {
        return new CheckboxField($name, $title);
    }
    public function TextField($name, $title)
    {
        return new TextField($name, $title);
    }

    public function AttributeSorterLink()
    {
        $singleton = singleton(ProductAttributeType::class);
        if (class_exists("DataObjectSorterController") && $singleton->hasExtension("DataObjectSorterDOD")) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            return DataObjectSorterController::popup_link($className = ProductAttributeType::class, $filterField = "", $filterValue = "", $linkText = "Sort Types");
        }
    }
    public function ValueSorterLink()
    {
        $singleton = singleton(ProductAttributeValue::class);
        if (class_exists("DataObjectSorterController") && $singleton->hasExtension("DataObjectSorterDOD")) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            return DataObjectSorterController::popup_link($className = ProductAttributeValue::class, $filterField = "TypeChangeToId", $filterValue = "ID", $linkText = "Sort Values");
        }
    }
}

