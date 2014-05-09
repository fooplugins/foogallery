<?php

//common includes
require_once( FOOGALLERY_PATH . 'includes/constants.php' );
require_once( FOOGALLERY_PATH . 'includes/functions.php' );
require_once( FOOGALLERY_PATH . 'includes/class-posttypes.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery.php' );

if( is_admin() ) {
	//only admin
	require_once( FOOGALLERY_PATH . 'includes/admin/class-admin.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-settings.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-metaboxes.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-menu.php' );
	require_once( FOOGALLERY_PATH . 'includes/admin/class-columns.php' );

} else {
	//only front-end
	require_once( FOOGALLERY_PATH . 'includes/public/class-public.php' );
	require_once( FOOGALLERY_PATH . 'includes/public/class-shortcodes.php' );

	//load Gamajo_Template_Loader files
	require_once( FOOGALLERY_PATH . 'includes/public/class-gamajo-template-loader.php' );
	require_once( FOOGALLERY_PATH . 'includes/public/class-foogallery-template-loader.php' );
}