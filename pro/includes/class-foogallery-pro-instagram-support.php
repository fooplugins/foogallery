<?php
/**
 * FooGallery Pro support for Instagram Sync
 */
if ( ! class_exists( 'FooGallery_Pro_Instagram_Support' ) ) {

	class FooGallery_Pro_Instagram_Support {

		function __construct() {
			//add some settings for Instagram
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_instagram_settings' ) );
			add_action( 'admin_init', array( $this,'intercept_instagram_authorization' ) );

			add_action( 'wp_ajax_foogallery_instagram_disconnect', array( $this, 'ajax_handle_disconnect') );
        }

        function get_redirect_url() {
	        return add_query_arg( 'foogallery_instagram', 'auth', admin_url() );
        }
		
		/**
		 * Add some Instagram settings
		 * @param $settings
		 *
		 * @return array
		 */
		function add_instagram_settings( $settings ) {

			$settings['tabs']['instagram'] = __( 'Instagram', 'foogallery' );
			$redirect_url = $this->get_redirect_url();
			$instagram_token_raw = get_option( 'foogallery_instagram_token' );
			$instagram_token = @json_decode( $instagram_token_raw, true );

			if ( isset( $instagram_token ) && isset( $instagram_token['user']['username'] ) ) {

				$html = '<script type="text/javascript">
					jQuery( function($) {
				        $(".foogallery_instagram_disconnect").click(function(e) {
				            if ( confirm("' . __('Are you sure? There is no undo!', 'foogallery') . '") ) {
			                    e.preventDefault();
		
		                        var $button = $(this),
		                            $container = $("#foogallery_instagram_disconnect_container"),
		                            $spinner = $("#foogallery_instagram_disconnect_spinner"),
		                            data = "action=foogallery_instagram_disconnect" +
		                                "&_wpnonce=" + $button.data("nonce") +
		                                "&_wp_http_referer=" + encodeURIComponent($("input[name=\"_wp_http_referer\"]").val());

		                        $spinner.addClass("is-active");
		                        $button.prop("disabled", true);

					            $.ajax({
					                type: "POST",
					                url: ajaxurl,
					                data: data,
					                success: function(data) {
					                    $container.html(data);
					                },
					                complete: function() {
					                    $spinner.removeClass("is-active");
					                }
					            });
				            }
	                    });
    				});
				</script>
				<div id="foogallery_instagram_disconnect_container">
					' . $instagram_token['user']['username'] . '
					&nbsp;(<a href="#disconnent" data-nonce="' . esc_attr( wp_create_nonce( 'foogallery_instagram_disconnect' ) ) . '" class="foogallery_instagram_disconnect">' . __( 'disconnect', 'foogallery' ) . '</a>)
					<span id="foogallery_instagram_disconnect_spinner" style="position: absolute" class="spinner"></span>
				</div>';

				$settings['settings'][] = array(
					'id'      => 'instagram_token',
					'title'   => __( 'Connected Instagram Account', 'foogallery' ),
					'type'    => 'html',
					'desc'    => $html,
					'tab'     => 'instagram'
				);

			} else {

				$synced = get_option( 'foogallery' );

				$show_get_access_token_button = isset( $synced['insta_client_id'] ) && $synced['insta_client_id'] != '';

				$instagram_desc_setting = array(
					'id'    => 'insta_desc',
					'title' => __( 'You are not connected!', 'foogallery' ),
					'type'  => 'html',
					'desc'  => __('You do not have a connected Instagram account. To connect an Instagram account, you will need to follow the steps below:', 'foogallery') .
					           '<ol>
	<li>' . sprintf(__('Visit %s.', 'foogallery'), '<a href="https://www.instagram.com/developer/" target="_blank">instagram.com/developer</a>') . '</li>
	<li>' . __('Login using your Instagram credentials.' , 'foogallery') . '</li>
	<li>' . __('Click on "Manage Clients" in the top bar, which will list all your registered clients.' , 'foogallery') . '</li>
	<li>' . __('If you have no registered clients, then click on "Register a New Client".' , 'foogallery') . '</li>
	<li>' . __('Enter the Application Name, Description, Website URL for your new client. These values can be whatever you think fits.' , 'foogallery') . '</li>
	<li>' . __('Make sure you enter the following for "Valid redirect URLs" : ' , 'foogallery') . '<code>' . $redirect_url . '</code></li>
	<li>' . __('Click "Register", and your new client will be created.' , 'foogallery') . '</li>
	<li>' . __('Click the "Manage" button for the newly registered client.' , 'foogallery') . '</li>
	<li>' . __('Copy the "Client ID" and paste it into the "Instagram Client ID" input below.' , 'foogallery') . '</li>
	<li>' . __('Copy the "Client Secret" and paste it into the "Instagram Client Secret" input below.' , 'foogallery') . '</li>
	<li>' . __('Click "Save Changes".' , 'foogallery') . '</li>
</ol>',
					'tab'   => 'instagram'
				);

				if ( $show_get_access_token_button ) {
					$instagram_desc_setting['desc'] = '<strong>' . __('You are almost there! Just a couple more steps to connect your Instagram account:', 'foogallery') .
'</strong><ol>
	<li>'. __('Click the "Get Access Token" button below.' , 'foogallery') . '</li>
	<li>'. __('You will be redirected to the Instagram website to authorize.' , 'foogallery') . '</li>
	<li>'. __('Once authorized, you will be redirected back to this page and the account should be connected.' , 'foogallery') . '</li>
</ol>';
				}

				$settings['settings'][] = $instagram_desc_setting;

				$settings['settings'][] = array(
					'id'    => 'insta_client_id',
					'title' => __( 'Instagram Client ID', 'foogallery' ),
					'type'  => 'text',
					'desc'  => __( 'Please enter your Instagram Client ID. You can get this from ', 'foogallery' ),
					'tab'   => 'instagram'
				);

				$settings['settings'][] = array(
					'id'    => 'insta_client_secret',
					'title' => __( 'Instagram Client Secret', 'foogallery' ),
					'type'  => 'text',
					'desc'  => 'Please enter Instagram Client Secret',
					'tab'   => 'instagram'
				);



				if ( $show_get_access_token_button ) {

					$sync_data = '<a class="button-secondary" href="https://api.instagram.com/oauth/authorize/?client_id=' . $synced['insta_client_id'] . '&redirect_uri=' . $redirect_url  . '&response_type=code">Get Access Token</a>';

					$settings['settings'][] = array(
						'id'    => 'insta_synced',
						'title' => __( 'Get Token', 'foogallery' ),
						'type'  => 'html',
						'desc'  => $sync_data,
						'tab'   => 'instagram'
					);
				}
			}

			return $settings;
		}

		/**
		 * AJAX endpoint for testing thumbnail generation using WPThumb
		 */
		function ajax_handle_disconnect() {
			if ( check_admin_referer( 'foogallery_instagram_disconnect' ) ) {
				delete_option( 'foogallery_instagram_token' );
				echo __('The Instagram account has been disconnected.', 'foogallery');
				echo '<script>window.location.reload(); </script>';
				die();
			}
		}

		function intercept_instagram_authorization(){
			if ( isset( $_GET['foogallery_instagram'] ) && $_GET['foogallery_instagram'] === 'auth' ) {
				if ( isset( $_GET['code'] ) && $_GET['code'] != '' ) {
					$client_id = foogallery_get_setting( 'insta_client_id' );
					$client_secret = foogallery_get_setting( 'insta_client_secret' );
					if ( $client_id !== '' && $client_secret !== '' ) {
						$fields       = array(
							'client_id'     => $client_id,
							'client_secret' => $client_secret,
							'grant_type'    => 'authorization_code',
							'redirect_uri'  => $this->get_redirect_url(),
							'code'          => $_GET['code']
						);
						$url          = 'https://api.instagram.com/oauth/access_token';
						$response     = wp_remote_post( $url,
							array(
								'method'      => 'POST',
								'timeout'     => 45,
								'redirection' => 5,
								'httpversion' => '1.0',
								'body'        => $fields,
							)
						);

						if ( $response['response']['message'] == 'OK' ) {
							update_option( 'foogallery_instagram_token', $response['body'] );
						}

						wp_redirect( foogallery_admin_settings_url() . '#instagram' );
					}
				}
			}
		}
	}
}