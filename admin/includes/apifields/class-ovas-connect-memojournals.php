<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_MemoJournals extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'journalId'   => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_memoriaal',
			'databaseColumn'   => 'id',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'daybookId'   => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_memoriaal',
			'databaseColumn'   => 'id_db',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/daybooks',
		),
		'description' => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_fin_memoriaal',
			'databaseColumn'   => 'description',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'date'        => array(
			'validationInsert' => 'required,dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'dateFLD',
			'databaseTable'    => 'tbl_fin_memoriaal',
			'databaseColumn'   => 'datum',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'number'      => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_memoriaal',
			'databaseColumn'   => 'nr',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
	);

	public $uri            = array(
		'GET'    => '/memojournals/{id}',
		'POST'   => '/memojournals/',
		'PUT'    => '/memojournals/{id}',
		'DELETE' => '/memojournals/{id}',
	);
	public $name           = 'Memojournals';
	public $label          = 'Memo Journals';
	public $order          = 60;
	protected $returnField = 'journalId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Memo Journals', 'ovas-connect' );
	}
}
