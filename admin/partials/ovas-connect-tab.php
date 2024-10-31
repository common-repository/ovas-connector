<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<div class="tab">
	<input type="radio" name="css-tabs" id="tab-<?php echo esc_attr( $tabNumber ); ?>" class="tab-switch">
	<label for="tab-<?php echo esc_attr( $tabNumber ); ?>" class="tab-label"><?php echo esc_attr( $tabHeader ); ?></label>
	<div class="tab-content">
		<div class="datasource" id="<?php echo esc_attr( $datasourceID ); ?>">
			<div class="tabs subtabs">
				<?php echo wp_kses( $tabContent, 'template' ); ?>
			</div>
		</div>
	</div>
</div>
