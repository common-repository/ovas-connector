<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<?php echo wp_kses( $pagination ?? '', 'template' ); ?>
<?php echo wp_kses( $filter ?? '', 'template' ); ?>
<table class="logs">
<tr>
	<th><?php esc_html_e( 'Time', 'ovas-connect' ); ?></th>
	<th><?php esc_html_e( 'Datasource', 'ovas-connect' ); ?></th>
	<th><?php esc_html_e( 'Action', 'ovas-connect' ); ?></th>
	<th><?php esc_html_e( 'Request', 'ovas-connect' ); ?></th>
	<th><?php esc_html_e( 'Response', 'ovas-connect' ); ?></th>
	<th><?php esc_html_e( 'Actions', 'ovas-connect' ); ?></th>
</tr>
	<?php echo wp_kses( $logitems, 'template' ); ?>
</table>
<?php echo wp_kses( $pagination ?? '', 'template' ); ?>
