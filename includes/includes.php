<?php

//common includes
require_once( FOOGALLERY_PATH . 'includes/constants.php' );
require_once( FOOGALLERY_PATH . 'includes/functions.php' );
require_once( FOOGALLERY_PATH . 'includes/class-posttypes.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery-attachment.php' );
require_once( FOOGALLERY_PATH . 'includes/class-thumbnails.php' );
require_once( FOOGALLERY_PATH . 'includes/class-extensions-api.php' );
require_once( FOOGALLERY_PATH . 'includes/class-extensions-loader.php' );
require_once( FOOGALLERY_PATH . 'includes/class-attachment-filters.php' );

//check for WPThumb, include.
if ( ! class_exists( 'WP_Thumb' ) ) {
	require_once( FOOGALLERY_PATH . 'includes/WPThumb/wpthumb.php' );
}

//include bundled extensions
require_once( FOOGALLERY_PATH . 'extensions/albums/class-albums-extension.php' );
require_once( FOOGALLERY_PATH . 'extensions/default-templates/class-default-templates-extension.php' );
require_once( FOOGALLERY_PATH . 'extensions/nextgen-importer/class-nextgen-gallery-importer-extension.php' );

if ( is_admin() ) {

	//only admin
	require_once( FOOGALLERY_PATH . 'includes/admin/class-admin.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-extensions.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-settings.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-editor.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-metaboxes.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-fields.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-menu.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-columns.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-boilerplate-zip-generator.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-boilerplate-download-handler.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-attachment-fields.php' );

} else {

	//only front-end
	require_once( FOOGALLERY_PATH . 'includes/public/class-public.php' );
	require_once( FOOGALLERY_PATH . 'includes/public/class-shortcodes.php' );

	//load Template \Loader files
	require_once( FOOGALLERY_PATH . 'includes/public/class-foogallery-template-loader.php' );
}
