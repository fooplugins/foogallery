<?php

if ( ! class_exists( 'FooGallery_Justified_Gallery_Template' ) ) {

	define( 'FOOGALLERY_JUSTIFIED_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ) );

	class FooGallery_Justified_Gallery_Template {

		const TEMPLATE_ID = 'justified';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			// @formatter:off
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_filter( 'foogallery_template_thumbnail_dimensions-justified', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			// add the data options needed for justified.
			add_filter( 'foogallery_build_container_data_options-justified', array( $this, 'add_justified_options' ), 10, 3 );

			// build up the thumb dimensions from some arguments.
			add_filter( 'foogallery_calculate_thumbnail_dimensions-justified', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			// build up the arguments needed for rendering this template.
			add_filter( 'foogallery_gallery_template_arguments-justified', array( $this, 'build_gallery_template_arguments' ) );

			add_filter( 'foogallery_override_gallery_template_fields-justified', array( $this, 'adjust_default_field_values' ), 10, 2 );

			// add a style block for the gallery based on the field settings.
			add_action( 'foogallery_loaded_template_before', array( $this, 'add_style_block' ), 10, 1 );
			// @formatter:on
		}

		/**
		 * Add a style block based on the field settings
		 *
		 * @param $gallery FooGallery
		 */
		function add_style_block( $gallery ) {
			if ( self::TEMPLATE_ID !== $gallery->gallery_template ) {
				return;
			}

			$id         = $gallery->container_id();
			$margins    = intval( foogallery_gallery_template_setting( 'margins', 2 ) );
			$row_height = intval( foogallery_gallery_template_setting( 'row_height', 250 ) );

			// @formatter:off
			?>
			<style>
                #<?php echo $id; ?>.fg-justified .fg-item {
                    margin-right: <?php echo $margins; ?>px;
                    margin-bottom: <?php echo $margins; ?>px;
                }

                #<?php echo $id; ?>.fg-justified .fg-image {
                    height: <?php echo $row_height; ?>px;
                }
			</style>
			<?php
			// @formatter:on
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 *
		 * @param array $extensions
		 *
		 * @return array
		 */
		public function register_myself( $extensions ) {
			$extensions[] = __FILE__;

			return $extensions;
		}

		/**
		 * Add our gallery template to the list of templates available for every gallery
		 *
		 * @param array $gallery_templates The array of gallery templates.
		 *
		 * @return array
		 */
		public function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
				'slug'                  => self::TEMPLATE_ID,
				'name'                  => __( 'Justified Gallery', 'foogallery' ),
				'preview_support'       => true,
				'common_fields_support' => true,
				'lazyload_support'      => true,
				'paging_support'        => true,
				'mandatory_classes'     => 'fg-justified',
				'thumbnail_dimensions'  => true,
				'filtering_support'     => true,
				'enqueue_core'          => true,
				'fields'                => array(
					array(
						'id'       => 'row_height',
						'title'    => __( 'Row Height', 'foogallery' ),
						'desc'     => __( 'The preferred height of your gallery rows. Depending on the aspect ratio of your images and the viewport, the row height might increase up to Max Row Height.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'number',
						'class'    => 'small-text',
						'default'  => 200,
						'step'     => '10',
						'min'      => '0',
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector'  => 'input',
							'data-foogallery-preview'         => 'shortcode',
						),
					),
					array(
						'id'       => 'thumb_height',
						'title'    => __( 'Max Row Height', 'foogallery' ),
						'desc'     => __( 'Choose the max height of your gallery rows. It should always be larger than Row Height by about 150%.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'number',
						'class'    => 'small-text',
						'default'  => 300,
						'step'     => '10',
						'min'      => '0',
						'row_data' => array(
							'data-foogallery-preview'         => 'shortcode',
							'data-foogallery-change-selector' => 'input',
						),
					),
					array(
						'id'       => 'margins',
						'title'    => __( 'Margins', 'foogallery' ),
						'desc'     => __( 'The spacing between your thumbnails.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'number',
						'class'    => 'small-text',
						'default'  => 2,
						'step'     => '1',
						'min'      => '0',
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector'  => 'input',
							'data-foogallery-preview'         => 'shortcode',
						),
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __( 'Thumbnail Link', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'default' => 'image',
						'type'    => 'thumb_link',
						'desc'    => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery' ),
					),
					array(
						'id'      => 'lightbox',
						'type'    => 'lightbox',
					),
					array(
						'id'       => 'align',
						'title'    => __( 'Alignment', 'foogallery' ),
						'desc'     => __( 'For rows that cannot be justified, what alignment should be used?', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'radio',
						'spacer'   => '<span class="spacer"></span>',
						'default'  => 'center',
						'choices'  => array(
							'left'   => __( 'Left', 'foogallery' ),
							'center' => __( 'Center', 'foogallery' ),
							'right'  => __( 'Right', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview'         => 'shortcode',
						),
					),
					array(
						'id'       => 'last-row',
						'title'    => __( 'Last Row', 'foogallery' ),
						'desc'     => __( 'Decide what happens to the last row, when there are not enough images to full it completely.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'radio',
						'default'  => 'smart',
						'choices'  => array(
							'smart'   => __( 'Default - the last row is justified and aligned, but adheres to the max row height and also compares itself to the average height of all rows.', 'foogallery' ),
							'justify' => __( 'Justify - the last row is forced to be justified, ignoring the max row height. This can enlarge images to very large sizes in some scenarios.', 'foogallery' ),
							'hide'    => __( 'Hide - the last row is hidden if it does not fill the entire row.', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview'         => 'shortcode',
						),
					),
				),
			);

			return $gallery_templates;
		}

		/**
		 * Get the thumb dimensions arguments saved for the gallery for this gallery template
		 *
		 * @param array      $dimensions
		 * @param FooGallery $foogallery
		 *
		 * @return mixed
		 */
		function get_thumbnail_dimensions( $dimensions, $foogallery ) {
			return array(
				'height' => $this->max_row_height_from_current_gallery(),
				'width'  => 0,
				'crop'   => false,
			);
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
		function add_justified_options( $options, $gallery, $attributes ) {
			$this->calculate_row_heights_for_current_gallery();

			$values = foogallery_current_gallery_get_cached_value( 'justified_row_height' );

			$margins  = foogallery_gallery_template_setting( 'margins', '1' );
			$align    = foogallery_gallery_template_setting( 'align', 'center' );
			$last_row = foogallery_gallery_template_setting( 'last-row', 'smart' );

			$options['template']['rowHeight']    = intval( $values['row_height'] );
			$options['template']['maxRowHeight'] = intval( $values['max_row_height'] );
			$options['template']['margins']      = intval( $margins );
			$options['template']['align']        = $align;
			$options['template']['lastRow']      = $last_row;

			return $options;
		}

		/**
		 * Calculates the row heights for the current gallery, also taking into account legacy settings
		 */
		function calculate_row_heights_for_current_gallery() {
			if ( ! foogallery_current_gallery_has_cached_value( 'justified_row_height' ) ) {
				$row_height = foogallery_gallery_template_setting( 'row_height', '200' );

				//check to see if there is a legacy max_row_height
				$max_row_height = foogallery_gallery_template_setting( 'max_row_height', false );

				if ( false === $max_row_height ) {
					//we do not have a legacy max_row_height, so use the thumb_height
					$max_row_height = intval( foogallery_gallery_template_setting( 'thumb_height', '300' ) );
				} else {
					if ( strpos( $max_row_height, '%' ) === false ) {
						$max_row_height = intval( $max_row_height );
					} else {
						$max_row_height = intval( $row_height * intval( $max_row_height ) / 100 );
					}
				}

				//check for a negative max_row_height
				if ( $max_row_height < 0 ) {
					$max_row_height = $row_height * 2;
				}

				foogallery_current_gallery_set_cached_value( 'justified_row_height', array(
					'row_height'     => intval( $row_height ),
					'max_row_height' => $max_row_height,
				) );
			}
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
			return array(
				'height' => $this->max_row_height_from_current_gallery(),
				'width'  => 0,
				'crop'   => false,
			);
		}

		/**
		 * Returns the max_row_height for the current gallery
		 *
		 * @return int
		 */
		function max_row_height_from_current_gallery() {
			$this->calculate_row_heights_for_current_gallery();
			$values = foogallery_current_gallery_get_cached_value( 'justified_row_height' );

			return intval( $values['max_row_height'] );
		}

		/**
		 * Build up the arguments needed for rendering this gallery template
		 *
		 * @param $args
		 *
		 * @return array
		 */
		function build_gallery_template_arguments( $args ) {
			$args = array(
				'height' => $this->max_row_height_from_current_gallery(),
				'link'   => foogallery_gallery_template_setting( 'thumbnail_link', 'image' ),
				'crop'   => false,
			);

			return $args;
		}

		/**
		 * Adjust the default values for the justified template
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 * @uses "foogallery_override_gallery_template_fields"
		 */
		function adjust_default_field_values( $fields, $template ) {
			//update specific fields
			foreach ( $fields as &$field ) {
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