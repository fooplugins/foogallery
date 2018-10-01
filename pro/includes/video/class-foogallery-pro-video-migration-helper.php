<?php
/**
 * FooVideo Migration Helpers Class
 */
if ( ! class_exists( 'FooGallery_Pro_Video_Migration_Helper' ) ) {

	class FooGallery_Pro_Video_Migration_Helper {

		public function get_migration_state() {
			$state = get_option( 'foogallery-video-migration' );

			//check for the default state
			if ( false === $state ) {
				$state = array(
					'step' => 0,
					'button_text' => __( 'Start Migration', 'foogallery' ),
					'message' => __('The migration will only take a few minutes. Click "Start Migration" to begin.', 'foogallery')
				);
			}

			return $state;
		}

		public function save_migration_state( $state ) {
			if ( get_option( 'foogallery-video-migration' ) !== false ) {
				update_option( 'foogallery-video-migration', $state );
			} else {
				add_option( 'foogallery-video-migration', $state, null, 'no' );
			}
		}

		function find_galleries_to_migrate() {
			//first take all galleries, as all templates share the video settings
			$gallery_posts = get_posts( array(
				'fields' => 'ids',
				'post_type'     => FOOGALLERY_CPT_GALLERY,
				'post_status'	=> array( 'publish', 'draft' ),
				'cache_results' => false,
				'nopaging'      => true
			) );

			$galleries_to_migrate = array();

			//loop through the galleries and determine if
			// a. they use the Video Slider gallery template
			// b. have a video count
			foreach ($gallery_posts as $gallery_id) {
				$gallery_template = get_post_meta( $gallery_id, FOOGALLERY_META_TEMPLATE, true );

				if ( 'videoslider' === $gallery_template ) {
					$galleries_to_migrate[] = $gallery_id;
				} else {

					$video_count = intval( get_post_meta( $gallery_id , '_foovideo_video_count', true ) );

					if ( $video_count > 0 ) {
						$galleries_to_migrate[] = $gallery_id;
					}
				}
			}

			return $galleries_to_migrate;
		}

		public function run_next_migration_step() {
			$state = $this->get_migration_state();

			if ( 0 === $state['step'] ) {
				//first we need to identify what needs to be migrated.
				$gallery_posts = $this->find_galleries_to_migrate();

				//how many videos were imported into the media library with the legacy importer?
				$attachment_posts = get_posts( array(
					'fields' => 'ids',
					'post_type'     => 'attachment',
					'cache_results' => false,
					'nopaging'      => true,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => FOOGALLERY_FOOVIDEO_MIGRATED,
							'compare' => 'NOT EXISTS', // works!
							'value' => '' // This is ignored, but is necessary...
						),
						array(
							'key' => '_foovideo_video_data',
							'compare' => 'EXISTS',
						)
					)
				) );

				$gallery_count = count( $gallery_posts );
				$attachment_count = count( $attachment_posts );

				$state['gallery_data'] = $gallery_posts;
				$state['attachment_data'] = $attachment_posts;

				if ( $gallery_count > 0 && $attachment_count > 0 ) {
					$state['step'] = 1;
					$state['button_text'] =  __( 'Migrate Galleries', 'foogallery' );
					$state['message'] = sprintf( __('We found %d galleries and %d videos that need to be migrated. Click "Migrate Galleries" to continue.', 'foogallery' ), $gallery_count, $attachment_count );
				} else if ( $gallery_count === 0 && $attachment_count === 0 ) {
					$state['step'] = 3;
					$state['button_text'] =  __( 'Finalize Migration', 'foogallery' );
					$state['message'] = __('We found nothing that needs to be migrated. Click "Finalize Migration" to uninstall FooVideo.', 'foogallery' );
				} else if ( $attachment_count > 0 ) {
					$state['step'] = 2;
					$state['button_text'] =  __( 'Migrate Videos', 'foogallery' );
					$state['message'] = sprintf( __('We found %d videos that need to be migrated. Click "Migrate Videos" to continue.', 'foogallery' ), $attachment_count );
				} else if ( $gallery_count > 0 ) {
					$state['step'] = 1;
					$state['button_text'] =  __( 'Migrate Galleries', 'foogallery' );
					$state['message'] = sprintf( __('We found %d galleries that need to be migrated. Click "Migrate Galleries" to continue.', 'foogallery' ), $gallery_count );
				}

			} else if ( 1 === $state['step'] ) {
				//migrate the galleries
				$count = 0;
				foreach ($state['gallery_data'] as $gallery_id) {
					$gallery = FooGallery::get_by_id( $gallery_id );
					//migrate the gallery settings
					$this->migrate_gallery( $gallery, true );
					//migrate video counts
					$this->migrate_video_counts( $gallery_id );
					$count++;
				}

				$attachment_count = count( $state['attachment_data'] );

				if ( $attachment_count > 0 ) {
					$state['step'] = 2;
					$state['button_text'] =  __( 'Migrate Videos', 'foogallery' );
					$state['message'] = sprintf( __('%d galleries were migrated. %d videos still need to be migrated. Click "Migrate Videos" to continue.', 'foogallery' ), $count, $attachment_count );
				} else {
					$state['step'] = 3;
					$state['button_text'] =  __( 'Finalize Migration', 'foogallery' );
					$state['message'] = sprintf( __('%d galleries were migrated. Click "Finalize Migration" to uninstall FooVideo.', 'foogallery' ), $count );
				}

			} else if ( 2 === $state['step'] ) {
				//migrate the attachments
				$count = 0;
				foreach ($state['attachment_data'] as $attachment_id) {
					$this->migrate_attachment( $attachment_id );
					$count++;
				}

				$state['step'] = 3;
				$state['button_text'] =  __( 'Finalize Migration', 'foogallery' );
				$state['message'] = sprintf( __('%d video attachments were migrated. Click "Finalize Migration" to uninstall FooVideo.', 'foogallery' ), $count );
			} else if ( 3 === $state['step'] ) {

				//delete the option for migrations so we no longer see the migration admin message
				delete_option( FOOGALLERY_FOOVIDEO_MIGRATION_REQUIRED );

				//DEACTIVATE FOOVIDEO!
				$api = foogallery_extensions_api();
				$api->deactivate('foovideo', true);

				$state['step'] = 4;
				$state['message'] = __('The migration has completed. Click "Check" to ensure the migration was a success.', 'foogallery' );
				$state['button_text'] =  __( 'Check', 'foogallery' );
			} else if ( 4 === $state['step'] ) {

				if ( class_exists( 'Foo_Video' ) ) {
					$state['button_text'] =  __( 'Check Again', 'foogallery' );
					$state['message'] = __('FooVideo cannot be deactivated automatically. Please manually deactivate "FooGallery - Video Extension" from the plugins listing, and then restart this migration.', 'foogallery' );
				} else {
					//delete the option for migrations so we no longer see the migration admin message
					delete_option( FOOGALLERY_FOOVIDEO_MIGRATION_REQUIRED );

					$state['button_text'] =  __( 'All Done!', 'foogallery' );
					$state['message'] = __('The migration was successful. You can now navigate away from this page.', 'foogallery' );
				}
			}

			$this->save_migration_state( $state );

			return $state;
		}

		public function reset_state() {
			delete_option( 'foogallery-video-migration' );
			return $this->get_migration_state();
		}

		/**
		 * Checks if the gallery needs to be migrated
		 *
		 * @param $gallery_id
		 *
		 * @return bool
		 */
		public function check_gallery_needs_migration( $gallery_id ) {

			//check if the gallery has legacy videos
			$video_count = get_post_meta( $gallery_id , '_foovideo_video_count', true );

			if ( $video_count !== '' ) {
				return true;
			}

			return false;
		}

		/**
		 * Cleans up the legacy video counts and sets the new counts
		 *
		 * @param $gallery_id
		 */
		public function migrate_video_counts( $gallery_id ) {
			//get the legacy video count
			$video_count = intval( get_post_meta( $gallery_id , '_foovideo_video_count', true ) );

			//clear the video count so we do not migrate the gallery again
			delete_post_meta( $gallery_id, '_foovideo_video_count' );

			//update the new video count
			update_post_meta( $gallery_id, FOOGALLERY_VIDEO_POST_META_VIDEO_COUNT, $video_count );
		}

		/**
		 * Migrate a gallery's settings
		 *
		 * @param FooGallery $gallery
		 *
		 * @param bool       $save_changes
		 *
		 * @return FooGallery
		 */
		public function migrate_gallery( &$gallery, $save_changes ) {

			//get the old settings, so we can migrate to the new
			$settings = $gallery->settings;

			if ( 'videoslider' === $gallery->gallery_template ) {

				if ( $save_changes ) {
					//update the gallery template
					update_post_meta( $gallery->ID, FOOGALLERY_META_TEMPLATE, 'slider' );
				}

				//we need to port all settings from 'videoslider' across to 'slider'
				foreach ( $settings as $name => $value) {
					if ( strpos( $name, 'videoslider_' ) === 0 ) {
						$new_name = str_replace( 'videoslider_', 'slider_', $name );
						$settings[$new_name] = $value;
					}
				}

				//update the layout setting
				$this->migrate_setting( $settings, 'videoslider_layout', array(
					'rvs-vertical' => '',
					'rvs-horizontal' => 'fgs-horizontal'
				), 'slider_layout' );

				//update the viewport setting
				$this->migrate_setting( $settings, 'videoslider_viewport', array(
					'' => '',
					'rvs-use-viewport' => 'yes'
				), 'slider_viewport' );

				//update the theme setting
				$this->migrate_setting( $settings, 'videoslider_theme', array(
					'' => 'fg-dark',
					'rvs-light' => 'fg-light',
					'rvs-custom' => 'fg-custom'
				), 'slider_theme' );

				//update the highlight setting
				$this->migrate_setting( $settings, 'videoslider_highlight', array(
					'' => 'fgs-purple',
					'rvs-blue-highlight' => 'fgs-blue',
					'rvs-green-highlight' => 'fgs-green',
					'rvs-orange-highlight' => 'fgs-orange',
					'rvs-red-highlight' => 'fgs-red',
					'rvs-custom-highlight' => 'fgs-custom'
				), 'slider_highlight' );

				$gallery->settings = $settings;

				if ( $save_changes ) {
					update_post_meta( $gallery->ID, FOOGALLERY_META_SETTINGS, $settings );
				}

				$gallery->gallery_template = 'slider';
			}

			//we need to migrate the old foovideo settings that are saved on all galleries
			$this->migrate_setting(
				$settings, $gallery->gallery_template . '_foovideo_video_overlay', array(
				'video-icon-default' => 'fg-video-default',
				'video-icon-1'       => 'fg-video-1',
				'video-icon-2'       => 'fg-video-2',
				'video-icon-3'       => 'fg-video-3',
				'video-icon-4'       => 'fg-video-4'
			), $gallery->gallery_template . '_video_hover_icon' );

			$this->migrate_setting(
				$settings, $gallery->gallery_template . '_foovideo_sticky_icon', array(
				'video-icon-sticky' => 'fg-video-sticky',
				''                  => ''
			), $gallery->gallery_template . '_video_sticky_icon' );

			$this->migrate_setting(
				$settings, $gallery->gallery_template . '_foovideo_video_size', array(
				'640x360'   => '640x360',
				'854x480'   => '854x480',
				'960x540'   => '960x540',
				'1024x576'  => '1024x576',
				'1280x720'  => '1280x720',
				'1366x768'  => '1366x768',
				'1600x900'  => '1600x900',
				'1920x1080' => '1920x1080',
			), $gallery->gallery_template . '_video_size' );

			$this->migrate_setting( $settings, $gallery->gallery_template . '_foovideo_autoplay', array(
				'yes' => 'yes',
				'no' => 'no'
			), $gallery->gallery_template . '_video_autoplay' );

			$gallery->settings = $settings;

			if  ( $save_changes ) {
				//update the gallery settings
				update_post_meta( $gallery->ID, FOOGALLERY_META_SETTINGS, $settings );
			}

			return $gallery;
		}

		/**
		 * Migrate settings and the choice mappings
		 *
		 * @param array $settings
		 * @param string $setting_name
		 * @param array $mappings
		 */
		function migrate_setting( &$settings, $setting_name, $mappings, $override_setting_name = false ) {
			if ( false === $settings ) {
				return;
			}

			$old_setting_name = $setting_name;
			foreach ( $settings as $name => $value) {
				if ( $old_setting_name === $name ) {
					foreach( $mappings as $mapping_key => $mapping_value ) {
						if ( $mapping_key === $value ) {
							if ( false === $override_setting_name ) {
								$override_setting_name = $setting_name;
							}
							$settings[$override_setting_name] = $mapping_value;
							return;
						}
					}
				}
			}
		}

		/**
		 * Migrate a single attachment
		 * @param $attachment_id
		 */
		function migrate_attachment( $attachment_id ) {
			$video_info = get_post_meta( $attachment_id, '_foovideo_video_data', true );

			if ( isset( $video_info ) && !empty( $video_info ) ) {
				$is_migrated = get_post_meta( $attachment_id, FOOGALLERY_FOOVIDEO_MIGRATED, true );

				if ( '1' === $is_migrated ) return;

				//need to update the post mime type
				$update_attachment = array(
					'ID'             => $attachment_id,
					'post_mime_type' => 'image/foogallery'
				);

				wp_update_post( $update_attachment );

				//set the new data
				update_post_meta( $attachment_id, FOOGALLERY_VIDEO_POST_META, $video_info );

				//mark as migrated
				update_post_meta( $attachment_id, FOOGALLERY_FOOVIDEO_MIGRATED, 1 );
			}
		}

	}
}