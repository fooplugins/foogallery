<?php
/**
 * FooGallery masonry gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$width = foogallery_gallery_template_setting( 'thumbnail_width', '150' );
$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
$args = array(
	'width' => $width,
 	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' ),
	'crop' => false
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
wp_enqueue_script( 'masonry' );

if ( !foo_check_wp_version_at_least( '3.9' ) ) { ?>
	<script>
		jQuery(function ($) {
			$('#foogallery-gallery-<?php echo $current_foogallery->ID; ?>').masonry({
				itemSelector: '.item',
				columnWidth: <?php echo $width; ?>,
				gutter: <?php echo $gutter_width; ?>
			});
		});
	</script>
<?php } ?>
<style>
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?> .item {
		margin-bottom: <?php echo $gutter_width; ?>px;
		width: <?php echo $width; ?>px;
	}

	#foogallery-gallery-<?php echo $current_foogallery->ID; ?> .item a:hover img {
		-webkit-transform: scale(1.05);
		-moz-transform: scale(1.05);
		-o-transform: scale(1.05);
		-ms-transform: scale(1.05);
		transform: scale(1.05);
	}
</style>
<div id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>"
	 class="<?php echo foogallery_build_class_attribute( $current_foogallery, 'js-masonry', 'foogallery-lightbox-' . $lightbox); ?>"
	 data-masonry-options='{ "itemSelector": ".item", "gutter": <?php echo $gutter_width; ?> }'>
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '<div class="item">';
		echo $attachment->html( $args );
		echo '</div>';
	} ?>
</div>
