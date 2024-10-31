<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://ovas.nl
 * @since      1.0.0
 *
 * @package    Ovas_Connect
 * @subpackage Ovas_Connect/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ovas_Connect
 * @subpackage Ovas_Connect/includes
 * @author     Ovas Solutions <info@ovas.nl>
 */
class Ovas_Connect_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ovas-connect',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
