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

			// Render the datasource modal
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			
			// Add google photos tab in gallery datasource modal tabs
			add_filter( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ), 10 );

			// Display content in google photos gallery datasource modal tab
			add_action( 'foogallery-datasource-modal-content_googlephotos', array( $this, 'render_datasource_modal_content' ), 10, 3 );

			// Add google photos setting tab in plugin settings
			add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );

			// Get and save google account access token from authentication page ajax
			add_action('wp_ajax_foogallery_google_photos_token', array( $this, 'ajax_obtain_token' ) );

			// Create all our settings
			add_filter( 'foogallery_admin_settings', array($this, 'create_settings'), 9999, 2 );

			// Render the html required by the datasource in order to add item
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );

			// Render the featured FooGalleryAttachment from the datasource
			add_filter( 'foogallery_datasource_googlephotos_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );

			// Render the number of attachments used for the gallery
			add_filter( 'foogallery_datasource_googlephotos_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );

			// Render an array of FooGalleryAttachments from the datasource
			add_filter( 'foogallery_datasource_googlephotos_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );

			// Render an array of the attachment ID's for the gallery
			add_filter( 'foogallery_datasource_googlephotos_attachment_ids', array( $this, 'get_gallery_attachment_ids' ), 10, 2 );

			// Filter to clears the cache for the specific post query
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );

			// Authenticate google refresh token when initialize google photos class
			//$this->authenticate();
		}

		/**
		 * Enqueues google-photos assets on authentication page
		 */
		function enqueue_scripts_and_styles() {

			//check if the gallery edit page is being shown
			$screen = get_current_screen();

			if ( /*( 'foogallery_page_foogallery_auth' === $screen->id ) ||*/ ( 'post' == $screen->base && 'foogallery' == $screen->post_type ) ) {
				wp_enqueue_style( 'foogallery.admin.datasources.googlephotos', FOOGALLERY_PRO_URL . 'css/foogallery.admin.datasources.googlephotos.css', array(), FOOGALLERY_VERSION );
				wp_enqueue_script( 'foogallery.admin.datasources.googlephotos', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.googlephotos.js', array( 'jquery' ), FOOGALLERY_VERSION );
				wp_localize_script( 'foogallery.admin.datasources.googlephotos', 'google_photos', array( 'setting_url' => admin_url( 'edit.php?post_type=foogallery&page=foogallery-settings#google_photos' ) ) );
			}

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
				$albums = $this->get_albums();
				print_r($albums);
			} ?>
			</div>
			<script type="text/javascript">
				document.foogalleryDatasourceGooglePhotosNonce = '<?php echo wp_create_nonce( "foogallery_datasource_google_photos_change" ); ?>';
			</script>
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
				'google_oauth_code' => sanitize_text_field( $_POST['code'] ),
				'google_oauth_state' => sanitize_text_field( $_POST['state'] ),
			);
			$this->update_api_data( $option_data );

			// Get google API data from database
			$this->get_api_data();

			// Get google client object
			$client = $this->google_client_request();	
				
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
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			
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
					'body'    => wp_json_encode( $data ),
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

					$return_html .= '<ul class="foogallery-datasource-googlephotos-albums">';
				
					foreach ( $res_body['albums'] as $album) {
						$album_id = $album['id'];
						$album_title = ( $album['title'] ? $album['title'] : 'Untitled' );
						$album_cover = $album['coverPhotoBaseUrl'].'=w180-h180-c';
						$mediaItemsCount = ( absint( $album['mediaItemsCount'] ) ? $album['mediaItemsCount'] : 0 );
						
						$return_html .= sprintf( __( '<li class="foogallery-gf-album-wrap"><a href="#" data-album_id="%1$s" data-label="%2$s" data-foogallery-nonce="%3$s"><img class="foogallery-gf-album-cover" src="%4$s" alt="%2$s"><span class="foogallery-gf-album-label">%2$s</span><span class="foogallery-gf-album-item-count"> (%5$d)</span></a></li>'), $album_id, $album_title, esc_attr( $this->google_photos_data['nonce'] ), $album_cover, $mediaItemsCount, 'foogallery' );
						
					}

					$return_html .= '</ul>';

					return $return_html;
	
				} else {

					return $response['message'];

				}

			}

			return $res_body;
		}

		/**
		 * @param $base_token
		 */
		/*public function authenticate() {
			// Get google API data from database
			$this->get_api_data();

			//$refresh_token = $this->google_photos_data['refresh_token'];

			// If google client id and secret both exists then update variablr data
			if ( !empty( $this->google_photos_data['client_id'] ) && !empty( $this->google_photos_data['client_secret'] ) ) {

				// Check if refresh token saved in database
				if ( !empty( $this->google_photos_data['refresh_token'] ) && !($this->is_token_expired( $this->google_photos_data['token_data'] ) ) ) {

					// Get google client object
					$client = $this->google_client_request();

					// Regenerate google refresh token if expired
					$this->google_photos_data['token_data'] = $client->refreshToken( $this->google_photos_data['refresh_token'] );

					// Update refresh token in database
					$data = array(
						'googlephotos_token_data' => $this->google_photos_data['token_data'],
						'googlephotos_refresh_token' => $this->google_photos_data['token_data']['refresh_token'],
					);
					
					$this->update_api_data( $data );

					// Set access token in google API
					//$client->setAccessToken( $this->google_photos_data['token_data'] );
					//$this->google_photos_data['refresh_token'] = $this->google_photos_data['token_data']['refresh_token'];

				}

			}*/

		//}

		/*public function is_token_expired() {
			// Get google API data from database
			$this->get_api_data();

			if ( empty( $this->google_photos_data['token_data'] ) ) {
				return true;
			}

			if ( !isset($this->google_photos_data['client_id'] ) || !isset( $this->google_photos_data['client_secret'] ) || !isset( $this->google_photos_data['refresh_token'] ) ) {
				return true;
			}

			$now = time();
			$time_diff = $now - $this->google_photos_data['token_data']['created'];
			if ( $time_diff > $this->google_photos_data['token_data']['expires_in'] ) {
				return true;
			}

			return false;
		}
		*/

		/**
		 * Get google client object from client class
		 * @return object $client
		 */
		function google_client_request() {

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

			return $client;

		}

		/**
		 * Output the html required by the datasource in order to add item(s)
		 *
		 * @param FooGallery $gallery
		 */
		function render_datasource_item( $gallery ) {
			// Setup some defaults.
			$show_container = isset( $gallery->datasource_name ) && 'googlephotos' === $gallery->datasource_name;
			$album_title          = ( $show_container && isset( $gallery->datasource_value['album_title'] ) ) ? $gallery->datasource_value['album_title'] : '';

			if ( isset( $gallery->datasource_name ) ) {
				$show_container = 'googlephotos' === $gallery->datasource_name;
			}

			?>
            <div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-item foogallery-datasource-googlephotos">
                <h3>
					<?php _e( 'Datasource : Google Photos', 'foogallery' ); ?>
                </h3>
                <p>
					<?php _e( 'This gallery will be dynamically populated with all images within the following album from google photos account:', 'foogallery' ); ?>
                </p>
                <div class="foogallery-items-html">
					<div class="foogallery-items-html">
						<?php echo __('Album Name : ', 'foogallery'); ?><span id="foogallery-datasource-googlephotos-album-name"><?php echo $album_title ?></span><br />
					</div>
					<br />
					<button type="button" class="button edit">
						<?php _e( 'Change', 'foogallery' ); ?>
					</button>
					<button type="button" class="button remove">
						<?php _e( 'Remove', 'foogallery' ); ?>
					</button>
				</div>

            </div>
			<?php
		}

		/**
		 * Returns the featured FooGalleryAttachment from the datasource
		 *
		 * @param FooGalleryAttachment $default
		 * @param FooGallery $foogallery
		 *
		 * @return bool|FooGalleryAttachment
		 */
		public function get_gallery_featured_attachment( $default, $foogallery ) {
			$attachments = $this->get_gallery_attachments_from_googlephotos( $foogallery );
			return reset( $attachments );
		}

		/**
		 * Returns the number of attachments used for the gallery
		 *
		 * @param int $count
		 * @param FooGallery $foogallery
		 *
		 * @return int
		 */
		public function get_gallery_attachment_count( $count, $foogallery ) {
			return count( $this->get_gallery_attachments_from_googlephotos( $foogallery ) );
		}

		/**
		 * Returns an array of the attachment ID's for the gallery
		 *
		 * @param $attachment_ids
		 * @param $foogallery
		 *
		 * @return array
		 */
		public function get_gallery_attachment_ids( $attachment_ids, $foogallery ) {
			$attachments = $this->get_gallery_attachments_from_googlephotos( $foogallery );
			return reset( $attachments );
		}

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 *
		 * @param array $attachments
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments( $attachments, $foogallery ) {
			return $this->get_gallery_attachments_from_googlephotos( $foogallery );
		}

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments_from_googlephotos( $foogallery ) {
			global $foogallery_gallery_preview;

			$attachments = array();		

			if ( ! empty( $foogallery->datasource_name ) ) {
				$transient_key = '_foogallery_datasource_googlephotos_' . $foogallery->ID;

				//never get the cached results if we are doing a preview
				if ( isset( $foogallery_gallery_preview ) ) {
					$cached_attachments = false;
				} else {
					$cached_attachments = get_transient( $transient_key );
				}

				if ( false === $cached_attachments ) {
					$expiry_hours = apply_filters( 'foogallery_datasource_googlephotos_expiry', 24 );
					$expiry       = $expiry_hours * 60 * 60;

					//find all products
					$attachments = $this->get_images( $foogallery );

					//save a cached list of attachments
					set_transient( $transient_key, $attachments, $expiry );
				} else {
					$attachments = $cached_attachments;
				}
			}

			return $attachments;
		}

		/**
		 * Get data from google photos all albums
		 */
		public function get_images( $foogallery ) {
			global $foogallery_gallery_preview;

			$max_attachments = 0;
			if ( isset( $foogallery_gallery_preview ) ) {
				$max_attachments = 100;
			}

			$attachments = array();

			// Get album id from datasource
			$album_id = $foogallery->datasource_value['album_id'];

			// Get all images from google photos by album id
			$images = $this->get_images_by_album_id( $album_id );

			if ( is_array( $images ) && !empty( $images ) ) {
				if ( array_key_exists( 'error', $images ) ) {
					return $images['error'];
				} else {
					foreach ( $images as $image ) {
						$image_id = $image['media_id'];
						$image_src = $image['media_url'];
						$filename = $image['filename'];
						$media_metadata = $image['media_metadata'];
						
						// Create foogallery attachment object
						$attachment 			  = new FooGalleryAttachment();
						$attachment->ID           = $image_id;
						$attachment->title        = $filename;
						$attachment->url          = $image_src;
						$attachment->type         = 'image';
						$attachment->has_metadata = false;
						$attachment->sort         = PHP_INT_MAX;
						if ( is_array( $media_metadata ) && !empty( $media_metadata ) ) {
							$attachment->width  = $media_metadata['width'];
							$attachment->height = $media_metadata['height'];
						}
						$attachments[] = $attachment;
					}
				}
			}

			return $attachments;
		}

		/**
		 * Render images from google photos by album id
		 */
		public function get_images_by_album_id( $album_id ) {
			$attachments = array();
			$return_html = '';

			// Get google API data from database
			$this->get_api_data();

			$data = array(
				'albumId' => $album_id
			);

			// Get data from all albums
			$response = $this->send_request( 'mediaItems:search', 'POST', $data );
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

					$attachments['error'] = $return_html;

				} elseif ( array_key_exists( 'mediaItems', $res_body ) ) { // If albums found then send album data
					if ( is_array( $res_body['mediaItems'] ) && !empty( $res_body['mediaItems'] ) ) {
						foreach ( $res_body['mediaItems'] as $key => $media ) {
							$attachments[$key]['media_id'] = $media['id'];
							$attachments[$key]['media_url'] = $media['baseUrl'] . '=w150-h150';
							$attachments[$key]['filename'] = $media['filename'];
							$attachments[$key]['media_metadata'] = $media['mediaMetadata'];
						}
					}
				}

			}
			
			return $attachments;
		}

		/**
		 * Clears the cache for the specific post query
		 *
		 * @param $foogallery_id
		 */
		public function before_save_gallery_datasource_clear_datasource_cached_images( $foogallery_id ) {
			$this->clear_gallery_transient( $foogallery_id );
		}

		/**
		 * Clears the cache for the specific post query
		 *
		 * @param $foogallery_id
		 */
		public function clear_gallery_transient( $foogallery_id ) {
			$transient_key = '_foogallery_datasource_googlephotos_' . $foogallery_id;
			delete_transient( $transient_key );
		}

	}
}