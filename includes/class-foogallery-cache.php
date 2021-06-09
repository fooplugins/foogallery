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
				add_action( 'foogallery_after_save_gallery', array( $this, 'cache_gallery_html_output_after_save' ), 10, 2 );

				//add some settings to allow the clearing and disabling of the cache
				add_filter( 'foogallery_admin_settings_override', array( $this, 'add_cache_settings' ) );

				//render the clear HTML cache button
				add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_settings' ) );

				// Ajax call for clearing HTML cache
				add_action( 'wp_ajax_foogallery_clear_html_cache', array( $this, 'ajax_clear_all_caches' ) );

				add_action( 'foogallery_admin_new_version_detected', array( $this, 'clear_cache_on_update' ) );

				//clear the gallery caches when settings are updated or reset
				add_action( 'foogallery_settings_updated', array( $this, 'clear_all_gallery_caches' ) );
				add_action( 'foogallery_settings_reset', array( $this, 'clear_all_gallery_caches' ) );
			}

			add_filter( 'foogallery_load_gallery_template', array( $this, 'fetch_gallery_html_from_cache' ), 10, 3 );

			add_filter( 'foogallery_html_cache_disabled', array( $this, 'disable_html_cache' ), 10, 3 );

			add_filter( 'foogallery_render_template_clear_globals' , array( $this, 'render_template_clear_globals' ) );
		}

		/**
		 * Override if the gallery html cache is disabled
		 *
		 * @param $disabled bool
		 * @param $gallery FooGallery
		 * @return bool
		 */
		function disable_html_cache( $disabled, $gallery ) {

			//check if the gallery sorting is random
			if ( 'rand' === $gallery->sorting ) {
				$disabled = true;
			}

			return $disabled;
		}

		/**
		 * Save the HTML output of the gallery after the gallery has been saved
		 *
		 * @param $post_id
		 * @param $form_post
		 */
		function cache_gallery_html_output_after_save( $post_id, $form_post ) {
			$this->cache_gallery_html_output( $post_id );
		}

		/**
		 * Save the HTML output of the gallery to post meta so that it can be used in future requests
		 *
		 * @param $foogallery_id
		 */
		function cache_gallery_html_output( $foogallery_id ) {
			$caching_enabled = $this->is_caching_enabled();

			//check if caching is disabled and quit early
			if ( !$caching_enabled ) {
				return;
			}

			//we need a way to force the gallery to render it's output every time it is saved
			global $foogallery_force_gallery_cache;
			$foogallery_force_gallery_cache = true;

			//capture the html output
			ob_start();
			foogallery_render_gallery( $foogallery_id );
			$gallery_html = ob_get_contents();
			ob_end_clean();

			if ( $caching_enabled ) {
				//save the output to post meta for later use
				update_post_meta( $foogallery_id, FOOGALLERY_META_CACHE, $gallery_html );
			}

			$foogallery_force_gallery_cache = false;
		}

		function is_caching_enabled() {
            global $current_foogallery_arguments;

			//do some checks if we are using arguments
			if ( isset( $current_foogallery_arguments ) ) {

                //never cache if showing a preview
                if ( array_key_exists( 'preview', $current_foogallery_arguments ) &&
                    true === $current_foogallery_arguments['preview'] ) {
                    return false;
                }

                //never cache if we are passing in extra arguments via the shortcode
                $array_keys = array_keys( $current_foogallery_arguments );
			    if ( $array_keys != array( 'id', 'gallery' ) ) {
                    return false;
                }
            }

			//next, check the settings
			if ( 'on' === foogallery_get_setting( 'enable_html_cache' ) ) {
				return true;
			}

			return false;
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
			if ( !$this->is_caching_enabled() ) {
				return false;
			}

			//allow extensions of others disable the html cache
			if ( apply_filters( 'foogallery_html_cache_disabled', false, $gallery ) ) {
				return false;
			}

			$gallery_cache = get_post_meta( $gallery->ID, FOOGALLERY_META_CACHE, true );

			if ( !empty( $gallery_cache ) && is_string( $gallery_cache ) ) {
				//output the cached gallery html
				echo $gallery_cache;
				return true; //return that we will override
			} else {
				//we should cache the result for next time
				$this->cache_gallery_html_output( $gallery->ID );
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
				'id'      => 'enable_html_cache',
				'title'   => __( 'Enable HTML Cache', 'foogallery' ),
				'desc'    => __( 'The gallery HTML that is generated can be cached. This can reduce the number of calls to the database when displaying a gallery and can increase site performance.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'general',
				'section' => __( 'Performance', 'foogallery' )
			);

			$cache_settings[] = array(
				'id'      => 'clear_html_cache',
				'title'   => __( 'Clear HTML Cache', 'foogallery' ),
				'desc'    => __( 'If you enable the HTML cache, then you can use this button to clear the gallery HTML that has been cached for all galleries.', 'foogallery' ),
				'type'    => 'clear_gallery_cache_button',
				'tab'     => 'general',
				'section' => __( 'Performance', 'foogallery' )
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

				$this->clear_all_gallery_caches();

				_e('The cache for all galleries has been cleared!', 'foogallery' );
				die();
			}
		}

		/**
		 * Clears all caches for all galleries
		 */
		function clear_all_gallery_caches() {
			delete_post_meta_by_key( FOOGALLERY_META_CACHE );
		}

		/**
		 * Clear all caches when the plugin has been updated. This is to account for changes in the HTML when a new version is released.
		 */
		function clear_cache_on_update() {
			$this->clear_all_gallery_caches();
		}

		/**
		 * Determine if the globals should be cleared when rendering a gallery
		 *
		 * @param $clear
		 *
		 * @return bool
		 */
		function render_template_clear_globals( $clear ) {
			global $foogallery_force_gallery_cache;
			if ( $foogallery_force_gallery_cache ) {
				return false;
			}

			return $clear;
		}
	}
}