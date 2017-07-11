<?php
/*
 * FooGallery Admin Extension class
 */

if ( ! class_exists( 'FooGallery_Admin_Extensions' ) ) {

	class FooGallery_Admin_Extensions {

		function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'deactivated_plugin', array( $this, 'handle_extensions_deactivation' ), 10, 2 );
			add_action( 'activated_plugin', array( $this, 'handle_extensions_activation' ), 10, 2 );
		}

		function init() {
			add_action( 'admin_init', array( $this, 'handle_extension_action' ) );
			//add_action( 'admin_init', array( $this, 'redirect_on_activation' ) );
		}

		function handle_extensions_deactivation( $plugin, $network_deactivating ) {
			//make sure that if we are dealing with a FooGallery extension, that we deactivate it too
			$api = new FooGallery_Extensions_API();
			$api->handle_wordpress_plugin_deactivation( $plugin );
		}

		function handle_extensions_activation( $plugin, $network_deactivating ) {
			//make sure that if we are dealing with a FooGallery extension, that we deactivate it too
			$api = new FooGallery_Extensions_API();
			$api->handle_wordpress_plugin_activation( $plugin );
		}

		function handle_extension_action() {
			$action         = safe_get_from_request( 'action' );
			$extension_slug = safe_get_from_request( 'extension' );
			$has_error      = safe_get_from_request( 'has_error' );

			if ( ( 'download' === $action || 'activate' === $action || 'deactivate' === $action ) && $extension_slug ) {
				$api = new FooGallery_Extensions_API();

				$fatal_error_redirect = remove_query_arg( 'action' );
				wp_redirect( add_query_arg( 'has_error', 'yes', $fatal_error_redirect ) ); // we'll override this later if the plugin can be included without fatal error
				ob_start();

				switch ( $action ) {
					case 'download':
						$result = $api->download( $extension_slug );
						break;
					case 'activate':
						$result = $api->activate( $extension_slug );
						break;
					case 'deactivate':
						$result = $api->deactivate( $extension_slug );
						break;
				}

				//if we get here then no fatal error - cool!
				ob_end_clean();

				//store the result in a short-lived transient
				if ( isset($result) ) {
					set_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY, $result, 30 );
				}

				//first, remove unwanted query args
				$redirect_url = remove_query_arg( array( 'extension', 'action' ) );
				//then add a query arg for our message
				$redirect_url = add_query_arg( 'show_message', 'yes', $redirect_url );
				//finally, allow extensions to override their own redirect
				$redirect_url = apply_filters( 'foogallery_extensions_redirect_url-' . $extension_slug, $redirect_url, $action );

				//redirect to this page, so the plugin can be properly activated/deactivated etc
				if ( $redirect_url ) {
					wp_redirect( $redirect_url );
					die();
				}
			} else if ( 'reload' === $action ) {
				$api = new FooGallery_Extensions_API();
				$api->reload();

				//first, remove unwanted query args
				$redirect_url = remove_query_arg( array( 'extension', 'action' ) );

				if ( ! $api->has_extension_loading_errors() ) {
					$result = array(
						'message' => __( 'The extensions have been reloaded', 'foogallery' ),
						'type'    => 'success',
					);

					set_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY, $result, 30 );

					//then add a query arg for our message
					$redirect_url = add_query_arg( 'show_message', 'yes', $redirect_url );
				}

				wp_redirect( $redirect_url );
				die();
			} else if ( $has_error ) {
				$api = new FooGallery_Extensions_API();
				$api->deactivate( $extension_slug, true, false );

				$result = array(
					'message' => __( 'The extension could not be activated due to an error!', 'foogallery' ),
					'type'    => 'error',
				);

				set_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY, $result, 30 );

				$api->add_to_error_extensions( $extension_slug, __( 'Activation Error!', 'foogallery' ) );

				//first, remove unwanted query args
				$redirect_url = remove_query_arg( array( 'extension', 'action', 'has_error' ) );
				//then add a query arg for our message
				$redirect_url = add_query_arg( 'show_message', 'yes', $redirect_url );

				wp_redirect( $redirect_url );
			}
		}

		function redirect_on_activation() {
			// Bail if no activation redirect
			if ( ! get_transient( FOOGALLERY_ACTIVATION_REDIRECT_TRANSIENT_KEY ) ) {
				return;
			}

			// Delete the redirect transient
			delete_transient( FOOGALLERY_ACTIVATION_REDIRECT_TRANSIENT_KEY );

			// Bail if activating from network, or bulk
			if ( is_network_admin() || isset($_GET['activate-multi']) ) {
				return;
			}

			wp_safe_redirect( foogallery_admin_help_url() );
			exit;
		}
	}
}
