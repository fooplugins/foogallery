<?php

if ( ! class_exists( 'FooGallery_Carousel_Gallery_Template' ) ) {

	class FooGallery_Carousel_Gallery_Template {

		const TEMPLATE_ID = 'carousel';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			// @formatter:off
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-carousel', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-carousel', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//build up the arguments needed for rendering this template
			add_filter( 'foogallery_gallery_template_arguments-carousel', array( $this, 'build_gallery_template_arguments' ) );

			//add the data options needed for grid pro
			add_filter( 'foogallery_build_container_data_options-carousel', array( $this, 'add_data_options' ), 10, 3 );

			add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'admin_custom_fields' ), 10, 3 );
			// @formatter:on
		}

		/**
		 * Output a custom gutter field.
		 *
		 * @param $field
		 * @param $gallery
		 * @param $template
		 *
		 * @return void
		 */
		function admin_custom_fields( $field, $gallery, $template ) {
			if ( isset( $field ) && is_array( $field ) && isset( $field['type'] ) && 'carousel_gutter' === $field['type'] ) {
				$id = $template['slug'] . '_' . $field['id'];
		        $min  = is_array( $field['value'] ) ? intval( $field['value']['min'] ) : -40;
				$max = is_array( $field['value'] ) ? intval( $field['value']['max'] ): -20;
				$units = is_array( $field['value'] ) ? $field['value']['units'] : '%';
				echo '<label for="FooGallerySettings_' . $id . '_min">' . __( 'Min', 'foogallery' ) . '</label>&nbsp;';
				echo '<input class="small-text" type="number" step="1" min="-1000" id="FooGallerySettings_' . $id . '_min" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][min]" value="' . esc_attr( $min ) . '" />';
				echo '&nbsp;&nbsp;<label for="FooGallerySettings_' . $id . '_max">' . __( 'Max', 'foogallery' ) . '</label>&nbsp;';
				echo '<input class="small-text" type="number" step="1" min="-1000" id="FooGallerySettings_' . $id . '_max" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][max]" value="' . esc_attr( $max ) . '" />';
				echo '&nbsp;&nbsp;<label for="FooGallerySettings_' . $id . '_units">' . __( 'Units', 'foogallery' ) . '</label>&nbsp;';
				echo '<select id="FooGallerySettings_' . $id . '_units" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][units]">';
				echo '<option ' . ( $units === '%' ? 'selected="selected" ' : '' ) . 'value="%">' . __( '%', 'foogallery' ) . '</option>';
				echo '<option ' . ( $units === 'px' ? 'selected="selected" ' : '' ) . 'value="px">' . __( 'px', 'foogallery' ) . '</option>';
				echo '</select>';
				?>
				<script type="text/javascript">
					jQuery(function ($) {
						$('.foogallery-field-carousel-gutter-preset').on('click', function(e) {
							e.preventDefault();

							$('#FooGallerySettings_<?php echo $id; ?>_min').val( $(this).data('min') );
							$('#FooGallerySettings_<?php echo $id; ?>_max').val( $(this).data('max') );
							$('#FooGallerySettings_<?php echo $id; ?>_units').val( $(this).data('units') ).trigger("change");
						});
					});
				</script>
				<?php
				echo '&nbsp;&nbsp;<a data-min="-40" data-max="-20" data-units="%" class="foogallery-field-carousel-gutter-preset" href="#" >' . __( 'Preset 1', 'foogallery' ) . '</a>';
				echo '&nbsp;&nbsp;<a data-min="5" data-max="10" data-units="px" class="foogallery-field-carousel-gutter-preset" href="#" >' . __( 'Preset 2', 'foogallery' ) . '</a>';
			}
		}

		/**
		 * Add the required data options if needed
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_data_options($options, $gallery, $attributes) {
			$maxItems = foogallery_gallery_template_setting( 'maxItems', 3 );
			$scale = foogallery_gallery_template_setting( 'scale', 0.12 );
			$gutter = foogallery_gallery_template_setting( 'gutter', array( 'min' => -40, 'max' => -20, 'units' => '%' ) );
			$gutter_min = $gutter['min'];
			$gutter_max = $gutter['max'];
			$gutter_units = $gutter['units'];
			$centerOnClick = foogallery_gallery_template_setting( 'centerOnClick', 'false' ) === 'true';
			$autoplay_interaction = foogallery_gallery_template_setting( 'autoplay_interaction', 'pause' );
			$autoplay_time = foogallery_gallery_template_setting( 'autoplay_time', 0 );

			$options['template']['maxItems'] = intval( $maxItems );
			$options['template']['scale'] = floatval( $scale );
			$options['template']['gutter']['min'] = intval( $gutter_min );
			$options['template']['gutter']['max'] = intval( $gutter_max );
			$options['template']['gutter']['unit'] = $gutter_units;
			$options['template']['autoplay']['time'] = intval( $autoplay_time );
			$options['template']['autoplay']['interaction'] = $autoplay_interaction;
			$options['template']['centerOnClick'] = $centerOnClick;

			return $options;
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
		 * Add our gallery template to the list of templates available for every gallery
		 *
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
				'slug'                  => self::TEMPLATE_ID,
				'name'                  => __( 'Carousel', 'foogallery' ),
				'preview_support'       => true,
				'common_fields_support' => true,
				'paging_support'        => false,
				'lazyload_support'      => true,
				'mandatory_classes'     => 'fg-carousel',
				'thumbnail_dimensions'  => true,
				'filtering_support'     => true,
				'enqueue_core'          => true,
				'fields'                => array(
					array(
						'id'       => 'thumbnail_dimensions',
						'title'    => __( 'Thumbnail Size', 'foogallery' ),
						'desc'     => __( 'Choose the size of your thumbnails.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'thumb_size_no_crop',
						'default' => array(
							'width' => 200,
							'height' => 200,
							'crop' => true
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'maxItems',
						'title'    => __( 'Max Items To Show', 'foogallery' ),
						'desc'     => __( 'The total number of items displayed in the carousel. This should be an ODD number as the active item is the center and the remainder make up each side.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => '5',
						'type'     => 'number',
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'scale',
						'title'    => __( 'Scaling', 'foogallery' ),
						'desc'     => __( 'How to scale the items that are not in the center. Each item to the side is scaled down by this factor.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => '0.12',
						'type'     => 'select',
						'choices' => array(
							'0' => __( 'None', 'foogallery' ),
							'0.05' => __( 'Less', 'foogallery' ),
							'0.12' => __( 'Normal', 'foogallery' ),
							'0.18' => __( 'More', 'foogallery' ),
							'0.25' => __( 'Most', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'gutter',
						'title'    => __( 'Gutters', 'foogallery' ),
						'desc'     => __( 'The minimum gutter to apply to items. Negative values create an overlap. ', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => array( 'min' => -40, 'max' => -20, 'units' => '%' ),
						'type'     => 'carousel_gutter',
						'row_data' => array(
							'data-foogallery-change-selector' => ':input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'centerOnClick',
						'title'    => __( 'Side Items Click', 'foogallery' ),
						'desc'     => __( 'What happens when an item in the carousel is clicked.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => 'true',
						'type'     => 'radio',
						'spacer'   => '<span class="spacer"></span>',
						'choices' => array(
							'true' => __( 'Center The Clicked Item', 'foogallery' ),
							'false' => __( 'Open The Item In Lightbox', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'autoplay_time',
						'title'    => __( 'Autoplay Time', 'foogallery' ),
						'desc'     => __( 'The number in seconds an item is displayed. Set to zero to turn off autoplay.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => '0',
						'min'      => 0,
						'type'     => 'number',
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'autoplay_interaction',
						'title'    => __( 'Autoplay', 'foogallery' ),
						'desc'     => __( 'Specifies what occurs once/when a user has interacted with the carousel. Please Note: for touch devices autoplay is paused on "touchstart" and is only resumed once the user has not interacted with the carousel for the supplied time.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => 'pause',
						'type'     => 'radio',
						'choices' => array(
							'pause' => __( 'Pause - autoplay will resume a short time after the user stops interacting with the carousel', 'foogallery' ),
							'disable' => __( 'Disable - autoplay is simply turned off once the carousel has been interacted with', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'inverted',
						'title'    => __( 'Invert Control Theme', 'foogallery' ),
						'desc'     => __( 'Inverts the theme used for the carousel controls (paging and navigation buttons) based on the theme under appearance.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => '',
						'type'     => 'radio',
						'spacer'   => '<span class="spacer"></span>',
						'choices' => array(
							'' => __( 'Same', 'foogallery' ),
							'fg-inverted' => __( 'Inverted', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
                    array(
                        'id'       => 'show_nav_arrows',
                        'title'    => __( 'Show Nav. Arrows', 'foogallery' ),
                        'section'  => __( 'General', 'foogallery' ),
                        'default'  => '',
                        'type'     => 'radio',
                        'spacer'   => '<span class="spacer"></span>',
                        'choices' => array(
                            '' => __( 'Shown', 'foogallery' ),
                            'fg-carousel-hide-nav-arrows' => __( 'Hidden', 'foogallery' ),
                        ),
                        'row_data' => array(
                            'data-foogallery-change-selector' => 'input',
                            'data-foogallery-preview'         => 'shortcode'
                        )
                    ),
                    array(
                        'id'       => 'show_pagination',
                        'title'    => __( 'Show Pagination', 'foogallery' ),
                        'section'  => __( 'General', 'foogallery' ),
                        'default'  => '',
                        'type'     => 'radio',
                        'spacer'   => '<span class="spacer"></span>',
                        'choices' => array(
                            '' => __( 'Shown', 'foogallery' ),
                            'fg-carousel-hide-pagination' => __( 'Hidden', 'foogallery' ),
                        ),
                        'row_data' => array(
                            'data-foogallery-change-selector' => 'input',
                            'data-foogallery-preview'         => 'shortcode'
                        )
                    ),
                    array(
                        'id'       => 'show_progress',
                        'title'    => __( 'Show Progress Bar', 'foogallery' ),
                        'section'  => __( 'General', 'foogallery' ),
                        'default'  => '',
                        'type'     => 'radio',
                        'spacer'   => '<span class="spacer"></span>',
                        'choices' => array(
                            '' => __( 'Shown', 'foogallery' ),
                            'fg-carousel-hide-progress-bar' => __( 'Hidden', 'foogallery' ),
                        ),
                        'row_data' => array(
                            'data-foogallery-change-selector' => 'input',
                            'data-foogallery-preview'         => 'shortcode'
                        )
                    ),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __( 'Thumbnail Link', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'default' => 'image',
						'type'    => 'thumb_link',
						'desc'    => __( 'You can choose to link each thumbnail to the full size image, the image\'s attachment page, a custom URL, or you can choose to not link to anything.', 'foogallery' ),
					),
					array(
						'id'      => 'lightbox',
						'type'    => 'lightbox',
					),
				)
			);

			return $gallery_templates;
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
			if ( array_key_exists( 'thumbnail_dimensions', $arguments ) ) {
				return array(
					'height' => intval( $arguments['thumbnail_dimensions']['height'] ),
					'width'  => intval( $arguments['thumbnail_dimensions']['width'] ),
					'crop'   => '1'
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
			$dimensions = $foogallery->get_meta( 'carousel_thumbnail_dimensions', array(
				'width'  => 200,
				'height' => 200
			) );
			$dimensions['crop'] = true;

			return $dimensions;
		}

		/**
		 * Build up the arguments needed for rendering this gallery template
		 *
		 * @param $args
		 *
		 * @return array
		 */
		function build_gallery_template_arguments( $args ) {
			$args         = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
			$args['crop'] = '1'; //we now force thumbs to be cropped
			$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

			return $args;
		}
	}
}