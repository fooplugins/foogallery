<?php

if (!class_exists('FooGallery_Admin_Settings')) {

    class FooGallery_Admin_Settings {

		function __construct() {
			add_filter( 'foogallery-admin_settings', array($this, 'create_settings') );
		}

        function create_settings() {

	        //region General Tab
	        $tabs['general'] = __('General', 'foogallery');

	        $sections['defaults'] = array(
		        'tab' => 'general',
		        'name' => __('Defaults', 'foogallery')
	        );

	        $gallery_templates = foogallery_gallery_templates();
	        $gallery_templates_choices = array();
	        foreach( $gallery_templates as $template ) {
		        $gallery_templates_choices[$template['slug']] = $template['name'];
	        }

	        $settings[] = array(
		        'id'      => 'gallery_template',
		        'title'   => __('Default Gallery Template', 'foogallery'),
		        'desc'    => __('The default gallery template to use for new galleries', 'foogallery'),
		        'default' => foogallery_get_default( 'gallery_template' ) ,
		        'type'    => 'select',
		        'choices' => $gallery_templates_choices,
		        'tab'     => 'general'
	        );

	        //endregion General

			//region Templates Tab
	        $tabs['templates'] = __('Templates', 'foogallery');

			$settings[] = array(
                'id'      => 'available_templates',
                'title'   => '',
                'type'    => 'templates',
                'tab'     => 'templates'
            );
	        //endregion Templates

	        //region Advanced Tab
	        $tabs['advanced'] = __('Advanced', 'foogallery');

	        $example_url = '<br /><code>' . trailingslashit( site_url() ) . foogallery_permalink() . '/my-cool-gallery</code>';

	        $settings[] = array(
		        'id'      => 'gallery_permalinks_enabled',
		        'title'   => __('Enable Friendly URL\'s', 'foogallery'),
		        'desc'    => sprintf( __('If enabled, you will be able to access your galleries from friendly URL\'s, e.g. %s', 'foogallery'), $example_url ),
		        'default' => foogallery_get_default( 'gallery_permalinks_enabled' ),
		        'type'    => 'checkbox',
		        'tab'     => 'advanced'
	        );

	        $settings[] = array(
		        'id'      => 'gallery_permalink',
		        'title'   => __('Gallery Permalink', 'foogallery'),
		        'desc'    => __('The part used in building up friendly URL\'s', 'foogallery'),
		        'default' => foogallery_get_default( 'gallery_permalink' ),
		        'type'    => 'text',
		        'tab'     => 'advanced'
	        );
	        //endregion Advanced

			return array(
				'tabs' => $tabs,
				'sections' => $sections,
				'settings' => $settings
			);
        }
    }
}
