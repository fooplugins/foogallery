<?php
/**
 * FooGallery Pro support for WP/LR Sync
 */
if ( ! class_exists( 'FooGallery_Pro_WPLR_Support' ) ) {

	class FooGallery_Pro_WPLR_Support {

		function __construct() {
			//add some settings for WP/LR
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_wplr_settings' ) );
			add_action( 'wplr_create_collection', array( $this, 'sync_collection_to_gallery'), 10, 3 );
        }

		/**
		 * Creates a draft gallery and populates it with the collection as the datasource
		 *
		 * @param $collection_id
		 * @param $parent_id
		 * @param $name
		 *
		 */
        function sync_collection_to_gallery( $collection_id, $parent_id, $name ) {
        	//first check if the setting is enabled
			if ( foogallery_get_setting('enable_sync_collections_to_galleries') === 'on' ) {

				$foogallery_title = $name['name'];

				//then check if we have already synced the collection to an existing gallery
				$foogallery_wplr = get_option( 'foogallery_wplr_sync', array() );
				if ( ! array_key_exists( $collection_id, $foogallery_wplr ) ) {

					//create a new gallery
					$foogallery_args = array(
						'post_title'  => $foogallery_title,
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
					update_post_meta( $gallery_id, FOOGALLERY_META_DATASOURCE_VALUE, array( 'collectionId' => $collection_id, 'collection' => $foogallery_title ) );

					//save the mapping so we dont do it again
					$foogallery_wplr[$collection_id] = array(
						'collectionId' => $collection_id,
						'collection'   => $foogallery_title,
						'foogalleryId' => $gallery_id
					);

					update_option( 'foogallery_wplr_sync', $foogallery_wplr );
				} else {
					//collection has already been synced
					$foogallery_sync_data = $foogallery_wplr[$collection_id];

					//get the foogallery ID
					$foogallery_id = intval( $foogallery_sync_data['foogalleryId'] );

					//update the datasource metadata value
					update_post_meta( $foogallery_id, FOOGALLERY_META_DATASOURCE_VALUE, array( 'collectionId' => $collection_id, 'collection' => $foogallery_title ) );

					//update the gallery name
					$gallery_post = array(
						'ID'           => $foogallery_id,
						'post_title'   => $foogallery_title
					);
					wp_update_post( $gallery_post );

					//clear the gallery cache
					$transient_key = '_foogallery_datasource_lightroom_' . $foogallery_id;
					delete_transient( $transient_key );

					//finally, update the synced data
					$foogallery_sync_data['name'] = $foogallery_title;
					update_option( 'foogallery_wplr_sync', $foogallery_wplr );
				}
			}
		}

		/**
		 * Add some WP/LR settings
		 * @param $settings
		 *
		 * @return array
		 */
		function add_wplr_settings( $settings ) {
			$settings['tabs']['wplr'] = __( 'WP/LR Sync', 'foogallery' );

			$wplr_url = 'https://fooplugins.com/refer/wp-lr-sync/';
			$wplr_link = '<a href="' . $wplr_url . '" target="_blank">' . __('WP/LR Sync', 'foogallery') . '</a>';

			$html = '<p>' . sprintf( __('%s is a Lightroom Publishing Service for WordPress. It exports your photos to WordPress, the folders and collections from Adobe Lightroom and keeps it all synchronized.', 'foogallery'), $wplr_link ) . '</p>';
			$html .= '<a href="' . $wplr_url . '" target="_blank"><img src="https://store.meowapps.com/wp-content/uploads/2017/03/meow-apps.png" width="500" /></a>';

			if ( !class_exists( 'Meow_WPLR_Sync_API' ) ) {
				$html .= '<p><h3>' . __('You need the WP/LR Plugin installed and configured first!', 'foogallery') . '</h3></p>';
			}

			$settings['settings'][] = array(
				'id'      => 'what_is_wprl',
				'title'   => __( 'What is WP/LR?', 'foogallery' ),
				'type'    => 'html',
				'desc'    => $html,
				'tab'     => 'wplr'
			);

			$settings['settings'][] = array(
				'id'      => 'enable_sync_collections_to_galleries',
				'title'   => __( 'Sync Collections To Galleries', 'foogallery' ),
				'type'    => 'checkbox',
				'desc'    => sprintf( __( 'If enabled, galleries will be automatically created when a collection is synchronized from LightRoom using %s', 'foogallery' ), $wplr_link ),
				'tab'     => 'wplr'
			);

			$synced = get_option( 'foogallery_wplr_sync', array() );

			$save_synced_data = false;
			$updated_sync_data = array();

			if ( isset( $_GET['data'] ) ) {

				if ( 'clean' === $_GET['data'] ) {
					$synced = array();
				} else if ( 'dummy' === $_GET['data'] ) {
					$synced[2] = array(
						'collectionId' => 2,
						'collection'   => 'test collection 2',
						'foogalleryId' => 345677
					);

					$synced[3] = array(
						'collectionId' => 3,
						'collection'   => 'test collection 3',
						'foogalleryId' => 1121212
					);

					$synced[1] = array(
						'collectionId' => 1,
						'collection'   => 'test collection',
						'foogalleryId' => 348
					);
				}
				update_option( 'foogallery_wplr_sync', $synced );
			}

			if ( count ( $synced ) > 0 ) {
				$sync_data = sprintf( '<table class="wp-list-table widefat striped"><thead><tr><td><strong>%s</strong></td><td><strong>%s</strong></td></tr></thead><tbody>', __( 'Collection', 'foogallery' ), __( 'Gallery', 'foogallery' ) );
				foreach ( $synced as $sync ) {
					$gallery_id = $sync['foogalleryId'];
					$gallery    = FooGallery::get_by_id( $gallery_id );
					if ( false === $gallery ) {
						//gallery no longer exists
						$save_synced_data = true;
					} else {
						$gallery_url                         = get_edit_post_link( $gallery_id, 'url' );
						$collection_id                       = $sync['collectionId'];
						$collection                          = $sync['collection'];
						$updated_sync_data[ $collection_id ] = $sync;
						$sync_data                           .= sprintf( '<tr><td>%s</td><td><a href="%s" target="_blank">%s</a></td></tr>', $collection . ' (id:' . $collection_id . ')', $gallery_url, $gallery->name );
					}
				}
				$sync_data .= '</tbody></table>';

				$settings['settings'][] = array(
					'id'    => 'wplr_synced',
					'title' => __( 'Already Synced', 'foogallery' ),
					'type'  => 'html',
					'desc'  => $sync_data,
					'tab'   => 'wplr'
				);

				//update the synced data, clearing out any unused collections where galleries have been deleted
				if ( $save_synced_data ) {
					update_option( 'foogallery_wplr_sync', $updated_sync_data );
				}
			}

			return $settings;
		}

	}
}