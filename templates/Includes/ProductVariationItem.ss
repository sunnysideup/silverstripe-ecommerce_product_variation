<% if WithProductTitle %>
	<span class="productVariationProductTitle">
		$Product.Title.XML
	</span>
<% end_if %>
<% if Values %> - <% if Values.count %>
		<% loop Values %>
			<span class="productVariationValues">
				<strong>$Type.Label:</strong> <em>$Value</em><% if Last %>;<% else %>,<% end_if %>
			</span>
		<% end_loop %>
	<% else %>
		<span class="productVariationValues">
		$InternalItemID;
		</span>
<% end_if %><% end_if %>
<% if Description %>
	<span class="productVariationDescription">
		$Description.XML;
	</span>
<% end_if %>
