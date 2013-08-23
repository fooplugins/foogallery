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
 * Version:     1.0.0
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

require_once( 'class-foogallery.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'FooGallery', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'FooGallery', 'deactivate' ) );

FooGallery::set_instance( new FooGallery(__FILE__) );