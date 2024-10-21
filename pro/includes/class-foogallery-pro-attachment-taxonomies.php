<?php
namespace FooPlugins\FooGallery\Pro;

if ( ! class_exists( 'FooGallery_Pro_Attachment_Taxonomies' ) ) {

    class FooGallery_Pro_Attachment_Taxonomies {

    	private $cached_terms = array();

        /**
         * Class Constructor
         */
        function __construct() {
			add_action( 'init', array( $this, 'init_taxonomies' ), 9 );
			add_action( 'init', array( $this, 'init_rest' ), 11 );
			add_action( 'foogallery_admin_settings_override', array( $this, 'add_admin_setting' ) );
        }

		/**
		 * Adds a setting to disable all FooGallery taxonomies
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
        function add_admin_setting($settings) {

			$settings['settings'][] = array(
				'id'      => 'disable_attachment_taxonomies',
				'title'   => __( 'Disable Attachment Taxonomies', 'foogallery' ),
				'desc'    => sprintf( __( 'Disables the %s attachment taxonomies (Media Tags and Media Categories).', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'checkbox',
				'tab'     => 'advanced'
			);

        	return $settings;
		}

		function init_taxonomies() {
			if ( foogallery_get_setting( 'disable_attachment_taxonomies' ) === 'on' ) {
				return;
			}

			$this->add_taxonomies();
		}

		/**
		 * Initialize the rest of the hooks if the taxonomies are not disabled
		 */
        function init_rest() {
			if ( foogallery_get_setting( 'disable_attachment_taxonomies' ) === 'on' ) {
				return;
			}

			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'add_menu_items' ), 1 );
				add_filter( 'parent_file', array( $this, 'set_current_menu' ) );
				add_filter( 'manage_media_columns', array( $this, 'change_attachment_column_names' ) );
				add_filter( 'manage_edit-foogallery_attachment_tag_columns', array( $this, 'clean_column_names' ), 999 );
				add_filter( 'manage_edit-foogallery_attachment_category_columns', array( $this, 'clean_column_names' ), 999 );

				//make the attachment taxonomies awesome
				add_action( 'admin_head', array( $this, 'include_inline_taxonomy_data_script' ) );
				add_filter( 'attachment_fields_to_edit', array( $this, 'add_taxonomy_fields' ), 0, 2 );

				//ajax actions from the media modal
				add_action( 'wp_ajax_foogallery-taxonomies-add-term', array( $this, 'ajax_add_term' ) );
				add_action( 'wp_ajax_foogallery-taxonomies-save-terms', array( $this, 'ajax_save_terms' ) );
			}

			add_action( 'wp_enqueue_media', array( $this, 'enqueue_js' ) );
		}
        /**
         * Save terms for an attachment
         *
         * @since 1.4.19
         */
        public function ajax_save_terms()
        {
            $nonce = $_POST['nonce'];
            if (wp_verify_nonce($nonce, 'foogallery-attachment-taxonomy')) {

                $attachment_id = $_POST['attachment_id'];
                $terms = $_POST['terms'];
                $taxonomy = $_POST['taxonomy'];

                if ( empty( $terms ) ) {

                	//remove all relationships between the object and any terms in a particular taxonomy
	                wp_delete_object_term_relationships( $attachment_id, $taxonomy );

                } else {
	                $result = wp_set_object_terms( $attachment_id, array_map('trim', preg_split('/,+/', $terms)), $taxonomy, false );

	                if ( is_wp_error( $result ) ) {
		                wp_send_json_error( $result );
	                }
                }

                //clear caches
				clean_post_cache($attachment_id);

                //send back success response
	            wp_send_json( $terms );


            }
            die();
        }

        /**
         * Add new term via an ajax call from admin
         *
         * @since 1.4.19
         * @access public
         */
        public function ajax_add_term() {
            $nonce = $_POST['nonce'];
            if (wp_verify_nonce($nonce, 'foogallery-attachment-taxonomy')) {

                $new_term = wp_insert_term($_POST['term_label'], $_POST['taxonomy']);

                if (is_wp_error($new_term)) {
                    die();
                }

                $new_term_obj = null;

                if (isset($new_term['term_id'])) {
                    $new_term_obj = get_term($new_term['term_id']);
                }

                if (!is_wp_error($new_term_obj)) {
                    wp_send_json(array(
                        'new_term' => $new_term_obj,
                        'all_terms' => foogallery_build_terms_recursive($_POST['taxonomy'], array('hide_empty' => false))
                    ));
                }
            }

            die();
        }

		/**
		 * Enqueue admin script and styles
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 */
		public function enqueue_js() {
			//enqueue selectize assets
			wp_enqueue_script( 'foogallery-selectize-core', FOOGALLERY_URL . 'lib/selectize/selectize.min.js', array('jquery'), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery-selectize', FOOGALLERY_URL . 'lib/selectize/foogallery.selectize.js', array('foogallery-selectize-core'), FOOGALLERY_VERSION );
			wp_enqueue_style(  'foogallery-selectize', FOOGALLERY_URL . 'lib/selectize/selectize.css', array(), FOOGALLERY_VERSION );

			//enqueue media attachment autosave script
            wp_enqueue_script( 'foogallery-attachment-autosave', FOOGALLERY_URL . 'js/admin-foogallery-attachment-autosave.js', array('media-views'), FOOGALLERY_VERSION );

			$this->include_inline_taxonomy_data_script();
		}

		/**
		 * Add fields to attachment
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 * @param array $fields An array with all fields to edit
		 * @param object $post An object for the current post
		 * @return array $fields An array with all fields to edit
		 */
		public function add_taxonomy_fields( $fields, $post ) {
            $allowed_taxonomies = array ( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );

            foreach ( get_attachment_taxonomies( $post ) as $taxonomy ) {

                if ( in_array( $taxonomy, $allowed_taxonomies ) ) {

                    $t = (array) get_taxonomy( $taxonomy );

                    $terms = get_object_term_cache( $post->ID, $taxonomy );

                    if ( false === $terms ) {
                        $terms = wp_get_object_terms( $post->ID, $taxonomy, array() );
                    }

                    $values = array();

                    foreach ( $terms as $term ) {
                        $values[] = $term->slug;
                    }

                    $t['value'] = implode( ', ', $values );
                    $t['show_in_edit'] = false;
					$t['input'] = 'html';
                    $t['html'] = $this->build_taxonomy_html( $taxonomy, $post, $t['value'] );
                    $fields[ $taxonomy ] = $t;
                }
            }

			return $fields;
		}

		/**
		 * Add custom js into admin head so that we can build up decent taxonomy selectize controls
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 */
		public function include_inline_taxonomy_data_script() {
			$taxonomy_data[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] = array(
				'slug' => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
				'terms' => foogallery_build_terms_recursive(FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, array('hide_empty' => false)),
				'query_var' => true,
				'labels' => array(
					'placeholder' => __( 'Select tags, or add a new tag...', 'foogallery' ),
					'add' => __( 'Add new tag', 'foogallery' )
				),
			);

			$taxonomy_data[FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY] = array(
				'slug' => FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY,
				'terms' => foogallery_build_terms_recursive(FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, array('hide_empty' => false)),
				'query_var' => true,
				'labels' => array(
					'placeholder' => __( 'Select categories, or add a new category...', 'foogallery' ),
					'add' => __( 'Add new category', 'foogallery' )
				),
			);

			$taxonomy_data['nonce'] = wp_create_nonce( 'foogallery-attachment-taxonomy' );

			wp_add_inline_script( 'foogallery-selectize', 'window.FOOGALLERY_TAXONOMY_DATA = ' . json_encode($taxonomy_data) . ';' );
		}

        function change_attachment_column_names( $columns ) {

             if ( array_key_exists( 'taxonomy-foogallery_attachment_category', $columns ) ) {
                 $columns['taxonomy-foogallery_attachment_category'] = __('Categories', 'foogallery');
             }

             return $columns;
        }

        /**
         * Clean up the taxonomy columns for WP Seo plugin
         *
         * @param $columns
         * @return mixed
         */
        function clean_column_names( $columns ) {

             //cleanup wpseo columns!
             if ( array_key_exists( 'wpseo_score', $columns ) ) {
                 unset( $columns['wpseo_score'] );
             }
            if ( array_key_exists( 'wpseo_score_readability', $columns ) ) {
                unset( $columns['wpseo_score_readability'] );
            }
             return $columns;
        }

        /**
         * Add the menu items under the FooGallery main menu
         */
        function add_menu_items() {
            foogallery_add_submenu_page(
                __( 'Media Tags', 'foogallery' ),
                'manage_options',
                'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '&post_type=' . FOOGALLERY_CPT_GALLERY,
                null
            );

            foogallery_add_submenu_page(
                __( 'Media Categories', 'foogallery' ),
                'manage_options',
                'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY . '&post_type=' . FOOGALLERY_CPT_GALLERY,
                null
            );
        }

        /**
         * Make sure the taxonomy menu items are highlighted
         * @param $parent_file
         * @return mixed
         */
        function set_current_menu( $parent_file ) {
            global $submenu_file, $current_screen;

            if ( $current_screen->post_type == FOOGALLERY_CPT_GALLERY ) {

                if ( 'edit-foogallery_attachment_tag' === $current_screen->id ) {
                    $submenu_file = 'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '&post_type=' . FOOGALLERY_CPT_GALLERY;
                }

                if ( 'edit-foogallery_attachment_category' === $current_screen->id ) {
                    $submenu_file = 'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY . '&post_type=' . FOOGALLERY_CPT_GALLERY;
                }
            }

            return $parent_file;
        }

        /**
         * Register the taxonomies for attachments
         */
        function add_taxonomies() {

            $tag_args = array(
                'labels'            => array(
                    'name'              => __( 'Media Tags', 'foogallery' ),
                    'singular_name'     => __( 'Tag', 'foogallery' ),
                    'search_items'      => __( 'Search Tags', 'foogallery' ),
                    'all_items'         => __( 'All Tags', 'foogallery' ),
                    'parent_item'       => __( 'Parent Tag', 'foogallery' ),
                    'parent_item_colon' => __( 'Parent Tag:', 'foogallery' ),
                    'edit_item'         => __( 'Edit Tag', 'foogallery' ),
                    'update_item'       => __( 'Update Tag', 'foogallery' ),
                    'add_new_item'      => __( 'Add New Tag', 'foogallery' ),
                    'new_item_name'     => __( 'New Tag Name', 'foogallery' ),
                    'menu_name'         => __( 'Media Tags', 'foogallery' )
                ),
                'hierarchical'      => false,
                'query_var'         => true,
                'rewrite'           => false,
                'show_admin_column' => false,
                'show_in_menu'      => false,
                'public'            => false,  /**makes taxonomy not be visible publicly for front-end users.**/
                'show_ui'           => true,    /** allow a UI for managing terms in this taxonomy in the admin **/

                'update_count_callback' => '_update_generic_term_count'
            );

            register_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, 'attachment', $tag_args );

            $category_args = array(
                'labels'            => array(
                    'name'              => __( 'Media Categories', 'foogallery' ),
                    'singular_name'     => __( 'Category', 'foogallery' ),
                    'search_items'      => __( 'Search Categories', 'foogallery' ),
                    'all_items'         => __( 'All Categories', 'foogallery' ),
                    'parent_item'       => __( 'Parent Category', 'foogallery' ),
                    'parent_item_colon' => __( 'Parent Category:', 'foogallery' ),
                    'edit_item'         => __( 'Edit Category', 'foogallery' ),
                    'update_item'       => __( 'Update Category', 'foogallery' ),
                    'add_new_item'      => __( 'Add New Category', 'foogallery' ),
                    'new_item_name'     => __( 'New Category Name', 'foogallery' ),
                    'menu_name'         => __( 'Media Categories', 'foogallery' )
                ),
                'hierarchical'      => true,
                'query_var'         => true,
                'rewrite'           => false,
                'show_admin_column' => true,
                'show_in_menu'      => false,
                'public'            => false,  /**makes taxonomy not be visible publicly for front-end users.**/
                'show_ui'           => true,    /** allow a UI for managing terms in this taxonomy in the admin **/

                'update_count_callback' => '_update_generic_term_count'
            );

            register_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, 'attachment', $category_args );
        }

		/**
		 * Build up a taxonomy field HTML
		 *
		 * @param $taxonomy
		 * @param $post
		 *
		 * @return array
		 */
        function build_taxonomy_html( $taxonomy, $post, $value ) {
			$html = '<input type="text" data-attachment_id="' . $post->ID . '" class="foogallery-attachment-ignore-change foogallery-attachment-selectize" id="attachments-' . $post->ID .'-' . $taxonomy . '" data-taxonomy="' . $taxonomy . '" name="attachments-' . $post->ID .'-' . $taxonomy . '" value="' . $value . '" data-original-value="' . $value . '" />';
//			$html .= '<script type="script/javascript">
//				FOOGALLERY_SELECTIZE(\'#attachments-' . $post->ID .'-' . $taxonomy . '\', \'' . $taxonomy .'\');
//				</script>';
			return $html;
		}
    }
}
