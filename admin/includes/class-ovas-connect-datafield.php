<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

class Ovas_Connect_DataField {
	public $id;
	public $name;
	public $type;
	public $fields = array();

	public function __construct( $id, $name, $type ) {
		$this->id   = $id;
		$this->name = $name;
		$this->type = $type;
	}

	public function addChild( $dataField ) {
		array_push( $this->fields, $dataField );
		return $this->fields;
	}
}
