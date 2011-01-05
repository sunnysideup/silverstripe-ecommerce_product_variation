


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
				CreateEcommerceVariationsField.reset('createvariations', data);
				return false;
			}
		);
	},
	
	getDataFromServer: function(action, getVariables) {
		jQuery("#"+CreateEcommerceVariationsField.fieldID).addClass("loading");
		jQuery.getJSON(
			'/' + CreateEcommerceVariationsField.url+'/' + action + '/'+CreateEcommerceVariationsField.productID+'/',
			getVariables,
			function(data) {
				if(data == "ok") {
					//do nothing
				}
				else {
					html = '<div><ul>'+CreateEcommerceVariationsField.messageHTML;
					var count = parseInt(data.TypeSize);
					if(count) {
						var typeHtml = '';
						for(var i = 0; i < count; i++ ) {
							typeData = data.TypeItems[i];
							typeHtml += CreateEcommerceVariationsField.createTypeNode(typeData);
						}
						html += typeHtml+CreateEcommerceVariationsField.typeAddHolderHTML+CreateEcommerceVariationsField.createButtonHolderHTML;
					}
					else {
						html += CreateEcommerceVariationsField.typeAddHolderHTML+CreateEcommerceVariationsField.typeAddFirstHolderHTML;
					}
					html += '</ul></div>';
					html = html.replace(/MESSAGE/g, data.Message);
					html = html.replace(/GOODORBAD/g, data.MessageClass);
					CreateEcommerceVariationsField.removeOldStuff();
					jQuery("#"+CreateEcommerceVariationsField.fieldID).html(html);
					CreateEcommerceVariationsField.attachFunctions();
					jQuery("#"+CreateEcommerceVariationsField.fieldID).removeClass("loading");
				}
			}
		);
	},

	createTypeNode: function(typeData) {
		var html = CreateEcommerceVariationsField.typesHolderHTML;
		html = html.replace(/ID/g, typeData.TypeID);
		html = html.replace(/NAME/g, typeData.TypeName);
		if(parseInt(typeData.TypeIsSelected) == 0) {
			html = html.replace(' checked="checked"', "");
		}
		if(typeData.CanDeleteType) {
			html = html.replace('DELETE', '');
		}
		else {
			html = html.replace('DELETE', 'display: none');
		}
		//valueHolder
		var count = parseInt(typeData.ValueSize);
		var valueHtml = '';
		if(count) {
			for(var i = 0; i < count; i++ ) {
				var valueData = typeData.ValueItems[i];
				valueHtml += CreateEcommerceVariationsField.createValueNode(valueData);
			}
		}
		html = html.replace(/<li>VALUEHOLDER<\/li>/g, valueHtml);
		return html;
	},

	createValueNode: function(valueData) {
		var html = CreateEcommerceVariationsField.valuesHolderHTML;
		html = html.replace(/ID/g, valueData.ValueID);
		html = html.replace(/NAME/g, valueData.ValueName);
		if(parseInt(valueData.ValueIsSelected) == 0) {
			html = html.replace(' checked="checked"', "");
		}
		if(valueData.CanDeleteType) {
			html = html.replace("DELETE", '');
		}
		else {
			html = html.replace("DELETE", 'display: none');
		}
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
