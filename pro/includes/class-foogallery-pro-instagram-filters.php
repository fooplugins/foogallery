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

			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'append_instagram_filter' ), 10, 3 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments', array( $this, 'preview_arguments' ), 10, 3 );

			add_action( 'foogallery_loaded_template', array( $this, 'enqueue_instagram_dependencies') );
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
			$args['instagram'] = $post_data[FOOGALLERY_META_SETTINGS][$template . '_instagram'];
			return $args;
		}

		/**
		 * Enqueue the stylesheet file for instrgram filters
		 *
		 * @param $foogallery FooGallery
		 */
		function enqueue_instagram_dependencies( $foogallery ) {
			if ( $foogallery ) {
				$filter = foogallery_gallery_template_setting( 'instagram', '' );

				if ( '' !== $filter ) {
					$css = FOOGALLERY_PRO_URL . 'css/instagram.min.css';
					foogallery_enqueue_style( "foogallery-instagram", $css, array(), FOOGALLERY_VERSION );
				}
			}
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
						'filter-1977'   => __( '1977', 'foogallery' ),
						//'filter-aden' => __( 'Aden', 'foogallery' ),
						'filter-amaro' => __( 'Amaro', 'foogallery' ),

						'filter-ashby' => __( 'Ashby', 'foogallery' ),
						'filter-brannan' => __( 'Brannan', 'foogallery' ),
						//'filter-brooklyn' => __( 'Brooklyn', 'foogallery' ),
						//'filter-charmes' => __( 'Charmes', 'foogallery' ),

						'filter-clarendon' => __( 'Clarendon', 'foogallery' ),
						//'filter-crema' => __( 'Crema', 'foogallery' ),
						'filter-dogpatch' => __( 'Dogpatch', 'foogallery' ),
						'filter-earlybird' => __( 'Earlybird', 'foogallery' ),

						'filter-gingham' => __( 'Gingham', 'foogallery' ),
						//'filter-ginza' => __( 'Ginza', 'foogallery' ),
						//'filter-hefe' => __( 'Hefe', 'foogallery' ),
						'filter-helena' => __( 'Helena', 'foogallery' ),

						//'filter-hudson' => __( 'Hudson', 'foogallery' ),
						//'filter-inkwell' => __( 'Inkwell', 'foogallery' ),
						//'filter-kelvin' => __( 'Kelvin', 'foogallery' ),
						'filter-juno' => __( 'Juno', 'foogallery' ),

						//'filter-lark' => __( 'Lark', 'foogallery' ),
						'filter-lofi' => __( 'Lo-Fi', 'foogallery' ),
						//'filter-kelvin' => __( 'Kelvin', 'foogallery' ),
						'filter-juno' => __( 'Juno', 'foogallery' ),

						//'filter-ludwig' => __( 'Ludwig', 'foogallery' ),
						'filter-maven' => __( 'Maven', 'foogallery' ),
						//'filter-mayfair' => __( 'Mayfair', 'foogallery' ),
						'filter-moon' => __( 'Moon', 'foogallery' ),

						//'filter-nashville' => __( 'Nashville', 'foogallery' ),
						//'filter-perpetua' => __( 'Perpetua', 'foogallery' ),
						'filter-poprocket' => __( 'Poprocket', 'foogallery' ),
						'filter-reyes' => __( 'Reyes', 'foogallery' ),

						//'filter-vesper' => __( 'Vesper', 'foogallery' ),
						//'filter-walden' => __( 'Walden', 'foogallery' ),
						'filter-willow' => __( 'Willow', 'foogallery' ),
						'filter-xpro-ii' => __( 'X-Pro II', 'foogallery' ),
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
		 * @param $class
		 * @param $foogallery_attachment
		 * @param $args
		 *
		 * @return string
		 */
		function append_instagram_filter( $attr, $args, $foogallery_attachment ) {
			global $current_foogallery;

			$filter = foogallery_gallery_template_setting( 'instagram', '' );

			if ( $filter !== '' ) {
				if ( array_key_exists( 'class', $attr ) ) {
					$attr['class'] .= ' ' . $filter;
				} else {
					$attr['class'] = $filter;
				}
			}

			return $attr;
		}
	}
}