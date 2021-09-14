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
			$this->type = 'image'; // set the default type to image.
			$this->title = '';
			$this->caption = '';
			$this->description = '';
			$this->alt = '';
			$this->url = '';
			$this->width = 0;
			$this->height = 0;
			$this->custom_url = '';
			$this->custom_target = '';
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
			$this->custom_url = get_post_meta( $this->ID, '_foogallery_custom_url', true );
			$this->custom_target = get_post_meta( $this->ID, '_foogallery_custom_target', true );
			$this->load_attachment_image_data( $this->ID );

			do_action( 'foogallery_attachment_instance_after_load', $this, $post );
		}

		public function load_attachment_image_data( $attachment_id ) {
			$image_attributes = foogallery_get_full_size_image_data( $attachment_id );
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

		/**
		 * Returns the image source only
		 *
		 * @param array $args
		 * @return string
		 */
		public function html_img_src( $args = array() ) {
			return esc_url( apply_filters( 'foogallery_attachment_resize_thumbnail', $this->url, $args, $this ) );
		}

		/**
		 * @deprecated 1.9.24 Functions inside render-functions.php should rather be used
		 *
		 * Returns the HTML img tag for the attachment
		 * @param array $args
		 *
		 * @return string
		 */
		public function html_img( $args = array() ) {
			$attr['src'] = $this->html_img_src( $args );

			if ( ! empty( $this->alt ) ) {
				$attr['alt'] = $this->alt;
			}

			//pull any custom attributes out the args
			if ( isset( $args['image_attributes'] ) && is_array( $args['image_attributes'] ) ) {
				$attr = array_merge( $attr, $args['image_attributes'] );
			}

			//check for width and height args and add those to the image
			if ( isset( $args['width'] ) && intval( $args['width'] ) > 0 ) {
				$attr['width'] = $args['width'];
			}
			if ( isset( $args['height'] ) && intval( $args['height'] ) > 0 ) {
				$attr['height'] = $args['height'];
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
		 * @deprecated 1.9.24 Functions inside render-functions.php should rather be used
		 *
		 * Returns HTML for the attachment
		 * @param array $args
		 * @param bool $output_image
		 * @param bool $output_closing_tag
		 *
		 * @return string
		 */
		public function html( $args = array(), $output_image = true, $output_closing_tag = true ) {
			if ( empty ( $this->url ) )  {
				return '';
			}

			$arg_defaults = array(
				'link' => 'image',
				'custom_link' => $this->custom_url
			);

			$args = wp_parse_args( $args, $arg_defaults );

			$link = $args['link'];

			$img = $this->html_img( $args );

			/* 12 Apr 2016 - PLEASE NOTE
			We no longer just return the image html when "no link" option is chosen.
			It was decided that it is better to return an anchor link with no href or target attributes.
			This results in more standardized HTML output for better CSS and JS code
			*/

			if ( 'page' === $link ) {
				//get the URL to the attachment page
				$url = get_attachment_link( $this->ID );
			} else if ( 'custom' === $link ) {
				$url = $args['custom_link'];
			} else {
				$url = $this->url;
			}

			//fallback for images that might not have a custom url
			if ( empty( $url ) ) {
				$url = $this->url;
			}

			$attr = array();

			//only add href and target attributes to the anchor if the link is NOT set to 'none'
			if ( $link !== 'none' ){
				$attr['href'] = $url;
				if ( ! empty( $this->custom_target ) && 'default' !== $this->custom_target ) {
					$attr['target'] = $this->custom_target;
				}
			}

			if ( ! empty( $this->caption ) ) {
				$attr['data-caption-title'] = $this->caption;
			}

			if ( !empty( $this->description ) ) {
				$attr['data-caption-desc'] = $this->description;
			}

			$attr['data-attachment-id'] = $this->ID;

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
			$html .= '>';
			if ( $output_image ) {
				$html .= $img;
			}
			if ( $output_closing_tag ) {
				$html .= '</a>';
			};

			return apply_filters( 'foogallery_attachment_html_link', $html, $args, $this );
		}

		/**
		 * @deprecated 1.9.24 Functions inside render-functions.php should rather be used
		 *
		 * Returns generic html for captions
		 *
		 * @param $caption_content string Include title, desc, or both
		 *
		 * @return string
		 */
		public function html_caption( $caption_content ) {
			$html = '';
			$caption_html = array();
			if ( $this->caption && ( 'title' === $caption_content || 'both' === $caption_content ) ) {
				$caption_html[] = '<div class="foogallery-caption-title">' . $this->caption . '</div>';
			}
			if ( $this->description && ( 'desc' === $caption_content || 'both' === $caption_content ) ) {
				$caption_html[] = '<div class="foogallery-caption-desc">' . $this->description . '</div>';
			}

			if ( count($caption_html) > 0 ) {
				$html = '<div class="foogallery-caption"><div class="foogallery-caption-inner">';
				$html .= implode( $caption_html );
				$html .= '</div></div>';
			}

			return apply_filters( 'foogallery_attachment_html_caption', $html, $caption_content, $this );
		}
	}
}
