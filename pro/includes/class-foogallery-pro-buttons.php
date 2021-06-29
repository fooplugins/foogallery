<?php
/**
 * FooGallery Pro Buttons Class
 */
if ( ! class_exists( 'FooGallery_Pro_Buttons' ) ) {

	class FooGallery_Pro_Buttons {

		function __construct() {
			if ( is_admin() ) {
				// Add attachment custom fields.
				add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ), 40 );
			}

			// Load button meta after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_button_meta' ), 10, 2 );

			// Append button HTML to the gallery output.
			add_filter( 'foogallery_attachment_html_caption', array( $this, 'add_button_html' ), 10, 3 );

			// Add button data to the json output
			add_filter( 'foogallery_build_attachment_json', array( $this, 'add_button_to_json' ), 20, 6 );
		}

		/**
		 * Loads any extra button data for an attachment.
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function load_button_meta( $foogallery_attachment, $post ) {
			$button_text = get_post_meta( $post->ID, '_foogallery_button_text', true );
			if ( !empty( $button_text ) ) {
				$button_url = get_post_meta( $post->ID, '_foogallery_button_url', true );
				if ( !empty( $button_url ) ) {
					$foogallery_attachment->buttons   = array();
					$foogallery_attachment->buttons[] = array(
						'text' => $button_text,
						'url'  => $button_url,
					);
				}
			}
		}

		/**
		 * Builds up button HTML and adds it to the output.
		 *
		 * @param $html
		 * @param $foogallery_attachment
		 * @param $args
		 *
		 * @return mixed
		 */
		public function add_button_html( $html, $foogallery_attachment, $args ) {
			if ( isset( $foogallery_attachment->buttons ) && is_array( $foogallery_attachment->buttons ) ) {
				$button_html = '<div class="fg-caption-buttons">';
				foreach ( $foogallery_attachment->buttons as $button ) {
					$button_html .= foogallery_html_opening_tag( 'a', array(
						'class' => isset( $button['class'] ) ? $button['class'] : '',
						'href' => isset( $button['url'] ) ? $button['url'] : '',
					) );
					$button_html .= isset( $button['text'] ) ? esc_html( $button['text'] ) : '';
					$button_html .= '</a>';
				}
				$button_html .= '</div>';
				$html = str_replace( '</div></figcaption>',  $button_html . '</div></figcaption>', $html );
			}
			return $html;
		}

		/**
		 * Add the button data to the json object.
		 *
		 * @param StdClass $json_object
		 * @param FooGalleryAttachment $foogallery_attachment
		 * @param array $args
		 * @param array $anchor_attributes
		 * @param array $image_attributes
		 * @param array $captions
		 *
		 * @return mixed
		 */
		public function add_button_to_json(  $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions ) {
			if ( isset( $foogallery_attachment->buttons ) && is_array( $foogallery_attachment->buttons ) ) {
				$json_object->buttons = $foogallery_attachment->buttons;
			}

			return $json_object;
		}

		/**
		 * Add button specific custom fields.
		 *
		 * @uses "foogallery_attachment_custom_fields" filter
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public function attachment_custom_fields( $fields ) {
			$fields['foogallery_button_text']  = array(
				'label'       => __( 'Button Text', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);

			$fields['foogallery_button_url']  = array(
				'label'       => __( 'Button URL', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);

			return $fields;
		}
	}
}