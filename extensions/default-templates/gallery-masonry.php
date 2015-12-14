<?php
/**
 * FooGallery masonry gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$width = foogallery_gallery_template_setting( 'thumbnail_width', '150' );
$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
$center_align = 'center' === foogallery_gallery_template_setting( 'center_align', false );
$hover_zoom_class = 'default' === foogallery_gallery_template_setting( 'hover_zoom', 'default' ) ? 'foogallery-masonry-hover-zoom-default' : '';
$args = array(
	'width' => $width,
	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' ),
	'crop' => false,
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' ); ?>
<style>
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?> .item {
		margin-bottom: <?php echo $gutter_width; ?>px;
		width: <?php echo $width; ?>px;
	}
	.foogallery-gallery-<?php echo $current_foogallery->ID; ?>-masonry-width {
		width: <?php echo $width; ?>px;
	}
	@media screen and (max-width: 480px) {
		.foogallery-gallery-<?php echo $current_foogallery->ID; ?>-masonry-width { width: 100%; }
		#foogallery-gallery-<?php echo $current_foogallery->ID; ?> .item { width: 100%; }
	}
	<?php if ( $center_align ) { ?>
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?> {
		margin: 0 auto;
	}
	<?php } ?>
</style>
<div data-masonry-options='{ "itemSelector" : ".item", "columnWidth" : ".foogallery-gallery-<?php echo $current_foogallery->ID; ?>-masonry-width", "gutter" : <?php echo $gutter_width; ?>, "isFitWidth" : <?php echo $center_align ? 'true' : 'false'; ?> }' id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php echo foogallery_build_class_attribute( $current_foogallery, 'foogallery-lightbox-' . $lightbox, $hover_zoom_class, 'foogallery-masonry-loading' ); ?>">
	<div class="foogallery-gallery-<?php echo $current_foogallery->ID; ?>-masonry-width"></div>
<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '	<div class="item">' . $attachment->html( $args )  . '</div>
';
	} ?>
</div>
