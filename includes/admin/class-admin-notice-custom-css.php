<?php
/*
 * FooGallery Admin Custom CSS Notice class
 */

if ( ! class_exists( 'FooGallery_Admin_Notice_CustomCSS' ) ) {

	class FooGallery_Admin_Notice_CustomCSS {

        private $option_name = FOOGALLERY_OPTION_CUSTOM_CSS;
        private $notice_id = 'customcss';
        private $notice_class = 'notice-info';
        private $css_classes_to_check = array( 'fg-caption' );

		public function __construct() {
            // We only need to check this after v2.5.0
            if ( version_compare( FOOGALLERY_VERSION, '2.5.0', '<' ) ) {
                return;
            }

            // display the notice
            add_action( 'admin_notices', array( $this, 'display_notice' ) );
            // ajax handler to dismiss the notice
			add_action( 'wp_ajax_foogallery_admin_notice_dismiss-' . $this->notice_id, array( $this, 'dismiss_notice' ) );
            // clear the saved data if a gallery is saved, or settings are updated
            add_action( 'foogallery_after_save_gallery', array( $this, 'clear_saved_data' ) );
            add_action( 'update_option_foogallery', array( $this, 'after_settings_updated_clear_saved_data' ), 10, 3 );
            // override the settings
            add_filter( 'foogallery_admin_settings_override', array( $this, 'override_settings' ) );
            //render the custom css settings
            add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_settings' ) );

		}

        /**
         * Override the settings
         */
        function override_settings( $settings ) {
            $option = get_option( $this->option_name );

            if ( is_array( $option ) && count( $option ) > 0 ) {

                $index = foogallery_admin_fields_find_index_of_field( $settings['settings'], 'custom_js' );

                $new_settings[] = array(
                    'id'      => 'custom_css_update',
                    'title'   => __( 'Custom CSS Update!', 'foogallery' ),
                    'desc'    => __( 'We found outdated custom CSS that needs to be updated. Since FooGallery update v2.5, custom CSS related to captions (specifically when using `fg-caption`), has changed. You will need to update your custom CSS. Please contact support if you need assistance.', 'foogallery' ),
                    'type'    => 'custom_css_update',
                    'tab'     => 'custom_assets',
                );

                array_splice( $settings['settings'], $index, 0, $new_settings );
            }

            return $settings;
        }

        /**
		 * Render any custom setting types to the settings page
		 */
		function render_settings( $args ) {
			if ('custom_css_update' === $args['type'] ) { 
                $option = get_option( $this->option_name );

                if ( is_array( $option ) && count( $option ) > 0 ) {
                    $galleries = $option['galleries'];
                    if ( count( $galleries ) > 0 ) {
                        ?>
                        <p><?php _e( 'You will need to update the custom CSS on the following galleries:', 'foogallery' ); ?></p>
                        <ul class="ul-disc">
                            <?php foreach ( $galleries as $gallery_id => $gallery_name ) { ?>
                                <li><a href="<?php echo get_edit_post_link( $gallery_id ); ?>"><?php echo $gallery_name; ?></a></li>
                            <?php } ?>
                        </ul>
                        <?php
                    }
                    if ( $option['setting'] ) {
                        ?>
                        <p><?php _e( 'You have outdated custom CSS saved in the Custom Stylesheet setting below that needs to be updated.', 'foogallery' ); ?></p>
                        <?php
                    }
                }
            }
		}

        function after_settings_updated_clear_saved_data( $old_value, $value, $option ) {
            if ( !is_admin() ) {
				return;
			}

			if ( !current_user_can( 'manage_options' ) ) {
				return;
			}

            $this->clear_saved_data();
        }

        /**
         * Clear the saved data if a gallery is saved. 
         * This ensures that the notice is only shown if the user has custom CSS that meets the requirements.
         */
        function clear_saved_data() {
            delete_option( $this->option_name );
        }

        /**
         * Display the admin notice.
         */
		function display_notice() {
			if ( $this->should_show_notice() ) {
				?>
                <script type="text/javascript">
					(function( $ ) {
						$( document ).ready( function() {
							$( '.foogallery-admin-notice-<?php echo $this->notice_id; ?>.is-dismissible' )
								.on( 'click', '.notice-dismiss', function( e ) {
									e.preventDefault();
									$.post( ajaxurl, {
										action: 'foogallery_admin_notice_dismiss-<?php echo $this->notice_id; ?>',
										url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
										_wpnonce: '<?php echo wp_create_nonce( 'foogallery_admin_notice_dismiss-' . $this->notice_id ); ?>'
									} );
								} );
						} );
					})( jQuery );
				</script>
				<div class="notice <?php echo $this->notice_class; ?> is-dismissible foogallery-admin-notice-<?php echo $this->notice_id; ?>">
					<?php $this->display_notice_inner_html(); ?>
				</div>
				<?php
			}
		}

        /**
         * Display the inner HTML of the admin notice.
         */
        function display_notice_inner_html() {
            $option = get_option( $this->option_name );
            $url = foogallery_admin_settings_url() . '#custom_assets';
            $link = '<a href="'. $url . '">' . __( 'FooGallery Settings', 'foogallery' ) . '</a>';
            if ( is_array( $option ) && count( $option ) > 0 ) { 
            ?>
            <h3><?php _e( 'FooGallery - Custom CSS Update Required', 'foogallery' ); ?></h3>
            <?php
                $galleries = $option['galleries'];
                if ( count( $galleries ) > 0 ) {
                    ?>
                    <p><?php printf( __( 'We found outdated custom CSS in %s galleries that needs to be updated.', 'foogallery' ), count( $galleries ) ); ?></p>
                    <?php
                }
                if ( $option['setting'] ) {
                    ?>
                    <p><?php printf( __( 'You have outdated custom CSS saved in settings that needs to be updated.', 'foogallery' ) ); ?></p>
                    <?php
                }
            ?>
            <p><?php printf( __( 'You will need to update your custom CSS! Visit %s to see what needs updating', 'foogallery' ), $link ); ?></p>
            <?php
            }
        }

        function should_show_notice() {
			$option = get_option( $this->option_name );

			if ( $option === false ) {
				// we have never checked CSS, so determine it.
				$this->determine_and_save_option();
				$option = get_option( $this->option_name );
			}

            if ( $option === false ) {
                // It is still false for some reason, so rather be safe and not show.
                return false;
            } else if ( 'clear' === $option ) {
                // we have previously checked and the results were clear.
				return false;
			} else if ( 'hide' === $option ) {
                // the user has hidden the notice. Never show!
				return false;
			}

			// If we get here, then the notice should be shown.
			return true;
		}

        /**
         * Ajax handler to dismiss the notice.
         */
        function dismiss_notice() {
            if ( check_admin_referer( 'foogallery_admin_notice_dismiss-' . $this->notice_id ) ) {
                update_option( $this->option_name, 'hide', false );
            }
        }

        /**
         * Determine and save the option.
         */ 
        function determine_and_save_option() {
            
            $galleries = foogallery_get_all_galleries();

            $option = array(
                'galleries' => array(),
                'setting' => false
            );

            $galleries_with_css = array();
            $custom_css_setting_contains = false;

            if ( ! empty( $galleries ) ) {
                foreach ( $galleries as $gallery ) {
                    $custom_css = get_post_meta( $gallery->ID, FOOGALLERY_META_CUSTOM_CSS, true );

                    if ( $this->does_contain_css_classes( $custom_css ) ) {
                        $galleries_with_css[$gallery->ID] = $gallery->name;
                    }
                }
            }

            $custom_css_setting = foogallery_get_setting( 'custom_css' );

            if ( !empty( $custom_css_setting ) ) {
                if ( $this->does_contain_css_classes( $custom_css_setting ) ) {
                    $custom_css_setting_contains = true;
                }
            }

            if ( count( $galleries_with_css ) > 0  || $custom_css_setting_contains ) {
                $option['galleries'] = $galleries_with_css;
                $option['setting'] = $custom_css_setting_contains;
                update_option( $this->option_name, $option, false );
            } else {
                update_option( $this->option_name, 'clear', false );
            }
        }

        /**
         * Check if the CSS contains any of the classes we are looking for.
         */
        function does_contain_css_classes( $css ) {
            foreach ( $this->css_classes_to_check as $class ) {
                if ( strpos( $css, $class ) !== false ) {
                    return true;
                }
            }
            return false;
        }
    }
}