<?php

if (!class_exists('FooGallery_Settings')) {

    class FooGallery_Settings {

        /**
         *
         * @param $foogallery FooGallery
         * @param $settings Foo_Plugin_Settings_v1_0
         */
        static function create_settings($foogallery, $settings) {

            $settings->add_tab('general', 'General');

            $settings->add_setting(array(
                'id'      => 'test_checkbox',
                'title'   => __('Example Checkbox', $foogallery->get_slug()),
                'desc'    => __('An example checkbox that does nothing', $foogallery->get_slug()),
                'default' => 'on',
                'type'    => 'checkbox',
                'tab'     => 'general'
            ));

            $settings->add_setting(array(
                'id'      => 'test_textbox',
                'title'   => __('Example Textbox', $foogallery->get_slug()),
                'desc'    => __('An example textbox that does nothing', $foogallery->get_slug()),
                'default' => 'on',
                'type'    => 'text',
                'tab'     => 'general'
            ));

        }
    }
}