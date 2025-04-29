<?php
/**
 * FooGallery Pro gallery blueprints Class
 */
if ( ! class_exists( 'FooGallery_Pro_Gallery_Blueprints' ) ) {

    class FooGallery_Pro_Gallery_Blueprints {

	    const ENABLED = 'enabled';

        function __construct() {
            add_action( 'plugins_loaded', array( $this, 'load_feature' ) );

            add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

		function load_feature() {
            if ( foogallery_feature_enabled( 'foogallery-gallery-blueprints' ) ) {                
				// Add the Gallery Blueprint metabox.
				add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_gallery_blueprint_meta_box_to_gallery' ) );

				// Ajax handler for toggling the Gallery Blueprint.
				add_action( 'wp_ajax_foogallery_gallery_blueprint_toggle', array( $this, 'ajax_blueprint_toggle' ) );

				// Ajax handler for setting the Gallery Blueprint.
				add_action( 'wp_ajax_foogallery_gallery_blueprint_set', array( $this, 'ajax_blueprint_set' ) );

				// Override the settings metabox.
				add_filter( 'foogallery_should_render_gallery_settings_metabox', array( $this, 'override_settings_metabox'), 10, 2 );
				add_action( 'foogallery_after_render_gallery_settings_metabox', array( $this, 'show_gallery_blueprint_info_in_settings_metabox' ), 10, 1 );

				// Hide other metaboxes when a Gallery Blueprint is being used.
				add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'hide_metaboxes' ), 99 );

				// Admin notice for Gallery Blueprint
				add_action( 'admin_notices', array( $this, 'display_gallery_blueprint_notice' ) );

				// Override where the settings for a gallery are loaded from. In this case, from the Gallery Blueprint.
				add_filter( 'foogallery_load_gallery_settings_id', array( $this, 'load_settings_from_gallery_blueprint' ) );
            }
        }

		function register_extension( $extensions_list ) {
			$pro_features = foogallery_pro_features();

            $extensions_list[] = array(
                'slug' => 'foogallery-gallery-blueprints',
                'class' => 'FooGallery_Pro_Gallery_Blueprints',
                'categories' => array( 'Premium' ),
                'title' => foogallery__( 'Gallery Blueprints', 'foogallery' ),
                'description' => $pro_features['gallery_blueprints']['desc'],
                'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                'external_link_url' => $pro_features['gallery_blueprints']['link'],
				'dashicon'          => 'dashicons-networking',
                'tags' => array( 'Premium' ),
                'source' => 'bundled',
                'activated_by_default' => true,
                'feature' => true
            );

            return $extensions_list;
        }

		/**
		 * Load settings from the Gallery Blueprint for a given FooGallery.
		 *
		 * This function retrieves the ID of the Gallery Blueprint associated with the specified FooGallery.
		 * If the FooGallery is using a Gallery Blueprint, the ID of the Gallery Blueprint is returned.
		 * If the FooGallery is not associated with any Gallery Blueprint, the ID of the original FooGallery is returned.
		 *
		 * @param int $foogallery_id The ID of the FooGallery for which to load settings.
		 *
		 * @return int The ID of the Gallery Blueprint if one is associated; otherwise, the original FooGallery ID.
		 */
		function load_settings_from_gallery_blueprint( $foogallery_id ) {
			$gallery_blueprint = $this->get_gallery_blueprint( $foogallery_id );
			if ( $gallery_blueprint !== false ) {
				return $gallery_blueprint->ID;
			}

			return $foogallery_id;
		}

	    /**
	     * Hide the metaboxes which don't make sense, if a Gallery Blueprint is being used.
	     *
	     * @param $post
	     *
	     * @return void
	     */
		function hide_metaboxes( $post ) {
			$gallery_blueprint = $this->get_gallery_blueprint( $post->ID );
			if ( false !== $gallery_blueprint ) {
				remove_meta_box( 'foogallery_customcss', FOOGALLERY_CPT_GALLERY, 'normal' );

				remove_meta_box( 'foogallery_bulk_copy', FOOGALLERY_CPT_GALLERY, 'normal' );

				remove_meta_box( 'foogallery_retina', FOOGALLERY_CPT_GALLERY, 'side' );

				remove_meta_box( 'foogallery_sorting', FOOGALLERY_CPT_GALLERY, 'side' );

				remove_meta_box( 'foogallery_thumb_settings', FOOGALLERY_CPT_GALLERY, 'side' );
			}
		}

	    /**
	     * Get all galleries that are using the Gallery Blueprint.
	     *
	     * @param $gallery_blueprint_id
	     *
	     * @return FooGallery[]
	     */
		function get_gallery_blueprint_usage( $gallery_blueprint_id ) {
			return foogallery_get_all_galleries( false, array(
				'meta_key'   => FOOGALLERY_META_BLUEPRINT_SET,
				'meta_value' => $gallery_blueprint_id
			) );
		}

	    /**
	     * Display info when editing the Gallery Blueprint.
	     *
	     * @return void
	     */
	    function display_gallery_blueprint_notice() {
		    global $post;

		    if ( !isset( $post ) ) {
			    return;
		    }

			$foogallery_id = $post->ID;

		    $screen_id = foo_current_screen_id();

		    //only include scripts if we on the foogallery edit page
		    if ( FOOGALLERY_CPT_GALLERY === $screen_id ) {

				if ( !$this->is_gallery_blueprint( $foogallery_id ) ) {
					return;
				}

			    $gallery_usage_count = count( $this->get_gallery_blueprint_usage( $foogallery_id ) );
			    ?>
			    <style>
                    .foogallery-rating-notice {
                        border-left-color: #ff8800;
                    }

                    .foogallery-rating-notice .dashicons-warning {
                        color: #ff8800;
                    }
			    </style>
			    <div class="foogallery-rating-notice notice notice-success is-dismissible">
				    <p>
					    <span class="dashicons dashicons-warning"></span>
					    <strong><?php _e( 'Editing Gallery Blueprint!' ) ?></strong>
					    <br/>
					    <?php printf( __( 'PLEASE NOTE : you are editing a Gallery Blueprint. Editing settings for this Gallery Blueprint will affect %s galleries. To see which galleries, scroll down to the Gallery Blueprint Settings metabox.', 'foogallery' ), $gallery_usage_count ); ?>
				    </p>
			    </div>
			    <?php
		    }
	    }

	    /**
	     * Show Gallery Blueprint details in the gallery settings metabox.
	     *
	     * @param $gallery
	     *
	     * @return void
	     */
		function show_gallery_blueprint_info_in_settings_metabox( $gallery ) {
			$gallery_blueprint = $this->get_gallery_blueprint( $gallery->ID );
			if ( false !== $gallery_blueprint ) {
				echo '<div style="margin: 6px 0 0; padding: 0 12px 12px;"><p>';
				echo __( 'All settings for this gallery are currently inherited from the Gallery Blueprint', 'foogallery' );
				echo ' <strong>' . $gallery_blueprint->name . '</strong>.</p><p>';
				echo __( 'To edit any settings for this gallery, you will need to edit the settings of the Gallery Blueprint directly.', 'foogallery' );
				echo '</p><p><a target="_blank" href="' . get_edit_post_link( $gallery_blueprint->ID ) . '">' . __( 'Edit the Gallery Blueprint', 'foogallery');
				echo '</a></p></div>';
			}
		}

	    /**
	     * Override the gallery settings metabox
	     *
	     * @param $show
	     * @param $gallery
	     *
	     * @return false|mixed
	     */
		function override_settings_metabox( $show, $gallery ) {
			$gallery_blueprint = $this->get_gallery_blueprint( $gallery->ID );
			if ( false !== $gallery_blueprint ) {
				$show = false;
			}
			return $show;
		}

        /**
         * Add a metabox to the gallery for gallery blueprints
         * @param $post
         */
        function add_gallery_blueprint_meta_box_to_gallery( $post ) {
            add_meta_box(
                'foogallery_blueprint',
                __( 'Gallery Blueprint Settings', 'foogallery' ),
                array( $this, 'render_metabox' ),
                FOOGALLERY_CPT_GALLERY,
                'normal',
                'low'
            );
        }

	    /**
	     * Returns if the gallery is a Gallery Blueprint
	     *
	     * @param $foogallery_id
	     *
	     * @return bool
	     */
		function is_gallery_blueprint( $foogallery_id ) {
			return get_post_meta( $foogallery_id, FOOGALLERY_META_BLUEPRINT_ENABLED, true ) === self::ENABLED;
		}

	    /**
	     * Set the gallery to be a Gallery Blueprint
	     *
	     * @param $foogallery_id
	     *
	     * @return void
	     */
		function set_as_gallery_blueprint( $foogallery_id ) {
			update_post_meta( $foogallery_id, FOOGALLERY_META_BLUEPRINT_ENABLED, self::ENABLED );
		}

	    /**
	     * Unset the gallery to not be a Gallery Blueprint
	     *
	     * @param $foogallery_id
	     *
	     * @return void
	     */
	    function unset_as_gallery_blueprint( $foogallery_id ) {
		    delete_post_meta( $foogallery_id, FOOGALLERY_META_BLUEPRINT_ENABLED );
	    }

	    /**
	     * Return the text of the toggle button.
	     *
	     * @param $foogallery_id
	     *
	     * @return mixed|string|void
	     */
		function get_toggle_button_text( $foogallery_id ) {
			return !$this->is_gallery_blueprint( $foogallery_id ) ? __( 'Set as a Gallery Blueprint', 'foogallery' ) : __( 'Unset Gallery Blueprint', 'foogallery' );
		}

	    /**
	     * Set a Gallery Blueprint for a gallery
	     *
	     * @param $foogallery_id
	     * @param $blueprint_foogallery_id
	     *
	     * @return void
	     */
	    function set_gallery_blueprint( $foogallery_id, $blueprint_foogallery_id ) {
			if ( $blueprint_foogallery_id > 0 ) {
				update_post_meta( $foogallery_id, FOOGALLERY_META_BLUEPRINT_SET, $blueprint_foogallery_id );
			} else {
				delete_post_meta( $foogallery_id, FOOGALLERY_META_BLUEPRINT_SET );
			}
	    }

	    /**
	     * Gets the Gallery Blueprint ID for a foogallery.
	     *
	     * @param $foogallery_id
	     *
	     * @return int
	     */
		function get_gallery_blueprint_id( $foogallery_id ) {
			return intval( get_post_meta( $foogallery_id, FOOGALLERY_META_BLUEPRINT_SET, true ) );
		}

	    /**
	     * Gets the Gallery Blueprint for a foogallery.
	     *
	     * @param $foogallery_id
	     *
	     * @return int
	     */
	    function get_gallery_blueprint( $foogallery_id ) {
			global $foogallery_admin_gallery_blueprint_cache;

			if ( isset( $foogallery_admin_gallery_blueprint_cache ) &&
			     is_array( $foogallery_admin_gallery_blueprint_cache ) && array_key_exists( $foogallery_id, $foogallery_admin_gallery_blueprint_cache ) ) {
				return $foogallery_admin_gallery_blueprint_cache[$foogallery_id];
			}

		    $gallery_blueprint_id = $this->get_gallery_blueprint_id( $foogallery_id );

			if ( $gallery_blueprint_id > 0 ) {
				$gallery_blueprint = FooGallery::get_by_id( $gallery_blueprint_id );
				if ( !isset( $foogallery_admin_gallery_blueprint_cache ) ) {
					$foogallery_admin_gallery_blueprint_cache = array();
				}
				$foogallery_admin_gallery_blueprint_cache[$foogallery_id] = $gallery_blueprint;
				return $gallery_blueprint;
			}

			return false;
	    }

        /**
         * Render the Gallery Blueprint metabox on the gallery edit page
         * @param $post
         */
        function render_metabox( $post ) {
            ?>
	        <style>
                .foogallery_gallery_blueprint_spinner,
                .foogallery_gallery_blueprint_set_spinner {
                    float: none !important;
                }
	        </style>
            <script type="text/javascript">
                jQuery(function ($) {
	                $('#foogallery_gallery_blueprint_container').on('click', '#foogallery_gallery_blueprint_toggle', function(e) {
		                e.preventDefault();

		                $('.foogallery_gallery_blueprint_spinner').addClass('is-active');
		                var data = 'action=foogallery_gallery_blueprint_toggle' +
		                           '&foogallery=<?php echo $post->ID; ?>' +
		                           '&foogallery_gallery_blueprint_toggle_nonce=' + $('#foogallery_gallery_blueprint_toggle_nonce').val() +
		                           '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

		                $.ajax({
			                type: "POST",
			                url: ajaxurl,
			                data: data,
			                success: function(data) {
				                $('#foogallery_gallery_blueprint_container').html(data);
			                }
		                });
	                });

	                $('#foogallery_gallery_blueprint_container').on('click', '#foogallery_gallery_blueprint_set', function(e) {
		                e.preventDefault();

		                $('.foogallery_gallery_blueprint_set_spinner').addClass('is-active');
		                var data = 'action=foogallery_gallery_blueprint_set' +
		                           '&foogallery=<?php echo $post->ID; ?>' +
		                           '&blueprint=' + $('#foogallery_gallery_blueprint_select').val() +
		                           '&foogallery_gallery_blueprint_set_nonce=' + $('#foogallery_gallery_blueprint_set_nonce').val() +
		                           '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

		                $.ajax({
			                type: "POST",
			                url: ajaxurl,
			                data: data,
			                success: function(data) {
				                $('#foogallery_gallery_blueprint_container').html(data);
			                }
		                });
	                });
                });
            </script>
            <div>
                <p class="foogallery-help"><?php _e('Gallery Blueprints allow you to setup a blueprint for multiple galleries, so that when you update the single blueprint, all the galleries change at the same time. Think of a blueprint as a "template". Any gallery using a blueprint will not be able to set its own settings, as everything is inherited from the blueprint. This allows you to update settings once, and affect multiple galleries.', 'foogallery'); ?></p>
            </div>
            <br/>
            <div id="foogallery_gallery_blueprint_container">
                <?php $this->render_gallery_blueprint_container( $post->ID ); ?>
            </div>
            <?php
        }

	    /**
	     * Returns all gallery blueprints
	     *
	     * @return FooGallery[] array of blueprint FooGallery galleries
	     */
	    function get_all_gallery_blueprints() {
		    return foogallery_get_all_galleries( false, array(
			    'meta_key'   => FOOGALLERY_META_BLUEPRINT_ENABLED,
			    'meta_value' => self::ENABLED
		    ) );
	    }

	    /**
	     * Render the container only
	     *
	     * @param $foogallery_id
	     *
	     * @return void
	     */
		function render_gallery_blueprint_container( $foogallery_id, $message = null ) {
			$selected_gallery_blueprint_id = $this->get_gallery_blueprint_id( $foogallery_id );
			if ( $selected_gallery_blueprint_id === 0 ) { ?>
				<button class="button button-primary button-large" id="foogallery_gallery_blueprint_toggle"><?php echo $this->get_toggle_button_text( $foogallery_id ); ?></button>
				<?php wp_nonce_field( 'foogallery_gallery_blueprint_toggle', 'foogallery_gallery_blueprint_toggle_nonce', false ); ?>
			<?php
				if ( isset( $message ) ) {
					echo $message;
				}
			?>
			<span class="foogallery_gallery_blueprint_spinner spinner"></span>
			<br />
			<br />
			<?php
			}
			if ( $this->is_gallery_blueprint( $foogallery_id ) ) {
				$galleries_using_blueprint = $this->get_gallery_blueprint_usage( $foogallery_id );
				if ( count ( $galleries_using_blueprint ) === 0 ) {
					echo __( 'To use this Gallery Blueprint, edit another gallery and set the Gallery Blueprint in the Gallery Blueprint Settings metabox.', 'foogallery' );
				} else { ?>
					<p>
						<?php _e( 'This Gallery Blueprint is being used by the following galleries:', 'foogallery' ); ?>
					</p>
					<ul class="ul-disc">
						<?php foreach ( $galleries_using_blueprint as $gallery ) {
							echo '<li><a href="' . esc_url( get_edit_post_link( $gallery->ID ) ) . '" target="_blank">' . $gallery->name . '</a></li>';
						} ?>
					</ul>
				<?php }
			} else {
				$gallery_blueprints = $this->get_all_gallery_blueprints();

				if ( count( $gallery_blueprints ) === 0 ) {
					echo __( 'There are no gallery blueprints available at the moment!', 'foogallery' );
				} else {
					if ( $selected_gallery_blueprint_id === 0 ) {
						_e( 'or', 'foogallery' );
						echo '<br /><br />';
					}
					echo __( 'Choose a Gallery Blueprint : ', 'foogallery' ); ?>
					<select id="foogallery_gallery_blueprint_select"><option><?php _e('None', 'foogallery' ); ?></option>
					<?php foreach ( $this->get_all_gallery_blueprints() as $gallery ) {
						$selected = ( $selected_gallery_blueprint_id === $gallery->ID ) ? ' selected="selected"' : '';
						echo '<option ' . $selected . ' value="' . $gallery->ID . '">' . $gallery->name . ' [' . $gallery->ID . ']</option>';
					} ?>
					</select>
					<button class="button button-primary" id="foogallery_gallery_blueprint_set"><?php _e( 'Set', 'foogallery'); ?></button>
					<?php wp_nonce_field( 'foogallery_gallery_blueprint_set', 'foogallery_gallery_blueprint_set_nonce', false ); ?>
					<span class="foogallery_gallery_blueprint_set_spinner spinner"></span>
					<p>
						<?php _e( 'PLEASE NOTE : If you set a Gallery Blueprint, the following features will be hidden for the gallery : Bulk Copy, Custom CSS, Retina Support, Gallery Sorting. Instead, these settings will be inherited from the Gallery Blueprint.', 'foogallery' ); ?>
					</p>
					<?php
				}
			}
		}

	    /**
	     * AJAX call for handling Gallery Blueprint toggling
	     *
	     * @return void
	     */
	    public function ajax_blueprint_toggle() {
		    if (check_admin_referer('foogallery_gallery_blueprint_toggle', 'foogallery_gallery_blueprint_toggle_nonce')) {
			    $foogallery_id = intval( sanitize_key( $_POST['foogallery'] ) );
				$message = null;
			    if ( $this->is_gallery_blueprint( $foogallery_id ) ) {
				    $galleries_using_blueprint = $this->get_gallery_blueprint_usage( $foogallery_id );
				    if ( count ( $galleries_using_blueprint ) === 0 ) {
					    $this->unset_as_gallery_blueprint( $foogallery_id );
				    } else {
						//do nothing!
					    $message = __( 'The Gallery Blueprint cannot be unset, as it is being used by other galleries!', 'foogallery' );
				    }
			    } else {
					$this->set_as_gallery_blueprint( $foogallery_id );
			    }
				$this->render_gallery_blueprint_container( $foogallery_id, $message );
		    }

		    die();
	    }

	    /**
	     * AJAX call for handling Gallery Blueprint setting
	     *
	     * @return void
	     */
	    public function ajax_blueprint_set() {
		    if (check_admin_referer('foogallery_gallery_blueprint_set', 'foogallery_gallery_blueprint_set_nonce')) {
			    $foogallery_id = intval( sanitize_key( $_POST['foogallery'] ) );
			    $blueprint_foogallery_id = intval( sanitize_key( $_POST['blueprint'] ) );
			    $this->set_gallery_blueprint( $foogallery_id, $blueprint_foogallery_id );
				if ( $blueprint_foogallery_id > 0 ) {
					echo __( 'The Gallery Blueprint has been set.', 'foogallery' );
				} else {
					echo __( 'The Gallery Blueprint has been cleared.', 'foogallery' );
				}

				$foogallery = FooGallery::get_by_id( $foogallery_id );
				if ( $foogallery->is_new() ) {
					echo __(' Update the gallery first, to reflect the changes.', 'foogallery');
				} else {
					echo __(' Refreshing...', 'foogallery');
					echo '<script>location.reload();</script>';
				}
		    }

		    die();
	    }
    }
}