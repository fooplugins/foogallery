<?php
/*
 * FooGallery Shortcodes
 */

if ( ! class_exists( 'FooGallery_Shortcodes' ) ) {

	class FooGallery_Shortcodes {

		function __construct() {
			add_action( 'init', array( $this, 'init_shortcode' ) );
			add_action( 'foogallery_load_template', array( $this, 'handle_lightbox_field' ) );
			add_action( 'foogallery_loaded_template', array( $this, 'render_custom_css' ) );
		}

		function init_shortcode() {
			add_shortcode( foogallery_gallery_shortcode_tag(), array( $this, 'render_foogallery_shortcode' ) );
		}

		function render_foogallery_shortcode( $atts ) {

			$args = wp_parse_args( $atts, array(
				'id'      => 0,
				'gallery' => '',
			) );

			$args = apply_filters( 'foogallery_shortcode_atts', $args );

			//create new instance of template engine
			$engine = new FooGallery_Template_Loader();

			ob_start();

			$engine->render_template( $args );

			$output_string = ob_get_contents();
			ob_end_clean();
			return $output_string;
		}

		/**
		 * Handle a gallery that has a lightbox. This allows us to include any scripts or CSS that is needed for the lightbox
		 *
		 * @param $gallery FooGallery
		 */
		function handle_lightbox_field( $gallery ) {
			if ( $gallery->gallery_template_has_field_of_type( 'lightbox' ) ) {
				$lightbox = foogallery_gallery_template_setting( 'lightbox' );

				if ( false != $lightbox ) {
					do_action( "foogallery_template_lightbox-{$lightbox}", $gallery );
				}
			}
		}

		function render_custom_css( $foogallery ) {
			if ( !empty( $foogallery->custom_css ) ) {
				echo '<style type="text/css">';
				echo $foogallery->custom_css;
				echo '</style>';
			}
		}
	}
}
