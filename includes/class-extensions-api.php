<?php
/**
 * @TODO
 */

if ( ! class_exists( 'FooGallery_Extensions_API' ) ) {

	define( 'FOOGALLERY_EXTENSIONS_ENDPOINT', 'https://raw.githubusercontent.com/fooplugins/foogallery-extensions/master/extensions.json' );
	define( 'FOOGALLERY_EXTENSIONS_FUTURE_ENDPOINT', 'https://raw.githubusercontent.com/fooplugins/foogallery-extensions/future/extensions.json' );
	//define( 'FOOGALLERY_EXTENSIONS_ENDPOINT', FOOGALLERY_URL . 'extensions/extensions.json.js' );
	define( 'FOOGALLERY_EXTENSIONS_LOADING_ERRORS', 'foogallery_extensions_loading_errors' );
	define( 'FOOGALLERY_EXTENSIONS_LOADING_ERRORS_RESPONSE', 'foogallery_extensions_loading_errors_response' );
	define( 'FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY', 'foogallery_extensions_available' );
	define( 'FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY', 'foogallery_extensions_message' );
	define( 'FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY', 'foogallery_extensions_activated' );
	define( 'FOOGALLERY_EXTENSIONS_ERRORS_OPTIONS_KEY', 'foogallery_extensions_errors' );
	define( 'FOOGALLERY_EXTENSIONS_SLUGS_OPTIONS_KEY', 'foogallery_extensions_slugs' );
	define( 'FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY', 'foogallery_extensions_auto_activated' );

	/**
	 * Foogalolery Extensions Manager Class
	 * Class FooGallery_Extensions_API
	 */
	class FooGallery_Extensions_API {

		/**
		 * Internal list of all extensions
		 * @var array
		 */
		private $extensions = false;

		/**
		 * Internal list of all extension slugs
		 * @var array
		 */
		private $extension_slugs = false;

		/**
		 * Extension API constructor
		 * @param bool $load
		 */
		function __construct( $load = false ) {
			if ( $load ) {
				$this->load_available_extensions();
			}
		}

		/**
		 * Returns true if there were errors loading extensions
		 * @return bool
		 */
		public function has_extension_loading_errors() {
			return get_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS );
		}

		/**
		 * Returns the actual reposnse when there were errors trying to fetch all extensions
		 * @return mixed
		 */
		public function get_extension_loading_errors_response() {
			return get_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS_RESPONSE );
		}

		/**
		 * Get back the extension endpoint based on a setting
		 */
		public function get_extensions_endpoint() {
			if ( 'on' === foogallery_get_setting( 'use_future_endpoint' ) ) {
				$extension_url = FOOGALLERY_EXTENSIONS_FUTURE_ENDPOINT;
			} else {
				$extension_url = FOOGALLERY_EXTENSIONS_ENDPOINT;
			}
			return apply_filters('foogallery_extension_api_endpoint', $extension_url );
		}

		/**
		 * Reset all previous errors
		 */
		public function reset_errors() {
			delete_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS );
			delete_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS_RESPONSE );
		}

		/**
		 * Load all available extensions from the public endpoint and store in a transient for later use
		 */
		private function load_available_extensions() {
			if ( false === ( $this->extensions = get_transient( FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY ) ) ) {

				//clear any previous state
				$this->reset_errors();
				$this->extensions = null;
				$expires = 60 * 60 * 24; //1 day

				$extension_url = $this->get_extensions_endpoint();

				//fetch the data from our public list of extensions hosted on github
				$response = wp_remote_get( $extension_url, array( 'sslverify' => false ) );

				if( ! is_wp_error( $response ) ) {

					if ( $response['response']['code'] == 200 ) {
						$this->extensions = @json_decode( $response['body'], true );

						//if we got a valid list of extensions then calculate which are new and cache the result
						if ( is_array( $this->extensions ) ) {
							$this->determine_new_extensions( );
							$this->save_slugs_for_new_calculations();
						}
					}
				}

				if ( ! is_array( $this->extensions ) ) {
					//there was some problem getting a list of extensions. Could be a network error, or the extension json was malformed
					update_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS, true );
					update_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS_RESPONSE, $response );
					$this->extensions = $this->default_extenions_in_case_of_emergency();
					$expires = 5 * 60; //Only cache for 5 minutes if there are errors.
				}

				//Cache the result
				set_transient( FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY, $this->extensions, $expires );
			}
		}

		/**
		 * Get an array of default extensions.
		 * If for some reason, the extension list cannot be fetched from our public listing, we need to return the defaults so that the plugin can function offline
		 *
		 * @return array
		 */
		private function default_extenions_in_case_of_emergency() {
			$extensions = array();

			//Our default gallery templates
			$extensions[] = array(
				'slug'        => 'default_templates',
				'class'       => 'FooGallery_Default_Templates_Extension',
				'categories'  => array( 'Featured', 'Free', ),
				'title'       => 'Default Templates',
				'description' => 'The bundled gallery templates.',
				'author'      => 'FooPlugins',
				'author_url'  => 'http://fooplugins.com',
				'thumbnail'   => '/assets/extension_bg.png',
				'tags'        => array( 'template', ),
				'source'      => 'bundled',
				'activated_by_default' => true,
			);

			$extensions[] =	array(
				'slug' => 'albums',
				'class' => 'FooGallery_Albums_Extension',
				'title' => 'Albums',
				'categories' =>	array( 'Featured', 'Free' ),
				'description' => 'Group your galleries into albums. Boom!',
				'html' => 'Group your galleries into albums. Boom!',
				'author' => 'FooPlugins',
				'author_url' => 'http://fooplugins.com',
				'thumbnail' => '/extensions/albums/foogallery-albums.png',
				'tags' => array( 'functionality' ),
				'source' => 'bundled'
			);

			//FooBox lightbox
			$extensions[] = array (
				'slug' => 'foobox-image-lightbox',
				'class' => 'FooGallery_FooBox_Free_Extension',
				'categories' => array( 'Featured', 'Free', ),
				'file' => 'foobox-free.php',
				'title' => 'FooBox FREE',
				'description' => 'The best lightbox for WordPress. Free',
				'author' => 'FooPlugins',
				'author_url' => 'http://fooplugins.com',
				'thumbnail' => '/assets/extension_bg.png',
				'tags' => array( 'lightbox' ),
				'source' => 'repo',
				'activated_by_default' => true,
				'minimum_version' => '1.0.2.1',
			);

			//FooBox premium
			$extensions[] = array(
				'slug' => 'foobox',
				'class' => 'FooGallery_FooBox_Extension',
				'categories' => array( 'Featured', 'Premium' ),
				'file' => 'foobox.php',
				'title' => 'FooBox PRO',
				'description' => 'The best lightbox for WordPress just got even better!',
				'price' => '$27',
				'author' => 'FooPlugins',
				'author_url' => 'http://fooplugins.com',
				'thumbnail' => '/assets/extension_bg.png',
				'tags' => array( 'premium', 'lightbox', ),
				'source' => 'fooplugins',
				'download_button' =>
					array(
						'text' => 'Buy - $27',
						'target' => '_blank',
						'href' => 'http://fooplugins.com/plugins/foobox',
						'confirm' => false,
					),
				'activated_by_default' => true,
				'minimum_version' => '2.3.2',
			);

			//The NextGen importer
			$extensions[] = array(
				'slug' => 'nextgen',
				'class' => 'FooGallery_Nextgen_Gallery_Importer_Extension',
				'categories' => array( 'Free' ),
				'title' => 'NextGen Importer',
				'description' => 'Imports all your existing NextGen galleries',
				'author' => 'FooPlugins',
				'author_url' => 'http://fooplugins.com',
				'thumbnail' => '/assets/extension_bg.png',
				'tags' => array( 'tools' ),
				'source' => 'bundled',
			);

			return $extensions;
		}

		/**
		 * @TODO
		 */
		private function determine_new_extensions() {
			$previous_slugs = get_option( FOOGALLERY_EXTENSIONS_SLUGS_OPTIONS_KEY );
			if ( $previous_slugs ) {
				//only do something if we have a previously saved array
				foreach ( $this->extensions as &$extension ) {
					if ( ! in_array( $extension['slug'], $previous_slugs ) ) {
						if ( ! isset( $extension['tags'] ) ) {
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
		 * Clears the cached list of extensions
		 */
		public function clear_cached_extensions() {
			delete_transient( FOOGALLERY_EXTENSIONS_AVAILABLE_TRANSIENT_KEY );
		}

		/**
		 * Reload the extensions from the public endpoint
		 */
		public function reload() {
			$this->clear_cached_extensions();
			$this->load_available_extensions();
		}

		/**
		 * Get all loaded extensions
		 * @return array
		 */
		function get_all() {

			//check if we need to load
			if ( false === $this->extensions ) {
				$this->load_available_extensions();
			}

			//get any extra extensions from plugins
			$extra_extensions = apply_filters( 'foogallery_available_extensions', array() );

			if ( count( $extra_extensions ) > 0 ) {
				//get a list of slugs so we can determine duplicates!
				$this->extension_slugs = array();
				foreach ( $this->extensions as $extension ) {
					$this->extension_slugs[] = $extension['slug'];
				}

				//only add if not a duplicate
				foreach ( $extra_extensions as $extension ) {
					if ( ! in_array( $extension['slug'], $this->extension_slugs ) ) {
						$this->extensions[] = $extension;
					}
				}
			}

			return $this->extensions;
		}

		/**
		 * Get all loaded extensions slugs
		 * @return array
		 */
		function get_all_slugs() {
			//load all extensions first!
			$this->get_all();

			return $this->extension_slugs;
		}

		/**
		 * Returns a distinct array of categories that are used in the extensions
		 * @return mixed
		 */
		function get_all_categories() {
			$categories['all'] = array(
				'name' => __( 'All', 'foogallery' ),
			);
			$categories['activated'] = array(
				'name' => __( 'Active', 'foogallery' ),
			);
			$active = 0;
			foreach ( $this->get_all() as $extension ) {
				if ( $this->is_active( $extension['slug'] ) ) {
					$active++;
				}
				$category_names = $extension['categories'];
				foreach ( $category_names as $category_name ) {
					$category_slug = foo_convert_to_key( $category_name );

					if ( ! array_key_exists( $category_slug, $categories ) ) {
						$categories[ $category_slug ] = array(
							'name'  => $category_name,
						);
					}
				}
			}
			$categories['build_your_own'] = array(
				'name' => __( 'Build Your Own', 'foogallery' )
			);
			return apply_filters( 'foogallery_extension_categories', $categories );
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
		 *
		 * @return bool
		 */
		public function is_active( $slug ) {
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
			if ( ! $extension && $slug !== false ) {
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
						if ( null !== $failure ) {
							return array(
								'message' => sprintf( __( 'The extension %s could NOT be deactivated!', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
								'type' => 'error'
							);
						}
					}
				}

				$active_extensions = $this->get_active_extensions();
				if ( array_key_exists( $slug, $active_extensions ) ) {
					unset( $active_extensions[ $slug ] );
					if ( empty($active_extensions) ) {
						delete_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY );
					} else {
						update_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY, $active_extensions );
					}
				}

				if ( $error_loading ) {
					$this->add_to_error_extensions( $slug );
				}

				//we are done, allow for extensions to do something after an extension is activated
				do_action( 'foogallery_extension_deactivated-' . $slug );

				return apply_filters( 'foogallery_extensions_deactivated_message-' . $slug, array(
					'message' => sprintf( __( 'The extension %s was successfully deactivated', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
					'type' => 'success',
				) );
			}
			return array(
				'message' => sprintf( __( 'Unknown extension : %s', 'foogallery' ), $slug ),
				'type' => 'error',
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

					if ( $plugin ) {

						//check min version
						$minimum_version = foo_safe_get( $extension, 'minimum_version' );
						if ( !empty($minimum_version) ) {
							$actual_version = $plugin['plugin']['Version'];
							if ( version_compare( $actual_version, $minimum_version ) < 0 ) {
								$this->add_to_error_extensions( $slug, sprintf( __( 'Requires %s version %s','foogallery' ), $extension['title'], $minimum_version ) );
								return array(
									'message' => sprintf( __( 'The extension %s could not be activated, because you are using an outdated version! Please update %s to at least version %s.', 'foogallery' ), $extension['title'], $extension['title'], $minimum_version ),
									'type' => 'error',
								);
							}
						}

						//try to activate the plugin
						$failure = activate_plugin( $plugin['file'], '', false, false );
						if ( null !== $failure ) {
							return array(
								'message' => sprintf( __( 'The extension %s could NOT be activated!', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
								'type' => 'error',
							);
						}
					}
				}
				//load an instance of the extension class into memory
				$loader = new FooGallery_Extensions_Loader();
				$loader->load_extension( $slug, foo_safe_get( $extension, 'class', false ) );

				//then add the extension to our saved option so that it can be loaded on startup
				$this->add_to_activated_extensions( $extension );

				//we are done, allow for extensions to do something after an extension is activated
				do_action( 'foogallery_extension_activated-' . $slug );

				//return our result
				return apply_filters( 'foogallery_extension_activated_message-' . $slug, array(
					'message' => sprintf( __( 'The extension %s was successfully activated', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
					'type' => 'success',
				) );
			}
			return array(
				'message' => sprintf( __( 'Unknown extension : %s', 'foogallery' ), $slug ),
				'type' => 'error',
			);
		}

		/**
		 * @TODO
		 * @param boolean $extension
		 *
		 * @return array|bool
		 */
		private function find_wordpress_plugin( $extension ) {
			$plugins = get_plugins();
			foreach ( $plugins as $plugin_file => $plugin ) {
				if ( isset($extension['file']) && foo_ends_with( $plugin_file, $extension['file'] ) ) {
					return array(
						'file' => $plugin_file,
						'plugin' => $plugin,
						'active' => is_plugin_active( $plugin_file ),
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
							'message' => sprintf( __( 'Unable to connect to the WordPress.org plugin API to download %s. Full error log: %s', 'foogallery' ), $slug,  '<br />' . var_export( $plugins_api, true ) ),
							'type' => 'error',
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
						'message' => sprintf( __( 'The extension %s has no download link!', 'foogallery' ), $slug ),
						'type' => 'error',
					);
				}

				$skin = new FooGallery_Silent_Installer_Skin();

				//instantiate Plugin_Upgrader
				$upgrader = new Plugin_Upgrader( $skin );

				$upgrader->install( $download_link );

				if ( 'process_failed' === $skin->feedback ) {
					//we had an error along the way
					return apply_filters( 'foogallery_extensions_download_failure-' . $slug, array(
						'message' => sprintf( __( 'The extension %s could NOT be downloaded!', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
						'type' => 'error'
					) );
				}

				//return our result
				return apply_filters( 'foogallery_extensions_download_success-' . $slug, array(
					'message' => sprintf( __( 'The extension %s was successfully downloaded and can now be activated. %s', 'foogallery' ),
						"<strong>{$extension['title']}</strong>",
						'<a href="' . esc_url( add_query_arg( array(
								'action' => 'activate',
								'extension' => $slug, ) ) ) . '">' . __( 'Activate immediately', 'foogallery' ) . '</a>'
					),
					'type' => 'success',
				) );
			}
			return array(
				'message' => sprintf( __( 'Unknown extension : %s', 'foogallery' ), $slug ),
				'type' => 'error',
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
				return $error_extensions[ $slug ];
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
				$active_extensions[ $slug ] = $extension['class'];
				update_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY, $active_extensions );
			}
		}

		/**
		 * @TODO
		 * @param $slug
		 */
		public function add_to_error_extensions( $slug, $error_message = '' ) {
			$error_extensions = $this->get_error_extensions();
			if ( ! array_key_exists( $slug, $error_extensions ) ) {
				if ( empty($error_message) ) {
					$error_message = __( 'Error loading extension!', 'foogallery' );
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
				unset( $error_extensions[ $slug ] );
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
