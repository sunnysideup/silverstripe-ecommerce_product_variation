<?php


class ProductVariation_OrderItem extends Product_OrderItem
{

    // ProductVariation Access Function
    public function ProductVariation($current = false)
    {
        return $this->Buyable($current);
    }

    /**
     * @decription: we return the product name here -
     * leaving the Table Sub Title for the name of the variation
     *
     * @return String - title in cart.
     */
    public function TableTitle()
    {
        return $this->getTableTitle();
    }
    public function getTableTitle()
    {
        $tableTitle = _t("Product.UNKNOWN", "Unknown Product");
        if ($variation = $this->ProductVariation()) {
            if ($product = $variation->Product()) {
                $tableTitle = $product->Title;
            }
        }
        $extendedTitle = $this->extend('updateTableTitle', $tableTitle);
        if ($extendedTitle !== null && is_array($extendedTitle) && count($extendedTitle)) {
            return implode("", $extendedTitle);
        }

        return $tableTitle;
    }

    /**
     * we return the product variation name here
     * the Table Title will return the name of the Product.
     * @return String - sub title in cart.
     **/
    public function TableSubTitle()
    {
        return $this->getTableSubTitle();
    }
    public function getTableSubTitle()
    {
        $tableSubTitle = _t("Product.VARIATIONNOTFOUND", "Variation Not Found");
        if ($variation = $this->ProductVariation()) {
            if ($variation->exists()) {
                $tableSubTitle = $variation->getTitle(true, true);
            }
        }
        $extendedSubTitle = $this->extend('updateTableSubTitle', $tableSubTitle);
        if ($extendedSubTitle !== null && is_array($extendedSubTitle) && count($extendedSubTitle)) {
            return implode("", $extendedSubTitle);
        }
        return $tableSubTitle;
    }


    /**
     * Check if this variation is new - that is, if it has yet to have been written
     * to the database.
     *
     * @return boolean True if this is new.
     */
    public function isNew()
    {
        /**
         * This check was a problem for a self-hosted site, and may indicate a
         * bug in the interpreter on their server, or a bug here
         * Changing the condition from empty($this->ID) to
         * !$this->ID && !$this->record['ID'] fixed this.
         */
        if (empty($this->ID)) {
            return true;
        }
        if (is_numeric($this->ID)) {
            return false;
        }
        return stripos($this->ID, 'new') === 0;
    }
}

