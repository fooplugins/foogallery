<?php
/**
 * FooGallery Pro Master Galleries Class
 */
if ( ! class_exists( 'FooGallery_Pro_Master_Galleries' ) ) {

    class FooGallery_Pro_Master_Galleries {

	    const ENABLED = 'enabled';

        function __construct() {
            // Add the master gallery metabox.
            add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_master_gallery_meta_box_to_gallery' ) );

	        // Ajax handler for toggling the master gallery.
	        add_action( 'wp_ajax_foogallery_master_gallery_toggle', array( $this, 'ajax_master_toggle' ) );

			// Ajax handler for setting the master gallery.
			add_action( 'wp_ajax_foogallery_master_gallery_set', array( $this, 'ajax_master_set' ) );

			// Override the settings metabox.
			add_filter( 'foogallery_should_render_gallery_settings_metabox', array( $this, 'override_settings_metabox'), 10, 2 );
			add_action( 'foogallery_after_render_gallery_settings_metabox', array( $this, 'show_master_gallery_info_in_settings_metabox' ), 10, 1 );

			// Hide other metaboxes when a master gallery is being used.
	        add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'hide_metaboxes' ), 99 );

			// Admin notice for master gallery
	        add_action( 'admin_notices', array( $this, 'display_master_gallery_notice' ) );

			// Override where the settings for a gallery are loaded from. In this case, from the master gallery.
	        add_filter( 'foogallery_load_gallery_settings_id', array( $this, 'load_settings_from_master_gallery' ) );
        }

		function load_settings_from_master_gallery( $foogallery_id ) {
			$master_gallery = $this->get_master_gallery( $foogallery_id );
			if ( $master_gallery !== false ) {
				return $master_gallery->ID;
			}

			return $foogallery_id;
		}

	    /**
	     * Hide the metaboxes which don't make sense, if a master gallery is being used.
	     *
	     * @param $post
	     *
	     * @return void
	     */
		function hide_metaboxes( $post ) {
			$master_gallery = $this->get_master_gallery( $post->ID );
			if ( false !== $master_gallery ) {
				remove_meta_box( 'foogallery_customcss', FOOGALLERY_CPT_GALLERY, 'normal' );

				remove_meta_box( 'foogallery_bulk_copy', FOOGALLERY_CPT_GALLERY, 'normal' );

				remove_meta_box( 'foogallery_retina', FOOGALLERY_CPT_GALLERY, 'side' );

				remove_meta_box( 'foogallery_sorting', FOOGALLERY_CPT_GALLERY, 'side' );

				remove_meta_box( 'foogallery_thumb_settings', FOOGALLERY_CPT_GALLERY, 'side' );
			}
		}

	    /**
	     * Get all galleries that are using the master gallery.
	     *
	     * @param $master_gallery_id
	     *
	     * @return FooGallery[]
	     */
		function get_master_gallery_usage( $master_gallery_id ) {
			return foogallery_get_all_galleries( false, array(
				'meta_key'   => FOOGALLERY_META_MASTER_SET,
				'meta_value' => $master_gallery_id
			) );
		}

	    /**
	     * Display info when editing the master gallery.
	     *
	     * @return void
	     */
	    function display_master_gallery_notice() {
		    global $post;

		    if ( !isset( $post ) ) {
			    return;
		    }

			$foogallery_id = $post->ID;

		    $screen_id = foo_current_screen_id();

		    //only include scripts if we on the foogallery add/edit page
		    if ( FOOGALLERY_CPT_GALLERY === $screen_id ||
		         'edit-' . FOOGALLERY_CPT_GALLERY === $screen_id ) {

				if ( !$this->is_master_gallery( $foogallery_id ) ) {
					return;
				}

			    $gallery_usage_count = count( $this->get_master_gallery_usage( $foogallery_id ) );
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
					    <strong><?php _e( 'Editing Master Gallery!' ) ?></strong>
					    <br/>
					    <?php printf( __( 'PLEASE NOTE : you are editing a master gallery. Editing settings for this master gallery will affect %s galleries. To see which galleries, scroll down to the Master Gallery Settings metabox.', 'foogallery' ), $gallery_usage_count ); ?>
				    </p>
			    </div>
			    <?php
		    }
	    }

	    /**
	     * Show master gallery details in the gallery settings metabox.
	     *
	     * @param $gallery
	     *
	     * @return void
	     */
		function show_master_gallery_info_in_settings_metabox( $gallery ) {
			$master_gallery = $this->get_master_gallery( $gallery->ID );
			if ( false !== $master_gallery ) {
				echo '<div style="margin: 6px 0 0; padding: 0 12px 12px;"><p>';
				echo __( 'All settings for this gallery are currently inherited from the master gallery', 'foogallery' );
				echo ' <strong>' . $master_gallery->name . '</strong>.</p><p>';
				echo __( 'To edit any settings for this gallery, you will need to edit the settings of the master gallery directly.', 'foogallery' );
				echo '</p><p><a target="_blank" href="' . get_edit_post_link( $master_gallery->ID ) . '">' . __( 'Edit the master gallery', 'foogallery');
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
			$master_gallery = $this->get_master_gallery( $gallery->ID );
			if ( false !== $master_gallery ) {
				$show = false;
			}
			return $show;
		}

        /**
         * Add a metabox to the gallery for master galleries
         * @param $post
         */
        function add_master_gallery_meta_box_to_gallery( $post ) {
            add_meta_box(
                'foogallery_master',
                __( 'Master Gallery Settings', 'foogallery' ),
                array( $this, 'render_metabox' ),
                FOOGALLERY_CPT_GALLERY,
                'normal',
                'low'
            );
        }

	    /**
	     * Returns if the gallery is a master gallery
	     *
	     * @param $foogallery_id
	     *
	     * @return bool
	     */
		function is_master_gallery( $foogallery_id ) {
			return get_post_meta( $foogallery_id, FOOGALLERY_META_MASTER_ENABLED, true ) === self::ENABLED;
		}

	    /**
	     * Set the gallery to be a master gallery
	     *
	     * @param $foogallery_id
	     *
	     * @return void
	     */
		function set_as_master_gallery( $foogallery_id ) {
			update_post_meta( $foogallery_id, FOOGALLERY_META_MASTER_ENABLED, self::ENABLED );
		}

	    /**
	     * Unset the gallery to not be a master gallery
	     *
	     * @param $foogallery_id
	     *
	     * @return void
	     */
	    function unset_as_master_gallery( $foogallery_id ) {
		    delete_post_meta( $foogallery_id, FOOGALLERY_META_MASTER_ENABLED );
	    }

	    /**
	     * Return the text of the toggle button.
	     *
	     * @param $foogallery_id
	     *
	     * @return mixed|string|void
	     */
		function get_toggle_button_text( $foogallery_id ) {
			return !$this->is_master_gallery( $foogallery_id ) ? __( 'Set as a Master Gallery', 'foogallery' ) : __( 'Unset Master Gallery', 'foogallery' );
		}

	    /**
	     * Set a master gallery for a gallery
	     *
	     * @param $foogallery_id
	     * @param $master_foogallery_id
	     *
	     * @return void
	     */
	    function set_master_gallery( $foogallery_id, $master_foogallery_id ) {
			if ( $master_foogallery_id > 0 ) {
				update_post_meta( $foogallery_id, FOOGALLERY_META_MASTER_SET, $master_foogallery_id );
			} else {
				delete_post_meta( $foogallery_id, FOOGALLERY_META_MASTER_SET );
			}
	    }

	    /**
	     * Gets the master gallery ID for a foogallery.
	     *
	     * @param $foogallery_id
	     *
	     * @return int
	     */
		function get_master_gallery_id( $foogallery_id ) {
			return intval( get_post_meta( $foogallery_id, FOOGALLERY_META_MASTER_SET, true ) );
		}

	    /**
	     * Gets the master gallery for a foogallery.
	     *
	     * @param $foogallery_id
	     *
	     * @return int
	     */
	    function get_master_gallery( $foogallery_id ) {
			global $foogallery_admin_master_gallery_cache;

			if ( isset( $foogallery_admin_master_gallery_cache ) &&
			     is_array( $foogallery_admin_master_gallery_cache ) && array_key_exists( $foogallery_id, $foogallery_admin_master_gallery_cache ) ) {
				return $foogallery_admin_master_gallery_cache[$foogallery_id];
			}

		    $master_gallery_id = $this->get_master_gallery_id( $foogallery_id );

			if ( $master_gallery_id > 0 ) {
				$master_gallery = FooGallery::get_by_id( $master_gallery_id );
				if ( !isset( $foogallery_admin_master_gallery_cache ) ) {
					$foogallery_admin_master_gallery_cache = array();
				}
				$foogallery_admin_master_gallery_cache[$foogallery_id] = $master_gallery;
				return $master_gallery;
			}

			return false;
	    }

        /**
         * Render the master gallery metabox on the gallery edit page
         * @param $post
         */
        function render_metabox( $post ) {
            ?>
	        <style>
                .foogallery_master_gallery_spinner,
                .foogallery_master_gallery_set_spinner {
                    float: none !important;
                }
	        </style>
            <script type="text/javascript">
                jQuery(function ($) {
	                $('#foogallery_master_gallery_container').on('click', '#foogallery_master_gallery_toggle', function(e) {
		                e.preventDefault();

		                $('.foogallery_master_gallery_spinner').addClass('is-active');
		                var data = 'action=foogallery_master_gallery_toggle' +
		                           '&foogallery=<?php echo $post->ID; ?>' +
		                           '&foogallery_master_gallery_toggle_nonce=' + $('#foogallery_master_gallery_toggle_nonce').val() +
		                           '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

		                $.ajax({
			                type: "POST",
			                url: ajaxurl,
			                data: data,
			                success: function(data) {
				                $('#foogallery_master_gallery_container').html(data);
			                }
		                });
	                });

	                $('#foogallery_master_gallery_container').on('click', '#foogallery_master_gallery_set', function(e) {
		                e.preventDefault();

		                $('.foogallery_master_gallery_set_spinner').addClass('is-active');
		                var data = 'action=foogallery_master_gallery_set' +
		                           '&foogallery=<?php echo $post->ID; ?>' +
		                           '&master=' + $('#foogallery_master_gallery_select').val() +
		                           '&foogallery_master_gallery_set_nonce=' + $('#foogallery_master_gallery_set_nonce').val() +
		                           '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

		                $.ajax({
			                type: "POST",
			                url: ajaxurl,
			                data: data,
			                success: function(data) {
				                $('#foogallery_master_gallery_container').html(data);
			                }
		                });
	                });
                });
            </script>
            <div>
                <p class="foogallery-help"><?php _e('Master galleries allow you to setup galleries that other galleries inherit all settings from. When you change the settings on a master gallery, all galleries that use that master gallery will be updated. Think of a master gallery as a "template". Any gallery using a master will not be able to set its own settings, as everything is inherited from the master. This allows you to update settings once, and affect multiple galleries.', 'foogallery'); ?></p>
            </div>
            <br/>
            <div id="foogallery_master_gallery_container">
                <?php $this->render_master_gallery_container( $post->ID ); ?>
            </div>
            <?php
        }

	    /**
	     * Returns all master galleries
	     *
	     * @return FooGallery[] array of master FooGallery galleries
	     */
	    function get_all_master_galleries() {
		    return foogallery_get_all_galleries( false, array(
			    'meta_key'   => FOOGALLERY_META_MASTER_ENABLED,
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
		function render_master_gallery_container( $foogallery_id, $message = null ) {
			$selected_master_gallery_id = $this->get_master_gallery_id( $foogallery_id );
			if ( $selected_master_gallery_id === 0 ) { ?>
				<button class="button button-primary button-large" id="foogallery_master_gallery_toggle"><?php echo $this->get_toggle_button_text( $foogallery_id ); ?></button>
				<?php wp_nonce_field( 'foogallery_master_gallery_toggle', 'foogallery_master_gallery_toggle_nonce', false ); ?>
			<?php
				if ( isset( $message ) ) {
					echo $message;
				}
			?>
			<span class="foogallery_master_gallery_spinner spinner"></span>
			<br />
			<br />
			<?php
			}
			if ( $this->is_master_gallery( $foogallery_id ) ) {
				$galleries_using_master = $this->get_master_gallery_usage( $foogallery_id );
				if ( count ( $galleries_using_master ) === 0 ) {
					echo __( 'To use this master gallery, edit another gallery and set the master gallery in the Master Gallery Settings metabox.', 'foogallery' );
				} else { ?>
					<p>
						<?php _e( 'This master gallery is being used by the following galleries:', 'foogallery' ); ?>
					</p>
					<ul class="ul-disc">
						<?php foreach ( $galleries_using_master as $gallery ) {
							echo '<li><a href="' . esc_url( get_edit_post_link( $gallery->ID ) ) . '" target="_blank">' . $gallery->name . '</a></li>';
						} ?>
					</ul>
				<?php }
			} else {
				$master_galleries = $this->get_all_master_galleries();

				if ( count( $master_galleries ) === 0 ) {
					echo __( 'There are no master galleries available at the moment!', 'foogallery' );
				} else {
					if ( $selected_master_gallery_id === 0 ) {
						_e( 'or', 'foogallery' );
						echo '<br /><br />';
					}
					echo __( 'Choose a master gallery : ', 'foogallery' ); ?>
					<select id="foogallery_master_gallery_select"><option></option>
					<?php foreach ( $this->get_all_master_galleries() as $gallery ) {
						$selected = ( $selected_master_gallery_id === $gallery->ID ) ? ' selected="selected"' : '';
						echo '<option ' . $selected . ' value="' . $gallery->ID . '">' . $gallery->name . ' [' . $gallery->ID . ']</option>';
					} ?>
					</select>
					<button class="button button-primary" id="foogallery_master_gallery_set"><?php _e( 'Set', 'foogallery'); ?></button>
					<?php wp_nonce_field( 'foogallery_master_gallery_set', 'foogallery_master_gallery_set_nonce', false ); ?>
					<span class="foogallery_master_gallery_set_spinner spinner"></span>
					<p>
						<?php _e( 'PLEASE NOTE : If you set a master gallery, the following features will be hidden for the gallery : Bulk Copy, Custom CSS, Retina Support, Gallery Sorting. Instead, these settings will be inherited from the master gallery.', 'foogallery' ); ?>
					</p>
					<?php
				}
			}
		}

	    /**
	     * AJAX call for handling master gallery toggling
	     *
	     * @return void
	     */
	    public function ajax_master_toggle() {
		    if (check_admin_referer('foogallery_master_gallery_toggle', 'foogallery_master_gallery_toggle_nonce')) {
			    $foogallery_id = intval( sanitize_key( $_POST['foogallery'] ) );
				$message = null;
			    if ( $this->is_master_gallery( $foogallery_id ) ) {
				    $galleries_using_master = $this->get_master_gallery_usage( $foogallery_id );
				    if ( count ( $galleries_using_master ) === 0 ) {
					    $this->unset_as_master_gallery( $foogallery_id );
				    } else {
						//do nothing!
					    $message = __( 'The master gallery cannot be unset, as it is being used by other galleries!', 'foogallery' );
				    }
			    } else {
					$this->set_as_master_gallery( $foogallery_id );
			    }
				$this->render_master_gallery_container( $foogallery_id, $message );
		    }

		    die();
	    }

	    /**
	     * AJAX call for handling master gallery setting
	     *
	     * @return void
	     */
	    public function ajax_master_set() {
		    if (check_admin_referer('foogallery_master_gallery_set', 'foogallery_master_gallery_set_nonce')) {
			    $foogallery_id = intval( sanitize_key( $_POST['foogallery'] ) );
			    $master_foogallery_id = intval( sanitize_key( $_POST['master'] ) );
			    $this->set_master_gallery( $foogallery_id, $master_foogallery_id );
				if ( $master_foogallery_id > 0 ) {
					echo __( 'The master gallery has been set.', 'foogallery' );
				} else {
					echo __( 'The master gallery has been cleared.', 'foogallery' );
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