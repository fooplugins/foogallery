<?php
/**
 * FooGallery default responsive gallery template
 */
global $current_foogallery;

$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );
$foogallery_active_class = 'fg-item-active';
?><div <?php echo $foogallery_default_attributes; ?>>
	<button type="button" class="fg-carousel-prev"></button>
	<div class="fg-carousel-inner">
		<div class="fg-carousel-center"></div>
		<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
			echo foogallery_attachment_html( $attachment, array('class' => $foogallery_active_class) );
			$foogallery_active_class = '';
		} ?>
	</div>
	<div class="fg-carousel-bottom"></div>
	<div class="fg-carousel-progress"></div>
	<button type="button" class="fg-carousel-next"></button>
</div>