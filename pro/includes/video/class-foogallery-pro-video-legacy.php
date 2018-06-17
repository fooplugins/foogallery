<?php
/**
 * All Legacy FooVideo Code lives in this class
 * Date: 19/05/2018
 */
if ( ! class_exists( 'FooGallery_Pro_Video_Legacy' ) ) {

	class FooGallery_Pro_Video_Legacy {

		function __construct() {
			add_filter( 'foogallery_is_attachment_video', array( $this, 'foogallery_is_attachment_video_legacy' ), 10, 2 );

			add_filter( 'foogallery_clean_video_url', array( $this, 'foogallery_clean_video_url_legacy_filter' ) );
			add_filter( 'foogallery_youtubekey', array( $this, 'foogallery_youtubekey_legacy_filter' ) );

			//check if the old FooVideo is installed
			if ( is_admin() && class_exists('Foo_Video') ) {
				add_action( 'admin_notices', array( $this, 'display_foovideo_notice') );
				add_action( 'admin_menu',  array( $this, 'add_migration_menu' ) );
			}

			// Ajax calls for migrating
			add_action( 'wp_ajax_foogallery_video_migration', array( $this, 'ajax_foogallery_video_migration' ) );
			add_action( 'wp_ajax_foogallery_video_migration_reset', array( $this, 'ajax_foogallery_video_migration_reset' ) );
		}

		/**
		 * Legacy way of knowing if an attachment is a video
		 *
		 * @param $is_video
		 * @param $foogallery_attachment
		 *
		 * @return bool
		 */
		function foogallery_is_attachment_video_legacy( $is_video, $foogallery_attachment ) {
			$video_info = get_post_meta( $foogallery_attachment->ID, FOOGALLERY_VIDEO_POST_META, true );

			return isset( $video_info ) && isset( $video_info['id'] );
		}

		/**
		 * Applies the legacy filter for backwards compatibility
		 * @param $url
		 *
		 * @return string
		 */
		function foogallery_clean_video_url_legacy_filter( $url ) {
			return apply_filters( 'foogallery_foovideo_clean_video_url', $url );
		}

		public function foogallery_build_class_attribute( $classes ) {
			global $current_foogallery_template;

			//first determine if the gallery has any videos

			//get the selected video icon
			$video_hover_icon = foogallery_gallery_template_setting( 'video_hover_icon', 'fg-video-default' );

			//backwards compatible for the videoslider
			if ( 'videoslider' === $current_foogallery_template ) {
				switch ( $video_hover_icon ) {
					case 'video-icon-default':
						$video_hover_icon = 'rvs-flat-circle-play';
						break;
					case 'video-icon-1':
						$video_hover_icon = 'rvs-plain-arrow-play';
						break;
					case 'video-icon-2':
						$video_hover_icon = 'rvs-youtube-play';
						break;
					case 'video-icon-3':
						$video_hover_icon = 'rvs-bordered-circle-play';
						break;
					default:
						$video_hover_icon = '';
				}
			} else {
				switch ( $video_hover_icon ) {
					case 'video-icon-default':
						$video_hover_icon = 'fg-video-default';
						break;
					case 'video-icon-1':
						$video_hover_icon = 'fg-video-1';
						break;
					case 'video-icon-2':
						$video_hover_icon = 'fg-video-2';
						break;
					case 'video-icon-3':
						$video_hover_icon = 'fg-video-3';
						break;
					case 'video-icon-4':
						$video_hover_icon = 'fg-video-4';
						break;
					default:
						$video_hover_icon = '';
				}
			}

			//include the video icon class
			$classes[] = $video_hover_icon;
			//get the video icon sticky state
			$video_icon_sticky = foogallery_gallery_template_setting( 'foovideo_sticky_icon', '' );
			if ( 'videoslider' === $current_foogallery_template && '' === $video_icon_sticky ) {
				$video_icon_sticky = 'rvs-show-play-on-hover';
			}
			//include the video sticky class
			$classes[] = $video_icon_sticky;
			return $classes;
		}

		/**
		 * Display a message if the FooVideo extension is also installed
		 */
		function display_foovideo_notice() {
			?>
			<div class="notice error">
				<p>
					<strong><?php _e('FooVideo Extension Redundant!', 'foogallery'); ?></strong><br/>
					<?php _e('You have both FooGallery PRO and the legacy FooVideo extension activated. FooGallery PRO now includes all the video features that FooVideo had, plus more! Which means the FooVideo extension is now redundant.', 'foogallery'); ?>
					<br/>
					<?php _e('Your video galleries will continue to work, but we recommend you migrate them across to FooGallery PRO and then deactivate FooVideo.', 'foogallery'); ?>
					<br/>
					<br/>
					<a href="#" class="button button-primary button-large"><?php _e('Migrate Video Galleries', 'foogallery'); ?></a>
					<br/>
					<br/>
				</p>
			</div>
			<?php
		}

		/**
		 * Outputs the video migration view
		 */
		function render_video_migration_view() {
			require_once 'view-video-migration.php';
		}

		/**
		 * Add a new menu item for running the migration
		 */
		function add_migration_menu() {
			foogallery_add_submenu_page( __( 'Video Migration', 'foogallery' ), 'manage_options', 'foogallery-video-migration', array( $this, 'render_video_migration_view', ) );
		}

		function ajax_foogallery_video_migration() {
			if ( check_admin_referer( 'foogallery_video_migration' ) ) {
				$helper = new FooGallery_Pro_Video_Migration_Helper();
				$state = $helper->run_next_migration_step();
				header( 'Content-type: application/json' );
				echo json_encode( $state );
			}
			die();
		}

		function ajax_foogallery_video_migration_reset() {
			if ( check_admin_referer( 'foogallery_video_migration' ) ) {
				$helper = new FooGallery_Pro_Video_Migration_Helper();
				$state = $helper->reset_state();
				header( 'Content-type: application/json' );
				echo json_encode( $state );
			}
			die();
		}
	}
}