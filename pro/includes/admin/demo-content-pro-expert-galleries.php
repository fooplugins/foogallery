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
				'default_captions_limit_length' => 'clamp',
				'default_caption_title_clamp'   => '1',
				'default_caption_desc_clamp'    => '2',
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
	), //Demo : Video Gallery
	array(
		'key'         => 'tag-filters',
		'post_title'  => 'Demo : Tag Filters Showcase',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo2', 'demo5', 'video-sunrise', 'demo1', 'video-mountain', 'video-lavender', 'demo7', 'demo8', 'demo9', 'video-ocean' ),
		'meta_input'  => array(
			FOOGALLERY_META_NOTICE => '<strong><i class="dashicons dashicons-star-filled"></i>This is a PRO demo gallery!</strong> It showcases the following PRO features: <ul class="ul-disc"><li>Tag filtering - tag filtering in the "Pill Block" style.</li><li>Gallery Search - the search box is shown at the top merged with the tag filters.</li><li>Video galleries - you can mix and match video and image in the same gallery.</li></ul>',
			'_foogallery_video_count' => 4,
			FOOGALLERY_META_TEMPLATE => 'default',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				'default_thumbnail_dimensions'  => array(
					'width'  => '150',
					'height' => '150',
				),
				'default_thumbnail_link'        => 'image',
				'default_lightbox'              => 'foogallery',
				'default_spacing'               => '10',
				'default_alignment'             => 'fg-center',

				//Appearance
				'default_theme'                 => 'fg-dark',
				'default_border_size'           => 'fg-border-thin',
				'default_rounded_corners'       => 'fg-round-small',
				'default_drop_shadow'           => 'fg-shadow-medium',
				'default_inner_shadow'          => '',
				'default_loading_icon'          => 'fg-loading-pulse',
				'default_loaded_effect'         => 'fg-loaded-fade-in',
				'default_instagram'             => '',

				//Captions
				'default_captions_type'         => '',
				'default_caption_title_source'  => 'none',
				'default_caption_desc_source'   => 'none',
				'default_captions_limit_length' => 'clamp',
				'default_caption_title_clamp'   => '1',
				'default_caption_desc_clamp'    => '2',
				'default_caption_alignment'     => 'fg-c-c',
				'default_caption_invert_color'  => 'fg-transparent-overlays',

				//Hover Effects - Showcasing PRO presets
				'default_hover_effect_caption_visibility' => '',
				'default_hover_effect_color'              => '',
				'default_hover_effect_icon'               => 'fg-hover-zoom5',
				'default_hover_effect_preset'             => '',
				'default_hover_effect_scale'              => 'fg-hover-semi-zoomed',
				'default_hover_effect_transition'         => 'fg-hover-instant',
                'default_hover_effect_icon_size'          => '48',
				'default_hover_effect_type'               => 'normal',

				//Filtering
				'default_filtering_theme' => 'fg-dark',
				'default_filtering_type'  => 'simple',
                'default_filtering_limit' => 8,
                'default_filtering_style' => 'pill-block',
				'default_filtering_hideall' => 'hide',
				'default_filtering_autoSelected' => 'true',
				'default_filtering_search' => 'true',
				'default_filtering_search_position' => 'after-merged',
				'default_filtering_sort' => 'count_inverse',

				//Advanced
				'default_state' => 'no',
				'default_custom_settings' => '',
				'default_lazyload' => '',

				//Video
				'default_video_autoplay'    => 'yes',
				'default_video_hover_icon'  => 'fg-video-1',
				'default_video_size'        => '1280x720',
				'default_video_sticky_icon' => 'fg-video-sticky',
				'default_video_enabled'     => '',
				'default_video_icon_size'   => '',

				//Lightbox
				'default_lightbox_thumbs_bestfit' => 'yes',
			),
		),
	), //Demo : Tag Filters Demo
);