<?php
/**
 * FooGallery justufued gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
$height = foogallery_gallery_template_setting( 'row_height', '150' );
$margins = foogallery_gallery_template_setting( 'margins', '1' );
$captions = foogallery_gallery_template_setting( 'captions', 'on' );
$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
$args = array(
	'height' => $height,
	'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' )
);
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
?>
<div id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>"
     class="<?php echo foogallery_build_class_attribute( $current_foogallery, 'foogallery-lightbox-' . $lightbox ); ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo $attachment->html( $args );
	} ?>
</div>
<script type="text/javascript">
	jQuery(function(){
		jQuery(".foogallery-justified").justifiedGallery({
			rowHeight: <?php echo $height; ?>,
			margins: <?php echo $margins; ?>,
			captions: <?php echo $captions == 'on' ? 'true' : 'false'; ?>,
			cssAnimation: true,
			sizeRangeSuffixes: {
				'lt100':'',
				'lt240':'',
				'lt320':'',
				'lt500':'',
				'lt640':'',
				'lt1024':''
			}
		});
	})
</script>
