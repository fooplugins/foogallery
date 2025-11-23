<?php

return array(
	array(
		'key'         => 'grid-pro',
		'post_title'  => 'Demo : Grid PRO',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo1', 'demo3', 'demo5', 'demo7', 'demo9', 'demo4' ),
		'meta_input'  => array(
			FOOGALLERY_META_NOTICE => '<strong><i class="dashicons dashicons-star-filled"></i>This is a PRO demo gallery!</strong> It showcases the following PRO features: <ul class="ul-disc"><li>Grid PRO Layout - showcase your images in a grid layout. Clicking on the images loads the full-size image inline in a "stage".</li><li>Hover Effect Presets - hover over the images to see a preset hover effect animation in action.</li></ul>',
			FOOGALLERY_META_TEMPLATE => 'foogridpro',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'foogridpro_thumbnail_size'  => array(
					'width'  => '200',
					'height' => '200',
					'crop' => '1',
				),
				'foogridpro_thumbnail_link'        => 'image',
				'foogridpro_lightbox'              => 'foogallery',
				'foogridpro_gutter'                => '1',
				'foogridpro_alignment'             => 'fg-center',
				'foogridpro_layout'                => 'fixed',
				'foogridpro_columns'               => 'foogrid-cols-6',

				//Appearance
				'foogridpro_theme'                 => 'fg-light',
				'foogridpro_border_size'           => '',
				'foogridpro_rounded_corners'       => '',
				'foogridpro_drop_shadow'           => '',
				'foogridpro_inner_shadow'          => '',
				'foogridpro_loading_icon'          => 'fg-loading-default',

				'foogridpro_loaded_effect'         => 'fg-loaded-fade-in',
				'foogridpro_instagram'             => '',

				//Captions
				'foogridpro_captions_type'         => 'fg-captions-bottom',
				'foogridpro_caption_title_source'  => 'title',
				'foogridpro_caption_desc_source'   => 'desc',
				'foogridpro_captions_limit_length' => 'clamp',
				'foogridpro_caption_title_clamp'   => '1',
				'foogridpro_caption_desc_clamp'    => '2',
				'foogridpro_caption_alignment'     => 'fg-c-c',

				//Hover Effects
				'foogridpro_hover_effect_type'               => 'preset',
				'foogridpro_hover_effect_preset'             => 'fg-preset fg-oscar',

				//Advanced
				'foogridpro_state' => 'no',
				'foogridpro_custom_settings' => '',
				'foogridpro_lazyload' => '',

				//Video
				'foogridpro_video_autoplay'    => 'yes',
				'foogridpro_video_hover_icon'  => 'fg-video-default',
				'foogridpro_video_size'        => '640x360',
				'foogridpro_video_sticky_icon' => '',
			),
		),
	), //Demo : Grid PRO
	array(
		'key'         => 'slider-pro',
		'post_title'  => 'Demo : Slider PRO',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo2', 'demo4', 'demo6', 'demo8', 'demo10', 'demo7' , 'demo5' ),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'slider',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'slider_thumbnail_link'        => 'image',
				'slider_lightbox_fit_media'    => 'yes',
				'slider_lightbox_thumbs'       => 'right',
				'slider_lightbox_arrows'       => 'yes',

				//Appearance
				'slider_theme'                 => 'fg-dark',
				'slider_inner_shadow'          => '',
				'slider_loading_icon'          => 'fg-loading-pulse',

				'slider_loaded_effect'         => 'fg-loaded-scale-up',
				'slider_instagram'             => '',

				//Hover Effects
				'slider_hover_effect_type'               => 'normal',
				'slider_hover_effect_color'              => '',
				'slider_hover_effect_icon'               => 'fg-hover-circle-plus',
				'slider_hover_effect_scale'              => '',
				'slider_hover_effect_transition'         => 'fg-hover-fade',

				//Filtering
				'slider_filtering_type' => '',

				//Paging
				'slider_paging_type' => '',

				//Advanced
				'slider_state' => 'no',
				'slider_custom_settings' => '',
				'slider_lazyload' => '',

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
		'items'       => array( 'demo3', 'demo9', 'demo6', 'demo2', 'demo10', 'demo7' , 'demo8' , 'demo4'),
		'meta_input'  => array(
			FOOGALLERY_META_NOTICE => '<strong><i class="dashicons dashicons-star-filled"></i>This is a PRO demo gallery!</strong> It showcases the following PRO features: <ul class="ul-disc"><li>Polaroid PRO Layout - showcase your images in the iconic polaroid-style layout.</li><li>Instagram Filters - add Instagram-style image filters to your thumbnails.</li></ul>',
			FOOGALLERY_META_TEMPLATE => 'polaroid_new',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'polaroid_new_thumbnail_dimensions'  => array(
					'width' => 250,
					'height' => 200,
					'crop' => '1',
				),
				'polaroid_new_thumbnail_link'        => 'image',
				'polaroid_new_lightbox'              => 'foogallery',
				'polaroid_new_gutter'                => '20',
				'polaroid_new_alignment'             => 'fg-center',
				'polaroid_new_layout'                => 'fixed',
				'polaroid_new_columns'               => '',

				//Appearance
				'polaroid_new_theme'                 => 'fg-light',
				'polaroid_new_border_size'           => 'fg-border-medium',
				'polaroid_new_rounded_corners'       => '',
				'polaroid_new_drop_shadow'           => 'fg-shadow-large',
				'polaroid_new_inner_shadow'          => 'fg-shadow-inset-small',
				'polaroid_new_loading_icon'          => 'fg-loading-default',

				'polaroid_new_loaded_effect'         => 'fg-loaded-swing-down',
				'polaroid_new_instagram'             => 'fg-filter-brannan',

				//Captions
				'polaroid_new_caption_position'      => '',
				'polaroid_new_caption_title_source'  => 'title',
				'polaroid_new_captions_limit_length' => 'clamp',
				'polaroid_new_caption_title_clamp'   => '1',

				//Hover Effects
				'polaroid_new_hover_effect_caption_visibility' => 'fg-caption-always',
				'polaroid_new_hover_effect_type' 			   => 'normal',
				'polaroid_new_hover_effect_icon'               => 'fg-hover-zoom2',
				'polaroid_new_hover_effect_icon_size'          => '48',
				'polaroid_new_hover_effect_scale'              => 'fg-hover-zoomed',
				'polaroid_new_caption_invert_color'            => '',

				//Advanced
				'polaroid_new_state' => 'no',
				'polaroid_new_custom_settings' => '',
				'polaroid_new_lazyload' => '',

				//Video
				'polaroid_new_video_autoplay'    => 'yes',
				'polaroid_new_video_hover_icon'  => 'fg-video-default',
				'polaroid_new_video_size'        => '250x300',
				'polaroid_new_video_sticky_icon' => '',
			),
		),
	), //Demo : Polaroid PRO
	array(
		'key'         => 'spotlight-pro',
		'post_title'  => 'Demo : Spotlight PRO',
		'post_status' => 'publish',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'items'       => array( 'demo2', 'demo1', 'demo6', 'demo2', 'demo10', 'demo7' , 'demo8' , 'demo4'),
		'meta_input'  => array(
			FOOGALLERY_META_TEMPLATE => 'spotlight',
			FOOGALLERY_META_SETTINGS => array(
				'foogallery_items_view'         => 'preview',

				//General
				'spotlight_thumbnail_dimensions'  => array(
					'width' => 640,
					'height' => 500,
					'crop' => true,
				),
				'spotlight_thumbnail_link'        => 'none',
				'spotlight_lightbox'              => 'none',
				'spotlight_alignment' 			  => 'fg-center',
				'spotlight_arrow_icon' 			  => 'fg-nav-icon-line',
				'spotlight_border_size' 		  => 'fg-border-thin',
				'spotlight_dots_position' 		  => 'fg-dots-center',

				//Appearance
				'spotlight_theme'                 => 'fg-light',
				'spotlight_rounded_corners'       => 'fg-round-large',
				'spotlight_drop_shadow'           => '',
				'spotlight_inner_shadow'          => '',
				'spotlight_loading_icon'          => 'fg-loading-default',

				'spotlight_loaded_effect'         => 'fg-loaded-fade-in',
				'spotlight_instagram'             => '',

				//Captions
				'spotlight_captions_type'		  => '',
				'spotlight_caption_title_source'  => 'none',
				'spotlight_caption_desc_source'   => 'none',

				//Hover Effects
				'spotlight_hover_effect_type'     => 'none',

				//Filtering
				'spotlight_filtering_type' => '',

				//Paging
				'spotlight_paging_type' => '',

				//Advanced
				'spotlight_state' => 'no',
				'spotlight_custom_settings' => '',
				'spotlight_lazyload' => '',
			),
			FOOGALLERY_META_NOTICE => '<strong><i class="dashicons dashicons-star-filled"></i>This is a PRO demo gallery!</strong><br />It showcases the new Spotlight PRO gallery layout, which is meant to spotlight your images in a modern carousel without the need for a lightbox or fancy hover effects.',
		),
	), //Demo : Spotlight PRO
);
