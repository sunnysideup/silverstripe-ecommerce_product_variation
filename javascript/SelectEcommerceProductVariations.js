/**
 * This JS helps you
 * hide and show attribute values
 * based on selections made so far.
 *
 *
 * @param String RootSelector - e.g. MyForm as in <form id="MyForm"> or <div id="MyDiv">
 */


var SelectEcommerceProductVariations = function(RootSelector) {

	/**
	 * holds all the functions and variables
	 *
	 */
	var AvailAttr = {

		/**
		 * @var Boolean
		 */
		debug: false,

		/**
		 *
		 * @var String
		 */
		rootSelector: RootSelector,

		/**
		 *
		 * @var jQuery Object
		 */
		rootjQueryObject: null,

		/**
		 * This is the selector for the items that trigger a
		 * recheck of the Available Attributes
		 *
		 * @var String
		 */
		changeItemsSelector: "select",

		/**
		 * available variations
		 * @var JSON
		 */
		variationsJSON: "",

		/**
		 * current selection
		 * @var array
		 */
		selected: [],

		/**
		 * items that are possible
		 * @var array
		 */
		possible: [],

		/**
		 * selector for the submit button
		 * @var String
		 */
		submitSelector: "input.action",

		/**
		 * set up
		 *
		 */
		init: function(){

			AvailAttr.rootjQueryObject = jQuery("#" + AvailAttr.rootSelector)

			AvailAttr.rootjQueryObject.find(AvailAttr.changeItemsSelector).change(function(){
				//dont bother if there are less than two options...
				if(!AvailAttr.variationsJSON || AvailAttr.variationsJSON.length <= 1) {
					return false;
				}
				var $changed = jQuery(this);
				//get all selected values
				AvailAttr.selected = AvailAttr.getSelectedValues();
				//find what is possible with the current selection
				AvailAttr.possible = AvailAttr.findVariation(AvailAttr.selected);

				//reset all the other items selected
				//if the current selection is NOT possible...
				if(!AvailAttr.possible){
					//TODO: display - you cannot have a x,y,z
					jQuery(AvailAttr.changeItemsSelector).each(
						function(){
							if($changed[0] !== $(this)[0]){
								jQuery(this).val(''); 		// disable other selections based on impossible selection
							}
						}
					);
					AvailAttr.selected = AvailAttr.getSelectedValues(); //re-get selected
				}

				//find all possible attributes
				jQuery(AvailAttr.changeItemsSelector).each(
					function(pos, el){
						//dont do this for the selected item...
						if(jQuery(this).find(":selected[value!=\"\"]").length <= 0){
							//first set everything to disabled
							AvailAttr.disableOption(jQuery(this).find("option[value!=\"\"]"));
							var enableme = AvailAttr.getAttributesNotJoinedWith(AvailAttr.selected);
							//then enable the ones that are possible..
							for(var i = 0; i < enableme.length; i++){
								if(enableme[i]){
									var object = AvailAttr.rootjQueryObject.find("option[value=\""+enableme[i]+"\"]");
									AvailAttr.enableOption(object);
								}
							}
						}
					}
				);

				//$o = AvailAttr.rootjQueryObject.find(AvailAttr.submitSelector);
				//if(!AvailAttr.possible){
				//	AvailAttr.enableOption($o);
				//}
				//else{
				//	AvailAttr.disableOption($o);
				//}
			});
		},

		/**
		 * returns a list of items selected
		 * that are not empty.
		 * @return Array
		 */
		getSelectedValues: function(){
			var selected = new Array();
			AvailAttr.rootjQueryObject.find("select option:selected").each(
				function(i, el){
					if(jQuery(this).val() && jQuery(this).val() != ''){
						selected.push(jQuery(this).val());
					}
				}
			);
			return selected;
		},

		/**
		 *
		 * @param jQueryObject $o
		 */
		disableOption: function($o){
			$o.addClass('disabled');
			$o.attr("disabled", "disabled");
		},

		/**
		 *
		 * @param jQueryObject $o
		 */
		enableOption: function($o){
			$o.removeClass('disabled');
			$o.removeAttr("disabled");
		},

		/**
		 * 1. for each variation check if the selected one occurs.
		 * 2. if it occurs, add all the attribute value IDs for that variation
		 * basically turns a list of attributes that are possible based
		 * on the current selection
		 *
		 * @param array selected
		 */
		getAttributesNotJoinedWith: function(selected){

			var attrs = new Array();

			vloop: for(variation in AvailAttr.variationsJSON){
				for(var i = 0; i < selected.length; i++){
					if(!AvailAttr.variationsJSON[variation][selected[i]])
						continue vloop;
				}

				for(a in AvailAttr.variationsJSON[variation]){
					attrs.push(AvailAttr.variationsJSON[variation][a]);
				}
			}
			return attrs;

		},

		/**
		 * Finds the first variation it can with the selected attributes
		 * @param Array
		 *
		 * @return Array | null
		 */
		findVariation: function(selections){
			vloop: for(v in AvailAttr.variationsJSON){
				for(var i = 0; i < selections.length; i++){
					if(!AvailAttr.variationsJSON[v][selections[i]]){ //check that all values are in a possible variation attribute
						continue vloop;
					}
				}
				return AvailAttr.variationsJSON[v];
			}
			return null;
		}

	}

	// Expose public API
	return {
		getVar: function( variableName ) {
			if ( AvailAttr.hasOwnProperty( variableName ) ) {
				return AvailAttr[ variableName ];
			}
		},

		setVar: function(variableName, value) {
			AvailAttr[variableName] = value;
			return this;
		},

		setJSON: function(json) {
			AvailAttr["variationsJSON"] = json;
			return this;
		},

		init: function(){
			AvailAttr.init();
			return this;
		}

	}

}


