<?php
/**
 * FooGallery WhiteLabelling Extension
 */

if ( ! class_exists('FooGallery_Pro_Whitelabelling_Extension') ) {

	class FooGallery_Pro_Whitelabelling_Extension {

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_plugin_name', array( $this, 'plugin_name' ) );
			add_filter( 'foogallery_gallery_shortcode_tag', array( $this, 'shortcode_tag' ) );

			if ( is_admin() ) {
                //move menu under media
				add_filter( 'foogallery_gallery_posttype_register_args', array($this, 'remove_posttype_menus') );
				add_filter( 'foogallery_admin_menu_parent_slug', array($this, 'change_menu_parent_slug') );
				add_action( 'foogallery_admin_menu_before', array($this, 'create_menus') );

				//menu visibility
				add_action( 'foogallery_admin_menu_after', array($this, 'hide_menus') );
				add_filter( 'foogallery_admin_menu_capability', array($this, 'menu_capability') );

				//menu labels
				add_filter( 'foogallery_admin_menu_labels', array($this, 'override_menu_labels') );

				//create all our settings
				add_filter( 'foogallery_admin_settings', array($this, 'create_settings'), 9999, 2 );

				// Remove datasource menu from add gallery
				add_filter( 'foogallery_gallery_datasources', array( $this, 'remove_datasource' ) );

				//add_action( 'admin_menu', array( $this, 'remove_submenu' ), 99999 );
				add_action( 'admin_init', array( $this, 'remove_submenu' ), 99999 );


			}
		}

		function plugin_name( $default ) {
			return foogallery_get_setting( 'whitelabelling_name', $default );
		}

		function shortcode_tag( $shortcode_tag ) {
			return foogallery_get_setting( 'whitelabelling_shortcode', $shortcode_tag );
		}

		function change_menu_parent_slug( $default ) {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) ) {
				return 'upload.php';
			}
			return $default;
		}

		function remove_posttype_menus($args) {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) ) {
				$args['show_in_menu'] = false;
			}
			return $args;
		}

		function create_menus() {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_move_menu_under_media' ) ) {
				add_media_page( __( 'Galleries', 'foogallery' ), __( 'Galleries', 'foogallery' ), 'upload_files', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY );
				add_media_page( __( 'Add Gallery', 'foogallery' ), __( 'Add Gallery', 'foogallery' ), 'upload_files', 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY );
			}
		}

		function menu_capability( $default ) {
			$override = foogallery_get_setting( 'whitelabelling_menu_capability' );

			if ( $default != $override && ! empty( $override ) ) {
				return $override;
			}

			return $default;
		}

		function hide_menus() {
			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_settings_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-settings' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_extensions' ) ||
				'on' == foogallery_get_setting( 'whitelabelling_hide_extensions_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-extensions' );
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

		function override_menu_labels( $menu_labels ) {
			$settings_label = foogallery_get_setting( 'whitelabelling_label_settings_menu' );
			$extensions_label = foogallery_get_setting( 'whitelabelling_label_extensions_menu' );
			$help_label = foogallery_get_setting( 'whitelabelling_label_help_menu' );

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

		function create_settings( $settings ) {

			$whitelabelling_tabs['whitelabelling'] = __( 'White Labeling', 'foogallery' );

			$whitelabelling_settings[] = array(
		        'id'      => 'whitelabelling_name',
		        'title'   => __('Plugin Name', 'foogallery'),
		        'desc'    => __('Rename "FooGallery" to something more client friendly, for example "Pro Gallery"', 'foogallery'),
		        'default' => 'FooGallery',
		        'type'    => 'text',
		        'tab'     => 'whitelabelling'
	        );

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_shortcode',
				'title'   => __('Shortcode', 'foogallery'),
				'desc'    => __('Override the shortcode to something more client friendly, for example "progallery". (Please do not include square brackets!)', 'foogallery'),
				'default' => 'foogallery',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_sections['menu'] = array(
				'name' => __( 'Menu', 'foogallery' )
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_move_menu_under_media',
				'title'   => __('Move Under Media Menu', 'foogallery'),
				'desc'    => sprintf( __( 'Move all %s menu items under the WordPress media menu.', 'foogallery' ), foogallery_plugin_name() ),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_menu_capability',
				'title'   => __('Menu Visibility', 'foogallery'),
				'desc'    => __('Who can see the Help, Extensions and Settings menu items.', 'foogallery'),
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
				'title'   => __('Hide Settings Menu', 'foogallery'),
				'desc'    => __('Hide the settings menu item.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_extensions_menu',
				'title'   => __('Hide Extensions Menu', 'foogallery'),
				'desc'    => __('Hide the extension menu item.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_help_menu',
				'title'   => __('Hide Help Menu', 'foogallery'),
				'desc'    => __('Hide the help menu item.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_system_info_menu',
				'title'   => __('Hide System Info Menu', 'foogallery'),
				'desc'    => __('Hide the system info menu item.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_account_menu',
				'title'   => __('Hide Account Menu', 'foogallery'),
				'desc'    => __('Hide the account menu item.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_pricing_menu',
				'title'   => __('Hide Pricing Menu', 'foogallery'),
				'desc'    => __('Hide the pricing menu item.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_label_settings_menu',
				'title'   => __('Settings Menu Label', 'foogallery'),
				'desc'    => __('Change the settings menu text.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_label_extensions_menu',
				'title'   => __('Extension Menu Label', 'foogallery'),
				'desc'    => __('Change the extensions menu text.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_label_help_menu',
				'title'   => __('Help Menu Label', 'foogallery'),
				'desc'    => __('Change the help menu text.', 'foogallery'),
				'section' => 'menu',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_sections['data-sources'] = array(
				'name' => __( 'Data Sources', 'foogallery' )
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_media_tags_menu',
				'title'   => __('Hide Media Tags Menu', 'foogallery'),
				'desc'    => __('Hide the media tags menu item.', 'foogallery'),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_media_categories_menu',
				'title'   => __('Hide Media Categories Menu', 'foogallery'),
				'desc'    => __('Hide the media categories menu item.', 'foogallery'),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);		

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_server_folder_menu',
				'title'   => __('Hide Server Folder Menu', 'foogallery'),
				'desc'    => __('Hide the server folder menu item.', 'foogallery'),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_adobe_lightroom_menu',
				'title'   => __('Hide Adobe Lightroom Menu', 'foogallery'),
				'desc'    => __('Hide the adobe lightroom menu item.', 'foogallery'),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);
			
			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_real_media_lib_menu',
				'title'   => __('Hide Real Media Library Menu', 'foogallery'),
				'desc'    => __('Hide the real media library menu item.', 'foogallery'),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_post_query_menu',
				'title'   => __('Hide Post Query Menu', 'foogallery'),
				'desc'    => __('Hide the post query menu item.', 'foogallery'),
				'section' => 'data-sources',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$whitelabelling_settings[] = array(
				'id'      => 'whitelabelling_hide_woo_products_menu',
				'title'   => __('Hide Woocommerce Products Menu', 'foogallery'),
				'desc'    => __('Hide the woocommerce products menu item.', 'foogallery'),
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

		function remove_submenu() {

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_account_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-account' );
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_pricing_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-pricing' );
			}
		}

		public function remove_datasource( $datasources ) {

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_media_tags_menu' ) ) {
				unset($datasources['media_tags']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_media_categories_menu' ) ) {
				unset($datasources['media_categories']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_server_folder_menu' ) ) {
				unset($datasources['folders']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_adobe_lightroom_menu' ) ) {
				unset($datasources['lightroom']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_real_media_lib_menu' ) ) {
				unset($datasources['rml']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_post_query_menu' ) ) {
				unset($datasources['post_query']);
			}

			if ( 'on' == foogallery_get_setting( 'whitelabelling_hide_woo_products_menu' ) ) {
				unset($datasources['woocommerce']);
			}

			return $datasources;
		}

	}

}