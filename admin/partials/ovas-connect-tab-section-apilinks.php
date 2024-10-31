<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
  <div class="addApiLinks" name="apiLink<?php echo esc_attr( $sourcename ); ?>" data-sourcename="<?php echo esc_attr( $sourcename ); ?>">
	  <div class="apisourcename">
		  <input type="checkbox" class="toggle" id="apisource<?php echo esc_attr( $datasourceID ); ?><?php echo esc_attr( $sectionId ); ?><?php echo esc_attr( $sourcename ); ?>" onchange="apiSourceLinksToggle(this);" <?php echo $linksDisplay === 'block' ? 'checked' : ''; ?> >
		  <label for="apisource<?php echo esc_attr( $datasourceID ); ?><?php echo esc_attr( $sectionId ); ?><?php echo esc_attr( $sourcename ); ?>"><?php echo esc_attr( $sourcelabel ); ?></label>
	  </div>
	  <div class="apisourcelinks" id="apisourcelinks_<?php echo esc_attr( $datasourceID ); ?><?php echo esc_attr( $sectionId ); ?><?php echo esc_attr( $sourcename ); ?>" style="display: <?php echo esc_attr( $linksDisplay ); ?>">
		<span class="addFieldLink" data-sourcename="<?php echo esc_attr( $sourcename ); ?>" data-sectionid="<?php echo esc_attr( $sectionId ); ?>" data-datasourceid="<?php echo esc_attr( $datasourceID ); ?>">
			<a onclick="openAddFieldLinkPopup(this);">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Add field', 'ovas-connect' ); ?>
			</a>
			<div class="popup hidden">
				<?php echo wp_kses( $addableFields ?? '', 'template' ) ?? esc_html( __( 'No fields left to be added', 'ovas-connect' ) ); ?>
			</div>
		</span>
		<span class="addFieldLink">
			<a onclick="addAllRequiredFieldLinks(this, '<?php echo esc_attr( $sourcename ); ?>', '<?php echo esc_attr( $sectionId ); ?>','<?php echo esc_attr( $datasourceID ); ?>');">
				<span class="dashicons dashicons-insert"></span>
				<?php esc_html_e( 'Add all required fields', 'ovas-connect' ); ?>
			</a>
		</span>

		<div class="fieldLinks" id="fieldLinks<?php echo esc_attr( $sectionId ); ?>">
			<?php echo wp_kses( $selectedFields ?? '', 'template' ); ?>
		</div>
	</div>
	<?php echo wp_kses( $apiSourceSubFieldTemplates ?? '', 'template' ); ?>
</div>
