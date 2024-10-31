<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Subscriptions extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'subscriptionId' => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_subscribtions',
			'databaseColumn'   => 'id',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'number'         => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_subscribtions',
			'databaseColumn'   => 'subscribtionnr',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'subscrTypeId'   => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_subscribtions',
			'databaseColumn'   => 'id_kind',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/subscriptiontypes',
		),
		'startDate'      => array(
			'validationInsert' => 'required,dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_subscribtions',
			'databaseColumn'   => 'startdate',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'endDate'        => array(
			'validationInsert' => 'dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'booleanFLD',
			'databaseTable'    => 'tbl_subscribtions',
			'databaseColumn'   => 'enddate',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'quantity'       => array(
			'validationInsert' => 'isNumeric',
			'validationUpdate' => 'isNumeric',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_subscribtions',
			'databaseColumn'   => 'quantity',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'emails'         => array(
			'validationInsert' => 'emailArray',
			'validationUpdate' => 'emailArray',
			'convertToDB'      => '',
			'convertFromDB'    => '',
			'databaseTable'    => '',
			'databaseColumn'   => '',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
	);
	public $uri       = array(
		'GET'    => '/subscriptions/{id}',
		'POST'   => '/relations/{relationId}/subscriptions/',
		'PUT'    => '/subscriptions/{relationId}',
		'DELETE' => '/subscriptions/{relationId}',
	);
	public $name      = 'Subscriptions';
	public $label     = 'Subscriptions';
	public $order     = 11;

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Subscriptions', 'ovas-connect' );
	}
}
