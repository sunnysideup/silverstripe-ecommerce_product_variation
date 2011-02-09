<?php

class ProductAttributeValueDecoratorColour extends DataObjectDecorator {

	protected static $colour_name_in_attribute = "colour";
		static function get_colour_name_in_attribute() {return self::$colour_name_in_attribute;}
		static function set_colour_name_in_attribute($s) {self::$colour_name_in_attribute = $s;}

	protected static $colour_array = array();
		static function get_colour_array() {return self::$colour_array;}

	public function extraStatics() {
		return array (
			'db' => array(
				'RGBCode' => 'Varchar(6)',
				'ContrastRGBColour' => 'Varchar(6)'
			)
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		if($this->hasColour()) {
			$fields->addFieldToTab("Root.Colour", new TextField("RGBCode", "RGBCode"));
			$fields->addFieldToTab("Root.Colour", new TextField("ContrastRGBColour", "Contrast RGB Colour"));
		}
	}

	function hasColour() {
		if($this->ProductAttributeType() && ($this->ProductAttributeType()->Name == self::get_colour_name_in_attribute())) {
			return true;
		}
		return false;
	}

	function colourArray() {
		if($this->hasColour()) {
			return array("ID" => $this->ID, "Colour" => $this->RGBCode, "ContrastRGBColour" => $this->RGBCode);
		}
	}

}
