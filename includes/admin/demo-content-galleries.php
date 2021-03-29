<?php

return array(
	array(
		'key'         => 'responsive',
		'post_title'  => 'Demo Gallery : Responsive',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'pixabay1', 'pixabay2', 'pixabay3', 'pixabay4', 'pixabay5', 'pixabay6' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'default',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'default_thumbnail_dimensions'  => array(
					'width'  => '200',
					'height' => '200',
				),
				'default_thumbnail_link'        => 'image',
				'default_lightbox'              => 'foogallery',
				'default_spacing'               => 'fg-gutter-10',
				'default_alignment'             => 'fg-center',
				//'default_layout'                => 'fixed',


				//Appearance
				'default_theme'                 => 'fg-dark',               // options : 'fg-light', 'fg-dark', 'fg-custom'
				'default_border_size'           => 'fg-border-thin',        // options : '', 'fg-border-thin', 'fg-border-medium', 'fg-border-thick'
				'default_rounded_corners'       => '',                      // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'default_drop_shadow'           => 'fg-shadow-outline',     // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
				'default_inner_shadow'          => '',                      // options : '', 'fg-shadow-inset-small', 'fg-shadow-inset-medium', 'fg-shadow-inset-large'
				'default_loading_icon'          => 'fg-loading-default',    // options : '', 'fg-loading-default', 'fg-loading-bars', 'fg-loading-dots', 'fg-loading-partial', 'fg-loading-pulse', 'fg-loading-trail',

				'default_loaded_effect'         => 'fg-loaded-fade-in',     // options : 'fg-loaded-fade-in',
				'default_instagram'             => '',

				//Captions
				'default_captions_type'         => '',
				'default_caption_title_source'  => '',
				'default_caption_desc_source'   => '',
				'default_captions_limit_length' => '',



				//Hover Effects
				'default_gutter_width'                    => '10',
				'default_hover_effect_caption_visibility' => 'fg-caption-hover',  // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'default_hover_effect_color'              => '',                  // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'default_hover_effect_icon'               => 'fg-hover-zoom',     // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'default_hover_effect_preset'             => 'fg-custom',
				'default_hover_effect_scale'              => 'fg-hover-zoomed',   // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'default_hover_effect_transition'         => 'fg-hover-fade',     // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'default_hover_effect_type'               => 'normal',            // options : 'none', 'normal', 'preset'
				'default_caption_invert_color'            => '',                  // options : '', 'fg-light-overlays', 'fg-transparent-overlays'


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
	),
	array(
		'key'         => 'image-viewer',
		'post_title'  => 'Demo Gallery : Image Viewer',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'pixabay1', 'pixabay3', 'pixabay5', 'pixabay10', 'pixabay11', 'pixabay12' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'image-viewer',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'image-viewer_thumbnail_dimensions'  => array(
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
				'image-viewer_rounded_corners'       => 'fg-round-medium',       // options : '', 'fg-round-small', 'fg-round-medium', 'fg-round-large', 'fg-round-full'
				'image-viewer_drop_shadow'           => 'fg-shadow-small',       // options : '', 'fg-shadow-outline', 'fg-shadow-small', 'fg-shadow-medium', 'fg-shadow-large'
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
				'image-viewer_hover_effect_icon'               => 'fg-hover-circle-plus', // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'image-viewer_hover_effect_preset'             => 'fg-custom',
				'image-viewer_hover_effect_scale'              => 'fg-hover-zoomed',   // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
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
	),
	array(
		'key'         => 'justified',
		'post_title'  => 'Demo Gallery : Justified',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'pixabay1', 'pixabay2', 'pixabay3', 'pixabay4', 'pixabay5', 'pixabay6', 'pixabay7', 'pixabay8', 'pixabay9', 'pixabay10', 'pixabay11', 'pixabay12' ),
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
				'justified_captions_limit_length' => '',
				'justified_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'justified_hover_effect_caption_visibility' => 'fg-caption-always',  // options : '', 'fg-caption-hover', 'fg-caption-always', 'fg-captions-bottom'
				'justified_hover_effect_color'              => 'fg-hover-colorize', // options : '', 'fg-hover-colorize', 'fg-hover-grayscale
				'justified_hover_effect_icon'               => '', // options : 'fg-hover-zoom', 'fg-hover-zoom2', 'fg-hover-zoom3', 'fg-hover-plus', 'fg-hover-circle-plus', 'fg-hover-eye', 'fg-hover-external'
				'justified_hover_effect_preset'             => 'fg-custom',
				'justified_hover_effect_scale'              => 'fg-hover-zoomed',   // options : '', 'fg-hover-scale', 'fg-hover-zoomed'
				'justified_hover_effect_transition'         => 'fg-hover-instant',     // options : 'fg-hover-instant', 'fg-hover-fade', 'fg-hover-slide-up', 'fg-hover-slide-down', 'fg-hover-slide-left', 'fg-hover-slide-right', 'fg-hover-push'
				'justified_hover_effect_type'               => 'normal',            // options : 'none', 'normal', 'preset'
				'justified_caption_invert_color'            => 'fg-transparent-overlays',                  // options : '', 'fg-light-overlays', 'fg-transparent-overlays'

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
	),
);