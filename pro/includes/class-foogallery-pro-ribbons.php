<?php
/**
 * FooGallery Pro Ribbons Class
 */
if ( ! class_exists( 'FooGallery_Pro_Ribbons' ) ) {

	class FooGallery_Pro_Ribbons {

		function __construct() {
			if ( is_admin() ) {
				// Add attachment custom fields.
				add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ), 40 );

				// Add some fields to the woocommerce product.
				add_action( 'foogallery_woocommerce_product_data_panels', array( $this, 'add_ribbon_fields_to_product' ) );

				// Save product meta.
				add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ), 10, 2 );
			}

			// Load ribbon meta after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_ribbon_meta' ), 10, 2 );

			// Append ribbon HTML to the gallery output.
			add_filter( 'foogallery_attachment_html_item_opening', array( $this, 'add_ribbon_html' ), 10, 3 );

			// Add ribbon data to the json output.
			add_filter( 'foogallery_build_attachment_json', array( $this, 'add_ribbon_to_json' ), 20, 6 );

			// Override the ribbon based on product metadata.
			add_filter( 'foogallery_datasource_woocommerce_build_attachment', array( $this, 'override_ribbon_from_product' ), 20, 2 );
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
			if ( isset( $_POST['foogallery_ribbon'] ) ) {
				$override_ribbon_type = wc_clean( $_POST['foogallery_ribbon'] );

				if ( !empty( $override_ribbon_type ) ) {
					update_post_meta( $id, '_foogallery_ribbon', $override_ribbon_type );
				} else {
					delete_post_meta( $id, '_foogallery_ribbon' );
				}
			}

			if ( isset( $_POST['foogallery_ribbon_text'] ) ) {
				$override_ribbon_text = wc_clean( $_POST['foogallery_ribbon_text'] );

				if ( !empty( $override_ribbon_text ) ) {
					update_post_meta( $id, '_foogallery_ribbon_text', $override_ribbon_text );
				} else {
					delete_post_meta( $id, '_foogallery_ribbon_text' );
				}
			}
		}

		/**
		 * Override the ribbon based on product metadata.
		 *
		 * @param $attachment
		 * @param $product
		 *
		 * @return FooGalleryAttachment
		 */
		public function override_ribbon_from_product( $attachment, $product ) {

			$override_ribbon_type = get_post_meta( $product->get_id(), '_foogallery_ribbon', true );

			if ( ! empty( $override_ribbon_type ) ) {
				$attachment->ribbon_type = $override_ribbon_type;
			}

			$override_ribbon_text = get_post_meta( $product->get_id(), '_foogallery_ribbon_text', true );

			if ( ! empty( $override_ribbon_text ) ) {
				$attachment->ribbon_text = $override_ribbon_text;
			}

			return $attachment;
		}

		/**
		 * Add ribbon fields to the product
		 *
		 * @return void
		 */
		public function add_ribbon_fields_to_product() {
			?>
			<p>
				<?php _e('By default, products that are on sale, will show a colorful ribbon to attract the visitors attention. You can override the default ribbon type and text for this product.', 'foogallery '); ?>
			</p>
			<?php

			$ribbon_choices = self::get_ribbon_choices();
			$ribbon_choices[''] = __( 'Do not override', 'foogallery' );

			woocommerce_wp_select( array(
				'id'          => 'foogallery_ribbon',
				'value'       => get_post_meta( get_the_ID(), '_foogallery_ribbon', true ),
				'label'       => __( 'Override Ribbon', 'foogallery' ),
				'options'     => $ribbon_choices,
			) );

			woocommerce_wp_text_input( array(
				'id'                => 'foogallery_ribbon_text',
				'value'             => get_post_meta( get_the_ID(), '_foogallery_ribbon_text', true ),
				'label'             => __( 'Override Ribbon Text', 'foogallery' ),
			) );
		}

		/**
		 * Loads any extra ribbon data for an attachment.
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function load_ribbon_meta( $foogallery_attachment, $post ) {
			$ribbon_type = get_post_meta( $post->ID, '_foogallery_ribbon', true );
			if ( !empty( $ribbon_type ) ) {
				$foogallery_attachment->ribbon_type = $ribbon_type;
				$foogallery_attachment->ribbon_text = get_post_meta( $post->ID, '_foogallery_ribbon_text', true );
				$foogallery_attachment->ribbon_override = true;
			}
		}

		/**
		 * Builds up ribbon HTML and adds it to the output.
		 *
		 * @param $html
		 * @param $foogallery_attachment
		 * @param $args
		 *
		 * @return mixed
		 */
		public function add_ribbon_html( $html, $foogallery_attachment, $args ) {
			if ( isset( $foogallery_attachment->ribbon_type ) && isset( $foogallery_attachment->ribbon_text ) ) {
				//Add the ribbon HTML!!!
				$ribbon_html = '<div class="' . $foogallery_attachment->ribbon_type . '"><span>' . esc_html( $foogallery_attachment->ribbon_text ) . '</span></div>';
				$html = str_replace( '<figure class=',  $ribbon_html . '<figure class=', $html );
			}
			return $html;
		}

		/**
		 * Add the ribbon data to the json object.
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
		public function add_ribbon_to_json(  $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions ) {
			if ( isset( $foogallery_attachment->ribbon_type ) && isset( $foogallery_attachment->ribbon_text ) ) {
				$json_object->ribbon = array(
					'type' => $foogallery_attachment->ribbon_type,
                    'text' => $foogallery_attachment->ribbon_text
				);
			}

			return $json_object;
		}

		/**
		 * Add Ribbon specific custom fields.
		 *
		 * @uses "foogallery_attachment_custom_fields" filter
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public function attachment_custom_fields( $fields ) {
			$fields['foogallery_ribbon']  = array(
				'label'       => __( 'Ribbon Type', 'foogallery' ),
				'input'       => 'select',
				'application' => 'image/foogallery',
				'options'     => self::get_ribbon_choices(),
			);

			$fields['foogallery_ribbon_text']  = array(
				'label'       => __( 'Ribbon Text', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);

			return $fields;
		}

		/**
		 * Returns the list of ribbon choices.
		 *
		 * @return array
		 */
		public static function get_ribbon_choices() {
			return array(
				''            => __( 'None', 'foogallery' ),
				'fg-ribbon-5' => __( 'Type 1 (top-right, diagonal, green)', 'foogallery' ),
				'fg-ribbon-3' => __( 'Type 2 (top-left, small, blue)', 'foogallery' ),
				'fg-ribbon-4' => __( 'Type 3 (top, full-width, yellow)', 'foogallery' ),
				'fg-ribbon-6' => __( 'Type 4 (top-right, rounded, red)', 'foogallery' ),
				'fg-ribbon-2' => __( 'Type 5 (top-left, medium, pink)', 'foogallery' ),
				'fg-ribbon-1' => __( 'Type 6 (top-left, vertical, orange)', 'foogallery' ),
                'fg-ribbon-7' => __( 'Type 7 (bottom, full-width, grey)', 'foogallery' ),
			);
		}
	}
}