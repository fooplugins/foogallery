<?php
/**
 * FooGallery Pro Paging Class
 */
if ( ! class_exists( 'FooGallery_Pro_Paging' ) ) {

	class FooGallery_Pro_Paging {

		function __construct() {
			add_filter( 'foogallery_gallery_template_paging_type_choices', array( $this, 'add_pro_paging_choices' ) );
		}

		/**
		 * Adds the presets that are available in the PRO version
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_pro_hover_presets( $choices ) {
			$choices['pagination'] = __( 'Pagination', 'foogallery' );
			$choices['infinite'] = __( 'Infinite Scroll', 'foogallery' );
			$choices['loadMore'] = __( 'Load More', 'foogallery' );
			return $choices;
		}
	}
}