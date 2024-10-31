<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<style>
  form[data-id="ovas_connect_tags"] .control-box button {
	  width: 25%;
	  min-width: 8em;
  }

  form[data-id="ovas_connect_tags"] .control-box .tb_button_description {
	  display: block;
  }
</style>
<div class="control-box">
	<h3><?php esc_html_e( 'Insert tags requried for the \'Ovas Connect\' plugin to function', 'ovas-connect' ); ?></h3>

	<h4><?php esc_html_e( 'Insert tags in the form that add extra data to be available in the emails and for the API', 'ovas-connect' ); ?></h4>

	<p>
		<button type="button" class="button button-primary" onclick="wpcf7.taggen.insert('[hidden ovas_iban]'); tb_remove();">IBAN</button>
		<span class="tb_button_description"><?php esc_html_e( 'The IBAN number of an iDeal payment after it has been completed', 'ovas-connect' ); ?></span>
	</p>

	<p>
		<button type="button" class="button button-primary" onclick="wpcf7.taggen.insert('[hidden ovas_transaction_id]'); tb_remove();">Transaction ID</button>
		<span class="tb_button_description"><?php esc_html_e( 'The transaction ID of an iDeal payment after it has been completed', 'ovas-connect' ); ?></span>
	</p>

	<p>
		<button type="button" class="button button-primary" onclick="wpcf7.taggen.insert('[hidden ovas_email_object]'); tb_remove();">email data</button>
		<span class="tb_button_description"><?php esc_html_e( 'The email sent by CF7', 'ovas-connect' ); ?></span>
	</p>

	<p>
		<button type="button" class="button button-primary" onclick="wpcf7.taggen.insert('[hidden ovas_email2_object]'); tb_remove();">mail (2) data</button>
		<span class="tb_button_description"><?php esc_html_e( 'The \'mail (2)\' email sent by CF7', 'ovas-connect' ); ?></span>
	</p>
</div>
