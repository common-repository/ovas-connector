<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<div class="fieldLink" data-type="<?php echo ( isset( $hasSubFields ) && $hasSubFields ) ? 'subfield' : ''; ?>">
	<a onclick="removeFieldLink(this)" class="removeFieldLink" style="visibility: <?php echo $required ? 'hidden' : 'visible'; ?>">
		<span class="dashicons dashicons-trash"></span>
	</a>

	<input type="hidden" name="apiFieldName" value="<?php echo esc_attr( $fieldMap->apiFieldName ?? '' ); ?>">
	<input type="hidden" name="apiLinkedToField" value="<?php echo esc_attr( $apiLinkedToField ?? '' ); ?>">
	<span name="apiFieldNameDisplay">
		<?php echo esc_html( $fieldMap->apiFieldName ?? null ); ?>
	</span>

	<?php if ( isset( $subfieldobjectlinks ) && $hasSubFields ) { ?>
		<span class="dashicons dashicons-admin-links"></span>
		<select class="subfieldobjectmultilink" name="subfieldobjectmultilink">
			<?php echo wp_kses( $subfieldobjectlinks, 'template' ); ?>
		</select>
	<?php } ?>

	<span class="dashicons dashicons-arrow-right-alt2"></span>

	<?php if ( ! isset( $subFields ) || $subFields === null ) { ?>
	<div class="datalink">
		<div class="fieldLinkContainer">
			<select name="datasourcefields" onchange="dataSourceChanged(this);" class="datasourceFieldLink" data-order="<?php echo esc_attr( $datasourceFieldDropdownOptionOrder ?? '' ); ?>" multiple>
			<?php echo wp_kses( $datasourceFieldDropdownOptions, 'template' ); ?>
			</select>
			<div class="tail-move-container"></div>

			<input type="text" name="datasourceStaticValue" class="datasourceStaticValue" value="<?php echo esc_attr( is_array( $fieldValueStatic ?? '' ) ? null : $fieldValueStatic ); ?>" style="display: <?php echo esc_attr( in_array( 'APIFIELD_STATIC', explode( ',', $fieldValueVal ), true ) ? 'block' : 'none' ); ?>;"/>

			<div class="datasourceStaticValueFromApi" style="display: <?php echo $fieldValueVal === 'APIFIELD_FROMAPI' ? 'block' : 'none'; ?>;">
				<select name="datasourceStaticValueFromApi" class="datasourceStaticValueFromApi">
				<?php echo wp_kses( $staticValueFromApiFields ?? '', 'template' ); ?>
				</select>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<div class="datalink">
		<div class="fieldLinkContainer subfields" data-name="<?php echo esc_attr( $fieldMap->apiFieldName ?? '' ); ?>">
			<input type="hidden" name="apiFieldName" value="<?php echo esc_attr( $fieldMap->apiFieldName ?? '' ); ?>">
			<?php echo wp_kses( $subFields ?? '', 'template' ); ?>
		</div>
	</div>
	<?php } ?>
</div>
