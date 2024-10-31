<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

class Ovas_Connect_Api_Fields {
	protected $fields      = array();
	protected $subfields   = array();
	protected $typefields  = array();
	protected $returnField = '';

	public $name               = 'APIFIELD';
	public $label              = 'Generic api field';
	public $order              = 0;
	public $uri                = null;
	public $defaultRequestType = 'POST';

	private $accounting = array( 'creditor', 'debtor', 'creditorPayterm', 'defaultReceiptsLedger', 'defaultPaymentsLedger' );
	private $costtype   = array( 'defaultReceiptsCosttype', 'defaultPaymentsCosttype' );
	private $response   = array( 'defaultReceiptsResponse', 'defaultPaymentsResponse' );

	public function getFields() {
		if ( get_class( $this ) === 'Ovas_Connect_Relations' ) {
			return array_intersect_key( $this->fields, array_flip( $this->typefields ) ); }
		if ( get_class( $this ) === 'Ovas_Connect_Relations_Company' ) {
			return array_intersect_key( $this->fields, array_flip( $this->typefields ) ); }
		if ( get_class( $this ) === 'Ovas_Connect_Relations_Church' ) {
			return array_intersect_key( $this->fields, array_flip( $this->typefields ) ); }
		return $this->fields;
	}

	public function getSubfields( $field = null ) {
		if ( $field !== null ) {
			return $this->subfields[ $field ] ?? null;
		}
		return $this->subfields;
	}

	public function getName() {
		return $this->name;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getOrder() {
		return $this->order;
	}

	public function getReturnField() {
		return $this->returnField;
	}

	public function getDefaultRequestType() {
		return $this->defaultRequestType;
	}

	public function getUri() {
		return $this->uri[ $this->defaultRequestType ];
	}

	// Sometimes the API expects an ID, but all we have is either a label that
	// might or might not match the API response.
	// If we have such an exception, manually map the values before sending to the API
	public function mapToApiValue( $field, $value ) {
		if ( isset( $this->apiFieldMap ) ) {
			if ( array_key_exists( $field, $this->apiFieldMap ) ) {
				if ( array_key_exists( $value, $this->apiFieldMap[ $field ] ) ) {
					return $this->apiFieldMap[ $field ][ $value ];
				}
			}
		}
		return $value;
	}
}
