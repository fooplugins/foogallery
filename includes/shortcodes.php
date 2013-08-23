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
			global $foogallery_templates;

			$template = '';

			$args = shortcode_atts( array(
				'id' => 0,
				'gallery' => '',
				'template' => 'default'
			), $atts, 'foogallery' );

			extract( $args );

			//get template instance
			if ( array_key_exists( $template, $foogallery_templates ) ) {
				$template_instance = $foogallery_templates[$template]['class'];
				$template_instance->render( $args );
			}
		}
	}
}