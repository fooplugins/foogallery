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
			$args = shortcode_atts( array(
				'id' => 0,
				'gallery' => ''
			), $atts, 'foogallery' );

            //create new instance of template engine
            $engine = new FooGallery_Template_Loader();
            $engine->render_template( $args );
		}
	}
}