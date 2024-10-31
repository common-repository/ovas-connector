<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Mandates extends Ovas_Connect_api_Fields {
	protected $fields      = array(
		'mandateId'             => array(
			'validationInsert' => 'notAllowed',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'nullFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'id',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'directDebitContractId' => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'id_collection_contract',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/collectioncontracts',
		),
		'mandateTypeId'         => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'isNumeric,listItemExists',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'id_mandate_type',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/mandatetypes',
		),
		'mandateReference'      => array(
			'validationInsert' => 'required,uniqueMandate',
			'validationUpdate' => 'uniqueMandate',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'mandate_reference',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'bankaccount'           => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'bankaccount',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'iban'                  => array(
			'validationInsert' => 'required,ibanFLD',
			'validationUpdate' => 'ibanFLD',
			'convertToDB'      => 'stringFLD,uppercaseFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'iban',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'bic'                   => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD,uppercaseFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'bic',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'signaturePlace'        => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD,uppercaseFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'signature_place',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'signatureDate'         => array(
			'validationInsert' => 'required,dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'dateFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'signature_date',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'active'                => array(
			'validationInsert' => 'booleanFLD',
			'validationUpdate' => 'booleanFLD',
			'convertToDB'      => 'booleanFLD',
			'convertFromDB'    => 'booleanFLD',
			'databaseTable'    => 'tbl_mandates',
			'databaseColumn'   => 'active',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
	);
	public $uri            = array(
		'GET'    => '/relations/{relationId}/mandates/{id}',
		'POST'   => '/relations/{relationId}/mandates/',
		'PUT'    => '/relations/{relationId}/mandates/{id}',
		'DELETE' => '/relations/{relationId}/mandates/{id}',
	);
	public $name           = 'Mandates';
	public $label          = 'Mandates';
	public $order          = 30;
	protected $returnField = 'mandateId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Mandates', 'ovas-connect' );
	}
}
