<?php
/**
 * FooGallery masonry gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;

$lightbox = foogallery_gallery_template_setting_lightbox();
$alignment = foogallery_gallery_template_setting( 'alignment', 'fg-center' );
$layout = foogallery_gallery_template_setting( 'layout', 'fixed' );
$gutter_percent = '';
if ( 'fixed' !== $layout ) {
	$gutter_percent = foogallery_gallery_template_setting( 'gutter_percent', '' );
}
$foogallery_masonry_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, $alignment, $gutter_percent );
$foogallery_masonry_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_masonry_classes) );
?>
<div <?php echo $foogallery_masonry_attributes; ?>>
	<?php if ( 'fixed' !== $layout ) { ?>
	<div class="fg-column-width"></div>
	<div class="fg-gutter-width"></div>
	<?php } ?>
	<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
		echo foogallery_attachment_html( $attachment );
	} ?>
</div>