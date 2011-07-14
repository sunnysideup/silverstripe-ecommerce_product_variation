

;(function($) {
	$(document).ready(
		function() {
			CreateEcommerceVariationsField.init();
		}
	);

})(jQuery);


var CreateEcommerceVariationsField = {

	//URL to access controller
	url: '',
		set_url: function(v) {this.url = v;},

	//product we are dealing with
	productID: 0,
		set_productID: function(v) {this.productID = v;},
		getProductIDSelector: '#Form_EditForm_ID',		

	//id of field that has link to controller
	fieldID:"CreateEcommerceVariationsInner",
		set_fieldID: function(v) {this.fieldID = v;},

	starLinkSelector:"",

	messageHTML: "",

	typeAddFirstHolderHTML: "",

	typeAddHolderHTML: "",

	typesHolderHTML: "",

	valuesHolderHTML: "",

	createButtonHolderHTML: "",

	init: function() {
		CreateEcommerceVariationsField.startLinkSelector = "#"+this.fieldID+" a#StartCreateEcommerceVariationsField";		
		jQuery(CreateEcommerceVariationsField.startLinkSelector).live(
			'click',
			function() {
				return CreateEcommerceVariationsField.startup();
			}
		);
		CreateEcommerceVariationsField.attachFunctions();
	},

	startup: function() {
		if(jQuery("#CreateEcommerceVariationsTemplate").length > 0) {
			CreateEcommerceVariationsField.messageHTML = '<li class="messageHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.messageHolder").html()+'</li>';
			CreateEcommerceVariationsField.typeAddFirstHolderHTML = '<li class="typeAddFirstHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeAddFirstHolder").html()+'</li>';
			CreateEcommerceVariationsField.typeAddHolderHTML = '<li class="typeAddHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeAddHolder").html()+'</li>';
			CreateEcommerceVariationsField.typesHolderHTML = '<li class="typeHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeHolder").html()+'</li>';
			CreateEcommerceVariationsField.valuesHolderHTML = '<li class="valueHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.valueHolder").html()+'</li>';
			CreateEcommerceVariationsField.createButtonHolderHTML = '<li class="createButtonHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.createButtonHolder").html()+'</li>';
			jQuery("#CreateEcommerceVariationsTemplate").remove();
			CreateEcommerceVariationsField.reset();
		}
		return false;
	},

	reset: function (action, getVariables) {
		if(!action) {
			action = 'jsonforform';
		}
		if(!getVariables) {
			getVariables = {};
		}
		CreateEcommerceVariationsField.getDataFromServer(action, getVariables);
	},

	removeOldStuff: function() {
		jQuery("#"+this.fieldID).html("&nbsp;");
	},

	attachFunctions: function() {
		CreateEcommerceVariationsField.productID = jQuery(CreateEcommerceVariationsField.getProductIDSelector).val();
		CreateEcommerceVariationsField.addAddLinkToggles();
		CreateEcommerceVariationsField.addEditLinkToggles();
		CreateEcommerceVariationsField.add();
		CreateEcommerceVariationsField.rename();
		CreateEcommerceVariationsField.move();
		CreateEcommerceVariationsField.select();
		CreateEcommerceVariationsField.remove();
		CreateEcommerceVariationsField.createVariations();
	},

	addAddLinkToggles: function() {
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .addLabelLink").live(
			"click",
			function() {
				jQuery(this).parent("label").next("div").slideToggle();
			}
		);
	},

	addEditLinkToggles: function() {
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .editNameLink").live(
			"click",
			function() {
				var rel = "#editFieldFor"+jQuery(this).attr("rel");
				jQuery(rel).slideToggle();
			}
		);
	},

	add:function() {
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .addInputHolder input").live(
			"change",
			function() {
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("add", data);
			}
		);
	},

	rename:function() {
		//reset form
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .editFieldHolder input").live(
			"change",
			function() {
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("rename", data);
				return false;
			}
		);
	},

	move:function() {

	},

	select:function() {
		jQuery('input:checkbox.dataForType:not(:checked)').each(
			function() {
				jQuery(this).parents('div.typeCheckHolder').next().hide();
			}
		);
		jQuery('input:checkbox.dataForType').change(
			function() {
				var values = jQuery(this).parents('div.typeCheckHolder').next();
				if(jQuery(this).is(':checked')) {
					jQuery(values).show();
				}
				else {
					jQuery(values).hide();
				}
			}
		);
	},

	remove:function() {
		//reset form
		jQuery("a.deleteLink").live(
			"click",
			function() {
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("remove", data);
				return false;
			}
		);
	},

	deleteValue:function() {

		//reset form
		jQuery("#A").live(
			"click",
			function() {
				CreateEcommerceVariationsField.reset();
				return false;
			}
		);
	},

	createVariations: function() {
		jQuery('li.createButtonHolder input').live(
			"click",
			function() {
				data = CreateEcommerceVariationsField.selectGetVariables();
				var mandatoryTypes = jQuery('#' + CreateEcommerceVariationsField.fieldID + ' input.dataForType:disabled:checked');
				var missingTypes = new Array();
				jQuery(mandatoryTypes).each(
					function() {
						var rel = jQuery(this).attr('rel');
						if(data[rel] == undefined) {
							var type = '#' + CreateEcommerceVariationsField.fieldID + ' a[rel=Type' + rel + ']';
							missingTypes.push(jQuery(type).text());
						}
					}
				);
				if(missingTypes.length > 0) {
					alert('You need to select values for the types ' + missingTypes.join(', ') + '.');
				}
				else {
					CreateEcommerceVariationsField.reset('createvariations', data);
					jQuery('#Form_EditForm_action_save').click();
					/*
					this might be better in SS 3.0+!
					jQuery.ajax({
						url: "/admin/getitem",
						data: {ID: CreateEcommerceVariationsField.productID, ajax: 1},
						success: function(response){jQuery("#Form_EditForm").html(response);},
						dataType: "html"
					});
					*/
					
				}
				return false;
			}
		);
	},

	getDataFromServer: function(action, getVariables) {
		if(jQuery("#Form_EditForm_ID").length > 0) {
			CreateEcommerceVariationsField.productID = jQuery("#Form_EditForm_ID").val();
		}
		jQuery("#"+CreateEcommerceVariationsField.fieldID).addClass("loading");
		jQuery.getJSON(
			jQuery('base').attr("href") + CreateEcommerceVariationsField.url +'/' + action + '/'+CreateEcommerceVariationsField.productID+'/',
			getVariables,
			function(data) {
				if(data == "ok") {
					//do nothing
				}
				else {
					CreateEcommerceVariationsField.startup();
					html = '<div><ul>'+CreateEcommerceVariationsField.messageHTML;
					html = html.replace(/MESSAGE/g, data.Message);
					html = html.replace(/GOODORBAD/g, data.MessageClass);
					var types = data.Types;
					if(types && types != "undefined" && types.length > 0) {
						var typeHtml = '';
						for(var i = 0; i < types.length; i++) {
							typeHtml += CreateEcommerceVariationsField.createTypeNode(types[i]);
						}
						html += typeHtml + CreateEcommerceVariationsField.typeAddHolderHTML + CreateEcommerceVariationsField.createButtonHolderHTML;
					}
					else {
						html += CreateEcommerceVariationsField.typeAddHolderHTML + CreateEcommerceVariationsField.typeAddFirstHolderHTML;
					}
					html += '</ul></div>';
					CreateEcommerceVariationsField.removeOldStuff();
					jQuery('#' + CreateEcommerceVariationsField.fieldID).html(html);
					//CreateEcommerceVariationsField.attachFunctions();
					jQuery('#' + CreateEcommerceVariationsField.fieldID).removeClass('loading');
				}
			}
		);
	},

	createTypeNode: function(type) {
		
		var html = CreateEcommerceVariationsField.typesHolderHTML;
		html = html.replace(/ID/g, type.ID);
		html = html.replace(/NAME/g, type.Name);
		if(! type.Checked) {
			html = html.replace(' checked="checked"', '');
		}
		if(! type.Disabled) {
			html = html.replace(' disabled="disabled"', '');
		}
		if(type.CanDelete) {
			html = html.replace(/DELETE/g, '');
		}
		else {
			html = html.replace(/DELETE/g, 'doNotShow');
		}
		var values = type.Values;
		var valueHtml = '';
		if(values && values.length > 0) {
			for(var i = 0; i < values.length; i++) {
				valueHtml += CreateEcommerceVariationsField.createValueNode(values[i]);
			}
		}
		html = html.replace(/<li>VALUEHOLDER<\/li>/g, valueHtml);
		html = html.replace(/ChangeToId/g, 'ID');
		return html;
	},

	createValueNode: function(value) {
		var html = CreateEcommerceVariationsField.valuesHolderHTML;
		html = html.replace(/ID/g, value.ID);
		html = html.replace(/NAME/g, value.Name);
		if(! value.Checked) {
			html = html.replace(' checked="checked"', '');
		}
		if(value.CanDelete) {
			html = html.replace(/DELETE/g, '');
		}
		else {
			html = html.replace(/DELETE/g, 'doNotShow');
		}
		html = html.replace(/ChangeToId/g, 'ID');
		return html;
	},

	createGetVariables: function(inputElement) {
		var a = {};
		if(jQuery(inputElement).hasClass("dataForValue")) {
			a.typeorvalue = "value";
		}
		else {
			a.typeorvalue = "type";
		}
		a.value = escape(jQuery(inputElement).val());
		a.id = jQuery(inputElement).attr("rel");
		return a;
	},

	selectGetVariables: function() {
		var types = jQuery('#' + CreateEcommerceVariationsField.fieldID + ' input.dataForType:checked');
		var a = {};
		jQuery(types).each(
			function() {
				var values = jQuery(this).parents('li.typeHolder').find('input.dataForValue:checked');
				if(jQuery(values).length > 0) {
					var ids = '';
					jQuery(values).each(
						function() {
							if(ids.length > 0) ids += ',';
							ids += jQuery(this).attr('rel');
						}
					);
					a[jQuery(this).attr('rel')] = ids;
				}
			}
		);
		return a;
	},


	
}



/*
CONFIG - CAN BE OVERRIDEN BY YOUR OWN JS FILE
//LeftAndMain::require_javascript("mysite/javascript/CreateEcommerceVariationsField.js");
*/
CreateEcommerceVariationsField.set_url('createecommercevariations')
CreateEcommerceVariationsField.set_fieldID('CreateEcommerceVariationsInner')

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

