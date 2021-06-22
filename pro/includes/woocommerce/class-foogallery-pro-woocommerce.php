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

				// Set the settings icon for protection.
				add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

				// Add a cart icon to the hover icons.
				add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_icon_choices', array( $this, 'add_cart_hover_icon' ) );

				// Determine ribbon and add data from product.
				add_filter( 'foogallery_datasource_woocommerce_build_attachment', array( $this, 'determine_product_ribbon' ), 10, 2 );
			}
		}


		/**
		 * Determine if a ribbon is needed for the product
		 *
		 * @param $attachment
		 * @param $product
		 *
		 * @return mixed
		 */
		function determine_product_ribbon( $attachment, $product ) {
			if ( 'yes' === foogallery_gallery_template_setting( 'eccommerce_sale_ribbon' ) ) {
				if ( $product->is_on_sale() ) {
					$attachment->ribbon_type = foogallery_gallery_template_setting( 'eccommerce_sale_ribbon_type', 'fg-ribbon-5' );
					$attachment->ribbon_text = foogallery_gallery_template_setting( 'eccommerce_sale_ribbon_text', __( 'Sale!', 'foogallery' ) );
				}
			}

			return $attachment;
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
					'id'       => 'eccommerce_sale_ribbon',
					'title'    => __( 'Show Sale Ribbon', 'foogallery' ),
					'desc'     => __( 'Shows a sale ribbon on the thumbnail for products that are on sale. (Only available if you are using the WooCommerce Product Datasource)', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
					'type'     => 'radio',
					'default'  => 'no',
					'choices'  => array(
						'yes' => __( 'Enabled', 'foogallery' ),
						'no'  => __( 'Disabled', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'eccommerce_sale_ribbon_type',
					'title'    => __( 'Sale Ribbon Type', 'foogallery' ),
					'desc'     => __( 'The type of ribbon to display for products that are on sale.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'select',
					'default'  => 'no',
					'choices'  => FooGallery_Pro_Ribbons::get_ribbon_choices(),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'eccommerce_sale_ribbon',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'yes',
						'data-foogallery-change-selector'          => 'select',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					),
				);

				$new_fields[] = array(
					'id'       => 'eccommerce_sale_ribbon_text',
					'title'    => __( 'Sale Ribbon Text', 'foogallery' ),
					'desc'     => __( 'The text inside the ribbon to display for products that are on sale.', 'foogallery' ),
					'section'  => __( 'Ecommerce', 'foogallery' ),
					'type'     => 'text',
					'default'  => __( 'Sale!', 'foogallery' ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'eccommerce_sale_ribbon',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'yes',
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
