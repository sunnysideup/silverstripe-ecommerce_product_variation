<div class="productImage">
<% if Image %>
	<a href="$Image.LargeImage.Link" class="colorboxImagePopup"><img src="$Image.SmallImage.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title.ATT) %>" width="$Image.SmallImage.Width" height="$Image.SmallImage.Height" /></a>
<% end_if %>
</div>


