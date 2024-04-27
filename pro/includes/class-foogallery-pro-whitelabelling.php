<?php
namespace FooPlugins\FooGallery\Pro;

/**
 * FooGallery WhiteLabelling Class that registers the extension.
 */

if ( ! class_exists('FooGallery_Pro_Whitelabelling') ) {

	class FooGallery_Pro_Whitelabelling {

		/**
		 * FooGallery_Pro_Whitelabelling constructor.
		 */
		function __construct() {
			add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

		/**
		 * Register the White Labeling extension
		 *
		 * @param $extensions_list
		 *
		 * @return array
		 */
		function register_extension( $extensions_list ) {
            $pro_features = foogallery_pro_features();

			$extensions_list[] = array(
				'slug' => 'foogallery-whitelabelling',
				'class' => 'FooGallery_Pro_Whitelabelling_Extension',
				'categories' => array( 'Premium' ),
				'title' => __( 'White Labeling', 'foogallery' ),
				'description' => $pro_features['whitelabeling']['desc'],
				'external_link_text' => __( 'Read documentation', 'foogallery' ),
                'external_link_url' => $pro_features['whitelabeling']['link'],
				'dashicon'          => 'dashicons-tag',
				'tags' => array( 'Premium' ),
				'source' => 'bundled'
			);

			return $extensions_list;
		}
	}
}