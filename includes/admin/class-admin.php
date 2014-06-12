<?php
/*
 * FooGallery Admin class
 */

if (!class_exists('FooGallery_Admin')) {

	class FooGallery_Admin {

		function __construct() {
			add_action( 'init', array($this, 'init') );

			new FooGallery_Admin_Settings();
			new FooGallery_Admin_Menu();
			new FooGallery_Admin_Gallery_Editor();
			new FooGallery_Admin_Gallery_MetaBoxes();
			new FooGallery_Admin_Gallery_MetaBox_Fields();
			new FooGallery_Admin_Columns();
		}

		function init() {
			add_filter( 'media_upload_tabs', array($this, 'add_media_manager_tab') );
			add_action( 'media_upload_foo_gallery', array($this, 'media_manager_iframe_content') );
//			add_filter( 'media_view_strings', array($this, 'custom_media_string'), 10, 2);
			add_filter( 'foogallery-has_settings_page', '__return_false' );
			add_action( 'deactivated_plugin', array($this, 'handle_extensions_deactivation'), 10, 2 );
			add_action( 'activated_plugin', array($this, 'handle_extensions_activation'), 10, 2 );
			add_action( 'foogallery-admin_print_styles', array($this, 'admin_print_styles') );
			add_action( 'foogallery-admin_print_scripts', array($this, 'admin_print_scripts') );
			add_action( 'admin_init', array($this, 'handle_extension_action') );
			add_action( 'admin_init', array($this, 'redirect_on_activation') );
		}

		function handle_extension_action() {
			$action = safe_get_from_request('action');
			$extension_slug = safe_get_from_request('extension');
			$has_error = safe_get_from_request('has_error');

			if ( $action && $extension_slug ) {
				$api = new FooGallery_Extensions_API();

				$fatal_error_redirect = remove_query_arg( 'action' );
				wp_redirect ( add_query_arg( 'has_error', 'yes', $fatal_error_redirect ) ); // we'll override this later if the plugin can be included without fatal error
				ob_start();

				switch( $action ) {
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
					set_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY, $result, 30);
				}

				//first, remove unwanted query args
				$redirect_url = remove_query_arg( array( 'extension', 'action' ) );
				//then add a query arg for our message
				$redirect_url = add_query_arg( 'show_message', 'yes', $redirect_url );
				//finally, allow extensions to override their own redirect
				$redirect_url = apply_filters( 'foogallery_extensions_redirect_url-' . $extension_slug, $redirect_url, $action );

				//redirect to this page, so the plugin can be properly activated/deactivated etc
				if ($redirect_url) {
					wp_redirect( $redirect_url );
					die();
				}
			} else if ( 'reload' === $action ) {
				$api = new FooGallery_Extensions_API();
				$api->reload();

				$result = array(
					'message' => __('The extensions have been reloaded', 'foogallery'),
					'type' => 'success'
				);

				set_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY, $result, 30);

				//first, remove unwanted query args
				$redirect_url = remove_query_arg( array( 'extension', 'action' ) );
				//then add a query arg for our message
				$redirect_url = add_query_arg( 'show_message', 'yes', $redirect_url );

				wp_redirect( $redirect_url );
				die();
			} else if ( $has_error ) {
				$api = new FooGallery_Extensions_API();
				$api->deactivate( $extension_slug, true, true );

				$result = array(
					'message' => __('The extension could not be activated. It generated a fatal error!', 'foogallery'),
					'type' => 'error'
				);

				set_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY, $result, 30);

				//first, remove unwanted query args
				$redirect_url = remove_query_arg( array( 'extension', 'action', 'has_error' ) );
				//then add a query arg for our message
				$redirect_url = add_query_arg( 'show_message', 'yes', $redirect_url );

				wp_redirect( $redirect_url );
			}
		}

		function custom_media_string($strings,  $post){
			$strings['customMenuTitle'] = __('Custom Menu Title', 'custom');
			$strings['customButton'] = __('Custom Button', 'custom');
			return $strings;
		}

		function add_media_manager_tab($tabs) {
			$newtab = array( 'foo_gallery' => __('Insert FooGallery', '') );
			return array_merge( $tabs, $newtab );
		}

		function media_manager_iframe() {
			return wp_iframe( array($this, 'media_manager_iframe_content') );
		}

		function media_manager_iframe_content() {
			echo media_upload_header();
			echo 'Still under development!';
			return;
			?>
			<div class="media-frame-router">
				<div class="media-router">
					<a href="#" class="media-menu-item">Select Gallery</a>
					<a href="#" class="media-menu-item active">Create New Gallery</a>
				</div>
			</div>
			<div class="media-frame-toolbar">
				<div class="media-toolbar">
					<div class="media-toolbar-primary">
						<a href="#" class="button media-button button-primary button-large media-button-insert" disabled="disabled">Insert into page</a>
					</div>
				</div>
			</div>
		<?php
		}

		function handle_extensions_deactivation($plugin, $network_deactivating) {
			//make sure that if we are dealing with a FooGallery extension, that we deactivate it too
			$api = new FooGallery_Extensions_API();
			$api->handle_wordpress_plugin_deactivation( $plugin );
		}

		function handle_extensions_activation($plugin, $network_deactivating) {
			//make sure that if we are dealing with a FooGallery extension, that we deactivate it too
			$api = new FooGallery_Extensions_API();
			$api->handle_wordpress_plugin_activation( $plugin );
		}

		function admin_print_styles() {
			$page = safe_get_from_request( 'page' );
			$foogallery = FooGallery_Plugin::get_instance();
			$foogallery->register_and_enqueue_css( 'admin-page-' . $page . '.css' );
		}

		function admin_print_scripts() {
			$page = safe_get_from_request( 'page' );
			$foogallery = FooGallery_Plugin::get_instance();
			$foogallery->register_and_enqueue_js( 'admin-page-' . $page . '.js' );
		}

		function redirect_on_activation() {
			// Bail if no activation redirect
			if ( ! get_transient( FOOGALLERY_ACTIVATION_REDIRECT_TRANSIENT_KEY ) )
				return;

			// Delete the redirect transient
			delete_transient( FOOGALLERY_ACTIVATION_REDIRECT_TRANSIENT_KEY );

			// Bail if activating from network, or bulk
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
				return;

			wp_safe_redirect( foogallery_admin_help_url() ); exit;
		}
	}
}
