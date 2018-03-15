<?php
if ( ! class_exists( 'FooGallery_Attachment_Taxonomies' ) ) {

    define( 'FOOGALLERY_ATTACHMENT_TAXONOMY_TAG', 'foogallery_attachment_tag' );
    define( 'FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION', 'foogallery_attachment_collection' );

    class FooGallery_Attachment_Taxonomies {

    	private $cached_terms = array();

        /**
         * Class Constructor
         */
        function __construct() {
            add_action( 'init', array( $this, 'add_taxonomies' ) );

            if ( is_admin() ) {
                add_action( 'admin_menu', array( $this, 'add_menu_items' ), 1 );
                add_filter( 'parent_file', array( $this, 'set_current_menu' ) );
                add_filter( 'manage_media_columns', array( $this, 'change_attachment_column_names' ) );
                add_filter( 'manage_edit-foogallery_attachment_tag_columns', array( $this, 'clean_column_names' ), 999 );
                add_filter( 'manage_edit-foogallery_attachment_collection_columns', array( $this, 'clean_column_names' ), 999 );

				//make the attachment taxonomies awesome
				add_action( 'admin_head', array( $this, 'include_inline_taxonomy_data_script' ) );
				add_filter( 'attachment_fields_to_edit', array( $this, 'override_attachment_taxonomy_fields' ), 10, 2 );
                add_filter( 'attachment_fields_to_save', array( $this, 'save_fields' ), 10, 2 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js' ), 99 );

				//ajax actions from the media modal
                add_action( 'wp_ajax_foogallery-taxonomies-add-term', array( $this, 'ajax_add_term' ) );
                add_action( 'wp_ajax_foogallery-taxonomies-save-terms', array( $this, 'ajax_save_terms' ) );

                //add_filter( 'foogallery_attachment_add_fields', array( $this, 'remove_taxonomy_fields') );
                //add_action( 'restrict_manage_posts', array( $this, 'add_collection_filter' ) );

                //add_filter( 'foogallery_attachment_custom_fields_with_post', array( $this, 'add_taxonomy_fields' ), 10, 2 );
                //add_filter( 'foogallery_attachment_field_taxonomy_tag', array( $this, 'customize_media_tag_field'), 10, 2 );
                //add_filter( 'foogallery_attachment_save_field_taxonomy_tag', array( $this, 'save_media_tag_field' ), 10, 4 );
            }
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

                $result = wp_set_object_terms($attachment_id, array_map('trim', preg_split('/,+/', $terms)), $taxonomy, false);
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
                        'all_terms' => $this->build_terms_recursive($_POST['taxonomy'], array('hide_empty' => false))
                    ));
                }
            }

            die();
        }

        public function save_fields( $post, $attachment ) {
            $something = $_POST;

            return $post;
        }

		/**
		 * Enqueue admin script and styles
		 *
		 * @since 1.0.0
		 * @access public
		 * @static
		 */
		public function enqueue_js() {
			global $pagenow, $mode;

			$should_add = wp_script_is('media-views') || ($pagenow === 'upload.php' && $mode === 'grid');

			if( !$should_add ) {
				return;
			}

//			// Enqueue filters script
//			if(Property::$has_filter) {
//				wp_enqueue_script(
//					'f4-media-taxonomies-filter',
//					F4_MT_URL . 'Core/Assets/js/filter.js',
//					array(),
//					false,
//					true
//				);
//			}

			//enqueue selectize assets
			wp_enqueue_script( 'foogallery-selectize-core', FOOGALLERY_URL . 'lib/selectize/selectize.min.js', array('jquery'), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery-selectize', FOOGALLERY_URL . 'lib/selectize/foogallery.selectize.js', array('foogallery-selectize-core'), FOOGALLERY_VERSION );
			wp_enqueue_style(  'foogallery-selectize', FOOGALLERY_URL . 'lib/selectize/selectize.css', array(), FOOGALLERY_VERSION );


			//enqueue media attachment autosave script
            wp_enqueue_script( 'foogallery-attachment-autosave', FOOGALLERY_URL . 'js/admin-foogallery-attachment-autosave.js', ['media-views']);



//wp_enqueue_script(
//				'f4-media-taxonomies-assignment',
//				F4_MT_URL . 'Core/Assets/js/assignment.js',
//				array(),
//				false,
//				true
//			);
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
		public function override_attachment_taxonomy_fields($fields, $post) {
			if ( array_key_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, $fields ) ) {

				$value = trim( $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG]['value'] );

				$fields[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] = array(
					'show_in_edit' => false,
					'input' => 'html',
					'html' => $this->build_taxonomy_html( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, $post, $value ),
					'label' => __( 'Media Tags', 'foogallery' )
				);
			}

			if ( array_key_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, $fields ) ) {

				$value = trim( $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION]['value'] );

				$fields[FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION] = array(
					'show_in_edit' => false,
					'input' => 'html',
					'html' => $this->build_taxonomy_html( FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, $post, $value ),
					'label' => __( 'Media Collections', 'foogallery' )
				);
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
			global $pagenow, $mode;

			$should_add = wp_script_is('media-views') || ($pagenow === 'upload.php' && $mode === 'grid');

			if( !$should_add ) {
				return;
			}

			$taxonomy_data[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] = array(
				'slug' => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
				'terms' => $this->build_terms_recursive(FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, array('hide_empty' => false)),
				'query_var' => true,
				'labels' => array(
					'placeholder' => __( 'Select tags, or add a new tag...', 'foogallery' ),
					'add' => __( 'Add new tag', 'foogallery' )
				),
			);

			$taxonomy_data[FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION] = array(
				'slug' => FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION,
				'terms' => $this->build_terms_recursive(FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, array('hide_empty' => false)),
				'query_var' => true,
				'labels' => array(
					'placeholder' => __( 'Select collections, or add a new collection...', 'foogallery' ),
					'add' => __( 'Add new collection', 'foogallery' )
				),
			);

			$taxonomy_data['nonce'] = wp_create_nonce( 'foogallery-attachment-taxonomy' );

			echo '<script type="text/javascript">
			window.FOOGALLERY_TAXONOMY_DATA = ' . json_encode($taxonomy_data) . ';
		</script>';
		}

        function change_attachment_column_names( $columns ) {

             if ( array_key_exists( 'taxonomy-foogallery_attachment_collection', $columns ) ) {
                 $columns['taxonomy-foogallery_attachment_collection'] = __('Collections', 'foogallery');
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
                __( 'Media Collections', 'foogallery' ),
                'manage_options',
                'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION . '&post_type=' . FOOGALLERY_CPT_GALLERY,
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

                if ( 'edit-foogallery_attachment_collection' === $current_screen->id ) {
                    $submenu_file = 'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION . '&post_type=' . FOOGALLERY_CPT_GALLERY;
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
                'update_count_callback' => '_update_generic_term_count'
            );

            register_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, 'attachment', $tag_args );

            $collection_args = array(
                'labels'            => array(
                    'name'              => __( 'Media Collections', 'foogallery' ),
                    'singular_name'     => __( 'Collection', 'foogallery' ),
                    'search_items'      => __( 'Search Collections', 'foogallery' ),
                    'all_items'         => __( 'All Collections', 'foogallery' ),
                    'parent_item'       => __( 'Parent Collection', 'foogallery' ),
                    'parent_item_colon' => __( 'Parent Collection:', 'foogallery' ),
                    'edit_item'         => __( 'Edit Collection', 'foogallery' ),
                    'update_item'       => __( 'Update Collection', 'foogallery' ),
                    'add_new_item'      => __( 'Add New Collection', 'foogallery' ),
                    'new_item_name'     => __( 'New Collection Name', 'foogallery' ),
                    'menu_name'         => __( 'Media Collections', 'foogallery' )
                ),
                'hierarchical'      => true,
                'query_var'         => true,
                'rewrite'           => false,
                'show_admin_column' => true,
                'show_in_menu'      => false,
                'update_count_callback' => '_update_generic_term_count'
            );

            register_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, 'attachment', $collection_args );
        }

        /**
         * Add the taxonomy fields to the attachment
         *
         * @param $fields array All fields that will be added to the media modal
		 * @param $post
         *
         * @return mixed
         */
        function add_taxonomy_fields( $fields, $post ) {

			$fields[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] = array(
				'show_in_edit' => false,
				'input' => 'html',
				'html' => $this->build_taxonomy_html( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, $post ),
				'label' => __( 'Tags', 'foogallery' )
			);

			$fields[FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION] = array(
				'show_in_edit' => false,
				'input' => 'html',
				'html' => $this->build_taxonomy_html( FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, $post ),
				'label' => __( 'Collections', 'foogallery' )
			);

            return $fields;
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
            //$html = '<input type="hidden" id="foogallery-attachment-nonce_' . $taxonomy . '" value="' . wp_create_nonce( 'foogallery-attachment-nonce_' . $taxonomy ) . '" />';
			$html = '<input type="text" data-attachment_id="' . $post->ID . '" class="foogallery-attachment-ignore-change" id="attachments-' . $post->ID .'-' . $taxonomy . '" name="attachments-' . $post->ID .'-' . $taxonomy . '" value="' . $value . '" />';
			$html .= '<script type="script/javascript">
				FOOGALLERY_SELECTIZE(\'#attachments-' . $post->ID .'-' . $taxonomy . '\', \'' . $taxonomy .'\');
				</script>';
			return $html;
		}

		/**
		 * Get terms sorted by hierarchy in a recursive way
		 *
		 * @param  string $taxonomy The taxonomy name
		 * @param  array $args The arguments which should be passed to the get_terms function
		 * @param  int $parent The terms parent id (for recursive usage)
		 * @param  int $level The current level (for recursive usage)
		 * @param  array $parents An array with all the parent terms (for recursive usage)
		 *
		 * @return array $terms_all An array with all the terms for this taxonomy
		 */
		function build_terms_recursive($taxonomy, $args = array(), $parent = 0, $level = 1, $parents = array()) {
			//check if the taxonomy terms have already been built up
			if ( 0 === $parent && array_key_exists( $taxonomy, $this->cached_terms ) ) {
				return $this->cached_terms[$taxonomy];
			}

			$terms_all = array();

			$args['parent'] = $args['child_of'] = $parent;

			$terms = get_terms($taxonomy, $args);

			foreach($terms as $term) {
				$term->level = $level;
				$term->parents = $parents;
				$term_parents = $parents;
				$term_parents[] = $term->name;
				$terms_all[] = $term;
				$terms_sub = $this->build_terms_recursive($taxonomy, $args, $term->term_id, $level + 1, $term_parents);

				if(!empty($terms_sub)) {
					$terms_all = array_merge($terms_all, $terms_sub);
				}
			}

			//cache what we have built up
			if ( 0 === $parent && !array_key_exists( $taxonomy, $this->cached_terms ) ) {
				$this->cached_terms[$taxonomy] = $terms_all;
			}

			return $terms_all;
		}

        /**
         * Remove the automatically added attachments fields
         * @param $fields
         *
         * @return mixed
         */
        function remove_taxonomy_fields( $fields ) {
            if ( array_key_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, $fields ) ) {
                unset( $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] );
            }

            if ( array_key_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, $fields ) ) {
                unset( $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION] );
            }

            return $fields;
        }

        /**
         * Customize the media tag field to make sure we output a checkboxlist
         * @param $field_values
         *
         * @return mixed
         */
        function customize_media_tag_field( $field_values, $post_id ) {

            $media_tags = array();

            //get the terms linked to the attachment
            $terms = get_the_terms( $post_id, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );
            if ( $terms && ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    $media_tags[ $term->term_id ] = $term->name;
                }
            }

            //set to html
            $field_values['input'] = 'html';

            $html = '';
            $i = 0;

            if ( ! empty( $field_values['options'] ) ) {

                foreach ( $field_values['options'] as $k => $v ) {
                    if ( array_key_exists( $k, $media_tags ) ) {
                        $checked = ' checked="checked"';
                    } else {
                        $checked = '';
                    }

                    $html .= '<input' . $checked . ' value="' . $k . '" type="checkbox" name="attachments[' . $post_id . '][' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '][' . $k . ']" id="' . sanitize_key( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '_' . $post_id . '_' . $i ) . '" /> <label for="' . sanitize_key( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '_' . $post_id . '_' . $i ) . '">' . $v . '</label> ';
                    $i++;
                }
            }

            if ( 0 === $i ) {
                $html .= __( 'No Tags Available!', 'foogallery' );
            }

            $html .= '<style>.compat-field-foogallery_media_tags .field input {margin-right: 0px;} .compat-field-foogallery_media_tags .field label {vertical-align: bottom; margin-right: 10px;}</style>';

            $html .= '<br /><a target="_blank" href="' . admin_url( 'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '&post_type=attachment' ) . '">' . __( 'Manage Tags', 'foogallery' ) . '</a>';

            $field_values['value'] = '';
            $field_values['html'] = $html;

            return $field_values;
        }

        /**
         * Save the tags for the attachment
         *
         * @param $field
         * @param $values
         * @param $post
         * @param $attachment
         */
        function save_media_tag_field($field, $values, $post, $attachment) {
            $post_id = $post['ID'];

            //first clear any tags for the post
            wp_delete_object_term_relationships( $post_id, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );

            $tag_ids = $attachment[ $field ];

            if ( !empty( $tag_ids ) ) {
                //clean tag ids
                $tag_ids = array_keys( $tag_ids );
                $tag_ids = array_map( 'intval', $tag_ids );
                $tag_ids = array_unique( $tag_ids );

                $term_taxonomy_ids = wp_set_object_terms( $post_id, $tag_ids, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );

                if ( is_wp_error( $term_taxonomy_ids ) ) {
                    // There was an error somewhere and the terms couldn't be set.
                    $post['errors'][ $field ]['errors'][] = __( 'Error saving the tags for the attachment!', 'foogallery' );
                }
            }
        }


        /***
         *
         * Add a tag filter to the attachments listing page
         */
        function add_collection_filter() {
            global $pagenow;
            if ( 'upload.php' == $pagenow ) {

                $dropdown_options = array(
                    'taxonomy'        => FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION,
                    'show_option_all' => __( 'All Collections' ),
                    'hide_empty'      => false,
                    'hierarchical'    => true,
                    'orderby'         => 'name',
                    'show_count'      => true,
                    'walker'          => new foogallery_walker_category_dropdown(),
                    'value'           => 'slug'
                );

                wp_dropdown_categories( $dropdown_options );
            }
        }
    }
}
