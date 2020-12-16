<?php
/**
 * Offers FooVideo users a discount on FooGallery PRO
 */
if ( ! class_exists( 'FooGallery_FooVideo_Compatibility' ) ) {

	class FooGallery_FooVideo_Compatibility {

		const option_discount_key = 'foogallery_video_discount';

		function __construct() {
			//check if the old FooVideo is still activated
			if ( is_admin() && $this->show_discount_message() ) {
				add_action( 'admin_notices', array( $this, 'display_discount_notice') );
				add_action( 'admin_menu',  array( $this, 'add_discount_menu' ) );

				// Ajax calls
				add_action( 'wp_ajax_foogallery_video_discount_offer', array( $this, 'ajax_foogallery_video_discount_offer' ) );
				add_action( 'wp_ajax_foogallery_video_discount_offer_support', array( $this, 'ajax_foogallery_video_discount_offer_support' ) );
				add_action( 'wp_ajax_foogallery_video_discount_offer_hide', array( $this, 'ajax_foogallery_video_discount_offer_hide' ) );

				add_action( 'wp_ajax_foogallery_video_discount_dismiss', array( $this, 'admin_notice_dismiss' ) );
			}
		}

		/**
		 * Determines if the discount message should be shown
		 *
		 * @return bool
		 */
		function show_discount_message() {
			//first try to get the saved option
			$show_message = get_option( FooGallery_FooVideo_Compatibility::option_discount_key, 0 );

			if ( '3' === $show_message ) {
				return false;
			}

			//we must show the message - get out early
			if ( 0 !== $show_message ) {
				return true;
			}

			if ( class_exists('Foo_Video') ) {
				//the legacy plugin is installed, so set the option for future use
				$show_message = true;

				update_option( FooGallery_FooVideo_Compatibility::option_discount_key, '1' );
			}

			//we have no option saved and no legacy plugin, so no discount available
			if ( 0 === $show_message ) {
				$show_message = false;
			}

			return $show_message;
		}

		/**
		 * Display a message if the FooVideo extension is also installed
		 */
		function display_discount_notice() {
			$show_message = get_option( FooGallery_FooVideo_Compatibility::option_discount_key, 0 );
			if ( '1' === $show_message ) {

				$notice_title   = apply_filters( 'foogallery_foovideo_discount_offer_notice_title', __( 'FooGallery PRO Discount Available!', 'foogallery' ) );
				$notice_message = apply_filters( 'foogallery_foovideo_discount_offer_notice_message', __( 'We noticed that you own a license for the older FooVideo extension but not for FooGallery PRO, which has all the awesome features of FooVideo, plus more! And because you already own FooVideo, you are eligible for a discount when upgrading to FooGallery PRO.', 'foogallery' ) );

				$url = admin_url( add_query_arg( array( 'page' => 'foogallery-video-offer' ), foogallery_admin_menu_parent_slug() ) );
				?>
				<script type="text/javascript">
					(function ($) {
						$(document).ready(function () {
							$('.foogallery-foovideo-discount-notice.is-dismissible')
								.on('click', '.notice-dismiss', function (e) {
									e.preventDefault();
									$.post(ajaxurl, {
										action  : 'foogallery_video_discount_dismiss',
										url     : '<?php echo admin_url( 'admin-ajax.php' ); ?>',
										_wpnonce: '<?php echo wp_create_nonce( 'foogallery_video_discount_dismiss' ); ?>'
									});
								});
						});
					})(jQuery);
				</script>
				<div class="foogallery-foovideo-discount-notice notice notice-info is-dismissible">
					<p>
						<strong><?php echo $notice_title; ?></strong><br />
						<?php echo $notice_message; ?><br />
						<br />
						<a class="button button-primary button-large" href="<?php echo $url; ?>"><?php _e( 'Redeem Now!', 'foogallery' ); ?></a>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Dismiss the admin notice
		 */
		function admin_notice_dismiss() {
			if ( check_admin_referer( 'foogallery_video_discount_dismiss' ) ) {
				update_option( FooGallery_FooVideo_Compatibility::option_discount_key, '2' );
			}
		}

		/**
		 * Outputs the video discount offer view
		 */
		function render_video_offer_view() {
			require_once 'view-foovideo-offer.php';
		}

		/**
		 * Add a new menu item for running the migration
		 */
		function add_discount_menu() {
			$menu = apply_filters( 'foogallery_foovideo_discount_offer_menu', __( 'Discount Offer', 'foogallery' ) );

			foogallery_add_submenu_page( $menu, 'manage_options', 'foogallery-video-offer', array( $this, 'render_video_offer_view', ) );
		}

		function ajax_foogallery_video_discount_offer() {
			if ( check_admin_referer( 'foogallery_video_discount_offer' ) ) {
				$license_key = get_site_option( 'foo-video_licensekey' );

				if ( empty( $license_key ) ) {
					echo '<h3>' . __( 'No FooVideo License Found!', 'foogallery' ) . '</h3>';
					$settings_link = '<a target="_blank" href="' . foogallery_admin_settings_url() . '#extensions">' . __('FooGallery Settings page', 'foogallery') . '</a>';
					echo '<h4>' . sprintf( __( 'There is no FooVideo license key set for this site. Please set it via the %s under the extensions tab and try again.', 'foogallery' ), $settings_link ) . '</h4>';
				} else {
					$license_url = "https://fooplugins.com/api/{$license_key}/licensekey/";

					//fetch the license info from FooPlugins.com
					$response = wp_remote_get( $license_url, array( 'sslverify' => false ) );

					if( ! is_wp_error( $response ) ) {

						if ( $response['response']['code'] == 200 ) {
							$license_details = @json_decode( $response['body'], true );

							if ( isset( $license_details ) ) {
								$coupon = $license_details['coupon'];

								if ( $coupon['valid'] ) {
									echo '<h3>' . __( 'Your discount code is : ', 'foogallery' ) . $coupon['code'] . '</h3>';
									echo '<h4>' . __( 'The value of the discount is : ', 'foogallery' ) . $coupon['value'] . '</h4>';

									$license_option = __( 'Single Site', 'foogallery' );
									if ( 'FooVideo Extension (Multi)' === $license_details['license'] ) {
										$license_option = __( '5 Site', 'foogallery' );
									} else if ( 'FooVideo Extension (Business)' === $license_details['license'] ) {
										$license_option = __( '25 Site', 'foogallery' );
									}
									$license_option = '<strong>' . $license_option . '</strong>';
									$pricing_page_url  = foogallery_admin_pricing_url();
									$pricing_page_text = apply_filters( 'foogallery_foovideo_pricing_menu_text', __( 'FooGallery -> Upgrade', 'foogallery' ) );
									$pricing_page_link = '<a target="_blank" href="' . $pricing_page_url . '">' . $pricing_page_text . '</a>';

									if ( !class_exists( 'FooGallery_Pro_Video' ) ) {
										echo sprintf( __( 'Your discount entitles you to a FooGallery PRO - %s license for no additional cost!', 'foogallery' ), $license_option );
										echo '<br />' . sprintf( __( 'Copy the discount code above and use it when purchasing FooGallery PRO from %s (make sure to select %s plan!).', 'foogallery' ), $pricing_page_link, $license_option );
									} else {
										echo sprintf( __( 'Your discount entitles you to a free FooGallery PRO - %s license renewal or extension!', 'foogallery' ), $license_option );
										echo '<br />' . sprintf( __( 'Copy the discount code above and use it when extending your FooGallery PRO license from %s (make sure to select the %s plan!).', 'foogallery' ), $pricing_page_link, $license_option );
									}
									$doc_link = '<a href="https://fooplugins.link/foovideo-upgrade" target="_blank">' . __( 'read our documentation', 'foogallery' ) . '</a>';
									echo '<br />' . sprintf( __( 'For a more detailed guide on the process, %s.', 'foogallery' ), $doc_link );

									//redeemed the code - no need to show the admin notice anymore
									update_option( FooGallery_FooVideo_Compatibility::option_discount_key, '2' );
								} else {
									echo '<h3>' . __( 'Invalid License!', 'foogallery' ) . '</h3>';
									echo '<h4>' .$coupon['code'] . '</h4>';
								}

							}
						}
					} else {
						echo '<h4>'. __('Sorry! There was an error retrieving your discount code from our servers. Please log a support ticket and we will help.', 'foogallery') . '</h4>';
					}
				}
			}
			die();
		}

		function ajax_foogallery_video_discount_offer_support() {
			if ( check_admin_referer( 'foogallery_video_discount_offer_support' ) ) {
				//send the support email!
				$message = $_POST['message'];
				if ( wp_mail( 'support@fooplugins.com', 'FooGallery/FooVideo Discount Offer Query', $message ) ) {
					echo __('Support email logged successfully!', 'foogallery' );
				} else {
					echo __('We could not log the ticket. Please email support@fooplugins.com directly.', 'foogallery' );
				}
			}
			die();
		}

		function ajax_foogallery_video_discount_offer_hide() {
			if ( check_admin_referer( 'foogallery_video_discount_offer_hide' ) ) {
				update_option( FooGallery_FooVideo_Compatibility::option_discount_key, '3' );
			}
			die();
		}
	}
}