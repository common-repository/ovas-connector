<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Find_ProjectLocation extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'locationId' => array(
			'validation'     => 'notAllowed',
			'convertToDB'    => 'nullFLD',
			'convertFromDB'  => 'intFLD',
			'databaseTable'  => 'tbl_relations',
			'databaseColumn' => 'id',
			'required'       => false,
			'linkedTo'       => '',
			'linkhref'       => '',
		),
		'id'         => array(
			'validation'     => 'notAllowed',
			'convertToDB'    => 'nullFLD',
			'convertFromDB'  => 'intFLD',
			'databaseTable'  => 'tbl_relations',
			'databaseColumn' => 'id',
			'required'       => true,
			'linkedTo'       => '',
			'linkhref'       => '/projectlocations',
		),
	);

	public $uri = array(
		'GET' => '/projectlocations/{id}',
	);

	public $name               = 'find_projectlocation';
	public $label              = 'Select Project Location';
	public $order              = 70;
	public $defaultRequestType = 'GET';
	protected $returnField     = 'locationId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Select Project Location', 'ovas-connect' );
	}
}
