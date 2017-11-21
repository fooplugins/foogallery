<?php

if ( !class_exists( 'FooGallery_Default_Gallery_Template' ) ) {

	define('FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Default_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			add_action( 'foogallery_located_template-default', array( $this, 'enqueue_dependencies' ) );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-default', array( $this, 'preview_arguments' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-default', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-default', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );
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
				'preview_support' => true,
				'common_fields_support' => true,
				'paging_support' => true,
				'lazyload_support' => true,
				'mandatory_classes' => 'fg-default',
				'thumbnail_dimensions' => true,
				'fields'	  => array(
                    array(
                        'id'      => 'thumbnail_dimensions',
                        'title'   => __( 'Thumbnail Size', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'thumb_size_no_crop',
                        'default' => array(
                            'width' => get_option( 'thumbnail_size_w' ),
                            'height' => get_option( 'thumbnail_size_h' ),
                        ),
						'row_data'=> array(
                            'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Link To', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'image',
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to link each thumbnail to the full size image, the image\'s attachment page, a custom URL, or you can choose to not link to anything.', 'foogallery' ),
                    ),
					array(
						'id'      => 'lightbox',
						'title'   => __( 'Lightbox', 'foogallery' ),
						'desc'    => __( 'Choose which lightbox you want to use. The lightbox will generally only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
						'type'    => 'lightbox',
                        'default' => 'none',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'select'
						)
					),
					array(
						'id'      => 'lightbox_foobox_help',
						'title'   => __( 'FooBox Help', 'foogallery' ),
						'desc'    => __( 'The FooBox lightbox is a separate plugin.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'help',
						'row_data'=> array(
							'data-foogallery-hidden' => true,
							'data-foogallery-show-when-field' => 'lightbox',
							'data-foogallery-show-when-field-value' => 'foobox'
						)
					),
					array(
						'id'      => 'spacing',
						'title'   => __( 'Spacing', 'foogallery' ),
						'desc'    => __( 'The spacing or gap between thumbnails in the gallery.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
						'type'    => 'select',
						'default' => 'fg-gutter-10',
						'choices' => array(
							'fg-gutter-0' => __( 'none', 'foogallery' ),
							'fg-gutter-5' => __( '5 pixels', 'foogallery' ),
							'fg-gutter-10' => __( '10 pixels', 'foogallery' ),
							'fg-gutter-15' => __( '15 pixels', 'foogallery' ),
							'fg-gutter-20' => __( '20 pixels', 'foogallery' ),
							'fg-gutter-25' => __( '25 pixels', 'foogallery' ),
						),
                        'row_data'=> array(
                            'data-foogallery-change-selector' => 'select',
							'data-foogallery-preview' => 'class'
                        )
					),
					array(
						'id'      => 'alignment',
						'title'   => __( 'Alignment', 'foogallery' ),
						'desc'    => __( 'The horizontal alignment of the thumbnails inside the gallery.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
						'default' => 'fg-center',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'fg-left' => __( 'Left', 'foogallery' ),
							'fg-center' => __( 'Center', 'foogallery' ),
							'fg-right' => __( 'Right', 'foogallery' ),
						),
                        'row_data'=> array(
                            'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview' => 'class'
                        )
					)
				)
			);

			return $gallery_templates;
		}

		/**
		 * Enqueue scripts that the default gallery template relies on
		 */
		function enqueue_dependencies( $gallery ) {
			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['thumbnail_dimensions'] = $post_data[FOOGALLERY_META_SETTINGS]['default_thumbnail_dimensions'];
			return $args;
		}

		/**
		 * Builds thumb dimensions from arguments
		 *
		 * @param array $dimensions
		 * @param array $arguments
		 *
		 * @return mixed
		 */
		function build_thumbnail_dimensions_from_arguments( $dimensions, $arguments ) {
            if ( array_key_exists( 'thumbnail_dimensions', $arguments) ) {
                return array(
                    'height' => intval($arguments['thumbnail_dimensions']['height']),
                    'width' => intval($arguments['thumbnail_dimensions']['width']),
                    'crop' => '1'
                );
            }
            return null;
		}

		/**
		 * Get the thumb dimensions arguments saved for the gallery for this gallery template
		 *
		 * @param array $dimensions
		 * @param FooGallery $foogallery
		 *
		 * @return mixed
		 */
		function get_thumbnail_dimensions( $dimensions, $foogallery ) {
			$dimensions = $foogallery->get_meta( 'default_thumbnail_dimensions', array(
				'width' => get_option( 'thumbnail_size_w' ),
				'height' => get_option( 'thumbnail_size_h' )
			) );
			$dimensions['crop'] = true;
			return $dimensions;
		}
	}
}