<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-api-fields.php';

class Ovas_Connect_Relations_Church extends Ovas_Connect_Relations {
	public $name          = 'Relations_church';
	public $label         = 'Add relation (church)';
	public $order         = 5;
	protected $typefields = array( 'relationId', 'relationNumber', 'relationTypeId', 'relationStatusId', 'genderId', 'maritalStatusId', 'religiousStatusId', 'religiousDenominationId', 'pastoralUnitTitle', 'familyRelationshipId', 'relationTitleId', 'initials', 'officialname', 'firstname', 'intersert', 'lastname', 'intersertPartner', 'lastnamePartner', 'nameUsageId', 'notes', 'street', 'number', 'addon', 'zip', 'residence', 'countryId', 'email', 'telephone', 'mobile', 'fax', 'bankaccount', 'iban', 'bic', 'thanks', 'noEmail', 'noLetters', 'dateOfBirth', 'placeOfBirth', 'dateOfRegistration', 'placeOfRegistration', 'religiousDenominationOfRegistration', 'dateOfDeregistration', 'placeOfDeregistration', 'religiousDenominationOfDeregistration', 'dateOfBaptism', 'placeOfBaptism', 'religiousDenominationOfBaptism', 'dateOfConfession', 'placeOfConfession', 'religiousDenominationOfConfession', 'dateOfWedding', 'placeOfWedding', 'dateOfReligiousWedding', 'placeOfReligiousWedding', 'religiousDenominationOfReligiousWedding', 'dateOfDivorce', 'placeOfDivorce', 'reasonOfDivorce', 'dateOfDeceased', 'placeOfDeceased', 'reasonOfDeceased', 'creditorPayterm', 'defaultReceiptsLedger', 'defaultPaymentsLedger', 'defaultReceiptsCosttype', 'defaultPaymentsCosttype', 'defaultReceiptsResponse', 'defaultPaymentsResponse' );

	// Hardcoded string needed for WP translation
	public function getLabel() {
		return __( 'Add relation (church)', 'ovas-connect' );
	}
}
