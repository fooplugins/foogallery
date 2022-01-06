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

				// Add some fields to the woocommerce product.
				add_action( 'foogallery_woocommerce_product_data_panels', array( $this, 'add_button_fields_to_product' ) );

				// Save product meta.
				add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ), 10, 2 );
			}

			// Load button meta after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_button_meta' ), 10, 2 );

			// Append button HTML to the gallery output.
			add_filter( 'foogallery_attachment_html_caption', array( $this, 'add_button_html' ), 10, 3 );

			// Add button data to the json output
			add_filter( 'foogallery_build_attachment_json', array( $this, 'add_button_to_json' ), 20, 6 );

			// Override the buttons based on product metadata.
			add_filter( 'foogallery_datasource_woocommerce_build_attachment', array( $this, 'override_buttons_from_product' ), 20, 2 );
		}

		/**
		 * Save the ribbon product meta
		 *
		 * @param $id
		 * @param $post
		 *
		 * @return void
		 */
		public function save_product_meta( $id, $post ){
			if ( isset( $_POST['foogallery_buttons_clear'] ) ) {
				$foogallery_buttons_clear = wc_clean( $_POST['foogallery_buttons_clear'] );

				if ( !empty( $foogallery_buttons_clear ) ) {
					update_post_meta( $id, '_foogallery_buttons_clear', $foogallery_buttons_clear );
				}
			} else {
				delete_post_meta( $id, '_foogallery_buttons_clear' );
			}

			if ( isset( $_POST['foogallery_button_text'] ) ) {
				$additional_button_text = wc_clean( $_POST['foogallery_button_text'] );

				if ( !empty( $additional_button_text ) ) {
					update_post_meta( $id, '_foogallery_button_text', $additional_button_text );
				} else {
					delete_post_meta( $id, '_foogallery_button_text' );
				}
			}

			if ( isset( $_POST['foogallery_button_url'] ) ) {
				$foogallery_button_url = wc_clean( $_POST['foogallery_button_url'] );

				if ( !empty( $foogallery_button_url ) ) {
					update_post_meta( $id, '_foogallery_button_url', $foogallery_button_url );
				} else {
					delete_post_meta( $id, '_foogallery_button_url' );
				}
			}
		}

		/**
		 * Override the buttons based on product metadata.
		 *
		 * @param $attachment
		 * @param $product
		 *
		 * @return FooGalleryAttachment
		 */
		public function override_buttons_from_product( $attachment, $product ) {

			$clear_buttons = get_post_meta( $product->get_id(), '_foogallery_buttons_clear', true );

			if ( ! empty( $clear_buttons ) ) {
				$attachment->buttons = array();
			}

			$button_text = get_post_meta( $product->get_id(), '_foogallery_button_text', true );

			if ( ! empty( $button_text ) ) {
				$button_url = get_post_meta( $product->get_id(), '_foogallery_button_url', true );

				$attachment->buttons[] = array(
					'text'  => $button_text,
					'url'   => $button_url,
				);
			}

			return $attachment;
		}

		/**
		 * Add button fields to the product.
		 *
		 * @return void
		 */
		public function add_button_fields_to_product() {
			?>
			<p>
				<?php _e('You can override the buttons shown for this product within the gallery.', 'foogallery '); ?>
			</p>
			<?php

			woocommerce_wp_checkbox( array(
				'id'      => 'foogallery_buttons_clear',
				'value'   => get_post_meta( get_the_ID(), '_foogallery_buttons_clear', true ),
				'label'   => __( 'Clear all other buttons', 'foogallery' ),
				'desc_tip' => true,
				'description' => __( 'If this is enabled, all other buttons will be removed for the product.', 'foogallery' ),
			) );

			woocommerce_wp_text_input( array(
				'id'          => 'foogallery_button_text',
				'value'       => get_post_meta( get_the_ID(), '_foogallery_button_text', true ),
				'label'       => __( 'Additional Button Text', 'foogallery' ),
				'desc_tip'    => true,
				'description' => __( 'If this is left blank, no button will be shown.', 'foogallery' ),
			) );

			woocommerce_wp_text_input( array(
				'id'    => 'foogallery_button_url',
				'value' => get_post_meta( get_the_ID(), '_foogallery_button_url', true ),
				'label' => __( 'Additional Button URL', 'foogallery' ),
			) );
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
				$button = array(
					'text' => $button_text,
				);
				if ( !empty( $button_url ) ) {
					$button['url'] = $button_url;
				}
				if ( !isset( $foogallery_attachment->buttons ) ) {
					$foogallery_attachment->buttons = array();
				}
				$foogallery_attachment->buttons[] = $button;
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
					$button_args = array(
						'class' => isset( $button['class'] ) ? $button['class'] : '',
					);
					if ( isset( $button['url'] ) && !empty( $button['url'] ) ) {
						$button_args['href'] = $button['url'];
					}
					$button_html .= foogallery_html_opening_tag( 'a', $button_args );
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