<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Find_Relation extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'relationId' => array(
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
			'linkhref'       => '/relations',
		),
	);

	public $uri = array(
		'GET' => '/relations/{id}',
	);

	public $name               = 'find_relation';
	public $label              = 'Select relation';
	public $order              = 1;
	public $defaultRequestType = 'GET';
	protected $returnField     = 'relationId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Select relation', 'ovas-connect' );
	}
}
