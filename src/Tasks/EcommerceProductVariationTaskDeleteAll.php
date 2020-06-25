<?php

namespace Sunnysideup\EcommerceProductVariation\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;

class EcommerceProductVariationTaskDeleteAll extends BuildTask
{
    protected $title = 'Deletes all the variations and associated data';

    protected $description = 'Deletes ALL variations and all associated data, careful.';

    protected $tableArray = [
        ProductVariation::class,
        'ProductVariation_AttributeValues',
        'Product_VariationAttributes',
        ProductAttributeType::class,
        ProductAttributeValue::class,
    ];

    public function run($request)
    {
        $productVariationArrayID = [];
        if (empty($_GET['live'])) {
            $live = false;
        } else {
            $live = intval($_GET['live']) === 1 ? true : false;
        }
        if ($live) {
            DB::alteration_message('this is a live task', 'deleted');
        } else {
            DB::alteration_message('this is a test only. If you add a live=1 get variable then you can make it for real ;-)', 'created');
        }
        foreach ($this->tableArray as $table) {
            $sql = "DELETE FROM \"${table}\"";
            DB::alteration_message("<pre>DELETING FROM ${table}: <br /><br />" . $sql . '</pre>');
            if ($live) {
                DB::query($sql);
            }
            $sql = "SELECT COUNT(ID) FROM \"${table}\"";
            $count = DB::query($sql)->value();
            if ($count === 0) {
                $style = 'created';
            } else {
                $style = 'deleted';
            }
            DB::alteration_message(' **** COMPLETED, NUMBER OF REMAINING RECORD: ' . $count . ' **** ', $style);
        }
    }
}
