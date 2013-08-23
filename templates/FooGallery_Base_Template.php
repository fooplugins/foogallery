<?php

/**
 *
 * FooGallery_Base_Template - Base Abstract Template Class for FooGallery templates
 *
 */

if (!class_exists('FooGallery_Base_Template')) {
	abstract class FooGallery_Base_Template {
		protected $_args;
		protected $_gallery = false;

		function __construct() {
			//setup hooks for adding js and css
		}

		/**
		 * Gets an argument passed into the Template
		 *
		 * @param $key string
		 * @param $default string
		 * @return string
		 */
		function get_arg($key, $default = '') {
			if (empty($args) || !array_key_exists($key, $this->_args)) {
				return $default;
			}

			return $this->_args[$key];
		}

		abstract function slug();

		/**
		 * Generates the class for the template
		 *
		 * @param $gallery FooGallery_Gallery
		 * @return string
		 */
		function generate_gallery_class($gallery) {
			$slug = $this->slug();
			$id = $gallery->ID;
			return apply_filters(
				"foogallery_render_{$slug}_template_class",
				"foogallery foogallery-{$id} foogallery-template-{$slug}",
				$gallery
			);
		}

		function render($args) {
			$this->_args = $args;

			//load the gallery based on the args
			$this->load_gallery($args);

			//check if we have a valid gallery
			if (!isset($this->_gallery) || $this->_gallery === false || $this->_gallery->ID === 0) return;

			//render it
			$this->render_gallery();
		}

		function load_gallery($args) {
			$id = 0;
			extract( $args );
			$id = intval($id);
			if ($id > 0) {
				$this->_gallery = FooGallery_Gallery::get_by_id( $id );
			} else if (isset($gallery)) {
				if (intval($gallery) > 0) {
					$this->_gallery = FooGallery_Gallery::get_by_id( intval( $gallery ) );
				}
				$this->_gallery = FooGallery_Gallery::get_by_slug( $gallery );
			}

			return $this->_gallery;
		}

		function render_gallery() {
			//if we have no attachments, then do nothing
			if (!$this->_gallery->has_attachments()) return;

			$this->render_gallery_start();

			foreach ($this->_gallery->attachments(false) as $id=>$attachment) {
				$this->render_gallery_attachment($id, $attachment);
			}

			$this->render_gallery_end();
		}

		function render_gallery_start() {
			$slug = $this->slug();
			do_action(
				"foogallery_render_{$slug}_template_start",
				$this->_gallery
			);
		}

		function render_gallery_end() {
			$slug = $this->slug();
			do_action(
				"foogallery_render_{$slug}_template_end",
				$this->_gallery
			);
		}

		/**
		 * @param $id int
		 * @param $attachment array
		 */
		function render_gallery_attachment($id, $attachment) {
			$slug = $this->slug();
			do_action(
				"foogallery_render_{$slug}_template_attachment",
				$id,
				$attachment,
				$this->_gallery
			);
		}
	}
}