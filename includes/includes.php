<?php

//common includes
require_once( FOOGALLERY_PATH . 'includes/constants.php' );
require_once( FOOGALLERY_PATH . 'includes/functions.php' );
require_once( FOOGALLERY_PATH . 'includes/render-functions.php' );
require_once( FOOGALLERY_PATH . 'includes/class-posttypes.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-attachment.php' );
require_once( FOOGALLERY_PATH . 'includes/class-thumbnails.php' );
require_once( FOOGALLERY_PATH . 'includes/extensions/class-extension.php' );
require_once( FOOGALLERY_PATH . 'includes/extensions/class-extensions-api.php' );
require_once( FOOGALLERY_PATH . 'includes/extensions/class-extensions-loader.php' );
require_once( FOOGALLERY_PATH . 'includes/class-attachment-filters.php' );
require_once( FOOGALLERY_PATH . 'includes/class-retina.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-upgrade.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-widget.php' );

//check for WPThumb, include.
if ( ! class_exists( 'WP_Thumb' ) ) {
	require_once( FOOGALLERY_PATH . 'includes/WPThumb/wpthumb.php' );
	//do not let WPThumb override build-in WordPress functions like the_post_thumbnail
	remove_filter( 'image_downsize', 'wpthumb_post_image', 99 );
}
require_once( FOOGALLERY_PATH . 'includes/class-wpthumb-enhancements.php' );

//include bundled extensions
require_once( FOOGALLERY_PATH . 'extensions/albums/class-albums-extension.php' );
require_once( FOOGALLERY_PATH . 'extensions/default-templates/class-default-templates-extension.php' ); //Legacy!
require_once( FOOGALLERY_PATH . 'extensions/default-templates/class-default-templates.php' );
require_once( FOOGALLERY_PATH . 'extensions/nextgen-importer/class-nextgen-gallery-importer-extension.php' );
require_once( FOOGALLERY_PATH . 'extensions/media-categories/class-media-categories-extension.php' );

//load Template Loader files
require_once( FOOGALLERY_PATH . 'includes/public/class-foogallery-template-loader.php' );

//Polylang Compatibility
require_once( FOOGALLERY_PATH . 'includes/class-polylang-compatibility.php' );

require_once( FOOGALLERY_PATH . 'includes/class-version-check.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-animated-gif-support.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-cache.php' );
require_once( FOOGALLERY_PATH . 'includes/class-thumbnail-dimensions.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foobox-support.php' );
require_once( FOOGALLERY_PATH . 'includes/class-responsive-lightbox-dfactory-support.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-common-fields.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-lazyload.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-paging.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-attachment-custom-class.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-extensions-compatibility.php' );

//Datasource includes
require_once( FOOGALLERY_PATH . 'includes/interface-foogallery-datasource.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-datasource-media_library.php' );

if ( is_admin() ) {

	//only admin
	require_once( FOOGALLERY_PATH . 'includes/admin/class-admin.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-extensions.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-settings.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-settings-image-optimization.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-editor.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-metaboxes.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-fields.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-settings.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-settings-helper.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-menu.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-columns.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-boilerplate-zip-generator.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-boilerplate-download-handler.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-attachment-fields.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-admin-css-load-optimizer.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-admin-notices.php' );
    require_once( FOOGALLERY_PATH . 'includes/admin/class-autoptimize-support.php' );

} else {

	//only front-end
	require_once( FOOGALLERY_PATH . 'includes/public/class-public.php' );
	require_once( FOOGALLERY_PATH . 'includes/public/class-shortcodes.php' );
	require_once( FOOGALLERY_PATH . 'includes/public/class-css-load-optimizer.php' );
	require_once( FOOGALLERY_PATH . 'includes/public/class-admin-bar.php' );
	require_once( FOOGALLERY_PATH . 'includes/public/class-yoast-seo-sitemaps.php' );
}
