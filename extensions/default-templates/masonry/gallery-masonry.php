<?php
/**
 * FooGallery masonry gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$width = foogallery_gallery_template_setting( 'thumbnail_width', '150' );
$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
$center_align = 'center' === foogallery_gallery_template_setting( 'center_align', false );
$layout = foogallery_gallery_template_setting( 'layout', 'fixed' );
$gutter_percent = foogallery_gallery_template_setting( 'gutter_percent', '' );
$thumbnail_link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$args = array(
	'width' => $width,
	'link' => $thumbnail_link,
	'crop' => false,
	'link_attributes' => array( 'class' => 'foogallery-thumb foogallery-item-inner' )
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$hover_effect_type = foogallery_gallery_template_setting( 'hover-effect-type', '' );
$small_screen = $width + $gutter_width + $gutter_width;
$column_layout = $layout !== 'fixed';
$column_width = '"#foogallery-gallery-' . $current_foogallery->ID . ' .masonry-item-width"';
if ( $column_layout ) {
	$gutter = '"#foogallery-gallery-' . $current_foogallery->ID . ' .masonry-gutter-width"';
} else {
	$gutter = $gutter_width;
}
$foogallery_masonry_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'foogallery-lightbox-' . $lightbox, 'masonry-layout-' . $layout );
$foogallery_masonry_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_masonry_classes ) );
?>
<style>
	<?php if ( $center_align && 'fixed' === $layout ) { ?>
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?> {
		margin: 0 auto;
	}
	<?php } ?>
</style>
<div <?php echo $foogallery_masonry_attributes; ?> data-masonry-options='{ "itemSelector" : ".foogallery-item", <?php echo 'fixed' === $layout ? '' : '"percentPosition": "true",'; ?> "columnWidth" : <?php echo $column_width; ?>, "gutter" : <?php echo $gutter; ?>, "isFitWidth" : <?php echo 'fixed' === $layout ? 'true' : 'false'; ?> }'>
	<?php if ( $column_layout ) { ?>
	<div class="masonry-item-width"></div>
	<div class="masonry-gutter-width"></div>
	<?php } else { ?>
	<div class="masonry-item-width" style="width:<?php echo $width; ?>px"></div>
	<?php } ?>
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '<div class="foogallery-item">';
		echo $attachment->html( $args, true, false );
		if ( 'hover-effect-caption' === $hover_effect_type ) {
			echo $attachment->html_caption( $caption_content );
		}
		echo '</a>';
		echo '</div>';
	} ?>
</div>
