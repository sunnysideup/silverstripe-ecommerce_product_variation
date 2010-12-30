


;(function($) {
	$(document).ready(
		function() {
			CreateEcommerceVariationsField.init();
		}
	);
	var CreateEcommerceVariationsField = {

		url: '',
			set_url: function(v) {this.url = v;},

		productID: 0,
			set_productID: function(v) {this.productID = v;},

		fieldID:"",
			set_fieldID: function(v) {this.fieldID = v;},

		init: function() {
			this.removeOldStuff();
			this.getDataFromServer();
			this.attachFunctions();
		}

		removeOldStuff: function() {
			jQuery("#"+this.fieldID).html("&nbsp;");
		}

		attachFunctions: function() {
			this.addType();
			this.addValue();
			this.renameType();
			this.renameValue();
			this.moveType();
			this.moveValue();
			this.selectType();
			this.selectValue();
			this.deleteType();
			this.deleteValue();
		},

		addType:function() {

			//reset form
			this.init();
		},

		addValue:function() {

			//reset form
			this.init();
		},

		renameType:function() {


			//reset form
			this.init();
		},

		renameValue:function() {

			//reset form
			this.init();
		},
		moveType:function() {

		},

		moveValue:function() {

		},
		selectType:function() {

		},

		selectValue:function() {

		},

		deleteType:function() {

			//reset form
			this.init();
		},

		deleteValue:function() {

			//reset form
			this.init();
		},

		getDataFromServer: function() {
			jQuery.get(
				CreateEcommerceVariationsField.url+'/jsonforform/'+productID+'/';
				function(data) {
					CreateEcommerceVariationsField.parseNodes(data);
					CreateEcommerceVariationsField.attachFunctions();

				}
			);
		},

		parseNodes: function(nodes) { // takes a nodes array and turns it into a <ol>
			var ol = document.createElement("ol");
			for(var i=0; i<nodes.length; i++) {
				ol.appendChild(CreateEcommerceVariationsField.parseNode(nodes[i]));
			}
			return ol;
		},

		parseNode: function(node) { // takes a node object and turns it into a <li>
			var li = document.createElement("li");
			li.innerHTML = node.title;
			li.className = node.class;
			if(node.nodes) li.appendChild(parseNodes(node.nodes));
			return li;
		}


	}
})(jQuery);


