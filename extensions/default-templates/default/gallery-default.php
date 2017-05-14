<?php
/**
 * FooGallery default responsive gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
$link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$args['link'] = $link;
//add the necessary classes to the anchors
$args['link_attributes'] = array( 'class' => 'foogallery-thumb foogallery-item-inner' );
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$spacing = foogallery_gallery_template_setting( 'spacing', '' );
$alignment = foogallery_gallery_template_setting( 'alignment', 'alignment-center' );
$loading_animation = 'yes' === foogallery_gallery_template_setting( 'loading_animation', 'yes' ) ? 'loading-icon-default' : '';
$hover_effect_type = foogallery_gallery_template_setting( 'hover-effect-type', '' );
$caption_content = foogallery_gallery_template_setting( 'caption-content', 'title' );
$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-link-' . $link, 'foogallery-lightbox-' . $lightbox, $spacing, $alignment, $loading_animation );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );
?><div <?php echo $foogallery_default_attributes; ?>>
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