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
$layout = foogallery_gallery_template_setting( 'layout', 'fixed' );
$gutter_percent = foogallery_gallery_template_setting( 'gutter_percent', '' );
$args = array(
	'width' => $width,
	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' ),
	'crop' => false,
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$small_screen = $width + $gutter_width + $gutter_width;

?>
<style>
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.masonry-layout-fixed .item {
		margin-bottom: <?php echo $gutter_width; ?>px;
		width: <?php echo $width; ?>px;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.masonry-layout-fixed .masonry-item-width {
		width: <?php echo $width; ?>px;
	}

	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.masonry-layout-fixed .masonry-gutter-width {
		width: <?php echo $gutter_width; ?>px;
	}

	<?php if ( $center_align && 'fixed' === $layout ) { ?>
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?> {
		margin: 0 auto;
	}
	<?php } ?>
</style>
<div data-masonry-options='{ "itemSelector" : ".item", "percentPosition" : "true", "columnWidth" : "#foogallery-gallery-<?php echo $current_foogallery->ID; ?> .masonry-item-width", "gutter" : "#foogallery-gallery-<?php echo $current_foogallery->ID; ?> .masonry-gutter-width", "isFitWidth" : <?php echo $center_align ? 'true' : 'false'; ?> }' id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php foogallery_build_class_attribute_render_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, $hover_zoom_class, 'masonry-layout-' . $layout, $gutter_percent, 'foogallery-masonry-loading' ); ?>">
	<div class="masonry-item-width"></div>
	<div class="masonry-gutter-width"></div>
<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '	<div class="item">' . $attachment->html( $args )  . '</div>
';
	} ?>
</div>
