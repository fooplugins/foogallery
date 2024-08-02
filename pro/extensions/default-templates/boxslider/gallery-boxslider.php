<?php
global $current_foogallery;

$lightbox = foogallery_gallery_template_setting_lightbox();
$foogallery_boxslider_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox );
$foogallery_boxslider_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_boxslider_classes ) );
?>
<div <?php echo $foogallery_boxslider_attributes; ?>>
	<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
		echo foogallery_attachment_html( $attachment );
	} ?>
</div>
