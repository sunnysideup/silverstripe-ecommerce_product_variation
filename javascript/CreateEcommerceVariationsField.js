


;(function($) {
	$(document).ready(
		function() {
			CreateEcommerceVariationsField.init();
		}
	);

})(jQuery);


var CreateEcommerceVariationsField = {

	url: '',
		set_url: function(v) {this.url = v;},

	productID: 0,
		set_productID: function(v) {this.productID = v;},

	fieldID:"CreateEcommerceVariationsInner",
		set_fieldID: function(v) {this.fieldID = v;},

	starLinkSelector:"",
		set_fieldID: function(v) {this.fieldID = v;},

	messageHTML: "",

	typeAddFirstHolderHTML: "",

	typeAddHolderHTML: "",

	typesHolderHTML: "",

	valuesHolderHTML: "",

	createButtonHolderHTML: "",

	init: function() {
		this.messageHTML = '<li class="messageHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.messageHolder").html()+'</li>';
		this.typeAddFirstHolderHTML = '<li class="typeAddFirstHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeAddFirstHolder").html()+'</li>';
		this.typeAddHolderHTML = '<li class="typeAddHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeAddHolder").html()+'</li>';
		this.typesHolderHTML = '<li class="typeHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeHolder").html()+'</li>';
		this.valuesHolderHTML = '<li class="valueHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.valueHolder").html()+'</li>';
		this.createButtonHolderHTML = '<li class="createButtonHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.createButtonHolder").html()+'</li>';
		jQuery("#CreateEcommerceVariationsTemplate").remove();
		this.startLinkSelector = "#"+this.fieldID+" a#StartCreateEcommerceVariationsField";
		CreateEcommerceVariationsField.attachFunctions();
		jQuery(this.startLinkSelector).livequery(
			"click",
			function() {
				CreateEcommerceVariationsField.attachFunctions();
				return false;
			}
		);
	},

	reset: function (action, getVariables) {
		if(!action) {
			action = 'jsonforform';
		}
		if(!getVariables) {
			getVariables = {};
		}
		this.getDataFromServer(action, getVariables);
	},

	removeOldStuff: function() {
		jQuery("#"+this.fieldID).html("&nbsp;");
	},

	attachFunctions: function() {
		if(jQuery(this.startLinkSelector).length) {
			this.startLink();
		}
		else {
			this.addAddLinkToggles();
			this.addEditLinkToggles();
			this.add();
			this.rename();
			this.move();
			this.select();
			this.remove();
			this.createVariations();
		}
	},

	startLink: function() {
		jQuery(this.startLinkSelector).click(
			function() {
				CreateEcommerceVariationsField.reset();
				return false;
			}
		);
	},

	addAddLinkToggles: function() {
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .addLabelLink").click(
			function() {
				jQuery(this).parent("label").next("div").slideToggle();
			}
		);
	},

	addEditLinkToggles: function() {
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .editNameLink").click(
			function() {
				var rel = "#editFieldFor"+jQuery(this).attr("rel");
				jQuery(rel).slideToggle();
			}
		);
	},

	add:function() {
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .addInputHolder input").change(
			function() {
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("add", data);
			}
		);
	},

	rename:function() {
		//reset form
		jQuery("#"+CreateEcommerceVariationsField.fieldID+" .editFieldHolder input").change(
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

	},

	remove:function() {
		//reset form
		jQuery("a.deleteLink").click(
			function() {
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("remove", data);
				return false;
			}
		);
	},

	deleteValue:function() {

		//reset form
		jQuery("#A").click(
			function() {
				CreateEcommerceVariationsField.reset();
				return false;
			}
		);
	},

	createVariations: function() {
		jQuery('li.createButtonHolder input').click(
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
				}
				return false;
			}
		);
	},

	getDataFromServer: function(action, getVariables) {
		jQuery("#"+CreateEcommerceVariationsField.fieldID).addClass("loading");
		jQuery.getJSON(
			jQuery('base').attr("href")+'/' + CreateEcommerceVariationsField.url+'/' + action + '/'+CreateEcommerceVariationsField.productID+'/',
			getVariables,
			function(data) {
				if(data == "ok") {
					//do nothing
				}
				else {
					html = '<div><ul>'+CreateEcommerceVariationsField.messageHTML;
					html = html.replace(/MESSAGE/g, data.Message);
					html = html.replace(/GOODORBAD/g, data.MessageClass);
					var types = data.Types;
					if(types.length > 0) {
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
					CreateEcommerceVariationsField.attachFunctions();
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
			html = html.replace(/DELETE/g, 'display: none;');
		}
		var values = type.Values;
		var valueHtml = '';
		if(values.length > 0) {
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
			console.debug(value.Name + ' : Can Not Delete');
			html = html.replace(/DELETE/g, 'display: none;');
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
	}
}

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
