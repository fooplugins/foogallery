<?php

//include the base engine class
require_once( FOOGALLERY_PATH . 'includes/thumbs/class-foogallery-thumb-engine.php' );

//include common functions
require_once( FOOGALLERY_PATH . 'includes/thumbs/functions.php' );

//include the manager class
require_once( FOOGALLERY_PATH . 'includes/thumbs/class-foogallery-thumb-manager.php' );

//include files for default engine
require_once( FOOGALLERY_PATH . 'includes/thumbs/default/class-foogallery-thumb-engine-default.php' );
require_once( FOOGALLERY_PATH . 'includes/thumbs/default/class-foogallery-thumb-generator.php' );
require_once( FOOGALLERY_PATH . 'includes/thumbs/default/class-foogallery-thumb-generator-background-fill.php' );
require_once( FOOGALLERY_PATH . 'includes/thumbs/default/functions.php' );

//include files for dummy engine
require_once( FOOGALLERY_PATH . 'includes/thumbs/dummy/class-foogallery-thumb-engine-dummy.php' );
