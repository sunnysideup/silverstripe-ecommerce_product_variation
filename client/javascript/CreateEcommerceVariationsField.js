


(function($) {
	$.entwine('ecommerce', function($) {
		$('#CreateEcommerceVariationsInner').entwine({
			onmatch : function() {
				CreateEcommerceVariationsField.init();
			}
		});
	});
}(jQuery));


var CreateEcommerceVariationsField = {

	/**
	 * set to true to get output to the console
	 * @var Boolean
	 */
	debug: false,

	/**
	 * Top selector.
	 * @var String
	 */
	//by setting the root delegate selector very "high", it will ensure it always works.
	delegateRootSelector: "body",
		set_delegateRootSelector: function(s) {this.delegateRootSelector = s;},

	/**
	 * messages has been shown
	 * @var Boolean
	 */
	reminderProvided: false,

	/**
	 * URL to access controller
	 * @var String
	 */
	url: '',
		set_url: function(v) {this.url = v;},

	/**
	 * product we are dealing with
	 * @var Int
	 */
	productID: 0,
		set_productID: function(v) {this.productID = v;},


	/**
	 * selector to get Product ID
	 * @var String
	 */
		getProductIDSelector: '#Form_EditForm_ID',

	/**
	 * ID selector for field
	 * @var String
	 */
	//id of field that has link to controller
	fieldID:"CreateEcommerceVariationsInner",
		set_fieldID: function(fieldName) {this.fieldID = fieldName;},

	starLinkSelector:"",

	messageHTML: "",

	typeAddFirstHolderHTML: "",

	typeAddHolderHTML: "",

	typesHolderHTML: "",

	valuesHolderHTML: "",

	createButtonHolderHTML: "",

	attached: false,

	selectedTypesAndValues: {},

	init: function() {
		if(CreateEcommerceVariationsField.debug) {console.debug("init");}
		CreateEcommerceVariationsField.attachFunctions();
		CreateEcommerceVariationsField.startLinkSelector = "#"+this.fieldID+" a#StartCreateEcommerceVariationsField";
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			'click',
			CreateEcommerceVariationsField.startLinkSelector,
			function() {
				return CreateEcommerceVariationsField.startup();
			}
		);
	},

	startup: function() {
		if(jQuery("#CreateEcommerceVariationsTemplate").length > 0) {
			if(CreateEcommerceVariationsField.debug) {console.debug("startup");}
			console.debug("do");
			CreateEcommerceVariationsField.messageHTML = '<li class="messageHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.messageHolder").html()+'</li>';
			CreateEcommerceVariationsField.typeAddFirstHolderHTML = '<li class="typeAddFirstHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeAddFirstHolder").html()+'</li>';
			CreateEcommerceVariationsField.typeAddHolderHTML = '<li class="typeAddHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeAddHolder").html()+'</li>';
			CreateEcommerceVariationsField.typesHolderHTML = '<li class="typeHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.typeHolder").html()+'</li>';
			CreateEcommerceVariationsField.valuesHolderHTML = '<li class="valueHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.valueHolder").html()+'</li>';
			CreateEcommerceVariationsField.createButtonHolderHTML = '<li class="createButtonHolder">'+jQuery("#CreateEcommerceVariationsTemplate li.createButtonHolder").html()+'</li>';
			jQuery("#CreateEcommerceVariationsTemplate").remove();
			CreateEcommerceVariationsField.reset();
		}
		else {
			if(CreateEcommerceVariationsField.debug) {console.debug("NOT startup");}
		}
		return false;
	},

	reset: function (action, getVariables) {
		if(CreateEcommerceVariationsField.debug) {console.debug("reset");}
		if(!action) {
			action = 'jsonforform';
		}
		if(!getVariables) {
			getVariables = {};
		}
		if(CreateEcommerceVariationsField.debug) {console.debug("get data from server");}
		CreateEcommerceVariationsField.getDataFromServer(action, getVariables);
		//nice addition
		jQuery("#CreateEcommerceVariationsInner a").each (
			function (i, el) {
				var title = jQuery(el).text();
				jQuery(el).attr("title", title);
			}
		);
	},

	removeOldStuff: function() {
		if(CreateEcommerceVariationsField.debug) {console.debug("removeOldStuff");}
		jQuery("#"+this.fieldID).html("&nbsp;");
	},

	attachFunctions: function() {
		if(!CreateEcommerceVariationsField.attached) {
			if(CreateEcommerceVariationsField.debug) {console.debug("attachFunctions");}
			CreateEcommerceVariationsField.productID = jQuery(CreateEcommerceVariationsField.getProductIDSelector).val();
			CreateEcommerceVariationsField.addAddLinkToggles();
			CreateEcommerceVariationsField.addEditLinkToggles();
			CreateEcommerceVariationsField.addEditInCMSLinks();
			CreateEcommerceVariationsField.addGroupItemLinkedClicks();
			CreateEcommerceVariationsField.add();
			CreateEcommerceVariationsField.rename();
			CreateEcommerceVariationsField.move();
			CreateEcommerceVariationsField.select();
			CreateEcommerceVariationsField.remove();
			CreateEcommerceVariationsField.createVariations();
			CreateEcommerceVariationsField.attached = true;
		}
		else {
			if(CreateEcommerceVariationsField.debug) {console.debug("DID NOT attachFunctions");}
		}
	},

	addAddLinkToggles: function() {
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"click",
			"#"+CreateEcommerceVariationsField.fieldID+" .addLabelLink",
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("addAddLinkToggles");}
				jQuery(this).parent("label").next("div").slideToggle();
			}
		);
	},

	addEditLinkToggles: function() {
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"click",
			"#"+CreateEcommerceVariationsField.fieldID+" .editInCMSLink",
			function(event) {
				if(CreateEcommerceVariationsField.debug) {console.debug("addEditLinkToggles");}
				jQuery(this).attr("target", jQuery(this).attr("rel"));
				return true;
			}
		);
	},

	addEditInCMSLinks: function() {
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"click",
			"#"+CreateEcommerceVariationsField.fieldID+" .editNameLink",
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("addEditInCMSLinks");}
				var rel = "#editFieldFor"+jQuery(this).attr("rel");
				jQuery(rel).slideToggle();
			}
		);
	},

	resetModeForOpenAndClose: false,

	runningParent: false,

	runningChild: false,

	addGroupItemLinkedClicks: function() {
		//if the parent is unticked then untick the children
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"change",
			"#"+CreateEcommerceVariationsField.fieldID+" input.dataForType.checkbox",
			function() {
				if(CreateEcommerceVariationsField.runningChild || CreateEcommerceVariationsField.resetModeForOpenAndClose) {
					return false;
				}
				if(CreateEcommerceVariationsField.debug) {console.debug("change type check");}
				CreateEcommerceVariationsField.runningParent = true;
				var parent = jQuery(this).parents("li.typeHolder");
				if(!CreateEcommerceVariationsField.reminderProvided) {
					jQuery("#MainReminderMessage").slideDown();
					jQuery("#InitMessage").removeClass("message");
					CreateEcommerceVariationsField.reminderProvided = true;
				}
				if(jQuery(this).is(':checked')) {
					parent.find(".valuesHolder").slideDown();
					parent.find(".valuesHolder input.dataForValue").each(
						function(i, el) {
							jQuery(el).prop("checked", true);
						}
					);
				}
				else {
					parent.find(".valuesHolder").slideUp();
					parent.find(".valuesHolder input.dataForValue").each(
						function(i, el) {
							jQuery(el).prop("checked", false);
						}
					);
				}
				CreateEcommerceVariationsField.runningParent = false;
			}
		);
		//if all the children are unticked then untick the parent
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"change",
			"#"+CreateEcommerceVariationsField.fieldID+" .valuesHolder input.dataForValue.checkbox",
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("change value checkbox");}
				CreateEcommerceVariationsField.runningChild = true;
				if(CreateEcommerceVariationsField.runningParent || CreateEcommerceVariationsField.resetModeForOpenAndClose) {
					return false;
				}
				var parent = jQuery(this).parents("li.typeHolder");
				if(parent.find("input.dataForType.checkbox").attr("disabled") == "disabled") {
					//do nothing
				}
				else {
					var hasTickedSibling = false;
					parent.find("ul li input.dataForValue.checkbox").each(
						function(i, el) {
							if(jQuery(el).is(":checked")) {
								hasTickedSibling = true;
							}
							else {
							}
						}
					);
					if(hasTickedSibling) {
						parent.find("input.dataForType.checkbox").prop("checked", true);
						parent.find(".valuesHolder").slideDown();
					}
					else {
						parent.find("input.dataForType.checkbox").prop("checked", false);
						parent.find(".valuesHolder").slideUp();
					}
				}
				CreateEcommerceVariationsField.runningChild = false;
			}
		);

	},

	add:function() {
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"change",
			"#"+CreateEcommerceVariationsField.fieldID+" .addInputHolder input",
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("add");}
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("add", data);
			}
		);
	},

	rename:function() {
		//reset form
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"change",
			"#"+CreateEcommerceVariationsField.fieldID+" .editFieldHolder input",
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("rename");}
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
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"click",
			"#"+CreateEcommerceVariationsField.fieldID+" a.deleteLink",
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("remove");}
				data = CreateEcommerceVariationsField.createGetVariables(this);
				CreateEcommerceVariationsField.reset("remove", data);
				return false;
			}
		);
	},

	deleteValue:function() {

		//reset form
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"click",
			"#"+CreateEcommerceVariationsField.fieldID+" #A",
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("deleteValue");}
				CreateEcommerceVariationsField.reset();
				return false;
			}
		);
	},

	createVariations: function() {
		jQuery(CreateEcommerceVariationsField.delegateRootSelector).on(
			"click",
			"#"+CreateEcommerceVariationsField.fieldID+' li.createButtonHolder input',
			function() {
				if(CreateEcommerceVariationsField.debug) {console.debug("createVariations");}
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
		CreateEcommerceVariationsField.selectedTypesAndValues = CreateEcommerceVariationsField.selectGetVariables();
		jQuery.getJSON(
			jQuery('base').attr("href") + CreateEcommerceVariationsField.url +'/' + action + '/'+CreateEcommerceVariationsField.productID+'/',
			getVariables,
			function(data) {
				if(CreateEcommerceVariationsField.debug) {console.debug(data);}
				if(1 == 2) {
					jQuery('#' + CreateEcommerceVariationsField.fieldID).removeClass('loading');
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
					//reset check boxes before hiding, etc...

					//attach functions ...
					CreateEcommerceVariationsField.attachFunctions();

					jQuery('#' + CreateEcommerceVariationsField.fieldID).removeClass('loading');
				}
			}
		);
	},

	createTypeNode: function(type) {
		for (var dataTypeKey in CreateEcommerceVariationsField.selectedTypesAndValues) {
			if(dataTypeKey == type.ID) {
				type.Checked = true;
				var dataValueIDs = CreateEcommerceVariationsField.selectedTypesAndValues[dataTypeKey];
				dataValueIDs = dataValueIDs.split(",")
				for (var dataValueID in dataValueIDs) {
					for (var nodeValueKey in type.Values) {
						if(type.Values[nodeValueKey].ID == dataValueIDs[dataValueID]) {
							type.Values[nodeValueKey].Checked = true;
							if(CreateEcommerceVariationsField.debug) {console.debug("turning on #ValueCheck"+dataValueID);}
						}
					}
				}
				if(CreateEcommerceVariationsField.debug) {console.debug("turning on "+"#TypeCheck"+type.ID);}
			}
		}

		var html = CreateEcommerceVariationsField.typesHolderHTML;
		html = html.replace(/ID/g, type.ID);
		html = html.replace(/NAME/g, type.Name);
		if(! type.Checked) {
			html = html.replace(' checked="checked"', '');
		}
		else {
			html = html.replace('<ul class="valuesHolder" style="display: none;">', '<ul class="valuesHolder">');
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
		html = html.replace(/EDITLINK/g, type.EditLink);
		return html;
	},

	createValueNode: function(value) {
		var html = CreateEcommerceVariationsField.valuesHolderHTML;
		html = html.replace(/ID/g, value.ID);
		html = html.replace(/NAME/g, value.Name);
		if(! value.Checked) {
			html = html.replace(' checked="checked" ', '');
		}
		if(value.CanDelete) {
			html = html.replace(/DELETE/g, '');
		}
		else {
			html = html.replace(/DELETE/g, 'doNotShow');
		}
		html = html.replace(/ChangeToId/g, 'ID');
		html = html.replace(/EDITLINK/g, value.EditLink);
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
		a.value = jQuery(inputElement).val();
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
CONFIG - CAN BE OVERRIDEN BY YOUR OWN JS FILE
LeftAndMain:
  extra_requirements_javascript:
    - framework/thirdparty/jquery/jquery.js
    - framework/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js
    - ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js
*/
CreateEcommerceVariationsField.set_url('createecommercevariations');
CreateEcommerceVariationsField.set_fieldID('CreateEcommerceVariationsInner');

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

