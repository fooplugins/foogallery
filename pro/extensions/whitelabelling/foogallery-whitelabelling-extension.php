<?php
/**
 * FooGallery WhiteLabelling Extension
 */

namespace FooPlugins\FooGallery\Pro\Extensions\Whitelabelling;

if ( ! class_exists('FooGallery_Pro_Whitelabelling_Extension' ) ) {

	class FooGallery_Pro_Whitelabelling_Extension {

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_plugin_name', array( $this, 'plugin_name' ) );
			add_filter( 'foogallery_gallery_shortcode_tag', array( $this, 'shortcode_tag' ) );

			if ( is_admin() ) {
                //move menu under media
				add_filter( 'foogallery_gallery_posttype_register_args', array( $this, 'remove_posttype_menus' ) );
				add_filter( 'foogallery_admin_menu_parent_slug', array( $this, 'change_menu_parent_slug' ) );
				add_action( 'foogallery_admin_menu_before', array( $this, 'create_menus' ) );

				//menu visibility
				add_action( 'foogallery_admin_menu_after', array( $this, 'hide_menus' ) );
				add_filter( 'foogallery_admin_menu_capability', array( $this, 'menu_capability' ) );

				//menu labels
				add_filter( 'foogallery_admin_menu_labels', array( $this, 'override_menu_labels' ) );

				//create all our settings
				add_filter( 'foogallery_admin_settings', array( $this, 'create_settings' ), 9999, 2 );

				// Remove datasource menu from add gallery
				add_filter( 'foogallery_gallery_datasources', array( $this, 'remove_datasource' ) );

				//add_action( 'admin_menu', array( $this, 'remove_submenu' ), 99999 );
				add_action( 'admin_init', array( $this, 'remove_submenu' ), 99999 );

				// Redirect to media parent menu if setting enabled
				add_action( 'admin_init', array( $this, 'redirect_plugin_parent' ), 1 );

                add_filter( 'wp_redirect', array( $this, 'redirect_after_menu_updated' ), 10, 2 );
			}
		}

        /**
         * Handles redirections after the menu is set back to the FooGallery main menu.
         * Before this would result in an admin permissions error.
         *
         * @param $location
         * @param $status
         * @return array|mixed|string|string[]
         */
        function redirect_after_menu_updated( $location, $status ) {
            $url = parse_url( $location, PHP_URL_QUERY );
            if ( empty( $url ) ) {
                return $location;
            }

            parse_str( $url , $output );
            if ( array_key_exists( 'page', $output ) && 'foogallery-settings' === $output['page'] ) {
                if ( 'on' !== foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) &&
                 !array_key_exists( 'post_type', $output ) ) {
                    $location = str_replace( 'upload.php?', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY . '&settings-updated=true&', $location );
                }
            }

            return $location;
        }

		/**
		 * Returns the plugin name.
		 *
		 * @param string $default The default plugin name.
		 * @return string The  plugin name.
		 */
		function plugin_name( $default ) {
			return esc_html( foogallery_get_setting( 'whitelabelling_name', $default ) );
		}

		/**
		 * Returns the shortcode tag.
		 *
		 * @param string $default The default tag.
		 * @return string The shortcode tag.
		 */
		function shortcode_tag( $shortcode_tag ) {
			$whitelabel_shortcode = foogallery_get_setting( 'whitelabelling_shortcode', $shortcode_tag );
			return wp_kses( $whitelabel_shortcode, array() );
		}			

		/**
		 * Changes the parent slug for the plugin menu.
		 *
		 * @param string $default The default parent slug.
		 * @return string The new parent slug if whitelabelling is enabled, otherwise the default.
		 */
		function change_menu_parent_slug( $default ) {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) ) {
				return 'upload.php';
			}
			return $default;
		}

		/**
		 * Removes post type menus if whitelabelling is enabled.
		 *
		 * @param array $args The arguments for registering the post type.
		 * @return array The modified arguments with show_in_menu set to false if whitelabelling is enabled.
		 */
		function remove_posttype_menus( $args) {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) ) {
				$args['show_in_menu'] = false;
			}
			return $args;
		}

		/**
		 * Creates whitelabelled menus if whitelabelling is enabled.
		 */
		function create_menus() {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) ) {
				add_media_page( __( 'Galleries', 'foogallery' ), __( 'Galleries', 'foogallery' ), 'upload_files', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY );
				add_media_page( __( 'Add Gallery', 'foogallery' ), __( 'Add Gallery', 'foogallery' ), 'upload_files', 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY );
			}
		}

		/**
		 * Overrides the menu capability if whitelabelling is enabled.
		 *
		 * @param string $default The default capability.
		 * @return string The overridden capability if set, otherwise the default.
		 */
		function menu_capability( $default ) {
			$override = foogallery_get_setting( 'whitelabelling_menu_capability' );

			if ( $default != $override && ! empty( $override ) ) {
				return $override;
			}

			return $default;
		}

		/**
		 * Hides various menus based on whitelabelling settings.
		 */
		function hide_menus() {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_settings_menu' ) && !current_user_can( 'administrator' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-settings' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_extensions' ) ||
				'on' == foogallery_get_setting( 'whitelabelling_hide_extensions_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-features' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_help_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-help' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_system_info_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-systeminfo' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_account_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-account' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_pricing_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-pricing' );
			}
		}

		/**
		 * Overrides menu labels based on whitelabelling settings.
		 *
		 * @param array $menu_labels The default menu labels.
		 * @return array The overridden menu labels.
		 */
		function override_menu_labels( $menu_labels ) {
			$settings_label = esc_html( foogallery_get_setting( 'whitelabelling_label_settings_menu' ) );
			$extensions_label = esc_html( foogallery_get_setting( 'whitelabelling_label_extensions_menu' ) );
			$help_label = esc_html( foogallery_get_setting( 'whitelabelling_label_help_menu' ) );

			if ( ! empty( $settings_label ) ) {
				$menu_labels[0]['menu_title'] = $settings_label;
			}

			if ( ! empty( $extensions_label ) ) {
				$menu_labels[1]['menu_title'] = $extensions_label;
			}

			if ( ! empty( $help_label ) ) {
				$menu_labels[2]['menu_title'] = $help_label;
			}

			return $menu_labels;
		}

		/**
		 * Creates settings based on whitelabelling configurations.
		 *
		 * @param array $settings The default settings.
		 * @return array The modified settings including whitelabelling settings.
		 */
		function create_settings( $settings ) {

			$whitelabelling_tabs['whitelabelling'] = __( 'White Labeling', 'foogallery' );

			$whitelabelling_settings[] = array(
		        'id'      => 'whitelabelling_name',
		        'title'   => __('Plugin Name', 'foogallery' ),
		        'desc'    => __('Rename "FooGallery" to something more client friendly, for example "Pro Gallery"', 'foogallery' ),
		        'default' => 'FooGallery',
		        'type'    => 'text',
		        'tab'     => 'whitelabelling'
	        );

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_shortcode',
				'title'   => __('Shortcode', 'foogallery' ),
				'desc'    => __('Override the shortcode to something more client friendly, for example "progallery". (Please do not include square brackets!)', 'foogallery' ),
				'default' => 'foogallery',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_sections['menu'] = array(
				'name' => __( 'Menu', 'foogallery' )
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_move_menu_under_media',
				'title'   => __('Move Under Media Menu', 'foogallery' ),
				'desc'    => sprintf( __( 'Move all %s menu items under the WordPress media menu.', 'foogallery' ), foogallery_plugin_name() ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_menu_capability',
				'title'   => __('Menu Visibility', 'foogallery' ),
				'desc'    => __('Who can see the Help, Features and Settings menu items.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'select',
				'choices' => array(
					'manage_options' => 'Administrators',
					'delete_others_posts' => 'Editors',
					'publish_posts' => 'Authors',
					'edit_posts' => 'Contributors'
				),
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_settings_menu',
				'title'   => __('Hide Settings Menu', 'foogallery' ),
				'desc'    => __('Hide the settings menu item. Please note : this menu will still be shown if you are an administrator!', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_extensions_menu',
				'title'   => __('Hide Features Menu', 'foogallery' ),
				'desc'    => __('Hide the features menu item.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_help_menu',
				'title'   => __('Hide Help Menu', 'foogallery' ),
				'desc'    => __('Hide the help menu item.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_system_info_menu',
				'title'   => __('Hide System Info Menu', 'foogallery' ),
				'desc'    => __('Hide the system info menu item.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_account_menu',
				'title'   => __('Hide Account Menu', 'foogallery' ),
				'desc'    => __('Hide the account menu item.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_pricing_menu',
				'title'   => __('Hide Pricing Menu', 'foogallery' ),
				'desc'    => __('Hide the pricing menu item.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_label_settings_menu',
				'title'   => __('Settings Menu Label', 'foogallery' ),
				'desc'    => __('Change the settings menu text.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_label_extensions_menu',
				'title'   => __('Features Menu Label', 'foogallery' ),
				'desc'    => __('Change the features menu text.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_label_help_menu',
				'title'   => __('Help Menu Label', 'foogallery' ),
				'desc'    => __('Change the help menu text.', 'foogallery' ),
				'section' => 'menu',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_sections['data-sources'] = array(
				'name' => __( 'Data Sources', 'foogallery' )
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_media_tags_menu',
				'title'   => __('Disable Media Tags', 'foogallery' ),
				'desc'    => __('Disable the media tags datasource.', 'foogallery' ),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_media_categories_menu',
				'title'   => __('Disable Media Categories', 'foogallery' ),
				'desc'    => __('Disable the media categories datasource.', 'foogallery' ),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);		

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_server_folder_menu',
				'title'   => __('Disable Server Folder', 'foogallery' ),
				'desc'    => __('Disable the server folder datasource.', 'foogallery' ),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_adobe_lightroom_menu',
				'title'   => __('Disable Adobe Lightroom', 'foogallery' ),
				'desc'    => __('Disable the adobe lightroom datasource.', 'foogallery' ),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);
			
			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_real_media_lib_menu',
				'title'   => __('Disable Real Media Library', 'foogallery' ),
				'desc'    => __('Disable the real media library datasource.', 'foogallery' ),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_post_query_menu',
				'title'   => __('Disable Post Query', 'foogallery' ),
				'desc'    => __('Disable the post query datasource.', 'foogallery' ),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_woo_products_menu',
				'title'   => __('Disable Woocommerce Products', 'foogallery' ),
				'desc'    => __('Disable the woocommerce products datasource.', 'foogallery' ),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);	

            $settings = array_merge_recursive( $settings, array(
            	'tabs'     => $whitelabelling_tabs,
                'sections' => $whitelabelling_sections,
                'settings' => $whitelabelling_settings,
            ) );

			return $settings;
		}

		/**
		 * Removes specific submenus based on whitelabelling settings.
		 */
		function remove_submenu() {

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_account_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-account' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_pricing_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-pricing' );
			}
		}

		/**
		 * Removes specific datasources based on whitelabelling settings.
		 *
		 * @param array $datasources The default datasources.
		 * @return array The modified datasources excluding hidden ones.
		 */
		public function remove_datasource( $datasources ) {

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_media_tags_menu' ) ) {
				unset( $datasources['media_tags']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_media_categories_menu' ) ) {
				unset( $datasources['media_categories']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_server_folder_menu' ) ) {
				unset( $datasources['folders']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_adobe_lightroom_menu' ) ) {
				unset( $datasources['lightroom']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_real_media_lib_menu' ) ) {
				unset( $datasources['rml']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_post_query_menu' ) ) {
				unset( $datasources['post_query']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_woo_products_menu' ) ) {
				unset( $datasources['woocommerce']);
			}

			return $datasources;
		}

		/**
		 * Redirects to a specific plugin parent page if whitelabelling is enabled.
		 */
		function redirect_plugin_parent() {
			$request_uri = 	$_SERVER['REQUEST_URI'];
			$parse_url = wp_parse_url( $request_uri);

			if ( is_array( $parse_url ) && !empty ( $parse_url ) && !empty ( $parse_url['path'] ) ) {
				if ( isset( $_GET['post_type'] ) && 'foogallery' == $_GET['post_type'] && 'on' == foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) ) {
					wp_redirect( admin_url( 'upload.php?page=foogallery-settings#whitelabelling' ) );
					exit;
				} 
			}
	
		}

	}

}