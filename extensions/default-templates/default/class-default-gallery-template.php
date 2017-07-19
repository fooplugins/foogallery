<?php

if ( !class_exists( 'FooGallery_Default_Gallery_Template' ) ) {

	define('FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Default_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-default', array( $this, 'add_common_thumbnail_fields' ), 10, 2 );

			add_action( 'foogallery_located_template-default', array( $this, 'enqueue_dependencies' ) );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_thumbnail_preview' ), 10, 3 );
			add_filter( 'foogallery_template_load_js-default', array( $this, 'can_enqueue_template_js' ), 10, 2 );
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 * @param $extensions
		 *
		 * @return array
		 */
		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		/**
		 * Add our gallery template to the list of templates available for every gallery
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
				'slug'        => 'default',
				'name'        => __( 'Responsive Image Gallery', 'foogallery' ),
				'lazyload_support' => true,
				'preview_css' => FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL . 'css/gallery-default.css',
				'admin_js'	  => FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL . 'js/admin-gallery-default.js',
				'fields'	  => array(
                    array(
                        'id'      => 'thumbnail_dimensions',
                        'title'   => __( 'Thumb Size', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'thumb_size',
                        'default' => array(
                            'width' => get_option( 'thumbnail_size_w' ),
                            'height' => get_option( 'thumbnail_size_h' ),
                            'crop' => true,
                        ),
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Thumb Link', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'image',
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to link each thumbnail to the full size image, the image\'s attachment page, a custom URL, or you can choose to not link to anything.', 'foogallery' ),
                    ),
					array(
						'id'      => 'lightbox',
						'title'   => __( 'Lightbox', 'foogallery' ),
						'desc'    => __( 'Choose which lightbox you want to use. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
						'type'    => 'lightbox',
					),
					array(
						'id'      => 'spacing',
						'title'   => __( 'Spacing', 'foogallery' ),
						'desc'    => __( 'The spacing or gap between thumbnails in the gallery.', 'foogallery' ),
                        'section' => __( 'Look &amp; Feel', 'foogallery' ),
						'type'    => 'select',
						'default' => 'fg-gutter-10',
						'choices' => array(
							'fg-gutter-0' => __( '0 pixels', 'foogallery' ),
							'fg-gutter-5' => __( '5 pixels', 'foogallery' ),
							'fg-gutter-10' => __( '10 pixels', 'foogallery' ),
							'fg-gutter-15' => __( '15 pixels', 'foogallery' ),
							'fg-gutter-20' => __( '20 pixels', 'foogallery' ),
							'fg-gutter-25' => __( '25 pixels', 'foogallery' ),
						)
					),
					array(
						'id'      => 'alignment',
						'title'   => __( 'Alignment', 'foogallery' ),
						'desc'    => __( 'The horizontal alignment of the thumbnails inside the gallery.', 'foogallery' ),
                        'section' => __( 'Look &amp; Feel', 'foogallery' ),
						'default' => 'fg-center',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'fg-left' => __( 'Left', 'foogallery' ),
							'fg-center' => __( 'Center', 'foogallery' ),
							'fg-right' => __( 'Right', 'foogallery' ),
						)
					),
					array(
						'id'      => 'loading_animation',
						'title'   => __( 'Loading Indicator', 'foogallery' ),
						'default' => 'yes',
						'type'    => 'radio',
						'choices' => array(
							'yes'  => __( 'Show Thumbnail Loading Indicator', 'foogallery' ),
							'no'   => __( 'Disabled', 'foogallery' )
						),
						'desc'	  => __( 'By default, an animated loading animation indicator is shown before the thumbnails have loaded. You can disable the loader if you want.', 'foogallery' ),
                        'section' => __( 'Advanced', 'foogallery' )
					),
//					array(
//						'id' => 'thumb_preview',
//						'title' => __( 'Preview', 'foogallery' ),
//						'desc' => __( 'This is what your gallery thumbnails will look like.', 'foogallery' ),
//						'section' => __( 'Thumbnail Settings', 'foogallery' ),
//						'type' => 'default_thumb_preview',
//					)
				)
			);

			return $gallery_templates;
		}

		/**
		 * Add thumbnail fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_common_thumbnail_fields( $fields, $template ) {
			return apply_filters( 'foogallery_gallery_template_common_thumbnail_fields', $fields );
		}

		/**
		 * Renders the thumbnail preview field
		 *
		 * @param $field array
		 * @param $gallery FooGallery
		 * @param $template array
		 */
		function render_thumbnail_preview( $field, $gallery, $template ) {
			if ( 'default_thumb_preview' == $field['type'] ) {
				$args = $gallery->get_meta( 'default_thumbnail_dimensions', array(
						'width' => get_option( 'thumbnail_size_w' ),
						'height' => get_option( 'thumbnail_size_h' ),
						'crop' => true
				) );

				//override the link so that it does not actually open an image
				$args['link'] = 'custom';
				$args['custom_link'] = '#preview';

				$hover_effect = $gallery->get_meta( 'default_hover-effect', 'hover-effect-zoom' );
				$border_style = $gallery->get_meta( 'default_border-style', 'border-style-square-white' );
				$hover_effect_type = $gallery->get_meta( 'default_hover-effect-type', '' );
				$caption_hover_effect = $gallery->get_meta( 'default_caption-hover-effect', 'hover-caption-simple' );

				$featured = $gallery->featured_attachment();

				if ( false === $featured ) {
					$featured = new FooGalleryAttachment();
					$featured->url = foogallery_test_thumb_url();
					$featured->caption = __( 'Caption Title', 'foogallery' );
					$featured->description = __( 'Long Caption Description Text', 'foogallery' );
				}

				echo '<div class="foogallery-default-preview ' . foogallery_build_class_attribute_safe( $gallery, $hover_effect, $border_style, $hover_effect_type, $caption_hover_effect, 'foogallery-thumbnail-preview' ) . '">';
				echo $featured->html( $args, true, false );
				echo $featured->html_caption( 'both' );
				echo '</a>';
				echo '</div>';
			}
		}

		/**
		 * Enqueue scripts that the default gallery template relies on
		 */
		function enqueue_dependencies( $gallery ) {
			wp_enqueue_script( 'jquery' );

			//how do we handle loading animations
//			if ( 'yes' === $gallery->get_meta( 'default_loading_animation', 'yes' ) ) {
//				foogallery_enqueue_imagesloaded_script();
//			}

			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();

			$css = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'default/css/foogallery.responsive.min.css';
			wp_enqueue_style( 'foogallery-default', $css, array( 'foogallery-core' ), FOOGALLERY_VERSION );

			$js = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'default/js/foogallery.responsive.min.js';
			wp_enqueue_script( 'foogallery-default', $js, array( 'foogallery-core' ), FOOGALLERY_VERSION );
		}

		/**
		 * @param $include bool By default we will try to include the template JS
		 * @param $gallery FooGallery the gallery instance we are loading
		 *
		 * @return bool if we want to try to include the template JS
		 */
		function can_enqueue_template_js( $include, $gallery ) {
			if ( 'yes' === $gallery->get_meta( 'default_loading_animation', 'yes' ) ) {
				return true;
			}
			return false;
		}
	}
}