<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ovas_Connect_Settings_DatasourceSettings {
	private $plugin;
	public $version     = 2;
	public $datasources = array();

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	// Load settings from json string to settings object
	public function loadSettings( $json ) {
		$settingsObj = json_decode( $json );
		if ( $settingsObj ) {
			if ( property_exists( $settingsObj, 'ovas_connect_settings_version' ) ) {
				$this->version = $settingsObj->ovas_connect_settings_version;
				$this->loadNewSettings( $settingsObj );
			} else {
				$this->loadLegacySettings( $settingsObj );
			}
		}
	}

	// New format
	public function loadNewSettings( $settingsObj ) {
		foreach ( $settingsObj as $_dataSource ) {
			if ( is_object( $_dataSource ) ) {
				$dataSource = new Ovas_Connect_Settings_Datasource( $_dataSource->id );

				foreach ( $_dataSource->sections as $_section ) {
					$section = new Ovas_Connect_Settings_Section( $_section->id, $_section->label, $_section->enabled );

					foreach ( $_section->apisources as $_apiSource ) {
						$apiSource = new Ovas_Connect_Settings_Apisource( $_apiSource->id );

						foreach ( $_apiSource->fieldMaps as $_fieldMap ) {
							$fieldMap = new Ovas_Connect_Settings_Fieldmap( $_fieldMap->id, $_fieldMap->isSubField, $_fieldMap->apiFieldName, null, $_fieldMap->subFieldMap, $_fieldMap->multiLinkField );
							foreach ( $_fieldMap->mappedFieldItems as $_mappedFieldItem ) {
								$mappedFieldItem = new Ovas_Connect_Settings_FieldMapItem( $_mappedFieldItem->key, $_mappedFieldItem->value, $_mappedFieldItem->label, $_mappedFieldItem->order );
								$fieldMap->addFieldMapitems( $mappedFieldItem );
							}
							$apiSource->addFieldMaps( $fieldMap );
						}
						$section->addApiSources( $apiSource );
					}
					$dataSource->addSections( $section );
				}
				$this->addDatasource( $dataSource );
			}
		}
	}

	// Legacy format
	public function loadLegacySettings( $settingsObj ) {
		if ( $settingsObj ) {
			foreach ( $settingsObj as $dsKey => $dsValue ) {
				$ds = new Ovas_Connect_Settings_Datasource( $dsKey );

				// Sections
				foreach ( $dsValue as $sectionKey => $sectionValue ) {
					$section = new Ovas_Connect_Settings_Section( str_replace( 'section_', '', $sectionKey ), str_replace( 'section_', '', $sectionKey ), $sectionValue->enabled );

					// Api Sources
					foreach ( $sectionValue->fields as $apiSourceKey => $apiSourceValue ) {
						$apiSource = new Ovas_Connect_Settings_Apisource( $apiSourceKey );
						$this->getFieldMaps( $apiSourceValue, $apiSource );
						$section->addApiSources( $apiSource );
					}
					$ds->addSections( $section );
				}
				$this->addDatasource( $ds, 1 );
			}
		}
	}

	private function getFieldMaps( $data, $apiSource ) {
		// Field maps
		$fieldMaps = array();

		foreach ( $data as $fieldMapKey => $fieldMapValue ) {
			$mappedField = key( get_mangled_object_vars( $fieldMapValue ) );
			$isSubField  = ( $mappedField === 'SUBFIELD' );
			$subFieldMap = null;

			$fieldMap = new Ovas_Connect_Settings_Fieldmap(
				$fieldMapKey,
				$isSubField,
				$fieldMapKey,
				null,
				$subFieldMap,
				$fieldMapValue->MULTILINK ?? null
			);

			if ( $isSubField ) {
				$subFieldMap = $this->getFieldMaps( $fieldMapValue->SUBFIELD, $fieldMap );
			}

			$order = 0;
			// Field map items
			foreach ( $fieldMapValue as $fieldMapItemKey => $fieldMapItemValue ) {
				if ( $fieldMapItemKey !== 'SUBFIELD' && $fieldMapItemKey !== 'MULTILINK' ) {
					$fieldMapItem = new Ovas_Connect_Settings_FieldMapItem( $fieldMapItemKey, $fieldMapItemValue, $fieldMapItemValue, $order++ );
					$fieldMap->addFieldMapitems( $fieldMapItem );
				}
			}

			$apiSource->addFieldMaps( $fieldMap );
		}
		return $fieldMaps;
	}

	public function addDatasource( $datasource ) {
		$this->datasources[ $datasource->id ] = $datasource;
	}

	public function getSettings( $asJson = false ) {
		if ( $asJson ) {
			return wp_json_encode( $this, JSON_PRETTY_PRINT );
		}
		return $this;
	}

	public function isSectionEnabled( $dataSourceId, $sectionId ) {
		foreach ( $this->datasources as $dataSource ) {
			if ( $dataSource->id === strval( $dataSourceId ) ) {
				foreach ( $dataSource->sections as $section ) {
					if ( $section->id === strval( $sectionId ) ) {
						return $section->enabled;
					}
				}
			}
		}
		return false;
	}

	// Get Fieldmaps for given datasource, section and apisource
	public function getFieldMapsById( $dataSourceId, $sectionId, $apiSourceId ) {
		foreach ( $this->datasources as $dataSource ) {
			if ( $dataSource->id === strval( $dataSourceId ) ) {
				foreach ( $dataSource->sections as $section ) {
					if ( $section->id === strval( $sectionId ) ) {
						foreach ( $section->apisources as $apiSource ) {
							if ( $apiSource->id === strval( $apiSourceId ) ) {
								return $apiSource->fieldMaps;
							}
						}
					}
				}
			}
		}
	}
}
