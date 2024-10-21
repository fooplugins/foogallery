<?php

/**
 * FooGallery PRO includes
 */
namespace FooPlugins\FooGallery\Pro;

require_once FOOGALLERY_PATH . 'pro/functions.php';

/**
 * FooGallery PRO Main Class
 */
if ( ! class_exists( 'FooGallery_Pro' ) ) {

	define( 'FOOGALLERY_PRO_PATH', plugin_dir_path( __FILE__ ) );
	define( 'FOOGALLERY_PRO_URL', plugin_dir_url( __FILE__ ) );

	/**
	 * Class FooGallery_Pro
	 */
	class FooGallery_Pro {

		/**
		 * FooGallery_Pro constructor.
		 */
		public function __construct() {
			new FooGallery_Pro_Hover_Presets();
			new Extensions\DefaultTemplates\FooGallery_Pro_Default_Templates();
			new FooGallery_Pro_Instagram_Filters();
			new Extensions\Whitelabelling\FooGallery_Pro_Whitelabelling_Extension();

			if ( foogallery_fs()->is_plan_or_trial( 'pro' ) ) {
				new FooGallery_Pro_Advanced_Gallery_Settings();
				new FooGallery_Pro_Paging();
				new FooGallery_Pro_Bulk_Copy();
				new FooGallery_Pro_Gallery_Shortcode_Override();
				new FooGallery_Pro_Attachment_Taxonomies();
				new FooGallery_Pro_Filtering();
				new Video\FooGallery_Pro_Video();
				new Video\FooGallery_Pro_Video_Legacy();
				new FooGallery_Pro_Datasource_MediaCategories();
				new FooGallery_Pro_Datasource_MediaTags();
				new FooGallery_Pro_Datasource_Folders();
				new FooGallery_Pro_Datasource_Lightroom();
				new FooGallery_Pro_WPLR_Support();
				new FooGallery_Pro_Datasource_RealMediaLibrary();
				new FooGallery_Pro_Datasource_Post_Query();
				new FooGallery_Pro_Advanced_Captions();
				new FooGallery_Pro_Advanced_Thumbnails();
				new FooGallery_Pro_Bulk_Management();
				new FooGallery_Pro_Exif();
			}
			if ( foogallery_fs()->is_plan_or_trial( 'commerce' ) ) {
				new Protection\FooGallery_Pro_Protection();
				new WooCommerce\FooGallery_Pro_Woocommerce();
				new FooGallery_Pro_Ribbons();
				new FooGallery_Pro_Buttons();
				new Extensions\DefaultTemplates\Product\FooGallery_Product_Gallery_Template();
				new WooCommerce\FooGallery_Pro_Woocommerce_Master_Product();
				new WooCommerce\FooGallery_Pro_Woocommerce_Downloads();
				new WooCommerce\FooGallery_Pro_Datasource_Products();
				new FooGallery_Pro_Whitelabelling();
				new FooGallery_Pro_Gallery_Blueprints();
			}
		}
	}
}