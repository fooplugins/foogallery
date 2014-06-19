<?php
/**
 * Class FooGalleryAttachment
 *
 * An easy to use wrapper class for a FooGallery Attachment
 */
if (!class_exists('FooGalleryAttachment')) {

    class FooGalleryAttachment extends stdClass {
		/**
		 * private constructor
		 *
		 * @param null $post
		 */
		private function __construct($post = NULL) {
			$this->set_defaults();

			if ($post !== NULL) {
				$this->load($post);
			}
		}

		/**
		 *  Sets the default when a new gallery is instantiated
		 */
		private function set_defaults() {
			$this->_post = NULL;
			$this->ID = 0;
			$this->title = '';
			$this->caption = '';
			$this->description = '';
			$this->alt = '';
			$this->url = '';
			$this->width = 0;
			$this->height = 0;
		}

		/**
		 * private attachment load function
		 * @param $post
		 */
		private function load($post) {
			$this->_post = $post;
			$this->ID = $post->ID;
			$this->title = $post->post_title;
			$this->caption = $post->post_excerpt;
			$this->description = $post->post_content;
			$this->alt = get_post_meta( $this->ID, '_wp_attachment_image_alt', true );
			$image_attributes = wp_get_attachment_image_src( $this->ID, 'full' );
			if ( $image_attributes ) {
				$this->url = $image_attributes[0];
				$this->width = $image_attributes[1];
				$this->height = $image_attributes[2];
			}
		}

		/**
		 * Static function to load a FooGalleryAttachment instance by passing in a post object
		 * @static
		 *
		 * @param $post
		 *
		 * @return FooGalleryAttachment
		 */
		public static function get($post) {
			return new self($post);
		}

		/**
		 * Returns HTML for the attachment
		 * @param string $size
		 * @param string $link
		 *
		 * @return string
		 */
		function html( $size = 'thumbnail', $link = 'image' ) {
			if ( 0 === $this->ID ) {
				return '';
			}

			add_filter( 'wp_get_attachment_image_attributes', array($this, 'filter_attachment_image_attributes'), 1, 2 );

			$img = wp_get_attachment_image( $this->ID, $size );

			remove_filter( 'wp_get_attachment_image_attributes', array($this, 'filter_attachment_image_attributes'), 1 );

			//if there is no link, then just return the image tag
			if ( 'none' === $link ) {
				return $img;
			}

			if ( 'page' === $link ) {
				//get the URL to the attachment page
				$url = get_attachment_link( $this->ID );
			} else {
				$url = $this->url;
			}

			return apply_filters( 'foogallery_attachment_html', "<a title='{$this->title}' href='{$url}'>{$img}</a>", $this, $size, $link );
		}

		function filter_attachment_image_attributes($attr) {

			if ( !empty( $this->alt ) ) {
				$attr['alt'] = $this->alt;
			}

			if ( !empty( $this->title ) ) {
				$attr['title'] = $this->title;
			}

			return $attr;
		}
    }
}