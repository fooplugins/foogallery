<?php
/**
 * FooGallery FooGrid PRO gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;

$columns = foogallery_gallery_template_setting( 'columns', 'foogrid-cols-4' );

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogrid', $columns );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );

?><div <?php echo $foogallery_default_attributes; ?>>
	<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
		echo foogallery_attachment_html( $attachment );
	} ?>
</div>