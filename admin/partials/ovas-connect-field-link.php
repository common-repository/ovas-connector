<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>
<fieldset>
	<legend><?php echo wp_kses( $section ?? '', 'template' ); ?></legend>
	<ul>
		<?php echo wp_kses( $item ?? '', 'template' ); ?>
	</ul>
</fieldset>
