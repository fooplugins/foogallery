<?php
/**
 * FooGallery Pro Paging Class
 */
if ( ! class_exists( 'FooGallery_Pro_Paging' ) ) {

	class FooGallery_Pro_Paging {

		function __construct() {
			add_filter( 'foogallery_gallery_template_paging_type_choices', array( $this, 'add_pro_paging_choices' ) );

			if ( is_admin() ) {
				//add a global setting to change the Load More button text
				add_filter( 'foogallery_admin_settings_override', array( $this, 'add_language_settings' ), 40 );
			}

			//add localised text
			add_filter( 'foogallery_il8n', array( $this, 'add_il8n' ) );
		}

		/**
		 * Add localisation settings
		 *
		 * @param $il8n
		 *
		 * @return string
		 */
		function add_il8n( $il8n ) {

			$paging_dots_current_entry = foogallery_get_language_array_value( 'language_paging_current', __( 'Current page', 'foogallery' ) );
			if ( $paging_dots_current_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'dots' => array(
							'current' => $paging_dots_current_entry
						),
						'pagination' => array(
							'labels' => array(
								'current' => $paging_dots_current_entry
							)
						)
					)
				) );
			}

			$paging_dots_page_entry = foogallery_get_language_array_value( 'language_paging_page', __( 'Page {PAGE}', 'foogallery' ) );
			if ( $paging_dots_page_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'dots' => array(
							'page' => $paging_dots_page_entry
						),
						'pagination' => array(
							'labels' => array(
								'page' => $paging_dots_page_entry
							)
						)
					)
				) );
			}

			$paging_first_entry = foogallery_get_language_array_value( 'language_paging_first_text', __( 'First page', 'foogallery' ) );
			if ( $paging_first_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'pagination' => array(
							'labels' => array(
								'first' => $paging_first_entry
							)
						)
					)
				) );
			}

			$paging_prev_entry = foogallery_get_language_array_value( 'language_paging_prev_text', __( 'Previous page', 'foogallery' ) );
			if ( $paging_prev_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'pagination' => array(
							'labels' => array(
								'prev' => $paging_prev_entry
							)
						)
					)
				) );
			}

			$paging_next_entry = foogallery_get_language_array_value( 'language_paging_next_text', __( 'Next page', 'foogallery' ) );
			if ( $paging_next_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'pagination' => array(
							'labels' => array(
								'next' => $paging_next_entry
							)
						)
					)
				) );
			}

			$paging_last_entry = foogallery_get_language_array_value( 'language_paging_last_text', __( 'Last page', 'foogallery' ) );
			if ( $paging_last_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'pagination' => array(
							'labels' => array(
								'last' => $paging_last_entry
							)
						)
					)
				) );
			}

			$paging_prev_more_entry = foogallery_get_language_array_value( 'language_paging_prev_more_text', __( 'Show previous {LIMIT} pages', 'foogallery' ) );
			if ( $paging_prev_more_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'pagination' => array(
							'labels' => array(
								'prevMore' => $paging_prev_more_entry
							)
						)
					)
				) );
			}

			$paging_next_more_entry = foogallery_get_language_array_value( 'language_paging_next_more_text', __( 'Show next {LIMIT} pages', 'foogallery' ) );
			if ( $paging_next_more_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'pagination' => array(
							'labels' => array(
								'nextMore' => $paging_next_more_entry
							)
						)
					)
				) );
			}

			$paging_loadmore_entry = foogallery_get_language_array_value( 'language_paging_loadmore_text', __( 'Load More', 'foogallery' ) );
			if ( $paging_loadmore_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'paging' => array(
						'loadMore' => array(
							'button' => $paging_loadmore_entry
						)
					)
				) );
			}

			return $il8n;
		}

		/**
		 * Adds the presets that are available in the PRO version
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_pro_paging_choices( $choices ) {
			$choices['pagination'] = __( 'Numbered', 'foogallery' );
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
				'id'      => 'language_paging_current',
				'title'   => __( 'Paging Current Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Current page', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_page',
				'title'   => __( 'Paging Page Number Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Page {PAGE}', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_first_text',
				'title'   => __( 'Paging First Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'First page', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_prev_text',
				'title'   => __( 'Paging Previous Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Previous page', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_next_text',
				'title'   => __( 'Paging Next Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Next page', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_last_text',
				'title'   => __( 'Paging Last Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Last page', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_prev_more_text',
				'title'   => __( 'Paging Previous More Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Show previous {LIMIT} pages', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_next_more_text',
				'title'   => __( 'Paging Next More Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Show next {LIMIT} pages', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_paging_loadmore_text',
				'title'   => __( 'Paging Load More Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Load More', 'foogallery' ),
				'section' => __( 'Paging', 'foogallery' ),
				'tab'     => 'language'
			);

			return $settings;
		}
	}
}