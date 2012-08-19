<div class="productImage">
<% if Image %>
	<a href="$Image.LargeImage.Link"><img src="$Image.SmallImage.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title.ATT) %>" width="$Image.SmallImage.Width" height="$Image.SmallImage.Height" /></a>
<% else %>
	<a class="noImage"><img src="$EcomConfig.DefaultImageLink" alt="<% _t("Product.NOIMAGEAVAILABLE","no image available") %>" width="$DummyImage.SmallWidth" height="$DummyImage.SmallHeight" /></a>
<% end_if %>
</div>


