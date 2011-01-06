<?php


class CreateEcommerceVariations extends Controller {

	static $allowed_actions = array(
		"jsonforform",
		"createvariations",
		"select",
		"rename",
		"add",
		"remove",
		"move",
	);

	protected $_productID = 0;
	protected $_product = null;
	protected $_typeorvalue = "type"; // or value!
	protected $_classname = "type"; // or value!
	protected $_namefield = "Name"; // or value!
	protected $_id = 0;
	protected $_value = "";
	protected $_position = -1; //use -1 to distinguish it from 0 (first in sorting order)
	protected $_message = ""; //use -1 to distinguish it from 0 (first in sorting order)
	protected $_messageclass = "good"; //use -1 to distinguish it from 0 (first in sorting order)
	protected $_selectedtypeid = array(); //use -1 to distinguish it from 0 (first in sorting order)
	protected $_selectedvalueid = array(); //use -1 to distinguish it from 0 (first in sorting order)

	protected static $session_name_for_selected_values = "SelectecedValues";


	function init() {
		parent::init();
		if(!Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need CMS rights to access it. Enter your credentials below and we will send you right along.'));
		}
		if(isset($_GET["typeorvalue"])) { $this->_typeorvalue = $_GET["typeorvalue"];}
		if(isset($_GET["id"])) { $this->_id = intval($_GET["id"]);}
		if(isset($_GET["value"])) { $this->_value = urldecode($_GET["value"]);}
		if(isset($_GET["position"])) { $this->_position = intval($_GET["_position"]);}
		if($this->_typeorvalue == "type") {
			$this->_classname = 'ProductAttributeType';
			$this->_namefield = 'Name';
		}
		else {
			$this->_classname = 'ProductAttributeValue';
			$this->_namefield = 'Value';
		}
		$this->_productID = $this->request->param("ProductID");
		$this->_product = DataObject::get_by_id("Product", $this->_productID);
		if(!$this->_product) {
			user_error("could not find product for ID: ".$this->_productID, E_USER_WARNING);
		}
		$this->_selectedtypeid = $this->_product->getArrayOfLinkedProductAttributeTypeIDs();
		$this->_selectedvalueid = $this->_product->getArrayOfLinkedProductAttributeValueIDs();
	}

	function createvariations() {
		$types = DataObject::get('ProductAttributeType');
		if($types) {
			$values = array();
			foreach($types as $type) {
				if(isset($_GET[$type->ID])) {
					$values[$type->ID] = explode(',', $_GET[$type->ID]);
				}
			}
			$cpt = 0;
			if(count($values) > 0) {
				$cpt = $this->_product->generateVariationsFromAttributeValues($values);
			}
			if($cpt > 0) {
				$this->_message = ($cpt == 1 ? '1 new variation has' : "$cpt new variations have") . ' been created successfully';
			}
			else {
				$this->_message = 'No new variations created';
			}
		}
		else {
			$this->_message = 'No attribute types';
		}
		return $this->jsonforform();
	}

	function jsonforform() {
		//create dataobjectset here...
		$jsonTypeArray = array();
		$jsonValueArray = array();
		$typeDos = DataObject::get("ProductAttributeType");
		if(!$this->_message) {
			$this->_message = _t("CreateEcommerceVariations.STARTEDITING", "Start editing the list below to create variations.");
		}
		$json = '{';
		if($typeDos) {
			$json = '{ "Message": "'.Convert::raw2att($this->_message).'","MessageClass": "'.Convert::raw2att($this->_messageclass).'", "TypeSize": '.$typeDos->count().', "TypeItems": [ ';
			foreach($typeDos as $typeDo) {
				$jsonTypeStringForArray = '{';
				$typeDo->IsSelected = isset($this->_selectedtypeid[$typeDo->ID]) ? 1 : 0;
				$typeDo->CanDelete = $typeDo->canDelete() ? 1 : 0;
				$valueDos = $typeDo->Values();
				$jsonTypeStringForArray .= '"TypeID": "'.$typeDo->ID.'", "TypeName": "'.Convert::raw2att($typeDo->Name).'", "TypeIsSelected": "'.$typeDo->IsSelected.'", "CanDelete": "'.$typeDo->CanDelete.'"';
				if($valueDos) {
					$jsonTypeStringForArray .= ', "ValueSize": '.$valueDos->count().', "ValueItems": [';
					$jsonValueArray = array();
					foreach($valueDos as $valueDo) {
						$jsonValueStringForArray = '{';
						$valueDo->IsSelected = isset($this->_selectedvalueid[$valueDo->ID]) ? 1 : 0;
						$valueDo->CanDelete = $valueDo->canDelete() ? 1 : 0;
						$jsonValueStringForArray .= '"ValueID": "'.$valueDo->ID.'", "ValueName": "'.Convert::raw2att($valueDo->Value).'", "ValueIsSelected": "'.$valueDo->IsSelected.'", "CanDelete": "'.$valueDo->CanDelete.'"';
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
		// is it type of Value?
		// if type is value -> create / delete Product Variation (if allowed)
		// elseif type is type - > add / remove selection...
		$this->_product->addAttributeType($obj);
		$this->_product->removeAttributeType($obj);
		die("not completed yet");
		return $this->jsonforform();
	}
	function rename() {
		//is it Type or Value?
		$obj = DataObject::get_by_id($this->_classname, $this->_id);
		if($obj) {
			$name = $obj->{$this->_namefield};
			$obj->{$this->_namefield} = $this->_value;
			$obj->write();
			$this->_message = _t("CreateEcommerceVariations.HASBEENRENAMED","$name has been renamed to ".$this->_value,".");
		}
		else {
			$this->_message = _t("CreateEcommerceVariations.CANNOTBEFOUND","Entry can not be found.");
			$this->_messageclass = "bad";
		}
		return $this->jsonforform();
	}
	function add() {
		//is it Type or Value?
		$obj = new $this->_classname();
		$obj->{$this->_namefield} = $this->_value;
		if($this->_id) {
			$obj->TypeID = $this->_id;
			$obj->write();
		}
		else {
			$obj->write();
			if($obj instanceOf ProductAttributeType) {
				$this->_product->addAttributeType($obj);
			}
			else {
				user_error($obj->Title ." should be an instance of ProductAttributeType", E_USER_WARNING);
			}
		}

		$this->_message = $this->_value.' '._t("CreateEcommerceVariations.HASBEENADDED",'has been added.');
		return $this->jsonforform();
	}
	function remove() {
		//is it Type or Value?
		$obj = DataObject::get_by_id($this->_classname, $this->_id);
		if($obj) {
			$name = $obj->{$this->_namefield};
			if($obj->canDelete()) {
				if($this->_typeorvalue == "type") {
					$this->_product->removeAttributeType($obj);
				}
				$obj->delete();
				$obj->destroy();
				$this->_message = _t("CreateEcommerceVariations.HASBEENDELETED","$name has been deleted.");
			}
			else {
				$this->_message = _t("CreateEcommerceVariations.CANNOTBEDELETED","$name can not be deleted (it is probably used in a sale).");
				$this->_messageclass = "bad";
			}
		}
		else {
			$this->_message = _t("CreateEcommerceVariations.CANNOTBEFOUND","Entry can not be found.");
			$this->_messageclass = "bad";
		}
		return $this->jsonforform();
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
		Requirements::javascript("ecommerce_product_variation/javascript/CreateEcommerceVariationsField.js");
		Requirements::customScript("CreateEcommerceVariationsField.set_url('createecommercevariations')", "CreateEcommerceVariationsField_set_url");
		Requirements::customScript("CreateEcommerceVariationsField.set_productID(".$productID.")", "CreateEcommerceVariationsField_set_productID");
		Requirements::customScript("CreateEcommerceVariationsField.set_fieldID('CreateEcommerceVariationsInner')", "CreateEcommerceVariationsField_set_fieldID");
		Requirements::themedCSS("CreateEcommerceVariationsField");
		$additionalContent .= $this->renderWith("CreateEcommerceVariations_Field");
		parent::__construct($name, $additionalContent);
	}

	function ProductVariationGetPluralName() {
		return Convert::raw2att(ProductVariation::get_plural_name());
	}

	function ProductAttributeTypeGetPluralName() {
		return Convert::raw2att(ProductAttributeType::get_plural_name());
	}
	function ProductAttributeValueGetPluralName() {
		return Convert::raw2att(ProductAttributeValue::get_plural_name());
	}

	function CheckboxField($name, $title) {
		return new CheckboxField($name, $title);
	}
	function TextField($name, $title) {
		return new TextField($name, $title);
	}

	function AttributeSorterLink() {
		if(class_exists("DataObjectSorterController")) {
			return DataObjectSorterController::popup_link($className = "ProductAttributeType", $filterField = "", $filterValue = "", $linkText = "Sort Types");
		}
	}
	function ValueSorterLink() {
		if(class_exists("DataObjectSorterController")) {
			return DataObjectSorterController::popup_link($className = "ProductAttributeValue", $filterField = "TypeChangeToId", $filterValue = "ID", $linkText = "sort values");
		}
	}

}
