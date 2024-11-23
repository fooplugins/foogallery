<?php

if ( ! class_exists( 'FooGallery_Admin_Settings' ) ) {

	/**
	 * Class FooGallery_Admin_Settings
	 */
	class FooGallery_Admin_Settings {

		function __construct() {
			add_filter( 'foogallery_admin_settings', array( $this, 'create_settings' ), 10, 2 );
			add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_custom_setting_types' ) );
			add_action( 'foogallery_admin_settings_after_render_setting', array( $this, 'after_render_setting' ) );
			add_action( 'update_option_foogallery', array( $this, 'generate_assets' ), 10, 3 );
			add_filter( 'pre_update_option_foogallery', array( $this, 'sanitize_settings' ), 10, 3 );

			// Ajax calls.
			add_action( 'wp_ajax_foogallery_clear_css_optimizations', array( $this, 'ajax_clear_css_optimizations' ) );
			add_action( 'wp_ajax_foogallery_thumb_generation_test', array( $this, 'ajax_thumb_generation_test' ) );
			add_action( 'wp_ajax_foogallery_apply_retina_defaults', array( $this, 'ajax_apply_retina_defaults' ) );
			add_action( 'wp_ajax_foogallery_uninstall', array( $this, 'ajax_uninstall' ) );
		}

		/**
		 * Sanitize the foogallery settings.
		 *
		 * @param mixed  $value The value of the settings.
		 * @param mixed  $old_value The old value.
		 * @param string $option The setting name. Should be 'foogallery'.
		 *
		 * @return mixed
		 */
		public function sanitize_settings( $value, $old_value, $option ) {
			if ( is_array( $value ) && array_key_exists( 'custom_js', $value ) ) {
				$value['custom_js'] = foogallery_sanitize_code( $value['custom_js'] );
			}
			if ( is_array( $value ) && array_key_exists( 'custom_css', $value ) ) {
				$value['custom_css'] = foogallery_sanitize_code( $value['custom_css'] );
			}
			return $value;
		}

		/**
		 * Create the settings for FooGallery
		 * @return array
		 */
		function create_settings() {

			//region General Tab
			$tabs['general'] = __( 'General', 'foogallery' );

			$settings[] = array(
				'id'      => 'clear_css_optimizations',
				'title'   => __( 'Clear CSS Cache', 'foogallery' ),
				'desc'    => sprintf( __( '%s optimizes the way it loads gallery stylesheets to improve page performance. This can lead to the incorrect CSS being loaded in some cases. Use this button to clear all the CSS optimizations that have been cached across all galleries.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'clear_optimization_button',
				'tab'     => 'general',
				'section' => __( 'Performance', 'foogallery' )
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

			$gallery_posts = get_posts( array(
				'post_type'     => FOOGALLERY_CPT_GALLERY,
				'post_status'	=> array( 'publish', 'draft' ),
				'cache_results' => false,
				'nopaging'      => true,
			) );

			$galleries = array();

			foreach ( $gallery_posts as $post ) {
				$galleries[] = array(
					'ID' => $post->ID,
					'name' => $post->post_title
				);
			}

			$gallery_choices = array();
			$gallery_choices[] = __( 'No default', 'foogallery' );
			foreach ( $galleries as $gallery ) {
				$gallery_choices[ $gallery['ID'] ] = esc_html( $gallery['name'] );
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
				'desc'    => __( 'By default, image caption titles are pulled from the attachment "Caption" field. Alternatively, you can choose to use other fields.', 'foogallery' ),
				'type'    => 'select',
				'choices' => array(
					'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
					'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
					'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
					'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' )
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
					'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
					'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
					'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
					'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' )
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

			$roles        = get_editable_roles();
			$role_choices = array(
				'' => __( 'Default', 'foogallery' )
			);

			foreach ( $roles as $role_slug => $role_data ) {
				$role_choices[ $role_slug ] = $role_data['name'];
			}

			$settings[] = array(
				'id'      => 'gallery_creator_role',
				'title'   => __( 'Gallery Creator Role', 'foogallery' ),
				'desc'    => __( 'Select the user role allowed to manage galleries. All roles with higher privileges will also be able to manage galleries.', 'foogallery' ),
				'type'    => 'select',
				'choices' => $role_choices,
				'tab'     => 'general',
				'section' => __( 'Admin', 'foogallery' ),
			);

			$settings[] = array(
				'id'      => 'hide_editor_button',
				'title'   => __( 'Hide Classic Editor Button', 'foogallery' ),
				'desc'    => sprintf( __( 'If enabled, this will hide the "Add %s" button in the Classic editor.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'checkbox',
				'tab'     => 'general',
				'section' => __( 'Admin', 'foogallery' )
			);

			$settings[] = array(
				'id'      => 'advanced_attachment_modal',
				'title'   => __( 'Enable Advanced Attachment Modal', 'foogallery' ),
				'desc'    => __( 'If enabled, this will use the advanced attachment modal which allows for faster and easier editing of attachment details, when creating your galleries.', 'foogallery' ),
				'type'    => 'checkbox',
				'default' => 'on',
				'tab'     => 'general',
				'section' => __( 'Admin', 'foogallery' )
			);

			// endregion General

			// region Album Tab.
			$tabs['albums'] = __( 'Albums', 'foogallery' );
			$roles         = get_editable_roles();
			$role_choices = array(
				'inherit' => __( 'Inherit from gallery creator role', 'foogallery' ),
			);

			foreach ( $roles as $role_slug => $role_data ) {
				$role_choices[ $role_slug ] = $role_data['name'];
			}

			$settings[] = array(
				'id'      => 'album_creator_role',
				'title'   => __( 'Album Creator Role', 'foogallery' ),
				'desc'    => __( 'Set the default role for album creators.', 'foogallery' ),
				'type'    => 'select',
				'choices' => $role_choices,
				'default' => 'inherit',
				'tab'     => 'albums',
			);
			// end of album region.

			//region Images Tab
			$tabs['thumb'] = __( 'Images', 'foogallery' );

			$engines = array();
			foreach ( foogallery_thumb_available_engines() as $engine_key => $engine ) {
				$engines[$engine_key] = '<strong>' . $engine['label'] . '</strong> - ' . $engine['description'];
			}

			$settings[] = array(
				'id'      => 'thumb_engine',
				'title'   => __( 'Thumbnail Engine', 'foogallery' ),
				'desc'    => __( 'The thumbnail generation engine used when creating different sized thumbnails for your galleries.', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'default',
				'choices' => $engines,
				'tab'     => 'thumb'
			);

			if ( foogallery_thumb_active_engine()->uses_image_editors() ) {
				$image_editor      = str_replace( 'FooGallery_Thumb_Image_Editor_', '', _wp_image_editor_choose( array( 'methods' => array( 'get_image' ) ) ) );
				$gd_supported      = extension_loaded( 'gd' ) ? __( 'yes', 'foogallery' ) : __( 'no', 'foogallery' );
				$imagick_supported = extension_loaded( 'imagick' ) ? __( 'yes', 'foogallery' ) : __( 'no', 'foogallery' );

				$settings[] = array(
					'id'    => 'thumb_image_library',
					'title' => __( 'Thumbnail Image Library', 'foogallery' ),
					'desc'  => sprintf( __( 'Currently active : %s.<br />Imagick supported : %s.<br />GD supported : %s.', 'foogallery' ), '<strong>' . $image_editor . '</strong>', $imagick_supported, $gd_supported ),
					'type'  => 'html',
					'tab'   => 'thumb'
				);
			}

			if ( foogallery_thumb_active_engine()->has_local_cache() ) {
				$settings[] = array(
					'id'      => 'thumb_jpeg_quality',
					'title'   => __( 'Thumbnail JPEG Quality', 'foogallery' ),
					'desc'    => __( 'The image quality to be used when resizing JPEG images.', 'foogallery' ),
					'type'    => 'text',
					'default' => '90',
					'tab'     => 'thumb'
				);
			}

			$image_optimization_html = sprintf( __('We recommend %s! An easy-to-use, lightweight WordPress plugin that optimizes images & PDFs.', 'foogallery'),
				'<a href="https://shortpixel.com/homepage/affiliate/foowww" target="_blank">' . __('ShortPixel Image Optimizer' , 'foogallery') . '</a>' );

			$settings[] = array(
				'id'      => 'image_optimization',
				'title'   => __( 'Image Optimization', 'foogallery' ),
				'type'    => 'html',
				'desc'    => $image_optimization_html,
				'tab'     => 'thumb'
			);

			$settings[] = array(
				'id'      => 'default_retina_support',
				'title'   => __( 'Default Retina Support', 'foogallery' ),
				'desc'    => __( 'Default retina support for all new galleries that are created. This can also be overridden for each gallery.', 'foogallery' ),
				'type'    => 'checkboxlist',
				'choices' => foogallery_retina_options(),
				'tab'     => 'thumb'
			);

			$settings[] = array(
				'id'      => 'use_original_thumbs',
				'title'   => __( 'Use Original Thumbnails', 'foogallery' ),
				'desc'    => __( 'Allow for the original thumbnails to be used when possible. This can be useful if your thumbs are animated gifs.<br/>PLEASE NOTE : this will only work if your gallery thumbnail sizes are identical to your thumbnail sizes under Settings -> Media.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'thumb'
			);

			$settings[] = array(
				'id'      => 'animated_gif_use_original_image',
				'title'   => __( 'Show Animated Thumbnails', 'foogallery' ),
				'desc'    => __( 'If animated GIFs are used, then show the original GIF as the thumbnail.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'thumb'
			);

			if ( foogallery_thumb_active_engine()->has_local_cache() ) {
				$settings[] = array(
					'id'    => 'thumb_resize_upscale_small',
					'title' => __( 'Upscale Small Images', 'foogallery' ),
					'desc'  => __( 'If the original image is smaller than the thumbnail size, then upscale the image thumbnail to match the size.', 'foogallery' ) . '<br/>' . __( 'PLEASE NOTE : this is only supported if your server supports the GD image library and it is currently active.', 'foogallery' ),
					'type'  => 'checkbox',
					'tab'   => 'thumb'
				);

				$settings[] = array(
					'id'      => 'thumb_resize_upscale_small_color',
					'title'   => __( 'Upscale Background Color', 'foogallery' ),
					'desc'    => __( 'The background color to use for upscaled images. You can also use "transparent" or "auto".', 'foogallery' ),
					'type'    => 'text',
					'default' => 'rgb(0,0,0)',
					'tab'     => 'thumb'
				);
			}

			if ( foogallery_thumb_active_engine()->requires_thumbnail_generation_tests() ) {
				$thumb_test_html = '<a href="' . admin_url( add_query_arg( array( 'page' => 'foogallery_thumb_test' ), foogallery_admin_menu_parent_slug() ) ) . '">' . __( 'View Thumb Test Page', 'foogallery' ) . '</a>';

				$settings[] = array(
					'id'    => 'thumb_generation_test',
					'title' => __( 'Thumbnail Generation Test', 'foogallery' ),
					'desc'  => sprintf( __( 'Test to see if %s can generate the thumbnails it needs. %s', 'foogallery' ), foogallery_plugin_name(), $thumb_test_html ),
					'type'  => 'thumb_generation_test',
					'tab'   => 'thumb'
				);
			}

			if ( foogallery_thumb_active_engine()->uses_image_editors() ) {
				$settings[] = array(
					'id'    => 'force_gd_library',
					'title' => __( 'Force GD Library', 'foogallery' ),
					'desc'  => __( 'By default, WordPress will use Imagick as the default Image Editor. This will force GD to be used as the default.', 'foogallery' ),
					'type'  => 'checkbox',
					'tab'   => 'thumb'
				);
			}

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
				'id'      => 'language_imageviewer_prev_text',
				'title'   => __( 'Image Viewer "Prev" Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Prev', 'foogallery' ),
				'section' => __( 'Image Viewer Template', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings[] = array(
				'id'      => 'language_imageviewer_next_text',
				'title'   => __( 'Image Viewer "Next" Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Next', 'foogallery' ),
				'section' => __( 'Image Viewer Template', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings[] = array(
				'id'      => 'language_imageviewer_of_text',
				'title'   => __( 'Image Viewer "Of" Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'of', 'foogallery' ),
				'section' => __( 'Image Viewer Template', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings[] = array(
				'id'      => 'language_images_count_none_text',
				'title'   => __( 'Image Count None Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'No images', 'foogallery' ),
				'section' => __( 'Admin', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings[] = array(
				'id'      => 'language_images_count_single_text',
				'title'   => __( 'Image Count Single Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( '1 image', 'foogallery' ),
				'section' => __( 'Admin', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings[] = array(
				'id'      => 'language_images_count_plural_text',
				'title'   => __( 'Image Count Many Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( '%s images', 'foogallery' ),
				'section' => __( 'Admin', 'foogallery' ),
				'tab'     => 'language'
			);
			
			//endregion Language Tab

			//region Advanced Tab
			$tabs['advanced'] = __( 'Advanced', 'foogallery' );

            $settings[] = array(
                'id'      => 'enable_custom_ready',
                'title'   => __( 'Custom Ready Event', 'foogallery' ),
                'desc'    => sprintf( __( 'There are sometimes unavoidable javascript errors on the page, which could result in the gallery not initializing correctly. Enable this setting to use a built-in custom ready event to overcome this problem if needed.', 'foogallery' ), foogallery_plugin_name() ),
                'type'    => 'checkbox',
                'tab'     => 'advanced',
                'default' => 'on'
            );

            $settings[] = array(
                'id'      => 'add_media_button_start',
                'title'   => __( 'Move Add Media Button', 'foogallery' ),
                'desc'    => sprintf( __( 'You can move the Add Media button to the beginning of the attachment list. This can help when your galleries have a large number of images, so you do not have to scroll.', 'foogallery' ), foogallery_plugin_name() ),
                'type'    => 'checkbox',
                'tab'     => 'advanced'
            );

			$settings[] = array(
				'id'      => 'enable_legacy_thumb_cropping',
				'title'   => __( 'Enable Legacy Thumb Cropping', 'foogallery' ),
				'desc'    => __( 'Enables legacy thumbnail cropping for the Simple Portfolio gallery template, meaning it will not crop thumbnails.<br/>PLEASE NOTE : only enable this if you have been asked to by our support team.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'advanced'
			);

			$settings[] = array(
				'id'      => 'enable_debugging',
				'title'   => __( 'Enable Debugging', 'foogallery' ),
				'desc'    => sprintf( __( 'Helps to debug problems and diagnose issues. Enable debugging if you need support for an issue you are having.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'checkbox',
				'tab'     => 'advanced'
			);

            $settings[] = array(
                'id'      => 'enqueue_polyfills',
                'title'   => __( 'Enqueue Polyfills', 'foogallery' ),
                'desc'    => sprintf( __( '%s uses modern JavaScript API\'s which may not be supported in older browsers. Enable the enqueueing of polyfills for better backwards compatibility.', 'foogallery' ), foogallery_plugin_name() ),
                'type'    => 'checkbox',
                'tab'     => 'advanced'
            );

			$settings[] = array(
				'id'      => 'uninstall',
				'title'   => __( 'Full Uninstall', 'foogallery' ),
				'desc'    => sprintf( __( 'Run a full uninstall of %s, which includes removing all galleries, settings and metadata. This basically removes all traces of the plugin from your system. Please be careful - there is no undo!', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'uninstall',
				'tab'     => 'advanced'
			);

			if ( foogallery_thumb_active_engine()->has_local_cache() ) {
				$settings[] = array(
					'id'    => 'override_thumb_test',
					'title' => __( 'Override Thumb Test', 'foogallery' ),
					'desc'  => __( 'Sometimes there are problems running the thumbnail generation test. This overrides the test to use a remote image from our CDN.', 'foogallery' ),
					'type'  => 'checkbox',
					'tab'   => 'advanced',
				);
			}

			if ( !foogallery_is_pro() ) {
				$settings[] = array(
					'id'    => 'force_hide_trial',
					'title' => __( 'Force Hide Trial Notice', 'foogallery' ),
					'desc'  => __( 'Force the trial notice admin banner to never show', 'foogallery' ),
					'type'  => 'checkbox',
					'tab'   => 'advanced'
				);
			}

			$settings[] = array(
				'id'    => 'demo_content',
				'type'  => 'checkbox',
				'title' => __( 'Demo Content Created', 'foogallery' ),
				'desc'  => __( 'If the demo content has been created, then this will be checked. You can uncheck this to allow for demo content to be created again.', 'foogallery' ),
				'tab'   => 'advanced'
			);

			$settings[] = array(
				'id'    => 'attachment_id_attribute',
				'type'  => 'radio',
				'title' => __( 'Item ID Attribute', 'foogallery' ),
				'desc'  => __( 'Each item has an ID attribute which identifies itself. Changing the attribute will change what is used for deeplinking.', 'foogallery' ),
				'choices' => array(
					'data-attachment-id' => __( 'data-attachment-id', 'foogallery' ),
					'data-id' => __( 'data-id', 'foogallery' ),
				),
				'tab'   => 'advanced'
			);
			
			$custom_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );

			if ( !empty( $custom_post_types ) ) {
				$post_type_choices = array();
				foreach ( $custom_post_types as $post_type ) {
					$post_type_choices[$post_type->name] = $post_type->label;
				}

				$settings[] = array(
					'id'      => 'allowed_custom_post_types',
					'title'   => __( 'Allowed Custom Post Types', 'foogallery' ),
					'desc'    => __( 'Select the custom post types where galleries can be attached.', 'foogallery' ),
					'type'    => 'checkboxlist',
					'choices' => $post_type_choices,
					'tab'     => 'advanced'
				);
			}

			//endregion Advanced Tab

			//region Custom JS & CSS
			$tabs['custom_assets'] = __( 'Custom JS & CSS', 'foogallery' );

			$custom_assets = get_option( FOOGALLERY_OPTION_CUSTOM_ASSETS );
			$custom_style_extra = '';
			if ( is_array( $custom_assets ) && array_key_exists( 'style', $custom_assets ) ) {
				$custom_style_extra = '<br /><a target="_blank" href="' . $custom_assets['style'] . '">' . __( 'Open Custom Stylesheet', 'foogallery' ) . '</a>';
			}
			$custom_script_extra = '';
			if ( is_array( $custom_assets ) && array_key_exists( 'script', $custom_assets ) ) {
				$custom_script_extra = '<br /><a target="_blank" href="' . $custom_assets['script'] . '">' . __( 'Open Custom Script', 'foogallery' ) . '</a>';
			}

			$custom_js = foogallery_get_setting( 'custom_js', '' );
			if ( !empty( $custom_js ) && empty( $custom_script_extra ) ) {
				$custom_script_extra = '<br /><strong>' . __( 'There was a problem generating the custom JS file! This is usually caused by a permissions issue on your server.', 'foogallery' ) . '</strong>';
			}

			$custom_css = foogallery_get_setting( 'custom_css', '' );
			if ( !empty( $custom_css ) && empty( $custom_style_extra ) ) {
				$custom_style_extra = '<br /><strong>' . __( 'There was a problem generating the custom CSS file! This is usually caused by a permissions issue on your server.', 'foogallery' ) . '</strong>';
			}

			$settings[] = array(
				'id'      => 'custom_js',
				'title'   => __( 'Custom Javascript', 'foogallery' ),
				'desc'    => __( 'Custom Javascript that will be added to the page when a gallery is rendered.', 'foogallery' ) . $custom_script_extra,
				'type'    => 'textarea',
				'tab'     => 'custom_assets',
				'default' => ''
			);

			$settings[] = array(
				'id'      => 'custom_css',
				'title'   => __( 'Custom Stylesheet', 'foogallery' ),
				'desc'    => __( 'Custom CSS that will be added to the page when a gallery is rendered.', 'foogallery' ) . $custom_style_extra,
				'type'    => 'textarea',
				'tab'     => 'custom_assets',
				'default' => ''
			);
			//endregion Custom JS & CSS

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
				<div id="foogallery_clear_css_optimizations_container">
					<input type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_clear_css_optimizations' ) ); ?>" class="button-primary foogallery_clear_css_optimizations" value="<?php _e( 'Clear CSS Optimization Cache', 'foogallery' ); ?>">
					<span id="foogallery_clear_css_cache_spinner" style="position: absolute" class="spinner"></span>
				</div>
			<?php } else if ( 'uninstall' === $args['type'] ) { ?>
				<div id="foogallery_uninstall_container">
					<input type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_uninstall' ) ); ?>" class="button-primary foogallery_uninstall" value="<?php _e( 'Run Full Uninstall', 'foogallery' ); ?>">
					<span id="foogallery_uninstall_spinner" style="position: absolute" class="spinner"></span>
				</div>
			<?php } else if ( 'thumb_generation_test' === $args['type'] ) { ?>
				<div id="foogallery_thumb_generation_test_container">
					<input type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_thumb_generation_test' ) ); ?>" class="button-primary foogallery_thumb_generation_test" value="<?php _e( 'Run Tests', 'foogallery' ); ?>">
					<span id="foogallery_thumb_generation_test_spinner" style="position: absolute" class="spinner"></span>
				</div>
			<?php }
		}

		function after_render_setting( $args ) {
			if ( 'default_retina_support' === $args['id'] ) {

				//build up a list of retina options and add them to a hidden input
				// so we can get the values on the client
				$input_ids = array();
				$count = 0;
				foreach( foogallery_retina_options() as $retina_option ) {
					$input_ids[] = '#default_retina_support' . $count;
					$count++;
				}
				$nonce = wp_create_nonce( 'foogallery_apply_retina_defaults' );
				?><div id="foogallery_apply_retina_support_container">
					<input type="button" data-inputs="<?php echo implode( ',', $input_ids ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" class="button-primary foogallery_apply_retina_support" value="<?php _e( 'Apply Defaults to all Galleries', 'foogallery' ); ?>">
					<span id="foogallery_apply_retina_support_spinner" style="position: absolute" class="spinner"></span>
				</div>
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

		/**
		 * AJAX endpoint for testing thumbnail generation
		 */
		function ajax_thumb_generation_test() {
			if ( check_admin_referer( 'foogallery_thumb_generation_test' ) ) {
				foogallery_output_thumbnail_generation_results();
				die();
			}
		}

		/**
		 * AJAX endpoint for applying the retina defaults to all galleries
		 */
		function ajax_apply_retina_defaults() {
			if ( check_admin_referer( 'foogallery_apply_retina_defaults' ) ) {

				$defaults = $_POST['defaults'];

				//extract the settings using a regex
				$regex = '/foogallery\[default_retina_support\|(?<setting>.+?)\]/';

				preg_match_all($regex, $defaults, $matches);

				$gallery_retina_settings = array();

				if ( isset( $matches[1] ) ) {
					foreach ( $matches[1] as $match ) {
						$gallery_retina_settings[$match] = "true";
					}
				}

				//go through all galleries and update the retina settings
				$galleries = foogallery_get_all_galleries();
				$gallery_update_count = 0;
				foreach ( $galleries as $gallery ) {
					update_post_meta( $gallery->ID, FOOGALLERY_META_RETINA, $gallery_retina_settings );
					$gallery_update_count++;
				}

				echo sprintf( _n(
					'1 gallery successfully updated to use the default retina settings.',
					'%s galleries successfully updated to use the default retina settings.',
					$gallery_update_count, 'foogallery' ), $gallery_update_count );

				die();
			}
		}

		function ajax_uninstall() {
			if ( check_admin_referer( 'foogallery_uninstall' ) && current_user_can( 'install_plugins' ) ) {
				foogallery_uninstall();

				_e('All traces of the plugin were removed from your system!', 'foogallery' );
				die();
			}
		}

		function generate_assets( $old_value, $value, $option) {
			if ( !is_admin() ) {
				return;
			}

			if ( !current_user_can( 'manage_options' ) ) {
				return;
			}

			$custom_assets = array();

			if ( is_array( $value ) && array_key_exists( 'custom_js', $value ) ) {
				$custom_js = foogallery_prepare_code( $value['custom_js'] );

				if ( !empty( $custom_js ) ) {
					$custom_js = '/*
* FooGallery Custom Javascript
* This file is created by adding custom JS on FooGallery Settings page in wp-admin
* Created : ' . date( 'j M Y, g:i a', time() ) . '
*/

'. $custom_js;
					//generate script in upload folder
					$script_url = $this->generate_custom_asset( 'custom.js', $custom_js );
					if ( $script_url !== false ) {
						$custom_assets['script'] = $script_url;
					}
				}
			}

			//check if we have saved any custom CSS
			if ( is_array( $value ) && array_key_exists( 'custom_css', $value ) ) {
				$custom_css = foogallery_prepare_code( $value['custom_css'] );

				if ( !empty( $custom_css ) ) {
					$custom_css = '/*
* FooGallery Custom CSS
* This file is created by adding custom CSS on FooGallery Settings page in wp-admin
* Created : ' . date( 'j M Y, g:i a', time() ) . '
*/

'. $custom_css;
					//generate stylesheet in upload folder
					$style_url = $this->generate_custom_asset( 'custom.css', $custom_css );
					if ( $style_url !== false ) {
						$custom_assets['style'] = $style_url;
					}
				}
			}

			//set another option with the details
			if ( count( $custom_assets ) > 0 ) {
				update_option( FOOGALLERY_OPTION_CUSTOM_ASSETS, $custom_assets );
			} else {
				delete_option( FOOGALLERY_OPTION_CUSTOM_ASSETS );
			}
		}

		function generate_custom_asset( $filename, $contents ) {
			$upload_dir = wp_upload_dir();
			if ( !empty( $upload_dir['basedir'] ) ) {
				$dir = trailingslashit( $upload_dir['basedir'] ) . 'foogallery/';

				$fs = foogallery_wp_filesystem();
				if ( false !== $fs ) {
					$fs->mkdir( $dir ); // Make a new folder for storing our file
					if ( $fs->put_contents( $dir . $filename, $contents, 0644 ) ) {
						return set_url_scheme( trailingslashit( $upload_dir['baseurl'] ) . 'foogallery/' . $filename );
					}
				}
			}

			return false;
		}
	}
}
