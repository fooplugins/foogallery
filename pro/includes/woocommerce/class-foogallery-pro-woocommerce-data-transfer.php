<?php
/**
 * FooGallery class for WooCommerce Data Transfer Feature
 * Where data from the attachment is transferred to the cart and order items.
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_Pro_Woocommerce_Master_Product' ) ) {
	/**
	 * Class FooGallery_Pro_Woocommerce_Data_Transfer
	 */
	class FooGallery_Pro_Woocommerce_Data_Transfer {

		/**
		 * Constructor for the class
		 *
		 * Sets up all the appropriate hooks and actions
		 */
		public function __construct() {

			// Load product data after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_master_product_data' ), 20, 2 );

			// Add custom data to the cart when added.
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 30, 2 );

			// Display the variable attributes in the cart.
			add_filter( 'woocommerce_get_item_data', array( $this, 'display_variable_item_data' ), 10, 2 );

			// Override the cart thumbnail image.
			add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'adjust_cart_thumbnail' ), 10, 3 );

			// Override the cart permalink.
			add_filter( 'woocommerce_cart_item_permalink', array( $this, 'adjust_cart_permalink' ), 10, 3 );

			// Override the cart name.
			add_filter( 'woocommerce_cart_item_name', array( $this, 'adjust_cart_name' ), 10, 3 );

			// Add order line item data.
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'adjust_order_item' ), 20, 4 );

			// Override the order item permalink.
			add_filter( 'woocommerce_order_item_permalink', array( $this, 'adjust_order_item_permalink' ), 10, 3 );

			if ( is_admin() ) {
				// Add extra fields to the templates.
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_more_ecommerce_fields' ), 40, 2 );

				// Override the order item thumbnail in admin.
				add_filter( 'woocommerce_admin_order_item_thumbnail',  array( $this, 'adjust_order_item_thumbnail' ), 10, 3 );

				// Override order meta keys.
				add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'adjust_order_item_display_meta_key' ), 10, 3 );

				add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'adjust_order_item_display_meta_value' ), 10, 3 );
			}
		}

		/**
		 * Override the keys for metadata within the order.
		 *
		 * @param $display_key
		 * @param $meta
		 * @param $order_item
		 *
		 * @return mixed|string|void
		 */
		public function adjust_order_item_display_meta_key( $display_key, $meta, $order_item ) {
			if ( '_foogallery_id' === $meta->key ) {
				$display_key = __( 'FooGallery', 'foogallery' );
			} else if ( '_foogallery_attachment_id' === $meta->key ) {
				$display_key = __( 'Attachment', 'foogallery' );
			} else if ( '_foogallery_attachment_url' === $meta->key ) {
				$display_key = __( 'Attachment Public URL', 'foogallery' );
			}
			return $display_key;
		}

		/**
		 * Override the keys for metadata within the order.
		 *
		 * @param $display_value
		 * @param $meta
		 * @param $order_item
		 *
		 * @return mixed|string|void
		 */
		public function adjust_order_item_display_meta_value( $display_value, $meta, $order_item ) {
			if ( '_foogallery_id' === $meta->key ) {
				$display_value = get_edit_post_link( $display_value );
			} else if ( '_foogallery_attachment_id' === $meta->key ) {
				$display_value = get_edit_post_link( $display_value );
			}
			return $display_value;
		}

		/**
		 * Adjust the order item to store relevant info about the attachment.
		 *
		 * @param WC_Order_Item $item
		 * @param $cart_item_key
		 * @param $values
		 * @param $order
		 */
		public function adjust_order_item( $item, $cart_item_key, $values, $order ) {
			if ( isset( $values['foogallery_id'] ) ) {
				$foogallery_id = intval( $values['foogallery_id'] );
				$attachment_id = intval( $values['foogallery_attachment_id'] );

				$item->add_meta_data( '_foogallery_id', $foogallery_id );
				$item->add_meta_data( '_foogallery_attachment_id', $attachment_id );
				$item->add_meta_data( '_foogallery_attachment_url', wp_get_attachment_url( $attachment_id ) );

				$name = $this->get_product_name( '', $foogallery_id, $attachment_id );
				if ( ! empty( $name ) ) {
					$item->set_props( array(
							'name' => $name
						) );
				}
			}
		}


		/**
		 * Adjust the order item thumbnail to use the attachment thumb
		 *
		 * @param $image
		 * @param $item_id
		 * @param $item
		 *
		 * @return mixed|string
		 */
		public function adjust_order_item_thumbnail( $image, $item_id, $item ) {
			$foogallery_attachment_id = $item->get_meta( '_foogallery_attachment_id' );
			if ( !empty( $foogallery_attachment_id ) ) {
				$image = wp_get_attachment_image( $foogallery_attachment_id );
			}

			return $image;
		}

		/**
		 * Override the order item permalink to be blank, so that you cannot click on an item in the cart to view it.
		 *
		 * @param $permalink
		 * @param $item
		 * @param $order
		 *
		 * @return mixed|string
		 */
		public function adjust_order_item_permalink( $permalink, $item, $order ) {
			$foogallery_attachment_id = $item->get_meta( '_foogallery_attachment_id' );
			if ( !empty( $foogallery_attachment_id ) ) {
				// Do not return a permalink, which means the product cannot be viewed from the order.
				// If they went to the product, they would not see the correct image.
				return '';
			}

			return $permalink;
		}

		/**
		 * Add protection fields to all gallery templates
		 *
		 * @param array  $fields The fields to override.
		 * @param string $template The gallery template.
		 *
		 * @return array
		 */
		public function add_more_ecommerce_fields( $fields, $template ) {

			$new_fields = array();

			if ( FooGallery_Pro_Woocommerce::is_woocommerce_activated() ) {

				$new_fields[] = array(
					'id'      => 'ecommerce_transfer_mode_info',
					'title'   => __( 'Transfer Mode Info', 'foogallery' ),
					'desc'    => __( 'You can choose to transfer info from the attachment to the linked product when it is added to the cart and ordered. This works best when you want to use a master product for all items in your gallery.', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Advanced', 'foogallery' ) ),
					'type'    => 'help',
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_transfer_mode',
					'title'    => __( 'Transfer Mode', 'foogallery' ),
					'desc'     => __( 'When the product is added to the cart or ordered, details from the attachment can be transferred to the cart and order items.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'radio',
					'spacer'   => '<span class="spacer"></span>',
					'default'  => '',
					'choices'  => array(
						'' => __( 'Do Nothing', 'foogallery' ),
						'transfer' => __( 'Transfer Attachment Details', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'      => 'ecommerce_master_product_info',
					'title'   => __( 'Master Product Info', 'foogallery' ),
					'desc'    => __( 'You can set a master product for the whole gallery, which will link that product to every item. You can still manually link items to individual products. All items that are not linked to a product will be linked to the master product.', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Advanced', 'foogallery' ) ),
					'type'    => 'help',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_transfer_mode',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_master_product_id',
					'title'    => __( 'Master Product ID', 'foogallery' ),
					'desc'     => __( 'The ID of the product that will be used as the master product for every item in the gallery.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'text',
					'default'  => '',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_transfer_mode',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_transfer_add_variable_attributes',
					'title'    => __( 'Add Variation Attributes', 'foogallery' ),
					'desc'     => __( 'When a variable product is added to the cart, add the attribute data to the cart and order item.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'radio',
					'spacer'   => '<span class="spacer"></span>',
					'default'  => 'add',
					'choices'  => array(
						'add' => __( 'Add Attribute Data', 'foogallery' ),
						'' => __( 'Do Nothing', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_transfer_mode',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_transfer_product_name_source',
					'title'    => __( 'Product Name Source', 'foogallery' ),
					'desc'     => __( 'When the product is added to the cart, the name is updated from which field of the attachment', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'radio',
					'spacer'   => '<span class="spacer"></span>',
					'default'  => 'title',
					'choices'  => array(
						'title' => __( 'Attachment Title', 'foogallery' ),
						'caption' => __( 'Attachment Caption', 'foogallery' ),
						'' => __( 'Do Not Change Product Name', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_transfer_mode',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

			}

			// find the index of the advanced section.
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}

		/**
		 * Loads master product data for an attachment.
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function load_master_product_data( $foogallery_attachment, $post ) {
			// Check if we already have a product linked. If so, then get out early.
			if ( isset( $foogallery_attachment->product ) ) {
				return;
			}
			if ( 'transfer' === foogallery_gallery_template_setting( 'ecommerce_transfer_mode' ) ) {
				$product_id = intval( foogallery_gallery_template_setting( 'ecommerce_master_product_id', '0' ) );
				if ( $product_id > 0 ) {
					$foogallery_attachment->product = wc_get_product( $product_id );

					FooGallery_Pro_Woocommerce::determine_extra_data_for_product( $foogallery_attachment, $foogallery_attachment->product );
				}
			}
		}

		/**
		 * Adjust the cart thumbnail to use the attachment thumb
		 *
		 * @param $image
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return mixed|string
		 */
		public function adjust_cart_thumbnail( $image, $cart_item, $cart_item_key ) {
			if ( array_key_exists( 'foogallery_attachment_id', $cart_item ) ) {
				return wp_get_attachment_image( $cart_item['foogallery_attachment_id'] );
			}

			return $image;
		}

		/**
		 * Override the cart permalink to be blank, so that you cannot click on an item in the cart to view it.
		 *
		 * @param $permalink
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return mixed|string
		 */
		public function adjust_cart_permalink( $permalink, $cart_item, $cart_item_key ) {
			if ( array_key_exists( 'foogallery_attachment_id', $cart_item ) ) {
				// Do not return a permalink, which means the product cannot be viewed from the cart.
				// If they went to the product, they would not see the correct image.
				return '';
			}

			return $permalink;
		}

		/**
		 * Adjust the cart name based
		 *
		 * @param $name
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return mixed|string
		 */
		public function adjust_cart_name( $name, $cart_item, $cart_item_key ) {
			if ( array_key_exists( 'foogallery_id', $cart_item ) &&
			     array_key_exists( 'foogallery_attachment_id', $cart_item ) ) {

				$foogallery_id = intval( $cart_item['foogallery_id'] );
				$attachment_id = intval( $cart_item['foogallery_attachment_id'] );
				$name = $this->get_product_name( $name, $foogallery_id, $attachment_id );
			}

			return $name;
		}

		/**
		 * Get the product name for the cart or order item.
		 *
		 * @param $default_product_name
		 * @param $foogallery_id
		 * @param $attachment_id
		 *
		 * @return mixed|string
		 */
		private function get_product_name( $default_product_name, $foogallery_id, $attachment_id ) {
			$foogallery = $this->get_gallery( $foogallery_id );
			if ( 'title' === $foogallery->get_setting( 'ecommerce_transfer_product_name_source', '' ) ) {
				return get_the_title( $attachment_id );
			} elseif ( 'caption' === $foogallery->get_setting( 'ecommerce_transfer_product_name_source', '' ) ) {
				return get_the_excerpt( $attachment_id );
			}
			return $default_product_name;
		}

		/**
		 * Get the gallery from a cache to avoid loading it multiple times
		 *
		 * @param $foogallery_id
		 *
		 * @return bool|FooGallery|mixed
		 */
		private function get_gallery( $foogallery_id ) {
			global $foogallery_gallery_cache;

			if ( !is_array( $foogallery_gallery_cache ) ) {
				$foogallery_gallery_cache = array();
			}

			if ( ! array_key_exists( $foogallery_id, $foogallery_gallery_cache ) ) {
				$foogallery_gallery_cache[ $foogallery_id ] = FooGallery::get_by_id( $foogallery_id );
			}

			return $foogallery_gallery_cache[ $foogallery_id ];
		}

		/**
		 * Add attribute data to the cart item
		 *
		 * @param $cart_item_data
		 * @param $cart_item
		 *
		 * @return mixed
		 */
		function display_variable_item_data( $cart_item_data, $cart_item ) {
			if ( array_key_exists( 'foogallery_id', $cart_item ) &&
			     array_key_exists( 'foogallery_attachment_id', $cart_item ) &&
			     array_key_exists( 'foogallery_add_attributes', $cart_item ) ) {

				$item_data  = $cart_item['data'];
				$attributes = $item_data->get_attributes();
				foreach ( $attributes as $attr_key => $attr_value ) {
					if ( is_string( $attr_value ) ) {
						$cart_item_data[] = array(
							'name'  => $attr_key,
							'value' => $attr_value,
						);
					}
				}
			}
			return $cart_item_data;
		}

		/**
		 * Add custom item data to the cart
		 */
		public function add_cart_item_data( $cart_item_data, $product_id ) {

			// If we have no foogallery ID, then do not do anything
			if ( ! isset( $_REQUEST['foogallery_id'] ) ) {
				return $cart_item_data;
			}

			$foogallery_id = foogallery_extract_gallery_id( sanitize_text_field( wp_unslash( $_REQUEST['foogallery_id'] ) ) );

			if ( $foogallery_id > 0 ) {
				$foogallery = FooGallery::get_by_id( $foogallery_id );

				// Check if we must transfer attachment data.
				if ( 'transfer' === $foogallery->get_setting( 'ecommerce_transfer_mode', '' ) ) {

					// Check if we have a master product.
					$product_id = intval( $foogallery->get_setting( 'ecommerce_master_product_id', '0' ) );
					if ( $product_id > 0 ) {
						$cart_item_data['foogallery_id'] = $foogallery_id;

						$attachment_id = intval( sanitize_text_field( wp_unslash( $_REQUEST['foogallery_attachment_id'] ) ) );
						if ( $attachment_id > 0 ) {
							$cart_item_data['foogallery_attachment_id'] = $attachment_id;
						}

						if ( 'add' === $foogallery->get_setting( 'ecommerce_transfer_add_variable_attributes', '' ) ) {
							$cart_item_data['foogallery_add_attributes'] = true;
						}
					}
				}
			}

			return $cart_item_data;
		}
	}
}