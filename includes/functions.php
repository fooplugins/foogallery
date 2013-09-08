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
        global $foogallery_templates;

        if (!isset($foogallery_templates)) {
            $foogallery = $GLOBALS['foogallery'];

            $foogallery_templates = $foogallery->build_template_list();
        }

        return $foogallery_templates;
    }
}