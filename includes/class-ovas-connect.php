<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

/**
* The file that defines the core plugin class
*
* A class definition that includes attributes and functions used across both the
* public-facing side of the site and the admin area.
*
* @link       https://ovas.nl
* @since      1.0.1
*
* @package    Ovas_Connect
* @subpackage Ovas_Connect/includes
*/

/**
* The core plugin class.
*
* This is used to define internationalization, admin-specific hooks, and
* public-facing site hooks.
*
* Also maintains the unique identifier of this plugin as well as the current
* version of the plugin.
*
* @since      1.0.0
* @package    Ovas_Connect
* @subpackage Ovas_Connect/includes
* @author     Ovas Solutions <info@ovas.nl>
*/

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Gateways\DigiWallet\Integration;

spl_autoload_register(
	function ( $class_name ) {
		$sources = array( '../datasources/', '../admin/includes/apifields/' );
		foreach ( $sources as $source ) {
			$file = plugin_dir_path( __FILE__ ) . $source . 'class-' . str_replace( '_', '-', $class_name ) . '.php';
			if ( file_exists( $file ) ) {
				include $file;
			}
		}
	}
);

class Ovas_Connect {
	/**
	* The loader that's responsible for maintaining and registering all hooks that power
	* the plugin.
	*
	* @since    1.0.0
	* @access   protected
	* @var      Ovas_Connect_Loader    $loader    Maintains and registers all hooks for the plugin.
	*/
	protected $loader;

	/**
	* The unique identifier of this plugin.
	*
	* @since    1.0.0
	* @access   protected
	* @var      string    $plugin_name    The string used to uniquely identify this plugin.
	*/
	protected $plugin_name;

	/**
	* The current version of the plugin.
	*
	* @since    1.0.0
	* @access   protected
	* @var      string    $version    The current version of the plugin.
	*/
	protected $version;

	protected $datasources = array();
	protected $apisources  = array();
	public $log_table_name;
	private $options;
	public $meta_name;
	public $api_environment;

	/**
	* Define the core functionality of the plugin.
	*
	* Set the plugin name and the plugin version that can be used throughout the plugin.
	* Load the dependencies, define the locale, and set the hooks for the admin area and
	* the public-facing side of the site.
	*
	* @since    1.0.0
	*/
	public function __construct() {
		global $wpdb;
		global $settings;

		if ( defined( 'OVAS_CONNECT_VERSION' ) ) {
			$this->version = OVAS_CONNECT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name    = 'ovas-connect';
		$this->log_table_name = $wpdb->prefix . 'ovas_connect_log';
		$this->meta_name      = $this->plugin_name . '_meta';

		// null value > live
		$this->api_environment = get_option( 'ovas_options' )['ovas_api_env'] ?? null;
		if ( $this->api_environment === '' || $this->api_environment === 'live' ) {
			$this->api_environment = null;
		}

		$this->load_dependencies();
		$this->set_locale();

		$this->initialise_datasources();
		$this->initialise_apisources();

		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	* Load the required dependencies for this plugin.
	*
	* Include the following files that make up the plugin:
	*
	* - Ovas_Connect_Loader. Orchestrates the hooks of the plugin.
	* - Ovas_Connect_i18n. Defines internationalization functionality.
	* - Ovas_Connect_Admin. Defines all hooks for the admin area.
	* - Ovas_Connect_Public. Defines all hooks for the public side of the site.
	*
	* Create an instance of the loader which will be used to register the hooks
	* with WordPress.
	*
	* @since    1.0.0
	* @access   private
	*/
	private function load_dependencies() {
		/**
		* The class responsible for orchestrating the actions and filters of the
		* core plugin.
		*/
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-ovas-connect-loader.php';

		/**
		* The class responsible for defining internationalization functionality
		* of the plugin.
		*/
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-ovas-connect-i18n.php';

		/**
		* The class responsible for defining all actions that occur in the admin area.
		*/
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-ovas-connect-admin.php';

		/**
		* The class responsible for defining all actions that occur in the public-facing
		* side of the site.
		*/
		require_once plugin_dir_path( __DIR__ ) . 'public/class-ovas-connect-public.php';

		$this->loader = new Ovas_Connect_Loader();
	}

	/**
	* Define the locale for this plugin for internationalization.
	*
	* Uses the Ovas_Connect_i18n class in order to set the domain and to register the hook
	* with WordPress.
	*
	* @since    1.0.0
	* @access   private
	*/
	private function set_locale() {

		$plugin_i18n = new Ovas_Connect_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	* Register all of the hooks related to the admin area functionality
	* of the plugin.
	*
	* @since    1.0.0
	* @access   private
	*/
	private function define_admin_hooks() {
		$plugin_admin = new Ovas_Connect_Admin( $this->get_plugin_name(), $this->get_version(), $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ovas_connect_add_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ovas_connect_admin_init' );
		add_action( 'wp_ajax_get_static_values_from_api', array( $plugin_admin, 'ajaxGetStaticValuesFromApi' ) );
		add_action( 'wp_ajax_set_active_tab', array( $plugin_admin, 'setActiveTab' ) );
		add_action( 'wp_ajax_get_active_tab', array( $plugin_admin, 'getActiveTab' ) );
		add_action( 'wp_ajax_get_log_page', array( $plugin_admin, 'getLogPage' ) );
		add_action( 'wp_ajax_filter_logs', array( $plugin_admin, 'ajaxFilterLogs' ) );

		// CF7 tag generator button(s)
		add_action( 'admin_init', array( $plugin_admin, 'ovas_connect_extend_tag_generator' ), 80 );
	}

	/**
	* Register all of the hooks related to the public-facing functionality
	* of the plugin.
	*
	* @since    1.0.0
	* @access   private
	*/
	private function define_public_hooks() {
		$plugin_public = new Ovas_Connect_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Ajax calls, routed via WordPress
		add_action( 'wp_ajax_resubmit_api_call', array( $this, 'ajaxSubmitFormToConnect' ) );
		add_action( 'wp_ajax_delete_log_line', array( $this, 'ajaxDeleteLogLine' ) );
		add_action( 'wp_ajax_resend_mail', array( $this, 'ajaxResendMail' ) );

		// Data filters
		add_action( 'wpcf7_skip_mail', array( $this, 'ovas_connect_before_send_mail_before_pronamic' ), 9, 1 );
		add_filter( 'wpcf7_posted_data', array( $this, 'ovas_connect_wpcf7_posted_data' ), 99, 1 );
		add_action( 'pronamic_payment_status_update', array( $this, 'ovas_connect_log_payment' ), 11, 4 );
		add_action( 'wpcf7_before_send_mail', array( $this, 'ovas_connect_wpcf7_before_send_mail' ), 11, 3 );

		// Pronamic Pay Digiwallet report hook
		add_action( 'pronamic_pay_digiwallet_report_url', array( $this, 'digiwallet_url_hook' ), 10, 1 );
		add_action( 'rest_api_init', array( $this, 'addRestEndpoints' ) );
	}

	/**
	* Run the loader to execute all of the hooks with WordPress.
	*
	* @since    1.0.0
	*/
	public function run() {
		$this->loader->run();
	}

	/**
	* The name of the plugin used to uniquely identify it within the context of
	* WordPress and to define internationalization functionality.
	*
	* @since     1.0.0
	* @return    string    The name of the plugin.
	*/
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	* The reference to the class that orchestrates the hooks with the plugin.
	*
	* @since     1.0.0
	* @return    Ovas_Connect_Loader    Orchestrates the hooks of the plugin.
	*/
	public function get_loader() {
		return $this->loader;
	}

	/**
	* Retrieve the version number of the plugin.
	*
	* @since     1.0.0
	* @return    string    The version number of the plugin.
	*/
	public function get_version() {
		return $this->version;
	}

	public function getDataSources() {
		return $this->datasources;
	}

	public function getDataSourceByID( $id ) {
		foreach ( $this->datasources as $datasource ) {
			if ( $datasource->getDataSourceID() === $id ) {
				return $datasource;
			}
		}
		return null;
	}

	public function getApiSources() {
		return $this->apisources;
	}

	public function getApiKey() {
		return $this->options['ovas_api_key'];
	}

	public function addRestEndpoints() {
		register_rest_route(
			$this->plugin_name,
			'report',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'at_rest_testing_endpoint' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	private function initialise_datasources() {
		$dir   = new DirectoryIterator( __DIR__ . '/../datasources' );
		$files = array();
		foreach ( $dir as $fileInfo ) {
			if ( ! $fileInfo->isDot() ) {
				$classname = str_replace( array( 'class-', '.php', '-' ), array( '', '', '_' ), $fileInfo->getFilename() );
				array_push( $files, $classname );
			}
		}

		sort( $files );

		foreach ( $files as $file ) {
			if ( $file !== 'ovas_connect_datasource' ) {
				$class = new $file( $this );
				array_push( $this->datasources, $class );
			}
		}
	}

	private function initialise_apisources() {
		$dir   = new DirectoryIterator( __DIR__ . '/../admin/includes/apifields' );
		$files = array();
		foreach ( $dir as $fileInfo ) {
			if ( ! $fileInfo->isDot() ) {
				$classname = str_replace( array( 'class-', '.php', '-' ), array( '', '', '_' ), $fileInfo->getFilename() );
				array_push( $files, $classname );
			}
		}

		sort( $files );

		foreach ( $files as $file ) {
			if ( $file !== 'ovas_connect_api_fields' ) {
				$class = new $file( $this );
				array_push( $this->apisources, $class );
			}
		}
	}

	public function ajaxSubmitFormToConnect( $id = null ) {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again.' );
		}
		$result = $this->submitFormToConnect( sanitize_text_field( wp_unslash( $_POST['id'] ?? $id ) ) );
		echo esc_html( sanitize_text_field( wp_unslash( $_POST['id'] ?? null ) ) );
		wp_die();
	}

	public function ajaxDeleteLogLine( $id ) {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again.' );
		}
		$logLineId = sanitize_text_field( wp_unslash( $_POST['id'] ?? null ) ) ?? $id;
		$result    = $this->deleteLogLine( $logLineId );
		echo esc_html( $result );
		wp_die();
	}

	public function ajaxResendMail( $id = null ) {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again.' );
		}
		return $this->sendDelayedEmailsByLogID( ( sanitize_text_field( wp_unslash( $_POST['id'] ?? null ) ) ?? $id ), sanitize_text_field( wp_unslash( $_POST['mailType'] ?? null ) ) );
	}

	public function deleteLogLine( $id ) {
		$retVal = Ovas_Connect_Log::deleteLog( $id );
		return $retVal;
	}

	public function mapFormFieldsToConnectFields( $fieldMaps, $values, $responseIds = null, $apiSource = null, $isMultilink = false ) {
		$mappedFields = array();
		$values       = (array) $values;

		foreach ( $fieldMaps as $fieldMap ) {
			$mappedFields[ $fieldMap->id ] = null;

			foreach ( $fieldMap->mappedFieldItems as $mappedFieldItem ) {
				$parentName = null;
				$dsKeyArr   = explode( '/', $mappedFieldItem->key );
				$dskey      = end( $dsKeyArr );
				// In case of multilink, the parent doesn't matter as it is already being linked to the correct items anyways
				if ( ! $isMultilink ) {
					$parentName = ( $dsKeyArr[0] !== end( $dsKeyArr ) ) ? $dsKeyArr[0] : null;
				}

				// Get custom fixed field dropdown options for this install
				$customOptions = apply_filters( 'custom_fixed_field_options', null, $values );

				if ( $customOptions ) {
					foreach ( $customOptions as $option ) {
						if ( $dskey === $option['ID'] ) {
							$mappedFields[ $fieldMap->id ] .= $option['VALUE'];
						}
					}
				}

				// Static values
				if ( ! is_array( $mappedFieldItem->value ) ) {
					if ( $dskey === 'APIFIELD_STATIC' ) {
						$mappedFields[ $fieldMap->id ] .= $mappedFieldItem->value;           // Static value
					} elseif ( $dskey === 'APIFIELD_FROMAPI' ) {
						$mappedFields[ $fieldMap->id ] .= $mappedFieldItem->value;           // Value from API
					} elseif ( $dskey === 'APIFIELD_STATIC_GUID' ) {      // Generate a 'unique' value
						$mappedFields[ $fieldMap->id ] .= substr( md5( wp_rand() ), 0, 32 );
					} elseif ( $dskey === 'APIFIELD_STATIC_DATE' ) {      // Generate timestampvalue of current date & time
						$mappedFields[ $fieldMap->id ] .= gmdate( 'YmdHis' );
					} elseif ( $dskey === 'APIFIELD_STATIC_DATE_FORMATTED' ) {      // Generate timestampvalue of current date & time
						$mappedFields[ $fieldMap->id ] .= gmdate( 'Y-m-d' );
					} elseif ( $dskey === 'ovas_email_object_subject' ) {         // CF7 mail object subject
						$mappedFields[ $fieldMap->id ] .= $values['ovas_email_object']->subject;
					} elseif ( $dskey === 'ovas_email_object_body' ) {            // CF7 mail object body
						$mappedFields[ $fieldMap->id ] .= nl2br( $values['ovas_email_object']->body );
					} elseif ( $dskey === 'ovas_email_object_recipient' ) {       // CF7 mail object recipient
						$mappedFields[ $fieldMap->id ] .= $values['ovas_email_object']->recipient;
					} elseif ( $dskey === 'ovas_email2_object_subject' ) {         // CF7 mail object subject
						$mappedFields[ $fieldMap->id ] .= $values['ovas_email2_object']->subject;
					} elseif ( $dskey === 'ovas_email2_object_body' ) {            // CF7 mail object body
						$mappedFields[ $fieldMap->id ] .= nl2br( $values['ovas_email2_object']->body );
					} elseif ( $dskey === 'ovas_email2_object_recipient' ) {       // CF7 mail object recipient
						$mappedFields[ $fieldMap->id ] .= $values['ovas_email2_object']->recipient;
						// Subfield, in case of a 'multilink' product
					} else {
						// Dropdowns etc are represented as array, in that case we want the value of the array item
						if ( array_key_exists( $dskey, $values ) && is_array( $values[ $dskey ] ) ) {
							$mappedFields[ $fieldMap->id ] .= $this->getFieldValue( $values[ $dskey ], $dskey, $parentName, null ); // Mapped field value
						} else {
							$mappedFields[ $fieldMap->id ] .= $this->getFieldValue( $values, $dskey, $parentName, null ); // Mapped field value
						}
					}
				}
				// Check for manually mapped fields
				$mappedFields[ $fieldMap->id ] = $apiSource->mapToApiValue( $fieldMap->id, $mappedFields[ $fieldMap->id ] );

				// Fix formatting for certain form fieldlinks
				if ( array_key_exists( $fieldMap->id, $apiSource->getFields() ) && $apiSource->getFields()[ $fieldMap->id ] !== null ) {
					if ( array_key_exists( 'validationInsert', $apiSource->getFields()[ $fieldMap->id ] ) ) {
						switch ( $apiSource->getFields()[ $fieldMap->id ]['validationInsert'] ) {
							case 'amountFLD':
								$mappedFields[ $fieldMap->id ] = str_replace( ',', '.', preg_replace( '/[^0-9.,]/', '', $mappedFields[ $fieldMap->id ] ) );
								break;
						}
					}
				}

				// Allow for custom override for each mapped field value by themes/plugins
				$mappedFields[ $fieldMap->id ] = apply_filters( 'ovas_get_mapped_field', $mappedFields[ $fieldMap->id ], $apiSource->name, $fieldMap->id, $values, $responseIds, $this );
			}

			if ( ! empty( $fieldMap->subFieldMap ) ) {
				if ( empty( $mappedFields[ $fieldMap->id ] ) ) {
					$mappedFields[ $fieldMap->id ] = array();
				}
				// Multilink, add an item to the mappedfields per mapped item in the datasource
				if ( isset( $fieldMap->multiLinkField ) ) {
					foreach ( $values[ $fieldMap->multiLinkField ] as $item ) {
						array_push( $mappedFields[ $fieldMap->id ], $this->mapFormFieldsToConnectFields( $fieldMap->subFieldMap, $item, $responseIds, $apiSource, true ) );
					}
				} else {
					array_push( $mappedFields[ $fieldMap->id ], $this->mapFormFieldsToConnectFields( $fieldMap->subFieldMap, $values, $responseIds, $apiSource, false ) );
				}
			}
		}

		return $mappedFields;
	}

	// Recursively find the value of the field in the values array
	public function getFieldValue( $values, $fieldName, $parentName, $currentParent = null ) {
		$retVal = null;
		foreach ( $values as $valKey => $valValue ) {
			if ( is_object( $valValue ) || is_array( $valValue ) ) {
				$retVal = $this->getFieldValue( $valValue, $fieldName, $parentName, $valKey );
			} elseif ( $valKey === $fieldName && $currentParent === $parentName ) {
				$retVal = $valValue;
			}
			if ( $retVal !== null ) {
				return $retVal; }
		}
		return null;
	}

	public function submitFormToConnect( $id = null ) {
		global $wpdb;
		$response    = array();
		$responseIds = array();

		$this->options = get_option( 'ovas_options' );
		$ConnectAPI    = new Ovas_ConnectAPI( $this->options['ovas_api_key'], 'nl', 'json', $this );

		$loglineid = $id;

		$logItems       = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i where id = %s', $this->log_table_name, $loglineid ), OBJECT );
		$logLine        = json_decode( $logItems->logline );
		$logLineRequest = json_decode( $logLine->request );

		// return $logItems;
		// Get datasource object for this action
		$datasource = array_column( $this->getDataSources(), null, 'dataSourceID' )[ $logItems->datasource ] ?? null;
		$fieldlinks = json_decode( $this->options[ 'ovas_fieldlinks_' . $datasource->dataSourceID ], true );

		$ds = new Ovas_Connect_Settings_DatasourceSettings( $this );
		$ds->loadSettings( $this->options[ 'ovas_fieldlinks_' . $datasource->dataSourceID ] );
		$savedSettings = $ds->getSettings();

		// Sort all api sources based on their order field to make sure they are
		// being submitted in the correct order
		$apiSources = $this->getApiSources();
		usort( $apiSources, fn( $a, $b ) => $a->order - $b->order );

		// For each enabled API call (in the correct order)
		foreach ( $apiSources as $apiSource ) {
			// Is api source enabled for this datasource section?
			$fieldMaps = $ds->getFieldMapsById( $datasource->dataSourceID, $logLineRequest->section_id, $apiSource->getName() );
			if ( ! empty( $fieldMaps ) ) {
				// Map fields from datasource to api fields
				$mappedFields = $this->mapFormFieldsToConnectFields( $fieldMaps, $logLineRequest, $responseIds, $apiSource, false );

				// Check if the return field of this API source is already in the responseIds array
				// If so, we already have an return item of this type, and thus we should skip it
				// This allows for example to search for a customer and create a new one if it does not exist
				$mergedresponseIds = array_merge( ...$responseIds );
				if ( array_key_exists( $apiSource->getReturnField(), $mergedresponseIds ) ) {
					continue;
				}

				// If the api call is a GET call, add request to response array for URI creation
				$t = array();
				if ( $apiSource->getDefaultRequestType() === 'GET' && $apiSource->getName() !== 'find_relation_by_field' ) {
					array_push( $t, $mappedFields );
				}

				// Create API URI
				// 'Find relation by field' requires the URL to be built differently
				if ( $apiSource->getName() === 'find_relation_by_field' ) {
					// find_relation_by_field; generate the URL from the user-defined fields
					$q = '';
					foreach ( $mappedFields as $fieldKey => $fieldVal ) {
						$q .= $fieldKey . '=' . $fieldVal . '&';
					}
					$q = rtrim( $q, '&' );

					$searchFields = array( 'searchvalues' => $q );
					array_push( $t, $searchFields );
				}

				// 'Adoptions' requires 'projectId' in the URL, but does NOT allow it
				// as a submitted field.
				if ( $apiSource->getName() === 'Adoptions' ) {
					$mappedFields['projectId'] = null;
				}

				$apiUri = $this->makeRequestUri( $apiSource->getUri( $apiSource->getDefaultRequestType() ), array_merge( $responseIds, $t ) );

				// Add fields from the previous API response(s) to the mapped fields,
				// but only if that fieldname exists in the API fields
				foreach ( $responseIds as $responseIdKey => $responseIdValue ) {
					if ( array_key_exists( key( $responseIdValue ), $apiSource->getFields() ) ) {
						$mappedFields = array_merge( array( key( $responseIdValue ) => current( $responseIdValue ) ), $mappedFields );
					}
				}

				$mappedFields = apply_filters( 'ovas_before_api_call_mappedfields', $mappedFields, $responseIds, $apiUri, $apiSource, $this );
				$skip         = apply_filters( 'ovas_before_api_call_skip', $mappedFields, $responseIds, $apiUri, $apiSource, $this );

				if ( $skip !== true ) {
					// Call API
					if ( $apiSource->getDefaultRequestType() === 'GET' ) {
						$ConnectAPI->request( $apiUri, $apiSource->getDefaultRequestType() );
					} else {
						$ConnectAPI->request( $apiUri, $apiSource->getDefaultRequestType(), $mappedFields );
					}

					$apiResponse = json_decode( $ConnectAPI->getResponse()['body'] );
					$apiResponse = apply_filters( 'ovas_api_call_result', $apiResponse, $mappedFields, $responseIds, $apiUri, $apiSource, $this );

					// Add response to response array
					array_push( $response, array( $apiSource->getName() => json_encode( $apiResponse ) ) );
					if ( isset( $apiResponse->data ) && ! empty( $apiResponse->data ) ) {
						if ( is_array( $apiResponse->data ) ) {
							array_push( $responseIds, array( $apiSource->getReturnField() => $apiResponse->data[0]->{$apiSource->getReturnField()} ) );
						} else {
							array_push( $responseIds, array( $apiSource->getReturnField() => $apiResponse->data->{$apiSource->getReturnField()} ) );
						}
					}
				} else {
					$result = wp_json_encode(
						array(
							'request' => array(
								'id'     => null,
								'status' => array(
									'success'      => true,
									'errorCode'    => 'skipped',
									'errorMessage' => "API call skipped due to the 'ovas_before_api_call_skip' hook returning true",
								),
							),
						)
					);
					array_push( $response, array( $apiSource->getName() => $result ) );
				}
			}
		}

		// Add response array to database
		$log               = Ovas_Connect_Log::getLog( $loglineid );
		$logline           = json_decode( $log->logline );
		$logline->response = wp_json_encode( $response );
		Ovas_Connect_Log::addResponse( $loglineid, $logline );

		return $log->logline;
	}

	private function makeRequestUri( $uri, $responseIds ) {
		preg_match_all( '/{+(.*?)}/', $uri, $matches );
		foreach ( $matches[1] as $match ) {
			$count = count( $responseIds );
			for ( $i = 0; $i < $count; $i++ ) {
				if ( isset( $responseIds[ $i ][ $match ] ) ) {
					$uri = str_replace( '{' . $match . '}', $responseIds[ $i ][ $match ], $uri );
				}
			}
		}
		return $uri;
	}

	public function log( $label, $tolog = null ) {
		$file   = wp_upload_dir()['basedir'] . '/ovas_connect_debug.log';
		$prefix = gmdate( 'Y-m-d H:i:s' );
		$label  = $label === null ? null : $label . ': ';
		file_put_contents( $file, '[' . $prefix . '] - ' . $label . var_export( $tolog, true ) . "\r\n", FILE_APPEND | LOCK_EX );
	}

	// Alter data based on the posted data, like filling the email objects if applicable
	// [wpcf7_posted_data] Add a unique ID to the hidden internal description (that is sent to pronamic)
	// Also try to fix invalid inputs for payment amount and convert from comma to dot notation
	public function ovas_connect_wpcf7_posted_data( $array ) {
		if ( $this->is_contact_form_pronamic_ideal() ) {
			$descriptionField = $this->getCF7FieldFieldNameByTagName( 'pronamic_pay_description' );
			$description      = $array[ $descriptionField ] ?? '';
			if ( is_array( $description ) ) {
				$description = $description[0];
			}

			$uniqid = uniqid();

			$descriptionField = $this->getCF7FieldFieldNameByTagName( 'pronamic_pay_description' );

			$array[ $descriptionField ] = $uniqid . ' - ' . $description;
			$_POST[ $descriptionField ] = $array[ $descriptionField ];

			$array['ovas_transaction_id'] = $uniqid;
		}

		// Fill email objects
		$contactform = WPCF7_ContactForm::get_current();
		if ( array_key_exists( 'ovas_email_object', $array ) ) {
			$mail_object = $contactform->prop( 'mail' );
			if ( $mail_object['active'] === true ) {
				$mail_object                = wpcf7_mail_replace_tags( $mail_object );
				$array['ovas_email_object'] = $mail_object;
			}
		}

		if ( array_key_exists( 'ovas_email2_object', $array ) ) {
			$mail2_object = $contactform->prop( 'mail_2' );
			if ( $mail2_object['active'] === true ) {
				$mail2_object                = wpcf7_mail_replace_tags( $mail2_object );
				$array['ovas_email2_object'] = $mail2_object;
			}
		}

		$amountFieldName = $this->getCF7FieldFieldNameByTagName( 'pronamic_pay_amount' );
		if ( $amountFieldName !== null ) {
			if ( is_array( $array[ $amountFieldName ] ?? null ) ) {
				$bedrag = $array[ $amountFieldName ][0];
				if ( $bedrag !== null ) {
					$array[ $amountFieldName ][0] = str_replace( ',', '.', preg_replace( '/[^0-9.,]/', '', $bedrag ) );
				}
			} else {
				$bedrag = $array[ $amountFieldName ] ?? null;
				if ( $bedrag !== null ) {
					$array[ $amountFieldName ] = str_replace( ',', '.', preg_replace( '/[^0-9.,]/', '', $bedrag ) );
				}
			}
		}

		return $array;
	}

	public function ovas_connect_wpcf7_before_send_mail( $contact_form, $abort, $submission ) {
		$mail_object = $contact_form->prop( 'mail' );
		if ( $mail_object['active'] === 1 ) {
			$mail_object      = wpcf7_mail_replace_tags( $mail_object );
			$descriptionField = $this->getCF7FieldFieldNameByTagName( 'pronamic_pay_description' );
			$paymentid        = trim( explode( '-', $submission->get_posted_data()[ $descriptionField ] ?? '' )[0] );
			$this->updateEmailObjectInFormSubmission( $mail_object, $paymentid, 'ovas_email_object' );
		}

		$mail2_object = $contact_form->prop( 'mail_2' );
		if ( $mail2_object['active'] === 1 ) {
			$mail2_object     = wpcf7_mail_replace_tags( $mail2_object );
			$descriptionField = $this->getCF7FieldFieldNameByTagName( 'pronamic_pay_description' );
			$paymentid        = trim( explode( '-', $submission->get_posted_data()[ $descriptionField ] ?? '' )[0] );
			$this->updateEmailObjectInFormSubmission( $mail2_object, $paymentid, 'ovas_email2_object' );
		}
	}

	// [wpcf7_skip_mail] Prepare the form for submission to pronamic and the API
	public function ovas_connect_before_send_mail_before_pronamic( $form ) {
		// Check if the submitted form is a pronamic ideal form
		// This will also skip the wpcf7 mail sending hooks and thus not submit the API calls
		return $this->is_contact_form_pronamic_ideal();
	}

	// [pronamic_payment_status_update] Actually send the CF7 form mail and submit the API call to Ovas Connect
	// Also, add the IBAN to the form response
	public function ovas_connect_log_payment( $payment, $can_redirect, $old_status, $new_status ) {
		if ( $new_status === 'Success' ) {
			$paymentid = trim( explode( '-', $payment->get_description() )[0] );

			$iban = null;

			$extraPaymentDetails = apply_filters( 'ovas_get_extra_payment_details', null, $payment->transaction_id );
			if ( $extraPaymentDetails ) {
				// Digiwallet
				if ( property_exists( $extraPaymentDetails, 'consumerIBAN' ) ) {
					if ( ! empty( $extraPaymentDetails->consumerIBAN ) ) {
						$iban = $extraPaymentDetails->consumerIBAN;
					}
				}
			}

			// Add IBAN to the form submission, if we haven't retrieved the iban from the extra payment details yet
			if ( $payment->get_consumer_bank_details() !== null ) {
				$iban = $payment->get_consumer_bank_details()->get_iban();
			}

			if ( $iban !== null ) {
				$submission = $this->addFieldToFormSubmission( $iban, $paymentid, 'ovas_iban' );
			}

			// Send the mail to the user that was initially withheld
			$this->sendDelayedEmailsByPaymentid( $paymentid, 'ovas_email_object' );
			$this->sendDelayedEmailsByPaymentid( $paymentid, 'ovas_email2_object' );

			// Finally, submit the API call after all data has been collected and updated
			$logline = $this->getOvasFormSubmissionByTransactionId( $paymentid );
			$this->submitFormToConnect( $logline->id );
		}
	}

	public function is_contact_form_pronamic_ideal() {
		$tags = WPCF7_FormTagsManager::get_instance()->get_scanned_tags();
		foreach ( $tags as $tag ) {
			if ( in_array( 'pronamic_pay_amount', $tag->options, true ) ) {
				return true;
			}
		}
		return false;
	}

	public function getOvasFormSubmissionByTransactionId( $paymentid ) {
		global $wpdb;
		$logitems = '';
		$result   = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE paymentid = %s ORDER BY timestamp DESC', $this->log_table_name, $paymentid ), OBJECT_K );
		return reset( $result );
	}

	public function getPaymentIdByFormId( $formId ) {
		global $wpdb;
		$logitems    = '';
		$result      = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE id = %s ORDER BY timestamp DESC', $this->log_table_name, $formId ), OBJECT_K );
		$loglineitem = reset( $result );
		if ( $loglineitem !== null ) {
			$logline        = json_decode( $loglineitem->logline );
			$formsubmission = json_decode( $logline->request );
			return $formsubmission->ovas_transaction_id;
		}
		return null;
	}

	public function addFieldToFormSubmission( $iban, $paymentid, $fieldname ) {
		global $wpdb;
		$submission = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE paymentid = %s ORDER BY timestamp DESC', $this->log_table_name, $paymentid ), OBJECT_K );

		// We expect 1 result row, so grab the first row. The result is an assiosative array
		// with an int as key , so we can't use [1] to grab the first result thus we have to use reset() here
		// to grab the first result item
		$item = reset( $submission );

		if ( $item->logline !== null ) {
			$logline                    = json_decode( $item->logline );
			$formsubmission             = json_decode( $logline->request );
			$formsubmission->$fieldname = $iban;
			$newrequest                 = wp_json_encode( $formsubmission );
			$logline->request           = $newrequest;
			$newlogline                 = wp_json_encode( $logline );
			$wpdb->update( $this->log_table_name, array( 'logline' => $newlogline ), array( 'paymentid' => $paymentid ) );
		}
		$newsubmission = '';
	}

	public function updateEmailObjectInFormSubmission( $mail_object, $paymentid, $fieldname ) {
		global $wpdb;
		$submission = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE paymentid = %s ORDER BY timestamp DESC', $this->log_table_name, $paymentid ), OBJECT_K );

		// We expect 1 result row, so grab the first row. The result is an associative array
		// with an int as key , so we can't use [1] to grab the first result thus we have to use reset() here
		// to grab the first result item
		$item = reset( $submission );

		if ( $item && $item->logline !== null ) {
			$logline                    = json_decode( $item->logline );
			$formsubmission             = json_decode( $logline->request );
			$formsubmission->$fieldname = $mail_object;
			$newrequest                 = wp_json_encode( $formsubmission );
			$logline->request           = $newrequest;
			$newlogline                 = wp_json_encode( $logline );
			$wpdb->update( $this->log_table_name, array( 'logline' => $newlogline ), array( 'paymentid' => $paymentid ) );
		}
		$newsubmission = '';
	}

	public function sendDelayedEmailsByPaymentid( $paymentid, $formfield ) {
		global $wpdb;
		$submission = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM%i WHERE paymentid = %s ORDER BY timestamp DESC', $this->log_table_name, $paymentid ), OBJECT_K );
		$item       = reset( $submission );

		return $this->sendDelayedEmailsByLogID( $item->id, $formfield );
	}

	public function sendDelayedEmailsByLogID( $logid, $formfield ) {
		global $wpdb;
		$submission = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i WHERE id = %s ORDER BY timestamp DESC', $this->log_table_name, $logid ), OBJECT_K );
		$item       = reset( $submission );
		$result     = null;

		if ( $item->logline !== null ) {
			$logline        = json_decode( $item->logline );
			$formsubmission = json_decode( $logline->request );
			if ( property_exists( $formsubmission, $formfield ) ) {
				$email_data = $formsubmission->$formfield;

				$extraheaders  = 'MIME-Version: 1.0' . "\r\n";
				$extraheaders .= 'Content-Type: text/html; charset=ISO-8859-1';

				if ( $email_data ) {
					// Old format for storing email data
					// If it is a JSON string, convert it to an object
					if ( ! is_object( $email_data ) ) {
						$jsonEmailData = json_decode( $email_data );
						if ( $jsonEmailData !== null ) {
							$email_data = $jsonEmailData;
						}
					}

					$result = wp_mail(
						$email_data->recipient,
						$email_data->subject,
						nl2br( htmlentities( $email_data->body ) ),
						$email_data->additional_headers . "\r\n" . $extraheaders,
						$email_data->attachments
					);
				}
			}
			return $result;
		}
	}

	public function getCF7FieldFieldNameByTagName( $tagname ) {
		$contactform = WPCF7_ContactForm::get_current();
		$form_fields = $contactform->scan_form_tags();

		foreach ( $form_fields as $field ) {
			if ( in_array( $tagname, $field->options, true ) ) {
				return $field->name;
			}
		}
		return '';
	}

	public function digiwallet_url_hook( $report_url ) {
		return get_site_url() . '/wp-json/' . $this->plugin_name . '/report';
	}

	public function at_rest_testing_endpoint( $data ) {
		$trxid     = $data->get_params()['trxid'];
		$iban      = $data->get_params()['cbank'];
		$paymentid = null;

		$payment = get_pronamic_payment_by_transaction_id( $trxid );

		if ( $payment ) {
			$paymentid = trim( explode( '-', $payment->get_description() )[0] );
		}

		// Add IBAN to the form submission, if we haven't retrieved the iban from the extra payment details yet
		if ( $payment->get_consumer_bank_details() !== null ) {
			$iban = $payment->get_consumer_bank_details()->get_iban();
		}

		if ( $iban !== null ) {
			$submission = $this->addFieldToFormSubmission( $iban, $paymentid, 'ovas_iban' );
		}

		// Replicate original pronamic report URL
		$orig_url = get_rest_url( null, Integration::REST_ROUTE_NAMESPACE . '/report' );

		$body   = array(
			'method'   => 'POST',
			'timeout'  => 45,
			'blocking' => true,
			'body'     => $data->get_params(),
		);
		$result = wp_remote_post( $orig_url, $body );

		echo wp_kses_post( $result['body'] );
	}

	public function ovas_connect_plugin_action_links( $actions ) {
		$newactions[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=ovas_settings_admin' ) ) . '">' . __( 'Settings', 'ovas-connect' ) . '</a>';
		$actions      = array_merge( $newactions, $actions );
		return $actions;
	}
}
