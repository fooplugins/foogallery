<?php
/**
 * Default FooGallery Template class
 *
 */

if (!class_exists('FooGallery_DefaultTemplate')) {
	class FooGallery_DefaultTemplate extends FooGallery_Base_Template {
		function slug() {
			return 'default';
		}

		function __construct() {
			add_action('foogallery_render_default_template_start', array($this, 'gallery_start'));
			add_action('foogallery_render_default_template_end', array($this, 'gallery_end'));
			add_action('foogallery_render_default_template_attachment', array($this, 'gallery_attachment'), 10, 3);
		}

		function gallery_start($gallery) {
			$class = $this->generate_gallery_class($gallery);
			echo '<div class="' . $class . '">';
		}

		function gallery_end() {
			echo '</div>';
		}

		function gallery_attachment($id) {
			$size = $this->get_arg('size', 'thumbnail');
            $link_to_image = $this->_gallery->link_to_image;
			echo wp_get_attachment_link($id, $size, !$link_to_image) . "\n";
		}
	}
}