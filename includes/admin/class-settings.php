<?php

if ( ! class_exists( 'FooGallery_Admin_Settings' ) ) {

	/**
	 * Class FooGallery_Admin_Settings
	 */
	class FooGallery_Admin_Settings {

		function __construct() {
			add_filter( 'foogallery_admin_settings', array( $this, 'create_settings' ), 10, 2 );
			add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_custom_setting_types' ) );

			// Ajax calls for clearing CSS optimization cache
			add_action( 'wp_ajax_foogallery_clear_css_optimizations', array( $this, 'ajax_clear_css_optimizations' ) );
		}

		function create_settings() {

			//region General Tab
			$tabs['general'] = __( 'General', 'foogallery' );

			$settings[] = array(
				'id'      => 'clear_css_optimizations',
				'title'   => __( 'Clear CSS Cache', 'foogallery' ),
				'desc'    => sprintf( __( '%s optimizes the way it loads gallery stylesheets to improve page performance. This can lead to the incorrect CSS being loaded in some cases. Use this button to clear all the CSS optimizations that have been cached across all galleries.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'clear_optimization_button',
				'tab'     => 'general',
				'section' => __( 'Cache', 'foogallery' )
			);

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
				'section' => __( 'Gallery Defaults', 'foogallery' )
			);

			$settings[] = array(
				'id'      => 'gallery_sorting',
				'title'   => __( 'Default Gallery Sorting', 'foogallery' ),
				'desc'    => __( 'The default attachment sorting to use for new galleries', 'foogallery' ),
				'default' => '',
				'type'    => 'select',
				'choices' => foogallery_sorting_options(),
				'tab'     => 'general',
				'section' => __( 'Gallery Defaults', 'foogallery' )
			);

			$galleries = foogallery_get_all_galleries();
			$gallery_choices = array();
			$gallery_choices[] = __( 'No default', 'foogallery' );
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
				'section' => __( 'Gallery Defaults', 'foogallery' )
			);

			$settings[] = array(
				'id'      => 'caption_title_source',
				'title'   => __( 'Caption Title Source', 'foogallery' ),
				'desc'    => __( 'By default, image caption titles are pulled from the attachment "Caption" field. Alternatively, you can also choose to pull from the attachment "Title" field.', 'foogallery' ),
				'type'    => 'select',
				'choices' => array(
					'caption' => __('Attachment Caption Field', 'foogallery'),
					'title' => __('Attachment Title Field', 'foogallery')
				),
				'default' => 'caption',
				'tab'     => 'general',
				'section' => __( 'Captions', 'foogallery' ),
				'spacer'  => '<span class="spacer"></span>'
			);

			$settings[] = array(
					'id'      => 'caption_desc_source',
					'title'   => __( 'Caption Description Source', 'foogallery' ),
					'desc'    => __( 'By default, image caption descriptions are pulled from the attachment "Description" field. Alternatively, you can choose to use other fields.', 'foogallery' ),
					'type'    => 'select',
					'choices' => array(
							'desc' => __('Attachment Description Field', 'foogallery'),
							'title' => __('Attachment Title Field', 'foogallery'),
							'caption' => __('Attachment Caption Field', 'foogallery'),
							'alt' => __('Attachment Alt Field', 'foogallery')
					),
					'default' => 'desc',
					'tab'     => 'general',
					'section' => __( 'Captions', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>'
			);

			$settings[] = array(
				'id'      => 'hide_gallery_template_help',
				'title'   => __( 'Hide Gallery Template Help', 'foogallery' ),
				'desc'    => __( 'Some gallery templates show helpful tips, which are useful for new users. You can choose to hide these tips.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'general',
				'section' => __( 'Admin', 'foogallery' )
			);

			$settings[] = array(
				'id'      => 'hide_editor_button',
				'title'   => __( 'Hide WYSIWYG Editor Button', 'foogallery' ),
				'desc'    => sprintf( __( 'If enabled, this will hide the "Add %s" button in the WYSIWYG editor.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'checkbox',
				'tab'     => 'general',
				'section' => __( 'Admin', 'foogallery' )
			);

			//endregion General

	        //region Extensions Tab
	        $tabs['extensions'] = __( 'Extensions', 'foogallery' );

	        $settings[] = array(
		        'id'      => 'use_future_endpoint',
		        'title'   => __( 'Use Beta Endpoint', 'foogallery' ),
		        'desc'    => __( 'The list of available extensions are pulled from an external URL. You can also pull from a "beta" endpoint which will sometimes contain beta extensions that are not publicly available.', 'foogallery' ),
		        'type'    => 'checkbox',
		        'tab'     => 'extensions',
	        );
			//endregion Extensions Tab

			//region Images Tab
			$tabs['thumb'] = __( 'Images', 'foogallery' );

			$settings[] = array(
				'id'      => 'thumb_jpeg_quality',
				'title'   => __( 'Thumbnail JPEG Quality', 'foogallery' ),
				'desc'    => __( 'The image quality to be used when resizing JPEG images.', 'foogallery' ),
				'type'    => 'text',
				'default' => '80',
				'tab'     => 'thumb'
			);

			$settings[] = array(
				'id'      => 'thumb_resize_animations',
				'title'   => __( 'Resize Animated GIFs', 'foogallery' ),
				'desc'    => __( 'Should animated gifs be resized or not. If enabled, only the first frame is used in the resize.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'thumb'
			);

			//endregion Thumbnail Tab

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

			//region Language Tab
			$tabs['language'] = __( 'Language', 'foogallery' );

			$settings[] = array(
				'id'      => 'language_images_count_none_text',
				'title'   => __( 'Image Count None Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'No images', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings[] = array(
				'id'      => 'language_images_count_single_text',
				'title'   => __( 'Image Count Single Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( '1 image', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings[] = array(
				'id'      => 'language_images_count_plural_text',
				'title'   => __( 'Image Count Many Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( '%s images', 'foogallery' ),
				'tab'     => 'language'
			);
			//endregion Language Tab

			return apply_filters( 'foogallery_admin_settings_override', array(
				'tabs'     => $tabs,
				'sections' => array(),
				'settings' => $settings,
			) );
		}

		/**
		 * Render any custom setting types to the settings page
		 */
		function render_custom_setting_types( $args ) {
			if ( 'clear_optimization_button' === $args['type'] ) { ?>
				<input type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_clear_css_optimizations' ) ); ?>" class="button-primary foogallery_clear_css_optimizations" value="<?php _e( 'Clear CSS Optimization Cache', 'foogallery' ); ?>">
				<span id="foogallery_clear_css_cache_spinner" style="position: absolute" class="spinner"></span>
			<?php }
		}

		/**
		 * AJAX endpoint for clearing all CSS optimizations
		 */
		function ajax_clear_css_optimizations() {
			if ( check_admin_referer( 'foogallery_clear_css_optimizations' ) ) {
				foogallery_clear_all_css_load_optimizations();

				_e('The CSS optimization cache was successfully cleared!', 'foogallery' );
				die();
			}
		}
	}
}
