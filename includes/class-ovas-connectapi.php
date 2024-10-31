<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

class Ovas_ConnectAPI {

	private $apiKey      = null;
	private $apiLanguage = 'nl';
	private $apiContent  = 'json';
	private $plugin      = null;

	private $requestURL    = null;
	private $requestMethod = null;
	private $params        = array();

	private $response = null;

	public function __construct( $apiKey, $apiLanguage = 'nl', $apiContent = 'json', $plugin = null ) {
		$this->apiKey      = $apiKey;
		$this->apiLanguage = $apiLanguage;
		$this->plugin      = $plugin;
		if ( $apiContent === 'xml' || $apiContent === 'text/xml' ) {
			$this->apiContent = 'text/xml';
		} else {
			$this->apiContent = 'application/json';
		}
	}

	public function request( $requestURL, $requestMethod, $params = array() ) {
		$requestURL    = ( substr( $requestURL, 0, 1 ) === '/' ? 'https://api.ovas.nl/v3' . $requestURL : $requestURL );
		$requestMethod = strtoupper( trim( $requestMethod ) );
		$params        = ( ! is_array( $params ) ? array() : $params );

		$this->requestURL    = $requestURL;
		$this->requestMethod = $requestMethod;
		$this->params        = $params;

		$this->doRequest();
	}

	private function doRequest() {
		$this->response = null;

		if ( $this->requestMethod === 'GET' ) {
			if ( strpos( $this->requestURL, '?' ) === false && is_array( $this->params ) && count( $this->params ) > 0 ) {
				$this->requestURL .= '?' . http_build_query( $this->params );
			}
		}

		$headers = array(
			'api-key'      => $this->apiKey,
			'api-language' => $this->apiLanguage,
			'server'       => ( $this->plugin->api_environment === 'live' ? null : $this->plugin->api_environment ),
		);

		$options = array(
			'body'      => $this->params,
			'method'    => $this->requestMethod,
			'headers'   => $headers,
			'timeout'   => 10,
			'sslverify' => false,
		);

		$r              = wp_remote_request( $this->requestURL, $options );
		$this->response = array(
			'headers' => wp_remote_retrieve_headers( $r ),
			'body'    => wp_remote_retrieve_body( $r ),
		);
	}

	public function getResponse() {
		 return $this->response;
	}
}
