<?php

if ( !class_exists( 'FooGallery_Image_Viewer_Gallery_Template' ) ) {

	define('FOOGALLERY_IMAGE_VIEWER_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Image_Viewer_Gallery_Template {

		const TEMPLATE_ID = 'image-viewer';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-image-viewer', array( $this, 'adjust_fields' ), 10, 2 );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_filter( 'foogallery_template_thumbnail_dimensions-image-viewer', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-image-viewer', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-image-viewer', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //alter the crop value if needed
            add_filter( 'foogallery_render_gallery_template_field_value', array( $this, 'alter_field_value'), 10, 4 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-image-viewer', array( $this, 'build_gallery_template_arguments' ) );

			//add the data options needed for image viewer
			add_filter( 'foogallery_build_container_data_options-image-viewer', array( $this, 'add_data_options' ), 10, 3 );

			// add a style block for the gallery based on the thumbnail width.
			add_action( 'foogallery_loaded_template_before', array( $this, 'add_width_style_block' ), 10, 1 );
        }

		/**
		 * Add a style block based on the width thumbnail size
		 *
		 * @param $gallery FooGallery
		 */
		function add_width_style_block( $gallery ) {
			if ( self::TEMPLATE_ID !== $gallery->gallery_template ) {
				return;
			}

			$id         = $gallery->container_id();
			$dimensions = foogallery_gallery_template_setting('thumbnail_size');
			if ( is_array( $dimensions ) && array_key_exists( 'width', $dimensions ) && intval( $dimensions['width'] ) > 0 ) {
				$width      = intval( $dimensions['width'] );

				// @formatter:off
				?>
<style type="text/css">
	<?php echo '#' . $id; ?> .fg-image {
        width: <?php echo $width; ?>px;
    }
</style>
				<?php
				// @formatter:on
			}
		}

        function alter_field_value( $value, $field, $gallery, $template ) {
            //only do something if we are dealing with the thumbnail_dimensions field in this template
            if ( self::TEMPLATE_ID === $template['slug'] && 'thumbnail_size' === $field['id'] ) {
                if ( !array_key_exists( 'crop', $value ) ) {
                    $value['crop'] = true;
                }
            }

            return $value;
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
				'slug'        => self::TEMPLATE_ID,
				'name'        => __( 'Image Viewer', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
				'lazyload_support' => true,
				'mandatory_classes' => 'fg-image-viewer',
				'thumbnail_dimensions' => true,
				'enqueue_core' => true,
				'fields'	  => array(
                    array(
                        'id'      => 'thumbnail-help',
                        'desc'    => __( 'It is recommended to crop your thumbnails, so that your gallery remains a constant size. If you do not crop, then the size of the gallery could potentially change for each thumbnail.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'help'
                    ),
                    array(
                        'id'      => 'thumbnail_size',
                        'title'   => __( 'Thumb Size', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails', 'foogallery' ),
                        'type'    => 'thumb_size',
                        'default' => array(
                            'width' => 640,
                            'height' => 360,
                            'crop' => true
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Thumbnail Link', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'image' ,
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to either link each thumbnail to the full size image or to the image\'s attachment page', 'foogallery'),
                    ),
                    array(
                        'id'      => 'lightbox',
                        'desc'    => __( 'Choose which lightbox you want to use in the gallery', 'foogallery' ),
                        'type'    => 'lightbox',
                    ),
                    array(
                        'id'      => 'alignment',
                        'title'   => __( 'Alignment', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'The horizontal alignment of the thumbnails inside the gallery', 'foogallery' ),
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
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
					array(
						'id'      => 'looping',
						'title'   => __( 'Loop Images', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'desc'    => __( 'When navigating through the images, do you want to loop image back to the first after you navigate past the last image?', 'foogallery' ),
						'default' => 'enabled',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'disabled' => __( 'Disabled', 'foogallery' ),
							'enabled' => __( 'Looping Enabled', 'foogallery' ),
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview' => 'shortcode'
						)
					),
                    array(
                        'id'      => 'language-help',
                        'desc'    => __( 'You can change the "Prev", "Next" and "of" text used in the gallery from the settings page, under the Language tab.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'type'    => 'help'
                    )
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
		function adjust_fields( $fields, $template ) {

			//update specific fields
			foreach ($fields as &$field) {
				if ( 'rounded_corners' === $field['id'] ) {
					unset( $field['choices']['fg-round-full'] );
				}
			}

			return $fields;
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
			$dimensions = $foogallery->get_meta( 'image-viewer_thumbnail_size', array(
				'width' => 640,
				'height' => 360,
                'crop' => true
			) );
            if ( !array_key_exists( 'crop', $dimensions ) ) {
                $dimensions['crop'] = true;
            }
			return $dimensions;
		}

		/**
		 * Override specific settings so that the gallery template will always work
		 *
		 * @param $settings
		 * @param $post_id
		 * @param $form_data
		 *
		 * @return mixed
		 */
		function override_settings($settings, $post_id, $form_data) {
			if ( 'fg-round-full' === $settings['image-viewer_rounded_corners'] ) {
				$settings['image-viewer_rounded_corners'] = 'fg-round-large';
			}

			return $settings;
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
            if ( array_key_exists( 'thumbnail_size', $arguments) ) {
                return array(
                    'height' => intval($arguments['thumbnail_size']['height']),
                    'width' => intval($arguments['thumbnail_size']['width']),
                    'crop' => $arguments['thumbnail_size']['crop']
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
            $args = foogallery_gallery_template_setting( 'thumbnail_size', array(
	            'width' => 640,
	            'height' => 360,
	            'crop' => true
            ) );
            if ( !array_key_exists( 'crop', $args ) ) {
                $args['crop'] = '1'; //we now force thumbs to be cropped by default
            }
            $args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

            return $args;
        }

		/**
		 * Add the required options
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_data_options($options, $gallery, $attributes) {

			$looping = foogallery_gallery_template_setting( 'looping', 'enabled' ) === 'enabled';
			$options['template']['loop'] = $looping;

			return $options;
		}
	}
}