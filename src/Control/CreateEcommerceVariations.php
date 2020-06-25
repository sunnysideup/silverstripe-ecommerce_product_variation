<?php

namespace Sunnysideup\EcommerceProductVariation\Control;












use SilverStripe\Versioned\Versioned;
use Sunnysideup\Ecommerce\Model\Extensions\EcommerceRole;
use Sunnysideup\Ecommerce\Config\EcommerceConfig;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeType;
use Sunnysideup\EcommerceProductVariation\Model\TypesAndValues\ProductAttributeValue;
use Sunnysideup\Ecommerce\Pages\Product;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use Sunnysideup\EcommerceProductVariation\Model\Buyables\ProductVariation;
use SilverStripe\ORM\DB;



/**
 * this class helps to create/edit/delete variations
 *
 *
 */

class CreateEcommerceVariations extends Controller
{
    private static $allowed_actions = array(
        "jsonforform" => "ADMIN",
        "createvariations",
        "select",
        "rename",
        "add",
        "remove",
        "move",
        'cansavevariation'
    );

    /**
     * The selected Product (ID)
     * @var Int
     */
    protected $_productID = 0;

    /**
     * The select Product (Object)
     * @var Product
     */
    protected $_product = null;

    /**
     * type | value
     * @var String
     */
    protected $_typeorvalue = "type"; // or value!

    /**
     * ProductAttributeValue | ProductAttributeType
     * @var String
     */
    protected $_classname = "type"; // or value!

    /**
     * Name of the Name field
     * @var String
     */
    protected $_namefield = "Name";

    /**
     * Name of the Label field
     * only for ProductAttributeType
     * @var String
     */
    protected $_labelfield = "Label";

    /**
     * Id of the item being altered
     * or its parent...
     * @var Int
     */
    protected $_id = 0;

    /**
     * Value of the item being altered
     * @var String
     */
    protected $_value = "";

    /**
     * Position in the sorting order
     * use -1 to distinguish it from 0 (first in sorting order)
     * @var Int
     */
    protected $_position = -1;

    /**
     * Return message
     * @var String
     */
    protected $_message = "";

    /**
     * Type of message
     * good | bad | warning
     * @var String
     */
    protected $_messageclass = "good";

    /**
     * Type IDs that are selected in the PRODUCT
     * @var Array
     */
    protected $_selectedtypeid = [];

    /**
     * Value IDs that are selected in the PRODUCT
     * @var Array
     */
    protected $_selectedvalueid = [];

    /**
     * What is going to be sent back.
     * @var String
     */
    protected $output = "";

    /**
     * The name for the session varilable.
     * @var String
     */
    private static $session_name_for_selected_values = "SelectecedValues";

    /**
     *
     * @var String
     */
    private static $url_segment = "createecommercevariations";

    public function init()
    {
        parent::init();
        Versioned::set_reading_mode("Stage.Stage");
        $shopAdminCode = EcommerceConfig::get(EcommerceRole::class, "admin_permission_code");
        if (!Permission::check("CMS_ACCESS_CMSMain") && !Permission::check($shopAdminCode)) {
            return Security::permissionFailure($this, _t('Security.PERMFAILURE', ' This page is secured and you need CMS rights to access it. Enter your credentials below and we will send you right along.'));
        }
        if (isset($_GET["typeorvalue"])) {
            $this->_typeorvalue = $_GET["typeorvalue"];
        }
        if (isset($_GET["id"])) {
            $this->_id = intval($_GET["id"]);
        }
        if (isset($_GET["value"])) {
            $this->_value = urldecode($_GET["value"]);
        }
        if (isset($_GET["position"])) {
            $this->_position = intval($_GET["_position"]);
        }
        if ($this->_typeorvalue == "type") {
            $this->_classname = ProductAttributeType::class;
            $this->_namefield = 'Name';
        } else {
            $this->_classname = ProductAttributeValue::class;
            $this->_namefield = 'Value';
        }

        $this->_productID = $this->request->param("ProductID");
        $this->_product = Product::get()->byID($this->_productID);
        if (!$this->_product) {
            user_error("could not find product for ID: ".$this->_productID, E_USER_WARNING);
        }
        $this->_selectedtypeid = $this->_product->getArrayOfLinkedProductAttributeTypeIDs();
        $this->_selectedvalueid = $this->_product->getArrayOfLinkedProductAttributeValueIDs();
    }


    public function Link($action = null)
    {
        return Controller::join_links(
            Director::baseURL(),
            $this->Config()->get("url_segment"),
            $action
        );
    }

    public function index()
    {
        return 10;
    }

    public function Output()
    {
        return $this->output;
    }

    /**
     *
     * checks the selected types and values and
     * makes variations from it...
     */
    public function createvariations()
    {
        //lazy array
        $missingTypesID = array(-1 => -1);
        $missingTypes = [];
        foreach ($this->_selectedtypeid as $typeID) {
            if (! isset($_GET[$typeID])) {
                $missingTypesID[$typeID] = $typeID;
            }
        }
        $types = ProductAttributeType::get()->exclude(array("ID" => $missingTypesID));
        if ($types->count()) {
            $allTypesAndValues = [];
            foreach ($types as $type) {
                if (isset($_GET[$type->ID])) {
                    $oldValuesArray = explode(',', $_GET[$type->ID]);
                    $newValuesArray = [];
                    foreach ($oldValuesArray as $oldValuesArray_Key => $oldValuesArray_Value) {
                        $newValuesArray[$oldValuesArray_Value] = $oldValuesArray_Value;
                    }
                    $allTypesAndValues[$type->ID] = $newValuesArray;
                }
            }
            $cpt = 0;
            if (count($allTypesAndValues) > 0) {
                //create the variations...
                $cpt = $this->_product->generateVariationsFromAttributeValues($allTypesAndValues);
                //reset values in this class ...
                $this->_selectedtypeid = $this->_product->getArrayOfLinkedProductAttributeTypeIDs();
                $this->_selectedvalueid = $this->_product->getArrayOfLinkedProductAttributeValueIDs();
            }
            if ($cpt > 0) {
                $this->_message = ($cpt == 1 ? '1 new variation has' : "$cpt new variations have") . ' been created successfully';
            } else {
                $this->_message = 'No new variations created';
            }
        } else {
            $this->_message = 'No attribute types';
        }
        $this->output = $this->jsonforform();
        return $this->output;
    }


    /**
     *
     * @return String
     */
    public function jsonforform()
    {
        if (! $this->_message) {
            $this->_message = _t("CreateEcommerceVariations.STARTEDITING", "Start editing the list below to create variations.");
        }
        $result['Message'] = $this->_message;
        $result['MessageClass'] = $this->_messageclass;
        $types = ProductAttributeType::get();
        if ($types->count()) {
            foreach ($types as $type) {
                $resultType = array(
                    'ID' => $type->ID,
                    'Name' => $type->Name,
                    'EditLink' => $type->CMSEditLink(),
                    'Checked' => isset($this->_selectedtypeid[$type->ID]),
                    'Disabled' => ! $this->_product->canRemoveAttributeType($type),
                    'CanDelete' => $type->canDelete()
                );
                $values = $type->Values();
                if ($values) {
                    foreach ($values as $value) {
                        $resultType['Values'][] = array(
                            'ID' => $value->ID,
                            'Name' => $value->Value,
                            'EditLink' => $value->CMSEditLink(),
                            'Checked' => isset($this->_selectedvalueid[$value->ID]),
                            'CanDelete' => $value->canDelete()
                        );
                    }
                }
                $result['Types'][] = $resultType;
            }
        }
        $this->output =  Convert::array2json($result);
        return $this->output;
    }

    public function select()
    {
        // is it type of Value?
        // if type is value -> create / delete Product Variation (if allowed)
        // elseif type is type - > add / remove selection...
        $this->_product->addAttributeType($obj);
        $this->_product->removeAttributeType($obj);
        die("not completed yet");
        $this->output =  $this->jsonforform();
        return $this->output;
    }

    public function rename()
    {
        //is it Type or Value?

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $className = $this->_classname;

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $obj = $className::get()->byID($this->_id);
        if ($obj) {
            $name = $obj->{$this->_namefield};
            if ($obj instanceof ProductAttributeType) {
                $obj->{$this->_labelfield} = $this->_value;
                $name .= " (".$obj->{$this->_labelfield}.")";
            }
            $obj->{$this->_namefield} = $this->_value;
            $obj->write();
            $this->_message = _t("CreateEcommerceVariations.HASBEENRENAMED", "$name has been renamed to ".$this->_value, ".");
        } else {
            $this->_message = _t("CreateEcommerceVariations.CANNOTBEFOUND", "Entry can not be found.");
            $this->_messageclass = "bad";
        }
        $this->output =  $this->jsonforform();
        return $this->output;
    }

    /**
     * add a Type or a Value
     */
    public function add()
    {
        //is it Type or Value?
        $obj = new $this->_classname();
        $obj->{$this->_namefield} = $this->_value;
        if ($this->_id) {
            $obj->TypeID = $this->_id;
            $obj->write();
        } else {
            $obj->write();
            if ($obj instanceof ProductAttributeType) {
                $this->_product->addAttributeType($obj);
            }
        }
        $this->_selectedtypeid = $this->_product->getArrayOfLinkedProductAttributeTypeIDs();
        $this->_selectedvalueid = $this->_product->getArrayOfLinkedProductAttributeValueIDs();
        $this->_message = $this->_value.' '._t("CreateEcommerceVariations.HASBEENADDED", 'has been added.');
        $this->output =  $this->jsonforform();
        return $this->output;
    }

    /**
     * remove a Type or a Value
     */
    public function remove()
    {
        //is it Type or Value?

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $className = $this->_classname;

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $className (case sensitive)
  * NEW: $className (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $obj = $className::get()->byID($this->_id);
        if ($obj) {
            $name = $obj->{$this->_namefield};
            if ($obj->canDelete()) {
                if ($this->_typeorvalue == "type") {
                    $this->_product->removeAttributeType($obj);
                }
                $obj->delete();
                $obj->destroy();
                $this->_selectedtypeid = $this->_product->getArrayOfLinkedProductAttributeTypeIDs();
                $this->_selectedvalueid = $this->_product->getArrayOfLinkedProductAttributeValueIDs();
                $this->_message = _t("CreateEcommerceVariations.HASBEENDELETED", "$name has been deleted.");
            } else {
                $this->_message = _t("CreateEcommerceVariations.CANNOTBEDELETED", "$name can not be deleted (it is probably used in a sale).");
                $this->_messageclass = "bad";
            }
        } else {
            $this->_message = _t("CreateEcommerceVariations.CANNOTBEFOUND", "Entry can not be found.");
            $this->_messageclass = "bad";
        }
        $this->output =  $this->jsonforform();
        return $this->output;
    }

    public function move()
    {
        //is it Type or Value?
        //move Item
        die("not completed yet");
        $this->output =  "ok";
        return $this->output;
    }

    /**
     *
     *
     * @return Boolean
     */
    public function cansavevariation()
    {
        $variation = null;
        if (isset($_GET['variation'])) {
            $obj = ProductVariation::get()->byID(intval($_GET['variation']));
        }
        foreach ($this->_selectedtypeid as $typeID) {
            if (isset($_GET[$typeID])) {
                $value = $_GET[$typeID];
                if (! $variation && ! $value) {
                    $this->output =  false;
                    return $this->output;
                }
                if ($value) {
                    $values[$typeID] = $value;
                }
            } else {
                $this->output =  false;
                return $this->output;
            }
        }
        $variations = $this->_product->getComponents('Variations', $variation ? "\"ProductVariation\".\"ID\" != '$variation->ID'" : '');
        foreach ($variations as $otherVariation) {
            $otherValues = DB::query(
                "
                SELECT \"TypeID\", \"ProductAttributeValueID\"
                FROM \"ProductVariation_AttributeValues\"
                    INNER JOIN \"ProductAttributeValue\" ON \"ProductAttributeValue\".\"ID\" = \"ProductAttributeValueID\"
                WHERE \"ProductVariationID\" = '$otherVariation->ID' ORDER BY \"TypeID\""
            )->map()->toArray();
            if ($otherValues == $values) {
                $this->output =  false;
                return $this->output;
            }
        }
        $this->output =  true;
        return $this->output;
    }
}

