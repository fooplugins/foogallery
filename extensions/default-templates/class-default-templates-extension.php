<?php
if ( !class_exists( 'FooGallery_Default_Templates_Extension' ) ) {

	define('FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Default_Templates_Extension {

		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_default_templates' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
		}

		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		function add_default_templates( $gallery_templates ) {

			$gallery_templates[] = array(
				'slug'        => 'default',
				'name'        => __( 'Responsive Image Gallery', 'foogallery'),
				'description' => __( 'The default image gallery template : clean and responsive and looks good in any theme.', 'foogallery'),
				'author'      => 'FooPlugins',
				'author_url'  => 'http://fooplugins.com',
				'demo_url'    => 'http://fooplugins.com/plugins/foogallery',
				'thumbnail'   => FOOGALLERY_URL . 'templates/default/thumb.png',
				'preview_css' => FOOGALLERY_URL . 'extensions/default-templates/css/gallery-default.css',
				'admin_js'	  => FOOGALLERY_URL . 'extensions/default-templates/js/admin-gallery-default.js',
				'fields'	  => array(
					array(
						'id'      => 'thumbnail_size',
						'title'   => __('Thumbnail Size', 'foogallery'),
						'desc'    => __('Choose the size of your thumbnails.', 'foogallery'),
						'type'    => 'thumb_size',
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery'),
						'default' => 'image' ,
						'type'    => 'thumb_link',
						'spacer'  => '<span class="spacer"></span>',
						'desc'	  => __('You can choose to either link each thumbnail to the full size image or to the image\'s attachment page.', 'foogallery')
					),
					array(
						'id'      => 'lightbox',
						'title'   => __('Lightbox', 'foogallery'),
						'desc'    => __('Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery'),
						'type'    => 'lightbox',
					),
					array(
						'id'      => 'spacing',
						'title'   => __('Thumbnail Spacing', 'foogallery'),
						'desc'    => __('The spacing or gap between images in the gallery.', 'foogallery'),
						'type'    => 'select',
						'choices' => array(
							'spacing-width-5' => __( '5 pixels', 'foogallery' ),
							'spacing-width-10' => __( '10 pixels', 'foogallery' ),
							'spacing-width-15' => __( '15 pixels', 'foogallery' ),
							'spacing-width-20' => __( '20 pixels', 'foogallery' ),
							'spacing-width-25' => __( '25 pixels', 'foogallery' )
						)
					),
					array(
						'id'      => 'border-style',
						'title'   => __('Border Style', 'foogallery'),
						'desc'    => __('The border style for each thumbnail in the gallery.', 'foogallery'),
						'type'    => 'icon',
						'default' => 'border-style-square-white',
						'choices' => array(
							'border-style-square-white' => array('label' => 'Square white border with shadow', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-square-white.png'),
							'border-style-circle-white' => array('label' => 'Circular white border with shadow', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-circle-white.png'),
							'border-style-square-black' => array('label' => 'Square Black', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-square-black.png'),
							'border-style-circle-black' => array('label' => 'Circular Black', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-circle-black.png'),
							'border-style-inset' => array('label' => 'Square Inset', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-square-inset.png'),
							'border-style-rounded' => array('label' => 'Plain Rounded', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-plain-rounded.png'),
							'' => array('label' => 'Plain', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-none.png'),
						)
					),
					array(
						'id'      => 'hover-effect',
						'title'   => __('Hover Effect', 'foogallery'),
						'desc'    => __('A hover effect is shown when you hover over each thumbnail.', 'foogallery'),
						'type'    => 'icon',
						'default' => 'hover-effect-zoom',
						'choices' => array(
							'hover-effect-zoom' => array('label' => 'Zoom', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-zoom.png'),
							'hover-effect-zoom2' => array('label' => 'Zoom 2', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-zoom2.png'),
							'hover-effect-zoom3' => array('label' => 'Zoom 3', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-zoom3.png'),
							'hover-effect-plus' => array('label' => 'Plus', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-plus.png'),
							'hover-effect-circle-plus' => array('label' => 'Cirlce Plus', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-circle-plus.png'),
							'hover-effect-eye' => array('label' => 'Eye', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-eye.png'),
							'' => array('label' => 'None', 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-none.png'),
						)
					)
				)
			);

			$gallery_templates[] = array(
				'slug'        => 'masonry',
				'name'        => __( 'Masonry Image Gallery', 'foogallery'),
				'description' => __( 'A masonry-style image gallery template', 'foogallery'),
				'author'      => 'FooPlugins',
				'author_url'  => 'http://fooplugins.com',
				'demo_url'    => 'http://fooplugins.com/plugins/foogallery',
				'thumbnail'   => FOOGALLERY_URL . 'templates/masonry/thumb.png',
				'fields'	  => array(
					array(
						'id'      => 'thumbnail_size',
						'title'   => __('Thumbnail Size', 'foogallery'),
						'desc'    => __('Choose the size of your thumbnails.', 'foogallery'),
						'type'    => 'thumb_size',
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery'),
						'default' => 'image' ,
						'type'    => 'thumb_link',
						'spacer'  => '<span class="spacer"></span>',
						'desc'	  => __('You can choose to either link each thumbnail to the full size image or to the image\'s attachment page.', 'foogallery')
					),
					array(
						'id'      => 'lightbox',
						'title'   => __('Lightbox', 'foogallery'),
						'desc'    => __('Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery'),
						'type'    => 'lightbox',
					)
				)
			);

			return $gallery_templates;
		}

	}
}