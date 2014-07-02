<?php
/**
 * FooGallery {name} gallery template
 * This is the template that is run when a FooGallery shortcode is rendered to the frontend
 */
global $current_foogallery;
global $current_foogallery_arguments;
$size = foogallery_gallery_template_setting( 'thumbnail_size', 'thumbnail' );
$link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
?>
<ul class="foogallery-container foogallery-{slug} foogallery-lightbox-<?php echo esc_attr($lightbox); ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '<li>' . $attachment->html( $size, $link ) . '</li>';
	} ?>
</ul>