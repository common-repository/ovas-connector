<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ovas.nl
 * @since             1.0.0
 * @package           Ovas_Connect
 *
 * @wordpress-plugin
 * Plugin Name:       Ovas Connector
 * Plugin URI:        https://ovas.nl
 * Description:       Connect WordPress with Ovas Connect
 * Version:           1.1.1
 * Author:            Ovas Solutions
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ovas-connect
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'OVAS_CONNECT_VERSION', '1.1.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ovas-connect-activator.php
 */
function activate_ovas_connect() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ovas-connect-activator.php';
	Ovas_Connect_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ovas-connect-deactivator.php
 */
function deactivate_ovas_connect() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ovas-connect-deactivator.php';
	Ovas_Connect_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ovas_connect' );
register_deactivation_hook( __FILE__, 'deactivate_ovas_connect' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ovas-connect.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ovas_connect() {
	// Replace superfluous WordPress ob flush with a proper implementation
	remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
	add_action(
		'shutdown',
		function () {
			while ( @ob_end_flush() ) {
			}
		}
	);
	$plugin = new Ovas_Connect();
	$plugin->run();

	// Add 'settings' option to the list of actions on the WP plugin list page
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $plugin, 'ovas_connect_plugin_action_links' ) );
}
run_ovas_connect();
