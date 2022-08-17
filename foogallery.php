<?php
/*
Plugin Name: FooGallery
Description: FooGallery is the most intuitive and extensible gallery management tool ever created for WordPress
Version:     2.2.16
Author:      FooPlugins
Plugin URI:  https://fooplugins.com/foogallery-wordpress-gallery-plugin/
Author URI:  https://fooplugins.com
Text Domain: foogallery
License:     GPL-2.0+
Domain Path: /languages

@fs_premium_only /pro/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( function_exists( 'foogallery_fs' ) ) {
	foogallery_fs()->set_basename( true, __FILE__ );
} else {

	if ( ! class_exists( 'FooGallery_Plugin' ) ) {

		define( 'FOOGALLERY_SLUG', 'foogallery' );
		define( 'FOOGALLERY_PATH', plugin_dir_path( __FILE__ ) );
		define( 'FOOGALLERY_URL', plugin_dir_url( __FILE__ ) );
		define( 'FOOGALLERY_FILE', __FILE__ );
		define( 'FOOGALLERY_VERSION', '2.2.16' );
		define( 'FOOGALLERY_SETTINGS_VERSION', '2' );

		require_once FOOGALLERY_PATH . 'includes/constants.php';

		// Create a helper function for easy SDK access.
		function foogallery_fs() {
			global $foogallery_fs;

			if ( ! isset( $foogallery_fs ) ) {
				// Include Freemius SDK.
				require_once dirname( __FILE__ ) . '/freemius/start.php';

				$foogallery_fs = fs_dynamic_init(
					array(
						'id'             => '843',
						'slug'           => 'foogallery',
						'type'           => 'plugin',
						'public_key'     => 'pk_d87616455a835af1d0658699d0192',
						'is_premium'     => true,
						'has_paid_plans' => true,
						'trial'          => array(
							'days'               => 7,
							'is_require_payment' => false,
						),
						'menu'           => array(
							'slug'       => 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY,
							'first-path' => 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY . '&page=' . FOOGALLERY_ADMIN_MENU_HELP_SLUG,
							'account'    => true,
							'contact'    => false,
							'support'    => false,
						),
					)
				);
			}

			return $foogallery_fs;
		}

		// Init Freemius.
		foogallery_fs();

		// Signal that SDK was initiated.
		do_action( 'foogallery_fs_loaded' );


		require_once FOOGALLERY_PATH . 'includes/foopluginbase/bootstrapper.php';

		/**
		 * FooGallery_Plugin class
		 *
		 * @package   FooGallery
		 * @author    Brad Vincent <brad@fooplugins.com>
		 * @license   GPL-2.0+
		 * @link      https://github.com/fooplugins/foogallery
		 * @copyright 2013 FooPlugins LLC
		 */
		class FooGallery_Plugin extends Foo_Plugin_Base_v2_4 {

			private static $instance;

			public static function get_instance() {
				if ( ! isset( self::$instance ) && ! ( self::$instance instanceof FooGallery_Plugin ) ) {
					self::$instance = new FooGallery_Plugin();
				}

				return self::$instance;
			}

			/**
			 * Initialize the plugin by setting localization, filters, and administration functions.
			 */
			private function __construct() {

				// include everything we need!
				require_once FOOGALLERY_PATH . 'includes/includes.php';

				register_activation_hook( __FILE__, array( 'FooGallery_Plugin', 'activate' ) );

				// init FooPluginBase.
				$this->init( FOOGALLERY_FILE, FOOGALLERY_SLUG, FOOGALLERY_VERSION, 'FooGallery' );

				// load text domain.
				$this->load_plugin_textdomain();

				// setup gallery post type.
				new FooGallery_PostTypes();

				// load any extensions.
				new FooGallery_Extensions_Loader();

                // Load any bundled extension initializers.
                new FooGallery_Import_Export_Extension();

				if ( is_admin() ) {
					new FooGallery_Admin();
					add_action( 'wpmu_new_blog', array( $this, 'set_default_extensions_for_multisite_network_activated' ) );
					foogallery_fs()->add_filter( 'connect_message_on_update', array( $this, 'override_connect_message_on_update' ), 10, 6 );
					foogallery_fs()->add_filter( 'is_submenu_visible', array( $this, 'is_submenu_visible' ), 10, 2 );
					foogallery_fs()->add_filter( 'plugin_icon', array( $this, 'freemius_plugin_icon' ), 10, 1 );
					add_action( 'foogallery_admin_menu_before', array( $this, 'add_freemius_activation_menu' ) );
				} else {
					new FooGallery_Public();
				}

				// initialize the thumbnail manager.
				new FooGallery_Thumb_Manager();

				new FooGallery_Shortcodes();

				new FooGallery_Thumbnails();

				new FooGallery_Attachment_Filters();

				new FooGallery_Retina();

				new FooGallery_Animated_Gif_Support();

				new FooGallery_Cache();

				new FooGallery_Common_Fields();

				new FooGallery_LazyLoad();

				new FooGallery_Paging();

				new FooGallery_Thumbnail_Dimensions();

				new FooGallery_Attachment_Custom_Class();

				new FooGallery_Compatibility();

				new FooGallery_Extensions_Compatibility();

				new FooGallery_Crop_Position();

				new FooGallery_ForceHttps();

				new FooGallery_Debug();

				$checker = new FooGallery_Version_Check();
				$checker->wire_up_checker();

				new FooGallery_Widget_Init();

				// include the default templates no matter what!
				new FooGallery_Default_Templates();

				// init the default media library datasource.
				new FooGallery_Datasource_MediaLibrary();

				$pro_code_included = false;

				if ( foogallery_fs()->is__premium_only() ) {
					if ( foogallery_fs()->can_use_premium_code() ) {
						require_once FOOGALLERY_PATH . 'pro/foogallery-pro.php';

						new FooGallery_Pro();

						$pro_code_included = true;
					}
				}

				if ( ! $pro_code_included ) {
					add_filter( 'foogallery_extensions_for_view', array( $this, 'add_foogallery_pro_extension' ) );
				}

				// init Gutenberg!
				new FooGallery_Gutenberg();

				// init advanced settings.
				new FooGallery_Advanced_Gallery_Settings();

				// init localization for FooGallery.
				new FooGallery_il8n();
			}

			function add_foogallery_pro_extension( $extensions ) {

				$extension = array(
					'slug'            => 'foogallery-pro',
					'class'           => 'FooGallery_Pro',
					'categories'      => array( 'Featured', 'Premium' ),
					'title'           => 'FooGallery Pro',
					'description'     => 'The best gallery plugin for WordPress just got even better!',
					'price'           => '$49',
					'author'          => 'FooPlugins',
					'author_url'      => 'http://fooplugins.com',
					'thumbnail'       => 'https://s3.amazonaws.com/foogallery/extensions/foogallerypro.png',
					'tags'            => array( 'premium' ),
					'source'          => 'fooplugins',
					'download_button' => array(
						'text'    => 'Start FREE Trial',
						'target'  => '_self',
						'href'    => foogallery_fs()->checkout_url( WP_FS__PERIOD_ANNUALLY, true ),
						'confirm' => false,
					),
				);

				array_unshift( $extensions, $extension );

				return $extensions;
			}

			/**
			 *
			 */
			function override_connect_message_on_update( $original, $first_name, $plugin_name, $login, $link, $freemius_link ) {

				return
					sprintf( __( 'Hey %s', 'foogallery' ), $first_name ) . '<br>' .
					sprintf(
						__( '<h2>Thank you for updating to %1$s v%5$s!</h2>Our goal with this update is to make %1$s the best gallery plugin for WordPress, but we need your help!<br><br>We have introduced this opt-in so that you can help us improve %1$s by simply clicking <strong>Allow &amp; Continue</strong>.<br><br>If you opt-in, some data about your usage of %1$s will be sent to %4$s. If you skip this, that\'s okay! %1$s will still work just fine.', 'foogallery' ),
						'<b>' . $plugin_name . '</b>',
						'<b>' . $login . '</b>',
						$link,
						$freemius_link,
						FOOGALLERY_VERSION
					);
			}

			function add_freemius_activation_menu() {
				global $foogallery_fs;

				$parent_slug = foogallery_admin_menu_parent_slug();

				if ( ! $foogallery_fs->is_registered() ) {
					add_submenu_page(
						$parent_slug,
						__( 'FooGallery Opt-In', 'foogallery' ),
						__( 'Activation', 'foogallery' ),
						'manage_options',
						'foogallery-optin',
						array( $foogallery_fs, '_connect_page_render' )
					);
				}
			}

			function is_submenu_visible( $visible, $id ) {
				if ( 'addons' === $id ) {
					//hide addons submenu for now
					$visible = false;
				}

				return $visible;
			}

			/**
			 * Set Freemius plugin icon.
			 *
			 * @return string
			 */
			public function freemius_plugin_icon( $icon ) {
				return FOOGALLERY_PATH . 'assets/foogallery.jpg';
			}

			/**
			 * Set default extensions when a new site is created in multisite and FooGallery is network activated
			 *
			 * @since 1.2.5
			 *
			 * @param int $blog_id The ID of the newly created site
			 */
			public function set_default_extensions_for_multisite_network_activated( $blog_id ) {
				switch_to_blog( $blog_id );

				if ( false === get_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, false ) ) {
					$api = new FooGallery_Extensions_API();

					$api->auto_activate_extensions();

					update_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, true );
				}

				restore_current_blog();
			}

			/**
			 * Fired when the plugin is activated.
			 *
			 * @since    1.0.0
			 *
			 * @param    boolean $network_wide       True if WPMU superadmin uses
			 *                                       "Network Activate" action, false if
			 *                                       WPMU is disabled or plugin is
			 *                                       activated on an individual blog.
			 */
			public static function activate( $network_wide ) {
				if ( function_exists( 'is_multisite' ) && is_multisite() ) {

					if ( $network_wide ) {

						// Get all blog ids
						$blog_ids = self::get_blog_ids();
						if ( is_array( $blog_ids ) ) {
							foreach ( $blog_ids as $blog_id ) {

								switch_to_blog( $blog_id );
								self::single_activate();
							}

							restore_current_blog();
						}

					} else {
						self::single_activate();
					}

				} else {
					self::single_activate( false );
				}
			}

			/**
			 * Fired for each blog when the plugin is activated.
			 *
			 * @since    1.0.0
			 */
			private static function single_activate( $multisite = true ) {
				if ( false === get_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, false ) ) {
					$api = new FooGallery_Extensions_API();

					$api->auto_activate_extensions();

					update_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, true );
				}
				if ( false === $multisite ) {
					//Make sure we redirect to the welcome page
					set_transient( FOOGALLERY_ACTIVATION_REDIRECT_TRANSIENT_KEY, true, 30 );
				}

				//force a version check on activation to make sure housekeeping is performed
				foogallery_perform_version_check();
			}

			/**
			 * Get all blog ids of blogs in the current network that are:
			 * - not archived
			 * - not spam
			 * - not deleted
			 *
			 * @since    1.0.0
			 *
			 * @return   array|false    The blog ids, false if no matches.
			 */
			private static function get_blog_ids() {

				if ( function_exists( 'get_sites' ) ) {

					$sites    = get_sites();
					$blog_ids = array();
					foreach ( $sites as $site ) {
						$blog_ids[] = $site->blog_id;
					}

					return $blog_ids;
				} else {
					//pre WP 3.7 - do this the old way!
					global $wpdb;

					// get an array of blog ids
					$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";

					return $wpdb->get_col( $sql );
				}
			}
		}
	}

	FooGallery_Plugin::get_instance();
}

// Generate image edit modal on gallery creation
add_action( 'wp_ajax_open_foogallery_image_edit_modal', 'open_foogallery_image_edit_modal_ajax' );
function open_foogallery_image_edit_modal_ajax() {
	global $wpdb;
	ob_start();
	$img_id = $_POST['img_id'];
	$img_post = get_post( $img_id );
	$image_attributes = wp_get_attachment_image_src( $img_id );
	$title = $img_post->post_title;
	$caption = $img_post->post_excerpt;
	$description = $img_post->post_content;
	$file_url = get_the_guid( $img_id );
	$image_alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
	$custom_url = get_post_meta( $img_id, '_foogallery_custom_url', true );
	$custom_target = ( get_post_meta( $img_id, '_foogallery_custom_target', true ) ? get_post_meta( $img_id, '_foogallery_custom_target', true ) : 'default' );
	$custom_class = get_post_meta( $img_id, '_foogallery_custom_class', true );
	?>
	<div class="foogallery-image-edit-main">
		<div class="foogallery-image-edit-view">
		<?php if ( $image_attributes ) : ?>
			<img src="<?php echo $image_attributes[0]; ?>" width="<?php echo $image_attributes[1]; ?>" height="<?php echo $image_attributes[2]; ?>" />
		<?php endif; ?>
		</div>
		<div class="foogallery-image-edit-button">
		<input type="button" id="imgedit-open-btn-<?php echo $img_id; ?>" onclick='imageEdit.open( <?php echo $img_id; ?>, "627a22308f" )' class="button" value="Edit Image">
		</div>
	</div>
	<div class="foogallery-image-edit-meta">
		<div class="tabset">
			<!-- Tab 1 -->
			<input type="radio" name="tabset" id="foogallery-tab-main" aria-controls="foogallery-panel-main" checked>
			<label for="foogallery-tab-main">Main</label>
			<!-- Tab 2 -->
			<input type="radio" name="tabset" id="foogallery-tab-taxonomies" aria-controls="foogallery-panel-taxonomies">
			<label for="foogallery-tab-taxonomies">Taxonomies</label>
			<!-- Tab 3 -->
			<input type="radio" name="tabset" id="foogallery-tab-thumbnails" aria-controls="foogallery-panel-thumbnails">
			<label for="foogallery-tab-thumbnails">Thumbnails</label>
			<!-- Tab 4 -->
			<input type="radio" name="tabset" id="foogallery-tab-watermark" aria-controls="foogallery-panel-watermark">
			<label for="foogallery-tab-watermark">Watermark</label>
			<!-- Tab 5 -->
			<input type="radio" name="tabset" id="foogallery-tab-exif" aria-controls="foogallery-panel-exif">
			<label for="foogallery-tab-exif">EXIF</label>
			<!-- Tab 6 -->
			<input type="radio" name="tabset" id="foogallery-tab-more" aria-controls="foogallery-panel-more">
			<label for="foogallery-tab-more">More</label>
			<!-- Tab 7 -->
			<input type="radio" name="tabset" id="foogallery-tab-info" aria-controls="foogallery-panel-info">
			<label for="foogallery-tab-info">Info</label>
			
			<div class="tab-panels">
				<section id="foogallery-panel-main" class="tab-panel">
					<div class="settings">								
						<span class="setting" data-setting="title">
							<label for="attachment-details-two-column-title" class="name">Title</label>
							<input type="text" id="attachment-details-two-column-title" value="<?php echo $title;?>">
						</span>								
						<span class="setting" data-setting="caption">
							<label for="attachment-details-two-column-caption" class="name">Caption</label>
							<textarea id="attachment-details-two-column-caption"><?php echo $caption;?></textarea>
						</span>
						<span class="setting" data-setting="description">
							<label for="attachment-details-two-column-description" class="name">Description</label>
							<textarea id="attachment-details-two-column-description"><?php echo $description;?></textarea>
						</span>
						<span class="setting has-description" data-setting="alt">
							<label for="attachment-details-two-column-alt-text" class="name">Alternative Text</label>
							<input type="text" id="attachment-details-two-column-alt-text" value="<?php echo $image_alt;?>" aria-describedby="alt-text-description">
						</span>
						<p class="description" id="alt-text-description"><a href="https://www.w3.org/WAI/tutorials/images/decision-tree" target="_blank" rel="noopener">Learn how to describe the purpose of the image<span class="screen-reader-text"> (opens in a new tab)</span></a>. Leave empty if the image is purely decorative.</p>
						<span class="setting" data-setting="url">
							<label for="attachment-details-two-column-copy-link" class="name">File URL:</label>
							<input type="text" class="attachment-details-copy-link" id="attachment-details-two-column-copy-link" value="<?php echo $file_url;?>" readonly="">
							<span class="copy-to-clipboard-container">
								<button type="button" class="button button-small copy-attachment-url" data-clipboard-target="#attachment-details-two-column-copy-link">Copy URL to clipboard</button>
								<span class="success hidden" aria-hidden="true">Copied!</span>
							</span>
						</span>
						<span class="setting" data-setting="custom_url">
							<label for="attachments-foogallery-custom-url" class="name">Custom URL</label>
							<input type="text" id="attachments-foogallery-custom-url" value="<?php echo $custom_url;?>">
						</span>
						<span class="setting" data-setting="custom_target">
							<label for="attachments-foogallery-custom-target" class="name">Custom Class</label>
							<select name="attachments-foogallery-custom-target">
								<option value="default" <?php selected( 'default', $custom_target, true ); ?>>Default</option>
								<option value="_blank" <?php selected( '_blank', $custom_target, true ); ?>>New tab (_blank)</option>
								<option value="_self" <?php selected( '_self', $custom_target, true ); ?>>Same tab (_self)</option>
								<option value="foobox" <?php selected( 'foobox', $custom_target, true ); ?>>FooBox</option>
							</select>
						</span>
						<span class="setting" data-setting="custom_class">
							<label for="attachments-foogallery-custom-class" class="name">Custom Class</label>
							<input type="text" id="attachments-foogallery-custom-class" value="<?php echo $custom_class;?>">
						</span>	
					</div>
				</section>
				<section id="foogallery-panel-taxonomies" class="tab-panel">
					<h2>Taxonomies</h2>
				</section>
				<section id="foogallery-panel-thumbnails" class="tab-panel">
					<h2>Thumbnails</h2>
				</section>
				<section id="foogallery-panel-watermark" class="tab-panel">
					<h2>Watermark</h2>
				</section>
				<section id="foogallery-panel-exif" class="tab-panel">
					<h2>EXIF</h2>
				</section>
				<section id="foogallery-panel-more" class="tab-panel">
					<h2>More</h2>
				</section>
				<section id="foogallery-panel-info" class="tab-panel">
					<h2>Info</h2>
				</section>
			</div>		
		</div>
	</div>
	<?php //echo ob_get_clean();
	wp_die();
}

// Admin modal wrapper for gallery image edit
add_action( 'admin_footer', 'foogallery_image_editor_modal' );
function foogallery_image_editor_modal() { ?>
	<div id="foogallery-image-edit-modal" style="display: none;">
		<div class="media-modal wp-core-ui">
			<div class="media-modal-content">
				<div class="edit-attachment-frame mode-select hide-menu hide-router">
					<div class="edit-media-header">
						<button type="button" class="media-modal-close" onclick="close_foogallery_img_modal();"><span class="media-modal-icon"><span class="screen-reader-text">Close dialog</span></span></button>
					</div>
					<div class="media-frame-title"><h1>Foogallery attachment details</h1></div>
					<div class="media-frame-content">
						<div class="attachment-details save-ready">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php }