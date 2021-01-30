<?php

if ( !class_exists( 'FooGallery_Justified_CSS_Gallery_Template' ) ) {

	class FooGallery_Justified_CSS_Gallery_Template {

		const template_id = 'justified-css';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_filter( 'foogallery_template_thumbnail_dimensions-' . self::template_id, array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-' . self::template_id, array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-' . self::template_id, array( $this, 'build_gallery_template_arguments' ) );

			add_filter( 'foogallery_override_gallery_template_fields-' . self::template_id, array( $this, 'adjust_default_field_values' ), 10, 2 );

			//add a style block for the gallery based on the field settings
			add_action( 'foogallery_loaded_template_before', array( $this, 'add_style_block' ), 10, 1 );
        }

		/**
		 * Add our gallery template to the list of templates available for every gallery
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
                'slug'        => 'justified-css',
                'name'        => __( 'Justified Gallery v2.0', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-justified-css',
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
                'enqueue_core' => true,
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
                ),
			);

			return $gallery_templates;
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 *
		 * @param $extensions
		 *
		 * @return array
		 */
		function register_myself( $extensions ) {
			$extensions[] = __FILE__;

			return $extensions;
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
			$height = $foogallery->get_meta( 'justified-css_thumb_height', false );
			return array(
				'height' => intval( $height ),
				'width'  => 0,
				'crop'   => false
			);
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
                    'height' => intval( $arguments['thumbnail_height'] ),
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

		/**
		 * Add a style block based on the field settings
		 *
		 * @param $gallery FooGallery
		 */
		function add_style_block( $gallery ) {
			if ( self::template_id !== $gallery->gallery_template ) {
				return;
			}

			$id = $gallery->container_id();
			$margins = intval( foogallery_gallery_template_setting( 'margins', 2 ) );
			$thumb_height = intval( foogallery_gallery_template_setting( 'thumb_height', 250 ) );

			?>
			<style>
                #<?php echo $id; ?>.fg-justified-css .fg-image-wrap {
                    max-height: <?php echo $thumb_height; ?>px;
                }
                #<?php echo $id; ?>.fg-justified-css .fg-item {
                    margin: <?php echo $margins; ?>px;
                }
			</style>
			<?php
		}
	}
}