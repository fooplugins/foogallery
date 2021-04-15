<?php
/**
 * FooGallery single thumbnail gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
if ( !array_key_exists( 'crop', $args ) ) {
    $args['crop'] = '1'; //we now force thumbs to be cropped by default
}
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$position = foogallery_gallery_template_setting( 'position', 'fg-center' );

$featured_attachment = $current_foogallery->featured_attachment( $args );
$featured_attachment->featured = true;

$args['override_caption_title'] = foogallery_gallery_template_setting( 'caption_title', '' );
$args['override_caption_desc'] = foogallery_gallery_template_setting( 'caption_description', '' );

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
