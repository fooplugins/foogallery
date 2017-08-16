<?php
/**
 * FooGallery Justified gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$height = foogallery_gallery_template_setting( 'thumb_height', '250' );
$args = array(
	'height' => $height,
	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' )
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );

$foogallery_justified_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox );
$foogallery_justified_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_justified_classes) );
?>
<div <?php echo $foogallery_justified_attributes; ?>>
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo foogallery_attachment_html( $attachment, $args );
	} ?>
</div>
