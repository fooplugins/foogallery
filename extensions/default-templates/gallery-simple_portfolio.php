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
?>
<div class="<?php echo foogallery_build_class_attribute( $current_foogallery, 'foogallery-lightbox-' . $lightbox, 'brickfolio' ); ?>">
<?php
foreach ( $current_foogallery->attachments() as $attachment ) {
	echo '<div class="bf-item" style="width:' . $args['width'] . 'px">';
	echo $attachment->html( $args );
	if ( $attachment->caption ) {
		echo '<h4>' . $attachment->caption . '</h4>';
	}
	if ( $attachment->description ) {
		echo '<p>' . $attachment->description . '</p>';
	}
	echo '</div>';
} ?>
</div>