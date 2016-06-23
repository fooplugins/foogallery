<?php
/**
 * FooGallery portfolio gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$args['image_attributes'] = array(
	'class'  => 'bf-img',
	'height' => $args['height'],
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$gutter = foogallery_gallery_template_setting( 'gutter', 40 );
$caption_position = foogallery_gallery_template_setting( 'caption_position', '' );
?>
<div data-brickfolio-gutter="<?php echo $gutter; ?>" id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php foogallery_build_class_attribute_render_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, 'brickfolio', $caption_position ); ?>">
<?php
foreach ( $current_foogallery->attachments() as $attachment ) {
	echo '<div class="bf-item" style="width:' . $args['width'] . 'px">';
	$caption = null;
	if ( !empty($attachment->caption) || !empty($attachment->description) ) {
		$caption = '<div class="bf-caption">';
		if ( !empty($attachment->caption) ) {
			$caption .= '<h4>' . $attachment->caption . '</h4>';
		}
		if ( !empty($attachment->description) ) {
			$caption .= '<p>' . $attachment->description . '</p>';
		}
		$caption .= '</div>';
	}
	if ( $caption_position === 'bf-captions-above' && !empty($caption) ){
		echo $caption;
		echo $attachment->html( $args );
	} else {
		echo $attachment->html( $args );
		echo $caption;
	}
	echo '</div>';
} ?>
</div>
