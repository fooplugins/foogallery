<?php
/*
 * Foo Plugin Base Bootsrapper
 * Includes all the files needed for the Foo Plugin Base framework
 *
 * Version: 2.0
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

//include the framework
require_once 'classes/class-foo-plugin-base.php';

//include other classes we need
require_once 'classes/class-foo-plugin-options.php';
require_once 'classes/class-foo-plugin-settings.php';
require_once 'classes/class-foo-plugin-textdomain.php';

//include all functions we need
require_once 'functions/arrays.php';
require_once 'functions/general.php';
require_once 'functions/screen.php';
require_once 'functions/strings.php';