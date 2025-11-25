<?php
/**
 * FooGallery Spotlight gallery template
 * This is the template that is run when a FooGallery shortcode is rendered to the frontend
 */
//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;
//the current shortcode args
global $current_foogallery_arguments;

$arrow_icon = foogallery_gallery_template_setting( 'arrow_icon', '' );
$next_arrow = $prev_arrow = FooGallery_Spotlight_Gallery_Template::get_arrow_svg( $arrow_icon );

//get which lightbox we want to use
$lightbox = foogallery_gallery_template_setting_lightbox();
$alignment = foogallery_gallery_template_setting( 'alignment', 'fg-center' );
$dots_position = foogallery_gallery_template_setting( 'dots_position', '' );
$link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-link-' . $link, 'foogallery-lightbox-' . $lightbox, $alignment, $dots_position, 'fg-image-viewer fg-spotlight fg-overlay-controls' );
$attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $classes ) );
?><div <?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated via foogallery_build_container_attributes_safe() ?>>
	<div class="fiv-inner">
		<div class="fiv-inner-container">
			<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
				echo foogallery_attachment_html( $attachment ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- function returns pre-escaped HTML
			} ?>
		</div>
		<div class="fiv-ctrls">
			<div class="fiv-prev"><?php echo $prev_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized SVG ?></div>
			<div class="fiv-next"><?php echo $next_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized SVG ?></div>
			<nav id="<?php echo esc_attr( $current_foogallery->container_id() . '_paging-bottom' ); ?>" class="fg-paging-container fg-ph-dots"></nav>
		</div>
	</div>
</div>