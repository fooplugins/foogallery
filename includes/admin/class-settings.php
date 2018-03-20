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

			// Ajax calls
			add_action( 'wp_ajax_foogallery_clear_css_optimizations', array( $this, 'ajax_clear_css_optimizations' ) );
			add_action( 'wp_ajax_foogallery_thumb_generation_test', array( $this, 'ajax_thumb_generation_test' ) );
			add_action( 'wp_ajax_foogallery_apply_retina_defaults', array( $this, 'ajax_apply_retina_defaults' ) );
			add_action( 'wp_ajax_foogallery_uninstall', array( $this, 'ajax_uninstall' ) );
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
				$gallery_choices[ $gallery['ID'] ] = $gallery['name'];
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
				'id'      => 'thumb_resize_animations',
				'title'   => __( 'Resize Animated GIFs', 'foogallery' ),
				'desc'    => __( 'Should animated gifs be resized or not. If enabled, only the first frame is used in the resize.', 'foogallery' ),
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

			$settings[] = array(
				'id'      => 'thumb_generation_test',
				'title'   => __( 'Thumbnail Generation Test', 'foogallery' ),
				'desc'    => sprintf( __( 'Test to see if %s can generate the thumbnails it needs.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'thumb_generation_test',
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

			//region Advanced Tab
			$tabs['advanced'] = __( 'Advanced', 'foogallery' );

            $settings[] = array(
                'id'      => 'enable_custom_ready',
                'title'   => __( 'Custom Ready Event', 'foogallery' ),
                'desc'    => sprintf( __( 'By default the jQuery ready event is used, but there are sometimes unavoidable javascript errors on the page, which could result in the default gallery templates not initializing correctly. Enable this setting to use a built-in custom ready event to overcome this if needed.', 'foogallery' ), foogallery_plugin_name() ),
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
				'id'      => 'uninstall',
				'title'   => __( 'Full Uninstall', 'foogallery' ),
				'desc'    => sprintf( __( 'Run a full uninstall of %s, which includes removing all galleries, settings and metadata. This basically removes all traces of the plugin from your system. Please be careful - there is no undo!', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'uninstall',
				'tab'     => 'advanced'
			);

//			$settings[] = array(
//				'id'      => 'force_https',
//				'title'   => __( 'Force HTTPS', 'foogallery' ),
//				'desc'    => __( 'Force all thumbnails to use HTTPS protocol.', 'foogallery' ),
//				'type'    => 'checkbox',
//				'tab'     => 'advanced'
//			);

			//endregion Advanced Tab

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
		 * AJAX endpoint for testing thumbnail generation using WPThumb
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
	}
}
