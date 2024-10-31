<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<div class="tab">
	<input type="radio" name="css-subtabs-<?php echo esc_attr( $datasourceID ); ?>" id="subtab-<?php echo esc_attr( $tabNumber ); ?>-<?php echo esc_attr( $sectionId ); ?>" class="tab-switch" <?php echo esc_attr( $checked ?? '' ); ?>>
	<label for="subtab-<?php echo esc_attr( $tabNumber ); ?>-<?php echo esc_attr( $sectionId ); ?>" class="tab-label">
		<span class="dashicons dashicons-<?php echo $sectionEnabled ? 'yes-alt' : 'marker'; ?>"></span>
		<?php echo wp_kses( $sectionName ?? '', 'template' ); ?>
	</label>
	<div class="tab-content">
		<div id="subtab-<?php echo esc_attr( $sectionId ); ?>">
			<section id="section_<?php echo esc_attr( $sectionId ); ?>" data-formid="<?php echo esc_attr( $sectionId ); ?>">
				<span class="sectionHeader">
					<div class="btnapisourceselector">
						<input type="checkbox" class="toggle" id="toggle<?php echo esc_attr( $sectionId ); ?>" name="toggle<?php echo esc_attr( $sectionId ); ?>" value="<?php echo esc_attr( $sectionId ); ?>" onchange="dataSourceToggle(this);" <?php echo esc_attr( $checkboxChecked[ $sectionId ] ?? '' ); ?>>
						<label for="<?php echo esc_attr( $sectionId ); ?>"><?php echo esc_attr( $sectionName ); ?></label>
					</div>
				</span>

				<div class="sectionLinks" style="display: <?php echo esc_attr( $sectionDisplay[ $sectionId ] ?? 'none' ); ?>">
					<?php echo wp_kses( $apilinkscontent, 'template' ); ?>
				<div>
			</section>

			<template id="tabSectionAddField<?php echo esc_attr( $sectionId ); ?>">
				<?php require 'ovas-connect-tab-section-fieldlink.php'; ?>
			</template>
		</div>
		<?php echo wp_kses( submit_button() ?? '', 'template' ); ?>
	</div>
</div>
