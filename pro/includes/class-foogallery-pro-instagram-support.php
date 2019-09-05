<?php
/**
 * FooGallery Pro support for Instagram Sync
 */
if ( ! class_exists( 'FooGallery_Pro_Instagram_Support' ) ) {

	class FooGallery_Pro_Instagram_Support {

		function __construct() {
			//add some settings for Instagram
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_insta_settings' ) );
			add_action('admin_init', array($this,'save_insta_access_token'));
			//add_action( 'wplr_create_collection', array( $this, 'sync_collection_to_gallery'), 10, 3 );
        }

		/**
		 * Creates a draft gallery and populates it with the collection as the datasource
		 *
		 * @param $collection_id
		 * @param $parent_id
		 * @param $name
		 *
		 */
       /* function sync_collection_to_gallery( $collection_id, $parent_id, $name ) {
        	//first check if the setting is enabled
			if ( foogallery_get_setting('enable_sync_collections_to_galleries') === 'on' ) {

				//then check if we have already synced the collection to an existing gallery
				$foogallery_insta = get_option( 'foogallery_insta_sync', array() );
				if ( ! array_key_exists( $collection_id, $foogallery_wplr ) ) {

					//create a new gallery
					$foogallery_args = array(
						'post_title'  => $name['name'],
						'post_type'   => FOOGALLERY_CPT_GALLERY,
						'post_status' => 'publish',
					);
					$gallery_id      = wp_insert_post( $foogallery_args );

					//save some default settings if setup
					$default_gallery_id = foogallery_get_setting( 'default_gallery_settings' );
					if ( $default_gallery_id ) {
						$settings = get_post_meta( $default_gallery_id, FOOGALLERY_META_SETTINGS, true );
						add_post_meta( $gallery_id, FOOGALLERY_META_SETTINGS, $settings, true );

						$default_gallery = FooGallery::get_by_id( $default_gallery_id );
						$template        = $default_gallery->gallery_template;
					} else {
						$template = foogallery_default_gallery_template();
					}

					//set a gallery template
					add_post_meta( $gallery_id, FOOGALLERY_META_TEMPLATE, $template, true );

					//make sure the datasource is set correctly
					update_post_meta( $gallery_id, FOOGALLERY_META_DATASOURCE, 'lightroom' );
					update_post_meta( $gallery_id, FOOGALLERY_META_DATASOURCE_VALUE, array( 'collectionId' => $collection_id, 'collection' => $name['name'] ) );

					//save the mapping so we dont do it again
					$foogallery_wplr[$collection_id] = array(
						'collectionId' => $collection_id,
						'collection'   => $name['name'],
						'foogalleryId' => $gallery_id
					);

					update_option( 'foogallery_wplr_sync', $foogallery_wplr );
				}
			}
		}*/

		/**
		 * Add some Instagram settings
		 * @param $settings
		 *
		 * @return array
		 */
		function add_insta_settings( $settings ) {
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

		function save_insta_access_token(){
			
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