<?php
/**
 * FooGallery class for WooCommerce Integration
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_Pro_Woocommerce' ) ) {


	/**
	 * Class FooGallery_Pro_Woocommerce
	 */
	class FooGallery_Pro_Woocommerce {

		/**
		 * Constructor for the class
		 *
		 * Sets up all the appropriate hooks and actions
		 */
		public function __construct() {
			if ( is_admin() ) {
				// Add extra fields to the templates.
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_ecommerce_fields' ), 30, 2 );

				// Set the settings icon for commerce.
				add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

				// Add a cart icon to the hover icons.
				add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_icon_choices', array( $this, 'add_cart_hover_icon' ) );

				// Add attachment custom fields.
				add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ), 50 );
			}

			// Determine ribbon/button data from product.
			add_filter( 'foogallery_datasource_woocommerce_build_attachment', array( $this, 'determine_extra_data_for_product' ), 10, 2 );

			// Enqueue WooCommerce scripts if applicable.
			add_action( 'foogallery_located_template', array( $this, 'enqueue_wc_scripts') );

			// Append product attributes onto the anchor in the galleries.
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_product_attributes' ), 10, 3 );

			// Load product data after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_product_data' ), 10, 2 );

			// Append a nonce that will be used in variation ajax calls.
			add_filter( 'foogallery_lightbox_data_attributes', array( $this, 'add_to_lightbox_options' ) );

			// Build up a product variation table for a product.
			add_filter( 'wp_ajax_foogallery_product_variations', array( $this, 'ajax_build_product_info' ) );
			add_filter( 'wp_ajax_nopriv_foogallery_product_variations', array( $this, 'ajax_build_product_info' ) );
		}

		/**
		 * AJAX request to build up the product info HTML to show in the lightbox.
		 */
		public function ajax_build_product_info() {
			$request = stripslashes_deep( $_REQUEST );

			if ( wp_verify_nonce( $request['nonce'], $request['nonce_time'] . 'foogallery_product_variations' ) ) {

				$product_id = sanitize_text_field( wp_unslash( $request['product_id'] ) );
				$gallery_id = foogallery_extract_gallery_id( sanitize_text_field( wp_unslash( $request['gallery_id'] ) ) );

				$info = $this->build_product_info( $product_id, $gallery_id );

				wp_send_json( $info );
			} else {
				wp_send_json( array( 'error' => __( 'Not allowed!', 'foogallery' ) ) );
			}

			die();
		}

		public function build_product_info( $product_id, $gallery_id ) {
			$product = wc_get_product( $product_id );

			$gallery = FooGallery::get_by_id( $gallery_id );

			$response = array();

			if ( empty( $product ) ) {
				$response['error'] = __( 'No product found!', 'foogallery' );
			} else {
				$html = ''; //'<h2>' . esc_html( $product->get_name() ) . '</h2>';
				$html .= '<p>' . esc_html( $product->get_description() ) . '</p>';

				if ( is_a( $product, 'WC_Product_Variable' ) ) {
					$html .= $this->build_product_variation_table( $product );
				}
				$response['title'] = $product->get_name();
				$response['purchasable'] = $product->is_purchasable();

				if ( 'on' === $gallery->get_setting( 'ecommerce_lightbox_show_view_product_button', '' ) ) {
					$response['product_url'] = $product->get_permalink();
				}

				$response['body'] = $html;
			}

			return $response;
		}

		/**
		 * Build up the product variation HTML table to show in the lightbox.
		 *
		 * @param $product
		 *
		 * @return string
		 */
		public function build_product_variation_table( $product ) {
			if ( !is_a( $product, 'WC_Product_Variable' ) ) {
				$product = wc_get_product( $product );
			}

			if ( empty( $product ) ) {
				return null;
			}

			// Get the variations for the product.
			$variations = $product->get_children();

			if ( empty( $variations ) ) {
				return '';
			}

			$attributes = array();

			// Get the attribute labels.
			foreach ( $product->get_variation_attributes() as $taxonomy => $term_names ) {
				$attributes[$taxonomy] = wc_attribute_label( $taxonomy );
			}

			// Build up the table head.
			$html = '<table><thead><tr><th></th>';

			foreach ( $attributes as $attribute_key => $attribute_label ) {
				$html .= '<th>' . $attribute_label . '</th>';
			}

			$html .= '<th>' . __( 'Price', 'foogallery' ) . '</th></tr></thead>';

			// Build up the table body.
			$html .= '<tbody>';
			$checked = ' checked="checked"';
			foreach ( $variations as $value ) {
				$single_variation = new WC_Product_Variation( $value );

				if ( $single_variation->is_purchasable() ) {
					$variation_id = $single_variation->get_id();
					$html         .= '<tr data-variation_id="' . esc_attr( $variation_id ) . '" title="' . esc_attr( $single_variation->get_description() ) .  '">';

					if ( $single_variation->is_on_sale() ) {
						$price = wc_price( $single_variation->get_sale_price() ) . " <del>" . wc_price( $single_variation->get_regular_price() ) . "</del>";
					} else {
						$price = wc_price( $single_variation->get_regular_price() );
					}
					$price = $single_variation->get_price_html();

					$html .= '<td><input type="radio" name="foogallery_product_variation_' . esc_attr( $product->get_id() ) . '" value="' . esc_attr( $variation_id ) . '" ' . $checked . ' /></td>';
					$checked = '';
					//$html .= '<td><span title="' . esc_attr( $single_variation->get_attribute_summary() ) . '">' . $single_variation->get_name() . '</span></td>';
					foreach ( $attributes as $attribute_key => $attribute_label ) {
						$html .= '<td>' . $single_variation->get_attribute( $attribute_key ) . '</td>';
					}
					$html .= '<td>' . $price . '</td>';
					$html .= '</tr>';
				}
			}

			$html .= '</tbody></table>';

			return $html;
		}

		/**
		 * Appends a nonce onto the lightbox options
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		public function add_to_lightbox_options( $options ) {
			$time = time();
			$options['cart'] = 'right';
			$options['cartVisible'] = true;
			$options['cartTimeout'] = $time;
			$options['cartNonce'] = wp_create_nonce( $time . 'foogallery_product_variations' );
			$options['cartAjax'] = admin_url( 'admin-ajax.php' );
			$options['admin'] = is_admin();
			return $options;
		}

		/**
		 * Loads product data for an attachment.
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function load_product_data( $foogallery_attachment, $post ) {
			// Check if we already have a product linked. If so, then get out early.
			if ( isset( $foogallery_attachment->product ) ) {
				return;
			}

			$product_id = get_post_meta( $post->ID, '_foogallery_product', true );
			if ( !empty( $product_id ) ) {
				$foogallery_attachment->product = wc_get_product( $product_id );

				$this->determine_extra_data_for_product( $foogallery_attachment, $foogallery_attachment->product );
			}
		}

		/**
		 * Add product attributes onto the anchor for an item.
		 *
		 * @param $attr
		 * @param $args
		 * @param $foogallery_attachment
		 *
		 * @return array
		 */
		public function add_product_attributes( $attr, $args, $foogallery_attachment ) {
			if ( isset( $foogallery_attachment->product ) ) {
				$attr['data-product-id'] = $foogallery_attachment->product->get_id();
			}
			return $attr;
		}

		/**
		 * Enqueue the WooCommerce scripts if add to cart ajax is enabled for one of the action buttons
		 */
		public function enqueue_wc_scripts() {
			if ( 'fg-woo-add-to-cart-ajax' === foogallery_gallery_template_setting( 'ecommerce_action_button_1' ) ||
			     'fg-woo-add-to-cart-ajax' === foogallery_gallery_template_setting( 'ecommerce_action_button_2' ) ) {
				wp_enqueue_script( 'wc-add-to-cart' );
			}
		}

		/**
		 * Determine if ribbons/buttons are needed for the product
		 *
		 * @param $attachment
		 * @param WC_Product $product
		 *
		 * @return mixed
		 */
		function determine_extra_data_for_product( $attachment, $product ) {
			// Do we need to add ribbons?
			$ribbon_type = foogallery_gallery_template_setting( 'ecommerce_sale_ribbon_type', 'fg-ribbon-5' );
			if ( 'none' !== $ribbon_type ) {
				if ( $product->is_on_sale() ) {
					$attachment->ribbon_type = $ribbon_type;
					$attachment->ribbon_text = foogallery_gallery_template_setting( 'ecommerce_sale_ribbon_text', __( 'Sale', 'foogallery' ) );
				}
			}

			if ( !is_a( $product, 'WC_Product_Variable' ) ) {
				// Do we need "Add To Cart" button?
				$button_add_to_cart = foogallery_gallery_template_setting( 'ecommerce_button_add_to_cart', '' );
				if ( '' !== $button_add_to_cart ) {
					$button_add_to_cart_behaviour = foogallery_gallery_template_setting( 'ecommerce_button_add_to_cart_behaviour', 'fg-woo-add-to-cart-ajax' );
					$button_add_to_cart_url       = $this->determine_url( $button_add_to_cart_behaviour, $product );
					if ( ! empty( $button_add_to_cart_url ) ) {
						$attachment->buttons[] = array(
							'class' => $button_add_to_cart_behaviour,
							'text'  => foogallery_gallery_template_setting( 'ecommerce_button_add_to_cart_text', __( 'Add To Cart', 'foogallery' ) ),
							'url'   => $button_add_to_cart_url,
						);
					}
				}
			} else {
				$button_variable = foogallery_gallery_template_setting( 'ecommerce_button_variable', '' );
				if ( '' !== $button_variable ) {
					$attachment->buttons[] = array(
						'text'  => foogallery_gallery_template_setting( 'ecommerce_button_variable_text', __( 'Select Options', 'foogallery' ) ),
					);
				}
			}

			// Do we need to add "View Product" button?
			$button_view_product = foogallery_gallery_template_setting( 'ecommerce_button_view_product', '' );
			if ( '' !== $button_view_product ) {
				$button = array(
					'class' => 'fg-woo-view-product',
					'text'  => foogallery_gallery_template_setting( 'ecommerce_button_view_product_text', __( 'View Product', 'foogallery' ) ),
					'url'   => $product->get_permalink(),
				);

				if ( 'first' === $button_view_product ) {
					if ( !isset( $attachment->buttons ) ) {
						$attachment->buttons = array();
					}
					array_unshift( $attachment->buttons, $button );
				} else {
					$attachment->buttons[] = $button;
				}
			}

			return $attachment;
		}

		/**
		 * Determine url for a product
		 *
		 * @param string $url_type
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		private function determine_url( $url_type, $product ) {
			if ( $product->is_purchasable() ) {
				switch ( $url_type ) {
					case 'fg-woo-add-to-cart':
					case 'fg-woo-add-to-cart-ajax':
						return trailingslashit( get_home_url() ) . '?add-to-cart=' . $product->get_id();
					case 'fg-woo-add-to-cart-redirect' :
						return trailingslashit( wc_get_cart_url() ) . '?add-to-cart=' . $product->get_id();
					case 'fg-woo-add-to-cart-checkout':
						return trailingslashit( wc_get_checkout_url() ) . '?add-to-cart=' . $product->get_id();
				}
			}
			return '';
		}

		/**
		 * Add a cart hover icon to the hover effects
		 *
		 * @param $hover_icons
		 *
		 * @return mixed
		 */
		function add_cart_hover_icon( $hover_icons ) {
			if ( $this->is_woocommerce_activated() ) {
				$hover_icons['fg-hover-cart'] = array(
					'label' => __( 'Cart', 'foogallery' ),
					'html'  => '<div class="foogallery-setting-caption_icon fg-hover-cart"></div>'
				);
			}

			return $hover_icons;
		}

		/**
		 * Check if WooCommerce is activated
		 */
		function is_woocommerce_activated() {
			return class_exists( 'woocommerce' );
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param string $section_slug The section we want to check.
		 *
		 * @return string
		 */
		public function add_section_icons( $section_slug ) {

			if ( 'ecommerce' === strtolower( $section_slug ) ) {
				return 'dashicons-cart';
			}

			return $section_slug;
		}

		/**
		 * Add protection fields to all gallery templates
		 *
		 * @param array  $fields The fields to override.
		 * @param string $template The gallery template.
		 *
		 * @return array
		 */
		public function add_ecommerce_fields( $fields, $template ) {

			$new_fields = array();

			if ( $this->is_woocommerce_activated() ) {

				$new_fields[] = array(
					'id'      => 'ecommerce_info',
					'title'   => __( 'Ecommerce Info', 'foogallery' ),
					'desc'    => __( 'The below settings will only apply if you are using the WooCommerce Product datasource, or if individual attachments are linked to WooCommerce products.', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'type'    => 'help',
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_lightbox_product_information',
					'title'    => __( 'Lightbox Product Info', 'foogallery' ),
					'desc'     => __( 'You can show product information in the PRO lightbox, including product variations, which the visitor can add to cart.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'radio',
					'default'  => 'right',
					'spacer'   => '<span class="spacer"></span>',
					'choices'  => array(
						'top'    => __( 'Top', 'foogallery'),
						'bottom' => __( 'Bottom', 'foogallery'),
						'left'   => __( 'Left', 'foogallery'),
						'right'  => __( 'Right', 'foogallery'),
						'none'   => __( 'None', 'foogallery'),
					),
					'row_data' => array(
						'data-foogallery-change-selector'          => 'select',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_lightbox_show_view_product_button',
					'title'    => __( 'Lightbox "View Product" Button', 'foogallery' ),
					'desc'     => __( 'Shows an extra button that opens the product page.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'radio',
					'default'  => '',
					'spacer'   => '<span class="spacer"></span>',
					'choices'  => array(
						'shown' => __( 'Shown', 'foogallery'),
						''    => __( 'Hidden', 'foogallery'),
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_lightbox_product_information',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => 'none',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_sale_ribbon_type',
					'title'    => __( 'Sale Ribbon', 'foogallery' ),
					'desc'     => __( 'The type of ribbon to display for products that are on sale.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'select',
					'default'  => 'fg-ribbon-5',
					'choices'  => FooGallery_Pro_Ribbons::get_ribbon_choices(),
					'row_data' => array(
						'data-foogallery-change-selector'          => 'select',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'select :selected',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_sale_ribbon_text',
					'title'    => __( 'Sale Ribbon Text', 'foogallery' ),
					'desc'     => __( 'The text inside the ribbon to display for products that are on sale.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'text',
					'default'  => __( 'Sale', 'foogallery' ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_sale_ribbon_type',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_button_view_product',
					'title'    => __( '"View Product" Button', 'foogallery' ),
					'desc'     => __( 'Shows a button which redirects to the product page.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'radio',
					'spacer'   => '<span class="spacer"></span>',
					'default'  => '',
					'choices'  => array(
						'first' => __( 'Shown (first)', 'foogallery' ),
						'last' => __( 'Shown (last)', 'foogallery' ),
						'' => __( 'Hidden', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_button_view_product_text',
					'title'    => __( '"View Product" Button Text', 'foogallery' ),
					'desc'     => __( 'The text displayed on the "View Product" button.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'text',
					'default'  => __( 'View Product', 'foogallery' ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_button_view_product',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_button_add_to_cart',
					'title'    => __( '"Add To Cart" Button', 'foogallery' ),
					'desc'     => __( 'Shows an "Add To Cart" button for the product. Will only show for purchasable products.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'radio',
					'spacer'   => '<span class="spacer"></span>',
					'choices'  => array(
						'first' => __( 'Shown', 'foogallery' ),
						'' => __( 'Hidden', 'foogallery' ),
					),
					'default'  => '',
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_button_add_to_cart_behaviour',
					'title'    => __( '"Add To Cart" Button Behaviour', 'foogallery' ),
					'desc'     => __( 'What happens when the "Add to Cart" button is clicked.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'select',
					'choices'  => self::get_button_behaviour_choices(),
					'default'  => 'fg-woo-add-to-cart-ajax',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_button_add_to_cart',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'select',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'select :selected',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_button_add_to_cart_text',
					'title'    => __( '"Add To Cart" Button Text', 'foogallery' ),
					'desc'     => __( 'The text displayed on the "Add To Cart" action button.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'text',
					'default'  => __( 'Add To Cart', 'foogallery' ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_button_add_to_cart',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_button_variable',
					'title'    => __( '"Select Options" Button', 'foogallery' ),
					'desc'     => __( 'Shows a "Select Options" button for the variable products only.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'radio',
					'spacer'   => '<span class="spacer"></span>',
					'choices'  => array(
						'shown' => __( 'Shown', 'foogallery' ),
						'' => __( 'Hidden', 'foogallery' ),
					),
					'default'  => '',
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_button_variable_text',
					'title'    => __( '"Select Options" Button Text', 'foogallery' ),
					'desc'     => __( 'The text displayed on the "Select Options" button for variable products.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'text',
					'default'  => __( 'Select Options', 'foogallery' ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_button_variable',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input',
					),
				);
			} else {
				$new_fields[] = array(
					'id'      => 'ecommerce_error',
					'title'   => __( 'Ecommerce Error', 'foogallery' ),
					'desc'    => __( 'WooCommerce is not installed!', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'type'    => 'help',
				);
			}

			// find the index of the advanced section.
			$index = $this->find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}

		/**
		 * Returns the list of button behaviour choices.
		 *
		 * @return array
		 */
		public static function get_button_behaviour_choices() {
			return array(
				'fg-woo-add-to-cart-ajax' => __( 'Add to cart (AJAX)', 'foogallery' ),
				//'fg-woo-view-product' => __( 'View product page', 'foogallery' ),
				'fg-woo-add-to-cart' => __( 'Add to cart and refresh page', 'foogallery' ),
				'fg-woo-add-to-cart-redirect' => __( 'Add to cart and redirect to cart', 'foogallery' ),
				'fg-woo-add-to-cart-checkout' => __( 'Add to cart and redirect to checkout', 'foogallery' ),
				//'' => __( 'Hidden', 'foogallery' ),
			);
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
			$fields['foogallery_product']  = array(
				'label'       => __( 'Product ID', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);

			return $fields;
		}

		/**
		 * Return the index of the requested section
		 *
		 * @param array  $fields The fields we are searching through.
		 * @param string $section The section we are looking for.
		 *
		 * @return int
		 */
		private function find_index_of_section( $fields, $section ) {
			$index = 0;
			foreach ( $fields as $field ) {
				if ( isset( $field['section'] ) && $section === $field['section'] ) {
					return $index;
				}
				$index++;
			}
			return $index;
		}
	}
}
