# Ecommerce Product Variations #

The product variations module allows for creating variations on products, such as size, colour. A specific price can be set for each variation.
This will allow keeping track of which version of a product is sold.

A Product has_many ProductAttributeTypes.
A ProductVariaton has_many ProdcutAttributeValues.
A ProductAttributeType has_many ProductAttributeValues.


ProductAttributeTypes have a name and a label.
'Name' and 'Label' are almost the same thing. 'Name' is a back-end way of storing a unique name for the attribute type. Eg: "Shoe Size". 'Label' is a front-end label used on the form Eg: "Size" or "Shoe Size" (but it should be obvious it's "Size" of shoes if you are on a shoe product page). 