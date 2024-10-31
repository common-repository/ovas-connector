<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Relations_Company extends Ovas_Connect_Relations {
	public $name          = 'Relations_company';
	public $label         = 'Add relation (company)';
	public $order         = 4;
	protected $typefields = array( 'relationId', 'relationNumber', 'relationTypeId', 'relationStatusId', 'sectorId', 'subSectorId', 'companyName', 'companySubname', 'cocNumber', 'vatNumber', 'street', 'number', 'addon', 'zip', 'residence', 'countryId', 'visitStreet', 'visitNumber', 'visitAddon', 'visitZip', 'visitResidence', 'visitCountryId', 'email', 'telephone', 'fax', 'website', 'bankaccount', 'iban', 'bic', 'notes', 'thanks', 'noEmail', 'noLetters', 'creditorPayterm', 'defaultReceiptsLedger', 'defaultPaymentsLedger', 'defaultReceiptsCosttype', 'defaultPaymentsCosttype', 'defaultReceiptsResponse', 'defaultPaymentsResponse' );

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Add relation (company)', 'ovas-connect' );
	}
}
