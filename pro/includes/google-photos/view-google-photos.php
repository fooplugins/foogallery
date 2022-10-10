<?php
/**
 * Google photos authentication view
 *
 * @package foogallery
 */

?>

<div class="wrap" id="foogallery-authentication">

	<h1><?php _e( 'FooGallery Authentication', 'foogallery' ); ?></h1>

	<div class="foo-nav-container" id="foogallery_authentication">

		<h2>Google Photos</h2>

		<div id="foogallery-datasource-googlephotos">

			<?php if ( empty( $this->google_photos_data['client_id'] ) || empty( $this->google_photos_data['client_secret'] ) ) { ?>

				<p><?php echo $this->google_photos_data['no_client_id_secret']; ?></p>
				<br>

			<?php } else {

				// Get query variables from URL
				$query_string = $_SERVER['QUERY_STRING'];
				parse_str( $query_string, $parameters );
				
				if ( !empty( $this->google_photos_data['refresh_token'] ) ) { ?>
					
					<p class="notice notice-success">
					<?php esc_html_e( 'You have already set up your authentication. Unless you wish to regenerate the token this step is not required.', 'foogallery' ); ?>
					</p>

				<?php } ?>

				<?php // Start process of authenticate if not found code in URL parameter
				if ( !isset( $parameters['code'] ) || !isset( $parameters['source'] ) || 'google' !== $parameters['source'] ) {

					$parameters = [
						'response_type' => $this->google_photos_data['response_type'],
						'redirect_uri' => $this->google_photos_data['redirect_uri'],
						'client_id' => $this->google_photos_data['client_id'],
						'scope' => $this->google_photos_data['scope'],
						'access_type' => $this->google_photos_data['access_type'],
						'state' => $this->google_photos_data['state'],
						'prompt' => $this->google_photos_data['prompt'],
					];
					$url = $this->google_photos_data['base_url'] . http_build_query( $parameters );
					$btn_1_class = '';
					$btn_2_class = ' button-large button-disabled ';
					$code = '';
					$state = '';

				} else {

					$url = 'javascript: void(0);';
					$btn_1_class = ' button-large button-disabled ';
					$btn_2_class = '';
					$code = $parameters['code'];
					$state = $parameters['state'];

				}
				
				echo '<p>' . esc_html__( 'You first have to authorize Foogallery to connect to your Google account.', 'foogallery' ) .'</p>';
				echo sprintf( __( '<a href="%s" class="button button-primary button-large %s" id="foogallery-google-photos-auth-btn">%s</a>'), esc_url( $url ), esc_html( $btn_1_class ), esc_html__( 'Step 1: Authenticate' ), 'foogallery' );

				echo '<p>' . esc_html__( 'Next, you have to obtain the token.', 'foogallery' ) .'</p>';
				echo sprintf( __( '<a href="javascript: void(0);" class="button button-secondary %s" id="foogallery-google-photos-token-btn" data-foogallery-nonce="%s">%s</a>' ), esc_html( $btn_2_class ), esc_attr( $this->google_photos_data['nonce'] ),  __( 'Step 2: Obtain Token' ), 'foogallery' );

				echo sprintf( __( '<input type="hidden" value="%s" id="foogallery-google-oauth-code"/>' ), esc_attr( $code ), 'foogallery' );
				echo sprintf( __( '<input type="hidden" value="%s" id="foogallery-google-oauth-state"/>' ), esc_attr( $state ), 'foogallery' );

				echo sprintf( __( '<div id="foogallery-google-result"/>' ), esc_attr( $code ), 'foogallery' );

			} ?>

		</div>

	</div>

</div>
