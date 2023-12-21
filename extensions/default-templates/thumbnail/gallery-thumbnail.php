<?php
/**
 * FooGallery single thumbnail gallery template
 */
global $current_foogallery;

$lightbox        = foogallery_gallery_template_setting_lightbox();
$position        = foogallery_gallery_template_setting( 'position', 'fg-center' );
$link_custom_url = foogallery_gallery_template_setting( 'link_custom_url' );

$featured_attachment = $current_foogallery->featured_attachment();
$featured_attachment->featured = true;

$args = foogallery_gallery_template_arguments();
$args['override_caption_title'] = foogallery_gallery_template_setting( 'caption_title', '' );
$args['override_caption_desc']  = foogallery_gallery_template_setting( 'caption_description', '' );

if ( 'on' === $link_custom_url && '' !== $lightbox && ! empty( $featured_attachment->custom_url ) ) {
	$featured_attachment->type = 'iframe';
}

$foogallery_single_thumbnail_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-single-thumbnail', 'foogallery-lightbox-' . $lightbox, $position );
$foogallery_single_thumbnail_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_single_thumbnail_classes ) );
?>
<div <?php echo $foogallery_single_thumbnail_attributes; ?>>
    <?php echo foogallery_attachment_html( $featured_attachment, $args ); ?>
    <div class="fg-st-hidden">
    <?php
    foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
        if ( $attachment->url !== $featured_attachment->url ) {
            echo foogallery_attachment_html( $attachment );
        }
    } ?>
    </div>
</div>
