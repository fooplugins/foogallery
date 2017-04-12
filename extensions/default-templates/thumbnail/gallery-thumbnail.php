<?php
/**
 * FooGallery single thumbnail gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$position = foogallery_gallery_template_setting( 'position', 'position-block' );
$caption_style = foogallery_gallery_template_setting( 'caption_style', 'caption-simple' );
$caption_title = foogallery_gallery_template_setting( 'caption_title', '' );
$caption_desc = foogallery_gallery_template_setting( 'caption_description', '' );
$caption_bgcolor = foogallery_gallery_template_setting( 'caption_bgcolor', 'rgba(0, 0, 0, 0.8)' );
$caption_color = foogallery_gallery_template_setting( 'caption_color', '#fff' );
$featured_attachment = $current_foogallery->featured_attachment( $args );
$thumb_url = $featured_attachment->url;
if ( foogallery_gallery_template_setting( 'link_custom_url', '' ) == 'on' ) {
    if ( !empty( $featured_attachment->custom_url ) ) {
        $thumb_url = $featured_attachment->custom_url;
    }
    $args['link'] = 'custom';
}
$args['link_attributes'] = array(
    'rel' => 'foobox[' . $current_foogallery->ID . ']',
    'class' => 'foogallery-thumb foogallery-item-inner'
);
$foogallery_single_thumbnail_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-single-thumbnail', 'foogallery-lightbox-' . $lightbox );
$foogallery_single_thumbnail_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_single_thumbnail_classes ) );
?>
<div <?php echo $foogallery_single_thumbnail_attributes; ?>>
    <div class="foogallery-item">
        <?php echo $featured_attachment->html( $args, false, false ); ?>
            <?php echo $featured_attachment->html_img( $args ); ?>
            <div class="foogallery-caption" style="background-color: <?php echo $caption_bgcolor; ?>; color:<?php echo $caption_color; ?>">
                <div class="foogallery-caption-inner">
                    <?php
                    if ( !empty( $caption_title ) ) {
                        echo '<div class="foogallery-caption-title">' . $caption_title . '</div>';
                    }
                    if ( !empty( $caption_desc ) ) {
                        echo '<div class="foogallery-caption-desc">' . $caption_desc . '</div>';
                    } ?>
                </div>
            </div>
        </a>
    </div>
    <div class="fg-st-hidden">
    <?php foreach ( $current_foogallery->attachments() as $attachment ) {
        if ( $attachment->ID !== $featured_attachment->ID ) {
            echo $attachment->html( $args, false, true );
        }
    } ?>
    </div>
</div>
