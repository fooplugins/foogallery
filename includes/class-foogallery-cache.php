<?php
/**
 * Class used to cache gallery HTML output to save requests to the database
 * Date: 20/03/2017
 */
if ( ! class_exists( 'FooGallery_Cache' ) ) {

	class FooGallery_Cache {

		function __construct() {
			if ( is_admin() ) {
				//intercept the gallery save and save the html output to post meta
				add_action( 'foogallery_after_save_gallery', array( $this, 'cache_gallery_html_output' ), 10, 2 );

				//add some settings to allow the clearing and disabling of the cache
				add_filter( 'foogallery_admin_settings_override', array( $this, 'add_cache_settings' ) );

				//render the clear HTML cache button
				add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_settings' ) );

				// Ajax call for clearing HTML cache
				add_action( 'wp_ajax_foogallery_clear_html_cache', array( $this, 'ajax_clear_all_caches' ) );
			}


			add_filter( 'foogallery_load_gallery_template', array( $this, 'fetch_gallery_html_from_cache' ), 10, 3 );
		}

		/**
		 * Save the HTML output of the gallery to post meta so that it can be used in future requests
		 *
		 * @param $post_id
		 * @param $form_post
		 */
		function cache_gallery_html_output( $post_id, $form_post ) {
			//check if caching is disabled and quit early
			if ( 'on' === foogallery_get_setting( 'disable_html_cache' ) ) {
				return;
			}

			//we need a way to force the gallery to render it's output every time it is saved
			global $foogallery_force_gallery_cache;
			$foogallery_force_gallery_cache = true;

			//capture the html output
			ob_start();
			foogallery_render_gallery( $post_id );
			$gallery_html = ob_get_contents();
			ob_end_clean();

			//save the output to post meta for later use
			update_post_meta( $post_id, FOOGALLERY_META_CACHE, $gallery_html );

			$foogallery_force_gallery_cache = false;
		}

		/**
		 * Override the template output
		 *
		 * @param $override
		 * @param $gallery
		 * @param $template_location
		 *
		 * @return bool
		 */
		function fetch_gallery_html_from_cache( $override, $gallery, $template_location ) {
			global $foogallery_force_gallery_cache;
			if ( $foogallery_force_gallery_cache ) {
				return false;
			}

			//check if caching is disabled and quit early
			if ( 'on' === foogallery_get_setting( 'disable_html_cache' ) ) {
				return false;
			}

			$gallery_cache = get_post_meta( $gallery->ID, FOOGALLERY_META_CACHE, true );

			if ( !empty( $gallery_cache ) ) {
				//output the cached gallery html
				echo $gallery_cache;
				return true; //return that we will override
			}

			return false;
		}

		/**
		 * Add some caching settings
		 * @param $settings
		 *
		 * @return array
		 */
		function add_cache_settings( $settings ) {

			$cache_settings[] = array(
				'id'      => 'disable_html_cache',
				'title'   => __( 'Disable HTML Cache', 'foogallery' ),
				'desc'    => __( 'The gallery HTML is cached by default. You can choose to disable the cache for debugging purposes. It is NOT recommended.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'general',
				'section' => __( 'Cache', 'foogallery' )
			);

			$cache_settings[] = array(
				'id'      => 'clear_html_cache',
				'title'   => __( 'Clear HTML Cache', 'foogallery' ),
				'desc'    => sprintf( __( '%s caches the gallery HTML output to improve page performance. Use this button to clear the gallery HTML that has been cached across all galleries.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'clear_gallery_cache_button',
				'tab'     => 'general',
				'section' => __( 'Cache', 'foogallery' )
			);

			$new_settings = array_merge( $cache_settings, $settings['settings'] );

			$settings['settings'] = $new_settings;

			return $settings;
		}

		/**
		 * Render any custom setting types to the settings page
		 */
		function render_settings( $args ) {
			if ('clear_gallery_cache_button' === $args['type'] ) { ?>
				<div id="foogallery_clear_html_cache_container">
					<input type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_clear_html_cache' ) ); ?>" class="button-primary foogallery_clear_html_cache" value="<?php _e( 'Clear Gallery HTML Cache', 'foogallery' ); ?>">
					<span id="foogallery_clear_html_cache_spinner" style="position: absolute" class="spinner"></span>
				</div>
			<?php }
		}

		/**
		 * AJAX endpoint for clearing all gallery caches
		 */
		function ajax_clear_all_caches() {
			if ( check_admin_referer( 'foogallery_clear_html_cache' ) ) {

				$galleries = foogallery_get_all_galleries();

				foreach( $galleries as $gallery ) {
					delete_post_meta( $gallery->ID, FOOGALLERY_META_CACHE );
				}

				_e('The cache for all galleries has been cleared!', 'foogallery' );
				die();
			}
		}
	}
}