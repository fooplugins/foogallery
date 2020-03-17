<?php
/*
 * FooGallery Admin Notices class
 */

if ( ! class_exists( 'FooGallery_Admin_Notices' ) ) {

    class FooGallery_Admin_Notices {

        public function __construct() {
            add_action( 'admin_notices', array( $this, 'display_thumb_test_notice') );
			add_action( 'admin_notices', array( $this, 'display_rating_notice') );
            add_action( 'foogallery_thumbnail_generation_test', array( $this, 'save_test_results') );

			add_action( 'wp_ajax_foogallery_admin_rating_notice_dismiss', array( $this, 'admin_rating_notice_dismiss' ) );
        }

        function should_run_tests() {
            $option = get_option( FOOGALLERY_OPTION_THUMB_TEST );
            $option_value = $this->generate_option_value();

            if ( !isset( $option ) ) {
                //we have never run tests before
                return true;
            } else {
                $option_key = $option['key'];
                if ( $option_value !== $option_key ) {
                    //either the PHP version or Host has changed. In either case, we should run tests again!
                    return true;
                }
            }

            return false;
        }

        function should_show_alert() {
            $option = get_option( FOOGALLERY_OPTION_THUMB_TEST );

            if ( isset( $option ) && array_key_exists( 'results', $option ) ) {
                $results = $option['results'];
                //should show the alert if the tests were not a success
                return !$results['success'];
            }

            return false;
        }

        function generate_option_value() {
            $php_version = phpversion();
            $host = home_url();
            return "php$($php_version}-{$host}";
        }

        function save_test_results($results) {
            update_option( FOOGALLERY_OPTION_THUMB_TEST, array (
                'key' => $this->generate_option_value(),
                'results' => $results
            ));
        }

		/**
		 * Dismiss the admin rating notice forever
		 */
		function admin_rating_notice_dismiss() {
			if ( check_admin_referer( 'foogallery_admin_rating_notice_dismiss' ) ) {
				update_option( 'foogallery_admin_rating_notice_dismiss', 'hide' );
			}
		}

        function should_show_rating_message() {
			//first try to get the saved option
			$show_message = get_option( 'foogallery_admin_rating_notice_dismiss', 0 );

			if ( 'hide' === $show_message ) {
				return false; //never show - user has dismissed
			}

			if ( 'show' === $show_message ) {
				return true; //always show - user has created 5 or more galleries
			}


			//we must show the message - get out early
			if ( 0 === $show_message ) {
				$gallery_count = count( get_posts( array(
					'post_type'     => FOOGALLERY_CPT_GALLERY,
					'post_status'	=> array( 'publish', 'draft' ),
					'cache_results' => false,
					'nopaging'      => true,
				) ) );

				if ( $gallery_count >= 5 ) {
					update_option( 'foogallery_admin_rating_notice_dismiss', 'show' );
				}
			}
		}

		function display_rating_notice() {
			if ( $this->should_show_rating_message() ) {

				$url = 'https://fooplugins.link/please-rate-foogallery';
				?>
				<script type="text/javascript">
					(function ($) {
						$(document).ready(function () {
							$('.foogallery-rating-notice.is-dismissible')
								.on('click', '.notice-dismiss', function (e) {
									e.preventDefault();
									$.post(ajaxurl, {
										action  : 'foogallery_admin_rating_notice_dismiss',
										url     : '<?php echo admin_url( 'admin-ajax.php' ); ?>',
										_wpnonce: '<?php echo wp_create_nonce( 'foogallery_admin_rating_notice_dismiss' ); ?>'
									});
								});
						});
					})(jQuery);
				</script>
				<style>
					.foogallery-rating-notice {
						border-left-color: #ff69b4;
					}

					.foogallery-rating-notice .dashicons-heart {
						color: #ff69b4;
					}
				</style>
				<div class="foogallery-rating-notice notice notice-success is-dismissible">
					<p>
						<strong><?php _e('Thanks for using FooGallery') ?> <span class="dashicons dashicons-heart"></span></strong><br />
						<?php _e('We noticed you have created 5 galleries in FooGallery. If you love FooGallery, please consider giving it a 5 star rating on WordPress.org. Your positive ratings help spread the word and help us grow.', 'foogallery'); ?><br />
						<br/>
						<a class="button button-primary button-large" target="_blank" href="<?php echo $url; ?>"><?php _e( 'Rate FooGallery on WordPress.org', 'foogallery' ); ?></a>
					</p>
				</div>
				<?php
			}
		}

        function display_thumb_test_notice() {
            //check if we are on specific admin pages
            if ( FOOGALLERY_CPT_GALLERY === foo_current_screen_post_type() ) {

                if ($this->should_run_tests()) {
                    $thumbs = new FooGallery_Thumbnails();
                    $thumbs->run_thumbnail_generation_tests();
                }

                if ($this->should_show_alert()) {
                    ?>
                    <div class="notice error">
                        <p>
                            <strong><?php _e('Thumbnail Generation Alert!', 'foogallery'); ?></strong><br/>
                            <?php _e('There is a problem generating thumbnails for your galleries. There could be a number of reasons which could cause this problem.' , 'foogallery'); ?><br />
                            <?php _e('If thumbnails cannot be generated, then full-sized, uncropped images will be used instead. This will result in slow page load times, and thumbnails that do not look correct.', 'foogallery'); ?><br/>
                            <a target="_blank" href="https://fooplugins.com/documentation/foogallery/troubleshooting-foogallery/thumbnail-generation-alert-help/"><?php _e('View Troubleshooting Documentation', 'foogallery'); ?></a>
                            <br/>
                        </p>
                    </div>
                    <?php
                }
            }
        }
    }

}