<?php
/**
 * FooGallery justufued gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$height = foogallery_gallery_template_setting( 'thumb_height', '250' );
$row_height = foogallery_gallery_template_setting( 'row_height', '150' );
$max_row_height = foogallery_gallery_template_setting( 'max_row_height', '200%' );
if ( strpos( $max_row_height, '%' ) !== false ) {
	$max_row_height = '"' . $max_row_height . '"';
}
$margins = foogallery_gallery_template_setting( 'margins', '1' );
$captions = foogallery_gallery_template_setting( 'captions', '' ) == 'on';
$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
$args = array(
	'height' => $height,
	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' )
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$caption_source = foogallery_gallery_template_setting( 'caption_source', 'title' );
?>
<div data-justified-options='{ "rowHeight": <?php echo $row_height; ?>, "maxRowHeight": <?php echo $max_row_height; ?>, "margins": <?php echo $margins; ?>, "captions": <?php echo $captions ? 'true' : 'false'; ?> }' id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php foogallery_build_class_attribute_render_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, 'foogallery-justified-loading' ); ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		if ( 'title' == $caption_source ) {
			$attachment->alt = $attachment->title;
		} else if ( 'caption' == $caption_source ) {
			$attachment->alt = $attachment->caption;
		}
		echo $attachment->html( $args );
	} ?>
</div>
