


(function($) {
	$.entwine('ecommerce', function($) {
		$('#DeleteEcommerceVariationsInner').entwine({
			onmatch : function() {
				DeleteEcommerceVariations.init();
			}
		});
	});
}(jQuery));


var DeleteEcommerceVariations = {

	//by setting the root delegate selector very "high", it will ensure it always works.
	delegateRootSelector: "body",
		set_delegateRootSelector: function(s) {this.delegateRootSelector = s;},

	//id of field that has link to controller
	fieldID:"DeleteEcommerceVariationsInner",
		set_fieldID: function(v) {this.fieldID = v;},

	//id of field that has link to controller

	initDone: false,

	init: function() {
		if(this.initDone) {
			return;
		}
		this.initDone = true;
		jQuery(DeleteEcommerceVariations.delegateRootSelector).on(
			'click',
			"#" + DeleteEcommerceVariations.fieldID,
			function(event) {
				event.preventDefault();
				jQuery('#' + DeleteEcommerceVariations.fieldID).addClass('loading');
				var url = jQuery('base').attr("href") + jQuery(this).attr("href");
				var confirmMessage = jQuery(this).attr("data-confirm");
				if(window.confirm(confirmMessage)) {
					jQuery.get(url)
					 .done(function() {
							jQuery('#' + DeleteEcommerceVariations.fieldID).text('');
							jQuery('[name="action_publish"]').click();
						})
						.fail(function() {
							jQuery('#' + DeleteEcommerceVariations.fieldID).text('ERROR!');
						})
						.always(function() {
							jQuery('[name="action_publish"]').click();
							jQuery('#' + DeleteEcommerceVariations.fieldID).removeClass('loading');
						})
					;
				}
			}
		);
	},


	runTask: function() {

	}




}



/*
CONFIG - CAN BE OVERRIDEN BY YOUR OWN JS FILE
LeftAndMain:
  extra_requirements_javascript:
    - framework/thirdparty/jquery/jquery.js
    - framework/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js
    - ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js
*/

/*
{
	"TypeSize": 2,
	"TypeItems": [ {
		"TypeID": "1",
		"TypeName": "colour",
		"TypeIsSelected": "to be coded",
		"CanDeleteType": "",
		"ValueSize": 3,
		"ValueItems": [{
			"ValueID": "1",
			"ValueName": "green",
			"ValueIsSelected": "to be coded",
			"CanDeleteValue": "1"
		]},
	}]
}



*/

