<?php
/**
 * FooGallery FooGrid PRO gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;

$args = foogallery_gallery_template_setting( 'thumbnail_size', array() );
$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

$columns = foogallery_gallery_template_setting( 'columns', 'foogrid-cols-4' );
$captions = foogallery_gallery_template_setting( 'captions', 'foogrid-caption-below' );
$transition = foogallery_gallery_template_setting( 'transition', 'foogrid-transition-horizontal' );

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogrid', $columns, $captions, $transition );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );

?><div <?php echo $foogallery_default_attributes; ?>>
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo foogallery_attachment_html( $attachment, $args );
	} ?>
</div>