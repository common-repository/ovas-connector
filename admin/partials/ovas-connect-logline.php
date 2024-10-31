<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<tr>
	<td class="logline_logtimestamp">
		<?php echo wp_kses_post( $logTimestamp ?? '' ); ?>
	</td>
	<td class="logline_logdatasource">
		<img src="<?php echo esc_url( plugin_dir_url( __DIR__ ) ); ?>images/logo_<?php echo esc_attr( $logDatasourceImg ); ?>.png" class="datasource_image" alt="logo <?php echo esc_attr( $logDatasource ); ?>" title="<?php echo esc_attr( $logDatasource ); ?>"><br/>
	</td>
	<td class="logline_logaction">
		<?php echo wp_kses_post( $logAction ?? '' ); ?> <br/>
		<?php echo wp_kses_post( $logLine ?? '' ); ?>
		</td>
	<td class="logline_logrequest">
		<div class="responseWrapper shaded" onclick="toggleRequest(this)" style="overflow: hidden;">
			<?php echo wp_kses( $logRequest ?? '', 'template' ); ?>
		</div>
		<div class="responseWrapperAfter"></div>
	</td>
	<td class="logline_logresponse">
		<?php echo wp_kses( $logResponse ?? '', 'template' ); ?>
	</td>
	<td class="logline_actions">
		<a href="#" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to resubmit this request to Ovas Connect?', 'ovas-connect' ); ?>')) { resubmitApiCall(<?php echo esc_attr( $logLineId ); ?>)};" title="<?php esc_html_e( 'Resubmit to connect', 'ovas-connect' ); ?>"><span class="dashicons dashicons-update"></span></a> &nbsp;
		<a href="#" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to delete this request?', 'ovas-connect' ); ?>')) { deleteLogLine(<?php echo esc_attr( $logLineId ); ?>)};" title="<?php esc_html_e( 'Delete response', 'ovas-connect' ); ?>"><span class="dashicons dashicons-trash"></span></a>

		<?php if ( $hasMail1 ) { ?>
			<a href="#" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to resend this email?', 'ovas-connect' ); ?>')) { resendMail('<?php echo esc_attr( $logLineId ); ?>', 'ovas_email_object')};" title="<?php esc_html_e( 'Resend mail 1', 'ovas-connect' ); ?>"><span class="dashicons dashicons-email"></span></a>
		<?php } ?>

		<?php if ( $hasMail2 ) { ?>
			<a href="#" onclick="if(confirm('<?php esc_html_e( 'Are you sure you want to resend this email?', 'ovas-connect' ); ?>')) { resendMail('<?php echo esc_attr( $logLineId ); ?>', 'ovas_email2_object')};" title="<?php esc_html_e( 'Resend mail 2', 'ovas-connect' ); ?>"><span class="dashicons dashicons-email"></span></a>
		<?php } ?>
	</td>
</tr>
