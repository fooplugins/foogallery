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

				// Determine ribbon/button data from product.
				add_filter( 'foogallery_datasource_woocommerce_build_attachment', array( $this, 'determine_extra_data_for_product' ), 10, 2 );
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

			// Do we need to add button 1?
			$button_1 = foogallery_gallery_template_setting( 'ecommerce_action_button_1', 'fg-woo-view-product' );
			if ( 'none' !== $button_1 ) {
				$button_1_url = $this->determine_url( $button_1, $product );
				if ( !empty( $button_1_url ) ) {
					$attachment->buttons[] = array(
						'class' => $button_1,
						'text'  => foogallery_gallery_template_setting( 'ecommerce_action_button_1_text', __( 'View Product', 'foogallery' ) ),
						'url'   => $button_1_url,
					);
				}
			}

			// Do we need to add button 2?
			$button_2 = foogallery_gallery_template_setting( 'ecommerce_action_button_2', 'none' );
			if ( 'none' !== $button_2 ) {
				$button_2_url = $this->determine_url( $button_2, $product );
				if ( !empty( $button_2_url ) ) {
					$attachment->buttons[] = array(
						'class' => $button_2,
						'text'  => foogallery_gallery_template_setting( 'ecommerce_action_button_2_text', __( 'Add To Cart', 'foogallery' ) ),
						'url'   => $button_2_url,
					);
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
			if ( 'fg-woo-view-product' === $url_type ) {
				return $product->get_permalink();
			}

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
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'yes',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_action_button_1',
					'title'    => __( 'Action Button 1', 'foogallery' ),
					'desc'     => __( 'Shows a button that is used to used to perform an action on the product.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'select',
					'choices'  => self::get_button_behaviour_choices(),
					'default'  => 'fg-woo-view-product',
					'row_data' => array(
						'data-foogallery-change-selector' => 'select',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'select :selected',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_action_button_1_text',
					'title'    => __( 'Action Button 1 Text', 'foogallery' ),
					'desc'     => __( 'The text displayed on the first action button.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'text',
					'default'  => __( 'View Product', 'foogallery' ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_action_button_1',
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field-value'    => 'none',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_action_button_2',
					'title'    => __( 'Action Button 2', 'foogallery' ),
					'desc'     => __( 'Shows a second button that is used to used to perform an action on the product.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'select',
					'choices'  => self::get_button_behaviour_choices(),
					'default'  => 'none',
					'row_data' => array(
						'data-foogallery-change-selector' => 'select',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'select :selected',
					),
				);

				$new_fields[] = array(
					'id'       => 'ecommerce_action_button_2_text',
					'title'    => __( 'Action Button 2 Text', 'foogallery' ),
					'desc'     => __( 'The text displayed on the second action button.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'text',
					'default'  => __( 'Add To Cart', 'foogallery' ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'ecommerce_action_button_2',
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
				'none' => __( 'None', 'foogallery' ),
				'fg-woo-view-product' => __( 'View product page', 'foogallery' ),
				'fg-woo-add-to-cart' => __( 'Add to cart and refresh page', 'foogallery' ),
				'fg-woo-add-to-cart-ajax' => __( 'Add to cart (AJAX)', 'foogallery' ),
				'fg-woo-add-to-cart-redirect' => __( 'Add to cart and redirect to cart', 'foogallery' ),
				'fg-woo-add-to-cart-checkout' => __( 'Add to cart and redirect to checkout', 'foogallery' ),
			);
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
