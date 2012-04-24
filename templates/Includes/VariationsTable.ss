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
		<th scope="col" class="actionCell">&nbsp;</th>
	</tr>
<% control Variations %>
	<tr>
	<% if AttributeValuesSorted %>
		<% control AttributeValues %>
		<td class="label"<% if RGBCode %> style="color: #{$ComputedRGBCode}; background-color: #{$ComputedContrastRGBCode}"<% end_if %> >$Value</td>
		<% end_control %>
	<% else %>
		<th scope="row" class="label">$Title.XML</th>
	<% end_if %>
		<td class="price">
			<span class="price">$CalculatedPrice.Nice $Currency</span>
		</td>
		<td class="actionCell">
			<div class="actionOuter"><% if canPurchase %><% include ProductActionsInner %><% end_if %></div>
			<p class="description">$Description</p>
		</td>
	</tr>
<% end_control %>
</table>

<% require themedCSS(VariationsTable) %>
