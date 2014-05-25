<?php
if ( !class_exists( 'FooGallery_Default_Templates_Extension' ) ) {

	class FooGallery_Default_Templates_Extension {

		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_default_templates' ) );
		}

		function add_default_templates( $gallery_templates ) {

			$gallery_templates[] = array(
				'key'         => 'default',
				'name'        => __( 'Responsive Image Gallery', 'foogallery'),
				'description' => __( 'The default image gallery template : clean and responsive and looks good in any theme.', 'foogallery'),
				'author'      => 'FooPlugins',
				'author_url'  => 'http://fooplugins.com',
				'demo_url'    => 'http://fooplugins.com/plugins/foogallery',
				'thumbnail'   => FOOGALLERY_URL . 'templates/default/thumb.png',
				'fields'	  => array(
					array(
						'id'      => 'link_to_image',
						'title'   => __('Link To Image', 'foogallery'),
						'desc'    => __('Should the gallery thumbnails link to the full size images. If not set, then the images will link to the attachment page.', 'foogallery'),
						'default' => 'on' ,
						'type'    => 'checkbox',
						//'section' => __('Responsive Image Gallery Settings', 'foogallery'),
					),
					array(
						'id'      => 'lightbox',
						'title'   => __('Lightbox', 'foogallery'),
						'desc'    => __('Choose which lightbox you want to use in the gallery.', 'foogallery'),
						'type'    => 'select',
					),
				)
			);

			$gallery_templates[] = array(
				'key'         => 'masonry',
				'name'        => __( 'Masonry Image Gallery', 'foogallery'),
				'description' => __( 'A masonry-style image gallery template', 'foogallery'),
				'author'      => 'FooPlugins',
				'author_url'  => 'http://fooplugins.com',
				'demo_url'    => 'http://fooplugins.com/plugins/foogallery',
				'thumbnail'   => FOOGALLERY_URL . 'templates/masonry/thumb.png',
				'fields'	  => array(
					array(
						'id'      => 'link_to_image',
						'title'   => __('Link To Image2', 'foogallery'),
						'desc'    => __('Should the gallery thumbnails link to the full size images. If not set, then the images will link to the attachment page.', 'foogallery'),
						'default' => 'on' ,
						'type'    => 'checkbox',
						//'section' => __('Responsive Image Gallery Settings', 'foogallery'),
					),
					array(
						'id'      => 'lightbox',
						'title'   => __('Lightbox2', 'foogallery'),
						'desc'    => __('Choose which lightbox you want to use in the gallery.', 'foogallery'),
						'type'    => 'select',
					),
				)
			);

			return $gallery_templates;
		}

	}
}