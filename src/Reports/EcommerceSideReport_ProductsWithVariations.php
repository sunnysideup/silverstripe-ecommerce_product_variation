<?php

namespace Sunnysideup\EcommerceProductVariation\Reports;





use Sunnysideup\Ecommerce\Pages\Product;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
use SilverStripe\Forms\FieldList;
use SilverStripe\Reports\Report;




/**
 * Products without variations.
 *
 * @authors: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: reports
 * @inspiration: Silverstripe Ltd, Jeremy
 **/
class EcommerceSideReport_ProductsWithVariations extends Report
{
    /**
     * The class of object being managed by this report.
     * Set by overriding in your subclass.
     */
    protected $dataClass = Product::class;

    /**
     * @return string
     */
    public function title()
    {
        return _t('EcommerceSideReport.PRODUCTSWITHVARIATIONS', 'E-commerce: Products without variations').
        ' ('.$this->sourceRecords()->count().')';
    }

    /**
     * not sure if this is used in SS3.
     *
     * @return string
     */
    public function group()
    {
        return _t('EcommerceSideReport.ECOMMERCEGROUP', 'Ecommerce');
    }

    /**
     * @return int - for sorting reports
     */
    public function sort()
    {
        return 7000;
    }

    /**
     * working out the items.
     *
     * @return DataList
     */
    public function sourceRecords($params = null)
    {
        $stage = '';
        if (Versioned::current_stage() == 'Live') {
            $stage = '_Live';
        }
        if (class_exists(ProductVariation::class)) {
            return Product::get()
                ->where('"ProductVariation"."ID" IS NULL ')
                ->sort('FullSiteTreeSort')
                ->leftJoin(ProductVariation::class, '"ProductVariation"."ProductID" = "Product'.$stage.'"."ID"');
        } else {
            return Product::get();
        }
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'Title' => array(
                'title' => 'FullName',
                'link' => true,
            ),
        );
    }

    /**
     * @return FieldList
     */
    public function getParameterFields()
    {
        return new FieldList();
    }
}
