<?php
/**
 * @TODO
 */

if ( !class_exists( 'FooGallery_Extensions_API' ) ) {

	//define('FOOGALLERY_EXTENSIONS_ENDPOINT', 'https://raw.githubusercontent.com/fooplugins/foogallery-extensions/master/README.md');
	define('FOOGALLERY_EXTENSIONS_ENDPOINT', FOOGALLERY_URL . 'extensions/extensions.json.js');
	define('FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY', 'foogallery_extensions_available');
	define('FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY', 'foogallery_extensions_message');
	define('FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY', 'foogallery_extensions_activated' );
	define('FOOGALLERY_EXTENSIONS_ERRORS_OPTIONS_KEY', 'foogallery_extensions_errors' );
	define('FOOGALLERY_EXTENSIONS_SLUGS_OPTIONS_KEY', 'foogallery_extensions_slugs' );
	define('FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY', 'foogallery_extensions_auto_activated' );

	/**
	 * @TODO
	 * Class FooGallery_Extensions_API
	 */
	class FooGallery_Extensions_API {

		/**
		 * @TODO
		 * @var array
		 */
		private $extensions = false;

		/**
		 * @TODO
		 * @var string
		 */
		private $error_message = false;

		/**
		 * @TODO
		 * @param bool $load
		 */
		function __construct($load = false) {
			if ( $load ) {
				$this->load_available_extensions();
			}
		}

		/**
		 * @TODO
		 */
		private function load_available_extensions() {
			if ( false === ( $this->extensions = get_transient( FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY ) ) ) {
				// It wasn't there, so fetch the data and save the transient
				$response = wp_remote_get( FOOGALLERY_EXTENSIONS_ENDPOINT );

				if( is_wp_error( $response ) ) {
					$this->error_message = $response->get_error_message();
				} else {
					$this->extensions = @json_decode( $response['body'], true );

					if ( NULL === $this->extensions ) {
						$this->error_message = 'There was a problem loading the extensions!';
						return;
					}

					$this->determine_new_extensions( );

					$expires = 60 * 60 * 24; //1 day
					set_transient( FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY, $this->extensions, $expires );
					$this->save_slugs_for_new_calculations();
				}
			}
		}

		/**
		 * @TODO
		 */
		private function determine_new_extensions() {
			$previous_slugs = get_option ( FOOGALLERY_EXTENSIONS_SLUGS_OPTIONS_KEY );
			if ( $previous_slugs ) {
				//only do something if we have a previously saved array
				foreach ( $this->extensions as &$extension ) {
					if ( !in_array( $extension['slug'], $previous_slugs ) ) {
						if ( !isset( $extension['tags'] ) ) {
							$extension['tags'] = array();
						}
						array_unshift( $extension['tags'] , __( 'new', 'foogallery' ) );
					}
				}
			}
		}

		/**
		 * @TODO
		 */
		private function save_slugs_for_new_calculations() {
			if ( is_array( $this->extensions ) ) {
				$slugs = array();
				foreach ( $this->extensions as $extension ) {
					$slugs[] = $extension['slug'];
				}
				if ( count( $slugs ) > 0 ) {
					update_option( FOOGALLERY_EXTENSIONS_SLUGS_OPTIONS_KEY, $slugs );
				}
			}
		}

		/**
		 * @TODO
		 */
		public function reload() {
			delete_transient( FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY );
			$this->load_available_extensions();
		}

		/**
		 * @TODO
		 * @return array|bool
		 */
		function get_all() {
			$this->error_message = false; //clear any errors

			//check if we need to load
			if ( false === $this->extensions ) {
				$this->load_available_extensions();
			}

			if ( !empty($this->error_message) ) {
				//we have errors!
				return array();
			}

			return $this->extensions;
		}

		/**
		 * @TODO
		 * @return mixed
		 */
		function get_all_categories() {
			$categories['all'] = array(
				'name' => __('All', 'foogallery')
			);
			$categories['activated'] = array(
				'name' => __('Active', 'foogallery')
			);
			$active = 0;
			foreach ( $this->get_all() as $extension ) {
				if ( $this->is_active( $extension['slug'] ) ) {
					$active++;
				}
				$category_names = $extension['categories'];
				foreach ( $category_names as $category_name ) {
					$category_slug = foo_convert_to_key( $category_name );
					if ( empty( $first_category ) ) { $first_category = $category_slug; }
					if ( !array_key_exists( $category_slug, $categories ) ) {
						$categories[$category_slug] = array(
							'name'  => $category_name
						);
					}
				}
			}
			if ( 0 == $active ) {
				unset( $categories['active'] );
				$categories[$first_category]['first'] = true;
			}
			return $categories;
		}

		/**
		 * @TODO
		 * @param $slug
		 *
		 * @return bool
		 */
		public function get_extension( $slug ) {
			foreach ( $this->get_all() as $extension ) {
				if ( $extension['slug'] === $slug ) {
					return $extension;
				}
			}
			return false;
		}

		/**
		 * @TODO
		 * @param $file
		 *
		 * @return bool
		 */
		public function get_extension_by_file( $file ) {
			$file = basename( $file ); //normalize to just the filename

			foreach ( $this->get_all() as $extension ) {
				if ( foo_safe_get( $extension, 'file' ) === $file ) {
					return $extension;
				}
			}
			return false;
		}

		/**
		 * @TODO
		 * @param      $slug
		 * @param bool $check_plugins
		 *
		 * @return bool
		 */
		public function is_active( $slug, $check_plugins = false ) {
			$active_extensions = $this->get_active_extensions();

			if ( $active_extensions ) {
				return array_key_exists( $slug, $active_extensions );
			}
			return false;
		}

		/**
		 * @TODO
		 *
		 * @param bool $extension
		 *
		 * @param bool $slug
		 *
		 * @return bool
		 */
		public function is_downloaded( $extension = false, $slug = false ) {
			//allow you to pass in a slug rather
			if ( !$extension && $slug !== false ) {
				$extension = $this->get_extension( $slug );
			}
			if ( $extension ) {
				//first check if the class exists
				if ( class_exists( $extension['class'] ) ) {
					return true;
				}

				//next fallback to see if a plugin exists that has the same file name
				$plugin = $this->find_wordpress_plugin( $extension );
				return false !== $plugin;
			}
			return false;
		}

		/**
		 * @TODO
		 * @param $slug
		 *
		 * @return bool
		 */
		public function has_errors( $slug ) {
			$error_extensions = $this->get_error_extensions();

			if ( $error_extensions ) {
				return array_key_exists( $slug, $error_extensions );
			}
			return false;
		}

		/**
		 * @TODO
		 * @param $plugin
		 */
		public function handle_wordpress_plugin_deactivation( $plugin ) {
			$extension = $this->get_extension_by_file( $plugin );
			if ( $extension ) {
				//we have found a matching extension
				$this->deactivate( $extension['slug'], false );
			}
		}

		/**
		 * @TODO
		 * @param $plugin
		 */
		public function handle_wordpress_plugin_activation( $plugin ) {
			$extension = $this->get_extension_by_file( $plugin );
			if ( $extension ) {
				//we have found a matching extension
				$this->activate( $extension['slug'], false );
			}
		}

		/**
		 * @TODO
		 * @param      $slug
		 * @param bool $deactivate_wordpress_plugin
		 * @param bool $error_loading
		 *
		 * @return array|mixed|void
		 */
		public function deactivate( $slug, $deactivate_wordpress_plugin = true, $error_loading = false ) {
			$extension = $this->get_extension( $slug );
			if ( $extension ) {
				if ( $deactivate_wordpress_plugin && 'bundled' === foo_safe_get( $extension, 'source', false ) ) {
					$plugin = $this->find_wordpress_plugin( $extension );
					if ( $plugin ) {
						$failure = deactivate_plugins( $plugin['file'], true, false );
						if (null !== $failure) {
							return apply_filters( 'foogallery_extensions_deactivate_failure-' . $slug, array(
								'message' => sprintf( __('The extension %s could NOT be deactivated!', 'foogallery'), "<strong>{$extension['title']}</strong>" ),
								'type' => 'error'
							) );
						}
					}
				}

				$active_extensions = $this->get_active_extensions();
				if ( array_key_exists( $slug, $active_extensions ) ) {
					unset( $active_extensions[$slug] );
					if ( empty($active_extensions) ) {
						delete_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY );
					} else {
						update_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY, $active_extensions );
					}
				}

				if ( $error_loading ) {
					$this->add_to_error_extensions( $slug );
				}

				do_action('foogallery_extension_deactivated', $slug);
				return apply_filters( 'foogallery_extensions_deactivate_success-' . $slug, array(
					'message' => sprintf( __('The extension %s was successfully deactivated', 'foogallery'), "<strong>{$extension['title']}</strong>" ),
					'type' => 'success'
				) );
			}
			return array(
				'message' => sprintf( __('Unknown extension : %s', 'foogallery'), $slug ),
				'type' => 'error'
			);
		}

		/**
		 * @TODO
		 *
		 * @param      $slug
		 * @param bool $activate_wordpress_plugin
		 *
		 * @return array|mixed|void
		 */
		public function activate( $slug, $activate_wordpress_plugin = true ) {
			$extension = $this->get_extension( $slug );
			if ( $extension ) {
				//first remove it from our error list (if it was there before)
				$this->remove_from_error_extensions( $slug );

				if ( $activate_wordpress_plugin && 'bundled' !== foo_safe_get( $extension, 'source', false ) ) {
					//activate the plugin, WordPress style!
					$plugin = $this->find_wordpress_plugin( $extension );

					//check min version
					$minimum_version = foo_safe_get($extension, 'minimum_version');
					if ( !empty($minimum_version) ) {
						$actual_version = $plugin['plugin']['Version'];
						if ( version_compare( $actual_version, $minimum_version ) < 0 ) {
							$this->add_to_error_extensions( $slug, sprintf( __('Requires %s version %s','foogallery'), $extension['title'], $minimum_version ) );
							return array(
								'message' => sprintf( __('The extension %s could not be activated, because you are using an outdated version! Please update %s to at least version %s.', 'foogallery'), $extension['title'], $extension['title'], $minimum_version ),
								'type' => 'error'
							);
						}
					}

					if ( $plugin ) {
						$failure = activate_plugin( $plugin['file'], '', false, true );
						if (null !== $failure) {
							return apply_filters( 'foogallery_extensions_activate_failure-' . $slug, array(
								'message' => sprintf( __('The extension %s could NOT be activated!', 'foogallery'), "<strong>{$extension['title']}</strong>" ),
								'type' => 'error'
							) );
						}
					}
				}
				//load an instance of the extension class into memory
				$loader = new FooGallery_Extensions_Loader();
				$loader->load_extension( $slug, foo_safe_get( $extension, 'class', false ) );

				//then add the extension to our saved option so that it can be loaded on startup
				$this->add_to_activated_extensions( $extension );

				//we are done, allow for extensions to do something after an extension is activated
				do_action('foogallery_extension_activated-' . $slug);

				//return our result
				return apply_filters( 'foogallery_extensions_activate_success-' . $slug, array(
					'message' => sprintf( __('The extension %s was successfully activated', 'foogallery'), "<strong>{$extension['title']}</strong>" ),
					'type' => 'success'
				) );
			}
			return array(
				'message' => sprintf( __('Unknown extension : %s', 'foogallery'), $slug ),
				'type' => 'error'
			);
		}

		/**
		 * @TODO
		 * @param $extension
		 *
		 * @return array|bool
		 */
		private function find_wordpress_plugin( $extension ) {
			$plugins = get_plugins();
			foreach ( $plugins as $plugin_file=>$plugin ) {
				if ( isset($extension['file']) && foo_ends_with( $plugin_file, $extension['file'] ) ) {
					return array(
						'file' => $plugin_file,
						'plugin' => $plugin,
						'active' => is_plugin_active($plugin_file)
					);
				}
			}
			return false;
		}

		/**
		 * @TODO
		 * @param $slug
		 *
		 * @return array|mixed|void
		 */
		public function download( $slug ) {
			$extension = $this->get_extension( $slug );
			if ( $extension ) {

				//we need some files!
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // plugins_api calls
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Plugin_Upgrader class
				require_once FOOGALLERY_PATH . 'includes/admin/class-silent-installer-skin.php'; //our silent installer skin

				$download_link = isset( $extension['download_link'] ) ? $extension['download_link'] : false;

				if ( 'repo' === $extension['source'] ) {
					$plugins_api = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );

					if ( is_wp_error( $plugins_api ) ) {
						return array(
							'message' => sprintf( __('Unable to connect to the WordPress.org plugin API to download %s. Full error log: %s', 'foogallery'), $slug,  '<br />' . var_export( $plugins_api, true ) ),
							'type' => 'error'
						);
					}

					//get the download link from the API call
					if ( isset( $plugins_api->download_link ) ) {
						$download_link = $plugins_api->download_link;
					}
				}

				//check we have something to download
				if ( empty( $download_link ) ) {
					return array(
						'message' => sprintf( __('The extension %s has no download link!', 'foogallery'), $slug ),
						'type' => 'error'
					);
				}

				$skin = new FooGallery_Silent_Installer_Skin();

				//instantiate Plugin_Upgrader
				$upgrader = new Plugin_Upgrader( $skin );

				$upgrader->install( $download_link );

				if ( 'process_failed' === $skin->feedback ) {
					//we had an error along the way
					return apply_filters( 'foogallery_extensions_download_failure-' . $slug, array(
						'message' => sprintf( __('The extension %s could NOT be downloaded!', 'foogallery'), "<strong>{$extension['title']}</strong>" ),
						'type' => 'error'
					) );
				}

				//return our result
				return apply_filters( 'foogallery_extensions_download_success-' . $slug, array(
					'message' => sprintf( __('The extension %s was successfully downloaded and can now be activated. %s', 'foogallery'),
						"<strong>{$extension['title']}</strong>",
						'<a href="' . add_query_arg( array(
								'action' => 'activate',
								'extension' => $slug ) ) . '">' . __('Activate immediately', 'foogallery') . '</a>'
					),
					'type' => 'success'
				) );
			}
			return array(
				'message' => sprintf( __('Unknown extension : %s', 'foogallery'), $slug ),
				'type' => 'error'
			);
		}

		/**
		 * @TODO
		 * @return mixed|void
		 */
		public function get_active_extensions() {
			return get_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY, array() );
		}

		/**
		 * @TODO
		 * @return mixed|void
		 */
		public function get_error_extensions() {
			return get_option( FOOGALLERY_EXTENSIONS_ERRORS_OPTIONS_KEY, array() );
		}

		public function get_error_message( $slug ) {
			$error_extensions = $this->get_error_extensions();
			if ( array_key_exists( $slug, $error_extensions ) ) {
				return $error_extensions[$slug];
			}
			return '';
		}

		/**
		 * @TODO
		 * @param $extension
		 */
		private function add_to_activated_extensions( $extension ) {
			$slug = $extension['slug'];
			$active_extensions = $this->get_active_extensions();
			if ( !array_key_exists( $slug, $active_extensions ) ) {
				$active_extensions[$slug] = $extension['class'];
				update_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY, $active_extensions );
			}
		}

		/**
		 * @TODO
		 * @param $slug
		 */
		private function add_to_error_extensions( $slug, $error_message = '' ) {
			$error_extensions = $this->get_error_extensions();
			if ( !array_key_exists( $slug, $error_extensions ) ) {
				if ( empty($error_message) ) {
					$error_message = __('Error loading extension!', 'foogallery');
				}
				$error_extensions[$slug] = $error_message;
				update_option( FOOGALLERY_EXTENSIONS_ERRORS_OPTIONS_KEY, $error_extensions );
			}
		}

		/**
		 * @TODO
		 * @param $slug
		 */
		private function remove_from_error_extensions( $slug ) {
			$error_extensions = $this->get_error_extensions();
			if ( array_key_exists( $slug, $error_extensions ) ) {
				unset( $error_extensions[$slug] );
				update_option( FOOGALLERY_EXTENSIONS_ERRORS_OPTIONS_KEY, $error_extensions );
			}
		}

		/**
		 * @TODO
		 */
		public function auto_activate_extensions() {
			foreach ( $this->get_all() as $extension ) {
				if ( true === foo_safe_get( $extension, 'activated_by_default' ) ) {
					//check to see if the extension is downloaded
					if ( $this->is_downloaded( $extension ) ) {
						$this->add_to_activated_extensions( $extension );
					}
				}
			}
		}
	}
}
