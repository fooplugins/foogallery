<?php
/**
 * FooGallery Justified gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$lightbox = foogallery_gallery_template_setting_lightbox();
$foogallery_justified_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox );
$foogallery_justified_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_justified_classes) );
?>
<div <?php echo $foogallery_justified_attributes; ?>>
	<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
		echo foogallery_attachment_html( $attachment );
	} ?>
</div>
