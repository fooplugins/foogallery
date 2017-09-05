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
			add_action( 'foogallery_foogallery_instance_after_load', array( $this, 'determine_paging' ), 10, 2 );

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
					'title'   => __( 'Paging', 'foogallery' ),
					'desc'    => __( 'Add paging to a large gallery.', 'foogallery' ),
					'section' => __( 'Paging', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'choices' => apply_filters( 'foogallery_gallery_template_paging_choices', array(
						''  => __( 'None', 'foogallery' ),
						'dots'   => __( 'Dots', 'foogallery' ),
						'pagination'   => __( 'Pagination', 'foogallery' ),
						'infinite'   => __( 'Infinite Scroll', 'foogallery' ),
						'loadMore'   => __( 'Load More', 'foogallery' )
					) ),
					'row_data'=> array(
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
		 * @param $post
		 */
		function determine_paging( $foogallery, $post ) {
			//always disable paging by default
			$paging = $foogallery->get_setting( 'paging_type', '' ) !== '';

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
				global $current_foogallery_arguments;
				if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments['paging'] ) ) {
					$paging = $current_foogallery_arguments['paging'];
				} else {
					$paging = $gallery->get_setting( 'paging_type', '' );
				}

				$options['paging'] = array(
					'type' => $paging,
					'theme' => 'fg-light',
					'size' => 3,
					'position' => 'both',
					'scrollToTop' => true
				);
			}
			return $options;
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
			$post_key = $template. '_paging_type';

			//check the template supports paging
			if ( $template_data && array_key_exists( 'paging_support', $template_data ) && true === $template_data['paging_support'] ) {
				$args['paging'] = $post_data[FOOGALLERY_META_SETTINGS][$post_key];
			}

			return $args;
		}
	}
}