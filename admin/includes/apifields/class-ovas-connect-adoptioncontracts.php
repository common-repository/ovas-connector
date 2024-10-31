<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_AdoptionContracts extends Ovas_Connect_api_Fields {
	protected $fields = array(
		'adoptionId'  => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_adoption_contract',
			'databaseColumn'   => 'id_adoption',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/adoptions',
		),
		'relationId'  => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_adoption_contract',
			'databaseColumn'   => 'id_relation',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/relations',
		),
		'startDate'   => array(
			'validationInsert' => 'required,dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_adoption_contract',
			'databaseColumn'   => 'startdate',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'endDate'     => array(
			'validationInsert' => 'dateFLD',
			'validationUpdate' => 'dateFLD',
			'convertToDB'      => 'dateFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_adoption_contract',
			'databaseColumn'   => 'enddate',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'amount'      => array(
			'validationInsert' => 'required,amountFLD',
			'validationUpdate' => 'amountFLD',
			'convertToDB'      => '',
			'convertFromDB'    => '',
			'databaseTable'    => 'tbl_adoption_contract',
			'databaseColumn'   => 'amount',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'paymethodId' => array(
			'validationInsert' => 'required,isNumeric,listItemExists',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_adoption_contract',
			'databaseColumn'   => 'id_paymethod',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '/promisespaymethods',
		),
		'mandateId'   => array(
			'validationInsert' => 'isNumeric',
			'validationUpdate' => 'notAllowed',
			'convertToDB'      => 'intFLD',
			'convertFromDB'    => 'intFLD',
			'databaseTable'    => 'tbl_promises',
			'databaseColumn'   => 'id_mandate',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'iban'        => array(
			'validationInsert' => 'ibanFLD',
			'validationUpdate' => 'ibanFLD',
			'convertToDB'      => 'ibanFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_promises',
			'databaseColumn'   => 'iban',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'bic'         => array(
			'validationInsert' => '',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD,uppercaseFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_promises',
			'databaseColumn'   => 'bic',
			'required'         => false,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
		'description' => array(
			'validationInsert' => 'required',
			'validationUpdate' => '',
			'convertToDB'      => 'stringFLD',
			'convertFromDB'    => 'stringFLD',
			'databaseTable'    => 'tbl_promises',
			'databaseColumn'   => 'description',
			'required'         => true,
			'linkedTo'         => '',
			'linkhref'         => '',
		),
	);

	public $uri            = array(
		'GET'    => '/adoptioncontracts/{id}',
		'POST'   => '/adoptioncontracts',
		'PUT'    => '/adoptioncontracts/{id}',
		'DELETE' => '/adoptioncontracts/{id}',
	);
	public $name           = 'Adoptioncontracts';
	public $label          = 'Adoption Contracts';
	public $order          = 90;
	protected $returnField = 'contractId';

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Adoption Contracts', 'ovas-connect' );
	}
}
