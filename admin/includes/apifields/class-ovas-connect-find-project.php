<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Find_Project extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'projectId'  => array(
			'validation'     => 'notAllowed',
			'convertToDB'    => 'nullFLD',
			'convertFromDB'  => 'intFLD',
			'databaseTable'  => 'tbl_relations',
			'databaseColumn' => 'id',
			'required'       => false,
			'linkedTo'       => '',
			'linkhref'       => '',
		),
		'locationId' => array(
			'validation'     => 'isNumeric,listItemExists',
			'convertToDB'    => 'intFLD',
			'convertFromDB'  => 'intFLD',
			'databaseTable'  => 'tbl_projects_locations',
			'databaseColumn' => 'id',
			'required'       => true,
			'linkedTo'       => '',
			'linkhref'       => '/projectlocations',
		),
		'id'         => array(
			'validation'     => 'notAllowed',
			'convertToDB'    => 'nullFLD',
			'convertFromDB'  => 'intFLD',
			'databaseTable'  => 'tbl_relations',
			'databaseColumn' => 'id',
			'required'       => true,
			'linkedTo'       => 'locationId',
			'linkhref'       => '/projectlocations/{locationId}/projects',
		),
	);

	public $uri = array(
		'GET' => '/projectlocations/{locationId}/projects',
	);

	public $name               = 'find_project';
	public $label              = 'Select project';
	public $order              = 72;
	public $defaultRequestType = 'GET';
	protected $returnField     = 'projectId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Select project', 'ovas-connect' );
	}
}
