<?php
/**
 * FooGallery Image Viewer gallery template
 * This is the template that is run when a FooGallery shortcode is rendered to the frontend
 */
//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;
//the current shortcode args
global $current_foogallery_arguments;
//get our thumbnail sizing args
$args = foogallery_gallery_template_setting( 'thumbnail_size', 'thumbnail' );
//add the link setting to the args
$link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$args['link'] = $link;
//get which lightbox we want to use
$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
$theme = foogallery_gallery_template_setting( 'theme', '' );
$hover_effect = foogallery_gallery_template_setting( 'hover-effect', 'hover-effect-zoom' );
$hover_effect_type = foogallery_gallery_template_setting( 'hover-effect-type', '' );
$caption_content = foogallery_gallery_template_setting( 'caption-content', 'none' );
$alignment = foogallery_gallery_template_setting( 'alignment', 'alignment-center' );
$attachments = $current_foogallery->attachments();
if ( 'fiv-custom' === $theme ) {?>
<style>
	/* Theme - Custom */
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-prev,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-next {
		background-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_bgcolor', '#ffffff' ); ?>;
		color: <?php echo foogallery_gallery_template_setting( 'theme_custom_textcolor', '#1b1b1b' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner > .fiv-inner-container,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-prev,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-next {
		border-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_bordercolor', '#e6e6e6' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-prev:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-next:hover {
		background-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_hovercolor', '#F2F2F2' ); ?>;
	}
</style>
<?php } ?>
<div id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php foogallery_build_class_attribute_render_safe( $current_foogallery, 'foogallery-link-' . $link, 'foogallery-lightbox-' . $lightbox, $theme, $hover_effect, $hover_effect_type, $alignment ); ?>">
	<div class="fiv-inner">
		<div class="fiv-inner-container">
			<?php foreach ( $attachments as $attachment ) {
				echo $attachment->html( $args, true, false );
				if ($caption_content !== 'none'){
					echo $attachment->html_caption( $caption_content );
				}
				echo '</a>';
			} ?>
		</div>
		<div class="fiv-ctrls">
			<div class="fiv-prev"><span><?php echo __('Prev', 'foogallery') ?></span></div>
			<label class="fiv-count"><span class="fiv-count-current">1</span><?php echo __('of', 'foogallery') ?><span><?php echo count($attachments) ?></span></label>
			<div class="fiv-next"><span><?php echo __('Next', 'foogallery') ?></span></div>
		</div>
	</div>
</div>