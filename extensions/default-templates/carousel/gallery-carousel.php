<?php
/**
 * FooGallery default responsive gallery template
 */
global $current_foogallery;

$lightbox = foogallery_gallery_template_setting_lightbox();
$inverted = foogallery_gallery_template_setting( 'inverted', '' );
$show_nav_arrows = foogallery_gallery_template_setting( 'show_nav_arrows', '' );
$show_pagination = foogallery_gallery_template_setting( 'show_pagination', '' );
$show_progress = foogallery_gallery_template_setting( 'show_progress', '' );
$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, $inverted, $show_nav_arrows, $show_pagination, $show_progress );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );
$foogallery_active_class = 'fg-item-active';
$args = foogallery_gallery_template_arguments();
?><div <?php echo $foogallery_default_attributes; ?>>
	<button type="button" class="fg-carousel-prev"></button>
	<div class="fg-carousel-inner">
		<div class="fg-carousel-center"></div>
		<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
			$args['class'] = $foogallery_active_class;
			echo foogallery_attachment_html( $attachment, $args );
			$foogallery_active_class = '';
		} ?>
	</div>
	<div class="fg-carousel-bottom"></div>
	<div class="fg-carousel-progress"></div>
	<button type="button" class="fg-carousel-next"></button>
</div>