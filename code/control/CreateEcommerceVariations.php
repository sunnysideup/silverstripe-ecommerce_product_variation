<?php


class CreateEcommerceVariations extends Controller {

	static $allowed_actions = array(
		"jsonforform",
		"createvariations",
		"select",
		"rename",
		"add",
		"move",
	);

	protected $_productID = 0;
	protected $_typeorvalue = "type"; // or value!
	protected $_id = 0;
	protected $_valueID = 0;
	protected $_name = "";
	protected $_position = "";

	function init() {
		parent::init();
		if(isset($_GET["typeorvalue"])) { $this->_typeorvalue = intval($_GET["_typeorvalue"]);}
		if(isset($_GET["id"])) { $this->_id = intval($_GET["_id"]);}
		if(isset($_GET["name"])) { $this->_name = intval($_GET["name"]);}
		if(isset($_GET["position"])) { $this->_position = intval($_GET["_position"]);}
		if(!Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
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
				$valueDos = $typeDo->Values();
				$jsonTypeStringForArray .= '"TypeID": "'.$typeDo->ID.'", "TypeName": "'.$typeDo->Name.'", "TypeIsSelected": "'.$typeDo->IsSelected.'"';
				if($valueDos) {
					$jsonTypeStringForArray .= ', "ValueSize": '.$valueDos->count().', "ValueItems": [';
					foreach($valueDos as $valueDo) {
						$jsonValueStringForArray = '{';
						$valueDo->IsSelected = "to be coded";
						$jsonValueStringForArray .= '"ValueID": "'.$valueDo->ID.'", "ValueName": "'.$valueDo->Value.'", "ValueIsSelected": "'.$valueDo->IsSelected.'"';
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
		die("not completed yet");
		return "ok";
	}
	function rename() {
		die("not completed yet");
		return jsonforform();
	}
	function add() {
		die("not completed yet");
		return jsonforform();
	}
	function move() {
		die("not completed yet");
		return "ok";
	}

}


class CreateEcommerceVariations_Field extends LiteralField {

	function __construct($name, $content) {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
		Requirements::javascript("ecommerce_product_variation/jquery/CreateEcommerceVariationsField.js");
		parent::__construct($name, $content);
	}

}
