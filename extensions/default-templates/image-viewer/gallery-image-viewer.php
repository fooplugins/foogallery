<?php
/**
 * FooGallery Image Viewer gallery template
 * This is the template that is run when a FooGallery shortcode is rendered to the frontend
 */
//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;
//the current shortcode args
global $current_foogallery_arguments;

$text_prev_default = foogallery_get_setting( 'language_imageviewer_prev_text',  __( 'Prev', 'foogallery' ) );
$text_prev = foogallery_gallery_template_setting( 'text-prev', $text_prev_default ) ;

$text_of_default = foogallery_get_setting( 'language_imageviewer_of_text', __( 'of', 'foogallery' ) );
$text_of = foogallery_gallery_template_setting( 'text-of', $text_of_default );

$text_next_default = foogallery_get_setting( 'language_imageviewer_next_text', __('Next', 'foogallery') );
$text_next = foogallery_gallery_template_setting( 'text-next', $text_next_default );

//get which lightbox we want to use
$lightbox = foogallery_gallery_template_setting_lightbox();
$alignment = foogallery_gallery_template_setting( 'alignment', 'fg-center' );
$link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$foogallery_imageviewer_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-link-' . $link, 'foogallery-lightbox-' . $lightbox, $alignment );
$foogallery_imageviewer_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_imageviewer_classes ) );
?><div <?php echo $foogallery_imageviewer_attributes; ?>>
	<div class="fiv-inner">
		<div class="fiv-inner-container">
			<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
				echo foogallery_attachment_html( $attachment );
			} ?>
		</div>
		<div class="fiv-ctrls">
			<div class="fiv-prev"><span><?php echo esc_html( $text_prev ); ?></span></div>
			<label class="fiv-count"><span class="fiv-count-current">1</span><?php echo esc_html( $text_of ); ?><span class="fiv-count-total"><?php echo esc_html( $current_foogallery->attachment_count() ); ?></span></label>
			<div class="fiv-next"><span><?php echo esc_html( $text_next ); ?></span></div>
		</div>
	</div>
</div>