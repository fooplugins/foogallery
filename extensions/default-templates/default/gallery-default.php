<?php
/**
 * FooGallery default responsive gallery template
 */
global $current_foogallery;

$lightbox = foogallery_gallery_template_setting_lightbox();
$spacing = foogallery_gallery_template_setting( 'spacing', 'spacing-width-10' );
$mobile_columns = foogallery_gallery_template_setting( 'mobile_columns', '' );
$alignment = foogallery_gallery_template_setting( 'alignment', 'fg-center' );

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, $spacing, $alignment, $mobile_columns );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );

?><div <?php echo $foogallery_default_attributes; ?>>
	<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
        echo foogallery_attachment_html( $attachment );
	} ?>
</div>
