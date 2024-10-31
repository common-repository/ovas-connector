<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ovas_Connect_Settings_FieldMapItem {
	public $key;
	public $value;
	public $label;
	public $order;

	public function __construct( $key, $value, $label = null, $order = 0 ) {
		$this->key   = $key;
		$this->value = $value;
		$this->label = $label;
		$this->order = $order;
	}
}
