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
$gutter = foogallery_gallery_template_setting( 'gutter', 40 );
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
$caption_position_class = 'bf-captions-above' === $caption_position ? 'fg-sp-captions-above' : '';
$foogallery_simple_portfolio_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, $caption_position_class, 'foogallery-simple-portfolio' );
$foogallery_simple_portfolio_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_simple_portfolio_classes ) );
?><div data-simple-portfolio-options='{"gutter":<?php echo $gutter; ?>}' <?php echo $foogallery_simple_portfolio_attributes; ?>>
<?php
foreach ( $current_foogallery->attachments() as $attachment ) {
	echo '<div class="foogallery-item"><div class="foogallery-item-inner">';
	$caption = null;
	if ( !empty($attachment->caption) || !empty($attachment->description) ) {
		$caption = '<div class="fg-sp-caption">';
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
	echo '</div></div>';
} ?>
</div>
