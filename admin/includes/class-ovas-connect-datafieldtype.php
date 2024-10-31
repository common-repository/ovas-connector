<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

final class Ovas_Connect_DataFieldType {
	public const ROOT     = 0;
	public const FIELD    = 1;
	public const CATEGORY = 2;
	public const SECTION  = 3;

	private function __construct() {
		throw new Exception( "Can't get an instance of dataFieldType" );
	}
}
