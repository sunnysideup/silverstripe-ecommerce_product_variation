<?php


class CreateEcommerceVariations extends Controller {

	static $allowed_actions = array(
		"jsonforform",
		"createvariations",
		"select",
		"rename",
		"add",
		"delete",
		"move",
	);

	protected $_productID = 0;
	protected $_typeorvalue = "type"; // or value!
	protected $_id = 0;
	protected $_valueID = 0;
	protected $_name = "";
	protected $_position = -1; //use -1 to distinguish it from 0 (first in sorting order)
	protected $_select = -1;//use -1 to distinguish it from 0 (not selected)

	function init() {
		parent::init();
		if(!Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need CMS rights to access it. Enter your credentials below and we will send you right along.'));
		}
		if(isset($_GET["typeorvalue"])) { $this->_typeorvalue = $_GET["_typeorvalue"];}
		if(isset($_GET["id"])) { $this->_id = intval($_GET["_id"]);}
		if(isset($_GET["name"])) { $this->_name = urldecode($_GET["name"]);}
		if(isset($_GET["position"])) { $this->_position = intval($_GET["_position"]);}
		if(isset($_GET["select"])) { $this->_select = intval($_GET["select"]);}
	}

	function createvariations() {
		die("not completed yet");
		LeftAndMain::forceReload();
	}
	function jsonforform() {
		//create dataobjectset here...
		$jsonTypeArray = array();
		$jsonValueArray = array();
		$typeDos = DataObject::get("ProductAttributeType");
		if($typeDos) {
			$json = '{ "TypeSize": '.$typeDos->count().', "TypeItems": [ ';
			foreach($typeDos as $typeDo) {
				$jsonTypeStringForArray = '{';
				$typeDo->IsSelected = "to be coded";
				$typeDo->CanDeleteType = $typeDo->canDelete();
				$valueDos = $typeDo->Values();
				$jsonTypeStringForArray .= '"TypeID": "'.$typeDo->ID.'", "TypeName": "'.$typeDo->Name.'", "TypeIsSelected": "'.$typeDo->IsSelected.'", "CanDeleteType": "'.$typeDo->CanDeleteType.'"';
				if($valueDos) {
					$jsonTypeStringForArray .= ', "ValueSize": '.$valueDos->count().', "ValueItems": [';
					foreach($valueDos as $valueDo) {
						$jsonValueStringForArray = '{';
						$valueDo->IsSelected = "to be coded";
						$valueDo->CanDeleteValue = $valueDo->canDelete();
						$jsonValueStringForArray .= '"ValueID": "'.$valueDo->ID.'", "ValueName": "'.$valueDo->Value.'", "ValueIsSelected": "'.$valueDo->IsSelected.'", "CanDeleteValue": "'.$valueDo->CanDeleteValue.'"';
						$jsonValueStringForArray .= '}';
						$jsonValueArray[] = $jsonValueStringForArray;
					}
					$jsonTypeStringForArray .= implode(",", $jsonValueArray).']';
				}
				$jsonTypeStringForArray .=  "}";
				$jsonTypeArray[] = $jsonTypeStringForArray;
			}
			$json .= implode(",", $jsonTypeArray);
			$json .= '] ';
		}
		$json .= '} ';
		return $json;
	}

	function select() {
		//is it Type or Value?
		//is it select or unselect
		//save value
		die("not completed yet");
		return "ok";
	}
	function rename() {
		//is it Type or Value?
		//save new name
		die("not completed yet");
		return jsonforform();
	}
	function add() {
		//is it Type or Value?
		//can the item be added
		// add item
		die("not completed yet");
		return jsonforform();
	}
	function delete() {
		//is it Type or Value?
		//can the item be deleted
		// delete item if it is allowed
		die("not completed yet");
		return jsonforform();
	}
	function move() {
		//is it Type or Value?
		//move Item
		die("not completed yet");
		return "ok";
	}

}


class CreateEcommerceVariations_Field extends LiteralField {

	function __construct($name, $additionalContent = '', $productID) {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
		Requirements::javascript("ecommerce_product_variation/jquery/CreateEcommerceVariationsField.js");
		Requirements::customScript("CreateEcommerceVariationsField.set_url('/createecommercevariations/')", "CreateEcommerceVariationsField_set_url");
		Requirements::customScript("CreateEcommerceVariationsField.set_productID(".$productID.")", "CreateEcommerceVariationsField_set_productID");
		Requirements::customScript("CreateEcommerceVariationsField.set_fieldID(".$this->Name().")", "CreateEcommerceVariationsField_set_fieldID");
		parent::__construct($name, $content);
	}

}
