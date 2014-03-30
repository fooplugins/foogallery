<?php
/**
 * FooGallery global functions
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2013 FooPlugins LLC
 */

if (!function_exists('foogallery_get_templates')) {
    function foogallery_get_templates() {
		$default_templates = array(
			'default' => array(
				'name' => 'Default'
			)
		);

		return apply_filters('foogallery-templates', $default_templates);
    }
}