<?php
/**
 * The Gallery Datasource which pulls images from a specific folder on the server
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_GooglePhotos' ) ) {

	class FooGallery_Pro_Datasource_GooglePhotos {

		/**
		 * FooGallery_Pro_Datasource_GooglePhotos constructor.
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ), 10 );
			add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
			add_action( 'foogallery-datasource-modal-content_googlephotos', array( $this, 'render_datasource_modal_content' ), 10, 3 );

			//create all our settings
			add_filter( 'foogallery_admin_settings', array($this, 'create_settings'), 9999, 2 );
		}

		/**
		 * Register the Google Photos extension
		 *
		 * @param $extensions_list
		 *
		 * @return array
		 */
		function register_extension( $extensions_list ) {
			$extensions_list[] = array(
				'slug' => 'foogallery-googlephotos',
				'class' => 'FooGallery_Pro_GooglePhotos_Extension',
				'categories' => array( 'Premium' ),
				'title' => __( 'Google Photos', 'foogallery' ),
				'description' => __( 'Using this extension the user will be able to see the Photos from him Google Photos and can choose and insert one on the FooGallery.', 'foogallery' ),
				'author' => 'FooPlugins',
				'author_url' => 'https://fooplugins.com',
				'thumbnail' => 'https://foogallery.s3.amazonaws.com/extensions/white_labelling.png',
				'tags' => array( 'premium' ),
				'source' => 'bundled'
			);

			return $extensions_list;
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
		function render_datasource_modal_content( $foogallery_id, $datasource_value ) { ?>
			<div id="foogallery-datasource-googlephotos">
				<p>
					<?php _e( 'You first have to authorize FooGallery to connect to your Google account.', 'foogallery' ); ?>
				</p>
				<div>
					<a class="button button-primary button-large" id="foogallery-google-photos-auth-btn"><?php _e( 'Step 1: Authenticate', 'foogallery' ); ?></a>
				</div>
				<p>
					<?php _e( 'Next, you have to obtain the token.', 'foogallery' ); ?>
				</p>
				<div>
					<a class="button button-secondary button-large" id="foogallery-google-photos-token-btn"><?php _e( 'Step 2: Obtain Token', 'foogallery' ); ?></a>
				</div>
			</div>
		<?php }

		function create_settings( $settings ) {

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
		        'tab'     => 'googlephotos'
	        );

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_client_secret',
				'title'   => __('Google Client Secret', 'foogallery'),
				'desc'    => __('Enter your Google Client Secret.', 'foogallery'),
				'default' => '',
				'section' => 'settings',
				'type'    => 'text',
				'tab'     => 'googlephotos'
			);

			$googlephotos_sections['auth'] = array(
				'name' => __( 'Authentication', 'foogallery' )
			);

			$googlephotos_auth_status = '<div id="foogallery-datasource-googlephotos-status">';
			$googlephotos_auth_details = '<div id="foogallery-datasource-googlephotos-details">';
			$foogallery = get_option( 'foogallery' );
			$googlephotos_client_id = ( array_key_exists( 'googlephotos_client_id', $foogallery ) && !empty( $foogallery['googlephotos_client_id'] ) ? $foogallery['googlephotos_client_id'] : '' );
			$googlephotos_client_secret = ( array_key_exists( 'googlephotos_client_secret', $foogallery ) && !empty( $foogallery['googlephotos_client_secret'] ) ? $foogallery['googlephotos_client_secret'] : '' );
			$googlephotos_refresh_token = ( array_key_exists( 'googlephotos_refresh_token', $foogallery ) && !empty( $foogallery['googlephotos_refresh_token'] ) ? $foogallery['googlephotos_refresh_token'] : '' );
			
			if ( empty( $googlephotos_client_id) || empty( $googlephotos_client_secret ) ) {

				$googlephotos_auth_status .= '<p>'. __( 'Failed', 'foogallery' ). '</p>';				
				$googlephotos_auth_details .= '<p>'. __( 'Please set up your Google Client ID and Client Secret.', 'foogallery' ). '</p>';
				
			} elseif ( !empty( $googlephotos_refresh_token) ) {
			
				$googlephotos_auth_status .= '<p>'. __( 'Success', 'foogallery' ). '</p>';
				$googlephotos_auth_details .= '<p>'. __( 'You have already set up your authentication. Unless you wish to regenerate the token this step is not required.', 'foogallery' ). '</p>';

			} else {

				$googlephotos_auth_status .= '<p>'. __( 'Success', 'foogallery' ). '</p>';
				$googlephotos_auth_details .= '<p>'. __( 'To access any content in Google Photos you need to get a token. To get your token go to Foogallery → Authentication → Google Photos → Google Photos Refresh Token Getter, and authenticate', 'foogallery' ). '</p>';

			}

			$googlephotos_auth_status .= '</div>';
			$googlephotos_auth_details .= '</div>';


			/*$googlephotos_auth_step_1 = '<div id="foogallery-datasource-googlephotos">';
			$googlephotos_auth_step_1 .= '<div><p>'. __( 'You first have to authorize FooGallery to connect to your Google account.', 'foogallery' ). '</p><br>';

			if (!isset($parameters['code']) || !isset($parameters['source']) || 'google' !== $parameters['source']) {

				$url = add_query_arg('test', 'test');
				$url = remove_query_arg('test', $url);
				$parameters = [
					'response_type' => 'code',
					'redirect_uri' => admin_url('edit.php?post_type=foogallery&page=foogallery-settings#googlephotos'),
					'client_id' => $googlephotos_client_id,
					'scope' => 'https://www.googleapis.com/auth/photoslibrary.readonly',
					'access_type' => 'offline',
					'state' => md5($googlephotos_client_secret . 'google') . '::' . rawurlencode($url),
					'prompt' => 'consent',
				];
				$url = 'https://accounts.google.com/o/oauth2/auth?'. http_build_query($parameters);

				
				$googlephotos_auth_step_1 .= '<a href="' . esc_url($url) . '" class="button button-primary button-large" id="foogallery-google-photos-auth-btn">'. __( 'Step 1: Authenticate', 'foogallery' ) . '</a>';

			} else {

			}*/
			
			/*$googlephotos_auth_step_2 = '</div><div><p>'. __( 'Next, you have to obtain the token.', 'foogallery' ) .'</p><br>';
			$googlephotos_auth_step_2 .= '<div><a class="button button-secondary button-large" id="foogallery-google-photos-token-btn">'. __( 'Step 2: Obtain Token', 'foogallery' ) .'</a></div></div>';
			$googlephotos_auth_step_2 .= '</div>';

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_auth_step_1',
				'title'   => __('Step 1', 'foogallery'),
				'desc'    => $googlephotos_auth_step_1,
				'section' => 'auth',
				'type'    => 'html',
				'tab'     => 'googlephotos'
			);

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_auth_step_2',
				'title'   => __('Step 2', 'foogallery'),
				'desc'    => $googlephotos_auth_step_2,
				'section' => 'auth',
				'type'    => 'html',
				'tab'     => 'googlephotos'
			);*/		

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_auth_status',
				'title'   => __('Status', 'foogallery'),
				'desc'    => $googlephotos_auth_status,
				'section' => 'auth',
				'type'    => 'html',
				'tab'     => 'googlephotos'
			);

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_auth_details',
				'title'   => __('Details', 'foogallery'),
				'desc'    => $googlephotos_auth_details,
				'section' => 'auth',
				'type'    => 'html',
				'tab'     => 'googlephotos'
			);

			$googlephotos_settings[] = array(
				'id'      => 'googlephotos_refresh_token',
				'title'   => __('Refresh Token (for Back-end / Server-side Authentication)', 'foogallery'),
				'default' => '',
				'section' => 'auth',
				'type'    => 'text',
				'tab'     => 'googlephotos'
			);

            $settings = array_merge_recursive( $settings, array(
            	'tabs'     => $googlephotos_tabs,
				'sections'     => $googlephotos_sections,
                'settings' => $googlephotos_settings,
            ) );

			return $settings;
		}

	}
}