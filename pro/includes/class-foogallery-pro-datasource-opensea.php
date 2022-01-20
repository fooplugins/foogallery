<?php
/**
 * The Gallery Datasource which pulls data from Opensea.
 *
 * @since 2.1.34
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Opensea' ) ) {

	class FooGallery_Pro_Datasource_Opensea extends FooGallery_Datasource_Base {

		public function __construct() {
			parent::__construct(
				'opensea',
				__('OpenSea', 'foogallery'),
				FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.opensea.js'
			);

			// Add some settings for OpenSea.
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_opensea_settings' ) );
		}

		/**
		 * Render the modal content for the OpenSea datasource.
		 *
		 * @param int $foogallery_id The ID of the current FooGallery.
		 * @param array $datasource_value The stored value for the datasource.
		 *
		 * @return void
		 */
		public function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
			$opensea_link = '<a href="https://opensea.io/" target="_blank">' . __( 'OpenSea', 'foogallery' ) . '</a>';
			$opensea_logo = 'https://opensea.io/static/images/logos/opensea.svg';
			?>
			<h2>
				<img src="<?php echo esc_url( $opensea_logo ); ?>" width="40" height="40" />
				<?php _e( 'OpenSea', 'foogallery' ); ?>
			</h2>
			<p>
				<?php echo sprintf( __('The gallery will be dynamically populated with NFT\'s from %s.', 'foogallery' ), $opensea_link ); ?>
			</p>
			<form action="" method="post" name="opensea_gallery_form">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e( 'Owner', 'foogallery' ) ?></th>
							<td>
								<input
									class="regular-text foogallery_opensea_input"
									name="owner"
									id="foogallery_opensea_owner"
									value="<?php echo isset( $datasource_value['owner'] ) ? $datasource_value['owner'] : '' ?>"
								/>
								<p class="description"><?php _e( 'The address of the owner of the assets.', 'foogallery' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Token ID\'s', 'foogallery' ) ?></th>
							<td>
								<input
									class="regular-text foogallery_opensea_input"
									name="token_ids"
									id="foogallery_opensea_token_ids"
									value="<?php echo isset( $datasource_value['token_ids'] ) ? $datasource_value['token_ids'] : '' ?>"
								/>
								<p class="description"><?php _e( 'A comma-separated list of token IDs to search for.', 'foogallery' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Order By', 'foogallery' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input
											type="radio"
											name="order_by"
											value="sale_date"
											class="order_by foogallery_opensea_input"
											<?php echo ( isset( $datasource_value['order_by'] ) && $datasource_value['order_by'] === 'sale_date' ) ? 'checked="checked"' : '' ?>
										/>
										<span><?php _e( 'Sale Date', 'foogallery' ) ?></span>
									</label>
									<br>
									<label>
										<input
											type="radio"
											name="order_by"
											value="sale_count"
											class="order_by foogallery_opensea_input"
											<?php echo ( isset( $datasource_value['order_by'] ) && $datasource_value['order_by'] === 'sale_count' ) ? 'checked="checked"' : '' ?>
										/>
										<span><?php _e( 'Sale Count', 'foogallery' ) ?></span>
									</label>
									<br>
									<label>
										<input
											type="radio"
											name="order_by"
											value="sale_price"
											class="order_by foogallery_opensea_input"
											<?php echo ( isset( $datasource_value['order_by'] ) && $datasource_value['order_by'] === 'sale_price' ) ? 'checked="checked"' : '' ?>
										/>
										<span><?php _e( 'Sale Price', 'foogallery' ) ?></span>
									</label>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Order Direction', 'foogallery' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input
											type="radio"
											name="order_direction"
											value="asc"
											class="order_direction foogallery_opensea_input"
											<?php echo ( isset( $datasource_value['order_direction'] ) && $datasource_value['order_direction'] === 'asc' ) ? 'checked="checked"' : '' ?>
										/>
										<span><?php _e( 'Ascending', 'foogallery' ) ?></span>
									</label>
									<br>
									<label>
										<input
											type="radio"
											name="order_direction"
											value="desc"
											class="order_direction foogallery_opensea_input"
											<?php echo ( isset( $datasource_value['order_direction'] ) && $datasource_value['order_direction'] === 'desc' ) ? 'checked="checked"' : '' ?>
										/>
										<span><?php _e( 'Descending', 'foogallery' ) ?></span>
									</label>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php
		}

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		function build_attachments_from_datasource( $foogallery ) {
			$base_url = 'https://api.opensea.io/api/v1/assets';
			$params = array();

			if ( ! empty( $foogallery->datasource_value['owner'] ) ) {
				$params['owner'] = $foogallery->datasource_value['owner'];
			}
			if ( ! empty( $foogallery->datasource_value['token_ids'] ) ) {
				$params['token_ids'] = explode( ',', $foogallery->datasource_value['token_ids'] );
			}
			if ( ! empty( $foogallery->datasource_value['order_by'] ) ) {
				$params['order_by'] = $foogallery->datasource_value['order_by'];
			}
			if ( ! empty( $foogallery->datasource_value['order_direction'] ) ) {
				$params['order_direction'] = $foogallery->datasource_value['order_direction'];
			}

			$attachments = array();

			$error = false;

			if ( count( $params ) > 0 ) {
				// Build up the URL for the OpenSea API.
				$url = add_query_arg( $params, $base_url );

				$url = preg_replace( '/%5B\d*%5D/', '', $url );

				$apikey = foogallery_get_setting( 'opensea_apikey' );

				// Set a really large timeout, as the API can be slow to respond.
				$args = array( 'timeout' => 30 );

				// Check if there is a stored API key.
				if ( $apikey !== false ) {
					$args['headers'] = array( 'X-API-KEY' => $apikey );
				}

				// Make the call to the API.
				$response = wp_remote_get( $url, $args );

				// Check for errors.
				if ( ! is_wp_error( $response ) ) {

					// Check we got a successful response.
					if ( wp_remote_retrieve_response_code( $response ) == 200 ) {

						// Delete any last stored errors.
						delete_post_meta( $foogallery->ID, 'foogallery_opensea_error' );

						// Decode the json body to an array, which we can work with.
						$assets = @json_decode( wp_remote_retrieve_body( $response ), true );

						if ( is_array( $assets ) ) {
							foreach ( $assets['assets'] as $asset ) {
								$attachment = new FooGalleryAttachment();

								$attachment->ID            = $asset['token_id'];
								$attachment->title         = empty( $asset['name'] ) ? '#' . $asset['token_id'] : $asset['name'];
								$attachment->description   = $asset['description'];
								$attachment->custom_url    = $asset['permalink'];
								$attachment->custom_target = '_blank';
								$attachment->sort          = '';
								$attachment->url           = $asset['image_url'];
								$attachment->asset         = $asset;

								$attachment    = apply_filters( 'foogallery_datasource_opensea_build_attachment', $attachment, $asset );
								$attachments[] = $attachment;
							}
						}
					} else {
						// We did NOT get a successful response.
						$error = wp_remote_retrieve_response_code( $response ) . ' - ' . wp_remote_retrieve_response_message( $response );
					}
				} else {
					// There was an error calling the API. Save the error so we can display it.
					$error = $response->get_error_message();
				}
			} else {
				$error = __( 'There was nothing to query from the OpenSea API. Please provide some data first.', 'foogallery' );
			}

			if ( $error !== false ) {
				add_post_meta( $foogallery->ID, 'foogallery_opensea_error', $error );
			}

			return $attachments;
		}

		/**
		 * Render the state of the datasource in the admin.
		 *
		 * @param FooGallery $gallery
		 */
		function render_datasource_state( $gallery ) {
			// Only show the container if that is the selected datasource for the gallery.
			$show_container = isset( $gallery->datasource_name ) && 'opensea' === $gallery->datasource_name;

			// Extract all the values we care about from the datasource value.
			$owner = $this->get_datasource_value( $gallery, 'owner', '' );
			$token_ids = $this->get_datasource_value($gallery, 'token_ids', '' );
			$order_by = $this->get_datasource_value( $gallery, 'order_by', '' );
			$order_direction = $this->get_datasource_value( $gallery, 'order_direction', '' );

			$error = get_post_meta( $gallery->ID, 'foogallery_opensea_error', true );

			?>
			<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-item foogallery-datasource-opensea">
				<h3>
					<?php _e( 'Datasource : OpenSea', 'foogallery' ); ?>
				</h3>
				<p>
					<?php _e( 'This gallery will be dynamically populated with NFT\'s from OpenSea with the following parameters:', 'foogallery' ); ?>
				</p>
				<div class="foogallery-items-html">
					<?php echo __('Owner : ', 'foogallery'); ?><span id="foogallery-datasource-opensea-owner"><?php echo $owner; ?></span><br />
					<?php echo __('Token ID\'s: ', 'foogallery'); ?><span id="foogallery-datasource-opensea-token-ids"><?php echo $token_ids; ?></span><br />
					<?php echo __('Order By : ', 'foogallery'); ?><span id="foogallery-datasource-opensea-order-by"><?php echo $order_by; ?></span><br />
					<?php echo __('Order Direction : ', 'foogallery'); ?><span id="foogallery-datasource-opensea-order-direction"><?php echo $order_direction; ?></span><br />
				</div>
				<br/>
				<?php if ( !empty( $error ) ) { ?>
				<p class="error">
					<strong><?php _e( 'There was an error calling OpenSea : ', 'foogallery' ); ?></strong>
					<?php echo $error; ?>
				</p>
				<br/>
				<?php } ?>
				<button type="button" class="button edit">
					<?php _e( 'Change', 'foogallery' ); ?>
				</button>
				<button type="button" class="button remove">
					<?php _e( 'Remove', 'foogallery' ); ?>
				</button>
			</div>
			<?php
		}

		/**
		 * Add some ecommerce settings
		 *
		 * @param array $settings The settings array.
		 *
		 * @return array
		 */
		public function add_opensea_settings( $settings ) {

			$settings['tabs']['opensea'] = __( 'OpenSea', 'foogallery' );

			$html = ' <a href="https://docs.opensea.io/reference/request-an-api-key" target="_blank">' . __( 'Request an API Key.', 'foogallery' ) . '</a>';

			$settings['settings'][] = array(
				'id'      => 'opensea_apikey',
				'title'   => __( 'OpenSea API Key', 'foogallery' ),
				'desc'    => __( 'You will need an API key to make calls to the OpenSea API. If you do not use a key, your calls will eventually get blocked.', 'foogallery' ) . $html,
				'type'    => 'text',
				'default' => __( '', 'foogallery' ),
				'tab'     => 'opensea'
			);

			return $settings;
		}
	}
}