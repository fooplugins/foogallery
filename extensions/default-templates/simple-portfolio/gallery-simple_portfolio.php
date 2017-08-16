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
	'height' => $args['height']
);
$args['link_attributes'] = array( 'class' => 'foogallery-thumb' );
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );

$caption_position = foogallery_gallery_template_setting( 'caption_position', '' );

$caption_bg_color = foogallery_gallery_template_setting( 'caption_bg_color', '#ffffff' );
$caption_text_color = foogallery_gallery_template_setting( 'caption_text_color', '#000000' );
if ( !empty( $caption_bg_color ) || !empty( $caption_text_color ) ) {
	echo '<style type="text/css">';
	if ( !empty( $caption_bg_color ) ) {
		echo '#foogallery-gallery-' . $current_foogallery->ID . '.foogallery-simple_portfolio a { background: ' . $caption_bg_color . '; }';
	}
	if ( !empty( $caption_text_color ) ) {
		echo '#foogallery-gallery-' . $current_foogallery->ID . '.foogallery-simple_portfolio a { color: ' . $caption_text_color . '; }';
	}
	echo '</style>';
}

$foogallery_portfolio_classes = foogallery_build_class_attribute_safe( $current_foogallery, $caption_position, 'foogallery-lightbox-' . $lightbox );
$foogallery_portfolio_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_portfolio_classes ) );

?><div <?php echo $foogallery_portfolio_attributes; ?>>
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo foogallery_attachment_html( $attachment, $args );
	} ?>
</div>