<?php
/**
 * Class to handle all interactions for Gallery datasources
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_Datasources' ) ) {

    class FooGallery_Admin_Gallery_Datasources {

        /**
         * Primary class constructor.
         */
        public function __construct() {
            //render the datasource modal
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
            add_action( 'admin_footer', array( $this, 'render_datasource_modal' ) );
            add_action( 'foogallery_gallery_metabox_items', array( $this, 'add_datasources_hidden_inputs' ) );
            add_action( 'foogallery_gallery_metabox_items_add', array( $this, 'add_datasources_button' ) );
            add_action( 'wp_ajax_foogallery_load_datasource_content', array( $this, 'ajax_load_datasource_content' ) );
            add_action( 'foogallery_before_save_gallery', array( $this, 'save_gallery_datasource' ), 8, 2 );
            add_filter( 'foogallery_preview_arguments', array( $this, 'include_datasource_in_preview' ), 10, 3 );
            add_filter( 'foogallery_render_template_argument_overrides', array( $this, 'override_datasource_arguments' ), 10, 2 );
        }

		public function enqueue_scripts_and_styles( $hook ) {
			wp_enqueue_style( 'foogallery.admin.datasources', FOOGALLERY_URL . 'css/foogallery.admin.datasources.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.datasources', FOOGALLERY_URL . 'js/foogallery.admin.datasources.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}

			/**
         * Include the datasource arguments for previews
         *
         * @param $args
         * @param $form_post
         * @param $template
         * @return array
         */
        public function include_datasource_in_preview( $args, $form_post, $template ) {
            if ( isset( $form_post['foogallery_datasource'] ) ) {
                $args['datasource'] = $form_post['foogallery_datasource'];
            }
            if ( isset( $form_post['foogallery_datasource_value'] ) ) {
                $args['datasource_value'] = $form_post['foogallery_datasource_value'];
            }

            return $args;
        }

        /**
         * Allow the gallery to render using an override for the datasource
         * @param $foogallery
         * @param $args
         * @return FooGallery
         */
        public function override_datasource_arguments( $foogallery, $args ) {
            if ( isset( $args['datasource'] ) ) {
                $foogallery->datasource_name = $args['datasource'];
            }
            if ( isset( $args['datasource_value'] ) ) {
                $foogallery->datasource_value = $this->get_json_datasource_value( $args['datasource_value'] );
            }

            return $foogallery;
        }

		/**
		 * Save the datasource name and value for the gallery
		 * @param $post_id
		 * @param $_post
		 */
        public function save_gallery_datasource( $post_id, $_post ) {
            //action pre-save
            do_action( 'foogallery_before_save_gallery_datasource', $post_id );

            //set some defaults
            $datasource = '';
            $datasource_value = array();

            if ( isset( $_POST[FOOGALLERY_META_DATASOURCE] ) ) {
				$datasource = $_POST[FOOGALLERY_META_DATASOURCE];
				update_post_meta( $post_id, FOOGALLERY_META_DATASOURCE, $datasource );

                if ( isset( $_POST[FOOGALLERY_META_DATASOURCE_VALUE] ) ) {
                    $datasource_value = $this->get_json_datasource_value( $_POST[FOOGALLERY_META_DATASOURCE_VALUE] );

                    if ( !empty( $datasource_value ) ) {
                        update_post_meta( $post_id, FOOGALLERY_META_DATASOURCE_VALUE, $datasource_value );
                    } else {
                        delete_post_meta( $post_id, FOOGALLERY_META_DATASOURCE_VALUE );
                    }
                }

			} else {
                delete_post_meta( $post_id, FOOGALLERY_META_DATASOURCE );
            }

            //action for post-save
            do_action( 'foogallery_after_save_gallery_datasource', $post_id, $datasource, $datasource_value );
        }

        /**
         * Safely returns an array from the json string
         * @param $datasource_value_string
         * @return mixed
         */
        public function get_json_datasource_value( $datasource_value_string ) {
            $datasource_value = array();

            //check if the value is JSON and convert to object if needed
            if ( is_string($datasource_value_string) && is_array( json_decode( stripslashes( $datasource_value_string ), true ) ) ) {
                $datasource_value = json_decode( stripslashes( $datasource_value_string ), true );
            }
            return $datasource_value;
        }

        /**
         * Outputs the modal content for the specific datasource
         */
        public function ajax_load_datasource_content() {
            $nonce = safe_get_from_request( 'nonce' );
            $datasource = safe_get_from_request( 'datasource' );
            $datasource_value = $this->get_json_datasource_value( safe_get_from_request( 'datasource_value' ) );
            $foogallery_id = intval( safe_get_from_request( 'foogallery_id' ) );

            if ( wp_verify_nonce( $nonce, 'foogallery-datasource-content' ) ) {
                do_action( 'foogallery-datasource-modal-content_'. $datasource, $foogallery_id, $datasource_value );
            }

            die();
        }

        /**
         * Adds the datasource hidden inputs to the page
         * @param FooGallery $gallery
         */
        public function add_datasources_hidden_inputs( $gallery ) {
            $datasources = foogallery_gallery_datasources();
            if ( count( $datasources ) > 1 ) {
                $datasource_value = get_post_meta( $gallery->ID, FOOGALLERY_META_DATASOURCE_VALUE, true );
                if ( is_array( $datasource_value ) ) {
                    $datasource_value = json_encode( $datasource_value );
                } ?>
            <input type="hidden" data-foogallery-preview="include" name="<?php echo FOOGALLERY_META_DATASOURCE; ?>" value="<?php echo $gallery->datasource_name; ?>" id="<?php echo FOOGALLERY_META_DATASOURCE; ?>" />
            <input type="hidden" data-foogallery-preview="include" value="<?php echo esc_attr( $datasource_value ); ?>" name="<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>" id="<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>" />
            <?php }
        }

        /**
         * Add the datasources button to the items metabox
         */
        public function add_datasources_button() {
            $datasources = foogallery_gallery_datasources();
            //we only want to show the datasources button if there are more than 1 datasources
            if ( count( $datasources ) > 1 ) { ?>
				<p><?php _e('or', 'foogallery');?></p>
				<button type="button" class="button button-secondary button-hero gallery_datasources_button">
					<span class="dashicons dashicons-format-gallery"></span><?php _e( 'Add From Another Source', 'foogallery' ); ?>
				</button>
            <?php }
        }

        /**
         * Renders the datasource modal for use on the gallery edit page
         */
        public function render_datasource_modal() {

            global $post;

            //check if the gallery edit page is being shown
            $screen = get_current_screen();
            if ( 'foogallery' !== $screen->id ) {
                return;
            }

            $datasources = foogallery_gallery_datasources();

            ?>
            <?php wp_nonce_field('foogallery_load_galleries', 'foogallery_load_galleries', false); ?>
            <div class="foogallery-datasources-modal-wrapper" data-foogalleryid="<?php echo $post->ID; ?>" data-nonce="<?php echo wp_create_nonce( 'foogallery-datasource-content' ); ?>" style="display: none;">
                <div class="media-modal wp-core-ui">
                    <button type="button" class="media-modal-close">
                        <span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span>
                    </button>
                    <div class="media-modal-content">
                        <div class="media-frame wp-core-ui">
                            <div class="foogallery-datasource-modal-title">
                                <h1><?php _e('Add To Gallery From Another Source', 'foogallery'); ?></h1>
                            </div>
                            <div class="foogallery-datasource-modal-sidebar">
                                <div class="foogallery-datasource-modal-sidebar-menu">
                                    <?php foreach ( $datasources as $key=>$datasource ) {
                                    if ( $datasource['public'] ) { ?>
                                    <a href="#" class="media-menu-item foogallery-datasource-modal-selector" data-datasource="<?php echo $key; ?>"><?php echo $datasource['menu']; ?></a>
                                        <?php } } ?>
                                </div>
                            </div>
                            <div class="foogallery-datasource-modal-container">
								<div class="foogallery-datasource-modal-container-inner">
									<?php _e('Select a source on the left to get started.', 'foogallery'); ?>
								</div>
                                <?php foreach ( $datasources as $key=>$datasource ) {
                                    if ( $datasource['public'] ) { ?>
                                        <div class="foogallery-datasource-modal-container-inner <?php echo $key; ?> not-loaded">
                                            <div class="spinner"></div>
                                        </div>
                                    <?php } } ?>
                            </div>
                            <div class="foogallery-datasource-modal-toolbar">
                                <div class="foogallery-datasource-modal-toolbar-inner">
                                    <div class="media-toolbar-secondary">
                                        <a href="#"
                                           class="foogallery-datasource-modal-cancel button media-button button-large button-secondary media-button-insert"
                                           title="<?php esc_attr_e('Cancel', 'foogallery'); ?>"><?php _e('Cancel', 'foogallery'); ?></a>
                                    </div>
                                    <div class="media-toolbar-primary">
                                        <a href="#"
                                           class="foogallery-datasource-modal-insert button media-button button-large button-primary media-button-insert"
                                           disabled="disabled"
                                           title="<?php esc_attr_e('OK', 'foogallery'); ?>"><?php _e('OK', 'foogallery'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="media-modal-backdrop"></div>
            </div>
            <?php
        }
    }
}
