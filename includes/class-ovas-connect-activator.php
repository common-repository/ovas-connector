<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

/**
 * Fired during plugin activation
 *
 * @link       https://ovas.nl
 * @since      1.0.0
 *
 * @package    Ovas_Connect
 * @subpackage Ovas_Connect/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ovas_Connect
 * @subpackage Ovas_Connect/includes
 * @author     Ovas Solutions <info@ovas.nl>
 */
class Ovas_Connect_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ovas_connect_log';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  datasource varchar(55),
		  logline text DEFAULT '',
          paymentid varchar(16),
		  PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
