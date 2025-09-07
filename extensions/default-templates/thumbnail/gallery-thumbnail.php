<?php
/**
 * FooGallery single thumbnail gallery template
 */
global $current_foogallery;

$lightbox        = foogallery_gallery_template_setting_lightbox();
$position        = foogallery_gallery_template_setting( 'position', 'fg-center' );
$link_custom_url = foogallery_gallery_template_setting( 'link_custom_url' );
$show_as_stack   = foogallery_gallery_template_setting( 'show_as_stack' );

$featured_attachment = $current_foogallery->featured_attachment();
$featured_attachment->featured = true;

$args = foogallery_gallery_template_arguments();
$args['override_caption_title'] = foogallery_format_caption_text( foogallery_gallery_template_setting( 'caption_title', '' ) );
$args['override_caption_desc']  = foogallery_format_caption_text( foogallery_gallery_template_setting( 'caption_description', '' ) );

if ( 'on' === $link_custom_url && '' !== $lightbox && ! empty( $featured_attachment->custom_url ) ) {
	$featured_attachment->type = 'iframe';
}

$foogallery_single_thumbnail_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-single-thumbnail', 'foogallery-lightbox-' . $lightbox, $position, $show_as_stack );
$foogallery_single_thumbnail_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_single_thumbnail_classes ) );

// Get 2 arrays of attachments for this gallery. 
// 1 will not be hidden (if show_as_stack is enabled) 
//  and the other will be the default hidden ones, so that they show up in the lightbox.
$attachments_not_hidden = array();
$attachments_hidden = array();

foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) {
    // We can skip the featured attachment.
    if ( $attachment->url === $featured_attachment->url ) {
        continue;
    }

    if ( 'fg-stacked' === $show_as_stack ) {
        $attachments_not_hidden[] = $attachment;
    } else {
        $attachments_hidden[] = $attachment;
    }
}


?>
<div <?php echo $foogallery_single_thumbnail_attributes; ?>>
    <?php echo foogallery_attachment_html( $featured_attachment, $args ); ?>
    <?php 
    foreach ( $attachments_not_hidden as $attachment ) {
        echo foogallery_attachment_html( $attachment );
    }
    ?>
    <div class="fg-st-hidden">
        <?php
        foreach ( $attachments_hidden as $attachment ) {
            echo foogallery_attachment_html( $attachment );
        }
        ?>
    </div>
</div>
