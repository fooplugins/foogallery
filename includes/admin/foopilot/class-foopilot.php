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
            add_action( 'wp_ajax_deduct_foopilot_points', array( $this, 'deduct_foopilot_points' ) );

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
			
		}

        /**
		 * Image modal foopilot tab data update
		 */
		public function foogallery_attachment_modal_data_foopilot( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                
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
                            $('.foogallery-foopilot').on('click', function(event) {
                                // Prevent the default action of the button click
                                event.preventDefault();
                                var task = $(this).data('task');
                                // Get the URL of the task file
                                var FOOGALLERY_URL = '<?php echo FOOGALLERY_URL; ?>';
                                var taskFileUrl = FOOGALLERY_URL + 'includes/admin/foopilot/tasks/' + 'foopilot-generate-' + task + '.php';

                                // Load the task file content and display it in the modal
                                $.get(taskFileUrl, function(response) {
                                    // Display the selected task content in the modal
                                    $('.foopilot-task-html').html(response);
                                    // Show the foopilot modal
                                    $('#foopilot-modal').show();

                                    // Check if the user has enough points to perform the task
                                    var currentPoints = parseInt($('#foogallery-credit-points').text());
                                    var pointsToDeduct = 1; // Adjust this as needed
                                    if (currentPoints >= pointsToDeduct) {
                                        // Deduct points after task completion
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'deduct_foopilot_points',
                                                points: pointsToDeduct
                                            },
                                            success: function(response) {
                                                // Reload the modal content dynamically
                                                $("#foopilot-modal").load(" #foopilot-modal");
                                            },
                                            error: function(xhr, status, error) {
                                                console.error(xhr.responseText); // Log any errors
                                            }
                                        });
                                    } else {
                                        // Display a message indicating insufficient points
                                        $('.foopilot-task-html').html('Insufficient points to perform this task.');
                                    }
                                }).fail(function() {
                                    // Display an error message if the task file cannot be loaded
                                    $('.foopilot-task-html').html('Task file: ' + task + ' not found.');
                                    // Show the foopilot modal
                                    $('#foopilot-modal').show();
                                });
                            });
                        });
                    </script>                                    
					<?php
				}
			}
		}

        /**
         * Deduct points after completing a task and return updated modal content.
         */
        public function deduct_foopilot_points() {
            if ( isset($_POST['points']) ) {
                $pointsToDeduct = intval( $_POST['points'] );
                // Check if user has sufficient points
                $currentPoints = $this->get_foopilot_credit_points();
                if ( $currentPoints >= $pointsToDeduct && $pointsToDeduct > 0 ) {
                    // Deduct points only if the user has sufficient points
                    $updatedPoints = max(0, $currentPoints - $pointsToDeduct);
                    update_option( 'foopilot_credit_points', $updatedPoints );
                } else {
                    // Handle case where user doesn't have enough points
                    wp_send_json_error( 'Insufficient points' );
                }
            }
            wp_die();
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
            <div class="foopilot-task-html" style="display: flex; justify-content: center; align-items:center; text-align:center; color: black;">
                
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
            <script>
                jQuery(document).ready(function($) {
                    // Function to close the modal
                    function closeFoopilotModal() {
                        $('#foopilot-modal').hide();
                    }

                    // Listen for click event on Cancel button
                    $('.foogallery-foopilot-modal-cancel').on('click', function(event) {
                        event.preventDefault();
                        closeFoopilotModal();
                    });

                    // Listen for click event on close button
                    $('.media-modal-close').on('click', function(event) {
                        event.preventDefault();
                        closeFoopilotModal();
                    });
                });
            </script>
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
