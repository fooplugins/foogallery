<?php
/**
 * FooGallery class for WooCommerce Data Transfer Feature
 * Where data from the attachment is transferred to the cart and order items.
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_Pro_Woocommerce_Master_Product' ) ) {
	/**
	 * Class FooGallery_Pro_Woocommerce_Master_Product.
	 */
	class FooGallery_Pro_Woocommerce_Master_Product extends FooGallery_Pro_Woocommerce_Base {

		/**
		 * Constructor for the class
		 *
		 * Sets up all the appropriate hooks and actions
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_feature' ) );		
		}

		function load_feature() {
            if ( foogallery_feature_enabled( 'foogallery-woocommerce' ) ) {
                // Load product data after attachment has loaded.
				add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_master_product_data' ), 20, 2 );

				// Add custom data to the cart when added.
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 30, 2 );

				//Adjust the cart item data
				add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'adjust_cart_item' ), 30, 2 );
				add_filter( 'woocommerce_add_cart_item', array( $this, 'adjust_cart_item' ), 30, 2 );

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

				// Ensure we use the correct data attributes when rendering the gallery
				add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'adjust_attachment_link_data_attributes' ), 10, 3 );

				// Override the product info response.
				add_filter( 'foogallery_ecommerce_build_product_info_response', array( $this, 'adjust_product_info_response' ), 10, 4 );

				// Adjust product permalinks to pass query params
				add_filter( 'foogallery_ecommerce_build_product_permalink', array( $this, 'adjust_product_permalink' ), 10, 4 );

				//Detect if a WooCommerce block is being rendered
				add_filter( 'woocommerce_hydration_dispatch_request', array( $this, 'check_for_woocommerce_blocks' ), 10, 4 );
				add_filter( 'rest_dispatch_request', array( $this, 'check_for_woocommerce_blocks' ), 10, 4 );
				add_filter( 'woocommerce_hydration_request_after_callbacks', array( $this, 'done_with_woocommerce_blocks' ), 99, 3 );
				add_filter( 'rest_request_after_callbacks', array( $this, 'done_with_woocommerce_blocks' ), 99, 3 );

				//Adjust the cart images (For WooCommerce Blocks)
				add_filter( 'woocommerce_store_api_cart_item_images', array( $this, 'block_adjust_cart_item_images' ), 10, 3 );

				//Add the attachment description to the product info within the lightbox
				add_filter( 'foogallery_ecommerce_build_product_info_response_description', array( $this, 'adjust_product_info_response_description' ), 10, 4 );

				if ( is_admin() ) {
					// Add extra fields to the templates.
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_more_ecommerce_fields' ), 40, 2 );

					//output the master product custom field
					add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_master_product_custom_field' ), 10, 3 );

					//enqueue assets needed for the product selector modal
					add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

					//output the modal
					add_action( 'admin_footer', array( $this, 'render_master_product_modal' ) );

					//ajax handler to render the modal content
					add_action( 'wp_ajax_foogallery_master_product_content', array( $this, 'ajax_load_modal_content' ) );

					//ajax handler to render the product details
					add_action( 'wp_ajax_foogallery_master_product_details', array( $this, 'ajax_render_master_product_details' ) );

					//ajax handler to generate a master product
					add_action( 'wp_ajax_foogallery_master_product_generate', array( $this, 'ajax_generate_master_product' ) );

					// Override the order item thumbnail in admin.
					add_filter( 'woocommerce_admin_order_item_thumbnail',  array( $this, 'adjust_order_item_thumbnail' ), 10, 3 );

					// Override order meta keys and values.
					add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'adjust_order_item_display_meta_key' ), 10, 3 );
					add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'adjust_order_item_display_meta_value' ), 10, 3 );

					// Allow for search in master product modal.
					add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'support_product_search' ), 10, 2 );
				}
            }
        }

		/**
		 * Adjust the product info response description.
		 *
		 * @param string $description The description.
		 * @param WC_Product $product The product.
		 * @param FooGallery $gallery The gallery.
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string The description.
		 */
		function adjust_product_info_response_description( $description, $product, $gallery, $attachment_id ) {

			$source = $gallery->get_setting( 'ecommerce_transfer_product_description_source', '' );
			if ( 'description' === $source ) {
				$attachment = get_post( $attachment_id );
				$description = $attachment->post_content;
			} elseif ( 'caption' === $source ) {
				$description = get_the_excerpt( $attachment_id );
			}

			return $description;
		}

		/**
		 * Adjust the cart item to store relevant info about the attachment.
		 */
		function adjust_cart_item( $cart_item, $cart_item_key = '' ) {
			// We only touch items coming from a FooGallery master-product setup
			if ( empty( $cart_item['foogallery_attachment_id'] ) ) {
				return $cart_item;
			}
			
			$attachment_id = intval( $cart_item['foogallery_attachment_id'] );
			$foogallery_id = intval( $cart_item['foogallery_id'] );
			$new_title = $this->get_product_name( null, $foogallery_id, $attachment_id );
			
			if ( ! empty( $new_title ) ) {
				$product_variation = $cart_item['data'];
				if ( $product_variation instanceof WC_Product_Variation ) {
					$product_variation->set_name( $new_title );
					
					$current_data = $product_variation->get_parent_data();
					$current_data['title'] = $new_title;
					$product_variation->set_parent_data( $current_data );

					$product_variation->apply_changes();
				}
			}
			
			return $cart_item;
		}

		/**
		 * Adjust the cart item to store relevant info about the attachment (For WooCommerce Blocks).
		 */
		function block_adjust_cart_item ( $cart_item, $cart_item_key = '' ) {
			// We only touch items coming from a FooGallery master-product setup
			if ( empty( $cart_item['foogallery_attachment_id'] ) ) {
				return $cart_item;
			}
		
			$attachment_id = intval( $cart_item['foogallery_attachment_id'] );
			$foogallery_id = intval( $cart_item['foogallery_id'] );
			$new_title = $this->get_product_name( null, $foogallery_id, $attachment_id );

			if ( ! empty( $new_title ) ) {
				// Clone the WC_Product so other cart rows (or catalog) stay intact
				$product_variation = $cart_item['data'];
				if ( $product_variation instanceof WC_Product_Variation ) {
					$current_data = $product_variation->get_parent_data();
					$current_data['title'] = $new_title;
					$product_variation->set_parent_data( $current_data );
				}
			}
		
			return $cart_item;
		}

		/**
		 * Replace image for Cart & Checkout blocks
		 *
		 * Works in WooCommerce ≥ 9.6 (when the hook was introduced).
		 */
		function block_adjust_cart_item_images( $images, $cart_item, $key ) {

			if ( empty( $cart_item['foogallery_attachment_id'] ) ) {
				return $images;
			}

			$attachment_id = (int) $cart_item['foogallery_attachment_id'];
			$src           = wp_get_attachment_image_url( $attachment_id, 'woocommerce_thumbnail' );
			if ( ! $src ) {
				return $images; // fallback to default
			}

			return array(
				(object) array(
					'id'        => $attachment_id,
					'src'       => $src,
					'thumbnail' => $src,
					'srcset'    => wp_get_attachment_image_srcset( $attachment_id, 'woocommerce_thumbnail' ),
					'sizes'     => wp_get_attachment_image_sizes( $attachment_id, 'woocommerce_thumbnail' ),
					'name'      => get_the_title( $attachment_id ),
					'alt'       => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				),
			);
		}

		/**
		 * Detect if a WooCommerce block is being rendered
		 */
		function check_for_woocommerce_blocks( $result, $request, $path, $handler ) {
			global $foogallery_is_woocommerce_block;
			$paths_to_check = array( '/wc/store/v1/cart', '/wc/store/v1/checkout' );
			
			if ( in_array( $path, $paths_to_check ) ) {
				$foogallery_is_woocommerce_block = true;
			}

			return $result;
		}

		/**
		 * Set the global to false when we are done with the WooCommerce block
		 */
		function done_with_woocommerce_blocks( $response, $handler, $request) {
			global $foogallery_is_woocommerce_block;
			$foogallery_is_woocommerce_block = false;

			return $response;
		}

		function is_rendering_woocommerce_block() {
			global $foogallery_is_woocommerce_block;
			if ( isset( $foogallery_is_woocommerce_block ) ) {
				return $foogallery_is_woocommerce_block;
			}
			return false;
		}

		/**
		 * Adjust the product permalink to include params for the gallery and attachment
		 *
		 * @param $permalink
		 * @param $product
		 * @param $attachment_id
		 * @param $gallery
		 *
		 * @return string
		 */
		public function adjust_product_permalink( $permalink, $product, $attachment_id, $gallery) {
			if ( !isset( $product ) || !isset( $gallery ) ) {
				return $permalink;
			}

			$master_product_id = intval( $gallery->get_setting( 'ecommerce_master_product_id', '0' ) );
			if ( $master_product_id === 0 ) {
				return $permalink;
			}

			return add_query_arg( array(
				'fg_id' => $gallery->ID,
				'fga_id' => $attachment_id
			), $permalink );
		}

		/**
		 * Adjust the product info title to return the correct name if a master product is used.
		 *
		 * @param $response
		 * @param $product
		 * @param $gallery FooGallery
		 * @param $attachment_id
		 *
		 * @return mixed
		 */
		public function adjust_product_info_response( $response, $product, $gallery, $attachment_id ) {
			$master_product_id = intval( $gallery->get_setting( 'ecommerce_master_product_id', '0' ) );
			if ( $master_product_id > 0 ) {
				$response['title'] = $this->get_product_name( $response['title'], $gallery->ID, $attachment_id );
			}
			return $response;
		}

		/**
		 * Adjusts the data attributes for an anchor to work with master products
		 *
		 * @param $attr
		 * @param $args
		 * @param $foogallery_attachment
		 *
		 * @return mixed
		 */
		public function adjust_attachment_link_data_attributes( $attr, $args, $foogallery_attachment ) {
			try {
				$product_id = $this->get_master_product_id_from_current_gallery();
				if ( $product_id > 0 ) {
					// Get the validated global setting for item ID attribute
					$item_id_attribute = $this->get_item_id_attribute_setting();
					
					// Only modify attributes if global setting requires data-id
					if ( $item_id_attribute === 'data-id' && array_key_exists( 'data-attachment-id', $attr ) ) {
						unset( $attr[ 'data-attachment-id' ] );
						$attr[ 'data-id' ] = $foogallery_attachment->ID;
					}
					// Otherwise preserve existing attribute type (data-attachment-id)
				}
			} catch ( Exception $e ) {
				error_log( 'FooGallery Master Product: Error adjusting attachment link data attributes: ' . $e->getMessage() );
				// Return original attributes on error to ensure functionality continues
			}

			return $attr;
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
					'id'      => 'ecommerce_master_product_info',
					'title'   => __( 'Master Product Help', 'foogallery' ),
					'desc'    => __( 'You can set a master product for the whole gallery, which will link that product to every item. You can still manually link items to individual products. All items that are not linked to a product will be linked to the master product.', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Master Product', 'foogallery' ) ),
					'type'    => 'help'
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_transfer_mode',
					'title'    => __( 'Master Product Mode', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Master Product', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => array(
						''         => __( 'Disabled', 'foogallery' ),
						'transfer' => __( 'Enabled', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_master_product_id',
					'title'    => __( 'Master Product', 'foogallery' ),
					'desc'     => __( 'The product that will be used as the master product for every item in the gallery.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Master Product', 'foogallery' ) ),
					'type'     => 'ecommerce_master_product',
					'default'  => '',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_transfer_mode',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => '.ecommerce-master-product-input',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_transfer_add_variable_attributes',
					'title'    => __( 'Add Variation Attributes', 'foogallery' ),
					'desc'     => __( 'When a variable product is added to the cart, add the attribute data to the cart and order item.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Master Product', 'foogallery' ) ),
					'type'     => 'radio',
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
					'desc'     => __( 'Which field of the attachment is used for the product name', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Master Product', 'foogallery' ) ),
					'type'     => 'radio',
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

				$new_fields[] = array(
					'id'       => 'ecommerce_transfer_product_description_source',
					'title'    => __( 'Product Desc Source', 'foogallery' ),
					'desc'     => __( 'Which field of the attachment is used for the product description', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-master-product' => __( 'Master Product', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'description',
					'choices'  => array(
						'description' => __( 'Attachment Description', 'foogallery' ),
						'caption' => __( 'Attachment Caption', 'foogallery' ),
						'' => __( 'Use Master Product Description', 'foogallery' ),
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

			// find the index of the master product section.
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Master Product', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}

        /**
         * Renders the master_product custom field field in admin
         *
         * @param $field
         * @param $gallery
         * @param $template
         */
        public function render_master_product_custom_field( $field, $gallery, $template ) {
            if ( 'ecommerce_master_product' === $field['type'] ) {

                if ( isset( $field['value'] ) ) {
                    // check that the product exists and is valid
                    $product_id = intval( $field['value'] );
                    echo '<div class="foogallery-master-product-field-container">';
                    $this->render_master_product_details( $product_id );
                    echo '</div>';
                }

                echo '<button class="button button-primary button-small ecommerce-master-product-selector">' . __( 'Select Master Product', 'foogallery' ) . '</button>';
                $field_name = FOOGALLERY_META_SETTINGS . '[' . $template['slug'] . '_' . $field['id'] . ']';
                echo '<input class="ecommerce-master-product-input" type="hidden" name=' . esc_attr( $field_name ) . ' value="' . esc_html( $field['value'] ) . '" />';
            }
        }

        /**
         * Only renders the master product validation details.
         *
         * @param $product_id
         * @return void
         */
        public function render_master_product_details( $product_id ) {
            if ( $product_id > 0 ) {
                $product = wc_get_product( $product_id );

                if ( $product === false ) {
                    echo '<p><span class="dashicons dashicons-warning" style="color:#d63638"></span>' . __('The master product does not exist! Please select another product.', 'foogallery') . '</p>';
                } else {
                    echo '<strong>' . esc_html($product->get_name('edit')) . '</strong>';
                    echo ' (ID : ' . esc_html($product_id) . ')';
                    $url = get_edit_post_link($product_id);
                    echo ' <a class="post-edit-link" target="_blank" href="' . esc_url($url) . '">' . __('edit', 'foogallery') . '</a>';

                    $validation_response = $this->validate_master_product($product);

                    if (isset($validation_response) && array_key_exists('errors', $validation_response) && count($validation_response['errors']) > 0) {
                        foreach ($validation_response['errors'] as $error) {
                            if ( 'critical' === $error['severity'] ) {
                                echo '<p><span class="dashicons dashicons-warning" style="color:#d63638"></span>';
                            } else {
                                echo '<p><span class="dashicons dashicons-info" style="color:#d0621c"></span>';
                            }
                            echo esc_html($error['message']) . '</p>';
                        }
                    } else {
                        echo '<p><span class="dashicons dashicons-yes-alt" style="color:#00a32a"></span>' . __('This product has been setup correctly to be a master product.', 'foogallery') . '</p>';
                    }
                }
            }
        }

        /**
         * Generates a new Master Product that can be used for a gallery.
         *
         * @return int
         * @throws WC_Data_Exception
         */
        private function generate_master_product() {
            $master_product = new WC_Product_Variable();
            $master_product->set_name(__( 'Generated Master Product', 'foogallery' ) );
            $master_product->set_catalog_visibility( 'hidden' );

            $attribute = new WC_Product_Attribute();
            $attribute->set_name( __( 'Size', 'foogallery' ) );
            $attribute->set_options( array( 'small','medium', 'large' ) );
            $attribute->set_visible( true );
            $attribute->set_variation( true );
            $attributes[] = $attribute;

            $master_product->set_attributes( $attributes );
            $master_product->save();

            $variation = new WC_Product_Variation();
            $variation->set_parent_id( $master_product->get_id() );
            $variation->set_attributes( array( 'size' => 'small' ) );
            $variation->set_status('publish');
            $variation->set_regular_price( '9.99' );
            $variation->save();

            $variation = new WC_Product_Variation();
            $variation->set_parent_id( $master_product->get_id() );
            $variation->set_attributes( array( 'size' => 'medium' ) );
            $variation->set_status('publish');
            $variation->set_regular_price( '19.99' );
            $variation->save();

            $variation = new WC_Product_Variation();
            $variation->set_parent_id( $master_product->get_id() );
            $variation->set_attributes( array( 'size' => 'large' ) );
            $variation->set_status('publish');
            $variation->set_regular_price( '99.99' );
            $variation->save();

            $data_store = $master_product->get_data_store();
            $data_store->sort_all_product_variations( $master_product->get_id() );

            add_post_meta( $master_product->get_id(), 'foogallery_master_product', true, true );

            return $master_product->get_id();
        }

        /**
         * Validates the product to determine if it can be a master product.
         *
         * @param $product
         * @return array
         */
        private function validate_master_product( $product ) {
            if ( is_numeric( $product ) ) {
                $product = wc_get_product( $product );
            }

            $exists = false;
            $validation_errors = array();

            if ( FooGallery_Pro_Woocommerce::is_product( $product ) ) {
                $exists = true;

                // Check the product is published.
                $product_data = $product->get_data();
                if ( isset( $product_data ) ) {
                    if ( 'publish' !== $product_data['status'] ) {
                        $validation_errors[] = array(
                            'message' => __( 'The product must be published.', 'foogallery' ),
                            'severity' => 'critical'
                        );
                    }
                }

                // Check the product type is variable.
                if ( !$product->is_type( 'variable' ) ) {
                    $validation_errors[] = array(
                        'message' => __( 'The product is not a variable product. We recommend using a variable product, but you can still use a simple product.', 'foogallery' ),
                        'severity' => 'warning'
                    );
                } else {
                    $variations = $product->get_children();
                    if ( count( $variations ) == 0 ) {
                        $validation_errors[] = array(
                            'message' => __( 'The product does not have any variations. Create variations for the product.', 'foogallery' ),
                            'severity' => 'critical'
                        );
                    }
                }

                // Check the visibility.
                if ( 'hidden' !== $product->get_catalog_visibility( 'edit' ) ) {
                    $validation_errors[] = array(
                        'message' => __( 'The master product should not be visible. Set the Catalog Visibility to "Hidden".', 'foogallery' ),
                        'severity' => 'critical'
                    );
                }

            } else {
                $validation_errors[] = array(
                    'message' => __( 'ERROR : the product was not found, or had been deleted!', 'foogallery' ),
                    'severity' => 'critical'
                );
            }

            return array(
                'exists' => $exists,
                'errors' => $validation_errors
            );
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
			$product_id = $this->get_master_product_id_from_current_gallery();
			if ( $product_id > 0 ) {
				$foogallery_attachment->product = wc_get_product( $product_id );

				FooGallery_Pro_Woocommerce::determine_extra_data_for_product( $foogallery_attachment, $foogallery_attachment->product );
			}
		}

		/**
		 * Helper function to return the master product ID for the current gallery
		 *
		 * @return int
		 */
		private function get_master_product_id_from_current_gallery() {
			if ( ! foogallery_current_gallery_has_cached_value( 'ecommerce_master_product_id' ) ) {
				$master_product_id = 0;
				if ( 'transfer' === foogallery_gallery_template_setting( 'ecommerce_transfer_mode' ) ) {
					$master_product_id = intval( foogallery_gallery_template_setting( 'ecommerce_master_product_id', '0' ) );
				}
				foogallery_current_gallery_set_cached_value( 'ecommerce_master_product_id', $master_product_id );
			}

			return intval( foogallery_current_gallery_get_cached_value( 'ecommerce_master_product_id' ) );
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
			if ( is_array( $cart_item ) && array_key_exists( 'foogallery_attachment_id', $cart_item ) ) {
				return wp_get_attachment_image( $cart_item['foogallery_attachment_id'], 'woocommerce_thumbnail' );
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
		 * Add attribute data to the cart item
		 *
		 * @param $cart_item_data
		 * @param $cart_item
		 *
		 * @return mixed
		 */
		function display_variable_item_data( $cart_item_data, $cart_item ) {
			if ( $this->is_rendering_woocommerce_block() ) {
				// Do not add attributes if we are rendering a woocommerce block, as they return the correct data.
				return $cart_item_data;
			}

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

				// Check if we have a master product.
				$master_product_id = intval( $foogallery->get_setting( 'ecommerce_master_product_id', '0' ) );
				if ( $master_product_id > 0 ) {
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

			return $cart_item_data;
		}

        /**
         * Enqueues js assets in admin
         */
        public function enqueue_scripts_and_styles() {
            wp_enqueue_style( 'foogallery.admin.woocommerce', FOOGALLERY_PRO_URL . 'css/foogallery.admin.woocommerce.css', array(), FOOGALLERY_VERSION );
            wp_enqueue_script( 'foogallery.admin.woocommerce', FOOGALLERY_PRO_URL . 'js/foogallery.admin.woocommerce.js', array( 'jquery' ), FOOGALLERY_VERSION );
        }

        /**
         * Renders the master product select modal for use on the gallery edit page
         */
        public function render_master_product_modal() {

            global $post;

            //check if the gallery edit page is being shown
            $screen = get_current_screen();
            if ( 'foogallery' !== $screen->id ) {
                return;
            }

            if ( !is_a( $post, 'WP_Post' ) ) {
                return;
            }

            ?>
            <div class="foogallery-master-product-modal-wrapper" data-foogalleryid="<?php echo $post->ID; ?>" data-nonce="<?php echo wp_create_nonce( 'foogallery_master_product' ); ?>" style="display: none;">
                <div class="media-modal wp-core-ui">
                    <button type="button" class="media-modal-close foogallery-master-product-modal-close">
						<span class="media-modal-icon"><span class="screen-reader-text"><?php _e( 'Close', 'foogallery' ); ?></span>
                    </button>
                    <div class="media-modal-content">
                        <div class="media-frame wp-core-ui">
                            <div class="foogallery-master-product-modal-title">
                                <h1><?php _e('Select a Master Product', 'foogallery'); ?></h1>
                                <a class="foogallery-master-product-modal-reload button" href="#"><span style="padding-top: 4px;" class="dashicons dashicons-update"></span> <?php _e('Reload', 'foogallery'); ?></a>
                            </div>
                            <div class="foogallery-master-product-modal-container not-loaded">
                                <div class="spinner is-active"></div>
                            </div>
                            <div class="foogallery-master-product-modal-toolbar">
                                <div class="foogallery-master-product-modal-toolbar-inner">
                                    <div class="media-toolbar-primary">
                                        <a href="#"
                                           class="foogallery-master-product-modal-close button button-large button-secondary"
                                           title="<?php esc_attr_e('Close', 'foogallery'); ?>"><?php _e('Close', 'foogallery'); ?></a>
                                        <a href="#" disabled="disabled"
                                           class="foogallery-master-product-modal-set button button-large button-primary"
                                           title="<?php esc_attr_e('Select Master Product', 'foogallery'); ?>"><?php _e('Select Master Product', 'foogallery'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="media-modal-backdrop"></div>
            </div>
            <?php
        }

        /**
         * Outputs the modal content
         */
        public function ajax_load_modal_content() {
            $nonce = safe_get_from_request( 'nonce' );

            if ( wp_verify_nonce( $nonce, 'foogallery_master_product' ) ) {

                $search = safe_get_from_request( 'search' );
                $product_id = intval( safe_get_from_request( 'product_id' ) );

                echo '<div class="foogallery-master-product-modal-content" data-selected="' . $product_id . '">';
                echo '<div class="foogallery-master-product-modal-content-inner">';
                echo '<div class="foogallery-master-product-modal-content-inner-search">';
                echo '<input type="search" value="' . esc_attr( $search ) . '" />';
                echo '<a href="#" class="foogallery-master-product-search button button-primary" title="' . esc_attr__('Search for a product', 'foogallery') . '">' . esc_html__('Search', 'foogallery') . '</a>';
                echo '</div>';
                $args = array(
                    'limit'       => 50,
                    'post_type'   => 'product',
                    'post_status' => 'any',
                    'orderby'     => 'date',
					'order'       => 'DESC',
                    'foogallery_master_product_search' => $search
                );
                /** @var $products array<WC_Product>*/
                $products = wc_get_products( $args );
                if ( count( $products ) === 0 ) {
                    echo 'No products found!';
                } else {
                    echo '<ul>';
                    foreach ( $products as $product ) {
                        $post_thumbnail_id = get_post_thumbnail_id( $product->get_id() );
                        $thumb_url = wp_get_attachment_thumb_url( $post_thumbnail_id );
                        if ( empty( $thumb_url ) ) {
                            $thumb_url = wc_placeholder_img_src();
                        }
                        $class = $product_id === $product->get_id() ? 'class="selected"' : '';
                        $price = $product->get_price_html();
                        if ( empty( $price ) ) {
                            $price = '&nbsp;';
                        }
                        echo '<li ' . $class . ' data-id="' . $product->get_id() . '">';
                        echo '<img src="' . $thumb_url . '" />';
                        echo '<h3>' . $product->get_title() . '</h3>';
                        echo '<span>' . $price . '</span>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }

                echo '</div>';
                echo '</div>';

                echo '<div class="foogallery-master-product-modal-sidebar">';
                $class = $product_id === 0 ? ' hidden' : '';
                echo '<div class="foogallery-master-product-modal-sidebar-inner foogallery-master-product-modal-details' . $class . '">';
                echo '<h2>' . __( 'Selected Master Product', 'foogallery' ) . '</h2>';
                echo '<div class="foogallery-master-product-modal-details-inner">';
                if ( $product_id > 0 ) {
                    $this->render_master_product_details($product_id);
                }
                echo '</div>';
                echo '</div>';
                echo '<div class="foogallery-master-product-modal-sidebar-inner foogallery-master-product-modal-help">';
                echo '<h2>' . __( 'Master Product Help', 'foogallery' ) . '</h2>';
                echo '<p>' . __( 'A master product can be set for a gallery, so that every item within the gallery will use details from the master product. This allows you to setup a single product across all images, and will save you time when configuring your gallery in order to sell your images online.', 'foogallery' ) . '</p>';
                echo '<p>' . __( 'A master product should be a variable product with multiple variations that you can configure to have different prices. Usually these variations are different by size, but you can have variations that use more attributes, e.g. size/format, print material, frame, etc.', 'foogallery' ) . '</p>';
                echo '<p>' . __( 'A master product will work as a simple product, but we recommend using a variable product.', 'foogallery' ) . '</p>';
                echo '<p>' . __( 'Additionally, a master product should be published and the Catalog Visibility should set to "Hidden".', 'foogallery' ) . '</p>';
                $generated_product_id = $this->find_generated_master_product();
                if ( $generated_product_id === 0 ) {
                    echo '<p>' . __('To help you get started, we can generate a master product that meets these requirements, which you can customize to your needs:', 'foogallery') . '</p>';
                    echo '<a href="#" class="foogallery-master-product-generate button button-small button-primary" title="' . esc_attr__('Generate Master Product', 'foogallery') . '">' . esc_html__('Generate Master Product', 'foogallery') . '</a>';
                    echo '<div class="spinner"></div>';
                }
                echo '</div>';
                echo '</div>';
            }

            die();
        }

        /**
         * Outputs the master product details.
         */
        public function ajax_render_master_product_details() {
            $nonce = safe_get_from_request('nonce');

            if ( wp_verify_nonce( $nonce, 'foogallery_master_product' ) ) {
                $product_id = intval( safe_get_from_request( 'product_id' ) );
                if ( $product_id > 0 ) {
                    $this->render_master_product_details( $product_id );
                }
            }

            die();
        }

        /**
         * Finds a previously generated master product.
         *
         * @return int
         */
        private function find_generated_master_product() {
            $args = array(
                'limit'       => 1,
                'post_type'   => 'product',
                'post_status' => 'publish',
                'orderby'     => 'date',
                'order'       => 'DESC',
                'meta_key'    => 'foogallery_master_product'
            );

            $generated_master_products = get_posts( $args );
            if ( count( $generated_master_products ) === 0 ) {
                return 0;
            } else {
                return $generated_master_products[0]->ID;
            }
        }


        /**
         * Ajax handler that generates a master product.
         * @return void
         */
        public function ajax_generate_master_product() {
            $nonce = safe_get_from_request('nonce');

            if ( wp_verify_nonce( $nonce, 'foogallery_master_product' ) ) {
                $product_id = $this->find_generated_master_product();
                if ( $product_id === 0 ) {
                    $product_id = $this->generate_master_product();
                }

                wp_send_json_success( array( 'productId' => $product_id ) );
            }

            die();
        }

        /**
         * Add support for 'foogallery_master_product_search' query var
         */
        function support_product_search( $query, $query_vars ) {
            if ( empty( $query_vars['foogallery_master_product_search'] ) ) {
                return $query;
            }

            $query['s'] = $query_vars['foogallery_master_product_search'];
            return $query;
        }

        /**
         * Helper method to get the item ID attribute setting with validation
         *
         * @return string The validated item ID attribute ('data-attachment-id' or 'data-id')
         */
        private function get_item_id_attribute_setting() {
            try {
                $item_id_attribute = foogallery_get_setting( 'attachment_id_attribute', 'data-attachment-id' );
                
                // Validate setting value and fallback to default if invalid
                if ( !in_array( $item_id_attribute, ['data-attachment-id', 'data-id'] ) ) {
                    error_log( 'FooGallery Master Product: Invalid item_id_attribute setting value: ' . $item_id_attribute . '. Falling back to default.' );
                    $item_id_attribute = 'data-attachment-id';
                }
                
                return $item_id_attribute;
            } catch ( Exception $e ) {
                error_log( 'FooGallery Master Product: Error getting item ID attribute setting: ' . $e->getMessage() );
                return 'data-attachment-id'; // Safe fallback
            }
        }
	}
}