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

$caption_bgcolor = foogallery_gallery_template_setting( 'caption_bgcolor', 'rgba(0, 0, 0, 0.8)' );
$caption_color = foogallery_gallery_template_setting( 'caption_color', '#fff' );
$featured_attachment = $current_foogallery->featured_attachment( $args );
$args['override_caption_title'] = $featured_attachment->caption = foogallery_gallery_template_setting( 'caption_title', '' );
$args['override_caption_desc'] = $featured_attachment->description = foogallery_gallery_template_setting( 'caption_description', '' );

$thumb_url = $featured_attachment->url;
if ( foogallery_gallery_template_setting( 'link_custom_url', '' ) == 'on' ) {
    if ( !empty( $featured_attachment->custom_url ) ) {
        $thumb_url = $featured_attachment->custom_url;
    }
    $args['link'] = 'custom';
}
$args['link_attributes'] = array(
    'rel' => 'foobox[' . $current_foogallery->ID . ']'
);
$foogallery_single_thumbnail_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-single-thumbnail', 'foogallery-lightbox-' . $lightbox, $position );
$foogallery_single_thumbnail_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_single_thumbnail_classes ) );
?>
<div <?php echo $foogallery_single_thumbnail_attributes; ?>>
    <?php echo foogallery_attachment_html( $featured_attachment, $args ); ?>
    <div class="fg-st-hidden">
    <?php foreach ( $current_foogallery->attachments() as $attachment ) {
        if ( $attachment->ID !== $featured_attachment->ID ) {
            echo $attachment->html( $args, false, true );
        }
    } ?>
    </div>
</div>
