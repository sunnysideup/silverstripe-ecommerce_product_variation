<% if WithProductTitle %>
	<span class="productVariationProductTitle">
		$Product.Title
	</span>
<% end_if %>
<% if Values %> - <% if Values.count %>
		<% control Values %>
			<span class="productVariationValues">
				<strong>$Type.Label:</strong> <em>$Value</em><% if Last %>;<% else %>,<% end_if %>
			</span>
		<% end_control %>
	<% else %>
		<span class="productVariationValues">
		$InternalItemID;
		</span>
<% end_if %><% end_if %>
<% if Description %>
	<span class="productVariationDescription">
		$Description;
	</span>
<% end_if %>
