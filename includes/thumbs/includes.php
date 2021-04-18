<?php

//include the base engine class
require_once( FOOGALLERY_PATH . 'includes/thumbs/class-foogallery-thumb-engine.php' );

//include the manager class
require_once( FOOGALLERY_PATH . 'includes/thumbs/class-foogallery-thumb-manager.php' );

//include files for default engine
require_once( FOOGALLERY_PATH . 'includes/thumbs/default/class-foogallery-thumb-engine-default.php' );
require_once( FOOGALLERY_PATH . 'includes/thumbs/default/class-foogallery-thumb-generator.php' );
require_once( FOOGALLERY_PATH . 'includes/thumbs/default/class-foogallery-thumb-generator-background-fill.php' );

//include files for dummy engine
require_once( FOOGALLERY_PATH . 'includes/thumbs/dummy/class-foogallery-thumb-engine-dummy.php' );

//include files for shortpixel engine
require_once( FOOGALLERY_PATH . 'includes/thumbs/shortpixel/class-foogallery-thumb-engine-shortpixel.php' );
