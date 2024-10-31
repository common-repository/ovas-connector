<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly

require_once plugin_dir_path( __FILE__ ) . 'class-ovas-connect-datasource.php';
require_once plugin_dir_path( __FILE__ ) . '../admin/includes/class-ovas-connect-datafield.php';
require_once plugin_dir_path( __FILE__ ) . '../admin/includes/class-ovas-connect-datafieldtype.php';

class Ovas_Connect_Datasource_Woocommerce extends Ovas_Connect_Datasource {
	public $dataSourceName = 'Woocommerce';
	public $dataSourceID   = 'WOO';

	public function getFields() {
		$root        = new Ovas_Connect_DataField( 'root', 'root', Ovas_Connect_DataFieldType::ROOT );
		$mainsection = new Ovas_Connect_DataField( $this->dataSourceID, $this->dataSourceName, Ovas_Connect_DataFieldType::SECTION );

		if ( class_exists( 'WC_order' ) ) {
			$fields = ( new WC_order() )->get_base_data();
			$this->addField( $fields, $mainsection );
			$root->addChild( $mainsection );

			$cat    = new Ovas_Connect_DataField( 'Product', 'Product', Ovas_Connect_DataFieldType::CATEGORY );
			$c      = new WC_Order_Item_Product();
			$fields = $c->get_data();
			$this->addField( $fields, $cat );
			$root->addChild( $cat );
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

	private function getDropdownValue( $field ) {
		return sprintf( '', null );
	}

	public function getSubFieldMultilinkObjects() {
		return array( 'Product' );
	}

	public function addHooks() {
		if ( $this->isDataSourceEnabled() ) {
			add_action( 'woocommerce_thankyou', array( $this, 'action_woocommerce_thankyou' ) );
			parent::addHooks();
		}
	}

	public function action_woocommerce_thankyou( $order_id ) {
		$loglineid = null;
		if ( ! $order_id ) {
			return;
		}

		// Allow code execution only once
		if ( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {
			$order = wc_get_order( $order_id );

			$orderData               = $order->get_data();
			$orderData['section_id'] = 'WOO';

			// Dates are always sent as simple dates, not arrays
			$orderData['date_created']  = $orderData['date_created']->date_i18n();
			$orderData['date_modified'] = $orderData['date_modified']->date_i18n();

			// Add products to the orderdata
			$items = array();
			foreach ( $order->get_items() as $item ) {
				array_push( $items, $item->get_data() );
			}
			$orderData['Product'] = $items;

			$loglineid = Ovas_Connect_Log::addLog( $this, 'Webshop order', 'Order #' . $order_id, wp_json_encode( $orderData ), null );

			// Flag the action as done (to avoid repetitions on reload for example)
			$order->update_meta_data( '_thankyou_action_done', true );
			$order->save();
		}

		// Submit form to connect
		if ( $loglineid !== null ) {
			$this->submitFormToConnect( $loglineid );
		}
	}
}
