<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/*
Object classes for parsing, storing and working with the settings as stored in WP

Datasource
  - <n> sections (cf7 forms etc)
	- <n> apisource (Ovas API endpoints; find relation, add relation etc) [currently incorrectly called "fields"]
	  - <n> fieldmaps (mapped api fields to datasource fields)
		- <n> sub-fieldmaps (single or multilink)

*/

require_once plugin_dir_path( __FILE__ ) . 'settings/class-ovas-connect-settings-datasource.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-ovas-connect-settings-section.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-ovas-connect-settings-apisource.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-ovas-connect-settings-fieldmap.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-ovas-connect-settings-fieldmapitem.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/class-ovas-connect-settings-datasourcesettings.php';
