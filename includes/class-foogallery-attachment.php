<?php
/**
 * Class FooGalleryAttachment
 *
 * An easy to use wrapper class for a FooGallery Attachment
 */
if ( ! class_exists( 'FooGalleryAttachment' ) ) {

	class FooGalleryAttachment extends stdClass {
		/**
		 * public constructor
		 *
		 * @param null $post
		 */
		public function __construct( $post = null ) {
			$this->set_defaults();

			if ( $post !== null ) {
				$this->load( $post );
			}
		}

		/**
		 *  Sets the default when a new gallery is instantiated
		 */
		private function set_defaults() {
			$this->_post = null;
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
		private function load( $post ) {
			$this->_post = $post;
			$this->ID = $post->ID;
			$this->title = trim( $post->post_title );
			$this->caption = trim( $post->post_excerpt );
			$this->description = trim( $post->post_content );
			$this->alt = trim( get_post_meta( $this->ID, '_wp_attachment_image_alt', true ) );
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
		public static function get( $post ) {
			return new self( $post );
		}

		/**
		 * Static function to load a FooGalleryAttachment instance by passing in an attachment_id
		 * @static
		 *
		 * @param $attachment_id
		 *
		 * @return FooGalleryAttachment
		 */
		public static function get_by_id( $attachment_id ) {
			$post = get_post( $attachment_id );
			return new self( $post );
		}

		function html_img( $args = array() ) {
			$attr['src'] = apply_filters( 'foogallery_attachment_resize_thumbnail', $this->url, $args, $this );

			if ( ! empty( $this->alt ) ) {
				$attr['alt'] = $this->alt;
			}

			//pull any custom attributes out the args
			if ( isset( $args['image_attributes'] ) && is_array( $args['image_attributes'] ) ) {
				$attr = array_merge( $attr, $args['image_attributes'] );
			}

			$attr = apply_filters( 'foogallery_attachment_html_image_attributes', $attr, $args, $this );
			$attr = array_map( 'esc_attr', $attr );
			$html = '<img ';
			foreach ( $attr as $name => $value ) {
				$html .= " $name=" . '"' . $value . '"';
			}
			$html .= ' />';

			return apply_filters( 'foogallery_attachment_html_image', $html, $args, $this );
		}

		/**
		 * Returns HTML for the attachment
		 * @param array $args
		 *
		 * @return string
		 */
		function html( $args = array() ) {
			if ( empty ( $this->url ) )  {
				return '';
			}

			$arg_defaults = array(
				'link' => 'image',
				'custom_link' => '#',
			);

			$args = wp_parse_args( $args, $arg_defaults );

			$link = $args['link'];

			$img = $this->html_img( $args );

			//if there is no link, then just return the image tag
			if ( 'none' === $link ) {
				return $img;
			}

			if ( 'page' === $link ) {
				//get the URL to the attachment page
				$url = get_attachment_link( $this->ID );
			} else if ( 'custom' === $link ) {
				$url = $args['custom_link'];
			} else {
				$url = $this->url;
			}

			$attr['href'] = $url;

			if ( ! empty( $this->caption ) ) {
				$attr['data-caption-title'] = $this->caption;
			}

			if ( !empty( $this->description ) ) {
				$attr['data-caption-desc'] = $this->description;
			}

			//pull any custom attributes out the args
			if ( isset( $args['link_attributes'] ) && is_array( $args['link_attributes'] ) ) {
				$attr = array_merge( $attr, $args['link_attributes'] );
			}

			$attr = apply_filters( 'foogallery_attachment_html_link_attributes', $attr, $args, $this );
			$attr = array_map( 'esc_attr', $attr );
			$html = '<a ';
			foreach ( $attr as $name => $value ) {
				$html .= " $name=" . '"' . $value . '"';
			}
			$html .= ">{$img}</a>";

			return apply_filters( 'foogallery_attachment_html_link', $html, $args, $this );
		}
	}
}