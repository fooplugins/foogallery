<?php

if (!class_exists('FooGallery_Admin_Settings')) {

    class FooGallery_Admin_Settings {

		function __construct() {
			add_filter('foogallery-admin_settings', array($this, 'create_settings'));
		}

        function create_settings() {

			$tabs['general'] = __('General', 'foogallery');

			//region Templates
			$sections['templates'] = array(
				'tab' => 'general',
				'name' => __('Templates', 'foogallery')
			);


			$settings[] = array(
                'id'      => 'test_checkbox',
                'title'   => __('Example Checkbox', 'foogallery'),
                'desc'    => __('An example checkbox that does nothing', 'foogallery'),
                'default' => 'on',
                'type'    => 'checkbox',
				'section' => 'templates',
                'tab'     => 'general'
            );

			$settings[] = array(
                'id'      => 'test_textbox',
                'title'   => __('Example Textbox', 'foogallery'),
                'desc'    => __('An example textbox that does nothing', 'foogallery'),
                'default' => 'on',
                'type'    => 'text',
				'section' => 'templates',
                'tab'     => 'general'
            );

			return array(
				'tabs' => $tabs,
				'sections' => $sections,
				'settings' => $settings
			);
        }
    }
}