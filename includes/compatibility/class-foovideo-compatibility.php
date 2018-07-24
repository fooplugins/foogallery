<?php
/**
 * Offers FooVideo users a discount on FooGallery PRO
 */
if ( ! class_exists( 'FooGallery_FooVideo_Compatibility' ) ) {

	class FooGallery_FooVideo_Compatibility {

		function __construct() {
			//check if the old FooVideo is still activated
			if ( is_admin() && class_exists( 'Foo_Video' ) ) {
				add_action( 'admin_notices', array( $this, 'display_foovideo_discount_notice') );
				add_action( 'admin_menu',  array( $this, 'add_discount_menu' ) );

				// Ajax calls for migrating
				add_action( 'wp_ajax_foogallery_video_discount_offer', array( $this, 'ajax_foogallery_video_discount_offer' ) );
				add_action( 'wp_ajax_foogallery_video_discount_offer_support', array( $this, 'ajax_foogallery_video_discount_offer_support' ) );
			}
		}

		/**
		 * Display a message if the FooVideo extension is also installed
		 */
		function display_foovideo_discount_notice() {
			if ( 'foogallery' !== foo_current_screen_post_type() ) return;

			$url = admin_url( add_query_arg( array( 'page' => 'foogallery-video-offer' ), foogallery_admin_menu_parent_slug() ) );
			?>
			<div class="notice notice-info">
				<p>
					<strong><?php _e('FooGallery PRO Discount Available!', 'foogallery'); ?></strong><br/>
					<?php _e('We noticed that you own a license for the older FooVideo extension but not for FooGallery PRO, which has all the awesome features of FooVideo, plus more! And because you already own FooVideo, you are eligible for a discount when upgrading to FooGallery PRO.', 'foogallery'); ?><br/>
					<br />
					<a class="button button-primary button-large" href="<?php echo $url; ?>"><?php _e('Redeem your discount now!', 'foogallery'); ?></a>
				</p>
			</div>
			<?php
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
			foogallery_add_submenu_page( __( 'FooGallery PRO Offer', 'foogallery' ), 'manage_options', 'foogallery-video-offer', array( $this, 'render_video_offer_view', ) );
		}

		function ajax_foogallery_video_discount_offer() {
			if ( check_admin_referer( 'foogallery_video_discount_offer' ) ) {
				$license_key = get_site_option( 'foo-video_licensekey' );

				if ( empty( $license_key ) ) {
					_e('There is no FooVideo license key set for this site. Please set it via the FooGallery Settings page under the extensions tab and try again.', 'foogallery');
				} else {
					$license_url = "http://fooplugins.com/api/{$license_key}/licensekey/";

					//fetch the license info from FooPlugins.com
					$response = wp_remote_get( $license_url, array( 'sslverify' => false ) );

					if( ! is_wp_error( $response ) ) {

						if ( $response['response']['code'] == 200 ) {
							$license_details = @json_decode( $response['body'], true );

							if ( isset( $license_details ) ) {
								$license_type = $license_details['license'];
								$expires = $license_details['expires'];

								echo var_dump( $license_details );
							}
						}
					} else {
						echo __('Sorry! There was an error retrieving your discount code from our servers. Please log a support ticket and we will help.', 'foogallery');
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
	}
}