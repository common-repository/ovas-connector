<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://ovas.nl
 * @since      1.0.0
 *
 * @package    Ovas_Connect
 * @subpackage Ovas_Connect/admin/partials
 */

	// Set class property
		$this->options = get_option( 'ovas_options' );
?>
		<h1><?php esc_html_e( 'OVAS Connect', 'ovas-connect' ); ?></h1>
		<form method="post" onsubmit="return validateForm()" action="options.php" id="frm_ovas_connect">
		  <div class="wrapper">
			<div class="tabs">

			  <div class="tab">
				<input type="radio" name="css-tabs" id="tab-1" checked class="tab-switch">
				<label for="tab-1" class="tab-label"><span><?php esc_html_e( 'General', 'ovas-connect' ); ?></span></label>
				<div class="tab-content settings-general">
				<!-- Selected API environment: <?php echo wp_kses_post( $this->plugin->api_environment ?? 'LIVE' ); ?>-->
					<?php
					  settings_fields( 'ovas_option_group' );
					  do_settings_sections( 'ovas_settings_admin' );
					  submit_button();
					?>
				</div>
			  </div>

			  <div class="tab">
				<input type="radio" name="css-tabs" id="tab-2" checked class="tab-switch">
				<label for="tab-2" class="tab-label"><span><?php esc_html_e( 'Logs', 'ovas-connect' ); ?></span></label>
				<div class="tab-content">
					<?php
					$this->addLogs();
					?>
				</div>
			  </div>

				<?php
				$this->addTabs();
				?>
			</div>
		  </div>
		</form>
