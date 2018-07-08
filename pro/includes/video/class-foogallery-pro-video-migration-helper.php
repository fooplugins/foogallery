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

		public function run_next_migration_step() {
			$state = $this->get_migration_state();

			if ( 0 === $state['step'] ) {
				//first we need to identify what needs to be migrated.

				//take all galleries, as all templates share the video settings
				$gallery_posts = get_posts( array(
					'fields' => 'ids',
					'post_type'     => FOOGALLERY_CPT_GALLERY,
					'post_status'	=> array( 'publish', 'draft' ),
					'cache_results' => false,
					'nopaging'      => true
				) );

				//how many videos were imported into the media library with the legacy importer?
				$attachment_posts = get_posts( array(
					'fields' => 'ids',
					'post_type'     => 'attachment',
					'cache_results' => false,
					'nopaging'      => true,
					'meta_query' => array(
						array(
							'key' => FOO_VIDEO_POST_META,
							'compare' => 'EXISTS',
						)
					)
				) );

				$state['step'] = 1;
				$state['button_text'] =  __( 'Next', 'foogallery' );
				$state['message'] = sprintf( __('We found %d galleries and %d videos that need to be migrated. Click Next to migrate the galleries first.', 'foogallery' ), count( $gallery_posts ), count( $attachment_posts ) );
				$state['gallery_data'] = $gallery_posts;
				$state['attachment_data'] = $attachment_posts;

			} else if ( 1 === $state['step'] ) {
				//migrate the galleries

				$count = 0;
				foreach ($state['gallery_data'] as $gallery_id) {
					$gallery = FooGallery::get_by_id( $gallery_id );
					$this->migrate_gallery( $gallery, true );
					$count++;
				}

				$state['step'] = 2;
				$state['message'] = sprintf( __('%d galleries were successfully migrated. Click Next to migrate the video attachments.', 'foogallery' ), $count );
			} else if ( 2 === $state['step'] ) {
				//migrate the attachments

				$count = 0;
				foreach ($state['attachment_data'] as $attachment_id) {
					$this->migrate_attachment( $attachment_id );
					$count++;
				}

				$state['step'] = 3;
				$state['button_text'] =  __( 'Deactivate FooVideo', 'foogallery' );
				$state['message'] = sprintf( __('%d video attachments were successfully migrated. You can now safely deactivate the FooVideo extension.', 'foogallery' ), $count );
			} else if ( 3 === $state['step'] ) {

				//DEACTIVATE FOOVIDEO!
				$api = foogallery_extensions_api();
				$api->deactivate('foovideo');

				//delete the option for migrations
				delete_option( FOOGALLERY_FOOVIDEO_MIGRATION_REQUIRED );
			}

			$this->save_migration_state( $state );

			return $state;
		}

		public function reset_state() {
			delete_option( 'foogallery-video-migration' );
			return $this->get_migration_state();
		}

		/**
		 * Migrate a gallery from the old video slider to the new slider
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

				//we need to port all settings from 'videoslider' across to 'slider'
				foreach ( $settings as $name => $value) {
					if ( strpos( $name, 'videoslider_' ) === 0 ) {
						$new_name = str_replace( 'videoslider_', 'slider_', $name );
						$settings[$new_name] = $value;
					}
				}

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

		function migrate_attachment( $attachment_id ) {
			$video_info = get_post_meta( $attachment_id, FOO_VIDEO_POST_META, true );
			$type = $video_info['type'];

			//need to update the post mime type
			$update_attachment = array(
				'ID' => $attachment_id,
				'post_mime_type' => 'image/foogallery'
			);

			wp_update_post( $update_attachment );

			update_post_meta( $attachment_id, FOOGALLERY_VIDEO_POST_META, $video_info );
		}

	}
}