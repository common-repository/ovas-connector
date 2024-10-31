<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

class Ovas_Connect_LogItem {
	public $action   = null;
	public $logLine  = null;
	public $request  = null;
	public $response = null;

	public function __construct( $action, $logLine, $request = null, $response = null ) {
		$this->action   = $action;
		$this->logLine  = $logLine;
		$this->request  = $request;
		$this->response = $response;
	}
}
