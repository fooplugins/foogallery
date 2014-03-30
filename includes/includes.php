<?php

//common includes
require_once( FOOGALLERY_PATH . 'includes/constants.php' );
require_once( FOOGALLERY_PATH . 'includes/functions.php' );
require_once( FOOGALLERY_PATH . 'includes/class-textdomain.php' );
require_once( FOOGALLERY_PATH . 'includes/class-posttypes-taxonomies.php' );
require_once( FOOGALLERY_PATH . 'includes/class-foogallery.php' );

if( is_admin() ) {
	//only admin
	require_once( FOOGALLERY_PATH . 'includes/admin/class-admin.php' );
} else {
	//only front-end
	require_once( FOOGALLERY_PATH . 'includes/public/class-public.php' );
}