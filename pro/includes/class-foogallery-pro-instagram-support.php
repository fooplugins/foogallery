<?php
/**
 * FooGallery Pro support for Instagram Sync
 */
if ( ! class_exists( 'FooGallery_Pro_Instagram_Support' ) ) {

	class FooGallery_Pro_Instagram_Support {

		function __construct() {
			//add some settings for Instagram
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_instagram_settings' ) );
			add_action('admin_init', array($this,'save_instagram_access_token'));
			
        }

		
		/**
		 * Add some Instagram settings
		 * @param $settings
		 *
		 * @return array
		 */
		function add_instagram_settings( $settings ) {
			//.'edit.php?post_type=foogallery&page=foogallery-settings'
			$settings['tabs']['insta'] = __( 'Instagram', 'foogallery' );
			$redirect_url = admin_url();

			if(get_option('instagram_token') != ''){
				//echo "<h3>Successfully connected to Instagram</h3>";
				$settings['settings'][] = array(
					'id'      => 'insta_connected_desc',
					'title'   => '',
					'type'    => 'html',
					'desc'    => "<h3>Successfully connected to Instagram</h3>",
					'tab'     => 'insta'
				);
			}

			$html = '<div><code>' .$redirect_url.'</code> </div>';

			$settings['settings'][] = array(
				'id'      => 'insta_desc',
				'title'   => __( 'Use this URL as REDIRECT_URL', 'foogallery' ),
				'type'    => 'html',
				'desc'    => $html,
				'tab'     => 'insta'
			);

			$settings['settings'][] = array(
				'id'      => 'insta_client_id',
				'title'   => __( 'Instagram Client ID', 'foogallery' ),
				'type'    => 'text',
				'desc'    => 'Please enter Instagram Client ID',
				'tab'     => 'insta'
			);

			$settings['settings'][] = array(
				'id'      => 'insta_client_secret',
				'title'   => __( 'Instagram Client Secret', 'foogallery' ),
				'type'    => 'text',
				'desc'    => 'Please enter Instagram Client Secret',
				'tab'     => 'insta'
			);

			$synced = get_option('foogallery');
			//print_r($synced['insta_client_id']);
			//	exit;
			if ( isset($synced['insta_client_id']) && $synced['insta_client_id'] != '' ) {
				
				$sync_data = '<a class="button-secondary" href="https://api.instagram.com/oauth/authorize/?client_id='.$synced['insta_client_id'].'&redirect_uri='.$redirect_url.'&response_type=code" target="_blank">Get Access Token</a>';

				$settings['settings'][] = array(
					'id'      => 'insta_synced',
					'title'   => __( 'Get Token', 'foogallery' ),
					'type'    => 'html',
					'desc'    => $sync_data,
					'tab'     => 'insta'
				);	
			}

			return $settings;
		}

		function save_instagram_access_token(){
			
			if(isset($_GET['code']) && $_GET['code'] != ''){
				$synced = get_option('foogallery');
				if ( (isset($synced['insta_client_id']) && $synced['insta_client_id'] != '') &&  (isset($synced['insta_client_secret']) && $synced['insta_client_secret'] != '') ) {
					$redirect_url = admin_url().'edit.php?post_type=foogallery&page=foogallery-settings/';	
					$fields = array(
				           'client_id'     => $synced['insta_client_id'],
				           'client_secret' => $synced['insta_client_secret'],
				           'grant_type'    => 'authorization_code',
				           'redirect_uri'  => admin_url(),
				           'code'          => $_GET['code']
				    );
				    $url = 'https://api.instagram.com/oauth/access_token';
				    $response = wp_remote_post( $url, 
				    	array(
				    	'method'      => 'POST',
					    'timeout'     => 45,
					    'redirection' => 5,
					    'httpversion' => '1.0',
					    'body' => $fields, 
						)
				    );

				    if($response['response']['message'] == 'OK'){
				    	update_option( 'instagram_token', $response['body'] );
				    }
				   
				   wp_redirect(admin_url().'edit.php?post_type=foogallery&page=foogallery-settings#insta');
				   //wp_die();
				}
			}
		}

	}
}