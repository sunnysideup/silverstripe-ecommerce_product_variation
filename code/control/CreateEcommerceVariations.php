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
		if(isset($_GET["typeorvalue"])) { $this->_typeorvalue = intval($_GET["_typeorvalue"]);
		if(isset($_GET["id"])) { $this->_id = intval($_GET["_id"]);
		if(isset($_GET["name"])) { $this->_name = intval($_GET["name"]);
		if(isset($_GET["position"])) { $this->_position = intval($_GET["_position"]);
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
		$oldDos = DataObject::get("Productvalue");
		$newDos = new DataObjectSet();
		foreach($oldDos as $oldDo) {
			$newDo = new DataObject();
			$newDo->ValueID = $oldDo->ID;
			$newDo->TypeID = $oldDo->TypeID;
			$newDo->ValueName = $oldDo->Value;
			$newDo->TypeName = $oldDo->Type()->Name;
			$newDo->ValueIsSelected = "to be completed";
			$newDo->TypeIsSelected = "to be completed";
			$newDos->push($newDo);
		}
		$fields = array();
		$dataFormatter = new convertDataObjectSet();
		echo $dataFormatter->convertDataObjectSet($dos, $fields = null);
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


class CreateEcommerceVariations_Field extends LiteralField() {

	function __construct($name, $content) {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
		Requirements::javascript("ecommerce_product_variation/jquery/CreateEcommerceVariationsField.js");
		parent::__construct($name, $content);
	}

}
