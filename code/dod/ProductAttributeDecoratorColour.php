<?php

class ProductAttributeDecoratorColour_Value extends DataObjectDecorator {

	protected static $colour_array = array();
		static function get_colour_array() {return self::$colour_array;}

	protected static $default_contrast_colour = "FFFFFF";
		static function get_default_contrast_colour() {return self::$default_contrast_colour;}
		static function set_default_contrast_colour() {return self::$default_contrast_colour;}

	protected static $default_colour = "000000";
		static function get_default_colour() {return self::$default_colour;}
		static function set_default_colour() {return self::$default_colour;}

	public function extraStatics() {
		return array (
			'db' => array(
				'RGBCode' => 'Varchar(6)',
				'ContrastRGBCode' => 'Varchar(6)'
			),
			'casting' => array(
				'ComputedRGBCode' => 'Varchar(6)',
				'ComputedContrastRGBCode' => 'Varchar(6)'
			)
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		if($this->hasColour()) {
			$fields->addFieldToTab("Root.Colour", new TextField("RGBCode", "RGBCode"));
			$fields->addFieldToTab("Root.Colour", new TextField("ContrastRGBCode", "Contrast RGB Colour"));
		}
		else {
			$fields->removeFieldFromTab("Root.Main", "RGBCode");
			$fields->removeFieldFromTab("Root.Main", "ContrastRGBCode");
		}
	}

	function hasColour() {
		if($this->owner->Type()) {
			return $this->owner->Type()->IsColour;
		}
	}

	function ComputedRGBCode() {
		$v = $this->owner->RGBCode;
		if(!$v) {
			$v = self::get_default_colour();
		}
		return $v;
	}

	function ComputedContrastRGBCode() {
		$v = $this->owner->ContrastRGBCode;
		if(!$v) {
			$v = self::get_default_contrast_colour();
		}
		return $v;
	}

	function updateValueForDropdown(&$v) {
		if($this->hasColour()) {
			$style = 'color: #'.$this->ComputedRGBCode().'; background-color: #'.$this->ComputedContrastRGBCode().';';
			$v = '<span style="'.$style.'">'.$v.'</span>';
		}
		return $v;
	}

}

class ProductAttributeDecoratorColour_Type extends DataObjectDecorator {

	public function extraStatics() {
		return array (
			'db' => array(
				'IsColour' => 'Boolean'
			)
		);
	}


	function updateCMSFields(FieldSet &$fields) {
		$fields->addFieldToTab("Root.Colour", new CheckboxField("IsColour", _t("ProductAttributeDecoratorColour.ISCOLOUR", "Is Colour")));
	}


}
