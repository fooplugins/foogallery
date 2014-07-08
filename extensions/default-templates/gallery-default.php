<?php
/**
 * FooGallery masonry gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$args = foogallery_gallery_template_setting( 'thumbnail_size', array() );
$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$spacing = foogallery_gallery_template_setting( 'spacing', '' );
$hover_effect = foogallery_gallery_template_setting( 'hover-effect', 'hover-effect-zoom' );
$border_style = foogallery_gallery_template_setting( 'border-style', 'border-style-square-white' );
$alignment = foogallery_gallery_template_setting( 'alignment', 'alignment-center' );
?>
<div class="foogallery-container foogallery-default foogallery-lightbox-<?php echo $lightbox; ?> <?php echo $spacing; ?> <?php echo $hover_effect; ?> <?php echo $border_style; ?> <?php echo $alignment; ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo $attachment->html( $args );
	} ?>
	<div style="clear:both"></div>
</div>