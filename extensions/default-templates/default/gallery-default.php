<?php
/**
 * FooGallery default responsive gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
$link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$args['link'] = $link;

$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$spacing = foogallery_gallery_template_setting( 'spacing', '' );
$hover_effect = foogallery_gallery_template_setting( 'hover-effect', 'hover-effect-zoom' );
$border_style = foogallery_gallery_template_setting( 'border-style', 'border-style-square-white' );
$alignment = foogallery_gallery_template_setting( 'alignment', 'alignment-center' );
$hover_effect_type = foogallery_gallery_template_setting( 'hover-effect-type', '' );
$caption_hover_effect = foogallery_gallery_template_setting( 'caption-hover-effect', 'hover-caption-simple' );
$caption_content = foogallery_gallery_template_setting( 'caption-content', 'title' );
$loading_animation = 'yes' === foogallery_gallery_template_setting( 'loading_animation', 'yes' ) ? 'foogallery-default-loading' : '';
if ( 'hover-effect-caption' === $hover_effect_type ) {
	$hover_effect = '';
}
?><div id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php foogallery_build_class_attribute_render_safe( $current_foogallery, 'foogallery-link-' . $link, 'foogallery-lightbox-' . $lightbox, $spacing, $hover_effect, $hover_effect_type, $border_style, $alignment, $caption_hover_effect, $loading_animation ); ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo $attachment->html( $args, true, false );
		if ( 'hover-effect-caption' === $hover_effect_type ) {
			echo $attachment->html_caption( $caption_content );
		}
		echo '</a>';
	} ?>
</div>
