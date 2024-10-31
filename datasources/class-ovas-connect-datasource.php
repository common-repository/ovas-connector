<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

class Ovas_Connect_Datasource {
	public $plugin         = null;
	public $dataSourceName = 'Generic datasource';
	public $dataSourceID   = 'GEN';
	public $options;

	public function __construct( object $plugin = null ) {
		$this->plugin = $plugin;
		$this->addHooks();
	}

	public function getDataSourceName() {
		return $this->dataSourceName;
	}

	public function getDataSourceID() {
		return $this->dataSourceID;
	}

	public function getFields() {
		return null;
	}

	public function getAdminFormFields() {
		return null;
	}

	public function addHooks() {
		return null;
	}

	public function getSubFieldMultilinkObjects() {
		return null;
	}

	public function isDataSourceEnabled() {
		$this->options = get_option( 'ovas_options' );
		if ( isset( $this->options[ 'ovas_datasource_' . $this->dataSourceID ] ) ) {
			return $this->options[ 'ovas_datasource_' . $this->dataSourceID ] === '1';
		}
		return false;
	}

	public function submitFormToConnect( $id ) {
		$this->plugin->submitFormToConnect( $id );
	}

	// GENERAL USAGE FUNCTIONS
	public function startsWith( $string, $startString ) {
		$len = strlen( $startString );
		return ( substr( $string, 0, $len ) === $startString );
	}

	public function getSections( $fields ) {
		$sections = array();
		foreach ( $fields->fields as $field ) {
			if ( $field->type === dataFieldType::section ) {
				array_push( $sections, $field );
			}
		}
		return $sections;
	}
}
