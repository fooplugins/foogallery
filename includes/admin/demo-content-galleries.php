<?php

return array(
	array(
		'key'         => 'responsive',
		'post_title'  => 'Demo : Responsive',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo6' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'default',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'default_thumbnail_dimensions'  => array(
					'width'  => '270',
					'height' => '230',
				),
				'default_thumbnail_link'        => 'image',
				'default_lightbox'              => 'foogallery',
				'default_spacing'               => 'fg-gutter-10',
				'default_alignment'             => 'fg-center',


				//Appearance
				'default_theme'                 => 'fg-dark',               // options : 'fg-light', 'fg-dark', 'fg-custom'
				'default_spacing'               => 'fg-gutter-10',
				'default_border_size'           => '',        				// options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'default_rounded_corners'       => '',                      // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'default_drop_shadow'           => 'fg-shadow-medium',      // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'default_inner_shadow'          => '',                      // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'default_loading_icon'          => 'fg-loading-partial',    // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'default_loaded_effect'         => 'fg-loaded-flip',        // options : 'fg-loaded-fade-in',
				'default_instagram'             => '',

				//Captions
				'default_captions_type'         => '',
				'default_caption_title_source'  => '',
				'default_caption_desc_source'   => '',
				'default_captions_limit_length' => '',

				//Hover Effects
				
				//'default_hover_effect_caption_visibility' => 'fg-caption-hover',  // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				//'default_hover_effect_color'              => '',                  // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				//'default_hover_effect_icon'               => 'fg-hover-zoom',     // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				//'default_hover_effect_scale'              => 'fg-hover-zoomed',   // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				//'default_hover_effect_transition'         => 'fg-hover-fade',     // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'default_hover_effect_type'               => 'preset',            // options : 'none', 'normal', 'preset'
				'default_hover_effect_preset'             => 'fg-preset fg-brad',
				'default_hover_effect_preset_size'        => 'fg-preset-small',
				//'default_caption_invert_color'            => '',                  // options : '', 'fg-light-overlays', 'fg-transparent-overlays'


				//Filtering
				'default_filtering_type' => '',

				//Paging
				'default_paging_type' => '',

				//Advanced
				'default_state' => 'no',
				'default_custom_settings' => '',
				'default_lazyload' => '',

				//Video
				'default_video_autoplay'    => 'yes',
				'default_video_hover_icon'  => 'fg-video-default',
				'default_video_size'        => '640x360',
				'default_video_sticky_icon' => '',
			),
		),
	), //Demo : Responsive
	array(
		'key'         => 'image-viewer',
		'post_title'  => 'Demo : Image Viewer',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo3', 'demo1', 'demo5', 'demo10', 'demo7', 'demo2' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'image-viewer',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'image-viewer_thumbnail_size'  => array(
					'width'  => '640',
					'height' => '360',
					'crop'   => '1'
				),
				'image-viewer_thumbnail_link'        => 'image',
				'image-viewer_lightbox'              => 'foogallery',
				'image-viewer_alignment'             => 'fg-center',
				'image-viewer_looping'               => 'enabled',


				//Appearance
				'image-viewer_theme'                 => 'fg-light',              // options : 'fg-light', 'fg-dark', 'fg-custom'
				'image-viewer_border_size'           => 'fg-border-medium',      // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'image-viewer_rounded_corners'       => 'fg-round-small',       // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'image-viewer_drop_shadow'           => 'fg-shadow-outline',     // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'image-viewer_inner_shadow'          => 'fg-shadow-inset-large', // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'image-viewer_loading_icon'          => 'fg-loading-pulse',      // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'image-viewer_loaded_effect'         => 'fg-loaded-scale-up',    // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
				'image-viewer_instagram'             => '',

				//Captions
				'image-viewer_captions_type'         => '',
				'image-viewer_caption_title_source'  => '',
				'image-viewer_caption_desc_source'   => '',
				'image-viewer_captions_limit_length' => '',



				//Hover Effects
				'image-viewer_gutter_width'                    => '10',
				'image-viewer_hover_effect_caption_visibility' => 'fg-caption-always',  // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'image-viewer_hover_effect_color'              => 'fg-hover-grayscale', // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'image-viewer_hover_effect_icon'               => 'fg-hover-plus3',     // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'image-viewer_hover_effect_preset'             => 'fg-custom',
				'image-viewer_hover_effect_scale'              => 'fg-hover-semi-zoomed',   // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'image-viewer_hover_effect_transition'         => 'fg-hover-fade',     // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'image-viewer_hover_effect_type'               => 'normal',            // options : 'none', 'normal', 'preset'
				'image-viewer_caption_invert_color'            => 'fg-light-overlays',                  // options : '', 'fg-light-overlays', 'fg-transparent-overlays'


				//Filtering
				'image-viewer_filtering_type' => '',

				//Paging
				'image-viewer_paging_type' => '',

				//Advanced
				'image-viewer_state' => 'no',
				'image-viewer_custom_settings' => '',
				'image-viewer_lazyload' => '',

				//Video
				'image-viewer_video_autoplay'    => 'yes',
				'image-viewer_video_hover_icon'  => 'fg-video-default',
				'image-viewer_video_size'        => '640x360',
				'image-viewer_video_sticky_icon' => '',
			),
		),
	), //Demo : Image Viewer
	array(
		'key'         => 'justified',
		'post_title'  => 'Demo : Justified',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo2', 'demo1', 'demo3', 'demo4', 'demo5', 'demo6', 'demo7', 'demo8', 'demo9', 'demo10' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'justified',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'justified_row_height'            => '200',
				'justified_thumb_height'          => '300',
				'justified_thumbnail_link'        => 'image',
				'justified_lightbox'              => 'foogallery',
				'justified_align'                 => 'center',
				'justified_margins'               => '1',

				//Appearance
				'justified_theme'                 => 'fg-dark',              // options : 'fg-light', 'fg-dark', 'fg-custom'
				'justified_border_size'           => '',                     // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'justified_rounded_corners'       => '',                     // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'justified_drop_shadow'           => '',                     // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'justified_inner_shadow'          => '',                     // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'justified_loading_icon'          => 'fg-loading-default',   // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'justified_loaded_effect'         => 'fg-loaded-fade-in',    // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
				'justified_instagram'             => '',

				//Captions
				'justified_captions_type'         => '',
				'justified_caption_title_source'  => '',
				'justified_caption_desc_source'   => 'none',
				'justified_captions_limit_length' => 'clamp',
				'justified_caption_alignment'     => 'fg-c-c',
				'justified_caption_title_clamp'   => '1',

				//Hover Effects
				'justified_hover_effect_caption_visibility' => 'fg-caption-always', // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'justified_hover_effect_color'              => '',                  // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'justified_hover_effect_icon'               => 'fg-hover-zoom2',    // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'justified_hover_effect_preset'             => 'fg-custom',
				'justified_hover_effect_scale'              => 'fg-hover-zoomed',   // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'justified_hover_effect_transition'         => 'fg-hover-instant',  // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'justified_hover_effect_type'               => 'normal',            // options : 'none', 'normal', 'preset'
				'justified_caption_invert_color'            => '',                  // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

				//Filtering
				'justified_filtering_type' => '',

				//Paging
				'justified_paging_type' => '',

				//Advanced
				'justified_state' => 'no',
				'justified_custom_settings' => '',
				'justified_lazyload' => '',

				//Video
				'justified_video_autoplay'    => 'yes',
				'justified_video_hover_icon'  => 'fg-video-default',
				'justified_video_size'        => '640x360',
				'justified_video_sticky_icon' => '',
			),
		),
	), //Demo : Justified
	array(
		'key'         => 'masonry',
		'post_title'  => 'Demo : Masonry',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo4', 'demo2', 'demo3', 'demo1', 'demo5', 'demo6', 'demo7', 'demo8', 'demo9', 'demo10' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'masonry',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'masonry_thumbnail_width'       => '250',
				'masonry_layout'                => 'fixed',
				'masonry_thumbnail_link'        => 'image',
				'masonry_lightbox'              => 'foogallery',
				'masonry_align'                 => 'fg-center',
				'masonry_gutter_width'          => '10',

				//Appearance
				'masonry_theme'                 => 'fg-light',              // options : 'fg-light', 'fg-dark', 'fg-custom'
				'masonry_border_size'           => 'fg-border-medium',      // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'masonry_rounded_corners'       => 'fg-round-small',        // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'masonry_drop_shadow'           => 'fg-shadow-outline',     // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'masonry_inner_shadow'          => '',                      // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'masonry_loading_icon'          => 'fg-loading-default',    // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'masonry_loaded_effect'         => 'fg-loaded-fade-in',     // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
				'masonry_instagram'             => '',

				//Captions
				'masonry_captions_type'         => '',
				'masonry_caption_title_source'  => '',
				'masonry_caption_desc_source'   => '',
				'masonry_captions_limit_length' => 'clamp',
				'masonry_caption_title_clamp'   => '1',
				'masonry_caption_desc_clamp'    => '2',
				'masonry_caption_alignment'     => '',
				'masonry_caption_visibility_no_hover_effect' => 'fg-captions-bottom',

				//Hover Effects
				'masonry_hover_effect_caption_visibility' => 'fg-captions-bottom', // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'masonry_hover_effect_color'              => '',                   // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'masonry_hover_effect_icon'               => 'fg-hover-zoom4',      // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'masonry_hover_effect_preset'             => '',
				'masonry_hover_effect_scale'              => 'fg-hover-semi-zoomed',    // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'masonry_hover_effect_transition'         => 'fg-hover-fade',      // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'masonry_hover_effect_type'               => 'normal',             // options : 'none', 'normal', 'preset'
				'masonry_caption_invert_color'            => '',                   // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

				//Filtering
				'masonry_filtering_type' => '',

				//Paging
				'masonry_paging_type' => '',

				//Advanced
				'masonry_state' => 'no',
				'masonry_custom_settings' => '',
				'masonry_lazyload' => '',

				//Video
				'masonry_video_autoplay'    => 'yes',
				'masonry_video_hover_icon'  => 'fg-video-default',
				'masonry_video_size'        => '640x360',
				'masonry_video_sticky_icon' => '',
			),
		),
	), //Demo : Masonry
	array(
		'key'         => 'portfolio',
		'post_title'  => 'Demo : Simple Portfolio',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo5', 'demo1', 'demo2', 'demo3', 'demo4', 'demo6', 'demo7', 'demo8', 'demo9', 'demo10' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'simple_portfolio',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'simple_portfolio_thumbnail_dimensions'  => array(
					'width'  => '250',
					'height' => '200',
				),
				'simple_portfolio_thumbnail_link'        => 'image',
				'simple_portfolio_lightbox'              => 'foogallery',
				'simple_portfolio_align'                 => 'center',
				'simple_portfolio_gutter'                => '5',

				//Appearance
				'simple_portfolio_theme'                 => 'fg-dark',              // options : 'fg-light', 'fg-dark', 'fg-custom'
				'simple_portfolio_border_size'           => 'fg-border-medium',       // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'simple_portfolio_rounded_corners'       => '',                     // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'simple_portfolio_drop_shadow'           => '',      // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'simple_portfolio_inner_shadow'          => '',                     // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'simple_portfolio_loading_icon'          => 'fg-loading-default',   // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'simple_portfolio_loaded_effect'         => 'fg-loaded-swing-down',    // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
				'simple_portfolio_instagram'             => '',

				//Captions
				'simple_portfolio_captions_type'         => '',
				'simple_portfolio_caption_title_source'  => '',
				'simple_portfolio_caption_desc_source'   => '',
				'simple_portfolio_captions_limit_length' => 'clamp',
				'simple_portfolio_caption_title_clamp'   => '2',
				'simple_portfolio_caption_desc_clamp'    => '2',
				'simple_portfolio_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'simple_portfolio_hover_effect_caption_visibility' => 'fg-caption-always', // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'simple_portfolio_hover_effect_color'              => '',                   // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'simple_portfolio_hover_effect_icon'               => 'fg-hover-plus3',      // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'simple_portfolio_hover_effect_preset'             => '',
				'simple_portfolio_hover_effect_scale'              => '',    // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'simple_portfolio_hover_effect_transition'         => 'fg-hover-slide-down',      // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'simple_portfolio_hover_effect_type'               => 'normal',             // options : 'none', 'normal', 'preset'
				'simple_portfolio_caption_invert_color'            => '',                   // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

				//Filtering
				'simple_portfolio_filtering_type' => '',

				//Paging
				'simple_portfolio_paging_type' => '',

				//Advanced
				'simple_portfolio_state' => 'no',
				'simple_portfolio_custom_settings' => '',
				'simple_portfolio_lazyload' => '',

				//Video
				'simple_portfolio_video_autoplay'    => 'yes',
				'simple_portfolio_video_hover_icon'  => 'fg-video-default',
				'simple_portfolio_video_size'        => '640x360',
				'simple_portfolio_video_sticky_icon' => '',
			),
		),
	), //Demo : Simple Portfolio
	array(
		'key'         => 'portfolio2',
		'post_title'  => 'Demo : Simple Portfolio (Variation)',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo6', 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo7', 'demo8', 'demo9', 'demo10' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'simple_portfolio',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'simple_portfolio_thumbnail_dimensions'  => array(
					'width'  => '220',
					'height' => '180',
				),
				'simple_portfolio_thumbnail_link'        => 'image',
				'simple_portfolio_lightbox'              => 'foogallery',
				'simple_portfolio_align'                 => 'center',
				'simple_portfolio_gutter'                => '5',

				//Appearance
				'simple_portfolio_theme'                 => 'fg-transparent',              // options : 'fg-light', 'fg-dark', 'fg-custom'
				'simple_portfolio_border_size'           => '',       // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'simple_portfolio_rounded_corners'       => 'fg-round-large',                     // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'simple_portfolio_drop_shadow'           => '',      // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'simple_portfolio_inner_shadow'          => '',                     // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'simple_portfolio_loading_icon'          => 'fg-loading-default',   // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'simple_portfolio_loaded_effect'         => 'fg-loaded-flip',    // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
				'simple_portfolio_instagram'             => '',

				//Captions
				'simple_portfolio_captions_type'         => '',
				'simple_portfolio_caption_title_source'  => '',
				'simple_portfolio_caption_desc_source'   => 'none',
				'simple_portfolio_captions_limit_length' => '',
				'simple_portfolio_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'simple_portfolio_hover_effect_caption_visibility' => 'fg-caption-always', // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'simple_portfolio_hover_effect_color'              => '',                   // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'simple_portfolio_hover_effect_icon'               => 'fg-hover-zoom2',      // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'simple_portfolio_hover_effect_preset'             => 'fg-custom',
				'simple_portfolio_hover_effect_scale'              => 'fg-hover-zoomed',    // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'simple_portfolio_hover_effect_transition'         => 'fg-hover-fade',      // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'simple_portfolio_hover_effect_type'               => 'normal',             // options : 'none', 'normal', 'preset'
				'simple_portfolio_caption_invert_color'            => '',                   // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

				//Filtering
				'simple_portfolio_filtering_type' => '',

				//Paging
				'simple_portfolio_paging_type' => '',

				//Advanced
				'simple_portfolio_state' => 'no',
				'simple_portfolio_custom_settings' => '',
				'simple_portfolio_lazyload' => '',

				//Video
				'simple_portfolio_video_autoplay'    => 'yes',
				'simple_portfolio_video_hover_icon'  => 'fg-video-default',
				'simple_portfolio_video_size'        => '640x360',
				'simple_portfolio_video_sticky_icon' => '',
			),
		),
	), //Demo : Simple Portfolio : Variation
	array(
		'key'         => 'thumbnail',
		'post_title'  => 'Demo : Single Thumbnail',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo9', 'demo4', 'demo3', 'demo2', 'demo1', 'demo5', 'demo6', 'demo7', 'demo8', 'demo9', 'demo10' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'thumbnail',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'thumbnail_thumbnail_dimensions'  => array(
					'width'  => '350',
					'height' => '250',
				),
				'thumbnail_layout'                => 'fixed',
				'thumbnail_thumbnail_link'        => 'image',
				'thumbnail_lightbox'              => 'foogallery',
				'thumbnail_align'                 => 'fg-center',
				'thumbnail_gutter_width'          => '10',

				//Appearance
				'thumbnail_theme'                 => 'fg-light',              // options : 'fg-light', 'fg-dark', 'fg-custom'
				'thumbnail_border_size'           => 'fg-border-medium',      // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'thumbnail_rounded_corners'       => 'fg-round-small',        // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'thumbnail_drop_shadow'           => 'fg-shadow-outline',     // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'thumbnail_inner_shadow'          => 'fg-shadow-inset-medium',// options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'thumbnail_loading_icon'          => 'fg-loading-default',    // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'thumbnail_loaded_effect'         => 'fg-loaded-fade-in',     // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
				'thumbnail_instagram'             => '',

				//Captions
				'thumbnail_captions_type'         => '',
				'thumbnail_caption_title_source'  => '',
				'thumbnail_caption_desc_source'   => '',
				'thumbnail_captions_limit_length' => 'clamp',
				'thumbnail_caption_title_clamp'   => '1',
				'thumbnail_caption_desc_clamp'    => '2',
				'thumbnail_caption_alignment'     => 'fg-c-c',
				'thumbnail_caption_title'         => '{{gallery-title}}',
				'thumbnail_caption_description'   => '{{gallery-count}} Images',
				

				//Hover Effects
				'thumbnail_hover_effect_caption_visibility' => 'fg-caption-always', // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'thumbnail_hover_effect_color'              => '',                   // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'thumbnail_hover_effect_icon'               => 'fg-hover-zoom4',      // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'thumbnail_hover_effect_preset'             => '',
				'thumbnail_hover_effect_scale'              => 'fg-hover-semi-zoomed',    // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'thumbnail_hover_effect_transition'         => 'fg-hover-fade',      // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'thumbnail_hover_effect_type'               => 'normal',             // options : 'none', 'normal', 'preset'
				'thumbnail_caption_invert_color'            => '',                   // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

				//Filtering
				'thumbnail_filtering_type' => '',

				//Paging
				'thumbnail_paging_type' => '',

				//Advanced
				'thumbnail_state' => 'no',
				'thumbnail_custom_settings' => '',
				'thumbnail_lazyload' => '',

				//Video
				'thumbnail_video_autoplay'    => 'yes',
				'thumbnail_video_hover_icon'  => 'fg-video-default',
				'thumbnail_video_size'        => '640x360',
				'thumbnail_video_sticky_icon' => '',
			),
		),
	), //Demo : Single Thumbnail
    array(
        'key'         => 'carousel',
        'post_title'  => 'Demo : Carousel',
        'post_status' => 'publish',
        'post_type'   => FOOGALLERY_CPT_GALLERY,
        'items'       => array( 'demo7', 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo7', 'demo8', 'demo9' ),
        'meta_input'  => array(
            FOOGALLERY_META_TEMPLATE => 'carousel',
            FOOGALLERY_META_SETTINGS => array(
                'foogallery_items_view'         => 'preview',
                'carousel_autoplay_interaction'=> 'pause',
                'carousel_autoplay_time' => '0',
                'carousel_centerOnClick'=> 'true',
                'carousel_maxItems'=> '5',
                'carousel_scale'=> '0.12',
                'carousel_gutter'=>
                    array(
                        'min'=> '-40',
                        'max'=> '-20',
                        'units'=> '%',
                    ),
                'carousel_inverted'=> '',

                //General
                'carousel_thumbnail_dimensions'  => array(
                    'width'  => '200',
                    'height' => '200',
                ),
                'carousel_thumbnail_link'                => 'image',
                'carousel_lightbox'                      => 'foogallery',


                //Appearance
                'carousel_theme'                 => 'fg-light',              // options : 'fg-light', 'fg-dark', 'fg-custom'
                'carousel_border_size'           => 'fg-border-medium',       // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
                'carousel_rounded_corners'       => 'fg-round-small',                     // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
                'carousel_drop_shadow'           => 'fg-shadow-medium',      // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
                'carousel_inner_shadow'          => '',                     // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
                'carousel_loading_icon'          => 'fg-loading-bars',   // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

                'carousel_loaded_effect'         => 'fg-loaded-flip',    // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
                'carousel_instagram'             => '',

                //Captions
                'carousel_captions_type'         => '',
                'carousel_caption_title_source'  => '',
                'carousel_caption_desc_source'   => 'none',
                'carousel_captions_limit_length' => '',

                //Hover Effects
                'carousel_hover_effect_caption_visibility' => '', // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
                'carousel_hover_effect_color'              => '',                   // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
                'carousel_hover_effect_icon'               => 'fg-hover-circle-plus2',      // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
                'carousel_hover_effect_preset'             => 'fg-custom',
                'carousel_hover_effect_scale'              => 'fg-hover-semi-zoomed',    // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
                'carousel_hover_effect_transition'         => 'fg-hover-fade',      // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
                'carousel_hover_effect_type'               => 'normal',             // options : 'none', 'normal', 'preset'
                'carousel_caption_invert_color'            => '',                   // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

                //Filtering
                'carousel_filtering_type' => '',

                //Paging
                'carousel_paging_type' => '',

                //Advanced
                'carousel_state' => 'no',
                'carousel_custom_settings' => '',
                'carousel_lazyload' => '',

                //Video
                'carousel_video_autoplay'    => 'yes',
                'carousel_video_hover_icon'  => 'fg-video-default',
                'carousel_video_size'        => '640x360',
                'carousel_video_sticky_icon' => '',
            ),
        ),
    ), //Demo : Carousel
	array(
        'key'         => 'carousel2',
        'post_title'  => 'Demo : Carousel Logo Scroller',
        'post_status' => 'publish',
        'post_type'   => FOOGALLERY_CPT_GALLERY,
        'items'       => array( 'demo8', 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo6', 'demo7', 'demo9' ),
        'meta_input'  => array(
            FOOGALLERY_META_TEMPLATE => 'carousel',
            FOOGALLERY_META_SETTINGS => array(
                'foogallery_items_view'         => 'preview',
                'carousel_autoplay_interaction'=> 'pause',
                'carousel_autoplay_time' => '2',
                'carousel_centerOnClick'=> 'true',
                'carousel_maxItems'=> '7',
                'carousel_scale'=> '0',
                'carousel_gutter'=>
                    array(
                        'min'=> '20',
                        'max'=> '20',
                        'units'=> 'px',
                    ),
                'carousel_inverted'=> '',
				'carousel_show_pagination' => 'fg-carousel-hide-pagination',
				'carousel_show_progress' => 'fg-carousel-hide-progress-bar',

                //General
                'carousel_thumbnail_dimensions'  => array(
                    'width'  => '100',
                    'height' => '100',
                ),
                'carousel_thumbnail_link'                => 'none',
                'carousel_lightbox'                      => 'foogallery',


                //Appearance
                'carousel_theme'                 => 'fg-light',              // options : 'fg-light', 'fg-dark', 'fg-custom'
                'carousel_border_size'           => '',       // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
                'carousel_rounded_corners'       => 'fg-round-medium',                     // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
                'carousel_drop_shadow'           => '',      // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
                'carousel_inner_shadow'          => '',                     // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
                'carousel_loading_icon'          => 'fg-loading-bars',   // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

                'carousel_loaded_effect'         => 'fg-loaded-flip',    // options : 'fg-loaded-fade-in', 'fg-loaded-scale-up'
                'carousel_instagram'             => '',

                //Captions
                'carousel_captions_type'         => '',
                'carousel_caption_title_source'  => '',
                'carousel_caption_desc_source'   => 'none',
                'carousel_captions_limit_length' => '',
				'carousel_caption_visibility_no_hover_effect' => '',

                //Hover Effects
                'carousel_hover_effect_caption_visibility' => '', // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
                'carousel_hover_effect_color'              => '',                   // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
                'carousel_hover_effect_icon'               => '',      // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
                'carousel_hover_effect_preset'             => '',
                'carousel_hover_effect_scale'              => '',    // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
                'carousel_hover_effect_transition'         => '',      // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
                'carousel_hover_effect_type'               => 'none',             // options : 'none', 'normal', 'preset'
                'carousel_caption_invert_color'            => '',                   // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

                //Filtering
                'carousel_filtering_type' => '',

                //Paging
                'carousel_paging_type' => '',

                //Advanced
                'carousel_state' => 'no',
                'carousel_custom_settings' => '',
                'carousel_lazyload' => '',

                //Video
                'carousel_video_autoplay'    => 'yes',
                'carousel_video_hover_icon'  => 'fg-video-default',
                'carousel_video_size'        => '640x360',
                'carousel_video_sticky_icon' => '',
            ),
        ),
    ), //Demo : Carousel Logo Scroller
);