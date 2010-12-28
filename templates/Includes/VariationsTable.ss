<table class="quantityTable">
	<tr>
		<% if VariationAttributes %>
		<% control VariationAttributes %>
			<th>$Label</th>
		<% end_control %>
		<% else %>
			<th>Variation</th>
		<% end_if %>
		<th>Price</th><th><% _t("QUANTITYCART","Quantity in cart") %></th>
	</tr>
	<% control Variations %>
			<tr>
				<% if AttributeValues %>
				<% control AttributeValues %>
					<td>$Value</td>
				<% end_control %>
				<% else %>
					<td>$Title.XML</td>
				<% end_if %>
				
				<td>$Price.Nice $Currency $TaxInfo.PriceSuffix</td>
				<td>
				<% if canPurchase %>
					<% if IsInCart %>
						<% control Item %>
							$QuantityField
						<% end_control %>
					<% else %>
						<a class="button" href="$Item.addLink" title="<% sprintf(_t("ADD","Add &quot;%s&quot; to your cart"),$Title.XML) %>"><% _t("ADDLINK","Add this item to cart") %></a>
					<% end_if %>
				
				<% end_if %>
				</td>
			</tr>
	<% end_control %>
</table>