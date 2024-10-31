<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ovas_Connect_Settings_Fieldmap {
	public $id;
	public $isSubField;
	public $apiFieldName;
	public $mappedFieldItems = array();
	public $subFieldMap;
	public $multiLinkField;

	public function __construct( $id, $isSubField = false, $apiFieldName = null, $mappedFieldItems = null, $subFieldMap = null, $multiLinkField = null ) {
		$this->id               = $id;
		$this->isSubField       = $isSubField;
		$this->apiFieldName     = $apiFieldName;
		$this->mappedFieldItems = $mappedFieldItems ?? array();
		$this->subFieldMap      = $subFieldMap ?? array();
		$this->multiLinkField   = $multiLinkField;
	}

	public function addFieldMaps( $fieldmaps ) {
		array_push( $this->subFieldMap, $fieldmaps );
	}

	public function addFieldMapitems( $mappedFieldItems ) {
		array_push( $this->mappedFieldItems, $mappedFieldItems );
	}
}
