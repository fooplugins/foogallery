<?php

return array(
	array(
		'key'         => 'video',
		'post_title'  => 'Demo : Video Gallery',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'video-sunrise', 'video-fire', 'video-forest', 'video-mountain', 'video-road', 'video-lavender', 'video-ocean', 'video-turtle' ),
		'meta_input'  => array(
            FOOGALLERY_META_NOTICE => '<strong><i class="dashicons dashicons-star-filled"></i>This is a PRO demo gallery!</strong> It showcases the following PRO features: <ul class="ul-disc"><li>Video galleries - image thumbs are hosted on your site, while the large video .MP4 files are hosted on an external CDN.</li><li>Filtering - tag filtering in the "Button Block" style.</li></ul>',
			FOOGALLERY_META_TEMPLATE => 'default',
            '_foogallery_video_count' => 8,
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'default_thumbnail_dimensions'  => array(
					'width'  => '300',
					'height' => '200',
				),
				'default_thumbnail_link'        => 'image',
				'default_lightbox'              => 'foogallery',
				'default_spacing'               => '15',
				'default_alignment'             => 'fg-center',

				//Appearance
				'default_theme'                 => 'fg-light',
				'default_border_size'           => 'fg-border-thin',
				'default_rounded_corners'       => 'fg-round-small',
				'default_drop_shadow'           => 'fg-shadow-outline',
				'default_inner_shadow'          => '',
				'default_loading_icon'          => 'fg-loading-default',

				'default_loaded_effect'         => 'fg-loaded-fade-in',
				'default_instagram'             => '',

				//Captions
				'default_captions_type'         => '',
				'default_caption_title_source'  => '',
				'default_caption_desc_source'   => 'none',
				'default_captions_limit_length' => '',
				'default_caption_alignment'     => 'fg-c-c',

				//Hover Effects - Showcasing PRO presets
				'default_hover_effect_caption_visibility' => 'fg-caption-hover',
				'default_hover_effect_color'              => '',
				'default_hover_effect_icon'               => '',
				'default_hover_effect_preset'             => '',
				'default_hover_effect_scale'              => 'fg-hover-semi-zoomed',
				'default_hover_effect_transition'         => 'fg-hover-instant',
                'default_hover_effect_icon_size'          => '48',
				'default_hover_effect_type'               => 'normal',

				//Filtering
				'default_filtering_type'  => 'simple',
                'default_filtering_limit' => 10,
                'default_filtering_style' => 'button-block',

				//Advanced
				'default_state' => 'no',
				'default_custom_settings' => '',
				'default_lazyload' => '',

				//Video
				'default_video_autoplay'    => 'yes',
				'default_video_hover_icon'  => 'fg-video-default',
				'default_video_size'        => '1280x720',
				'default_video_sticky_icon' => '',
				'default_video_enabled'     => '',
				'default_video_icon_size'   => '',
			),
		),
	), //Demo : Hover Effect Presets Showcase
	array(
		'key'         => 'instagram-filters',
		'post_title'  => 'Demo : Instagram Filters Showcase',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo5', 'demo6', 'demo7', 'demo8', 'demo9', 'demo10' , 'demo3' , 'demo2' , 'demo1' , 'demo4'),
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
				'default_lazyload' => '',

				//Video
				'default_video_autoplay'    => 'yes',
				'default_video_hover_icon'  => 'fg-video-default',
				'default_video_size'        => '250x250',
				'default_video_sticky_icon' => '',
			),
		),
	), //Demo : Instagram Filters Showcase
);