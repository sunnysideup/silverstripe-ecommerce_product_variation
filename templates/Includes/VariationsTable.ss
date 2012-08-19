<table class="quantityTable" summary="<% _t("PRODUCTOPTIONS","Product Options") %>">
	<thead>
		<tr>
			<th scope="col" class="description"><% _t("Product.VARIATIONDESCRIPTIONLABEL", "") %>&nbsp;</th>
<% if VariationAttributes %>
	<% control VariationAttributes %>
			<th scope="col" class="label">$Label</th>
	<% end_control %>
		<% else %>
			<th scope="col" class="label">Variation</th>
<% end_if %>
			<th scope="col" class="price"><% _t("PRICE","Price") %> <% if EcomConfig.Currency %> ($EcomConfig.Currency)<% end_if %></th>
			<th scope="col" class="actionCell">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<% control Variations %>
		<tr>
			<th scope="row" class="<% if Description %>hasDescription<% else %>noDescription<% end_if %>">
				<% include ProductVariationImage %>
				$Description &nbsp;
			</th>
	<% if AttributeValuesSorted %>
		<% control AttributeValues %>
			<td class="label"<% if RGBCode %> style="color: #{$ComputedRGBCode}; background-color: #{$ComputedContrastRGBCode}"<% end_if %> >$Value</td>
		<% end_control %>
	<% else %>
			<th scope="row" class="label">$Title.XML</th>
	<% end_if %>
			<td class="price">
				<span class="price">$CalculatedPrice.Nice</span>
				<% include Order_Content_DisplayPrice %>
			</td>
			<td class="actionCell">
				<div class="actionOuter">
					<% if canPurchase %>
						<% include ProductActionsInner %>
					<% else %>
						<div class="notForSale message">$EcomConfig.NotForSaleMessage</div>
					<% end_if %>
				</div>
			</td>
		</tr>
<% end_control %>
	</tbody>
</table>

<% require themedCSS(VariationsTable) %>
