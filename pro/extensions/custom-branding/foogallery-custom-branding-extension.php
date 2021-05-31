<?php
/**
 * FooGallery Custom Branding Extension
 *
 * Rename FooGallery to whatever you like for your clients.
 *
 * @package   Custom_BrandingFooGallery_Extension
 * @author    Brad Vincent
 * @license   GPL-2.0+
 * @link      http://fooplugins.com
 * @copyright 2014 Brad Vincent
 *
 * @wordpress-plugin
 * Plugin Name: FooGallery - Custom Branding
 * Description: Rebrand FooGallery to whatever you like for your clients. Ideal for freelancers and agencies.
 * Version:     1.0.1
 * Author:      Brad Vincent
 * Author URI:  http://fooplugins.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

if ( !class_exists( 'Custom_Branding_FooGallery_Extension' ) ) {

	define( 'CUSTOM_BRANDING_FOOGALLERY_EXTENSION_SLUG', 'foogallery-custom-branding' );
	define( 'CUSTOM_BRANDING_FOOGALLERY_EXTENSION_FILE', __FILE__);
	define( 'CUSTOM_BRANDING_FOOGALLERY_EXTENSION_PATH', plugin_dir_path( __FILE__ ) );
	define( 'CUSTOM_BRANDING_FOOGALLERY_EXTENSION_URL', plugin_dir_url( __FILE__ ) );
	define( 'CUSTOM_BRANDING_FOOGALLERY_EXTENSION_VERSION', '1.0.1' );
	define( 'CUSTOM_BRANDING_FOOGALLERY_EXTENSION_UPDATE_URL', 'http://fooplugins.com/api/foogallery-branding/check' );
	define( 'CUSTOM_BRANDING_FOOGALLERY_EXTENSION_FOOGALLERY_MIN_VERSION', '1.1.8' );

	require_once( CUSTOM_BRANDING_FOOGALLERY_EXTENSION_PATH . 'foogallery-custom-branding-init.php' );
	require_once( CUSTOM_BRANDING_FOOGALLERY_EXTENSION_PATH . 'includes/foopluginbase/bootstrapper.php' );
	require_once( CUSTOM_BRANDING_FOOGALLERY_EXTENSION_PATH . 'includes/foolic.php' );

	class Custom_Branding_FooGallery_Extension extends Foo_Plugin_Base_v2_3 {

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			//init FooPluginBase
			$this->init( __FILE__, CUSTOM_BRANDING_FOOGALLERY_EXTENSION_SLUG, CUSTOM_BRANDING_FOOGALLERY_EXTENSION_VERSION, 'FooGallery Custom Branding' );

			//setup text domain
			$this->load_plugin_textdomain();

			add_filter( 'foogallery_plugin_name', array( $this, 'plugin_name' ) );
			add_filter( 'foogallery_gallery_shortcode_tag', array( $this, 'shortcode_tag' ) );

			if ( is_admin() ) {
				add_filter( 'foogallery_admin_help_title', array($this, 'admin_help_title') );
				add_filter( 'foogallery_admin_help_tagline', array($this, 'admin_help_tagline') );
				add_filter( 'foogallery_admin_help_tagline_link', array($this, 'admin_help_tagline_link') );
				add_filter( 'foogallery_admin_show_foobot', array($this, 'admin_show_foobot') );
				add_filter( 'foogallery_admin_help_show_tabs', array($this, 'admin_help_show_tabs') );
				add_filter( 'foogallery_admin_help_show_extensions_section', array($this, 'admin_help_show_extensions_section') );

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
				add_filter( 'foogallery-custom-branding_admin_settings', array($this, 'create_settings'), 10, 2 );
				add_action( 'foogallery-custom-branding_admin_settings_custom_type_render_setting', array($this, 'import_export_settings') );

				//FooGallery version check
				add_action( 'admin_notices', array($this, 'foogallery_version_check') );
			}

			Custom_Branding_FooGallery_Extension_Fooplugins::get_instance();
		}

		function get_setting( $key, $default = '' ) {
			$override = $this->options()->get( $key, '' );
			return empty( $override ) ? $default : $override;
		}

		function plugin_name( $default ) {
			return $this->get_setting( 'custom_branding_name', $default );
		}

		function shortcode_tag( $default ) {
			return $this->get_setting( 'custom_branding_shortcode', $default );
		}

		function alter_extension_categories( $categories ) {
			if ( 'on' == $this->get_setting( 'custom_branding_extensions_hide_build_your_own' ) ) {
				unset( $categories['build_your_own'] );
			}
			return $categories;
		}

		function change_extensions_endpoint( $default ) {
			$override = $this->get_setting( 'custom_branding_extensions_endpoint', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_help_title( $default ) {
			$override = $this->get_setting( 'custom_branding_help_title', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_help_tagline( $default ) {
			$override = $this->get_setting( 'custom_branding_help_tagline', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_help_tagline_link( $default ) {
			$override = $this->get_setting( 'custom_branding_help_link', $default );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function admin_show_foobot() {
			if ( 'on' == $this->get_setting( 'custom_branding_hide_foobot' ) ) {
				return false;
			}
			return true;
		}

		function admin_help_show_tabs() {
			if ( 'on' == $this->get_setting( 'custom_branding_help_hide_tabs' ) ) {
				return false;
			}
			return true;
		}

		function admin_help_show_extensions_section() {
			if ( 'on' == $this->get_setting( 'custom_branding_hide_extensions' ) ||
				'on' == $this->get_setting( 'custom_branding_extensions_hide_help_section' ) ) {
				return false;
			}

			return true;
		}

		function admin_extensions_tagline( $default ) {
			$override = $this->get_setting( 'custom_branding_extensions_tagline' );
			if ( $override != $default && ! empty( $override ) ) {
				return $override;
			}
			return $default;
		}

		function change_menu_parent_slug( $default ) {
			if ( 'on' == $this->get_setting( 'custom_branding_move_menu_under_media' ) ) {
				return 'upload.php';
			}
			return $default;
		}

		function remove_posttype_menus($args) {
			if ( 'on' == $this->get_setting( 'custom_branding_move_menu_under_media' ) ) {
				$args['show_in_menu'] = false;
			}
			return $args;
		}

		function create_menus() {
			if ( 'on' == $this->get_setting( 'custom_branding_move_menu_under_media' ) ) {
				add_media_page( __( 'Galleries', 'foogallery' ), __( 'Galleries', 'foogallery' ), 'upload_files', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY );
				add_media_page( __( 'Add Gallery', 'foogallery' ), __( 'Add Gallery', 'foogallery' ), 'upload_files', 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY );
			}
		}

		function menu_capability( $default ) {
			$override = $this->get_setting( 'custom_branding_menu_capability' );

			if ( $default != $override && ! empty( $override ) ) {
				return $override;
			}

			return $default;
		}

		function hide_menus() {
			if ( 'on' == $this->get_setting( 'custom_branding_hide_settings_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-settings' );
			}

			if ( 'on' == $this->get_setting( 'custom_branding_hide_extensions' ) ||
				'on' == $this->get_setting( 'custom_branding_hide_extensions_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-extensions' );
			}

			if ( 'on' == $this->get_setting( 'custom_branding_hide_help_menu' ) ) {
				remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery-help' );
			}
		}

		function override_menu_labels( $menu_labels ) {
			$settings_label = $this->get_setting( 'custom_branding_label_settings_menu' );
			$extensions_label = $this->get_setting( 'custom_branding_label_extensions_menu' );
			$help_label = $this->get_setting( 'custom_branding_label_help_menu' );

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

			$tabs['general'] = __( 'General', 'foogallery-custom-branding' );

			$settings[] = array(
				'id'           => 'license',
				'title'        => __( 'License Key', 'foogallery-custom-branding' ),
				'desc'         => __( 'The license key is used to access automatic updates and support for this plugin.', 'foogallery-custom-branding' ),
				'type'         => 'license',
				'section'      => 'license',
				'tab'          => 'general',
				'setting_name' => 'foogallery-custom-branding-key',
				'update_url'   => CUSTOM_BRANDING_FOOGALLERY_EXTENSION_UPDATE_URL
			);

			$settings[] = array(
		        'id'      => 'custom_branding_name',
		        'title'   => __('Plugin Name', 'foogallery-custom-branding'),
		        'desc'    => __('Rename "FooGallery" to something more client friendly, for example "Pro Gallery"', 'foogallery-custom-branding'),
		        'default' => 'FooGallery',
		        'type'    => 'text',
		        'tab'     => 'general'
	        );

			$shortcode = '<code>[' . foogallery_gallery_shortcode_tag() . ']</code>';

			$settings[] = array(
				'id'      => 'custom_branding_shortcode',
				'title'   => __('Shortcode', 'foogallery-custom-branding'),
				'desc'    => sprintf( __('Override the default shortcode to something more client friendly, for example "progallery". (please do not include square brackets)<br />The shortcode currently looks like %s.', 'foogallery-custom-branding'), $shortcode ),
				'default' => 'foogallery',
				'type'    => 'text',
				'tab'     => 'general'
			);

			$settings[] = array(
				'id'      => 'custom_branding_hide_foobot',
				'title'   => __('Hide FooBot Images', 'foogallery-custom-branding'),
				'desc'    => __('Hide the FooBot images on the help and extension pages.', 'foogallery-custom-branding'),
				'type'    => 'checkbox',
				'tab'     => 'general'
			);

			$tabs['menu'] = __( 'Menu', 'foogallery-custom-branding' );

			$sections['position'] = array(
				'name' => __( 'Positioning', 'foogallery-custom-branding' )
			);

			$settings[] = array(
				'id'      => 'custom_branding_move_menu_under_media',
				'title'   => __('Use Media Menu', 'foogallery-custom-branding'),
				'desc'    => __('Move all FooGallery menu items under the WordPress media menu.', 'foogallery-custom-branding'),
				'section' => 'position',
				'type'    => 'checkbox',
				'tab'     => 'menu'
			);

			$sections['visibility'] = array(
				'name' => __( 'Visibility', 'foogallery-custom-branding' )
			);

			$settings[] = array(
				'id'      => 'custom_branding_menu_capability',
				'title'   => __('Menu Visibility', 'foogallery-custom-branding'),
				'desc'    => __('Who can see the Help, Extensions and Settings menu items.', 'foogallery-custom-branding'),
				'section' => 'visibility',
				'type'    => 'select',
				'choices' => array(
					'manage_options' => 'Administrators',
					'delete_others_posts' => 'Editors',
					'publish_posts' => 'Authors',
					'edit_posts' => 'Contributors'
				),
				'tab'     => 'menu'
			);

			$settings[] = array(
				'id'      => 'custom_branding_hide_settings_menu',
				'title'   => __('Hide Settings Menu', 'foogallery-custom-branding'),
				'desc'    => __('Hide the settings menu item.', 'foogallery-custom-branding'),
				'section' => 'visibility',
				'type'    => 'checkbox',
				'tab'     => 'menu'
			);

			$settings[] = array(
				'id'      => 'custom_branding_hide_extensions_menu',
				'title'   => __('Hide Extensions Menu', 'foogallery-custom-branding'),
				'desc'    => __('Hide the extension menu item.', 'foogallery-custom-branding'),
				'section' => 'visibility',
				'type'    => 'checkbox',
				'tab'     => 'menu'
			);

			$settings[] = array(
				'id'      => 'custom_branding_hide_help_menu',
				'title'   => __('Hide Help Menu', 'foogallery-custom-branding'),
				'desc'    => __('Hide the help menu item.', 'foogallery-custom-branding'),
				'section' => 'visibility',
				'type'    => 'checkbox',
				'tab'     => 'menu'
			);

			$sections['labels'] = array(
				'name' => __( 'Labels', 'foogallery-custom-branding' )
			);

			$settings[] = array(
				'id'      => 'custom_branding_label_settings_menu',
				'title'   => __('Setting Menu Label', 'foogallery-custom-branding'),
				'desc'    => __('Change the settings menu text.', 'foogallery-custom-branding'),
				'section' => 'labels',
				'type'    => 'text',
				'tab'     => 'menu'
			);

			$settings[] = array(
				'id'      => 'custom_branding_label_extensions_menu',
				'title'   => __('Extension Menu Label', 'foogallery-custom-branding'),
				'desc'    => __('Change the extensions menu text.', 'foogallery-custom-branding'),
				'section' => 'labels',
				'type'    => 'text',
				'tab'     => 'menu'
			);

			$settings[] = array(
				'id'      => 'custom_branding_label_help_menu',
				'title'   => __('Help Menu Label', 'foogallery-custom-branding'),
				'desc'    => __('Change the help menu text.', 'foogallery-custom-branding'),
				'section' => 'labels',
				'type'    => 'text',
				'tab'     => 'menu'
			);

			$tabs['extensions'] = __( 'Extensions', 'foogallery-custom-branding' );

			$sections['help_page'] = array(
				'name' => __( 'Help Page', 'foogallery-custom-branding' )
			);

			$sections['extensions_page'] = array(
				'name' => __( 'Extensions Page', 'foogallery-custom-branding' )
			);

			$extensions_link = '<br /><a href="' . foogallery_admin_extensions_url() . '">' . __('Click here to access the extensions page', 'foogallery-custom-branding') . '</a>';

			$settings[] = array(
				'id'      => 'custom_branding_hide_extensions',
				'title'   => __('Hide Extensions', 'foogallery-custom-branding'),
				'desc'    => sprintf( __('Hide everything related to extensions from all users. (This will override other settings)%s', 'foogallery-custom-branding'), $extensions_link ),
				'type'    => 'checkbox',
				'tab'     => 'extensions'
			);

			$extensions_url = '<br />' . __('The default URL is ', 'foogallery-custom-branding') . '<code>' . FOOGALLERY_EXTENSIONS_ENDPOINT . '</code>';

			$settings[] = array(
				'id'      => 'custom_branding_extensions_endpoint',
				'title'   => __('Extensions URL', 'foogallery-custom-branding'),
				'desc'    => __('The list of available extensions are pulled from an external URL. Change this URL to pull your own custom list of extensions.', 'foogallery-custom-branding') . $extensions_url,
				'type'    => 'text',
				'tab'     => 'extensions'
			);

			$settings[] = array(
				'id'      => 'custom_branding_extensions_tagline',
				'title'   => __('Page Tagline', 'foogallery-custom-branding'),
				'desc'    => __('Change the tagline paragraph of the FooGallery extensions page. The tagline is directly underneath the page title.', 'foogallery-custom-branding'),
				'section' => 'extensions_page',
				'type'    => 'text',
				'tab'     => 'extensions'
			);

			$settings[] = array(
				'id'      => 'custom_branding_extensions_hide_build_your_own',
				'title'   => __('Hide "Build Your Own" Tab', 'foogallery-custom-branding'),
				'desc'    => __('Hide the "Build Your Own" tab on the FooGallery extensions page.', 'foogallery-custom-branding'),
				'section' => 'extensions_page',
				'type'    => 'checkbox',
				'tab'     => 'extensions'
			);

			$tabs['help'] = __( 'Help Page', 'foogallery-custom-branding' );

			$settings[] = array(
				'id'      => 'custom_branding_help_title',
				'title'   => __('Page title', 'foogallery-custom-branding'),
				'desc'    => __('Change the title of the FooGallery help page.', 'foogallery-custom-branding'),
				'type'    => 'text',
				'tab'     => 'help'
			);

			$settings[] = array(
				'id'      => 'custom_branding_help_tagline',
				'title'   => __('Page Tagline', 'foogallery-custom-branding'),
				'desc'    => __('Change the tagline paragraph of the FooGallery help page. The tagline is directly underneath the page title.', 'foogallery-custom-branding'),
				'type'    => 'text',
				'tab'     => 'help'
			);

			$settings[] = array(
				'id'      => 'custom_branding_help_link',
				'title'   => __('Page Tagline Link', 'foogallery-custom-branding'),
				'desc'    => __('Change the link that is displayed at the end of the tagline paragraph on the FooGallery help page. You can use HTML.', 'foogallery-custom-branding'),
				'type'    => 'text',
				'tab'     => 'help'
			);

			$settings[] = array(
				'id'      => 'custom_branding_help_hide_tabs',
				'title'   => __('Hide Tabs', 'foogallery-custom-branding'),
				'desc'    => __('Hide the tabs on the FooGallery help page.', 'foogallery-custom-branding'),
				'type'    => 'checkbox',
				'tab'     => 'help'
			);

			$settings[] = array(
				'id'      => 'custom_branding_extensions_hide_help_section',
				'title'   => __('Hide "Extensions" Section', 'foogallery-custom-branding'),
				'desc'    => __('Hide the extensions section on the FooGallery help page.', 'foogallery-custom-branding'),
				'type'    => 'checkbox',
				'tab'     => 'help'
			);

			return array(
				'tabs' => $tabs,
				'sections' => $sections,
				'settings' => $settings
			);
		}

		function import_export_settings( $args ) {
			if ( 'license' === $args['type'] ) {
				$data = apply_filters( 'foolic_get_validation_data-'.$this->plugin_slug, false );
				if ($data === false) return;
				echo $data['html'];
			}
		}

		function foogallery_version_check() {
			if ( current_user_can( 'activate_plugins' ) &&
				version_compare( FOOGALLERY_VERSION, CUSTOM_BRANDING_FOOGALLERY_EXTENSION_FOOGALLERY_MIN_VERSION ) < 0 ) { ?>
				<div class="error">
					<h3><?php _e('FooGallery Custom Branding Error', 'foogallery-custom-branding'); ?></h3>
					<p><?php printf( __('The FooGallery Custom Branding plugin requires at least version %s of FooGallery to function correctly.', 'foogallery-custom-branding'), CUSTOM_BRANDING_FOOGALLERY_EXTENSION_FOOGALLERY_MIN_VERSION ); ?></p>
				</div><?php
			}
		}

	}
}