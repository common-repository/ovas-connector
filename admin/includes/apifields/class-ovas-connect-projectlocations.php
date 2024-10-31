<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_ProjectLocations extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'locationId'       => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_projects_locations',
			'databaseColumn'   => 'id',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'countryId'        => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_projects_locations',
			'databaseColumn'   => 'id_country',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/countries',
		),
		'locationStatusId' => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_projects_locations',
			'databaseColumn'   => 'id_location_status',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/locationstatus',
		),
		'title'            => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_projects_locations',
			'databaseColumn'   => 'title',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'description'      => array(
			'validationInsert' => '',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_projects_locations',
			'databaseColumn'   => 'description',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
	);

	public $uri            = array(
		'GET'    => '/projectlocations/{id}',
		'POST'   => '/projectlocations/',
		'PUT'    => '/projectlocations/{id}',
		'DELETE' => '/projectlocations/{id}',
	);
	public $name           = 'Projectlocations';
	public $label          = 'Add project Location';
	public $order          = 71;
	protected $returnField = 'locationId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Add project Location', 'ovas-connect' );
	}
}
