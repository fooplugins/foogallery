<?php
/**
 * Default FooGallery Template class
 *
 */

if (!class_exists('FooGallery_Template_Default')) {
	class FooGallery_Template_Default {

		function __construct() {
			add_action('foogallery_template_js-default', array($this, 'load_js'));
            add_action('foogallery_template_css-default', array($this, 'load_css'));
		}

        function load_js() {
            $url = plugins_url("js/default.js", __FILE__);
            wp_enqueue_script("foogallery-js-default", $url);
        }

        function load_css() {
            $url = plugins_url("css/default.css", __FILE__);
            wp_enqueue_style("foogallery-css-default", $url);
        }
	}

	new FooGallery_Template_Default();
}