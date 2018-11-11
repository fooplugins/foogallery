<?php
/**
 * FooGallery Pro Paging Class
 */
if ( ! class_exists( 'FooGallery_Pro_Paging' ) ) {

	class FooGallery_Pro_Paging {

		function __construct() {
			add_filter( 'foogallery_gallery_template_paging_type_choices', array( $this, 'add_pro_paging_choices' ) );

			if ( is_admin() ) {
				//add a global setting to change the All filter
				add_filter( 'foogallery_admin_settings_override', array( $this, 'add_language_settings' ) );
			}

			//add the paging attributes to the gallery container
			add_filter( 'foogallery_build_container_data_options', array( $this, 'add_paging_data_options' ), 10, 3 );
		}

		/**
		 * Adds the presets that are available in the PRO version
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_pro_paging_choices( $choices ) {
			$choices['pagination'] = __( 'Pagination', 'foogallery' );
			$choices['infinite'] = __( 'Infinite Scroll', 'foogallery' );
			$choices['loadMore'] = __( 'Load More', 'foogallery' );
			return $choices;
		}

		/**
		 * Add global setting to override the "All" text used in the filtering
		 * @param $settings
		 *
		 * @return mixed
		 */
		public function add_language_settings( $settings ) {

			$settings['settings'][] = array(
				'id'      => 'language_paging_loadmore_text',
				'title'   => __( 'Paging Load More Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Load More', 'foogallery' ),
				'tab'     => 'language'
			);

			return $settings;
		}

		/**
		 * Add the required paging data options if needed
		 *
		 * @param $attributes array
		 * @param $gallery    FooGallery
		 *
		 * @return array
		 */
		function add_paging_data_options( $options, $gallery, $attributes ) {

			$paging_loadmore_text_default = __( 'Load More', 'foogallery' );
			$paging_loadmore_text = foogallery_get_setting( 'language_paging_loadmore_text', $paging_loadmore_text_default );
			if ( empty( $paging_loadmore_text ) ) {
				$paging_loadmore_text = $paging_loadmore_text_default;
			}
			if ( $paging_loadmore_text_default !== $paging_loadmore_text ) {
				if ( !array_key_exists( 'il8n', $options ) ) {
					$options['il8n'] = array();
				}

				$options['il8n']['paging'] = array(
					'button' => $paging_loadmore_text
				);
			}

			return $options;
		}
	}
}