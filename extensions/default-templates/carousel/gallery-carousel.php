<?php
/**
 * FooGallery default responsive gallery template
 */
global $current_foogallery;

$text_prev = foogallery_get_setting( 'language_carousel_previous_text',  __( 'Previous', 'foogallery' ) );
$text_next = foogallery_get_setting( 'language_carousel_next_text', __( 'Next', 'foogallery' ) );

$lightbox = foogallery_gallery_template_setting_lightbox();
$inverted = foogallery_gallery_template_setting( 'inverted', '' );
$show_nav_arrows = foogallery_gallery_template_setting( 'show_nav_arrows', '' );
$show_pagination = foogallery_gallery_template_setting( 'show_pagination', '' );
$show_progress = foogallery_gallery_template_setting( 'show_progress', '' );
$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, $inverted, $show_nav_arrows, $show_pagination, $show_progress );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );
$foogallery_active_class = 'fg-item-active';
$args = foogallery_gallery_template_arguments();
?><div <?php echo $foogallery_default_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<button type="button" class="fg-carousel-prev" title="<?php echo esc_attr( $text_prev ); ?>"></button>
	<div class="fg-carousel-inner">
		<div class="fg-carousel-center"></div>
		<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
			$args['class'] = $foogallery_active_class;
			echo foogallery_attachment_html( $attachment, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$foogallery_active_class = '';
		} ?>
	</div>
	<div class="fg-carousel-bottom"></div>
	<div class="fg-carousel-progress"></div>
	<button type="button" class="fg-carousel-next" title="<?php echo esc_attr( $text_next ); ?>"></button>
</div>