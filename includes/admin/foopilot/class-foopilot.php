<?php
/*
 * FooGallery Admin FooPilot class
 */

 if ( ! class_exists( 'FooGallery_Admin_FooPilot' ) ) {

    class FooGallery_Admin_FooPilot{

        /**
		 * Primary class constructor.
		 */
        public function __construct(){
            add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_foopilot' ), 70 );
            add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_foopilot' ), 70, 1 );
            add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_foopilot' ), 70, 4 );
            add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_foopilot' ), 70, 2 );
            // Enqueue CSS and JavaScript
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

            add_filter( 'foogallery_admin_settings_override', array( $this, 'add_foopilot_settings' ), 50 );
            add_action( 'wp_ajax_generate_foopilot_api_key', array( $this, 'generate_random_api_key' ) );

            // Initialize credit points
            add_action( 'init', array( $this, 'initialize_foopilot_credit_points' ) );

        }

        /**
         * Enqueue CSS and JavaScript files.
         */
        public function enqueue_scripts_and_styles() {
            // Enqueue CSS
            wp_enqueue_style( 'foopilot-modal-css', FOOGALLERY_URL . 'includes/admin/foopilot/css/foopilot-modal.css' );

            // Enqueue JavaScript
            wp_enqueue_script( 'foopilot-modal-js', FOOGALLERY_URL . 'includes/admin/foopilot/js/foopilot-modal.js' , array( 'jquery' ), '1.0', true );
        }
        
        /**
		 * Save foopilot tab data content
		 * 
		 * @param $img_id int attachment id to update data
		 * 
		 * @param $data array of form post data
		 * 
		 */
		public function foogallery_attachment_save_data_foopilot($img_id, $data ) {

			if ( is_array( $data ) && !empty( $data ) ) {
				if ( array_key_exists( 'data-width', $data ) ) {
					update_post_meta( $img_id, '_data-width', $data['data-width'] );
				}

				if ( array_key_exists( 'data-height', $data ) ) {
					update_post_meta( $img_id, '_data-height', $data['data-height'] );
				}

				if ( array_key_exists( 'panning', $data ) ) {
					update_post_meta( $img_id, '_foobox_panning', $data['panning'] );
				}

				if ( array_key_exists( 'override_type', $data ) ) {
					update_post_meta( $img_id, '_foogallery_override_type', $data['override_type'] );
				}
			}
		}

        /**
		 * Image modal foopilot tab data update
		 */
		public function foogallery_attachment_modal_data_foopilot( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                $modal_data['data_width'] =    get_post_meta( $attachment_id, '_data-width', true );
                $modal_data['data_height'] =   get_post_meta( $attachment_id, '_data-height', true );
                $modal_data['panning'] =       get_post_meta( $attachment_id, '_foobox_panning', true );
                $modal_data['override_type'] = get_post_meta( $attachment_id, '_foogallery_override_type', true );
            }
			return $modal_data;
		}

        /**
		 * Image modal foopilot tab title
		 */
		public function display_tab_foopilot() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-foopilot">
				<input type="radio" name="tabset" id="foogallery-tab-foopilot" aria-controls="foogallery-panel-foopilot">
				<label for="foogallery-tab-foopilot"><?php _e( 'FooPilot', 'foogallery'); ?></label>
			</div>
		<?php }

        /**
		 * Image modal foopilot tab content
		 */
		public function display_tab_content_foopilot( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-foopilot" class="tab-panel">
                        <div>
                            <?php echo $this->display_foopilot_settings_html(); ?>
                        </div>

                        <div id="foopilot-modal" class="foogallery-foopilots-modal-wrapper" style="display: none;">
                            <?php echo $this->display_foopilot_modal_html(); ?>
                        </div>
					</section>
                    
                    <script>
                        jQuery(document).ready(function($) {
                            // Listen for click event on foopilot buttons
                            $( '.foogallery-foopilot').on( 'click', function(event) {
                                // Prevent the default action of the button click
                                event.preventDefault();
                                
                                // Show the foopilot modal
                                $( '#foopilot-modal').show();
                            });
                        });
                    </script>                    
					<?php
				}
			}
		}

        /**
         * Generate foopilot settings HTML.
         */
        public function display_foopilot_settings_html() {
            ob_start();
            ?>
            <div class="settings">

                <span class="setting has-description" data-setting="foopilot-image-title" style="margin-bottom: 8px;">
                    <label for="foogallery-foopilot" class="name"><?php _e( 'Generate Title', 'foogallery'); ?></label>
                    <button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="title"><?php _e( 'Generate Title', 'foogallery'); ?></button>
                </span>

                <span class="setting has-description" data-setting="foopilot-image-tags" style="margin-bottom: 8px;">
                    <label for="foogallery-foopilot" class="name"><?php _e( 'Generate Tags', 'foogallery'); ?></label>
                    <button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="tags"><?php _e( 'Generate Tags', 'foogallery'); ?></button>
                </span>

                <span class="setting has-description" data-setting="foopilot-image-alt-text" style="margin-bottom: 8px;">
                    <label for="foogallery-foopilot" class="name"><?php _e( 'Generate ALT Text', 'foogallery'); ?></label>
                    <button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="alt-text"><?php _e( 'Generate ALT Text', 'foogallery'); ?></button>
                </span>

                <span class="setting has-description" data-setting="foopilot-image-caption" style="margin-bottom: 8px;">
                    <label for="foogallery-foopilot" class="name"><?php _e( 'Generate Caption', 'foogallery'); ?></label>
                    <button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="caption"><?php _e( 'Generate Caption', 'foogallery'); ?></button>
                </span>                          

                <span class="setting has-description" data-setting="foopilot-image-categories" style="margin-bottom: 8px;">
                    <label for="foogallery-foopilot" class="name"><?php _e( 'Generate Categories', 'foogallery'); ?></label>
                    <button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="categories"><?php _e( 'Generate Categories', 'foogallery'); ?></button>
                </span>

                <span class="setting has-description" data-setting="foopilot-image-description" style="margin-bottom: 8px;">
                    <label for="foogallery-foopilot" class="name"><?php _e( 'Generate Description', 'foogallery'); ?></label>
                    <button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="description"><?php _e( 'Generate Description', 'foogallery'); ?></button>
                </span>

            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Generate foopilot selected task HTML.
         */
        public function display_foopilot_selected_task_html() {
            ob_start();
            ?>
            <div class="foopilot-task-html" style="display: flex; justify-content: center; align-items:center;">
                <div>
                    <!-- include here the selected task file -->
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Generate foopilot modal HTML.
         */
        public function display_foopilot_modal_html(){
            // Check if the FooPilot API key is present
            $foopilot_api_key = foogallery_get_setting( 'foopilot_api_key' );
            ?>
            <div class="media-modal wp-core-ui" id="fg-foopilot-modal">
                <?php
                // If the API key is not present, display the sign-up form
                if ( empty( $foopilot_api_key ) ) {
                    ob_start();
                    ?>
                    <div class="foogallery-foopilot-signup-form">
                        <div class="foogallery-foopilot-signup-form-inner">
                            <p><?php _e( 'Unlock the power of FooPilot! Sign up for free and get 20 credits to explore our service.', 'foogallery' ); ?></p>
                            <form class="foogallery-foopilot-signup-form-inner-content">
                                <div style="margin-bottom: 20px;">
                                <input type="email" id="foopilot-email" name="email" placeholder="<?php _e( 'Enter your email', 'foogallery'); ?>" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 250px;">
                                </div>
                                <button class="foogallery-foopilot-signup-form-inner-content-button button button-primary button-large" type="submit" style="padding: 10px 20px; background-color: #0073e6; color: #fff; border: none; border-radius: 5px; cursor: pointer;"><?php _e( 'Sign Up for free', 'foogallery'); ?></button>
                            </form>
                        </div>                    
                    </div>
                    <script>
                        jQuery(document).ready(function($) {
                            // Listen for click event on foopilot buttons
                            $( '.foogallery-foopilot-signup-form-inner-content-button').on( 'click', function(event) {
                                event.preventDefault();
                                var email = $( '#foopilot-email').val();
                                // Make Ajax call
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'generate_foopilot_api_key',
                                        email: email 
                                    },
                                    success: function(response) {
                                        // Reload the modal content dynamically
                                        $('#foopilot-modal').html(response);
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(xhr.responseText); // Log any errors
                                    }
                                });
                            });
                        });
                    </script>

                    <?php
                    return ob_get_clean();
                }

                // If the API key is present, display the regular modal content
                ob_start();
                ?>
                <div>
                    <button type="button" class="media-modal-close">
                        <span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span>
                    </button>
                    <div class="media-modal-content">
                        <div class="media-frame wp-core-ui">
                            <div class="foogallery-foopilot-modal-title">
                                <h3>
                                    <?php $credit_points = $this->get_foopilot_credit_points();
                                        _e( 'FooPilot Credit Points:', 'foogallery' ); ?> <span id="foogallery-credit-points">
                                        <?php echo $credit_points; 
                                    ?></span>
                                </h3>                                         
                            </div>
                            <div class="foogallery-foopilot-modal-sidebar">
                                <div class="foogallery-foopilot-modal-sidebar-menu">
                                    <?php echo $this->display_foopilot_settings_html(); ?>
                                </div>
                            </div>
                            <div class="foogallery-foopilot-modal-container">
                                <div class="foogallery-foopilot-modal-container-inner">
                                <?php echo $this->display_foopilot_selected_task_html(); ?>
                                </div>
                            </div>
                            <div class="foogallery-foopilot-modal-toolbar">
                                <div class="foogallery-foopilot-modal-toolbar-inner">
                                    <div class="media-toolbar-secondary">
                                        <a href="#"
                                        class="foogallery-foopilot-modal-cancel button"
                                        title="<?php esc_attr_e( 'Cancel', 'foogallery'); ?>"><?php _e( 'Cancel', 'foogallery'); ?></a>
                                    </div>
                                    <div class="media-toolbar-primary">
                                        <a href="#"
                                        class="foogallery-foopilot-modal-insert button"
                                        disabled="disabled"
                                        title="<?php esc_attr_e( 'OK', 'foogallery'); ?>"><?php _e( 'OK', 'foogallery'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Function to initialize credit points
         */
        public function initialize_foopilot_credit_points() {
            // Check if the credit points are already set, if not, set it to 0
            if ( ! get_option( 'foopilot_credit_points' ) ) {
                update_option( 'foopilot_credit_points', 0 );
            }
        }

        /**
         * Function to retrieve credit points
         */
        public function get_foopilot_credit_points() {
            return get_option( 'foopilot_credit_points', 0 );
        }

        /**
         * Function to add credit points
         */
        public function add_foopilot_credit_points( $points ) {
            $current_points = $this->get_foopilot_credit_points();
            $updated_points = $current_points + $points;
            update_option( 'foopilot_credit_points', $updated_points );
        }

        /**
         * Generate Foopilot api keys
         */
        public function generate_random_api_key() {
            // Retrieve the current points balance
            $current_points = $this->get_foopilot_credit_points();

            // If the current points balance is greater than 0, reset it to 0
            if ( $current_points > 0 ) {
                update_option( 'foopilot_credit_points', 0 );
            }

            // Credit the register user +20 points
            $this->add_foopilot_credit_points(20);

            // Generate a random API key (64 characters in hexadecimal)
            $random_api_key = bin2hex( random_bytes(32) );

            // Save API key to foogallery setting
            foogallery_set_setting( 'foopilot_api_key', $random_api_key );

            wp_die(); // Terminate immediately
        }            

        /**
         * Add FooPilot settings to the provided settings array.
         *
         * This function adds foopilot-related settings for the foogallery Box Slider section.
         *
         * @param array $settings An array of existing settings.
         *
         * @return array The modified settings array with added foopilot settings.
         */
        function add_foopilot_settings( $settings ) {

            $settings['settings'][] = array(
                'id'      => 'foopilot_api_key',
                'title'   => __( 'FooPilot API key', 'foogallery' ),
                'type'    => 'text',
                'default' => __( '', 'foogallery' ),
                'tab'     => 'FooPilot'
            );

            return $settings;
        }
    }
 }
