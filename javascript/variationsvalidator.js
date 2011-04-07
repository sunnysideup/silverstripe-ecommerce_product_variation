
(function($){

	$("#Form_VariationForm select").change(function(){

		$changed = $(this);

		if(!variationsjson || variationsjson.length <= 0)
			return false;

		//get all selected values

		var selected = getSelectedValues();
		var possible = findVariation(selected);

		//break the impossibles by resetting other fields
		if(!possible){
			//TODO: display - you cannot have a x,y,z

			$("#Form_VariationForm select").each(function(){
				if($changed[0] !== $(this)[0]){
					$(this).val(''); 		// diable other selections based on impossible selection
				}
			});
			selected = getSelectedValues(); //re-get selected
		}


		//find all possible attributes
		$("#Form_VariationForm select").each(function(el){

			if($(this).find(":selected[value!=\"\"]").length <= 0){
				disableOption($(this).find("option[value!=\"\"]"));
				var enableme = getAttributesNotJoinedWith(selected);

				for(var i = 0; i < enableme.length; i++){
					if(enableme[i]){
						enableOption($("#Form_VariationForm option[value=\""+enableme[i]+"\"]"));
					}
				}
			}
		});

		//TODO: supply appropriate error message
		if(!possible){
			$('#Form_VariationForm input.action').addClass('disabled');
		}
		else{
			$('#Form_VariationForm input.action').removeClass('disabled');
		}


	});


	function getSelectedValues(){
		var selected = new Array();
		$("#Form_VariationForm option:selected").each(function(el){
			if($(this).val() && $(this).val() != ''){
				selected.push($(this).val());
			}
		});
		return selected;
	}

	function disableOption($o){
		$o.addClass('disabled');
	}

	function enableOption($o){
		$o.removeClass('disabled');
	}

	function getAttributesNotJoinedWith(selected){

		var attrs = new Array();

		vloop: for(variation in variationsjson){
			for(var i = 0; i < selected.length; i++){
				if(!variationsjson[variation][selected[i]])
					continue vloop;
			}

			for(a in variationsjson[variation]){
				attrs.push(variationsjson[variation][a]);
			}
		}
		return attrs;

	}

	// Finds the first variation it can with the selected attributes
	function findVariation(selections){
		vloop: for(v in variationsjson){
			for(var i = 0; i < selections.length; i++){
				if(!variationsjson[v][selections[i]]){ //check that all values are in a possible variation attribute
					continue vloop;
				}
			}
			return variationsjson[v];
		}
		return null;
	}

})(jQuery);

