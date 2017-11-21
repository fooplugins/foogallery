<?php

if ( !class_exists( 'FooGallery_Masonry_Gallery_Template' ) ) {

	define('FOOGALLERY_MASONRY_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Masonry_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_action( 'foogallery_enqueue_preview_dependencies', array( $this, 'enqueue_preview_dependencies' ) );

			add_filter( 'foogallery_located_template-masonry', array( $this, 'enqueue_dependencies' ) );

			add_filter( 'foogallery_template_thumbnail_dimensions-masonry', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//add the data options needed for masonry
			add_filter( 'foogallery_build_container_data_options-masonry', array( $this, 'add_masonry_options' ), 10, 3 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-masonry', array( $this, 'preview_arguments' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-masonry', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );
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
                'slug'        => 'masonry',
                'name'        => __( 'Masonry Image Gallery', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-masonry',
				'thumbnail_dimensions' => true,
                'fields'	  => array(
                    array(
                        'id'      => 'thumbnail_width',
                        'title'   => __( 'Thumb Width', 'foogallery' ),
                        'desc'    => __( 'Choose the width of your thumbnails. Thumbnails will be generated on the fly and cached once generated', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 150,
                        'step'    => '1',
                        'min'     => '0',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'layout',
                        'title'   => __( 'Masonry Layout', 'foogallery' ),
                        'desc'    => __( 'Choose a fixed width thumb layout, or responsive columns.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'radio',
                        'choices' => array(
                            'fixed'  => __( 'Fixed Width', 'foogallery' ),
                            'col2'   => __( '2 Columns', 'foogallery' ),
                            'col3'   => __( '3 Columns', 'foogallery' ),
                            'col4'   => __( '4 Columns', 'foogallery' ),
                            'col5'   => __( '5 Columns', 'foogallery' )
                        ),
                        'default' => 'fixed',
                        'row_data'=> array(
                            'data-foogallery-change-selector' => 'input:radio',
                            'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
                        )
                    ),
                    array(
                        'id'      => 'gutter_width',
                        'title'   => __( 'Gutter Width', 'foogallery' ),
                        'desc'    => __( 'The spacing between your thumbnails. Only applicable when using a fixed layout!', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 10,
                        'step'    => '1',
                        'min'     => '0',
                        'row_data'=> array(
                            'data-foogallery-hidden' => true,
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
                            'data-foogallery-show-when-field' => 'layout',
                            'data-foogallery-show-when-field-value' => 'fixed',
							'data-foogallery-preview' => 'shortcode',
                        )
                    ),
                    array(
                        'id'      => 'gutter_percent',
                        'title'   => __( 'Gutter Size', 'foogallery' ),
                        'desc'    => __( 'Choose a gutter size when using responsive columns.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'radio',
                        'choices' => array(
                            'fg-gutter-none'   => __( 'No Gutter', 'foogallery' ),
                            ''  => __( 'Normal Size Gutter', 'foogallery' ),
                            'fg-gutter-large'   => __( 'Larger Gutter', 'foogallery' )
                        ),
                        'default' => '',
                        'row_data'=> array(
                            'data-foogallery-hidden' => true,
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector' => 'input:checked',
                            'data-foogallery-show-when-field' => 'layout',
                            'data-foogallery-show-when-field-operator' => '!==',
                            'data-foogallery-show-when-field-value' => 'fixed',
							'data-foogallery-preview' => 'class'
                        )
                    ),
                    array(
                        'id'      => 'alignment',
                        'title'   => __( 'Alignment', 'foogallery' ),
                        'desc'    => __( 'You can choose to center align your images or leave them at the default (left). Only applicable when using a fixed layout!', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
                        'choices' => array(
                            ''  => __( 'Left', 'foogallery' ),
                            'fg-center'   => __( 'Center', 'foogallery' )
                        ),
                        'default' => 'fg-center',
						'row_data'=> array(
							'data-foogallery-hidden' => true,
							'data-foogallery-show-when-field' => 'layout',
							'data-foogallery-show-when-field-value' => 'fixed',
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview' => 'class'
						)
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Thumb Link', 'foogallery' ),
                        'default' => 'image' ,
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything', 'foogallery' ),
                    ),
                    array(
                        'id'      => 'lightbox',
                        'title'   => __( 'Lightbox', 'foogallery' ),
                        'desc'    => __( 'Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image"', 'foogallery' ),
                        'type'    => 'lightbox',
                        'default' => 'none',
                    ),
                ),
			);


			return $gallery_templates;
		}

		/**
		 * Enqueue scripts that the masonry gallery template relies on
		 */
		function enqueue_preview_dependencies() {
			wp_enqueue_script( 'masonry' );
		}

		/**
		 * Enqueue scripts that the masonry gallery template relies on
		 */
		function enqueue_dependencies() {
			wp_enqueue_script( 'masonry' );

			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();
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
			$width = $foogallery->get_meta( 'masonry_thumbnail_width', false );
			return array(
				'height' => 0,
				'width'  => intval( $width ),
				'crop'   => false
			);
		}

		/**
		 * Add the required masonry options if needed
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_masonry_options($options, $gallery, $attributes) {
			$layout = foogallery_gallery_template_setting( 'layout', 'fixed' );
			$options['template']['layout'] = $layout;
			if ( 'fixed' === $layout ) {
				$width = foogallery_gallery_template_setting( 'thumbnail_width', '150' );
				$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
				$options['template']['columnWidth'] = intval($width);
				$options['template']['gutter'] = intval($gutter_width);
			}
			return $options;
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['thumbnail_width'] = $post_data[FOOGALLERY_META_SETTINGS]['masonry_thumbnail_width'];
			$args['layout'] = $post_data[FOOGALLERY_META_SETTINGS]['masonry_layout'];
			$args['gutter_width'] = $post_data[FOOGALLERY_META_SETTINGS]['masonry_gutter_width'];
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
            if ( array_key_exists( 'thumbnail_width', $arguments) ) {
                return array(
                    'height' => 0,
                    'width' => intval($arguments['thumbnail_width']),
                    'crop' => false
                );
            }
            return null;
		}
	}
}