<?php
/**
 * FooGallery justufued gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$height = foogallery_gallery_template_setting( 'row_height', '150' );
$margins = foogallery_gallery_template_setting( 'margins', '1' );
$captions = foogallery_gallery_template_setting( 'captions', '' ) == 'on';
$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
$args = array(
	'height' => $height,
	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' )
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
?>
<div data-justified-options='{ "rowHeight": <?php echo $height; ?>, "margins": <?php echo $margins; ?>, "captions": <?php echo $captions ? 'true' : 'false'; ?> }' id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php echo foogallery_build_class_attribute( $current_foogallery, 'foogallery-lightbox-' . $lightbox, 'foogallery-justified-loading' ); ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo $attachment->html( $args );
	} ?>
</div>
