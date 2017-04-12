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
$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
$args = array(
	'height' => $height,
	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' )
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$hover_effect_type = foogallery_gallery_template_setting( 'hover-effect-type', '' );
$foogallery_justified_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox);
$foogallery_justified_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_justified_classes ) );

?><div <?php echo $foogallery_justified_attributes; ?> data-justified-options='{ "rowHeight": <?php echo $row_height; ?>, "maxRowHeight": <?php echo $max_row_height; ?>, "margins": <?php echo $margins; ?> }'">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '<div class="foogallery-item">';
		echo $attachment->html( $args, true, false );
		if ( 'hover-effect-caption' === $hover_effect_type ) {
			echo $attachment->html_caption( $caption_content );
		}
		echo '</a>';
		echo '</div>';
	} ?>
</div>
