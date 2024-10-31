<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ovas_Connect_Settings_Section {
	public $id;
	public $label;
	public $enabled;
	public $apisources = array();

	public function __construct( $id, $label = '', $enabled = false, $apisources = null ) {
		$this->id         = $id;
		$this->label      = $label;
		$this->enabled    = $enabled;
		$this->apisources = $apisources ?? array();
	}

	public function setEnabled( $enabled ) {
		$this->enabled = $enabled;
	}

	public function addApiSources( $apisources ) {
		if ( $apisources ) {
			array_push( $this->apisources, $apisources );
		}
	}
}
