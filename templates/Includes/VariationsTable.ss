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
		<th scope="col" class="cartActions">&nbsp;</th>
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
			<span class="price">$CalculatedPrice.Nice $Currency</span>
		</td>
		<td class="action">
			<span class="cartActions">
	<% if canPurchase %>
		<% include ProductActionsForGroup %>
	<% end_if %>
			</span>
			<p class="description">$Description</p>
		</td>
	</tr>
<% end_control %>
</table>

<% require themedCSS(VariationsTable) %>
