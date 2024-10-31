<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ovas_Connect_Settings_Apisource {
	public $id;
	public $fieldMaps = array();

	public function __construct( $id, $fieldMaps = null ) {
		$this->id        = $id;
		$this->fieldMaps = $fieldMaps ?? array();
	}

	public function addFieldMaps( $fieldmaps ) {
		if ( $fieldmaps ) {
			array_push( $this->fieldMaps, $fieldmaps );
		}
	}
}
