<?php
/**
 * Class used to handle paging for gallery templates
 */
if ( ! class_exists( 'FooGallery_Paging' ) ) {

	class FooGallery_Paging {

		function __construct() {
			if ( is_admin() ) {
				//add extra fields to the templates that support lazy loading
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_paging_fields' ), 10, 2 );

				//build up any preview arguments
				add_filter( 'foogallery_preview_arguments', array( $this, 'preview_arguments' ), 10, 3 );
			}

			//adds the paging property to a FooGallery
			add_action( 'foogallery_located_template', array( $this, 'determine_paging' ), 10, 2 );

			//add the paging attributes to the gallery container
			add_filter( 'foogallery_build_container_data_options', array( $this, 'add_paging_options' ), 10, 3 );
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
			if ( $template && array_key_exists( 'paging_support', $template ) && true === $template['paging_support'] ) {
				$fields[] = array(
					'id'      => 'paging_type',
					'title'   => __( 'Paging Type', 'foogallery' ),
					'desc'    => __( 'Add paging to a large gallery.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => '',
					'choices' => apply_filters( 'foogallery_gallery_template_paging_type_choices', array(
						''  => __( 'None', 'foogallery' ),
						'dots'   => __( 'Dots', 'foogallery' )
					) ),
					'row_data'=> array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode',
						'data-foogallery-value-selector' => 'input:checked',
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
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
					)
				);

				$fields[] = array(
					'id'      => 'paging_position',
					'title'   => __( 'Position', 'foogallery' ),
					'desc'    => __( 'The position of the paging for either dots or pagination.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => 'both',
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
					'desc'    => __( 'The theme used for paging.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => 'fg-light',
					'choices' => apply_filters( 'foogallery_gallery_template_paging_position_choices', array(
						'fg-light'  => __( 'Light', 'foogallery' ),
						'fg-dark'   => __( 'Dark', 'foogallery' ),
					) ),
					'row_data'=> array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'paging_type',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
					)
				);

				$fields[] = array(
					'id'      => 'paging_scroll',
					'title'   => __( 'Scroll To Top', 'foogallery' ),
					'desc'    => __( 'Whether or not it should scroll to the top of the gallery when paging is changed.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'type'    => 'radio',
					'spacer'  => '<span class="spacer"></span>',
					'default' => 'true',
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
					'desc'    => __( 'Whether or not to show the first &amp; last buttons for pagination.', 'foogallery' ),
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
					'desc'    => __( 'Whether or not to show the previous &amp; next buttons for pagination.', 'foogallery' ),
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
					'desc'    => __( 'Whether or not to show the previous &amp; next more buttons for pagination.', 'foogallery' ),
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
			}

			return $fields;
		}

		/**
		 * Determine if the gallery has paging enabled
		 *
		 * @param $foogallery FooGallery
		 */
		function determine_paging( $foogallery ) {
			$template_data = foogallery_get_gallery_template( $foogallery->gallery_template );

			//check the template supports paging
			$paging = $template_data && array_key_exists( 'paging_support', $template_data ) && true === $template_data['paging_support'];

			$foogallery->paging = apply_filters( 'foogallery_paging', $paging, $foogallery );
		}

		/**
		 * Add the required paging options if needed
		 *
		 * @param $attributes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function add_paging_options($options, $gallery, $attributes) {
			if ( isset( $gallery->paging ) && true === $gallery->paging) {

				//check if we have arguments from the shortcode and override the saved settings
				$paging = $this->get_foogallery_argument( $gallery, 'paging_type', 'paging', '' );

				if ( '' !== $paging ) {
					$paging_position = $this->get_foogallery_argument( $gallery, 'paging_position', 'paging_position', 'both' );
					$paging_theme    = $this->get_foogallery_argument( $gallery, 'paging_theme', 'paging_theme', 'fg-light' );
					$paging_size     = intval( $this->get_foogallery_argument( $gallery, 'paging_size', 'paging_size', '30' ) );
					$paging_scroll   = $this->get_foogallery_argument( $gallery, 'paging_scroll', 'paging_scroll', 'true' ) === 'true';

					//force bottom position for infinite and loadMore paging
					if ( 'infinite' === $paging || 'loadMore' === $paging ) {
						$paging_position = 'bottom';
					}

					$options['paging'] = array(
						'type'        => $paging,
						'theme'       => $paging_theme,
						'size'        => $paging_size,
						'position'    => $paging_position,
						'scrollToTop' => $paging_scroll
					);

					if ( 'pagination' === $paging ) {
						$options['paging']['limit'] = intval( $this->get_foogallery_argument( $gallery, 'paging_limit', 'paging_limit', '5' ) );;
						$options['paging']['showFirstLast'] = $this->get_foogallery_argument( $gallery, 'paging_showFirstLast', 'paging_showFirstLast', 'true' ) === 'true';;
						$options['paging']['showPrevNext'] = $this->get_foogallery_argument( $gallery, 'paging_showPrevNext', 'paging_showPrevNext', 'true' ) === 'true';;
						$options['paging']['showPrevNextMore'] = $this->get_foogallery_argument( $gallery, 'paging_showPrevNextMore', 'paging_showPrevNextMore', 'true' ) === 'true';;
					}
				}
			}
			return $options;
		}

		private function get_foogallery_argument( $gallery, $setting_id, $argument_name, $default_value ) {
			global $current_foogallery_arguments;

			if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments[$argument_name] ) ) {
				return $current_foogallery_arguments[$argument_name];
			} else {
				return $gallery->get_setting( $setting_id, $default_value );
			}
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 *
		 * @param $args
		 * @param $post_data
		 * @param $template
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data, $template ) {
			$template_data = foogallery_get_gallery_template( $template );

			//check the template supports paging
			if ( $template_data && array_key_exists( 'paging_support', $template_data ) && true === $template_data['paging_support'] ) {
				$args['paging'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_type'];
				$args['paging_position'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_position'];
				$args['paging_theme'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_theme'];
				$args['paging_size'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_size'];
				$args['paging_scroll'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_scroll'];

				$args['paging_limit'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_limit'];
				$args['paging_showFirstLast'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_showFirstLast'];
				$args['paging_showPrevNext'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_showPrevNext'];
				$args['paging_showPrevNextMore'] = $post_data[FOOGALLERY_META_SETTINGS][$template. '_paging_showPrevNextMore'];
			}

			return $args;
		}
	}
}