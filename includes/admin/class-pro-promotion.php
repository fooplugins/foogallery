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

				//presets
				add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_type_choices', array( $this, 'add_preset_type' ) );
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_preset_fields' ), 99, 2 );
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

		/**
		 * Adds the promo preset type for hover effect type
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_preset_type( $choices ) {
			$new_choices = array();

			$choices_before = array_slice( $choices, 0, 1 );
			$choices_after = array_slice( $choices, 1 );

			$new_choices['promo-presets'] = array(
				'label'   => __( 'Preset',   'foogallery' ),
                'tooltip' => __('Choose from 11 stylish hover effect presets in FooGallery PRO.', 'foogallery'),
                'class'   => 'foogallery-promo',
				'icon'    => 'dashicons-star-filled'
            );

			return $choices_before + $new_choices + $choices_after;
		}

		/**
		 * Return the index of the requested section
		 *
		 * @param $fields
		 * @param $section
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

		/**
		 * Return the index of the requested field
		 *
		 * @param $fields
		 * @param $field_id
		 *
		 * @return int
		 */
		private function find_index_of_field( $fields, $field_id ) {
			$index = 0;
			foreach ( $fields as $field ) {
				if ( isset( $field['id'] ) && $field_id === $field['id'] ) {
					return $index;
				}
				$index++;
			}
			return $index;
		}

		/**
		 * Add the fields for presets promos
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_preset_fields( $fields, $template ) {
			$index_of_hover_effect_preset_field = $this->find_index_of_field( $fields, 'hover_effect_preset' );

			$new_fields[] = array(
				'id'       => 'hover_effect_help',
				'title'    => __( 'Hover Effect Presets', 'foogallery' ),
				'desc'     => __( 'There are 11 stylish hover effect presets to choose from, which takes all the hard work out of making your galleries look professional and elegant.', 'foogallery' ) .
				              '<br />' . __( 'Some of the effects like "Sarah" add subtle colors on hover, while other effects like "Layla" and "Oscar" add different shapes to the thumbnail.', 'foogallery') .
				              '<br />' . __(' You really need to see all the different effects in action to appreciate them.', 'foogallery' ),
				'cta_text' => __( 'View Demo', 'foogallery' ),
				'cta_link' => 'https://fooplugins.com/foogallery/hover-presets/',
				'section'  => __( 'Hover Effects', 'foogallery' ),
				'type'     => 'promo',
				'row_data' => array(
					'data-foogallery-hidden'                => true,
					'data-foogallery-show-when-field'       => 'hover_effect_type',
					'data-foogallery-show-when-field-value' => 'promo-presets',
				)
			);

			$new_fields[] = array(
				'id'       => 'hover_effect_preset',
				'title'    => __( 'Preset', 'foogallery' ),
				'section'  => __( 'Hover Effects', 'foogallery' ),
				'default'  => 'fg-preset fg-sadie',
				'type'     => 'radio',
				'choices'  =>  array(
						'sadie'   => __( 'Sadie',   'foogallery' ),
						'layla'   => __( 'Layla',   'foogallery' ),
						'oscar'   => __( 'Oscar',   'foogallery' ),
						'sarah'   => __( 'Sarah',   'foogallery' ),
						'goliath' => __( 'Goliath', 'foogallery' ),
						'jazz'    => __( 'Jazz',    'foogallery' ),
						'lily'    => __( 'Lily',    'foogallery' ),
						'ming'    => __( 'Ming',    'foogallery' ),
						'selena'  => __( 'Selena',  'foogallery' ),
						'steve'   => __( 'Steve',   'foogallery' ),
						'zoe'     => __( 'Zoe',     'foogallery' ),
				),
				'spacer'   => '<span class="spacer"></span>',
				'promo'    => __( 'A preset styling that is used for the hover effect.', 'foogallery' ),
				'row_data' => array(
					'data-foogallery-change-selector'       => 'input:radio',
					'data-foogallery-value-selector'        => 'input:checked',
					'data-foogallery-preview'               => 'class',
					'data-foogallery-hidden'                => true,
					'data-foogallery-show-when-field'       => 'hover_effect_type',
					'data-foogallery-show-when-field-value' => 'promo-presets',
				)
			);

			array_splice( $fields, $index_of_hover_effect_preset_field, 0, $new_fields );

			return $fields;
		}
	}
}