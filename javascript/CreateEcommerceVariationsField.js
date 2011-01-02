


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
		this.messageHTML = jQuery("#CreateEcommerceVariationsTemplate .message").html();
		this.typeAddFirstHolderHTML = jQuery("#CreateEcommerceVariationsTemplate .typeAddFirstHolder").html();
		this.typeAddHolderHTML = jQuery("#CreateEcommerceVariationsTemplate .typeAddHolder").html();
		this.typesHolderHTML = jQuery("#CreateEcommerceVariationsTemplate .typeHolder").html();
		this.valuesHolderHTML = jQuery("#CreateEcommerceVariationsTemplate .valueHolder").html();
		this.createButtonHolderHTML = jQuery("#CreateEcommerceVariationsTemplate .createButtonHolder").html();
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
		this.removeOldStuff();
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
			this.add();
			this.rename();
			this.move();
			this.select();
			this.remove();
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
		jQuery(".addLabelLink").click(
			function() {
				jQuery(this).parent("label").next("div").slideToggle();
			}
		);
	},

	add:function() {
		jQuery(".addInputHolder input").change(
			function() {
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("add", data);
			}
		);
	},

	rename:function() {
		//reset form
		jQuery("#A").click(
			function() {
				CreateEcommerceVariationsField.reset();
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

	getDataFromServer: function(action, getVariables) {
		jQuery.getJSON(
			'/' + CreateEcommerceVariationsField.url+'/' + action + '/'+CreateEcommerceVariationsField.productID+'/',
			getVariables,
			function(data) {
				if(data == "ok") {
					//do nothing
				}
				else {
					html = '<div>';
					var count = parseInt(data.TypeSize);
					if(count) {
						var typeHtml = '';
						for(var i = 0; i < count; i++ ) {
							typeData = data.TypeItems[i];
							typeHtml += CreateEcommerceVariationsField.createTypeNode(typeData);
						}
						html += '<ul>'+CreateEcommerceVariationsField.messageHTML+typeHtml+CreateEcommerceVariationsField.typeAddHolderHTML+CreateEcommerceVariationsField.createButtonHolderHTML+'</ul>';
					}
					else {
						html += '<ul>'+CreateEcommerceVariationsField.messageHTML+CreateEcommerceVariationsField.typeAddFirstHolderHTML+'</ul>';
					}
					jQuery("#"+CreateEcommerceVariationsField.fieldID).html(html);
					html += '</div>';
					html = html.replace(/GOODORBAD/g, data.MessageClass);
					html = html.replace(/MESSAGE/g, data.Message);
					CreateEcommerceVariationsField.attachFunctions();
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
		else {
			alert(typeData.TypeIsSelected);
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
