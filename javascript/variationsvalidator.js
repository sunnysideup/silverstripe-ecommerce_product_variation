(function($){
	
	var variations = $.parseJSON($("#Form_VariationForm_VariationOptions").val());
	
	$("#Form_VariationForm select").change(function(){
		
		//check if variation(s) with selection combination exist
		
		//$('#Form_VariationForm select option').css('color','grey');
		//$('#Form_VariationForm fieldset').appendTo("<p class=\"message bad\">not available</p>");
		//$('#Form_VariationForm input.action').attr('disabled','disabled').addClass('disabled').hide();
		
		var selected = new Array();
		
		$("#Form_VariationForm option:selected").each(function(el){
			
			if($(this).val() && $(this).val() != ''){
				selected.push($(this).val());
			}
			
		});
		
		if(!findVariation(selected)){
			$('#Form_VariationForm input.action').attr('disabled','disabled').addClass('disabled').hide();
		}else{
			$('#Form_VariationForm input.action').removeAttr('disabled').removeClass('disabled').show();
		}
		
	});	

	/* Finds the first variation it can with the selected attributes */
	function findVariation(selections){
		vloop: for(v in variations){
			for(var i = 0; i < selections.length; i++){
				if(!variations[v][selections[i]]){ //check that all values are in a possible variation attribute
					continue vloop;
				}
			}
			return variations[v];
		}
		return null;
	}
	
})(jQuery);