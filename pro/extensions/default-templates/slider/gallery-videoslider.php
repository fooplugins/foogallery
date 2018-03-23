<?php
/**
 * FooGallery video gallery template
 * This is the template that is run when a FooGallery shortcode is rendered to the frontend
 */
//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;
//the current shortcode args
global $current_foogallery_arguments;

$layout = foogallery_gallery_template_setting( 'layout', '' );
$theme = foogallery_gallery_template_setting( 'theme', '' );
$highlight = foogallery_gallery_template_setting( 'highlight', '' );
$viewport = foogallery_gallery_template_setting( 'viewport', '' );

if ( 'rvs-custom' === $theme ) {?>
<style>
	/* Custom Theme */
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom.rvs-container,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item-title {
		color: <?php echo foogallery_gallery_template_setting( 'theme_custom_textcolor', '#ffffff' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item-credits {
		color: #767676;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom.rvs-container,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-player {
		background-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_bgcolor', '#000000' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next:hover {
		background-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_hovercolor', '#222222' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-container,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-prev,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-next {
		border-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_dividercolor', '#2e2e2e' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item:first-child {
		border-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_bgcolor', '#000000' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item:first-child:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom .rvs-nav-item:first-child:hover {
		border-color: <?php echo foogallery_gallery_template_setting( 'theme_custom_hovercolor', '#222222' ); ?>;
	}
</style>
<?php }

if ( 'rvs-custom-highlight' === $highlight ) {?>
<style>
	/* Custom Highlight */
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-close:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-close:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-close:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-play-video:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-play-video:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-play-video:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight.rvs-thumb-play .rvs-nav-container span.rvs-nav-item-thumb:hover:before {
		background-color: <?php echo foogallery_gallery_template_setting( 'highlight_custom_bgcolor', '#7816D6' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:first-child,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:first-child:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:first-child:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-nav-item.rvs-active:first-child:hover {
		border-color: <?php echo foogallery_gallery_template_setting( 'highlight_custom_bgcolor', '#7816D6' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-close:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-close:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-close:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-play-video:active,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-play-video:focus,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-play-video:hover,
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-active .rvs-nav-item-title {
		color: <?php echo foogallery_gallery_template_setting( 'highlight_custom_textcolor', '#ffffff' ); ?>;
	}
	#foogallery-gallery-<?php echo $current_foogallery->ID; ?>.rvs-custom-highlight .rvs-active .rvs-nav-item-credits {
		color: <?php echo foogallery_gallery_template_setting( 'highlight_custom_textcolor', '#ffffff' ); ?>;
	}
</style>
<?php } ?>
<div id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php echo foogallery_build_class_attribute( $current_foogallery, 'rvs-container rvs-hide-credits', $layout, $theme, $highlight, $viewport ); ?>">

	<div class="rvs-item-container">
		<div class="rvs-item-stage">

			<?php foreach ( $current_foogallery->attachments() as $attachment ) { ?>
			<div class="rvs-item" style="background-image: url(<?php echo $attachment->url; ?>)">
				<p class="rvs-item-text"><?php echo $attachment->title; ?></p>
				<?php echo $attachment->html( array(
					'link_attributes' => array( 'class' => 'rvs-play-video' )
				), false ); ?>
			</div>
			<?php } ?>

		</div>
	</div>

	<div class="rvs-nav-container">
		<a class="rvs-nav-prev"></a>
		<div class="rvs-nav-stage">

			<?php foreach ( $current_foogallery->attachments() as $attachment ) { ?>
			<a class="rvs-nav-item">
				<span class="rvs-nav-item-thumb" style="background-image: url(<?php echo foogallery_foovideo_get_video_thumbnail_from_attachment( $attachment ); ?>)"></span>
				<h4 class="rvs-nav-item-title"><?php echo $attachment->title; ?></h4>
			</a>
			<?php } ?>

		</div>
		<a class="rvs-nav-next"></a>
	</div>

</div>
