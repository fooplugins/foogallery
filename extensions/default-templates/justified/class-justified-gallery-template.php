<?php

if ( !class_exists( 'FooGallery_Justified_Gallery_Template' ) ) {

	define('FOOGALLERY_JUSTIFIED_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Justified_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_filter( 'foogallery_template_thumbnail_dimensions-justified', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			add_action( 'foogallery_located_template-justified', array( $this, 'enqueue_dependencies' ) );

			//add the data options needed for justified
			add_filter( 'foogallery_build_container_data_options-justified', array( $this, 'add_justified_options' ), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-justified', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-justified', array( $this, 'build_gallery_template_arguments' ) );

			add_filter( 'foogallery_override_gallery_template_fields-justified', array( $this, 'adjust_default_field_values' ), 10, 2 );
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
                'slug'        => 'justified',
                'name'        => __( 'Justified Gallery', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-justified',
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
                'fields'	  => array(
                    array(
                        'id'      => 'thumb_height',
                        'title'   => __( 'Thumb Height', 'foogallery' ),
                        'desc'    => __( 'Choose the height of your thumbnails. Thumbnails will be generated on the fly and cached once generated', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 250,
                        'step'    => '10',
                        'min'     => '0',
						'row_data'=> array(
							'data-foogallery-preview' => 'shortcode',
							'data-foogallery-change-selector' => 'input',
						)
                    ),
                    array(
                        'id'      => 'row_height',
                        'title'   => __( 'Row Height', 'foogallery' ),
                        'desc'    => __( 'The preferred height of your gallery rows. This can be different from the thumbnail height', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 200,
                        'step'    => '10',
                        'min'     => '0',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						)
                    ),
                    array(
                        'id'      => 'max_row_height',
                        'title'   => __( 'Max Row Height', 'foogallery' ),
                        'desc'    => __( 'A number (e.g 200) which specifies the maximum row height in pixels. A negative value for no limits. Alternatively, use a percentage (e.g. 200% which means that the row height cannot exceed 2 * rowHeight)', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'text',
                        'class'   => 'small-text',
                        'default' => '150%',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						)
                    ),
                    array(
                        'id'      => 'margins',
                        'title'   => __( 'Margins', 'foogallery' ),
                        'desc'    => __( 'The spacing between your thumbnails.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 2,
                        'step'    => '1',
                        'min'     => '0',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						)
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Thumbnail Link', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'image' ,
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery' ),
                    ),
                    array(
                        'id'      => 'lightbox',
                        'title'   => __( 'Lightbox', 'foogallery' ),
                        'desc'    => __( 'Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'none',
                        'type'    => 'lightbox',
                    ),
                    array(
                        'id'      => 'lastrow',
                        'title'   => __( 'Last Row', 'foogallery' ),
                        'desc'    => __( 'What should be done with the last row in the gallery?', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'radio',
                        'spacer'  => '<span class="spacer"></span>',
                        'default' => 'justify',
                        'choices' => array(
                            'hide' => __( 'Hide', 'foogallery' ),
                            'justify' => __( 'Justify', 'foogallery' ),
                            'nojustify' => __( 'No Justify', 'foogallery' ),
                            'right' => __( 'Right', 'foogallery' ),
                            'center' => __( 'Center', 'foogallery' ),
                            'left' => __( 'Left', 'foogallery' ),
                        ),
                        'row_data'=> array(
                            'data-foogallery-change-selector' => 'input:radio',
                            'data-foogallery-value-selector' => 'input:checked',
                            'data-foogallery-preview' => 'shortcode',
                        )
                    ),
                ),
			);

			return $gallery_templates;
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
			$height = $foogallery->get_meta( 'justified_thumb_height', false );
			return array(
				'height' => intval( $height ),
				'width'  => 0,
				'crop'   => false
			);
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
		 * Add the required justified options if needed
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_justified_options($options, $gallery, $attributes) {

			$row_height = foogallery_gallery_template_setting( 'row_height', '150' );
			$max_row_height = foogallery_gallery_template_setting( 'max_row_height', '200%' );
			if ( strpos( $max_row_height, '%' ) === false ) {
				$max_row_height = intval( $max_row_height );
			}
			$margins = foogallery_gallery_template_setting( 'margins', '1' );
			$lastRow = foogallery_gallery_template_setting( 'lastrow', 'center' );

			$options['template']['rowHeight'] = intval($row_height);
			$options['template']['maxRowHeight'] = $max_row_height;
			$options['template']['margins'] = intval($margins);
            $options['template']['lastRow'] = $lastRow;

			return $options;
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
		    if ( array_key_exists( 'thumbnail_height', $arguments) ) {
                return array(
                    'height' => intval($arguments['thumbnail_height']),
                    'width' => 0,
                    'crop' => false
                );
            }

            return null;
		}

        /**
         * Build up the arguments needed for rendering this gallery template
         *
         * @param $args
         * @return array
         */
        function build_gallery_template_arguments( $args ) {
            $height = foogallery_gallery_template_setting( 'thumb_height', '250' );
            $args = array(
                'height' => $height,
                'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' ),
				'crop' => false
            );

            return $args;
        }

		/**
		 * Adjust the default values for the justified template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function adjust_default_field_values( $fields, $template ) {
			//update specific fields
			foreach ($fields as &$field) {
				if ( 'border_size' === $field['id'] ) {
					$field['default'] = '';
				} else if ( 'hover_effect_caption_visibility' == $field['id'] ) {
					$field['default'] = 'fg-caption-always';
				} else if ( 'hover_effect_icon' == $field['id'] ) {
					$field['default'] = 'fg-hover-zoom2';
				} else if ( 'caption_desc_source' == $field['id'] ) {
					$field['default'] = 'none';
				}
			}

			return $fields;
		}
	}
}