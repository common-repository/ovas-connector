<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-datasource.php';
require_once plugin_dir_path( __FILE__ ) . '../admin/includes/class-ovas-connect-datafield.php';
require_once plugin_dir_path( __FILE__ ) . '../admin/includes/class-ovas-connect-datafieldtype.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-ovas-connect-log.php';

class Ovas_Connect_Datasource_Cf7 extends Ovas_Connect_Datasource {
	public $dataSourceName = 'Contact Form 7';
	public $dataSourceID   = 'CF7';

	public function getFields() {
		$root  = new Ovas_Connect_DataField( 'root', 'root', Ovas_Connect_DataFieldType::ROOT );
		$forms = $this->getForms();

		foreach ( $forms as $form ) {
			$formField = new Ovas_Connect_DataField( $form->ID, $form->post_title, Ovas_Connect_DataFieldType::SECTION );
			$fields    = $this->getFormFields( $form->ID );

			if ( $fields ) {
				foreach ( $fields as $field ) {
					if ( $field->name !== null ) {
						if ( $field->name === 'ovas_email_object' || $field->name === 'ovas_email2_object' ) {
							$cat = new Ovas_Connect_DataField( $field->name, $field->name, Ovas_Connect_DataFieldType::CATEGORY );

							$cat->addChild( new Ovas_Connect_DataField( $field->name . '_subject', 'Subject', Ovas_Connect_DataFieldType::FIELD ) );
							$cat->addChild( new Ovas_Connect_DataField( $field->name . '_recipient', 'Recipient', Ovas_Connect_DataFieldType::FIELD ) );
							$cat->addChild( new Ovas_Connect_DataField( $field->name . '_body', 'Body', Ovas_Connect_DataFieldType::FIELD ) );

							$formField->addChild( $cat );
						} else {
							$formField->addChild( new Ovas_Connect_DataField( $field->name, $field->name, Ovas_Connect_DataFieldType::FIELD ) );
						}
					}
				}
			}
			$root->addChild( $formField );
		}
		return $root;
	}

	private function addField( $fields, $parent ) {
		foreach ( $fields as $key => $value ) {
			if ( is_array( $value ) ) {
				$cat = new Ovas_Connect_DataField( $key, $key, Ovas_Connect_DataFieldType::CATEGORY );
				$this->addField( $value, $cat );
				$parent->addChild( $cat );
			} else {
				$parent->addChild( new Ovas_Connect_DataField( $key, $key, Ovas_Connect_DataFieldType::FIELD ) );
			}
		}
	}

	public function getForms() {
		$cf7Forms = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
			)
		);
		// Sort alphabetically on contact form name
		usort( $cf7Forms, fn( $a, $b ) => strcmp( $a->post_title, $b->post_title ) );
		return $cf7Forms;
	}

	public function getFormFields( $form_id ) {
		if ( class_exists( 'WPCF7_ContactForm' ) ) {
			$ContactForm = WPCF7_ContactForm::get_instance( $form_id );
			return $ContactForm->scan_form_tags();
		}
	}

	public function addHooks() {
		if ( $this->isDataSourceEnabled() ) {
			add_action( 'wpcf7_before_send_mail', array( $this, 'action_wpcf7_submit' ) );
			parent::addHooks();
		}
	}

	public function action_wpcf7_submit( $cf7 ) {
		$options   = get_option( 'ovas_options' );
		$loglineid = null;
		if ( $options !== null ) {
			$opt = json_decode( $options[ 'ovas_fieldlinks_' . $this->dataSourceID ], true );

			$ds = new Ovas_Connect_Settings_DatasourceSettings( $this->plugin );
			$ds->loadSettings( $this->options[ 'ovas_fieldlinks_' . $this->dataSourceID ] );
			$savedSettings = $ds->getSettings();

			// Get CF7 instance
			$wpcf = WPCF7_ContactForm::get_current();

			// If form is enabled, add the request to the Log
			if ( $savedSettings->isSectionEnabled( $this->dataSourceID, $wpcf->id() ) ) {
				// Get submission data
				$submission = WPCF7_Submission::get_instance();
				if ( $submission ) {
					$data               = $submission->get_posted_data();
					$data               = $this->flattenData( $data );
					$data['section_id'] = $wpcf->id();
					if ( empty( $data ) ) {
						return;
					}

					$loglineid = Ovas_Connect_Log::addLog( $this, 'Form submit', $wpcf->title(), wp_json_encode( $data ), null, $data['ovas_transaction_id'] ?? null );
				} else {
					$loglineid = Ovas_Connect_Log::addLog( $this, 'Form submit', 'Submitted form (' . $wpcf7->title() . ') was empty', null, null, null );
				}
			}
		}

		// Submit form to connect (unless it's an iDeal form, in that case we will do it manually later)
		if ( $loglineid !== null && ! $this->plugin->is_contact_form_pronamic_ideal() ) {
			$this->submitFormToConnect( $loglineid );
		}
	}

	// CF7 dropdowns, free choice text field and other elements return their values
	// as an array. We prefer the response as a proper key => value pair so we can
	// properly link them, so we flatten the incoming data before saving it
	private function flattenData( $data ) {
		foreach ( $data as $key => $value ) {
			$retval = '';
			if ( $key === 'ovas_email_object' || $key === 'ovas_email2_object' ) {
				$data[ $key ] = wpcf7_mail_replace_tags( $value );
			} else {
				if ( is_array( $value ) ) {
					foreach ( $value as $subkey => $subval ) {
						$retval .= $subval . ', ';
					}
				} else {
					$retval = $value;
				}
				$data[ $key ] = rtrim( $retval, ', ' );
			}
		}
		return $data;
	}
}
