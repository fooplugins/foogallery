<?php
/**
 * Class used to handle paging for gallery templates
 */
if ( ! class_exists( 'FooGallery_Paging' ) ) {

	class FooGallery_Paging {

		function __construct() {
            add_action( 'plugins_loaded', array( $this, 'load_feature' ) );
		}

        function load_feature() {
            if ( is_admin() ) {
                //add extra fields to the templates that support paging
                add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_paging_fields' ), 10, 2 );
            }

            add_action( 'foogallery_located_template', array( $this, 'determine_paging' ), 10, 1 );

            //add the paging options to the gallery container
            add_filter( 'foogallery_build_container_data_options', array( $this, 'add_paging_data_options' ), 20, 3 );

            //limit the number of attachments returned when rendering a gallery if paging is enabled
            add_filter( 'foogallery_gallery_attachments_override_for_rendering', array( $this, 'attachments_override' ), 10, 3 );

            //output pagination placeholders
            add_action( 'foogallery_loaded_template_before', array( $this, 'output_pagination_placeholders_before' ), 20, 1 );
            add_action( 'foogallery_loaded_template_after', array( $this, 'output_pagination_placeholders_after' ), 10, 1 );

            //output a script block with the rest of the attachments as json
            add_action( 'foogallery_loaded_template_after', array( $this, 'output_paging_script_block' ), 90, 1 );

            add_filter( 'foogallery_attachment_html_item_classes', array( $this, 'hide_item_for_html_output' ), 10, 3 );
        }


		function hide_item_for_html_output( $classes, $foogallery_attachment, $args ) {
			if ( isset( $foogallery_attachment->class ) ) {
				$classes[] = $foogallery_attachment->class;
			}
			return $classes;
		}

		/**
		 * Determine if the gallery supports paging and set a cached value for all paging options on the gallery for later use
		 *
		 * @param $foogallery
		 */
		function determine_paging( $foogallery ) {
			if ( foogallery_current_gallery_check_template_has_supported_feature( 'paging_support') ) {

				//check if we have arguments from the shortcode and override the saved settings
				$paging = foogallery_gallery_template_setting( 'paging_type', '' );

				if ( '' !== $paging ) {
					$paging_position = foogallery_gallery_template_setting( 'paging_position', 'both' );
					$paging_theme    = foogallery_gallery_template_setting( 'paging_theme', 'fg-light' );
					$paging_size     = intval( foogallery_gallery_template_setting( 'paging_size', 20 ) );
					$paging_scroll   = foogallery_gallery_template_setting( 'paging_scroll', 'true' ) === 'true';
					$paging_output   = foogallery_gallery_template_setting( 'paging_output', '' );

					//force bottom position for infinite and loadMore paging
					if ( 'infinite' === $paging || 'loadMore' === $paging ) {
						$paging_position = 'bottom';
					}

					$paging_options = array(
						'type'        => $paging,
						'theme'       => $paging_theme,
						'size'        => $paging_size,
						'position'    => $paging_position,
						'scrollToTop' => $paging_scroll,
						'output'      => $paging_output
					);

					if ( 'pagination' === $paging ) {
						$paging_options['limit'] = intval( foogallery_gallery_template_setting( 'paging_limit', 5 ) );
						$paging_options['showFirstLast'] = foogallery_gallery_template_setting( 'paging_showFirstLast', 'true' ) === 'true';
						$paging_options['showPrevNext'] = foogallery_gallery_template_setting( 'paging_showPrevNext', 'true' ) === 'true';
						$paging_options['showPrevNextMore'] = foogallery_gallery_template_setting( 'paging_showPrevNextMore', 'true' ) === 'true';
					}

					//cache the paging options on the gallery to be used later
					foogallery_current_gallery_set_cached_value( 'paging', $paging_options );
				}
			}
		}

		/**
		 * Renders the top pagination placeholder
		 *
		 * @param $foogallery FooGallery
		 */
		function output_pagination_placeholders_before( $foogallery ) {
			$this->output_pagination_placeholder( $foogallery, 'top' );
		}

		/**
		 * Renders the bottom pagination placeholder
		 *
		 * @param $foogallery FooGallery
		 */
		function output_pagination_placeholders_after( $foogallery ) {
			$this->output_pagination_placeholder( $foogallery, 'bottom' );
		}

		/**
		 * render the pagination placeholder
		 *
		 * @param $foogallery FooGallery
		 * @param $position
		 */
		function output_pagination_placeholder( $foogallery, $position ) {
			if ( foogallery_current_gallery_has_cached_value('paging' ) ) {
				$paging_options = foogallery_current_gallery_get_cached_value( 'paging' );

				//check to see if the page size is less than the number of items
				if ( $foogallery->attachment_count() > intval( $paging_options['size'] ) ) {

					$paging_position = $paging_options['position'];
					if ( $position === $paging_position || 'both' === $paging_position ) {

						$paging_type = $paging_options['type'];

						$paging_types_that_require_placeholders = apply_filters( 'foogallery_pagination_types_require_placeholders', array( 'dots' ) );

						if ( in_array( $paging_type, $paging_types_that_require_placeholders ) ) {
							$paging_type = apply_filters( 'foogallery_pagination_format_type_for_placeholder', $paging_type );

							echo '<nav id="' . $foogallery->container_id() . '_paging-' . $position . '" class="fg-paging-container fg-ph-' . $paging_type . '"></nav>';
						}
					}
				}
			}
		}

		/**
		 * Add paging fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_paging_fields( $fields, $template ) {
			$paging_support = false;
			if ( $template && is_array( $template ) && array_key_exists( 'paging_support', $template ) ) {
				$paging_support = $template['paging_support'];
			}

			if ( $paging_support ) {
				$fields[] = array(
					'id'      => 'paging_type',
					'title'   => __( 'Paging Type', 'foogallery' ),
					'desc'    => __( 'Add paging to a large gallery.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
                    'section_order' => 5,
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => '',
					'choices' => apply_filters( 'foogallery_gallery_template_paging_type_choices', array(
						''  => __( 'None', 'foogallery' ),
						'dots'   => __( 'Dots', 'foogallery' )
					) ),
					'row_data'=> array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					)
				);

				$fields[] = array(
					'id'      => 'paging_size',
					'title'   => __( 'Page Size', 'foogallery' ),
					'desc'    => __( 'The size of your pages.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'type'    => 'number',
					'class'   => 'small-text',
					'default' => 20,
					'step'    => '1',
					'min'     => '0',
					'row_data'=> array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'paging_type',
						'data-foogallery-show-when-field-operator' => 'regex',
						'data-foogallery-show-when-field-value'    => 'dots|pagination|infinite|loadMore',
					)
				);

				$fields[] = array(
					'id'      => 'paging_position',
					'title'   => __( 'Position', 'foogallery' ),
					'desc'    => __( 'The position of the paging for either dots or pagination.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => 'bottom',
					'choices' => apply_filters( 'foogallery_gallery_template_paging_position_choices', array(
						''  => __( 'None', 'foogallery' ),
						'top'   => __( 'Top', 'foogallery' ),
						'bottom'   => __( 'Bottom', 'foogallery' ),
						'both'   => __( 'Both', 'foogallery' )
					) ),
					'row_data'=> array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field-operator' => 'regex',
						'data-foogallery-show-when-field' => 'paging_type',
						'data-foogallery-show-when-field-value' => 'dots|pagination',
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode'
					)
				);

				$fields[] = array(
					'id'      => 'paging_theme',
					'title'   => __( 'Theme', 'foogallery' ),
					'desc'    => __( 'The theme used for pagination.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => 'fg-light',
					'choices' => apply_filters( 'foogallery_gallery_template_paging_theme_choices', array(
						'fg-light'  => __( 'Light', 'foogallery' ),
						'fg-dark'   => __( 'Dark', 'foogallery' ),
						'fg-custom' => __( 'Custom', 'foogallery' ),
					) ),
					'row_data'=> array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'paging_type',
						'data-foogallery-show-when-field-operator' => 'regex',
						'data-foogallery-show-when-field-value'    => 'dots|pagination|loadMore',
					)
				);

				$fields[] = array(
					'id'      => 'paging_scroll',
					'title'   => __( 'Scroll To Top', 'foogallery' ),
					'desc'    => __( 'Whether or not it should scroll to the top of the gallery when paging is changed.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'type'    => 'radio',
					'spacer'  => '<span class="spacer"></span>',
					'default' => 'false',
					'choices' => array(
						'true'  => __( 'Yes', 'foogallery' ),
						'false'  => __( 'No', 'foogallery' ),
					),
					'row_data'=> array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field-operator' => 'regex',
						'data-foogallery-show-when-field' => 'paging_type',
						'data-foogallery-show-when-field-value' => 'dots|pagination',
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode'
					)
				);

				$fields[] = array(
					'id'      => 'paging_limit',
					'title'   => __( 'Paging Limit', 'foogallery' ),
					'desc'    => __( 'The maximum number of page links to display for the gallery.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'type'    => 'number',
					'class'   => 'small-text',
					'default' => 5,
					'step'    => '1',
					'min'     => '0',
					'row_data'=> array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field' => 'paging_type',
						'data-foogallery-show-when-field-value' => 'pagination',
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode'
					)
				);

				$fields[] = array(
					'id'      => 'paging_showFirstLast',
					'title'   => __( 'First &amp; Last Buttons', 'foogallery' ),
					'desc'    => __( 'Whether or not to show the first &amp; last buttons for numbered pagination.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'type'    => 'radio',
					'spacer'  => '<span class="spacer"></span>',
					'default' => 'true',
					'choices' => array(
						'true'  => __( 'Show', 'foogallery' ),
						'false'  => __( 'Hide', 'foogallery' ),
					),
					'row_data'=> array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field' => 'paging_type',
						'data-foogallery-show-when-field-value' => 'pagination',
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode'
					)
				);

				$fields[] = array(
					'id'      => 'paging_showPrevNext',
					'title'   => __( 'Prev &amp; Next Buttons', 'foogallery' ),
					'desc'    => __( 'Whether or not to show the previous &amp; next buttons for numbered pagination.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'type'    => 'radio',
					'spacer'  => '<span class="spacer"></span>',
					'default' => 'true',
					'choices' => array(
						'true'  => __( 'Show', 'foogallery' ),
						'false'  => __( 'Hide', 'foogallery' ),
					),
					'row_data'=> array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field' => 'paging_type',
						'data-foogallery-show-when-field-value' => 'pagination',
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode'
					)
				);

				$fields[] = array(
					'id'      => 'paging_showPrevNextMore',
					'title'   => __( 'More Buttons', 'foogallery' ),
					'desc'    => __( 'Whether or not to show the previous &amp; next more buttons for numbered pagination.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'type'    => 'radio',
					'spacer'  => '<span class="spacer"></span>',
					'default' => 'true',
					'choices' => array(
						'true'  => __( 'Show', 'foogallery' ),
						'false'  => __( 'Hide', 'foogallery' ),
					),
					'row_data'=> array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field' => 'paging_type',
						'data-foogallery-show-when-field-value' => 'pagination',
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode'
					)
				);

				$fields[] = array(
					'id'      => 'paging_output',
					'title'   => __( 'Paging Output', 'foogallery' ),
					'desc'    => __( 'How the paging items are output. We recommend that very large galleries output as JSON.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => '',
					'choices' => array(
						''  => __( 'Fastest (JSON)', 'foogallery' ),
						'html'   => __( 'Legacy (HTML)', 'foogallery' )
					),
					'row_data'=> array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode',
						'data-foogallery-value-selector' => 'input:checked',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'paging_type',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
					)
				);
			}

			return $fields;
		}

		/**
		 * Add the required paging data options if needed
		 *
		 * @param $attributes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function add_paging_data_options($options, $gallery, $attributes) {
			if ( foogallery_current_gallery_has_cached_value('paging' ) ) {
				$options['paging'] = foogallery_current_gallery_get_cached_value( 'paging' );
			}
			return $options;
		}

        /**
         * Override the attachments returned for rendering a paginated gallery to the first page only
         * The rest of the items will be added to the script block below the gallery as json items
         * This is only when paging_output is JSON. When it's HTML then the all items are rendered
         *
         * @param bool $override
         * @param FooGallery $gallery
         * @return bool|array
         */
		function attachments_override( $override, $gallery ) {

			if ( foogallery_current_gallery_has_cached_value('paging' ) ) {

                $paging_options = foogallery_current_gallery_get_cached_value( 'paging' );
				$page_size = intval( $paging_options['size'] );
				$output = $paging_options['output'];

                if ( $page_size > 0 ) {
	                $attachments = $gallery->attachments();

                	if ( $output === 'html' ) {
                		$index = 0;
                		foreach ( $attachments as &$attachment ) {
                			$index++;
                			if ( $index > $page_size ) {
				                $attachment->class = 'fg-hidden';
			                }
		                }
                		return $attachments;

	                } else {
		                //return the first page of attachments
		                return array_splice( $attachments, 0, $page_size );
	                }
                }
            }

            return $override;
        }

		/**
		 * Output a script block with all the gallery attachments as json
		 *
		 * @param FooGallery $gallery
		 */
		function output_paging_script_block( $gallery ) {
			if ( foogallery_current_gallery_has_cached_value('paging' ) ) {
				$paging_options = foogallery_current_gallery_get_cached_value( 'paging' );
				$page_size = intval( $paging_options['size'] );
				$output = $paging_options['output'];

				if ( $page_size > 0 && $output === '' ) {
					//get the attachments that are not on the first page
					$attachments = array_slice( $gallery->attachments(), $page_size );
					foogallery_render_script_block_for_json_items( $gallery, $attachments );
				}
			}
		}
	}
}