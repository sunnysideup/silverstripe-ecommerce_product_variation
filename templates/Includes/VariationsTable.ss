<table class="quantityTable" summary="<% _t("PRODUCTOPTIONS","Product Options") %>">
    <thead>
        <tr>
            <th scope="col" class="description"><% _t("Product.VARIATIONDESCRIPTIONLABEL", "") %>&nbsp;</th>
<% if VariationAttributes %>
    <% loop VariationAttributes %>
            <th scope="col" class="label">$Label</th>
    <% end_loop %>
        <% else %>
            <th scope="col" class="label">Variation</th>
<% end_if %>
            <th scope="col" class="price"><% _t("PRICE","Price") %> ($Cart.CurrencyUsed.Code.UpperCase)</th>
            <th scope="col" class="actionCell">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
<% loop Variations %>
        <tr>
            <th scope="row" class="<% if Description %>hasDescription<% else %>noDescription<% end_if %>">
                <% include ProductVariationImage %>
                $Description &nbsp;
            </th>
    <% if AttributeValuesSorted %>
        <% loop AttributeValuesSorted %>
            <td class="label"<% if RGBCode %> style="color: #{$ComputedRGBCode}; background-color: #{$ComputedContrastRGBCode}"<% end_if %> >$Value</td>
        <% end_loop %>
    <% else %>
            <th scope="row" class="label">$Title.XML</th>
    <% end_if %>
            <td class="price">
                <span class="calculatedPrice">$CalculatedPriceAsMoney.NiceDefaultFormat</span>
            </td>
            <td class="actionCell">
                <div class="actionOuter">
                    <% include ProductActions %>
                </div>
            </td>
        </tr>
<% end_loop %>
    </tbody>
</table>

<% require themedCSS(VariationsTable, ecommerce_product_variation) %>
