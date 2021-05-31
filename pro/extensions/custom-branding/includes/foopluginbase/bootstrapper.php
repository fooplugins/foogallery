<?php
/*
 * Foo Plugin Base Bootsrapper
 * Includes all the files needed for the Foo Plugin Base framework
 *
 * Version: 2.1
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

//include the framework
require_once 'classes/class-foo-plugin-base.php';

//include other classes we need
require_once 'classes/class-foo-plugin-options.php';
require_once 'classes/class-foo-plugin-textdomain.php';
require_once 'classes/class-foo-friendly-dates.php';
require_once 'classes/class-foo-plugin-file-locator.php';

//include classes we need in the admin
if ( is_admin() ) {
	require_once 'classes/class-foo-plugin-settings.php';
	require_once 'classes/class-foo-plugin-metabox-sanity.php';
}

//include all functions we need
require_once 'functions/arrays.php';
require_once 'functions/general.php';
require_once 'functions/screen.php';
require_once 'functions/strings.php';
require_once 'functions/dates.php';