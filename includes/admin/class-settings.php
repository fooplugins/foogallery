<?php

if ( ! class_exists( 'FooGallery_Admin_Settings' ) ) {

	/**
	 * Class FooGallery_Admin_Settings
	 */
	class FooGallery_Admin_Settings {

		function __construct() {
			add_filter( 'foogallery_admin_settings', array( $this, 'create_settings' ), 10, 2 );
		}

		function create_settings() {

			//region General Tab
			$tabs['general'] = __( 'General', 'foogallery' );

	        $gallery_templates = foogallery_gallery_templates();
			$gallery_templates_choices = array();
			foreach ( $gallery_templates as $template ) {
				$gallery_templates_choices[ $template['slug'] ] = $template['name'];
			}

			$settings[] = array(
				'id'      => 'gallery_template',
				'title'   => __( 'Default Gallery Template', 'foogallery' ),
				'desc'    => __( 'The default gallery template to use for new galleries', 'foogallery' ),
				'default' => foogallery_get_default( 'gallery_template' ) ,
				'type'    => 'select',
				'choices' => $gallery_templates_choices,
				'tab'     => 'general',
			);

			$settings[] = array(
				'id'      => 'hide_gallery_template_help',
				'title'   => __( 'Hide Gallery Template Help', 'foogallery' ),
				'desc'    => __( 'Some gallery templates show helpful tips, which are useful for new users. You can choose to hide these tips.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'general',
			);

			$galleries = foogallery_get_all_galleries();
			$gallery_choices = array();
			foreach ( $galleries as $gallery ) {
				$gallery_choices[ $gallery->ID ] = $gallery->name;
			}

			$settings[] = array(
				'id'      => 'default_gallery_settings',
				'title'   => __( 'Default Gallery Settings', 'foogallery' ),
				'desc'    => __( 'When creating a new gallery, it can use the settings from an existing gallery as the default settings. This will save you time when creating many galleries that all have the same look and feel.', 'foogallery' ),
				'type'    => 'select',
				'choices' => $gallery_choices,
				'tab'     => 'general',
			);
			//endregion General

	        //region Extensions Tab
	        $tabs['extensions'] = __( 'Extensions', 'foogallery' );

			$extensions_url = '<br />' . __('The default URL is ', 'foogallery') . '<code>' . FOOGALLERY_EXTENSIONS_ENDPOINT . '</code>';

	        $settings[] = array(
		        'id'      => 'use_future_endpoint',
		        'title'   => __( 'Use Beta Endpoint', 'foogallery' ),
		        'desc'    => __( 'The list of available extensions are pulled from an external URL. You can also pull from a "beta" endpoint which will sometimes contain beta extensions that are not publicly available.', 'foogallery' ),
		        'type'    => 'checkbox',
		        'tab'     => 'extensions',
	        );

	        //endregion Advanced

//	        //region Advanced Tab
//	        $tabs['advanced'] = __( 'Advanced', 'foogallery' );
//
//	        $example_url = '<code>' . trailingslashit( site_url() ) . foogallery_permalink() . '/my-cool-gallery</code>';
//
//	        $settings[] = array(
//		        'id'      => 'gallery_permalinks_enabled',
//		        'title'   => __( 'Enable Friendly URL\'s', 'foogallery' ),
//		        'desc'    => sprintf( __( 'If enabled, you will be able to access your galleries from a friendly URL e.g. %s', 'foogallery' ), $example_url ),
//		        'default' => foogallery_get_default( 'gallery_permalinks_enabled' ),
//		        'type'    => 'checkbox',
//		        'tab'     => 'advanced',
//	        );
//
//	        $settings[] = array(
//		        'id'      => 'gallery_permalink',
//		        'title'   => __( 'Gallery Permalink', 'foogallery' ),
//		        'desc'    => __( 'If friendly URL\'s are enabled, this is used in building up a friendly URL', 'foogallery' ),
//		        'default' => foogallery_get_default( 'gallery_permalink' ),
//		        'type'    => 'text',
//		        'tab'     => 'advanced',
//	        );
//	        //endregion Advanced

			return apply_filters( 'foogallery_admin_settings_override', array(
				'tabs'     => $tabs,
				'sections' => array(),
				'settings' => $settings,
			) );
		}
	}
}
