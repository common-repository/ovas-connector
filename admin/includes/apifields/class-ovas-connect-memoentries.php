<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_MemoEntries extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'entryId'     => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_memoriaal_regels',
			'databaseColumn'   => 'id',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'ledgerId'    => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_memoriaal_regels',
			'databaseColumn'   => 'id_gb',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/ledgers',
		),
		'date'        => array(
			'validationInsert' => 'required,dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'dateFLD',
			'databaseTable'    => 'tbl_fin_memoriaal_regels',
			'databaseColumn'   => 'datum',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'amount'      => array(
			'validationInsert' => 'required,amountFLD',
			'validationUpdate' => 'amountFLD',
			'convertToDB'      => 'amountFLD',
			'convertFromDB'    => 'numericFLD',
			'databaseTable'    => 'tbl_fin_memoriaal_regels',
			'databaseColumn'   => 'bedrag',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'description' => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_fin_memoriaal_regels',
			'databaseColumn'   => 'description',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'costtypeId'  => array(
			'validationInsert' => 'isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_memoriaal_regels',
			'databaseColumn'   => 'id_kpl',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '/costtypes',
		),
		'responseId'  => array(
			'validationInsert' => 'isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_memoriaal_regels',
			'databaseColumn'   => 'id_response',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '/responses',
		),
	);

	public $uri            = array(
		'GET'    => '/memojournals/{journalId}/memoentries/{id}',
		'POST'   => '/memojournals/{journalId}/memoentries/',
		'PUT'    => '/memojournals/{journalId}/memoentries/{id}',
		'DELETE' => '/memojournals/{journalId}/memoentries/{id}',
	);
	public $name           = 'Memoentries';
	public $label          = 'Memo Entries';
	public $order          = 61;
	protected $returnField = 'entryId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Memo Entries', 'ovas-connect' );
	}
}
