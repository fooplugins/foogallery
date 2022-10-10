<?php
/**
 * The Gallery Datasource which pulls images from google photos
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_GooglePhotos' ) ) {

	class FooGallery_Pro_Datasource_GooglePhotos {

		// Set default values in $google_photos_data to use within class
		private $google_photos_data = array(
			'client_id' 		  => '',
			'client_secret' 	  => '',
			'token_data'	 	  => '',
			'refresh_token'		  => '',
			'nonce'	 			  => '',
			'state'				  => '',
			'prompt'			  => 'consent',
			'access_type'		  => 'offline',
			'provider' 			  => 'google',
			'oauth_version'       => '2.0',
			'response_type'       => 'code',
			'scope'               => 'https://www.googleapis.com/auth/photoslibrary.readonly',
			'redirect_uri'		  => '',
			'base_url'			  => 'https://accounts.google.com/o/oauth2/auth?',
			'oauth_code' 		  => '',
			'oauth_state'		  => '',
			'no_client_id_secret' => 'Please set up your Google Client ID and Client Secret under <em>Foogallery &rarr; Settings &rarr; Google Photos &rarr; Settings</em>',
			'photos_base_url'	  => 'https://photoslibrary.googleapis.com/v1/',
		);

		/**
		 * FooGallery_Pro_Datasource_GooglePhotos constructor.
		 */
		function __construct() {

			// Include google API composer dependencies
			require_once FOOGALLERY_PRO_PATH . '/extensions/google-photos/google-api/vendor/autoload.php';

			//Render the datasource modal
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			
			// Add google photos tab in gallery datasource modal tabs
			add_filter( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ), 10 );

			// Display content in google photos gallery datasource modal tab
			add_action( 'foogallery-datasource-modal-content_googlephotos', array( $this, 'render_datasource_modal_content' ), 10, 3 );

			// Add google photos setting tab in plugin settings
			add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );

			// Get and save google account access token from authentication page ajax
			add_action('wp_ajax_foogallery_google_photos_token', array( $this, 'ajax_obtain_token' ) );

			//create all our settings
			add_filter( 'foogallery_admin_settings', array($this, 'create_settings'), 9999, 2 );
		}

		/**
		 * Enqueues google-photos assets on authentication page
		 */
		function enqueue_scripts_and_styles() {

			//check if the gallery edit page is being shown
			$screen = get_current_screen();

			if ( 'foogallery_page_foogallery_auth' !== $screen->id ) {
				return;
			}

			wp_enqueue_style( 'foogallery.admin.datasources.googlephotos', FOOGALLERY_PRO_URL . 'css/foogallery.admin.datasources.googlephotos.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.datasources.googlephotos', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.googlephotos.js', array( 'jquery' ), FOOGALLERY_VERSION );
			wp_localize_script( 'foogallery.admin.datasources.googlephotos', 'google_photos', array( 'setting_url' => admin_url( 'edit.php?post_type=foogallery&page=foogallery-settings#google_photos' ) ) );
		}

		/**
		 * Add the Google Photos Datasource
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['googlephotos'] = array(
				'id'     => 'googlephotos',
				'name'   => __( 'Google Photos', 'foogallery' ),
				'menu'   => __( 'Google Photos', 'foogallery' ),
				'public' => true
			);

			return $datasources;
		}

		/**
		 * Output the datasource modal content
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
			// Get google API data from database
			$this->get_api_data();
			?>
			<div id="foogallery-datasource-googlephotos">
			<?php if ( empty( $this->google_photos_data['client_id'] ) || empty( $this->google_photos_data['client_secret'] ) ) { ?>
				<p><?php echo $this->google_photos_data['no_client_id_secret']; ?></p>
			<?php } else {
				print_r($albums = $this->get_albums());
			} ?>
			</div>
		<?php }

		/**
		 * Create the settings to manage google account authentication 
		 * @param $settings
		 */
		function create_settings( $settings ) {

			// Get google API data from database
			$this->get_api_data();

			$googlephotos_tabs['GooglePhotos'] = __( 'Google Photos Labeling', 'foogallery' );

			$googlephotos_sections['settings'] = array(
				'name' => __( 'Settings', 'foogallery' )
			);

			$googlephotos_settings[] = array(
		        'id'      => 'googlephotos_client_id',
		        'title'   => __('Google Client ID', 'foogallery'),
		        'desc'    => __('Enter your Google Client ID.', 'foogallery'),
		        'default' => '',
				'section' => 'settings',
		        'type'    => 'text',
		        'tab'     => 'Google Photos'
	        );

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_client_secret',
				'title'   => __('Google Client Secret', 'foogallery'),
				'desc'    => __('Enter your Google Client Secret.', 'foogallery'),
				'default' => '',
				'section' => 'settings',
				'type'    => 'text',
				'tab'     => 'Google Photos'
			);

			$googlephotos_sections['auth'] = array(
				'name' => __( 'Authentication', 'foogallery' )
			);

			$googlephotos_auth_status = '<div id="foogallery-datasource-googlephotos-status">';
			$googlephotos_auth_details = '<div id="foogallery-datasource-googlephotos-details">';
			
			if ( empty( $this->google_photos_data['client_id'] ) || empty( $this->google_photos_data['client_secret'] ) ) {

				$googlephotos_auth_status .= '<p>'. __( 'Failed', 'foogallery' ). '</p>';				
				$googlephotos_auth_details .= '<p>'. __( 'Please set up your Google Client ID and Client Secret.', 'foogallery' ). '</p>';
				
			} elseif ( !empty( $this->google_photos_data['token_data'] ) ) {
			
				$googlephotos_auth_status .= '<p>'. __( 'Success', 'foogallery' ). '</p>';
				$googlephotos_auth_details .= sprintf( __( '<p>You have already set up your authentication. Unless you wish to regenerate the token this step is not required. To regenerate token click on this <a href="%s">link</a></p>' ), $this->google_photos_data['redirect_uri'],  'foogallery' );

			} else {

				$googlephotos_auth_status .= '<p>'. __( 'Success', 'foogallery' ). '</p>';
				$googlephotos_auth_details .= sprintf( __( '<p>To access any content in Google Photos you need to get a token. To get your token from this <a href="%s">link</a></p>' ), $this->google_photos_data['redirect_uri'], 'foogallery' );

			}

			$googlephotos_auth_status .= '</div>';
			$googlephotos_auth_details .= '</div>';	

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_auth_status',
				'title'   => __('Status', 'foogallery'),
				'desc'    => $googlephotos_auth_status,
				'section' => 'auth',
				'type'    => 'html',
				'tab'     => 'Google Photos'
			);

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_auth_details',
				'title'   => __('Details', 'foogallery'),
				'desc'    => $googlephotos_auth_details,
				'section' => 'auth',
				'type'    => 'html',
				'tab'     => 'Google Photos'
			);

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_refresh_token',
				'title'   => __('Refresh Token (for Back-end / Server-side Authentication)', 'foogallery'),
				'desc'    => $this->google_photos_data['refresh_token'],
				'section' => 'auth',
				'type'    => 'html',
				'tab'     => 'Google Photos',
			);

            $settings = array_merge_recursive( $settings, array(
            	'tabs'     => $googlephotos_tabs,
				'sections'     => $googlephotos_sections,
                'settings' => $googlephotos_settings,
            ) );

			return $settings;
		}

		/**
		 * Registers the google authentication hidden menu page
		 */
		public function add_menu() {
			add_submenu_page(
				'',
				__( 'Authentication', 'foogallery' ),
				__( 'Authentication', 'foogallery' ),
				apply_filters( 'foogallery_admin_menu_capability', 'manage_options' ),
				'foogallery_auth',
				array( $this, 'render_google_photos_page' )
			);
		}

		/**
		 * Renders google photos authentication page
		 */
		public function render_google_photos_page() {
			// Get google API data from database
			$this->get_api_data();

			require_once 'google-photos/view-google-photos.php';
		}

		/**
		 * Get google account token from ajax request
		 */
		public function ajax_obtain_token() {

			// Check for nonce security      
			if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'foogallery-google-photo-token' ) ) {
				die ( 'Busted!');
			}
			
			$return = array(
				'status' 		=> 'error',
				'refresh_token' => '',
				'nonce' 		=> '',
			);

			// Rerturn error if google auth code or state not found
			if ( !isset( $_POST['code'] ) || !isset( $_POST['state'] )) {
				wp_send_json( $return );
			}
			
			// Update google auth code and state from ajax data
			$option_data = array(
				'google_oauth_code' => $_POST['code'],
				'google_oauth_state' => $_POST['state'],
			);
			$this->update_api_data( $option_data );

			// Get google API data from database
			$this->get_api_data();

			// Set google API object to generate access token
			$client = new Google\Client();
			$client->setClientId( $this->google_photos_data['client_id'] );
			$client->setClientSecret( $this->google_photos_data['client_secret'] );
			$client->setAccessType( $this->google_photos_data['access_type'] );
			$client->setScopes( $this->google_photos_data['scope'] );
    		$client->setPrompt('consent');
			$client->setIncludeGrantedScopes(true);   // incremental auth
			$client->setState( $this->google_photos_data['oauth_state'] );
			$client->setRedirectUri( $this->google_photos_data['redirect_uri'] );	
				
			if ( $this->google_photos_data['oauth_code'] ) {
				// Generate google access token from authenication code
				$token = $client->fetchAccessTokenWithAuthCode( $this->google_photos_data['oauth_code'] );

				// Check to see if there was an error.
				if ( array_key_exists( 'error', $token ) ) {
					wp_send_json( $return );
				}

				// If valid token found from authenication code then update in database and local variable
				if ( is_array( $token ) && !empty( $token ) && array_key_exists( 'refresh_token', $token ) ) {
					$this->google_photos_data['token_data'] = $token;

					// Update refresh token in database
					$data = array(
						'googlephotos_token_data' => $this->google_photos_data['token_data'],
						'googlephotos_refresh_token' => $this->google_photos_data['token_data']['refresh_token'],
					);
					
					$this->update_api_data( $data );

					// Set access token in google API
					$client->setAccessToken( $this->google_photos_data['token_data'] );

					// Return google access token in success response
					$return = array(
						'status' 		=> 'success',
						'refresh_token' => $this->google_photos_data['token_data']['refresh_token'],
						'nonce' 		=> $this->google_photos_data['nonce'],
					);
				}				
				
			}

			wp_send_json( $return );
		}

		/**
		 * Get google account data from option table
		 */
		public function get_api_data() {
			// Get foogallery options from database
			$foogallery = get_option( 'foogallery' );

			// Update private array data
			$this->google_photos_data['client_id'] = ( array_key_exists( 'googlephotos_client_id', $foogallery ) && !empty( $foogallery['googlephotos_client_id'] ) ? $foogallery['googlephotos_client_id'] : $this->google_photos_data['client_id'] );
			$this->google_photos_data['client_secret'] = ( array_key_exists( 'googlephotos_client_secret', $foogallery ) && !empty( $foogallery['googlephotos_client_secret'] ) ? $foogallery['googlephotos_client_secret'] : '' );
			$this->google_photos_data['token_data'] = ( array_key_exists( 'googlephotos_token_data', $foogallery ) && !empty( $foogallery['googlephotos_token_data'] ) ? $foogallery['googlephotos_token_data'] : $this->google_photos_data['token_data'] );
			$this->google_photos_data['nonce'] = wp_create_nonce( 'foogallery-google-photo-token' );
			$this->google_photos_data['redirect_uri'] = admin_url( 'edit.php?post_type=foogallery&page=foogallery_auth&source=google' );

			// Set refresh token variable value
			if ( is_array( $this->google_photos_data['token_data'] ) && !empty( $this->google_photos_data['token_data'] ) ) {
				$this->google_photos_data['refresh_token'] = $this->google_photos_data['token_data']['refresh_token'];
			}
			
			// If google client id and secret both exists then update variablr data
			if ( !empty( $this->google_photos_data['client_id'] ) && !empty( $this->google_photos_data['client_secret'] ) ) {
				$url = add_query_arg( 'test', 'test' );
				$url = remove_query_arg( 'test', $url );

				// Generate state variable from client secret
				$this->google_photos_data['state'] = md5( $this->google_photos_data['client_secret'] . $this->google_photos_data['provider'] ) . '::' . rawurlencode( $url );
				
				// Get autherization code from database
				$this->google_photos_data['oauth_code'] = ( array_key_exists( 'google_oauth_code', $foogallery ) && !empty( $foogallery['google_oauth_code'] ) ? $foogallery['google_oauth_code'] : $this->google_photos_data['oauth_code'] );

				// Get autherization state from database
				$this->google_photos_data['oauth_state'] = ( array_key_exists( 'google_oauth_state', $foogallery ) && !empty( $foogallery['google_oauth_state'] ) ? $foogallery['google_oauth_state'] : $this->google_photos_data['oauth_state'] );
			}
		}

		/**
		 * Update google account data in option table
		 * @param $data array
		 */
		public function update_api_data( $data ) {
			// Get foogallery options from database
			$foogallery = get_option( 'foogallery' );

			// If $data is array and not empty then update
			if ( is_array( $data ) && !empty( $data ) ) {
				foreach ( $data as $key => $value ) {
					// Set foogallery option array keys & values
					$foogallery[ $key ]	 = $value;
				}
				update_option( 'foogallery', $foogallery );
			}

		}

		/**
		 * Common function to send get/post request to fetch google photos and albums data
		 * @param $action string
		 * @param $method string (GET or POST) or empty ('')
		 * @param $data array
		 * @return array
		 */
		public function send_request( $action, $method = 'POST', $data = array() ) {
			// Get google API data from database
			$this->get_api_data();

			$url = $this->google_photos_data['photos_base_url'] . '' . $action;
			$headers = array(
				'Content-type' => 'application/json',
				'Authorization' => 'Bearer ' . $this->google_photos_data['token_data']['access_token'],
			);

			if ( $method == 'GET' ) {
				$response = wp_remote_get( $url, array(
					'headers' => $headers,
				) );
			} else {
				$response = wp_remote_post( $url, array(
					'body'    => $data,
					'headers' => $headers,
				) );
			}

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();

				$return = array(
					'message' => "Something went wrong: $error_message",
					'status'  => 'error',
					'data' => array()
				);

			} else {

				$return = array(
					'message' => "Success",
					'status'  => 'success',
					'data' => $response
				);

			}

			return $return;

		}

		/**
		 * Get data from google photos all albums
		 */
		public function get_albums() {
			// Get google API data from database
			$this->get_api_data();
			
			// Set default variables value
			$return_html = '';
			$data = array(
				'pageSize' => '100',
			);

			// Get data from all albums
			$response = $this->send_request( 'albums', 'GET', $data );
			$res_body = json_decode( $response['data']['body'], true );

			// If response available and valid array then process
			if ( is_array( $res_body ) && is_array( $res_body ) ) {

				// If error found in response
				if ( array_key_exists( 'error', $res_body ) ) {
					
					// Display error message HTML code
					$return_html .= '<h3>Error Found</h3>';
					$return_html .= '<p><strong>Error Code: </strong>' . $res_body['error']['code'] . '</p>';
					$return_html .= '<p><strong>Error Status: </strong>' . $res_body['error']['status'] . '</p>';
					$return_html .= '<p><strong>Error Message: </strong>' . $res_body['error']['message'] . '</p>';

					return $return_html;

				} elseif ( array_key_exists( 'albums', $res_body ) ) { // If albums found then send album data

					$return_html .= '<div class="foogallery-gp-albums">';
				
					foreach ( $res_body['albums'] as $album) {

						$return_html .= '<p>' . ( $album['title'] ? $album['title'] : $album['id'] ) . '</p>';
						$coverPhotoBaseUrl = $album['coverPhotoBaseUrl'];
						

					}

					$return_html .= '</div>';

					return $return_html;
	
				} else {

					return $response['message'];

				}

			}

			return $res_body;
		}

	}
}