;(function($) {
	$(document).ready(
		function() {
			ProductVariation.init();
		}
	);

})(jQuery);

var ProductVariation = {
	url: '',
		set_url: function(v) {this.url = v;},
	productID: 0,
	variationID: 0,
	types: null,
	button: null,

	init: function() {
		this.productID = jQuery('#ComplexTableField_Popup_DetailForm_ProductID').val();
		if(this.productID) {
			this.variationID = jQuery('#ComplexTableField_Popup_DetailForm_ctf-childID').val();
			this.button = jQuery('#ComplexTableField_Popup_DetailForm div.Actions input.save');
		}
		else {
			this.productID = jQuery('#ComplexTableField_Popup_AddForm_ProductID').val();
			this.button = jQuery('#ComplexTableField_Popup_AddForm div.Actions input.save');
		}
		this.types = jQuery('select[name^="ProductAttributes"]');
		ProductVariation.attachFunctions();
		if(! this.variationID) {
			this.checkPermission();
		}
	},

	attachFunctions: function() {
		jQuery(this.types).change(this.checkPermission);
	},

	checkPermission: function() {
		jQuery(ProductVariation.button).attr('disabled', 'disabled').addClass('loading').attr('value', 'Validation checking in progress...');
		var data = {'variation' : ProductVariation.variationID};
		jQuery(ProductVariation.types).each(
			function() {
				var name = jQuery(this).attr('name');
				var from = name.indexOf('[');
				var to = name.indexOf(']');
				data[name.substring(from + 1, to)] = jQuery(this).val();
			}
		);
		jQuery.getJSON(
			jQuery('base').attr('href') + '/' + ProductVariation.url + '/cansavevariation/' + ProductVariation.productID + '/',
			data,
			function(result) {
				jQuery(ProductVariation.button).removeClass('loading');
				var message = '';
				if(result) {
					message = '<p class="message good">You can save this variation with these attributes.</p>';
					jQuery(ProductVariation.button).removeAttr('disabled');
				}
				else {
					message = '<p class="message bad">You can not save this variation with these attributes.</p>';
				}
				jQuery(ProductVariation.button).prev('p').detach();
				jQuery(ProductVariation.button).before(message);
				jQuery(ProductVariation.button).attr('value', 'Save');
			}
		);
	}
}
