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

				// Add some settings for woocommerce.
				add_filter( 'foogallery_admin_settings_override', array( $this, 'add_ecommerce_settings' ) );

				// Add some help for custom captions.
				add_filter( 'foogallery_build_custom_captions_help-default', array( $this, 'add_product_custom_caption_help' ) );

				// Add a new tab to the product to override button or ribbon.
				add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_settings_tabs' ) );

				// Add some fields to the new FooGallery product setting panel.
				add_action( 'woocommerce_product_data_panels', array( $this, 'add_fields_to_product_panel' ) );

                // Attachment modal actions:
                add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'attachment_modal_display_tab' ), 60 );
                add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'attachment_modal_display_tab_content' ), 60, 1 );
                add_action( 'foogallery_attachment_save_data', array( $this, 'attachment_modal_save_data' ), 60, 2 );
                add_filter( 'foogallery_attachment_modal_data', array( $this, 'attachment_modal_data' ), 70, 4 );
			}

			// Determine ribbon/button data from product.
			add_filter( 'foogallery_datasource_woocommerce_build_attachment', array( $this, 'determine_data_for_product' ), 10, 2 );

			// Enqueue WooCommerce scripts if applicable.
			add_action( 'foogallery_located_template', array( $this, 'enqueue_wc_scripts') );

			// Append product attributes onto the anchor in the galleries.
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_product_attributes' ), 10, 3 );

			// Load product data after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_product_data' ), 10, 2 );

			// Append a nonce that will be used in variation ajax calls.
			add_filter( 'foogallery_lightbox_data_attributes', array( $this, 'add_to_lightbox_options' ) );

			// Build up a product info for a product.
			add_filter( 'wp_ajax_foogallery_product_variations', array( $this, 'ajax_build_product_info' ) );
			add_filter( 'wp_ajax_nopriv_foogallery_product_variations', array( $this, 'ajax_build_product_info' ) );

			//add localised text
			add_filter( 'foogallery_il8n', array( $this, 'add_il8n' ) );

			// Build up captions based on product data.
			add_filter( 'foogallery_build_custom_caption_placeholder_replacement', array( $this, 'build_product_captions' ), 10, 3 );

			// Add button data to the json output
			add_filter( 'foogallery_build_attachment_json', array( $this, 'add_button_to_json' ), 40, 6 );
		}

		/**
		 * Adds a new tab to the products data settings.
		 *
		 * @param $tabs
		 *
		 * @return mixed
		 */
		public function add_product_settings_tabs( $tabs ) {
			$tabs['foogallery'] = array(
				'label'    => __( 'FooGallery', 'foogallery' ),
				'target'   => 'foogallery_product_data',
				//'class'    => array('show_if_virtual'),
				'priority' => 71,
			);

			return $tabs;
		}

		/**
		 * Add FooGallery-specific fields to the product panel
		 *
		 * @return void
		 */
		public function add_fields_to_product_panel() {
			?>
			<style>
				#woocommerce-product-data ul.wc-tabs li.foogallery_options.foogallery_tab a:before {
                    content: "\f161";
                }
			</style>

			<div id="foogallery_product_data" class="panel woocommerce_options_panel hidden">
			<?php

			do_action( 'foogallery_woocommerce_product_data_panels' );

			echo '</div>';
		}

		/**
		 * Add the productId to the json object.
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
			if ( isset( $foogallery_attachment->product ) && self::is_product( $foogallery_attachment->product ) ) {
				$json_object->productId = $foogallery_attachment->product->get_id();
			}

			return $json_object;
		}

		/**
		 * Build up a caption based on product info.
		 *
		 * @param $caption
		 * @param $placeholder
		 * @param $foogallery_attachment
		 *
		 * @return false|mixed|string
		 */
		public function build_product_captions( $caption, $placeholder, $foogallery_attachment ) {
			if ( isset( $foogallery_attachment->product ) && strpos( $placeholder, 'product.' ) === 0 && self::is_product( $foogallery_attachment->product ) ) {
				$property = str_replace( 'product.', '', $placeholder );
				$product = $foogallery_attachment->product;
				switch ( $property ) {
					case 'ID':
					case 'id':
						return $product->get_id();
					case 'short-description':
					case 'short_description':
						return $product->get_short_description();
					case 'url':
						return self::build_product_permalink( $product, $foogallery_attachment->ID );
					case 'price':
						return $product->get_price_html();
					case 'discount%':
						return self::calculate_percentage_discount( $product );
					case 'rating':
						return wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() );
					default:
						if ( method_exists( $product, 'get_' . $property ) ) {
							return call_user_func( array( $product, 'get_' . $property ) );
						}
				}
			}
			return $caption;
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		static function calculate_percentage_discount( $product ) {
			if ( $product->is_on_sale() && ! $product->is_type('variable') ) {

				// Get product prices.
				$regular_price = (float) $product->get_regular_price();
				$sale_price = (float) $product->get_sale_price();

				$saving_percentage = intval( 100 - ( $sale_price / $regular_price * 100 ) );

				return $saving_percentage . '%';
			}
			return '';
		}

		/**
		 * Append some help to the custom captions help, specific for products.
		 *
		 * @param $html
		 *
		 * @return string
		 */
		public function add_product_custom_caption_help( $html ) {
			if ( self::is_woocommerce_activated() ) {
				$html .= __('You can also use the follow product-specific placeholders, if the attachment is linked to a product:', 'foogallery') . '<br /><br />' .
				         '<code>{{product.ID}}</code> - ' . __('Product ID', 'foogallery') . '<br />' .
				         '<code>{{product.title}}</code> - ' . __('Product title', 'foogallery') . '<br />' .
				         '<code>{{product.sku}}</code> - ' . __('Product SKU', 'foogallery') . '<br />' .
				         '<code>{{product.description}}</code> - ' . __('Product description', 'foogallery') . '<br />' .
						 '<code>{{product.short_description}}</code> - ' . __('Product short description', 'foogallery') . '<br />' .
						 '<code>{{product.price}}</code> - ' . __('Product Price', 'foogallery') . '<br />' .
						 '<code>{{product.url}}</code> - ' . __('Product Page URL', 'foogallery') . '<br /><br />';
			}
			return $html;
		}

		/**
		 * AJAX request to build up the product info HTML to show in the lightbox.
		 */
		public function ajax_build_product_info() {
			$request = stripslashes_deep( $_REQUEST );

			if ( wp_verify_nonce( $request['nonce'], $request['nonce_time'] . 'foogallery_product_variations' ) ) {

				$product_id = sanitize_text_field( wp_unslash( $request['product_id'] ) );
				$gallery_id = foogallery_extract_gallery_id( sanitize_text_field( wp_unslash( $request['gallery_id'] ) ) );
				$attachment_id = intval( sanitize_text_field( wp_unslash( $_REQUEST['attachment_id'] ) ) );

				$info = $this->build_product_info( $product_id, $gallery_id, $attachment_id );

				wp_send_json( $info );
			} else {
				wp_send_json( array(
					'error' => __( 'Invalid NONCE!', 'foogallery' ),
					'title' => __( 'Oops!', 'foogallery' ),
					'body' => __( 'Something went wrong! Please try refreshing the page and try again.', 'foogallery' ),
					'purchasable' => false,
				) );
			}

			die();
		}

		/**
		 * Build up product info that is passed back to the front end
		 *
		 * @param $product_id
		 * @param $gallery_id
		 * @param $attachment_id
		 *
		 * @return array
		 */
		public function build_product_info( $product_id, $gallery_id, $attachment_id ) {
			$product = wc_get_product( $product_id );

			$gallery = FooGallery::get_by_id( $gallery_id );

			$response = array();

			if ( empty( $product ) ) {
				$response['error'] = $response['title'] = __( 'No product found!', 'foogallery' );
				$response['body'] = __( 'We could not load any product information, as the product was not found!', 'foogallery' );
				$response['purchasable'] = false;
			} else {
				$description = $product->get_description();
				if ( $this->is_html( $description ) ) {
					$description = wp_kses( $description, wp_kses_allowed_html() );
				} else {
					$description = '<p>' . esc_html( $description ) . '</p>';
				}
				$html = $description;
				$response['title'] = $product->get_name();
				$response['purchasable'] = $product->is_purchasable();
				if ( '' === $gallery->get_setting( 'ecommerce_lightbox_show_add_to_cart_button', 'shown' ) ) {
					// Hide the "Add to Cart" button based on setting.
					$response['purchasable'] = false;
				}
				// Only if its purchasable and a variable product, then build up the variation html.
				if ( $response['purchasable'] && is_a( $product, 'WC_Product_Variable' ) ) {
					$html .= $this->build_product_variation_table( $product );
				}
				if ( '' !== $gallery->get_setting( 'ecommerce_lightbox_show_view_product_button', '' ) ) {
					$response['product_url'] = self::build_product_permalink( $product, $attachment_id );
				}

				$response['body'] = $html;

				$response = apply_filters( 'foogallery_ecommerce_build_product_info_response', $response, $product, $gallery, $attachment_id );
			}

			return $response;
		}

		/**
		 * Build up a product permalink URL
		 *
		 * @param      $product
		 * @param      $attachment_id
		 * @param null $gallery
		 *
		 * @return string
		 */
		static function build_product_permalink( $product, $attachment_id, $gallery = null ) {
			if ( is_null( $gallery ) ) {
				global $current_foogallery;
				$gallery = $current_foogallery;
			}

			return apply_filters( 'foogallery_ecommerce_build_product_permalink', $product->get_permalink(), $product, $attachment_id, $gallery );
		}

		/**
		 * Check if string contains any html tags.
		 *
		 * @param $string
		 *
		 * @return bool
		 */
		private function is_html( $string ) {
			return preg_match('/<\s?[^\>]*\/?\s?>/i', $string);
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
			$purchasable_variation_count = 0;
			foreach ( $variations as $value ) {
				$single_variation = new WC_Product_Variation( $value );

				if ( $single_variation->is_purchasable() ) {
					$variation_id = $single_variation->get_id();
					$html         .= '<tr data-variation_id="' . esc_attr( $variation_id ) . '" title="' . esc_attr( $single_variation->get_description() ) .  '">';
					$price = $single_variation->get_price_html();
					$html .= '<td><input type="radio" name="foogallery_product_variation_' . esc_attr( $product->get_id() ) . '" value="' . esc_attr( $variation_id ) . '" ' . $checked . ' /></td>';
					$checked = '';
					$has_all_attributes_set = true;
					foreach ( $attributes as $attribute_key => $attribute_label ) {
						$attribute_value = $single_variation->get_attribute( $attribute_key );
						if ( empty( $attribute_value ) ) {
							$has_all_attributes_set = false;
						}
						$html .= '<td>' . $attribute_value . '</td>';
					}
					if ( $has_all_attributes_set ) {
						$purchasable_variation_count++;
					}
					$html .= '<td>' . $price . '</td>';
					$html .= '</tr>';
				}
			}

			$html .= '</tbody></table>';

			if ( $purchasable_variation_count === 0 ) {
				return '';
			}

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
			$ecommerce_lightbox_product_information = foogallery_gallery_template_setting( 'ecommerce_lightbox_product_information', 'none' );

			if ( 'none' !== $ecommerce_lightbox_product_information ) {
				$time                   = time();
				$options['cart']        = $ecommerce_lightbox_product_information;
				$options['cartVisible'] = true;
				$options['cartTimeout'] = $time;
				$options['cartNonce']   = wp_create_nonce( $time . 'foogallery_product_variations' );
				$options['cartAjax']    = admin_url( 'admin-ajax.php' );
				$options['admin']       = is_admin();
			}
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

				self::determine_extra_data_for_product( $foogallery_attachment, $foogallery_attachment->product );
			}
		}

        public static function is_product( $product ) {
            return isset( $product ) && is_object( $product ) && is_a( $product, 'WC_Product' );
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
			if ( isset( $foogallery_attachment->product ) && self::is_product( $foogallery_attachment->product ) ) {
				$attr['data-product-id'] = $foogallery_attachment->product->get_id();
			}
			return $attr;
		}

		/**
		 * Enqueue the WooCommerce scripts if add to cart ajax is enabled for one of the action buttons
		 */
		public function enqueue_wc_scripts() {
			$enqueue = false;

			// View Products Buttons
			$ecommerce_button_view_product = foogallery_gallery_template_setting( 'ecommerce_button_view_product' );
			$ecommerce_button_view_product_behaviour = foogallery_gallery_template_setting( 'ecommerce_button_view_product_behaviour' );

			// Add To Cart Buttons
			$ecommerce_button_add_to_cart = foogallery_gallery_template_setting( 'ecommerce_button_add_to_cart' );
			$ecommerce_button_add_to_cart_behaviour = foogallery_gallery_template_setting( 'ecommerce_button_add_to_cart_behaviour' );

			// Select Options Buttons
			$ecommerce_button_variable = foogallery_gallery_template_setting( 'ecommerce_button_variable' );

			// If the View Product button is visible and behaviour is to open lightbox.
			if ( $ecommerce_button_view_product !== '' && $ecommerce_button_view_product_behaviour === '' ) {
				$enqueue = true;
			}

			// If Add To Cart buttons is shown and behaviour is ajax add to cart.
			if ( $ecommerce_button_add_to_cart === 'shown' && $ecommerce_button_add_to_cart_behaviour === 'fg-woo-add-to-cart-ajax' ) {
				$enqueue = true;
			}

			// If Select Options button is shown.
			if ( $ecommerce_button_variable === 'shown' ) {
				$enqueue = true;
			}

			if ( $enqueue ) {
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
		public function determine_data_for_product( $attachment, $product ) {
			self::determine_extra_data_for_product( $attachment, $product );

			return $attachment;
		}

		/**
		 * Determine if ribbons/buttons are needed for the product
		 *
		 * @param $attachment
		 * @param WC_Product $product
		 *
		 * @return mixed
		 */
		static function determine_extra_data_for_product( &$attachment, $product ) {
            if ( !self::is_product( $product ) ) {
                return $attachment;
            }

			// Do we need to add ribbons?
			$ribbon_type = foogallery_gallery_template_setting( 'ecommerce_sale_ribbon_type', 'fg-ribbon-5' );
			if ( 'none' !== $ribbon_type ) {
				if ( $product->is_on_sale() ) {
					$attachment->ribbon_type = $ribbon_type;
					$attachment->ribbon_text = foogallery_gallery_template_setting( 'ecommerce_sale_ribbon_text', __( 'Sale', 'foogallery' ) );
					if ( strpos( $attachment->ribbon_text, '{{%}}' ) > 0 ) {
						$attachment->ribbon_text = str_replace( '{{%}}', self::calculate_percentage_discount( $product ), $attachment->ribbon_text );
					}
				}
			}

			if ( !is_a( $product, 'WC_Product_Variable' ) ) {
				// Do we need "Add To Cart" button?
				$button_add_to_cart = foogallery_gallery_template_setting( 'ecommerce_button_add_to_cart', '' );
				if ( '' !== $button_add_to_cart ) {
					$button_add_to_cart_behaviour = foogallery_gallery_template_setting( 'ecommerce_button_add_to_cart_behaviour', 'fg-woo-add-to-cart-ajax' );
					$button_add_to_cart_url       = self::determine_url( $button_add_to_cart_behaviour, $product, $attachment );
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
						'class' => 'fg-woo-select-variation',
						'text'  => foogallery_gallery_template_setting( 'ecommerce_button_variable_text', __( 'Select Options', 'foogallery' ) ),
					);
				}
			}

			// Do we need to add "View Product" button?
			$button_view_product = foogallery_gallery_template_setting( 'ecommerce_button_view_product', '' );
			if ( '' !== $button_view_product ) {
				$button_view_product_behaviour = foogallery_gallery_template_setting( 'ecommerce_button_view_product_behaviour', 'fg-woo-view-product' );
				$button = array(
					'class' => $button_view_product_behaviour,
					'text'  => foogallery_gallery_template_setting( 'ecommerce_button_view_product_text', __( 'View Product', 'foogallery' ) ),
				);
				if ( '' !== $button_view_product_behaviour ) {
					$button['url'] = self::build_product_permalink( $product, $attachment->ID );
				}

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
		 * @param FooGalleryAttachment $attachment
		 *
		 * @return string
		 */
		static function determine_url( $url_type, $product, $attachment ) {
			global $current_foogallery;

			if ( $product->is_purchasable() ) {
				$args = array(
					'add-to-cart' => $product->get_id(),
				);
				if ( isset( $current_foogallery ) ) {
					$args['foogallery_id'] = $current_foogallery->ID;
					if ( isset( $attachment ) && $attachment->ID > 0 ) {
						$args['foogallery_attachment_id'] = $attachment->ID;
					}
				}
				
				switch ( $url_type ) {
					case 'fg-woo-add-to-cart':
					case 'fg-woo-add-to-cart-ajax':
						if ( is_admin() ) {
							return add_query_arg( $args, get_home_url() );
						} else {
							return add_query_arg( $args );
						}
					case 'fg-woo-add-to-cart-redirect' :
						return add_query_arg( $args, wc_get_cart_url() );
					case 'fg-woo-add-to-cart-checkout':
						return add_query_arg( $args, wc_get_checkout_url() );
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
			if ( self::is_woocommerce_activated() ) {
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
		static function is_woocommerce_activated() {
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

			if ( self::is_woocommerce_activated() ) {

				$new_fields[] = array(
					'id'      => 'ecommerce_info',
					'title'   => __( 'Ecommerce Info', 'foogallery' ),
					'desc'    => __( 'The below settings will only apply if you are using the WooCommerce Product datasource, or if individual attachments are linked to WooCommerce products.', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
					'type'    => 'help',
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_sale_ribbon_type',
					'title'    => __( 'Sale Ribbon', 'foogallery' ),
					'desc'     => __( 'The type of ribbon to display for products that are on sale.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'select',
					'default'  => 'fg-ribbon-3',
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
					'desc'     => __( 'The text inside the ribbon to display for products that are on sale. Use "{{%}}" to display the percentage discount.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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
					'id'       => 'ecommerce_button_view_product_behaviour',
					'title'    => __( '"View Product" Button Behaviour', 'foogallery' ),
					'desc'     => __( 'What happens when the "View Product" button is clicked.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'select',
					'choices'  => array(
						'fg-woo-view-product' => __( 'Redirect to product page', 'foogallery' ),
						'' => __( 'Open lightbox', 'foogallery' ),
					),
					'default'  => 'fg-woo-view-product',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_button_view_product',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'select',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'select :selected',
					),
				);


				$new_fields[] = array(
					'id'       => 'ecommerce_button_view_product_text',
					'title'    => __( '"View Product" Button Text', 'foogallery' ),
					'desc'     => __( 'The text displayed on the "View Product" button.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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
					'id'       => 'ecommerce_button_add_to_cart_behaviour',
					'title'    => __( '"Add To Cart" Button Behaviour', 'foogallery' ),
					'desc'     => __( 'What happens when the "Add to Cart" button is clicked.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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
					'subsection' => array( 'ecommerce-general' => __( 'General', 'foogallery' ) ),
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

				$new_fields[] = array(
					'id'      => 'ecommerce_lightbox_info',
					'title'   => __( 'Ecommerce Lightbox Info', 'foogallery' ),
					'desc'    => __( 'You can choose to display product information within the lightbox, if items are linked to a WooCommerce Product. This only works with the PRO lightbox.', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-lightbox' => __( 'Lightbox', 'foogallery' ) ),
					'type'    => 'help',
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_lightbox_product_information',
					'title'    => __( 'Lightbox Product Info', 'foogallery' ),
					'desc'     => __( 'You can show product information in the PRO lightbox, including product variations, which the visitor can add to cart.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-lightbox' => __( 'Lightbox', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'none',
					'spacer'   => '<span class="spacer"></span>',
					'choices'  => array(
						'left'   => __( 'Left Panel', 'foogallery'),
						'right'  => __( 'Right Panel', 'foogallery'),
						'none'   => __( 'Do Not Show', 'foogallery'),
					),
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_lightbox_show_add_to_cart_button',
					'title'    => __( 'Show "Add to Cart" Button', 'foogallery' ),
					'desc'     => __( 'Within the lightbox, shows the "Add to Cart" button.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-lightbox' => __( 'Lightbox', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'shown',
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
					'id'       => 'ecommerce_lightbox_show_view_product_button',
					'title'    => __( 'Show "View Product" Button', 'foogallery' ),
					'desc'     => __( 'Within the lightbox, shows an extra button that redirects to the product page.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'subsection' => array( 'ecommerce-lightbox' => __( 'Lightbox', 'foogallery' ) ),
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
			} else {
				$new_fields[] = array(
					'id'      => 'ecommerce_error',
					'title'   => __( 'Ecommerce Error', 'foogallery' ),
					'desc'    => __( 'WooCommerce is not installed! Ecommerce features are only available when WooCommerce is activated.', 'foogallery' ),
					'section' => __( 'Ecommerce', 'foogallery' ),
					'type'    => 'help',
				);
			}

			// find the index of the advanced section.
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}

		/**
		 * Add some ecommerce settings
		 *
		 * @param array $settings The settings array.
		 *
		 * @return array
		 */
		public function add_ecommerce_settings( $settings ) {
			$settings['tabs']['ecommerce'] = __( 'Ecommerce', 'foogallery' );

			$settings['settings'][] = array(
				'id'      => 'ecommerce_lightbox_add_to_cart_text',
				'title'   => __( 'Add to Cart Text', 'foogallery' ),
				'desc'    => __( 'The "Add to Cart" button text that is shown within the lightbox.', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Add to Cart', 'foogallery' ),
				'section' => __( 'Language', 'foogallery' ),
				'tab'     => 'ecommerce'
			);

			$settings['settings'][] = array(
				'id'      => 'ecommerce_lightbox_view_product_text',
				'title'   => __( 'View Product Text', 'foogallery' ),
				'desc'    => __( 'The "View Product" button text that is shown within the lightbox.', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'View Product', 'foogallery' ),
				'section' => __( 'Language', 'foogallery' ),
				'tab'     => 'ecommerce'
			);

			$settings['settings'][] = array(
				'id'      => 'ecommerce_lightbox_success_text',
				'title'   => __( 'Success Message Text', 'foogallery' ),
				'desc'    => __( 'The success message shown after a product has been added to the cart, within the lightbox.', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Successfully added to cart.', 'foogallery' ),
				'section' => __( 'Language', 'foogallery' ),
				'tab'     => 'ecommerce'
			);

			$settings['settings'][] = array(
				'id'      => 'ecommerce_lightbox_error_text',
				'title'   => __( 'Error Message Text', 'foogallery' ),
				'desc'    => __( 'The error message shown after a product could not be added to the cart, within the lightbox.', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Something went wrong adding to cart!', 'foogallery' ),
				'section' => __( 'Language', 'foogallery' ),
				'tab'     => 'ecommerce'
			);

            $settings['settings'][] = array(
				'id'      => 'ecommerce_alternative_download_paths',
				'title'   => __( 'Use Alternative File Download Paths', 'foogallery' ),
				'desc'    => __( 'To overcome some limitations found in WooCommerce, we adjust the default file download paths by default. Enabling this will turn off those adjustments.', 'foogallery' ),
				'type'    => 'checkbox',
				'section' => __( 'File Downloads', 'foogallery' ),
				'tab'     => 'ecommerce'
			);

			return $settings;
		}

		/**
		 * Add localisation settings
		 *
		 * @param $il8n
		 *
		 * @return string
		 */
		function add_il8n( $il8n ) {

			$add_to_cart_text = foogallery_get_language_array_value( 'ecommerce_lightbox_add_to_cart_text', __( 'Add to Cart', 'foogallery' ) );
			if ( $add_to_cart_text !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'template' => array(
						'core' => array(
							'panel' => array(
								'media' => array(
									'product' => array(
										'addToCart' => $add_to_cart_text
									)
								)
							)
						)
					)
				) );
			}

			$view_product_text = foogallery_get_language_array_value( 'ecommerce_lightbox_view_product_text', __( 'View Product', 'foogallery' ) );
			if ( $view_product_text !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'template' => array(
						'core' => array(
							'panel' => array(
								'media' => array(
									'product' => array(
										'viewProduct' => $view_product_text
									)
								)
							)
						)
					)
				) );
			}

			$success_message_text = foogallery_get_language_array_value( 'ecommerce_lightbox_success_text', __( 'Successfully added to cart.', 'foogallery' ) );
			if ( $success_message_text !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'template' => array(
						'core' => array(
							'panel' => array(
								'media' => array(
									'product' => array(
										'success' => $success_message_text
									)
								)
							)
						)
					)
				) );
			}

			$error_message_text = foogallery_get_language_array_value( 'ecommerce_lightbox_error_text', __( 'Something went wrong adding to cart!', 'foogallery' ) );
			if ( $error_message_text !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'template' => array(
						'core' => array(
							'panel' => array(
								'media' => array(
									'product' => array(
										'error' => $error_message_text
									)
								)
							)
						)
					)
				) );
			}

			return $il8n;
		}


		/**
		 * Returns the list of button behaviour choices.
		 *
		 * @return array
		 */
		public static function get_button_behaviour_choices() {
			return array(
				'fg-woo-add-to-cart-ajax' => __( 'Add to cart (AJAX)', 'foogallery' ),
				'fg-woo-add-to-cart' => __( 'Add to cart and refresh page', 'foogallery' ),
				'fg-woo-add-to-cart-redirect' => __( 'Add to cart and redirect to cart', 'foogallery' ),
				'fg-woo-add-to-cart-checkout' => __( 'Add to cart and redirect to checkout', 'foogallery' ),
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
         * Image modal EXIF tab title
         */
        public function attachment_modal_display_tab() { ?>
            <div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-commerce">
                <input type="radio" name="tabset" id="foogallery-tab-commerce" aria-controls="foogallery-panel-commerce">
                <label for="foogallery-tab-commerce"><?php _e('Commerce', 'foogallery'); ?></label>
            </div>
        <?php }

        /**
         * Image modal EXIF tab content
         */
        public function attachment_modal_display_tab_content( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-commerce" class="tab-panel">
						<div class="settings">
							<span class="setting" data-setting="button-text">
								<label for="attachment-details-two-column-button-text" class="name"><?php _e('Button Text', 'foogallery'); ?></label>
								<input type="text" name="foogallery[button-text]" id="attachment-details-two-column-button-text" value="<?php echo $modal_data['foogallery_button_text']; ?>">
							</span>
							<span class="setting" data-setting="button-url">
								<label for="attachment-details-two-column-button-url" class="name"><?php _e('Button URL', 'foogallery'); ?></label>
								<input type="text" name="foogallery[button-url]" id="attachment-details-two-column-button-url" value="<?php echo $modal_data['foogallery_button_url']; ?>">
							</span>
							<span class="setting" data-setting="ribbon">
								<label for="attachment-details-two-column-ribbon" class="name"><?php _e('Ribbon', 'foogallery'); ?></label>
								<select id="attachment-details-two-column-ribbon" name="foogallery[ribbon]">
									<option selected="selected" value=""><?php _e('None', 'foogallery'); ?></option>
									<option value="fg-ribbon-5" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-5', true ); ?>><?php _e('Type 1 (top-right, diagonal, green)', 'foogallery'); ?></option>
									<option value="fg-ribbon-3" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-3', true ); ?>><?php _e('Type 2 (top-left, small, blue)', 'foogallery'); ?></option>
									<option value="fg-ribbon-4" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-4', true ); ?>><?php _e('Type 3 (top, full-width, yellow)', 'foogallery'); ?></option>
									<option value="fg-ribbon-6" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-6', true ); ?>><?php _e('Type 4 (top-right, rounded, pink)', 'foogallery'); ?></option>
									<option value="fg-ribbon-2" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-2', true ); ?>><?php _e('Type 5 (top-left, medium, purple)', 'foogallery'); ?></option>
									<option value="fg-ribbon-1" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-1', true ); ?>><?php _e('Type 6 (top-left, vertical, orange)', 'foogallery'); ?></option>
								</select>
							</span>
							<span class="setting" data-setting="ribbon-text">
								<label for="attachment-details-two-column-ribbon-text" class="name"><?php _e('Ribbon Text', 'foogallery'); ?></label>
								<input type="text" name="foogallery[ribbon-text]" id="attachment-details-two-column-ribbon-text" value="<?php echo $modal_data['foogallery_ribbon_text']; ?>">
							</span>
							<span class="setting" data-setting="product-id">
								<label for="attachment-details-two-column-product-id" class="name"><?php _e('Product ID', 'foogallery'); ?></label>
								<input type="text" name="foogallery[product-id]" id="attachment-details-two-column-product-id" value="<?php echo $modal_data['foogallery_product']; ?>">
							</span>
						</div>
					</section>
					<?php
				}
			}
        }

        /**
         * Save EXIF tab data content
         *
         * @param $img_id int attachment id to update data
         *
         * @param $foogallery array of form post data
         *
         */
        public function attachment_modal_save_data( $img_id, $foogallery ) {
			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {
				foreach( $foogallery as $key => $val ) {
					if ( $key === 'button-text' ) {
						update_post_meta( $img_id, '_foogallery_button_text', $val );
					}
					if ( $key === 'button-url' ) {
						update_post_meta( $img_id, '_foogallery_button_url', $val );
					}
					if ( $key === 'ribbon' ) {
						update_post_meta( $img_id, '_foogallery_ribbon', $val );
					}
					if ( $key === 'ribbon-text' ) {
						update_post_meta( $img_id, '_foogallery_ribbon_text', $val );
					}
					if ( $key === 'product-id' ) {
						update_post_meta( $img_id, '_foogallery_product', $val );
					}
				}
			}
        }


		/**
		 * Image modal more tab data update
		 */
		public function attachment_modal_data( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                $modal_data['foogallery_button_text'] = get_post_meta( $attachment_id, '_foogallery_button_text', true );
                $modal_data['foogallery_button_url'] =  get_post_meta( $attachment_id, '_foogallery_button_url', true );
                $modal_data['foogallery_ribbon'] =      get_post_meta( $attachment_id, '_foogallery_ribbon', true );
                $modal_data['foogallery_ribbon_text'] = get_post_meta( $attachment_id, '_foogallery_ribbon_text', true );
                $modal_data['foogallery_product'] =     get_post_meta( $attachment_id, '_foogallery_product', true );
            }
			return $modal_data;
		}

	}
}
