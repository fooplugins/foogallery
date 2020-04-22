<?php
/**
 * FooGallery Pro Instagram Filters Class
 */
if ( ! class_exists( 'FooGallery_Pro_Instagram_Filters' ) ) {

	class FooGallery_Pro_Instagram_Filters {

		function __construct() {
			if ( is_admin() ) {
				//add extra fields to the templates
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_instagram_fields' ), 10, 2 );
			}

			add_filter( 'foogallery_build_class_attribute', array( $this, 'append_instagram_filter_class' ), 10, 2 );
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 * @param $template
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data, $template ) {
			if ( array_key_exists( $template . '_instagram', $post_data[FOOGALLERY_META_SETTINGS] ) ) {
				$args['instagram'] = $post_data[FOOGALLERY_META_SETTINGS][$template . '_instagram'];
			}
			return $args;
		}

		/**
		 * Add fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_instagram_fields( $fields, $template ) {
			$fields[] = array(
				'id'       => 'instagram',
				'title'    => __( 'Instagram Filter', 'foogallery' ),
				'desc'     => __( 'Apply an Instagram Filter to the images in your gallery.', 'foogallery' ),
				'section'  => __( 'Appearance', 'foogallery' ),
				'type'     => 'select',
				'default'  => '',
				'choices'  => apply_filters(
					'foogallery_gallery_template_instagram_choices', array(
						''         => __( 'None', 'foogallery' ),
						'fg-filter-1977'   => __( '1977', 'foogallery' ),
						'fg-filter-amaro' => __( 'Amaro', 'foogallery' ),
						'fg-filter-brannan' => __( 'Brannan', 'foogallery' ),
						'fg-filter-clarendon' => __( 'Clarendon', 'foogallery' ),
						'fg-filter-earlybird' => __( 'Earlybird', 'foogallery' ),
						'fg-filter-lofi' => __( 'Lo-Fi', 'foogallery' ),
						'fg-filter-poprocket' => __( 'PopRocket', 'foogallery' ),
						'fg-filter-reyes' => __( 'Reyes', 'foogallery' ),
						'fg-filter-toaster' => __( 'Toaster', 'foogallery' ),
						'fg-filter-walden' => __( 'Walden', 'foogallery' ),
						'fg-filter-xpro2' => __( 'X-Pro 2', 'foogallery' ),
						'fg-filter-xtreme' => __( 'Xtreme', 'foogallery' ),
					)
				),
				'row_data' => array(
					'data-foogallery-change-selector'       => 'select',
					'data-foogallery-preview'               => 'shortcode'
				)
			);

			return $fields;
		}

		/**
		 * Adds the instagram filter onto the figure for the attachment
		 *
		 * @param $classes
		 * @param $foogallery
		 *
		 * @return array
		 */
		function append_instagram_filter_class( $classes, $foogallery ) {

			$filter = foogallery_gallery_template_setting( 'instagram', '' );

			if ( $filter !== '' ) {
				$classes[] = $filter;
			}

			return $classes;
		}
	}
}