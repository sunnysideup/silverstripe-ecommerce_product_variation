<h1>Product List</h1>
$Title
ddddddddddd
<% if Variations %>
<ul>
<% loop Variations %>
<li>$Product.Title, $Title</li>
<% end_loop %>
</ul>
<% else %>
<p class="message error">Can not find any variations....</p>
<% end_if %>

