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
	'crop' => false,
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );

if ( ! foo_check_wp_version_at_least( '3.9' ) ) {} ?>
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
<script>
	jQuery(function ($) {
		var $container<?php echo $current_foogallery->ID; ?> = $('#foogallery-gallery-<?php echo $current_foogallery->ID; ?>');
		// initialize Masonry
		$container<?php echo $current_foogallery->ID; ?>.masonry({
			itemSelector: '.item',
			columnWidth: <?php echo $width; ?>,
			gutter: <?php echo $gutter_width; ?>
		});
		// layout Masonry again after all images have loaded
		$container<?php echo $current_foogallery->ID; ?>.imagesLoaded( function() {
			$container<?php echo $current_foogallery->ID; ?>.masonry();
		});
	});
</script>
<div id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>"
	 class="<?php echo foogallery_build_class_attribute( $current_foogallery, 'foogallery-lightbox-' . $lightbox ); ?>">
<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '	<div class="item">' . $attachment->html( $args )  . '</div>
';
	} ?>
</div>