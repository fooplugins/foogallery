<?php
/**
 * FooGallery
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2013 FooPlugins LLC
 */


/*
 * TODO
 *
 * Add Gallery column to attachments in media gallery ('add to gallery' link directly from here)
 * Check out post attachments plugin to see how the images are shown for a post
 *
 */

if ( !class_exists( 'FooGallery' ) ) {

	define('FOOGALLERY_PATH', plugin_dir_path( __FILE__ ));

	require_once( FOOGALLERY_PATH . 'includes/constants.php' );
	require_once( FOOGALLERY_PATH . 'includes/Foo_Plugin_Base.php' );
    require_once( FOOGALLERY_PATH . 'includes/functions.php' );
    require_once( FOOGALLERY_PATH . 'includes/admin_settings.php' );
    require_once( FOOGALLERY_PATH . 'includes/metaboxes.php' );
    require_once( FOOGALLERY_PATH . 'includes/FooGallery_Template_Engine.php' );
	require_once( FOOGALLERY_PATH . 'includes/shortcodes.php' );
	require_once( FOOGALLERY_PATH . 'includes/templates.php' );

    require_once( FOOGALLERY_PATH . 'includes/classes/album.php' );
    require_once( FOOGALLERY_PATH . 'includes/classes/gallery.php' );
    require_once( FOOGALLERY_PATH . 'includes/classes/media.php' );


	/**
	 * FooGallery class.
	 *
	 * @package FooGallery
	 * @author  Brad Vincent <brad@fooplugins.com>
	 */
	class FooGallery extends Foo_Plugin_Base_v1_1 {

		/**
		 * Initialize the plugin by setting localization, filters, and administration functions.
		 */
		function __construct($file) {
			$this->init(  $file, 'foogallery', '1.0.0', 'FooGallery' );
            add_action( 'init', array($this, 'init_foogallery') );
			new FooGallery_Metaboxes(__FILE__);
			new FooGallery_Shortcodes();
		}

        function init_foogallery() {
            add_action( 'foogallery-admin_create_settings', array('FooGallery_Settings', 'create_settings'), 10, 2 );
			add_filter( 'manage_edit-' . FOOGALLERY_CPT_GALLERY . '_columns', array($this, 'gallery_custom_columns') );
			add_action( 'manage_posts_custom_column', array($this, 'gallery_custom_column_content' ));

//			add_filter( 'media_upload_tabs', array($this, 'add_media_manager_tab') );
//			add_action( 'media_upload_foo_gallery', array($this, 'media_manager_iframe_content') );
//			add_filter( 'media_view_strings', array($this, 'custom_media_string'), 10, 2);

            $this->register_post_types();
            $this->register_taxonomies();

			if (is_admin()) {

				add_action( 'admin_menu', array($this, 'register_menu_items') );

				add_filter( 'manage_upload_columns', array($this, 'setup_media_columns') );

				add_action( 'manage_media_custom_column', array($this, 'media_columns_content'), 10, 2 );
			}
        }

		function build_template_list() {

			$default_templates = array(
				'default' => array(
					'name' => 'Default'
				)
			);

			return apply_filters('foogallery-template_list', $default_templates);
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

        function register_post_types() {
            register_post_type(FOOGALLERY_CPT_GALLERY, array(
                'labels' => array(
                    'name' => __('Galleries', 'foogallery'),
                    'singular_name' => __('Gallery', 'foogallery'),
                    'add_new' => __('Add Gallery', 'foogallery'),
                    'add_new_item' => __('Add New Gallery', 'foogallery'),
                    'edit_item' => __('Edit Gallery', 'foogallery'),
                    'new_item' => __('New Gallery', 'foogallery'),
                    'view_item' => __('View Gallery', 'foogallery'),
                    'search_items' => __('Search Galleries', 'foogallery'),
                    'not_found' => __('No Galleries found', 'foogallery'),
                    'not_found_in_trash' => __('No Galleries found in Trash', 'foogallery'),
                    'menu_name' => __('FooGallery', 'foogallery'),
					'all_items' => __('Galleries', 'foogallery' )
                ),
                'hierarchical' => false,
                'public' => false,
                'rewrite' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'supports' => array('title', 'thumbnail')
            ));
        }

        function register_taxonomies() {
            $labels = array(
                'name'              => __( 'Albums', 'foogallery' ),
                'singular_name'     => __( 'Album', 'foogallery' ),
                'search_items'      => __( 'Search Albums', 'foogallery' ),
                'all_items'         => __( 'All Albums', 'foogallery' ),
                'parent_item'       => __( 'Parent Album', 'foogallery' ),
                'parent_item_colon' => __( 'Parent Album:', 'foogallery' ),
                'edit_item'         => __( 'Edit Album', 'foogallery' ),
                'update_item'       => __( 'Update Album', 'foogallery' ),
                'add_new_item'      => __( 'Add New Album', 'foogallery' ),
                'new_item_name'     => __( 'New Album Name', 'foogallery' ),
                'menu_name'         => __( 'Albums', 'foogallery' ),
            );

            $args = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'album' ),
            );

            register_taxonomy( FOOGALLERY_TAX_ALBUM, array( FOOGALLERY_CPT_GALLERY ), $args );
        }

		function register_menu_items() {

			add_media_page( __('Galleries', 'foogallery'), __('Galleries', 'foogallery'), 'upload_files', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY);

			add_media_page( __('Add Gallery', 'foogallery'), __('Add Gallery', 'foogallery'), 'upload_files', 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY);

		}

		function setup_media_columns( $columns ) {

			$columns['_galleries'] = __('Galleries', 'foogallery');

			return $columns;

		}

		function media_columns_content( $column_name, $post_id ) {

		}

		function gallery_custom_columns($columns) {
			$new_columns = array(
				FOOGALLERY_CPT_GALLERY . '_count' => __('Images', 'foogallery')
			);
			return array_merge($columns, $new_columns);
		}

		function gallery_custom_column_content($column) {
            global $post;

            if ( $column == FOOGALLERY_CPT_GALLERY . '_count' ) {
				$gallery = FooGallery_Gallery::get($post);
				echo sizeof( $gallery->attachments(false) );
            }
		}

		/**
		 * Fired when the plugin is activated.
		 *
		 * @param    boolean $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
		 */
		public static function activate($network_wide) {
			// TODO: create a sample gallery with some images
		}

		/**
		 * Fired when the plugin is deactivated.
		 *
		 * @param    boolean $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
		 */
		public static function deactivate($network_wide) {
			//don't do anything on deactivate
		}
	}
}