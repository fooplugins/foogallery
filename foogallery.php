<?php
/**
 * FooGallery
 *
 * The goal of FooGallery is simple : To provide an easy-to-use and intuitive image gallery management solution.
 * Also, the plugin must utilize as much WordPress core functionality as possible!
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2013 FooPlugins LLC
 *
 * @wordpress-plugin
 * Plugin Name: FooGallery
 * Plugin URI:  https://github.com/fooplugins/foogallery
 * Description: Better Image Galleries for WordPress
 * Version:     1.0.1
 * Author:      bradvin
 * Author URI:  http://fooplugins.com
 * Text Domain: foogallery
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FOOGALLERY_SLUG', 'foogallery' );
define( 'FOOGALLERY_PATH', plugin_dir_path( __FILE__ ));
define( 'FOOGALLERY_FILE', __FILE__ );
define( 'FOOGALLERY_VERSION', '1.0.1' );

require_once( 'includes/class-foogallery.php' );

$GLOBALS['foogallery'] = new FooGallery();