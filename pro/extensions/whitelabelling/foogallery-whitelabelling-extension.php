<?php
/**
 * FooGallery WhiteLabelling Extension
 */
if ( !class_exists('Whitelabelling_FooGallery_Extension') ) {

	define( 'WHITELABELLING_FOOGALLERY_EXTENSION_SLUG', 'foogallery-whitelabelling' );
	class Whitelabelling_FooGallery_Extension {

	    protected $foogallery_instance = null;
	    
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
		    
		    $this->foogallery_instance = FooGallery_Plugin::get_instance();

			//setup text domain
			$this->foogallery_instance->load_plugin_textdomain();

			add_filter( 'foogallery_plugin_name', array( $this, 'plugin_name' ) );
			add_filter( 'foogallery_gallery_shortcode_tag', array( $this, 'shortcode_tag' ) );

			if ( is_admin() ) {

				//extensions
				add_filter( 'foogallery_admin_extensions_tagline', array($this, 'admin_extensions_tagline') );
				add_filter( 'foogallery_extension_categories', array($this, 'alter_extension_categories') );
				add_filter( 'foogallery_extension_api_endpoint', array($this, 'change_extensions_endpoint') );

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
				add_filter( WHITELABELLING_FOOGALLERY_EXTENSION_SLUG . '_admin_settings', array($this, 'create_settings'), 10, 2 );
			}

		}

		function get_setting( $key, $default = '' ) {
			$override = $this->foogallery_instance->options()->get( $key, '' );
			return empty( $override ) ? $default : $override;
		}

		function plugin_name( $default ) {
			return $this->foogallery_instance->options()->get( 'whitelabelling_name', $default );
		}

		function shortcode_tag( $default ) {
			return $this->foogallery_instance->options()->get( 'whitelabelling_shortcode', $default );
		}

		function alter_extension_categories( $categories ) {
			return $categories;
		}

		function change_extensions_endpoint( $default ) {
			$override = $this->foogallery_instance->options()->get( 'whitelabelling_extensions_endpoint', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_help_title( $default ) {
			$override = $this->foogallery_instance->options()->get( 'whitelabelling_help_title', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_help_tagline( $default ) {
			$override = $this->foogallery_instance->options()->get( 'whitelabelling_help_tagline', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_help_tagline_link( $default ) {
			$override = $this->foogallery_instance->options()->get( 'whitelabelling_help_link', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_help_show_extensions_section() {
			if ( 'on' == $this->foogallery_instance->options()->get( 'whitelabelling_hide_extensions' ) ||
				'on' == $this->foogallery_instance->options()->get( 'whitelabelling_extensions_hide_help_section' ) ) {
				return false;
			}

			return true;
		}

		function admin_extensions_tagline( $default ) {
			$override = $this->foogallery_instance->options()->get( 'whitelabelling_extensions_tagline' );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function change_menu_parent_slug( $default ) {
			if ( 'on' == $this->foogallery_instance->options()->get( 'whitelabelling_move_menu_under_media' ) ) {
				return 'upload.php';
			}
			return $default;
		}

		function remove_posttype_menus($args) {
			if ( 'on' == $this->foogallery_instance->options()->get( 'whitelabelling_move_menu_under_media' ) ) {
				$args['show_in_menu'] = false;
			}
			return $args;
		}

		function create_menus() {
			if ( 'on' == $this->foogallery_instance->options()->get( 'whitelabelling_move_menu_under_media' ) ) {
				add_media_page( __( 'Galleries', 'foogallery' ), __( 'Galleries', 'foogallery' ), 'upload_files', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY );
				add_media_page( __( 'Add Gallery', 'foogallery' ), __( 'Add Gallery', 'foogallery' ), 'upload_files', 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY );
			}
		}

		function menu_capability( $default ) {
			$override = $this->foogallery_instance->options()->get( 'whitelabelling_menu_capability' );

			if ( $default != $override && ! empty( $override ) ) {
				return $override;
			}

			return $default;
		}

		function hide_menus() {
			if ( 'on' == $this->foogallery_instance->options()->get( 'whitelabelling_hide_settings_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-settings' );
			}

			if ( 'on' == $this->foogallery_instance->options()->get( 'whitelabelling_hide_extensions' ) ||
				'on' == $this->foogallery_instance->options()->get( 'whitelabelling_hide_extensions_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-extensions' );
			}

			if ( 'on' == $this->foogallery_instance->options()->get( 'whitelabelling_hide_help_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-help' );
			}
		}

		function override_menu_labels( $menu_labels ) {
			$settings_label = $this->foogallery_instance->options()->get( 'whitelabelling_label_settings_menu' );
			$extensions_label = $this->foogallery_instance->options()->get( 'whitelabelling_label_extensions_menu' );
			$help_label = $this->foogallery_instance->options()->get( 'whitelabelling_label_help_menu' );

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

		function create_settings() {

			$tabs['whitelabelling'] = __( 'Whitelabelling', 'foogallery-whitelabelling' );

			$settings[] = array(
		        'id'      => 'whitelabelling_name',
		        'title'   => __('Plugin Name', 'foogallery-whitelabelling'),
		        'desc'    => __('Rename "FooGallery" to something more client friendly, for example "Pro Gallery"', 'foogallery-whitelabelling'),
		        'default' => 'FooGallery',
		        'type'    => 'text',
		        'tab'     => 'whitelabelling'
	        );

			$shortcode = '<code>[' . foogallery_gallery_shortcode_tag() . ']</code>';

			$settings[] = array(
				'id'      => 'whitelabelling_shortcode',
				'title'   => __('Shortcode', 'foogallery-whitelabelling'),
				'desc'    => sprintf( __('Override the default shortcode to something more client friendly, for example "progallery". (please do not include square brackets)<br />The shortcode currently looks like %s.', 'foogallery-whitelabelling'), $shortcode ),
				'default' => 'foogallery',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$tabs['menu'] = __( 'Menu', 'foogallery-whitelabelling' );

			$sections['position'] = array(
				'name' => __( 'Positioning', 'foogallery-whitelabelling' )
			);

			$settings[] = array(
				'id'      => 'whitelabelling_move_menu_under_media',
				'title'   => __('Use Media Menu', 'foogallery-whitelabelling'),
				'desc'    => __('Move all FooGallery menu items under the WordPress media menu.', 'foogallery-whitelabelling'),
				'section' => 'position',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$sections['visibility'] = array(
				'name' => __( 'Visibility', 'foogallery-whitelabelling' )
			);

			$settings[] = array(
				'id'      => 'whitelabelling_menu_capability',
				'title'   => __('Menu Visibility', 'foogallery-whitelabelling'),
				'desc'    => __('Who can see the Help, Extensions and Settings menu items.', 'foogallery-whitelabelling'),
				'section' => 'visibility',
				'type'    => 'select',
				'choices' => array(
					'manage_options' => 'Administrators',
					'delete_others_posts' => 'Editors',
					'publish_posts' => 'Authors',
					'edit_posts' => 'Contributors'
				),
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_hide_settings_menu',
				'title'   => __('Hide Settings Menu', 'foogallery-whitelabelling'),
				'desc'    => __('Hide the settings menu item.', 'foogallery-whitelabelling'),
				'section' => 'visibility',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_hide_extensions_menu',
				'title'   => __('Hide Extensions Menu', 'foogallery-whitelabelling'),
				'desc'    => __('Hide the extension menu item.', 'foogallery-whitelabelling'),
				'section' => 'visibility',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_hide_help_menu',
				'title'   => __('Hide Help Menu', 'foogallery-whitelabelling'),
				'desc'    => __('Hide the help menu item.', 'foogallery-whitelabelling'),
				'section' => 'visibility',
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$sections['labels'] = array(
				'name' => __( 'Labels', 'foogallery-whitelabelling' )
			);

			$settings[] = array(
				'id'      => 'whitelabelling_label_settings_menu',
				'title'   => __('Setting Menu Label', 'foogallery-whitelabelling'),
				'desc'    => __('Change the settings menu text.', 'foogallery-whitelabelling'),
				'section' => 'labels',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_label_extensions_menu',
				'title'   => __('Extension Menu Label', 'foogallery-whitelabelling'),
				'desc'    => __('Change the extensions menu text.', 'foogallery-whitelabelling'),
				'section' => 'labels',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_label_help_menu',
				'title'   => __('Help Menu Label', 'foogallery-whitelabelling'),
				'desc'    => __('Change the help menu text.', 'foogallery-whitelabelling'),
				'section' => 'labels',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$tabs['extensions'] = __( 'Extensions', 'foogallery-whitelabelling' );

			$sections['help_page'] = array(
				'name' => __( 'Help Page', 'foogallery-whitelabelling' )
			);

			$sections['extensions_page'] = array(
				'name' => __( 'Extensions Page', 'foogallery-whitelabelling' )
			);

			$extensions_link = '<br /><a href="' . foogallery_admin_extensions_url() . '">' . __('Click here to access the extensions page', 'foogallery-whitelabelling') . '</a>';

			$settings[] = array(
				'id'      => 'whitelabelling_hide_extensions',
				'title'   => __('Hide Extensions', 'foogallery-whitelabelling'),
				'desc'    => sprintf( __('Hide everything related to extensions from all users. (This will override other settings)%s', 'foogallery-whitelabelling'), $extensions_link ),
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_extensions_tagline',
				'title'   => __('Page Tagline', 'foogallery-whitelabelling'),
				'desc'    => __('Change the tagline paragraph of the FooGallery extensions page. The tagline is directly underneath the page title.', 'foogallery-whitelabelling'),
				'section' => 'extensions_page',
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$tabs['help'] = __( 'Help Page', 'foogallery-whitelabelling' );

			$settings[] = array(
				'id'      => 'whitelabelling_help_title',
				'title'   => __('Page title', 'foogallery-whitelabelling'),
				'desc'    => __('Change the title of the FooGallery help page.', 'foogallery-whitelabelling'),
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_help_tagline',
				'title'   => __('Page Tagline', 'foogallery-whitelabelling'),
				'desc'    => __('Change the tagline paragraph of the FooGallery help page. The tagline is directly underneath the page title.', 'foogallery-whitelabelling'),
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_help_link',
				'title'   => __('Page Tagline Link', 'foogallery-whitelabelling'),
				'desc'    => __('Change the link that is displayed at the end of the tagline paragraph on the FooGallery help page. You can use HTML.', 'foogallery-whitelabelling'),
				'type'    => 'text',
				'tab'     => 'whitelabelling'
			);

			$settings[] = array(
				'id'      => 'whitelabelling_help_hide_tabs',
				'title'   => __('Hide Tabs', 'foogallery-whitelabelling'),
				'desc'    => __('Hide the tabs on the FooGallery help page.', 'foogallery-whitelabelling'),
				'type'    => 'checkbox',
				'tab'     => 'whitelabelling'
			);

			return array(
				'tabs' => $tabs,
				'sections' => $sections,
				'settings' => $settings
			);
		}

	}
}