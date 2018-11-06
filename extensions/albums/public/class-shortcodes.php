<?php
/*
 * FooGallery Album Shortcode
 */

if ( ! class_exists( 'FooGallery_Album_Shortcodes' ) ) {

	class FooGallery_Album_Shortcodes {

		function __construct() {
			add_action( 'foogallery_loaded_album_template', array( $this, 'render_custom_css' ) );
			add_shortcode( foogallery_album_shortcode_tag(), array( $this, 'render_foogallery_album_shortcode' ) );
		}

		function render_foogallery_album_shortcode( $atts ) {

			$args = wp_parse_args( $atts, array(
				'id'    => 0,
				'album' => '',
			) );

			$args = apply_filters( 'foogallery-album_shortcode_atts', $args );

			//create new instance of template engine
			$engine = new FooGallery_Album_Template_Loader();

			ob_start();

			$engine->render_template( $args );

			$output_string = ob_get_contents();
			ob_end_clean();
			return $output_string;
		}

		function render_custom_css( $foogallery_album ) {
			if ( !empty( $foogallery_album->custom_css ) ) {
				echo '<style type="text/css">';
				echo $foogallery_album->custom_css;
				echo '</style>';
			}
		}
	}
}
