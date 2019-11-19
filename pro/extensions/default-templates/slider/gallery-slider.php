<?php
/**
 * FooGallery slider gallery template
 */

//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'slider' );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );

?><div <?php echo $foogallery_default_attributes; ?>>
	<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
		echo foogallery_attachment_html( $attachment );
	} ?>
</div>