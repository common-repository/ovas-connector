<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Invoices extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'invoiceId'       => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_verkoop',
			'databaseColumn'   => 'id',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'daybookId'       => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_verkoop',
			'databaseColumn'   => 'id_db',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/daybooks',
		),
		'number'          => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_verkoop',
			'databaseColumn'   => 'nr',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'date'            => array(
			'validationInsert' => 'required,dateFLD',
			'validationUpdate' => 'required,dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'dateFLD',
			'databaseTable'    => 'tbl_fin_verkoop',
			'databaseColumn'   => 'invoicedate',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'description'     => array(
			'validationInsert' => 'required',
			'validationUpdate' => 'required',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_fin_verkoop',
			'databaseColumn'   => 'description',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'rules'           => array(
			'validationInsert' => 'required,invoiceRules',
			'validationUpdate' => 'required,invoiceRules',
			'convertToDB'      => '',
			'convertFromDB'    => '',
			'databaseTable'    => '',
			'databaseColumn'   => '',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'invoiceStatusId' => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => '',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_fin_verkoop',
			'databaseColumn'   => 'id_fin_verkoop_status',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'credittransfer'  => array(
			'validationInsert' => 'booleanFLD',
			'validationUpdate' => 'booleanFLD',
			'convertToDB'      => 'booleanFLD',
			'convertFromDB'    => 'booleanFLD',
			'databaseTable'    => 'tbl_fin_verkoop',
			'databaseColumn'   => 'betalingsbestand',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
	);

	protected $subfields = array(
		'rules' => array(
			'description' => array(
				'required' => true,
				'linkedTo' => '',
				'linkhref' => '',
			),
			'amount'      => array(
				'required' => true,
				'linkedTo' => '',
				'linkhref' => '',
			),
			'vatIncluded' => array(
				'required' => true,
				'linkedTo' => '',
				'linkhref' => '',
			),
			'ledgerId'    => array(
				'required' => true,
				'linkedTo' => '',
				'linkhref' => '/ledgers',
			),
			'costtypeId'  => array(
				'required' => false,
				'linkedTo' => '',
				'linkhref' => '/costtypes',
			),
			'responseId'  => array(
				'required' => false,
				'linkedTo' => '',
				'linkhref' => '/responses',
			),
		),
	);

	public $uri            = array(
		'GET'    => '/invoices/{id}',
		'POST'   => '/relations/{relationId}/invoices/',
		'PUT'    => '/relations/{relationId}/invoices/{id}',
		'DELETE' => '/relations/{relationId}/invoices/{id}',
	);
	public $name           = 'Invoices';
	public $label          = 'Invoices';
	public $order          = 20;
	protected $returnField = 'invoiceId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Invoices', 'ovas-connect' );
	}
}
