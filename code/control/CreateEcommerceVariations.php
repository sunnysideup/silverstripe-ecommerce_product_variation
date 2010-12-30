<?php


class CreateEcommerceVariations extends Controller {

	static $allowed_actions = array(
		"jsonforform",
		"createvariations",
		"selectattribute",
		"selectattributevalue",
		"addattribute",
		"addattributevalue",
		"moveattribute",
		"moveattributevalue"

	);

	function init() {
		parent::init();
		if(!Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
	}

	function createvariations() {
		die("not completed yet");
	}
	function jsonforform() {
		//create dataobjectset here...
		$oldDos = DataObject::get("ProductAttributeValue");
		$oldDos = new DataObjectSet();
		foreach($oldDos as $oldDo) {
			$newDo = new DataObject();
			$newDo->ValueID = $oldDo->ID;
			$newDo->TypeID = $oldDo->TypeID;
			$newDo->ValueName = $oldDo->Value;
			$newDo->TypeName = $oldDo->Type()->Name;
		}
		$fields = array();
		$dataFormatter = new convertDataObjectSet();
		echo $dataFormatter->convertDataObjectSet($dos, $fields = null);
	}
	function selectattribute() {
		die("not completed yet");
		return "ok";
	}
	function selectattributevalue() {
		die("not completed yet");
		return "ok";
	}
	function addattribute() {
		die("not completed yet");
		return jsonforform();
	}
	function addattributevalue() {
		die("not completed yet");
		return jsonforform();
	}
	function moveattribute() {
		die("not completed yet");
		return "ok";
	}
	function moveattributevalue() {
		die("not completed yet");
		return "ok";
	}

}


class CreateEcommerceVariations_Field extends FormField() {
 /* equirements:
 - jquery
 - livequery
 - custom js
 */
}
