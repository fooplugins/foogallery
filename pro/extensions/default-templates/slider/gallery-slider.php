<?php
/**
 * FooGallery slider gallery template
 */

//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;

$layout = foogallery_gallery_template_setting( 'layout', '' );
$viewport = foogallery_gallery_template_setting( 'viewport', '' );
$highlight = foogallery_gallery_template_setting( 'highlight', 'fgs-purple' );
$thumbnail_captions = foogallery_gallery_template_setting( 'thumbnail_captions', '' );

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'slider', $layout, $highlight, $thumbnail_captions );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );

$attachments_to_render = foogallery_current_gallery_attachments_for_rendering();

?><div <?php echo $foogallery_default_attributes; ?>>
	<?php if ( count($attachments_to_render) > 0 ){ ?>
		<div class="fgs-content-container">
			<div class="fgs-content-stage"></div>
		</div>
		<div class="fgs-item-container">
			<div class="fgs-item-prev"></div>
			<div class="fgs-item-stage">
				<?php foreach ( $attachments_to_render as $attachment ) {
					echo foogallery_attachment_html( $attachment );
				} ?>
			</div>
			<div class="fgs-item-next"></div>
		</div>
	<?php } ?>
</div>
