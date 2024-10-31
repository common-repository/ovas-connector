<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require plugin_dir_path( __FILE__ ) . '/../includes/class-ovas-connectapi.php';
require plugin_dir_path( __FILE__ ) . '/includes/class-ovas-connect-settings.php';
require plugin_dir_path( __FILE__ ) . '/includes/class-ovas-connect-datafieldtype.php';
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ovas.nl
 * @since      1.0.0
 *
 * @package    Ovas_Connect
 * @subpackage Ovas_Connect/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ovas_Connect
 * @subpackage Ovas_Connect/admin
 * @author     Ovas Solutions <info@ovas.nl>
 */

class Ovas_Connect_Admin {

	private $plugin_name;
	private $version;
	public $ConnectAPI;
	private $plugin;
	private $datasources;
	private $apiSources;

	public function __construct( $plugin_name, $version, $plugin ) {
		$this->plugin_name        = $plugin_name;
		$this->version            = $version;
		$this->plugin             = $plugin;
		$this->apiSources         = $this->plugin->getApiSources();
		$this->dataSourceSettings = array();
		usort( $this->apiSources, fn( $a, $b ) => $a->order - $b->order );
		$this->migrateSettings();
	}

	public function settings_page() {
		// DEBUG/DEV
		$options       = get_option( 'ovas_options' );
		$savedSettings = null;

		if ( $options ) {
			$ds = new Ovas_Connect_Settings_DatasourceSettings( $this->plugin );
			foreach ( $options as $key => $value ) {
				$dataSourceName = explode( '_', $key )[2];
				if ( $this->startsWith( $key, 'ovas_fieldlinks' ) ) {
					$ds->loadSettings( $options[ 'ovas_fieldlinks_' . $dataSourceName ] );
					$savedSettings            = $ds->getSettings();
					$this->dataSourceSettings = $savedSettings;
				}
			}
		}

		// AlLow 'display: none'inline styles
		add_filter( 'safe_style_css', array( $this, 'allowed_css_styles' ) );
		add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_html_tags' ), 10, 2 );
		include 'partials/ovas-connect-admin-display.php';
	}

	// Migrate plugin settings from old mixed prefixes to new prefix ('ovas_*')
	public function migrateSettings() {
		$settings = array(
			'api_key'       => 'ovas_api_key',
			'api_env'       => 'ovas_api_env',
			'api_key_valid' => 'ovas_api_key_valid',
			'dw_outlet_id'  => 'ovas_dw_outlet_id',
			'dw_api_key'    => 'ovas_dw_api_key',
		);
		$options  = get_option( 'ovas_options' );
		if ($options) {
			foreach ( $options as $key => $value ) {
				// Static options
				if ( array_key_exists( $key, $settings ) ) {
					$options[ $settings[ $key ] ] = $value;
					unset( $options[ $key ] );
				}

				// Datasource options
				if ( $this->startsWith( $key, 'datasource_' ) ) {
					$datasourceName                                  = explode( '_', $key )[1];
					$options[ 'ovas_datasource_' . $datasourceName ] = $value;
					unset( $options[ $key ] );
				}
				if ( $this->startsWith( $key, 'fieldlinks_' ) ) {
					$datasourceName                                  = explode( '_', $key )[1];
					$options[ 'ovas_fieldlinks_' . $datasourceName ] = $value;
					unset( $options[ $key ] );
				}
			}

			update_option( 'ovas_options', $options );
		}
	}

	public function enqueue_styles() {
		if ( get_current_screen()->base === 'toplevel_page_ovas_settings_admin' ) {
			wp_enqueue_style( $this->plugin_name . 'tailselect', plugin_dir_url( __FILE__ ) . 'css/tail.select-light.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . 'css', plugin_dir_url( __FILE__ ) . 'css/ovas-connect-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'dashicons' );
		}
	}

	public function enqueue_scripts() {
		if ( get_current_screen()->base === 'toplevel_page_ovas_settings_admin' ) {
			wp_enqueue_script( $this->plugin_name . 'tailselect', plugin_dir_url( __FILE__ ) . 'js/tail.select.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name . 'js', plugin_dir_url( __FILE__ ) . 'js/ovas-connect-admin.js', array( 'jquery' ), $this->version, true );
			wp_localize_script( $this->plugin_name . 'js', 'ajax_var', array( 'nonce' => wp_create_nonce( 'ajax-nonce' ) ) );
		}
	}

	public function ovas_connect_add_settings_page() {
		add_menu_page(
			__( 'Ovas Connect', 'ovas-connect' ),
			__( 'Ovas Connect', 'ovas-connect' ),
			'manage_options',
			'ovas_settings_admin',
			array(
				$this,
				'settings_page',
			),
			'data:image/svg+xml;base64,' . base64_encode( file_get_contents( plugins_url( 'ovas-connect/admin/images/o64.svg' ) ) )
		);
	}

	public function ovas_connect_admin_init() {
		// Define settings group in database
		register_setting(
			'ovas_option_group', // Option group
			'ovas_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		// API settings
		add_settings_section(
			'ovas_api_settings', // ID
			__( 'API Settings', 'ovas-connect' ), // Title
			array( $this, 'print_api_section_header' ), // Callback
			'ovas_settings_admin' // Page
		);

		add_settings_field(
			'ovas_api_key', // ID
			__( 'API Key', 'ovas-connect' ), // Title
			array( $this, 'cb_display_input_field' ), // Callback
			'ovas_settings_admin', // Page
			'ovas_api_settings',
			array(
				'type'      => 'text',
				'fieldname' => 'ovas_api_key',
			) // callback argument(s)
		);

		add_settings_field(
			'ovas_api_env', // ID
			__( 'API Environment', 'ovas-connect' ), // Title
			array( $this, 'cb_display_input_field' ), // Callback
			'ovas_settings_admin', // Page
			'ovas_api_settings',
			array(
				'type'      => 'text',
				'fieldname' => 'ovas_api_env',
				'datalist'  => 'dev,live,mk',
			) // callback argument(s)
		);

		add_settings_field(
			'ovas_api_key_valid', // ID
			__( 'Administration information', 'ovas-connect' ), // Title
			array( $this, 'cb_display_api_information' ), // Callback
			'ovas_settings_admin', // Page
			'ovas_api_settings',
			array(
				'type'          => 'hidden',
				'fieldname'     => 'ovas_api_key_valid',
				'api_key_field' => 'ovas_api_key',
			) // callback argument(s)
		);

		add_settings_field(
			'ovas_logtab_filter_limit', // ID
			__( 'Default log duration display', 'ovas-connect' ), // Title
			array( $this, 'cb_display_input_field' ), // Callback
			'ovas_settings_admin', // Page
			'ovas_api_settings',
			array(
				'type'      => 'select',
				'fieldname' => 'ovas_logtab_filter_limit',
			) // callback argument(s)
		);

		// Digiwallet settings
		add_settings_section(
			'ovas_digiwallet_settings', // ID
			__( 'Digiwallet Settings', 'ovas-connect' ), // Title
			array( $this, 'print_api_section_header' ), // Callback
			'ovas_settings_admin' // Page
		);

		add_settings_field(
			'ovas_dw_outlet_id', // ID
			__( 'Outlet ID', 'ovas-connect' ), // Title
			array( $this, 'cb_display_input_field' ), // Callback
			'ovas_settings_admin', // Page
			'ovas_digiwallet_settings',
			array(
				'type'      => 'text',
				'fieldname' => 'ovas_dw_outlet_id',
			) // callback argument(s)
		);

		add_settings_field(
			'ovas_dw_api_key', // ID
			__( 'API Key', 'ovas-connect' ), // Title
			array( $this, 'cb_display_input_field' ), // Callback
			'ovas_settings_admin', // Page
			'ovas_digiwallet_settings',
			array(
				'type'      => 'text',
				'fieldname' => 'ovas_dw_api_key',
			) // callback argument(s)
		);

		// Datasource group
		add_settings_section(
			'ovas_datasource_settings', // ID
			__( 'Datasource settings', 'ovas-connect' ), // Title
			array( $this, 'print_datasource_header' ), // Callback
			'ovas_settings_admin' // Page
		);

		// Datasources
		foreach ( $this->plugin->getDataSources() as $datasource ) {
			// Add checkbox to enable/disable a datasource
			add_settings_field(
				'ovas_datasource_' . $datasource->getDataSourceID(), // ID
				$datasource->getDataSourceName(), // Title
				array( $this, 'cb_display_input_field' ), // Callback
				'ovas_settings_admin', // Page
				'ovas_datasource_settings',
				array(
					'type'      => 'checkbox',
					'fieldname' => 'ovas_datasource_' . $datasource->getDataSourceID(),
				) // Section
			);

			// Hidden field for saving datasource field links
			add_settings_field(
				'ovas_fieldlinks_' . $datasource->getDataSourceID(), // ID
				$datasource->getDataSourceName(), // Title
				array( $this, 'cb_display_input_field' ), // Callback
				'ovas_settings_admin', // Page
				'ovas_datasource_settings',
				array(
					'type'      => 'hidden',
					'fieldname' => 'ovas_fieldlinks_' . $datasource->getDataSourceID(),
				) // Section
			);
		}
	}

	public function sanitize( $input ) {
		return $input;
	}

	public function cb_display_input_field( array $params ) {
		if ( $params['type'] === 'select' ) {
			ob_start();
			include 'partials/ovas-connect-select-' . str_replace( '_', '-', $params['fieldname'] ) . '.php';
			echo wp_kses( ob_get_clean(), 'template' );
		} else {
			$checked  = '';
			$datalist = null;
			if ( isset( $this->options[ $params['fieldname'] ] ) ) {
				$checked = checked( 1, $this->options[ $params['fieldname'] ], false );
			}
			if ( array_key_exists( 'datalist', $params ) !== false ) {
				$datalist = '<datalist id="list_' . $params['fieldname'] . '">';
				foreach ( explode( ',', $params['datalist'] ) as $env ) {
					$datalist .= '<option value="' . esc_attr( $env ) . '"></option>';
				}
				$datalist .= '</datalist>';
			}

			echo wp_kses(
				sprintf(
					'<input type="%3$s" id="%2$s" name="ovas_options[%2$s]" value="%1$s" ' . $checked . ' %4$s />%5$s',
					isset( $this->options[ $params['fieldname'] ] ) ? esc_attr( $this->options[ $params['fieldname'] ] ) : ( ( $params['type'] === 'checkbox' ) ? '1' : '' ),
					$params['fieldname'],
					$params['type'],
					( array_key_exists( 'datalist', $params ) ? 'list="list_' . $params['fieldname'] . '"' : '' ),
					$datalist
				),
				'template'
			);
		}
	}

	public function cb_display_api_information( array $params ) {
		if ( $this->options ) {
			$this->ConnectAPI = new Ovas_ConnectAPI( $this->options[ $params['api_key_field'] ], 'nl', 'json', $this->plugin );
			$this->ConnectAPI->request( '/administration', 'GET' );
			$admin = $this->ConnectAPI->getResponse();

			$body = json_decode( $admin['body'] );

			if ( $body->request->status->success === true ) {
				$modules = '';
				foreach ( $body->data->modules as $module ) {
					$modules .= sprintf( '<span class="module">%1$s</span>', $module->name );
				}
				echo wp_kses_post(
					sprintf(
						'<div class="api_information">
                    <span class="dashicons dashicons-yes"></span>
                    <span class="admin_name">%1$s</span>
                    <span class="admin_modules">%2$s</span>
                    </div>',
						$body->data->name,
						$modules
					)
				);

			} else {
				echo wp_kses_post(
					sprintf(
						'<div class="api_information">
                    <span class="dashicons dashicons-no"></span>
                    %1$s
                    </div>',
						$body->request->status->errorMessage
					)
				);
			}
		}
	}

	public function print_api_section_header() {
		// Optional sub-header
	}

	public function print_datasource_header() {
		// Optional sub-header
	}

	public function addTabs() {
		$tabNumber      = 2;
		$tabHeader      = 'Tab header';
		$tabContent     = 'Tab content';
		$options        = get_option( 'ovas_options' );
		$selectedFields = '';

		// Check for enabled datasources
		if ( $options ) {
			foreach ( $options as $key => $value ) {
				if ( $this->startsWith( $key, 'ovas_datasource' ) ) {
					// Get datasource objects
					foreach ( $this->plugin->getDataSources() as $datasource ) {
						// Find correct datasource object for selected option
						if ( $datasource->getDataSourceID() === explode( '_', $key )[2] ) {
							$tabHeader        = $datasource->getDataSourceName();
							$tabContent       = null;
							$datasourceFields = null;
							$datasourceID     = $datasource->getDataSourceID();
							$sections         = $this->getSections( $datasource->getFields() );

							$firstSubTab = true;
							// Loop through sections (CF7 forms, woocommerce etc) and fill the fieldlink template
							foreach ( $sections as $section ) {
								$checkboxChecked  = array();
								$sectionDisplay   = array();
								$apilinkscontent  = null;
								$apisourcecontent = null;
								$apisources       = $this->apiSources;
								$linksDisplay     = 'none';
								$apiLinkedToField = null;
								$sectionId        = $section->id;

								$sectionEnabled = $this->dataSourceSettings->isSectionEnabled( $datasource->getDataSourceID(), $section->id );

								// Loop through all available API sources
								foreach ( $this->apiSources as $source ) {
									$apiSourceSubFieldTemplates = null;
									$sourcename                 = $source->getName();
									$sourcelabel                = $source->getLabel();
									// Available fields in the datasource for that section
									$sectionName    = $section->name;
									$required       = false;
									$hasSubFields   = false;
									$fieldValueVal  = '';
									$linksDisplay   = 'none';
									$selectedFields = null;
									$fieldMaps      = null;

									$checkboxChecked[ $section->id ] = ( $sectionEnabled === true ? 'checked' : '' );
									$sectionDisplay[ $section->id ]  = ( $sectionEnabled === true ? 'block' : 'none' );

									$fieldMaps = $this->dataSourceSettings->getFieldMapsById( $datasource->getDataSourceID(), $section->id, $source->getName() ) ?? null;

									// Field links
									if ( $fieldMaps ) {
										$linksDisplay = isset( $fieldMaps ) ? 'block' : 'none';
										// Recursively get links and add them to $selectedFields
										$selectedFields = $this->getFieldLinks( $fieldMaps, $sourcename, $source, $datasource, $selectedFields, $section );
									}

									$fieldValueStatic = '';

									// Get all API fields
									$addableFields = $this->getAddableFields( $source );

									// Get all datasource fields
									$datasourceFieldDropdownOptions = $this->getDatasourceDropdown( $datasource->getFields(), $section->id, false, '', $sourcename );

									$apiSourceSubFieldTemplates .= $this->getSubFieldTemplates( $source, $datasource, $section, $sourcename );

									// If the apifeld has subfields
									if ( empty( $source->getSubfields() ) ) {
										$subFieldMultilinkObjects = $datasource->getSubFieldMultilinkObjects();
										if ( $subFieldMultilinkObjects !== null ) {
											$subfieldobjectlinks = '<option value=""></option>';
											foreach ( $subFieldMultilinkObjects as $obj ) {
												$subfieldobjectlinks .= '<option value="' . $obj . '">' . $obj . '</option>';
											}
										}
									}

									// draw tab section contents
									ob_start();
									include 'partials/ovas-connect-tab-section-apilinks.php';
									$apilinkscontent .= ob_get_clean();
								}

								$checked     = $firstSubTab ? 'checked' : '';
								$firstSubTab = false;

								// draw tab section contents
								ob_start();
								include 'partials/ovas-connect-tab-section.php';
								$tabContent .= ob_get_clean();
							}
						}
					}
					++$tabNumber;

					// draw tab contents
					include 'partials/ovas-connect-tab.php';
				}
			}
		}
	}

	private function getAddableFields( $source ) {
		$retVal = null;

		foreach ( $source->getFields() as $key => $value ) {
			$retVal .= sprintf(
				'<a data-id="%2$s" data-required="%3$s" data-hassubfields="%4$s" data-hasapicallback="%5$s" data-linkedto=%6$s>%1$s</a>',
				( $value['required'] === 1 ? '*' : '&nbsp;' ) . $key,
				$key,
				$value['required'],
				$source->getSubfields( $key ) !== null,
				! empty( $value['linkhref'] ),
				$value['linkedTo']
			);
		}

		return $retVal;
	}

	private function getFieldLinks( $fieldMaps, $sourcename, $source, $datasource, $selectedFields, $section, $isSubField = false, $parentKey = null ) {
		$selectedFields = null;
		foreach ( $fieldMaps as $fieldMap ) {
			// Sort mapped field items based on their stored order
			usort( $fieldMap->mappedFieldItems, fn( $a, $b ) => $a->order - $b->order );

			// Comma seperated list of mapped field item field names
			$fieldValueVal = implode( ',', array_column( $fieldMap->mappedFieldItems, 'key' ) );

			// Get static values array for each mapped field item
			$fieldValuesStatic = array();
			foreach ( $fieldMap->mappedFieldItems as $mappedFieldItem ) {
				$fieldValuesStatic[ $mappedFieldItem->key ] = $mappedFieldItem->value;
			}

			// Static value
			$fieldValueStatic    = $fieldValuesStatic['SUBFIELD'] ?? $fieldValuesStatic['APIFIELD_STATIC'] ?? null;
			$hasSubFields        = ! empty( $fieldMap->subFieldMap );
			$subFields           = null;
			$fromApiPossible     = false;
			$subfieldobjectlinks = null;
			$apiLinkedToField    = null;

			// Fill template
			ob_start();

			if ( $isSubField ) {
				$required         = $source->getSubFields( $parentKey )[ $fieldMap->id ]['required'] ?? false;
				$apiLinkedToField = $source->getSubFields( $parentKey )[ $fieldMap->id ]['linkedTo'] ?? null;
			} else {
				$required         = $source->getFields()[ $fieldMap->id ]['required'] ?? false;
				$apiLinkedToField = $source->getFields()[ $fieldMap->id ]['linkedTo'] ?? null;
			}

			$datasourceFieldDropdownOptions     = $this->getDatasourceDropdown( $datasource->getFields(), $section->id, false, $fieldValueVal, $sourcename, $fieldMap->id, $isSubField, $parentKey );
			$datasourceFieldDropdownOptionOrder = $fieldValueVal;
			if ( $hasSubFields ) {
				$subFields                = $this->getFieldLinks( $fieldMap->subFieldMap, $sourcename, $source, $datasource, $selectedFields, $section, true, $fieldMap->id );
				$subFieldMultilinkObjects = $datasource->getSubFieldMultilinkObjects();
				if ( $subFieldMultilinkObjects !== null ) {
					$subfieldobjectlinks = '<option value="" ' . ( ( ( $fieldMap->multiLinkField ?? null ) === null ) ? 'selected' : '' ) . '></option>';
					foreach ( $subFieldMultilinkObjects as $obj ) {
						$subfieldobjectlinks .= '<option value="' . $obj . '" ' . ( ( ( $fieldMap->multiLinkField ?? null ) === $obj ) ? 'selected' : '' ) . '>' . $obj . '</option>';
					}
				}
			} else {
				$staticValueFromApiFields = $this->getDropdownFieldsForApiField( $fieldMap->mappedFieldItems );
			}

			if ( $isSubField ) {
				$apiFieldName = $fieldMap->id;
			}

			include 'partials/ovas-connect-tab-section-fieldlink.php';
			$selectedFields .= ob_get_clean();
		}
		return $selectedFields;
	}

	private function getSubFieldTemplates( $apiSource, $datasource, $section, $sourcename ) {
		$subFieldsArr                       = $apiSource->getSubfields();
		$subFieldsName                      = key( $apiSource->getSubfields() );
		$fieldStr                           = null;
		$retVal                             = null;
		$subfieldobjectlinks                = null;
		$datasourceFieldDropdownOptionOrder = null;

		if ( $subFieldsArr !== null ) {
			foreach ( $subFieldsArr as $subFieldKey => $subFieldValue ) {
				$line      = null;
				$fieldsStr = null;
				foreach ( $subFieldValue as $subFieldValueKey => $subFieldValueLine ) {
					ob_start();
					$required                       = $subFieldValueLine['required'] ?? false;
					$apiFieldName                   = $subFieldValueKey;
					$datasourceFieldDropdownOptions = $this->getDatasourceDropdown( $datasource->getFields(), $section->id, false, '', $sourcename, $subFieldValueKey, true, $subFieldsName );
					$staticValueFromApiFields       = null;
					$fieldValueStatic               = null;
					$fieldValueVal                  = '';
					$fieldKey                       = $subFieldValueKey;

					include 'partials/ovas-connect-tab-section-fieldlink.php';
					$fieldsStr .= ob_get_clean();
				}
				$retVal .= sprintf( '<template class="apiSourceSubFields" data-name="%1$s"><div class="fieldLinkContainer subfields" data-name="%1$s">%2$s</div></template>', $subFieldKey, $fieldsStr );
			}
		}
		return $retVal;
	}

	public function getApiSubFieldDropdown() {
		$retVal = '';
		foreach ( $apiSourceFields as $key => $value ) {
			$retVal .= sprintf(
				'<option value="%1$s" data-required="%3$s" %4$s>%2$s</option>',
				$key,
				$key,
				$value['required'] ? 'true' : 'false',
				( $selected === $key ? 'selected' : '' )
			);
		}
		return $retVal;
	}

	public function addLogs( $filter = null, $duration = null, $logpageid = null ) {
		global $wpdb;
		$logitems  = '';
		$log_limit = $duration ?? get_option( 'ovas_options' )['ovas_logtab_filter_limit'] ?? -1;

		if ( $filter === null || $filter === '' ) {
			$logItems = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM %i where timestamp > '%1s' ORDER BY timestamp DESC ", $this->plugin->log_table_name, gmdate( 'Y-m-d H:i:s', strtotime( '- ' . $log_limit . ' days' ) ) ),
				OBJECT_K
			);
		} else {
			$logItems = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM %i where timestamp > '%1s' %1s logline LIKE %s ORDER BY timestamp DESC ", $this->plugin->log_table_name, gmdate( 'Y-m-d H:i:s', strtotime( '- ' . $log_limit . ' days' ) ), $log_limit_sql === null ? ' where ' : ' and ', '%' . $wpdb->esc_like( $filter ) . '%' ),
				OBJECT_K
			);
		}
		$logLineContent = '';
		$logResponse    = null;

		$limit = 50; // per page

		$page       = (int) $logpageid ?? 1;
		$total      = count( $logItems ); // total items in array
		$totalPages = ceil( $total / $limit ); // calculate total pages
		$page       = max( $page, 1 ); // get 1 page when $_GET['page'] <= 0
		$page       = min( $page, $totalPages ); // get last page when $_GET['page'] > $totalPages
		$offset     = ( $page - 1 ) * $limit;
		if ( $offset < 0 ) {
			$offset = 0;
		}

		$logItems = array_slice( $logItems, $offset, $limit );

		$pagination = $this->addPagination( $total, $page, $totalPages, $filter );
		$filter     = $this->addFilter( $duration, $filter );

		foreach ( $logItems as $logItem ) {
			$logLineId        = $logItem->id;
			$logTimestamp     = str_replace( ' ', '<br/>', wp_date( 'd-m-Y H:i', strtotime( $logItem->timestamp ) ) );
			$logDatasource    = $this->getDataSourceFromID( $logItem->datasource )->getDataSourceName();
			$logDatasourceImg = $logItem->datasource;
			$logLineItem      = json_decode( $logItem->logline );
			$logAction        = $logLineItem->action ?? null;
			$logLine          = $logLineItem->logLine ?? null;
			$logRequest       = $this->prettyPrintJSON( json_decode( $logLineItem->request ?? null, true ) ) ?? null;
			$logResponse      = null;
			$hasMail1         = false;
			$hasMail2         = false;
			if ( $logLineItem->response ) {
				$logResponse = $this->formatResponse( json_decode( $logLineItem->response ?? null, true ) ) ?? null;
			}
			$hasMail1 = array_key_exists( 'ovas_email_object', json_decode( $logLineItem->request ?? null, true ) );
			$hasMail2 = array_key_exists( 'ovas_email2_object', json_decode( $logLineItem->request ?? null, true ) );

			ob_start();
			include 'partials/ovas-connect-logline.php';
			$logitems .= ob_get_clean();
		}
		include 'partials/ovas-connect-logtab.php';
	}

	public function addPagination( $total, $page, $totalPages, $filter ) {
		$pageButtons = '';

		for ( $i = 1; $i <= $totalPages; $i++ ) {
			$pageButtons .= '<a href="#" onclick="getLogPage(' . $i . ',\'' . $filter . '\'); event.preventDefault();" class="page_button ' . ( $i === $page ? 'current' : '' ) . '">' . $i . '</a> ';
		}
		ob_start();
		include 'partials/ovas-connect-logtab-pagination.php';
		return ob_get_clean();
	}

	public function addFilter( $duration, $filter ) {
		$log_limit = $duration ?? get_option( 'ovas_options' )['ovas_logtab_filter_limit'] ?? -1;
		ob_start();
		include 'partials/ovas-connect-logtab-filter.php';
		return ob_get_clean();
	}

	public function ajaxFilterLogs() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again.' );
		}
		add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_html_tags' ), 10, 2 );
		add_filter( 'safe_style_css', array( $this, 'allowed_css_styles' ), 10, 1 );
		ob_start();
		$this->addLogs(
			sanitize_text_field( wp_unslash( $_POST['filter'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['duration'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['logpageid'] ?? '' ) )
		);
		echo wp_kses( ob_get_clean(), 'template' );
		wp_die();
	}

	public function getDatasourceDropdown( $fields, $sectionId, $current = false, $selected = '', $sourcename = null, $currentField = null, $isSubField = false, $subFieldsName = null ) {
		$retVal = null;

		// Add empty
		if ( $selected === null ) {
			$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', null, '', 'selected disabled' );
		}
		$retVal .= $this->getAdminFormFieldsDynamic( $fields, $sectionId, $current, $selected );
		$retVal .= $this->getFixedFieldDropdownFields( $selected, $sourcename, $currentField, $isSubField, $subFieldsName );

		return $retVal;
	}

	public function getAdminFormFieldsDynamic( $fields, $sectionId, $current = false, $selected = '', $parentName = null ) {
		$retVal = '';
		foreach ( $fields->fields as $field ) {
			if ( $field->type === Ovas_Connect_DataFieldType::SECTION ) {
				if ( $field->id === $sectionId ) {
					$current = true;
				} else {
					$current = false;
				}
			}

			if ( is_array( $field->fields ) && ! empty( $field->fields ) ) {
				$retVal .= ( $current === true && $field->type !== Ovas_Connect_DataFieldType::SECTION ) ? sprintf( '<optgroup label="%1$s" data-name="%2$s">', $field->name, $field->name ) : '';
				$retVal .= $this->getAdminFormFieldsDynamic( $field, $sectionId, $current, $selected, ( $field->type === Ovas_Connect_DataFieldType::CATEGORY ? $field->name : null ) );
				$retVal .= ( $current === true && $field->type !== Ovas_Connect_DataFieldType::SECTION ) ? '</optgroup>' : '';
			} elseif ( $current ) {
				$fullid  = $parentName . ( $parentName !== null ? '/' : '' ) . $field->id;
				$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', $fullid, $field->name, ( in_array( $fullid, explode( ',', $selected ), true ) ? 'selected' : '' ) );
			}
		}

		return $retVal;
	}

	public function getSections( $fields ) {
		$sections = array();
		foreach ( $fields->fields as $field ) {
			if ( $field->type === Ovas_Connect_DataFieldType::SECTION ) {
				array_push( $sections, $field );
			}
		}
		return $sections;
	}

	private function getApiFieldRequiredStatus( $fields, $field = null, $sourcename = null ) {
		foreach ( $fields as $apiSource ) {
			if ( $apiSource->getName() === $sourcename ) {
				return $apiSource->getFields()[ $field ]['required'] ?? false;
			}
		}
		return false;
	}

	// Add fixed value fields to the datasource values dropdown
	private function getFixedFieldDropdownFields( $selected, $sourcename, $currentField, $isSubField, $subFieldsName ) {
		$retVal  = '<optgroup label="' . __( 'Static values', 'ovas-connect' ) . '" data-name="static-values">';
		$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', 'APIFIELD_STATIC', __( 'Static value', 'ovas-connect' ), ( in_array( 'APIFIELD_STATIC', explode( ',', $selected ), true ) ? 'selected' : '' ) );
		$apiObj  = array_column( $this->apiSources, null, 'name' )[ $sourcename ] ?? null;
		$apiHref = null;
		if ( $isSubField ) {
			$apiHref = $apiObj->getSubfields()[ $subFieldsName ][ $currentField ]['linkhref'] ?? null;
		} else {
			$apiHref = $apiObj->getFields()[ $currentField ]['linkhref'] ?? null;
		}

		$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', 'APIFIELD_STATIC_GUID', __( 'GUID', 'ovas-connect' ), ( in_array( 'APIFIELD_STATIC_GUID', explode( ',', $selected ), true ) ? 'selected' : '' ) );
		$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', 'APIFIELD_STATIC_DATE', __( 'Unique datetime string', 'ovas-connect' ), ( in_array( 'APIFIELD_STATIC_DATE', explode( ',', $selected ), true ) ? 'selected' : '' ) );
		$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', 'APIFIELD_STATIC_DATE_FORMATTED', __( 'Formatted date', 'ovas-connect' ), ( in_array( 'APIFIELD_STATIC_DATE_FORMATTED', explode( ',', $selected ), true ) ? 'selected' : '' ) );

		// Get custom fixed field dropdown options for this install
		$customOptions = apply_filters( 'custom_fixed_field_options', null, null );
		if ( $customOptions ) {
			foreach ( $customOptions as $option ) {
				$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', $option['ID'], $option['LABEL'], ( in_array( $option['ID'], explode( ',', $selected ), true ) ? 'selected' : '' ) );
			}
		}

		if ( $currentField === null || $apiHref !== null ) {
			$retVal .= sprintf( '<option value="%1$s" %3$s>%2$s</option>', 'APIFIELD_FROMAPI', __( 'Value via API', 'ovas-connect' ), ( in_array( 'APIFIELD_FROMAPI', explode( ',', $selected ), true ) ? 'selected' : '' ) );
		}
		$retVal .= '</optgroup>';

		return $retVal;
	}

	private function getDataSourceFromID( $dsid ) {
		foreach ( $this->plugin->getDataSources() as $datasource ) {
			if ( $datasource->getDataSourceID() === $dsid ) {
				return $datasource;
			}
		}
		return null;
	}

	private function prettyPrintJSON( $jsonObject ) {
		if ( is_object( $jsonObject ) || is_array( $jsonObject ) ) {
			$retVal = '<ul class="prettyJSON">';
			foreach ( $jsonObject as $key => $value ) {
				if ( is_object( $value ) || is_array( $value ) ) {
					$value = $this->prettyPrintJSON( $value );
				}
				$retVal .= sprintf( '<li><span class="key">%1$s</span> <span class="value">%2$s</span></li>', $key, nl2br( $value ?? '' ) );
			}
			$retVal .= '</ul>';
		} else {
			$retVal = $jsonObject;
		}

		return $retVal;
	}

	private function formatResponse( $response ) {
		$retval = null;

		if ( is_object( $response ) || is_array( $response ) ) {
			foreach ( $response as $apicall ) {
				ob_start();
				$apiName = key( $apicall );
				if ( is_string( current( $apicall ) ) ) {
					$apiValue = json_decode( current( $apicall ) );
				}

				$apiStatusIcon = '';
				if ( $apiValue ) {
					switch ( $apiValue->request->status->success ) {
						case true:
							$apiStatusIcon = ( $apiValue->request->status->errorCode === 'skipped' ? 'marker' : 'yes-alt' );
							break;
						case false:
							$apiStatusIcon = 'dismiss';
					}
				}

				include 'partials/ovas-connect-tab-logtab-response.php';
				$retval .= ob_get_clean();
			}
		}
		return $retval;
	}

	public function ajaxGetStaticValuesFromApi() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again.' );
		}
		// Get apifields object
		$apiObj = array_column( $this->apiSources, null, 'name' )[ sanitize_text_field( wp_unslash( $_POST['apisource'] ?? null ) ) ] ?? null;

		// Initialize connectapi object
		$this->options = get_option( 'ovas_options' );
		$ConnectAPI    = new Ovas_ConnectAPI( $this->options['ovas_api_key'], 'nl', 'json', $this->plugin );
		$apilinkhref   = $field['linkhref'] ?? null;

		if ( sanitize_text_field( wp_unslash( $_POST['subFieldname'] ?? null ) ) !== '' ) {
			$field = $apiObj->getSubfields()[ sanitize_text_field( wp_unslash( $_POST['subFieldname'] ?? null ) ) ][ sanitize_text_field( wp_unslash( $_POST['apifield'] ?? null ) ) ];
		} else {
			// Get api fields for selected dropdown option
			$field = $apiObj->getFields()[ sanitize_text_field( wp_unslash( $_POST['apifield'] ?? null ) ) ];
		}

		// If the field is linked to the value of another field change the href
		if ( sanitize_text_field( wp_unslash( $_POST['linkedToFieldValue'] ?? null ) ) !== null ) {
			$field['linkhref'] = str_replace( '{' . sanitize_text_field( wp_unslash( $_POST['linkedToFieldName'] ?? null ) ) . '}', sanitize_text_field( wp_unslash( $_POST['linkedToFieldValue'] ?? null ) ), $field['linkhref'] );
		}

		if ( $field['linkhref'] !== null ) {
			// Do call to api and fetch the values for this (static) field
			$ConnectAPI->request( $field['linkhref'] ?? '', 'GET', array( 'limit' => 999999 ) );
			$response     = json_decode( $ConnectAPI->getResponse()['body'] );
			$responseData = $this->fillAPIResponseTitle( $field['linkhref'], $response->data );
			echo wp_json_encode( $responseData );
			wp_die();
		} else {
			echo esc_html( __( 'No API link found', 'ovas-connect' ) );
			wp_die();
		}
	}

	public function setActiveTab() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again.' );
		}
		$id = get_current_user_id();
		update_user_meta( $id, $this->plugin->meta_name . '_activetab', sanitize_text_field( wp_unslash( $_POST['tabname'] ?? null ) ) );

		echo wp_json_encode( sanitize_text_field( wp_unslash( $_POST['tabname'] ?? null ) ) );
		wp_die();
	}

	public function getLogPage() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again. Supplied nonce: ' );
		}
		add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_html_tags' ), 10, 2 );
		add_filter( 'safe_style_css', array( $this, 'allowed_css_styles' ), 10, 1 );
		ob_start();
		$this->addLogs(
			sanitize_text_field( wp_unslash( $_POST['filter'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['duration'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['logpageid'] ?? '' ) )
		);
		echo wp_kses( ob_get_clean(), 'template' );
		wp_die();
	}

	public function getActiveTab() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? null ) ), 'ajax-nonce' ) ) {
			die( 'Nonce verification error. Refresh the page and try again.' );
		}
		$id      = get_current_user_id();
		$tabname = get_user_meta( $id, $this->plugin->meta_name . '_activetab', true );

		echo wp_json_encode( $tabname );
		wp_die();
	}

	private function getDropdownFieldsForApiField( $mappedFields ) {
		$retVal = null;
		foreach ( $mappedFields as $mappedField ) {
			if ( $mappedField->key === 'APIFIELD_FROMAPI' ) {
				$retVal .= sprintf( '<option value="%1$s" "selected">%2$s</option>', $mappedField->value, $mappedField->label ?? $mappedField->value );
			}
		}
		return $retVal;
	}

	// Make sure the 'title' field is always filled in the API response.
	// Most API calls return a 'title' field as field to display, but not all calls
	// We call this function to make sure add and fill the title field (based on
	// the API call) if it's not already filled.
	private function fillAPIResponseTitle( $apiLinkHref, $response ) {
		foreach ( $response as $item ) {
			// If title is empty
			if ( ! isset( $item->title ) ) {
				if ( $this->endsWith( $apiLinkHref, '/promises' ) ) {
					$item->title = $item->description;
				}
				if ( $this->endsWith( $apiLinkHref, '/collectioncontracts' ) ) {
					$item->title = $item->description;
				}
				if ( $this->endsWith( $apiLinkHref, '/relations' ) ) {
					// Particulier
					if ( $item->relationTypeId->value === 1 ) {
						$item->title = ( $item->initials ?? '' ) . ' ' . ( $item->intersert ?? '' ) . ' ' . ( $item->lastname ?? '' ) . ' (' . ( $item->relationNumber ?? '' ) . ')';
					} elseif ( $item->relationTypeId->value === 2 ) {
						$item->title = ( $item->companyName ?? '' ) . ' ' . ( $item->companySubname ?? '' ) . ' (' . ( $item->relationNumber ?? '' ) . ')';
					}
				}
				if ( $this->endsWith( $apiLinkHref, '/adoptions' ) ) {
					$item->title = $item->firstname . ' ' . $item->intersert . ' ' . $item->surname . '(' . $item->adoptionId . ')';
				}
			}
		}
		return $response;
	}

	private function startsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		return substr( $haystack, 0, $length ) === $needle;
	}

	private function endsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		if ( ! $length ) {
			return true;
		}
		return substr( $haystack, -$length ) === $needle;
	}

	public function ovas_connect_extend_tag_generator() {
		if ( class_exists( 'WPCF7_TagGenerator' ) ) {
			$tag_generator = WPCF7_TagGenerator::get_instance();
			$tag_generator->add( 'ovas_connect_tags', __( 'Ovas Connect', 'ovas-connect' ), array( $this, 'ovas_connect_tag_generator' ) );
		}
	}

	public function ovas_connect_tag_generator() {
		include 'partials/ovas-connect-cf7-tag-generator.php';
	}

	// Allow extra tags in the template engine html escaping
	public function allowed_html_tags( $tags, $context ) {
		if ( $context === 'template' ) {
			$tags                 = wp_kses_allowed_html( 'post' );
			$tags['a']['onclick'] = true;

			$tags['div']['name']    = true;
			$tags['div']['onclick'] = true;

			$tags['input']['id']       = true;
			$tags['input']['name']     = true;
			$tags['input']['class']    = true;
			$tags['input']['style']    = true;
			$tags['input']['type']     = true;
			$tags['input']['onsubmit'] = true;
			$tags['input']['onchange'] = true;
			$tags['input']['value']    = true;
			$tags['input']['checked']  = true;
			$tags['input']['data-*']   = true;

			$tags['input']['list']  = true;
			$tags['datalist']['id'] = true;

			$tags['select']['id']       = true;
			$tags['select']['name']     = true;
			$tags['select']['class']    = true;
			$tags['select']['multiple'] = true;
			$tags['select']['onchange'] = true;
			$tags['select']['data-*']   = true;

			$tags['option']['value']    = true;
			$tags['option']['selected'] = true;

			$tags['optgroup']['label']  = true;
			$tags['optgroup']['data-*'] = true;

			$tags['span']['onclick'] = true;
			$tags['span']['name']    = true;

			$tags['template']['id']     = true;
			$tags['template']['data-*'] = true;
		}

		return $tags;
	}

	// Allow extra tags in the template engine inline css styles
	public function allowed_css_styles( $styles ) {
		$styles[] = 'display';
		$styles[] = 'visibility';
		return $styles;
	}
}
