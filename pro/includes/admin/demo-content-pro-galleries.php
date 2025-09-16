<?php

return array(
	array(
		'key'         => 'grid-pro',
		'post_title'  => 'Demo : Grid PRO',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo6' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'foogridpro',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'foogrid_thumbnail_dimensions'  => array(
					'width'  => '300',
					'height' => '200',
				),
				'foogrid_thumbnail_link'        => 'image',
				'foogrid_lightbox'              => 'foogallery',
				'foogrid_spacing'               => 'fg-gutter-10',
				'foogrid_alignment'             => 'fg-center',
				'foogrid_layout'                => 'fixed',
				'foogrid_columns'               => '3',

				//Appearance
				'foogrid_theme'                 => 'fg-light',
				'foogrid_border_size'           => 'fg-border-thin',
				'foogrid_rounded_corners'       => 'fg-round-small',
				'foogrid_drop_shadow'           => 'fg-shadow-small',
				'foogrid_inner_shadow'          => '',
				'foogrid_loading_icon'          => 'fg-loading-default',

				'foogrid_loaded_effect'         => 'fg-loaded-fade-in',
				'foogrid_instagram'             => '',

				//Captions
				'foogrid_captions_type'         => 'fg-captions-bottom',
				'foogrid_caption_title_source'  => 'title',
				'foogrid_caption_desc_source'   => 'desc',
				'foogrid_captions_limit_length' => 'yes',
				'foogrid_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'foogrid_hover_effect_caption_visibility' => 'fg-caption-hover',
				'foogrid_hover_effect_color'              => 'fg-hover-colorize',
				'foogrid_hover_effect_icon'               => 'fg-hover-zoom',
				'foogrid_hover_effect_preset'             => 'fg-custom',
				'foogrid_hover_effect_scale'              => 'fg-hover-zoomed',
				'foogrid_hover_effect_transition'         => 'fg-hover-fade',
				'foogrid_hover_effect_type'               => 'normal',
				'foogrid_caption_invert_color'            => 'fg-light-overlays',

				//Filtering
				'foogrid_filtering_type' => 'tags',

				//Paging
				'foogrid_paging_type' => 'load_more',

				//Advanced
				'foogrid_state' => 'no',
				'foogrid_custom_settings' => '',
				'foogrid_lazyload' => 'yes',

				//Video
				'foogrid_video_autoplay'    => 'yes',
				'foogrid_video_hover_icon'  => 'fg-video-default',
				'foogrid_video_size'        => '640x360',
				'foogrid_video_sticky_icon' => '',
			),
		),
	), //Demo : Grid PRO
	array(
		'key'         => 'slider-pro',
		'post_title'  => 'Demo : Slider PRO',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo6' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'slider',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'slider_thumbnail_dimensions'  => array(
					'width'  => '800',
					'height' => '400',
				),
				'slider_thumbnail_link'        => 'image',
				'slider_lightbox'              => 'foogallery',
				'slider_autoplay'              => 'yes',
				'slider_autoplay_time'         => '5000',
				'slider_autoplay_hover'        => 'pause',
				'slider_show_nav'              => 'yes',
				'slider_show_thumbnails'       => 'yes',
				'slider_show_arrows'           => 'yes',

				//Appearance
				'slider_theme'                 => 'fg-dark',
				'slider_border_size'           => 'fg-border-medium',
				'slider_rounded_corners'       => 'fg-round-medium',
				'slider_drop_shadow'           => 'fg-shadow-large',
				'slider_inner_shadow'          => '',
				'slider_loading_icon'          => 'fg-loading-pulse',

				'slider_loaded_effect'         => 'fg-loaded-scale-up',
				'slider_instagram'             => '',

				//Captions
				'slider_captions_type'         => 'fg-captions-bottom',
				'slider_caption_title_source'  => 'title',
				'slider_caption_desc_source'   => 'desc',
				'slider_captions_limit_length' => 'yes',
				'slider_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'slider_hover_effect_caption_visibility' => 'fg-caption-always',
				'slider_hover_effect_color'              => 'fg-hover-grayscale',
				'slider_hover_effect_icon'               => 'fg-hover-circle-plus',
				'slider_hover_effect_preset'             => 'fg-custom',
				'slider_hover_effect_scale'              => 'fg-hover-zoomed',
				'slider_hover_effect_transition'         => 'fg-hover-fade',
				'slider_hover_effect_type'               => 'normal',
				'slider_caption_invert_color'            => 'fg-transparent-overlays',

				//Filtering
				'slider_filtering_type' => '',

				//Paging
				'slider_paging_type' => '',

				//Advanced
				'slider_state' => 'no',
				'slider_custom_settings' => '',
				'slider_lazyload' => 'yes',

				//Video
				'slider_video_autoplay'    => 'yes',
				'slider_video_hover_icon'  => 'fg-video-default',
				'slider_video_size'        => '800x400',
				'slider_video_sticky_icon' => '',
			),
		),
	), //Demo : Slider PRO
	array(
		'key'         => 'polaroid-pro',
		'post_title'  => 'Demo : Polaroid PRO',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo6' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'polaroid_new',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'polaroid_thumbnail_dimensions'  => array(
					'width'  => '250',
					'height' => '300',
				),
				'polaroid_thumbnail_link'        => 'image',
				'polaroid_lightbox'              => 'foogallery',
				'polaroid_spacing'               => 'fg-gutter-20',
				'polaroid_alignment'             => 'fg-center',
				'polaroid_layout'                => 'fixed',
				'polaroid_columns'               => '4',

				//Appearance
				'polaroid_theme'                 => 'fg-light',
				'polaroid_border_size'           => '',
				'polaroid_rounded_corners'       => '',
				'polaroid_drop_shadow'           => 'fg-shadow-large',
				'polaroid_inner_shadow'          => 'fg-shadow-inset-small',
				'polaroid_loading_icon'          => 'fg-loading-default',

				'polaroid_loaded_effect'         => 'fg-loaded-swing-down',
				'polaroid_instagram'             => '',

				//Captions
				'polaroid_captions_type'         => 'fg-captions-bottom',
				'polaroid_caption_title_source'  => 'title',
				'polaroid_caption_desc_source'   => 'desc',
				'polaroid_captions_limit_length' => 'yes',
				'polaroid_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'polaroid_hover_effect_caption_visibility' => 'fg-caption-hover',
				'polaroid_hover_effect_color'              => '',
				'polaroid_hover_effect_icon'               => 'fg-hover-zoom2',
				'polaroid_hover_effect_preset'             => 'fg-custom',
				'polaroid_hover_effect_scale'              => 'fg-hover-zoomed',
				'polaroid_hover_effect_transition'         => 'fg-hover-slide-up',
				'polaroid_hover_effect_type'               => 'normal',
				'polaroid_caption_invert_color'            => '',

				//Filtering
				'polaroid_filtering_type' => 'tags',

				//Paging
				'polaroid_paging_type' => 'load_more',

				//Advanced
				'polaroid_state' => 'no',
				'polaroid_custom_settings' => '',
				'polaroid_lazyload' => 'yes',

				//Video
				'polaroid_video_autoplay'    => 'yes',
				'polaroid_video_hover_icon'  => 'fg-video-default',
				'polaroid_video_size'        => '250x300',
				'polaroid_video_sticky_icon' => '',
			),
		),
	), //Demo : Polaroid PRO
	array(
		'key'         => 'hover-effect-presets',
		'post_title'  => 'Demo : Hover Effect Presets Showcase',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo6' ),
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
				'default_spacing'               => 'fg-gutter-15',
				'default_alignment'             => 'fg-center',
				'default_layout'                => 'fixed',
				'default_columns'               => '4',

				//Appearance
				'default_theme'                 => 'fg-light',
				'default_border_size'           => 'fg-border-thin',
				'default_rounded_corners'       => 'fg-round-small',
				'default_drop_shadow'           => 'fg-shadow-small',
				'default_inner_shadow'          => '',
				'default_loading_icon'          => 'fg-loading-default',

				'default_loaded_effect'         => 'fg-loaded-fade-in',
				'default_instagram'             => '',

				//Captions
				'default_captions_type'         => 'fg-captions-bottom',
				'default_caption_title_source'  => 'title',
				'default_caption_desc_source'   => 'desc',
				'default_captions_limit_length' => 'yes',
				'default_caption_alignment'     => 'fg-c-c',

				//Hover Effects - Showcasing PRO presets
				'default_hover_effect_caption_visibility' => 'fg-caption-hover',
				'default_hover_effect_color'              => 'fg-hover-colorize',
				'default_hover_effect_icon'               => 'fg-hover-zoom',
				'default_hover_effect_preset'             => 'fg-preset-1', // PRO preset
				'default_hover_effect_scale'              => 'fg-hover-zoomed',
				'default_hover_effect_transition'         => 'fg-hover-fade',
				'default_hover_effect_type'               => 'preset', // Using preset instead of custom
				'default_caption_invert_color'            => 'fg-light-overlays',

				//Filtering
				'default_filtering_type' => 'tags',

				//Paging
				'default_paging_type' => 'load_more',

				//Advanced
				'default_state' => 'no',
				'default_custom_settings' => '',
				'default_lazyload' => 'yes',

				//Video
				'default_video_autoplay'    => 'yes',
				'default_video_hover_icon'  => 'fg-video-default',
				'default_video_size'        => '200x200',
				'default_video_sticky_icon' => '',
			),
		),
	), //Demo : Hover Effect Presets Showcase
	array(
		'key'         => 'instagram-filters',
		'post_title'  => 'Demo : Instagram Filters Showcase',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo1', 'demo2', 'demo3', 'demo4', 'demo5', 'demo6' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'default',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'default_thumbnail_dimensions'  => array(
					'width'  => '250',
					'height' => '250',
				),
				'default_thumbnail_link'        => 'image',
				'default_lightbox'              => 'foogallery',
				'default_spacing'               => 'fg-gutter-10',
				'default_alignment'             => 'fg-center',
				'default_layout'                => 'fixed',
				'default_columns'               => '4',

				//Appearance
				'default_theme'                 => 'fg-light',
				'default_border_size'           => 'fg-border-thin',
				'default_rounded_corners'       => 'fg-round-medium',
				'default_drop_shadow'           => 'fg-shadow-medium',
				'default_inner_shadow'          => '',
				'default_loading_icon'          => 'fg-loading-pulse',

				'default_loaded_effect'         => 'fg-loaded-fade-in',
				'default_instagram'             => 'sepia', // PRO Instagram filter

				//Captions
				'default_captions_type'         => 'fg-captions-bottom',
				'default_caption_title_source'  => 'title',
				'default_caption_desc_source'   => 'desc',
				'default_captions_limit_length' => 'yes',
				'default_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'default_hover_effect_caption_visibility' => 'fg-caption-hover',
				'default_hover_effect_color'              => 'fg-hover-grayscale',
				'default_hover_effect_icon'               => 'fg-hover-plus',
				'default_hover_effect_preset'             => 'fg-custom',
				'default_hover_effect_scale'              => 'fg-hover-zoomed',
				'default_hover_effect_transition'         => 'fg-hover-fade',
				'default_hover_effect_type'               => 'normal',
				'default_caption_invert_color'            => 'fg-transparent-overlays',

				//Filtering
				'default_filtering_type' => 'tags',

				//Paging
				'default_paging_type' => 'load_more',

				//Advanced
				'default_state' => 'no',
				'default_custom_settings' => '',
				'default_lazyload' => 'yes',

				//Video
				'default_video_autoplay'    => 'yes',
				'default_video_hover_icon'  => 'fg-video-default',
				'default_video_size'        => '250x250',
				'default_video_sticky_icon' => '',
			),
		),
	), //Demo : Instagram Filters Showcase
);
