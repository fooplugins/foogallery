<?php
/**
 * FooGallery portfolio gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;

$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
$args['crop'] = '1'; //we now force thumbs to be cropped
$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$args['image_attributes'] = array(
	'class'  => 'bf-img',
	'height' => $args['height']
);
$args['link_attributes'] = array( 'class' => 'foogallery-thumb' );
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );

$caption_position = foogallery_gallery_template_setting( 'caption_position', '' );

$foogallery_portfolio_classes = foogallery_build_class_attribute_safe( $current_foogallery, $caption_position, 'foogallery-lightbox-' . $lightbox );
$foogallery_portfolio_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_portfolio_classes ) );

?><div <?php echo $foogallery_portfolio_attributes; ?>>
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo foogallery_attachment_html( $attachment, $args );
	} ?>
</div>