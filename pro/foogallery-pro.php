<?php

/**
 * FooGallery PRO includes
 */
require_once FOOGALLERY_PATH . 'pro/functions.php';
require_once FOOGALLERY_PATH . 'pro/includes/video/class-foogallery-pro-video.php';
require_once FOOGALLERY_PATH . 'pro/includes/video/class-foogallery-pro-video-legacy.php';
require_once FOOGALLERY_PATH . 'pro/includes/video/class-foogallery-pro-video-migration-helper.php';

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
			new FooPlugins\FooGallery\Pro\FooGallery_Pro_Hover_Presets();
			new FooPlugins\FooGallery\Pro\Extensions\DefaultTemplates\FooGallery_Pro_Default_Templates();
			new FooPlugins\FooGallery\Pro\FooGallery_Pro_Instagram_Filters();

			if ( foogallery_fs()->is_plan_or_trial( 'pro' ) ) {
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Advanced_Gallery_Settings();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Paging();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Bulk_Copy();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Gallery_Shortcode_Override();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Attachment_Taxonomies();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Filtering();
				new FooGallery_Pro_Video();
				new FooGallery_Pro_Video_Legacy();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Datasource_MediaCategories();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Datasource_MediaTags();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Datasource_Folders();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Datasource_Lightroom();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_WPLR_Support();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Datasource_RealMediaLibrary();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Datasource_Post_Query();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Advanced_Captions();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Advanced_Thumbnails();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Bulk_Management();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Exif();
			}
			if ( foogallery_fs()->is_plan_or_trial( 'commerce' ) ) {
				new FooPlugins\FooGallery\Pro\Protection\FooGallery_Pro_Protection();
				new FooPlugins\FooGallery\Pro\Woocommerce\FooGallery_Pro_Woocommerce();
				new FooPlugins\FooGallery\Pro\Woocommerce\FooGallery_Pro_Datasource_Products();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Ribbons();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Buttons();
				new FooPlugins\FooGallery\Pro\Extensions\DefaultTemplates\FooGallery_Product_Gallery_Template();
				new FooPlugins\FooGallery\Pro\Woocommerce\FooGallery_Pro_Woocommerce_Master_Product();
				new FooPlugins\FooGallery\Pro\Woocommerce\FooGallery_Pro_Woocommerce_Downloads();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Whitelabelling();
				new FooPlugins\FooGallery\Pro\Extensions\Whitelabelling\FooGallery_Pro_Whitelabelling_Extension();
				new FooPlugins\FooGallery\Pro\FooGallery_Pro_Gallery_Blueprints();
			}
		}
	}
}
