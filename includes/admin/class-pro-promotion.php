<?php
/*
 * FooGallery Pro Feature Promotion class
 */

if ( ! class_exists( 'FooGallery_Pro_Promotion' ) ) {
	class FooGallery_Pro_Promotion {

		function __construct() {
			add_action( 'admin_init', array( $this, 'include_promos' ) );
		}

		/**
		 * conditionally include promos
		 */
		function include_promos() {
			if ( $this->can_show_promo() ) {
				add_filter( 'foogallery_admin_settings_override', array( $this, 'include_promo_settings' ) );
				add_filter( 'foogallery_gallery_template_paging_type_choices', array( $this, 'add_promo_paging_choices' ) );
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_paging_promo_fields' ), 10, 2 );
			}
		}

		/**
		 * Returns true if the promo areas can be shown
		 * @return bool
		 */
		private function can_show_promo() {
			return foogallery_get_setting( 'pro_promo_disabled' ) !== 'on';
		}

		/*
		 * Include promo settings
		 */
		public function include_promo_settings( $settings ) {
			$settings['settings'][] = array(
				'id'      => 'pro_promo_disabled',
				'title'   => __( 'Disable PRO Promotions', 'foogallery' ),
				'desc'    => __( 'Disable all premium upsell promotions throughout the WordPress admin.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'advanced'
			);

			if ( $this->can_show_promo() ) {
				//include promo tabs and settings
				$settings['tabs']['video'] = __( 'Video', 'foogallery' );

				$settings['settings'][] = array(
					'id'    => 'pro_promo_video',
					'title' => __( 'Video Support', 'foogallery' ),
					'desc'  => __( 'FooGallery ', 'foogallery' ),
					'type'  => 'html',
					'tab'   => 'video'
				);
			}

			return $settings;
		}

		/**
		 * Adds promo choices for paging
		 * @param $choices
		 *
		 * @return mixed
		 */
		public function add_promo_paging_choices( $choices ) {
			$choices['promo-pag'] = '<div class="foogallery-pro">' . __( 'Pagination', 'foogallery' ) . '</div>';
			$choices['promo-inf'] = '<div class="foogallery-pro">' . __( 'Infinite Scroll', 'foogallery' ) . '</div>';
			$choices['promo-load'] = '<div class="foogallery-pro">' . __( 'Load More', 'foogallery' ) . '</div>';
			return $choices;
		}

		/**
		 * Add promo paging fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_paging_promo_fields( $fields, $template ) {
			if ( $template && array_key_exists( 'paging_support', $template ) && true === $template['paging_support'] ) {
				$fields[] = array(
					'id'       => 'promo_paging',
					'title'    => __( 'Paging Type', 'foogallery' ),
					'desc'     => __( 'Add paging to a large gallery.', 'foogallery' ),
					'section'  => __( 'Paging', 'foogallery' ),
					'type'     => 'promo',
					'row_data'=> array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview' => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'paging_type',
						'data-foogallery-show-when-field-operator' => 'in',
						'data-foogallery-show-when-field-value'    => 'promo',
					)
				);
			}

			return $fields;
		}
	}
}