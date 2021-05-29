<?php

/**
 * FooGallery PRO includes
 */
require_once FOOGALLERY_PATH . 'pro/functions.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-presets.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-paging.php';
require_once FOOGALLERY_PATH . 'pro/extensions/default-templates/class-foogallery-pro-default-templates.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-bulk-copy.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-gallery-override.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-filtering.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-attachment-type.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-attachment-taxonomies.php';
require_once FOOGALLERY_PATH . 'pro/includes/video/class-foogallery-pro-video.php';
require_once FOOGALLERY_PATH . 'pro/includes/video/class-foogallery-pro-video-legacy.php';
require_once FOOGALLERY_PATH . 'pro/includes/video/class-foogallery-pro-video-migration-helper.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-advanced-gallery-settings.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-instagram-filters.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-datasource-taxonomy-base.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-datasource-mediatags.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-datasource-mediacategories.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-datasource-folders.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-datasource-lightroom.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-wplr-support.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-instagram-helper.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-datasource-realmedialibrary.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-datasource-post-query.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-lightbox.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-advanced-captions.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-advanced-thumbnails.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-bulk-management.php';
require_once FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-exif.php';

require_once FOOGALLERY_PATH . 'pro/includes/protection/class-foogallery-pro-protection.php';
require_once FOOGALLERY_PATH . 'pro/includes/protection/class-foogallery-watermark.php';
require_once FOOGALLERY_PATH . 'pro/includes/protection/class-foogallery-image-editor-helper-base.php';
require_once FOOGALLERY_PATH . 'pro/includes/protection/class-foogallery-image-editor-helper-gd.php';
require_once FOOGALLERY_PATH . 'pro/includes/protection/class-foogallery-image-editor-helper-imagick.php';

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
			new FooGallery_Pro_Lightbox();
			new FooGallery_Pro_Default_Templates();
			new FooGallery_Pro_Instagram_Filters();

			if ( foogallery_fs()->is_plan_or_trial( 'pro' ) ) {
				new FooGallery_Pro_Advanced_Gallery_Settings();
				new FooGallery_Pro_Paging();
				new FooGallery_Pro_Bulk_Copy();
				new FooGallery_Pro_Gallery_Shortcode_Override();
				new FooGallery_Pro_Attachment_Type();
				new FooGallery_Pro_Attachment_Taxonomies();
				new FooGallery_Pro_Filtering();
				new FooGallery_Pro_Video();
				new FooGallery_Pro_Video_Legacy();
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
				new FooGallery_Pro_Protection();
			}
		}
	}
}