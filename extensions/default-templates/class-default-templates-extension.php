<?php
if ( !class_exists( 'FooGallery_Default_Templates_Extension' ) ) {

	define('FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Default_Templates_Extension {

		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_default_templates' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_thumbnail_preview' ), 10, 3 );
		}

		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		function add_default_templates( $gallery_templates ) {

			$gallery_templates[] = array(
				'slug'        => 'default',
				'name'        => __( 'Responsive Image Gallery', 'foogallery'),
				'preview_css' => FOOGALLERY_URL . 'extensions/default-templates/css/gallery-default.css',
				'admin_js'	  => FOOGALLERY_URL . 'extensions/default-templates/js/admin-gallery-default.js',
				'fields'	  => array(
					array(
						'id'      => 'thumbnail_size',
						'title'   => __('Thumbnail Size', 'foogallery'),
						'desc'    => __('Choose the size of your thumbnails.', 'foogallery'),
						'type'    => 'thumb_size',
						'default' => array(
							'width' => get_option( 'thumbnail_size_w' ),
							'height' => get_option( 'thumbnail_size_h' ),
							'crop' => true
						)
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery'),
						'default' => 'image',
						'type'    => 'thumb_link',
						'spacer'  => '<span class="spacer"></span>',
						'desc'	  => __('You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery')
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
						'default' => 'spacing-width-10',
						'choices' => array(
							'spacing-width-5' => __( '5 pixels', 'foogallery' ),
							'spacing-width-10' => __( '10 pixels', 'foogallery' ),
							'spacing-width-15' => __( '15 pixels', 'foogallery' ),
							'spacing-width-20' => __( '20 pixels', 'foogallery' ),
							'spacing-width-25' => __( '25 pixels', 'foogallery' )
						)
					),
					array(
						'id'      => 'alignment',
						'title'   => __('Gallery Alignment', 'foogallery'),
						'desc'    => __('The horizontal alignment of the thumbnails inside the gallery.', 'foogallery'),
						'default' => 'alignment-center',
						'type'    => 'select',
						'choices' => array(
							'alignment-left' => __( 'Left', 'foogallery' ),
							'alignment-center' => __( 'Center', 'foogallery' ),
							'alignment-right' => __( 'Right', 'foogallery' )
						)
					),
					array(
						'id'      => 'border-style',
						'title'   => __('Border Style', 'foogallery'),
						'desc'    => __('The border style for each thumbnail in the gallery.', 'foogallery'),
						'type'    => 'icon',
						'default' => 'border-style-square-white',
						'choices' => array(
							'border-style-square-white' => array('label' => __('Square white border with shadow' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-square-white.png'),
							'border-style-circle-white' => array('label' => __('Circular white border with shadow' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-circle-white.png'),
							'border-style-square-black' => array('label' => __('Square Black' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-square-black.png'),
							'border-style-circle-black' => array('label' => __('Circular Black' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-circle-black.png'),
							'border-style-inset' => array('label' => __('Square Inset' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-square-inset.png'),
							'border-style-rounded' => array('label' => __('Plain Rounded' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-plain-rounded.png'),
							'' => array('label' => __('Plain' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/border-style-icon-none.png'),
						)
					),
					array(
						'id'      => 'hover-effect',
						'title'   => __('Hover Effect', 'foogallery'),
						'desc'    => __('A hover effect is shown when you hover over each thumbnail.', 'foogallery'),
						'type'    => 'icon',
						'default' => 'hover-effect-zoom',
						'choices' => array(
							'hover-effect-zoom' => array('label' => __('Zoom' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-zoom.png'),
							'hover-effect-zoom2' => array('label' => __('Zoom 2' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-zoom2.png'),
							'hover-effect-zoom3' => array('label' => __('Zoom 3' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-zoom3.png'),
							'hover-effect-plus' => array('label' => __('Plus' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-plus.png'),
							'hover-effect-circle-plus' => array('label' => __('Cirlce Plus' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-circle-plus.png'),
							'hover-effect-eye' => array('label' => __('Eye' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-eye.png'),
							'' => array('label' => __('None' ,'foogallery'), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'assets/hover-effect-icon-none.png'),
						)
					),
					array(
						'id' => 'thumb_preview',
						'title' => __('Thumbnail Preview', 'foogallery'),
						'desc' => __('This is what your thumbnails will look like on the frontend', 'foogallery'),
						'type' => 'thumb_preview'
					)
				)
			);

			$gallery_templates[] = array(
				'slug'        => 'masonry',
				'name'        => __( 'Masonry Image Gallery', 'foogallery'),
				'fields'	  => array(
					array(
						'id'      => 'thumbnail_size',
						'title'   => __('Thumbnail Size', 'foogallery'),
						'desc'    => __('Choose the size of your thumbnails. Thumbnails will be generated on the fly and cached once generated.', 'foogallery'),
						'type'    => 'thumb_size',
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery'),
						'default' => 'image' ,
						'type'    => 'thumb_link',
						'spacer'  => '<span class="spacer"></span>',
						'desc'	  => __('You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery')
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

		/**
		 * Renders the thumbnail preview field
		 *
		 * @param $field array
		 * @param $gallery FooGallery
		 * @param $template array
		 */
		function render_thumbnail_preview( $field, $gallery, $template ) {
			if ( 'thumb_preview' == $field['type'] ) {
				$args = $gallery->get_meta( 'default_thumbnail_size', array() );
				//override the link so that it does not actually open an image
				$args['link'] = 'custom';

				$hover_effect =  $gallery->get_meta( 'default_hover-effect', 'hover-effect-zoom' );
				$border_style =  $gallery->get_meta( 'default_border-style', 'border-style-square-white' );

				$featured = $gallery->featured_attachment();

				if ( false === $featured ) {
					$featured = new FooGalleryAttachment();
					$featured->url = FOOGALLERY_URL . 'assets/test_thumb_1.jpg';
				}

				echo '<div class="' . foogallery_build_class_attribute( $gallery, $hover_effect, $border_style, 'foogallery-thumbnail-preview' ) . '">';
				echo $featured->html( $args );
				echo '</div>';
			}
		}
	}
}