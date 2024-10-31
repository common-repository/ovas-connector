<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<ul class="apicallresponse">
	<li class="status">
		<span class="collapseindicator dashicons dashicons-arrow-right"></span>
		<b><?php echo wp_kses_post( $apiName ?? '' ); ?></b>: <span class="dashicons dashicons-<?php echo wp_kses_post( $apiStatusIcon ?? '' ); ?>"></span>
		<ul style="display: none">
			<li>Request ID: <?php echo wp_kses_post( $apiValue->request->id ?? '' ); ?></li>
			<li>Error code: <?php echo wp_kses_post( $apiValue->request->status->errorCode ?? '' ); ?></li>
			<li>Error message: <?php echo wp_kses_post( $apiValue->request->status->errorMessage ?? '' ); ?> <br/>
				<?php echo wp_kses( $this->prettyPrintJSON( $apiValue->data->errors ?? '' ), 'template' ); ?> </li>
		</ul>
	</li>
</ul>
