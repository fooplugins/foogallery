<?php
/*
 * FooGallery Shortcodes
 */

if ( !class_exists( 'FooGallery_Shortcodes' ) ) {

	class FooGallery_Shortcodes {

		function __construct() {
			add_shortcode( 'foogallery', array($this, 'render_foogallery_shortcode') );
		}

		function render_foogallery_shortcode($atts) {

			$args = wp_parse_args( $atts, array(
				'id' => 0,
				'gallery' => ''
			) );

			$args = apply_filters( 'foogallery_shortcode_atts', $args );

            //create new instance of template engine
            $engine = new FooGallery_Template_Loader();
            $engine->render_template( $args );
		}
	}
}