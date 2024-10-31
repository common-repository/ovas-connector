<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ovas_Connect_Settings_Datasource {
	public $id;
	public $sections = array();

	public function __construct( $id, $sections = null ) {
		$this->id       = $id;
		$this->sections = $sections ?? array();
	}

	public function addSections( $section ) {
		if ( $section ) {
			array_push( $this->sections, $section );
		}
	}
}
