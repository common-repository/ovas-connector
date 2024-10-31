<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . '../includes/class-ovas-connect-logitem.php';

final class Ovas_Connect_Log {
	public function __construct() {
		throw new Exception( "Can't get an instance of Ovas_Connect_Log" );
	}

	public static function addLog( $datasource, $action, $logline, $request = null, $response = null, $paymentid = null ) {
		global $wpdb;

		$logItem = new Ovas_Connect_LogItem( $action, $logline, $request, $response );
		$logJSON = wp_json_encode( $logItem );

		$table_name = $wpdb->prefix . 'ovas_connect_log';

		$wpdb->insert(
			$table_name,
			array(
				'timestamp'  => gmdate( 'Y-m-d H:i:s' ),
				'datasource' => $datasource->getDataSourceID(),
				'logline'    => $logJSON,
				'paymentid'  => $paymentid,
			)
		);

		return $wpdb->insert_id;
	}

	public static function getLog( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ovas_connect_log';
		$dbitem     = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE id = %s', $table_name, $id ) );

		return $dbitem;
	}

	public static function deleteLog( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ovas_connect_log';
		$retVal     = $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );

		return $retVal;
	}

	public static function addResponse( $id, $logline ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ovas_connect_log';

		$wpdb->update( $table_name, array( 'logline' => wp_json_encode( $logline ) ), array( 'id' => $id ) );
	}
}
