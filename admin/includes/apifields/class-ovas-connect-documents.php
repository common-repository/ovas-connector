<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Documents extends Ovas_Connect_api_Fields {
	protected $fields      = array(
		'documentId'       => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'id',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'documentTypeId'   => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'id_doctype',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/documenttypes',
		),
		'documentStatusId' => array(
			'validationInsert' => 'isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'id_status',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '/documentstatus',
		),
		'contactId'        => array(
			'validationInsert' => 'isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'id_contactpersoon',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '/contacts',
		),
		'description'      => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'description',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'text'             => array(
			'validationInsert' => '',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'txt',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'email'            => array(
			'validationInsert' => 'emailFLD',
			'validationUpdate' => 'emailFLD',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'email',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'creationDate'     => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'dateFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'createdate',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'sendDate'         => array(
			'validationInsert' => 'dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'dateFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'senddate',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'favourite'        => array(
			'validationInsert' => 'booleanFLD',
			'validationUpdate' => 'booleanFLD',
			'convertToDB'      => 'booleanFLD',
			'convertFromDB'    => 'booleanFLD',
			'databaseTable'    => 'tbl_docs',
			'databaseColumn'   => 'favoriet',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
	);
	public $uri            = array(
		'GET'    => '/relations/{relationId}/documents/{id}',
		'POST'   => '/relations/{relationId}/documents/',
		'PUT'    => '/relations/{relationId}/documents/{id}',
		'DELETE' => '/relations/{relationId}/documents/{id}',
	);
	public $name           = 'Documents';
	public $label          = 'Documents';
	public $order          = 50;
	protected $returnField = 'documentId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Documents', 'ovas-connect' );
	}
}
