<?php
/**
 * @TODO
 */

if ( ! class_exists( 'FooGallery_Extensions_API' ) ) {

	define( 'FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY', 'foogallery_extensions_message' );
	define( 'FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY', 'foogallery_extensions_activated' );
	define( 'FOOGALLERY_EXTENSIONS_ERRORS_OPTIONS_KEY', 'foogallery_extensions_errors' );
	define( 'FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY', 'foogallery_extensions_auto_activated' );
    define( 'FOOGALLERY_EXTENSIONS_OVERRIDES_OPTIONS_KEY', 'foogallery_extensions_overrides' );

	/**
	 * FooGallery Extensions Manager Class
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
		 * Reset all previous errors
		 */
		public function reset_errors() {
			//delete_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS );
			//delete_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS_RESPONSE );
		}

		/**
		 * Load all available extensions
		 */
		private function load_available_extensions() {
			$this->extensions = array();

			$this->extensions[] =	array(
				'slug' => 'albums',
				'class' => 'FooGallery_Albums_Extension',
				'title' => 'Albums',
				'categories' =>	array( 'Featured', 'Free' ),
				'description' => foogallery__( 'Group your galleries into albums. Albums comes with 2 unique album templates to showcase your galleries.', 'foogallery' ),
				'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                'external_link_url' => 'https://fooplugins.com/documentation/foogallery/getting-started-foogallery/adding-albums/',
				'dashicon'          => 'dashicons-book-alt',
				'tags' => array( 'functionality', 'free', ),
				'source' => 'bundled'
			);

			// The FooGallery Migrate feature.
			$this->extensions[] = array(
				'slug' => 'foogallery-migrate',
				'class' => 'FooGallery_Migrate_Dummy',
				'categories' => array( 'Free' ),
				'title' => 'FooGallery Migrate',
                'file' => 'migrate.php',
				'description' => foogallery__( 'Migrate to FooGallery from other gallery plugins', 'foogallery' ),
				'external_link_text' => foogallery__( 'Read about FooGallery Migrate', 'foogallery' ),
                'external_link_url' => 'https://fooplugins.com/foogallery-migrate-for-wordpress-galleries/',
				'dashicon'          => 'dashicons-migrate',
				'tags' => array( 'tools', 'free', ),
				'source' => 'repo',
				'download_link' => 'https://downloads.wordpress.org/plugin/foogallery-migrate.latest-stable.zip',
			);
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
		 * return a list of all extensions for the extension view.
		 * This list could be changed based on other plugin
		 */
		function get_all_for_view() {
			$extensions = array();

			//add all extensions to an array using the slug as the array key
			foreach ( $this->get_all() as &$extension ) {
				$active = $this->is_active( $extension['slug'], true );
				$extension['downloaded'] = $active || $this->is_downloaded( $extension );
				$extension['is_active'] = $active;
				$extension['has_errors'] = $this->has_errors( $extension['slug'] );

                $extensions[ $extension['slug'] ] = $extension;
			}

			$extensions = apply_filters( 'foogallery_extensions_for_view', $extensions );

			return $extensions;
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
			$overrides = $this->get_overrides();
            if ( array_key_exists( $slug, $overrides ) ) {
                return $overrides[$slug] === 'active';
            }

            $active_extensions = $this->get_active_extensions();
			if ( array_key_exists( $slug, $active_extensions ) ) {
				//it has been previously activated through the extensions page
				return true;
			}

            $extension = $this->get_extension( $slug );

            //if we have an 'plugin_active_class' attribute and that class exists, it means our plugin must be active
            if ( isset( $extension['plugin_active_class'] ) ) {
                if ( class_exists( $extension['plugin_active_class'] ) ) {
                    return true;
                }
            }

            //if we have an 'activated_by_default' attribute and it is true, it means the extension is active
            if ( isset( $extension['activated_by_default'] ) && $extension['activated_by_default'] ) {
                return true;
            }

            //if we cannot find the extension class in memory, then check to see if the extension plugin is activated
            if ( isset( $extension['perform_plugin_active_check'] ) && true === $extension['perform_plugin_active_check'] &&
                isset( $extension['file'] ) ) {
                $plugin = $this->find_active_wordpress_plugin( $extension );

                return $plugin !== false;
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
				if ( $deactivate_wordpress_plugin && 'bundled' !== foo_safe_get( $extension, 'source', false ) ) {
					$plugin = $this->find_wordpress_plugin( $extension );
					if ( $plugin ) {
						$failure = deactivate_plugins( $plugin['file'], true, false );
						if ( null !== $failure ) {
							return array(
								'message' => sprintf( __( 'The feature %s could NOT be deactivated!', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
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

                $this->set_override( $slug, 'deactivated' );

				if ( $error_loading ) {
					$this->add_to_error_extensions( $slug );
				}

				//we are done, allow for extensions to do something after an extension is activated
				do_action( 'foogallery_extension_deactivated-' . $slug );

				return apply_filters( 'foogallery_extensions_deactivated_message-' . $slug, array(
					'message' => sprintf( __( 'The feature %s was successfully deactivated', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
					'type' => 'success',
				) );
			}
			return array(
				'message' => sprintf( __( 'Unknown feature : %s', 'foogallery' ), $slug ),
				'type' => 'error',
			);
		}

        private function set_override( $slug, $status ) {
            $overrides = $this->get_overrides();
            $overrides[$slug] = $status;
            update_option( FOOGALLERY_EXTENSIONS_OVERRIDES_OPTIONS_KEY, $overrides );
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
									'message' => sprintf( __( 'The feature %s could not be activated, because you are using an outdated version! Please update %s to at least version %s.', 'foogallery' ), $extension['title'], $extension['title'], $minimum_version ),
									'type' => 'error',
								);
							}
						}

						//try to activate the plugin
						$failure = activate_plugin( $plugin['file'], '', false, false );
						if ( null !== $failure ) {
							return array(
								'message' => sprintf( __( 'The feature %s could NOT be activated!', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
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

                $this->set_override( $slug, 'active' );

				//we are done, allow for extensions to do something after an extension is activated
				do_action( 'foogallery_extension_activated-' . $slug );

				//return our result
				return apply_filters( 'foogallery_extension_activated_message-' . $slug, array(
					'message' => sprintf( __( 'The feature %s was successfully activated', 'foogallery' ), "<strong>{$extension['title']}</strong>" ),
					'type' => 'success',
				) );
			}
			return array(
				'message' => sprintf( __( 'Unknown feature : %s', 'foogallery' ), $slug ),
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
		 * @param boolean $extension
		 *
		 * @return array|bool
		 */
		private function find_active_wordpress_plugin( $extension ) {
			$plugins = get_plugins();
			foreach ( $plugins as $plugin_file => $plugin ) {
				if ( is_plugin_active( $plugin_file ) && isset($extension['file']) && foo_ends_with( $plugin_file, $extension['file'] ) ) {
					return array(
						'file' => $plugin_file,
						'plugin' => $plugin
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
						'message' => sprintf( __( 'The feature %s has no download link!', 'foogallery' ), $slug ),
						'type' => 'error',
					);
				}

				$skin = new FooGallery_Silent_Installer_Skin();

				//instantiate Plugin_Upgrader
				$upgrader = new Plugin_Upgrader( $skin );

				$upgrader->install( $download_link );

				if ( 'process_failed' === $skin->feedback ) {
					$error_message = is_wp_error( $skin->result ) ? $skin->result->get_error_message() : __( 'Unknown!', 'foogallery' );

					//save the error message for the extension
					$this->add_to_error_extensions( $slug, sprintf( __('Could not be downloaded! Error : %s', 'foogallery' ), $error_message ) );

					//we had an error along the way
					return apply_filters( 'foogallery_extensions_download_failure-' . $slug, array(
						'message' => sprintf( __( 'The feature %s could NOT be downloaded! Error : %s', 'foogallery' ), "<strong>{$extension['title']}</strong>", $error_message ),
						'type' => 'error'
					) );
				}

				//return our result
				return apply_filters( 'foogallery_extensions_download_success-' . $slug, array(
					'message' => sprintf( __( 'The feature %s was successfully downloaded and can now be activated. %s', 'foogallery' ),
						"<strong>{$extension['title']}</strong>",
						'<a href="' . esc_url( add_query_arg( array(
								'action' => 'activate',
								'extension' => $slug, ) ) ) . '">' . __( 'Activate immediately', 'foogallery' ) . '</a>'
					),
					'type' => 'success',
				) );
			}
			return array(
				'message' => sprintf( __( 'Unknown feature : %s', 'foogallery' ), $slug ),
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
         * Returns the extension overrides.
         * @return mixed|void
         */
        public function get_overrides() {
            return get_option( FOOGALLERY_EXTENSIONS_OVERRIDES_OPTIONS_KEY, array() );
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

			if ( empty($error_message) ) {
				$error_message = __( 'Error loading feature!', 'foogallery' );
			}

			if ( array_key_exists( $slug, $error_extensions ) &&
				$error_message === $error_extensions[$slug]) {
				//do nothing!
			} else {
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
