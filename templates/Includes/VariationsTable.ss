<table class="quantityTable" summary="<% _t("PRODUCTOPTIONS","Product Options") %>">
	<tr>
<% if VariationAttributes %>
	<% control VariationAttributes %>
		<th scope="col" class="label">$Label</th>
	<% end_control %>
		<% else %>
		<th scope="col" class="label">Variation</th>
<% end_if %>
		<th scope="col" class="price"><% _t("PRICE","Price") %></th>
		<th scope="col" class="action">&nbsp;</th>
	</tr>
<% control Variations %>
	<tr>
	<% if AttributeValues %>
		<% control AttributeValues %>
		<td class="label"<% if RGBCode %> style="color: #{$ComputedRGBCode}; background-color: #{$ComputedContrastRGBCode}"<% end_if %> >$Value</td>
		<% end_control %>
	<% else %>
		<th scope="row" class="label">$Title.XML</th>
	<% end_if %>
		<td class="price">
			<span class="price">$CalculatedPrice.Nice $Currency $TaxInfo.PriceSuffix</span>
		</td>
		<td class="action">
			<span class="action">
	<% if canPurchase %>
		<% if IsInCart %>
			<a class="button" href="$OrderItem.RemoveAllLink" title="<% sprintf(_t("REMOVE","Remove &quot;%s&quot; from your cart"),$Title.XML) %>"><% _t("REMOVELINK","Remove from cart") %></a>
		<% else %>
			<a class="button" href="$OrderItem.AddLink" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title.XML) %>"><% _t("ADDLINK","Add this item to cart") %></a>
		<% end_if %>
	<% end_if %>
			</span>
			<p class="description">$Description</p>
		</td>
	</tr>
<% end_control %>
</table>

<% require themedCSS(VariationsTable) %>
