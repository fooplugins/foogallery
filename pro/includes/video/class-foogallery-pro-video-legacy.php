<?php
/**
 * All Legacy FooVideo Code lives in this class
  */
if ( ! class_exists( 'FooGallery_Pro_Video_Legacy' ) ) {

	define( 'FOOGALLERY_FOOVIDEO_MIGRATION_REQUIRED', 'foogallery-foovideo-migration-required' );
	define( 'FOOGALLERY_FOOVIDEO_MIGRATED', 'foogallery-foovideo-migrated' );

	class FooGallery_Pro_Video_Legacy {

		function __construct() {
			add_filter( 'foogallery_is_attachment_video', array( $this, 'foogallery_is_attachment_video_legacy' ), 10, 2 );

			add_filter( 'foogallery_clean_video_url', array( $this, 'foogallery_clean_video_url_legacy_filter' ) );
			add_filter( 'foogallery_youtubekey', array( $this, 'foogallery_youtubekey_legacy_filter' ) );

			if ( is_admin() ) {

				//check if the old FooVideo was/is installed
				if ( $this->migration_required() ) {
					add_action( 'admin_notices', array( $this, 'display_foovideo_notice') );
					add_action( 'admin_menu',  array( $this, 'add_migration_menu' ) );

					add_filter( 'foogallery_render_gallery_settings_metabox', array( $this, 'migrate_settings' ) );

					add_action( 'foogallery_after_save_gallery', array( $this, 'migrate_gallery' ), 99, 2 );
				}

				//check if the old FooVideo is still activated
				if ( class_exists( 'Foo_Video' ) ) {
					//rename the Video Slider template
					add_filter( 'foogallery_gallery_templates', array( $this, 'rename_videoslider_template' ), 99 );

					//remove legacy fields added by FooVideo
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'remove_legacy_template_fields' ), 99 );

					//short-circuit saving the post meta for video count on the gallery
					add_filter( 'update_post_metadata', array( $this, 'short_circuit_legacy_video_count' ), 10, 5 );
				}

				add_filter( 'foogallery_foovideo_discount_offer_notice_title', array( $this, 'override_discount_offer_notice_title' ) );
				add_filter( 'foogallery_foovideo_discount_offer_notice_message', array( $this, 'override_discount_offer_notice_message' ) );
				add_filter( 'foogallery_foovideo_discount_offer_menu', array( $this, 'override_discount_offer_menu' ) );
				add_filter( 'foogallery_foovideo_discount_offer_show_upgrade', '__return_false' );
				add_filter( 'foogallery_foovideo_discount_offer_message', array( $this, 'override_discount_offer_message' ) );
				add_filter( 'foogallery_foovideo_pricing_menu_text', array( $this, 'override_pricing_menu_text' ) );
			}

			if ( !is_admin() && class_exists( 'Foo_Video' ) ) {
				add_filter( 'foogallery_build_class_attribute', array( $this, 'foogallery_build_class_attribute' ), 20 );
			}

			// Ajax calls for migrating
			add_action( 'wp_ajax_foogallery_video_migration', array( $this, 'ajax_foogallery_video_migration' ) );
			add_action( 'wp_ajax_foogallery_video_migration_reset', array( $this, 'ajax_foogallery_video_migration_reset' ) );
		}

		/**
		 * Determines if a migration is needed
		 *
		 * @return bool
		 */
		function migration_required() {
			//first try to get the saved option
- 			$migration_required = get_option( FOOGALLERY_FOOVIDEO_MIGRATION_REQUIRED, 0 );

			//we require migration - get out early
			if ( "1" === $migration_required ) {
				return true;
			}

			if ( class_exists('Foo_Video') ) {
				//the legacy plugin is installed, so set the option for future use
				$migration_required = true;

				update_option( FOOGALLERY_FOOVIDEO_MIGRATION_REQUIRED, $migration_required );
			}

			//we have no option saved and no legacy plugin, so no migration required
			if ( 0 === $migration_required ) {
				$migration_required = false;
			}

			return $migration_required;
		}

		/**
		 * Migrate the gallery settings
		 *
		 * @param $foogallery
		 *
		 * @return FooGallery
		 */
		function migrate_settings( $foogallery ) {
			$helper = new FooGallery_Pro_Video_Migration_Helper();
			$foogallery = $helper->migrate_gallery( $foogallery, false );
			return $foogallery;
		}

		/**
		 * Short-circuit the post meta updates for the legacy FooVideo while both plugins are activated
		 *
		 * @param $check
		 * @param $object_id
		 * @param $meta_key
		 * @param $meta_value
		 * @param $prev_value
		 *
		 * @return bool
		 */
		function short_circuit_legacy_video_count( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
			if ( '_foovideo_video_count' === $meta_key ) {
				$check = true;
			}
			return $check;
		}

		/**
		 * Migrate video for the gallery that is saved
		 *
		 * @param $post_id
		 * @param $post
		 */
		function migrate_gallery($post_id, $post) {
			if ( $this->migration_required() ) {

				$helper = new FooGallery_Pro_Video_Migration_Helper();

				if ( $helper->check_gallery_needs_migration( $post_id ) ) {

					//migrate all the video attachments
					$gallery = FooGallery::get_by_id( $post_id );
					foreach ( $gallery->attachments() as $attachment ) {
						$helper->migrate_attachment( $attachment->ID );
					}

					$helper->migrate_video_counts( $post_id );
				}
			}
		}

		/**
		 * Remove the legacy template fields added by FooVideo
		 *
		 * @param $fields
		 *
		 * @return array
		 */
		function remove_legacy_template_fields( $fields ) {
			$new_fields = array();

			foreach ( $fields as $field ) {

				if ( $field['id'] !== 'foovideo_video_overlay' &&
					$field['id'] !== 'foovideo_sticky_icon' &&
					$field['id'] !== 'foovideo_video_size' &&
					$field['id'] !== 'foovideo_autoplay' ) {

					$new_fields[] = $field;
				}
			}

			return $new_fields;
		}

		/**
		 * Rename the Video Slider template to include the text 'Deprecated'
		 * @param $templates
		 *
		 * @return mixed
		 */
		function rename_videoslider_template( $templates ) {
			foreach( $templates as &$template ) {
				if ( 'videoslider' === $template['slug'] ) {
					$template['name'] = __( 'Video Slider (Deprecated!)', 'foogallery' );
				}
			}

			return $templates;
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
			//remove any legacy classes
			if ( ( $key = array_search( 'video-icon-1', $classes ) ) !== false ) {
				unset( $classes[$key] );
			}
			if ( ( $key = array_search( 'video-icon-2', $classes ) ) !== false ) {
				unset( $classes[$key] );
			}
			if ( ( $key = array_search( 'video-icon-3', $classes ) ) !== false ) {
				unset( $classes[$key] );
			}
			if ( ( $key = array_search( 'video-icon-default', $classes ) ) !== false ) {
				unset( $classes[$key] );
			}

			return $classes;
		}

		/**
		 * Display a message if the FooVideo extension is also installed
		 */
		function display_foovideo_notice() {
			if ( 'foogallery' !== foo_current_screen_post_type() ) return;

			$url = admin_url( add_query_arg( array( 'page' => 'foogallery-video-migration' ), foogallery_admin_menu_parent_slug() ) );
			?>
			<div class="notice error">
				<p>
					<strong><?php _e('FooGallery Video Migration Required!', 'foogallery'); ?></strong><br/>
					<?php if ( class_exists( 'Foo_Video' ) ) { ?>
						<?php _e('You have both FooGallery PRO and the legacy FooVideo extension activated. FooGallery PRO now includes all the video features that FooVideo had, plus more! Which means the FooVideo extension is now redundant.', 'foogallery'); ?>
						<br/>
						<?php _e('Your video galleries will continue to work, but we recommend you migrate them across to use the video features in FooGallery PRO as soon as possible.', 'foogallery'); ?>
					<?php } else { ?>
						<?php _e('At some point you had the FooVideo extension installed. FooGallery PRO now includes all the video features that FooVideo had, plus more! Which means the FooVideo extension is now redundant.', 'foogallery'); ?>
						<br/>
						<?php _e('You will need to migrate your video galleries across to use the new video features in FooGallery PRO as soon as possible.', 'foogallery'); ?>
					<?php } ?>
					<br/>
					<br/>
					<a href="<?php echo $url; ?>" class="button button-primary button-large"><?php _e('Migrate Video Galleries', 'foogallery'); ?></a>
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

		/**
		 * Handle the Video Migration Step from an AJAX call
		 */
		function ajax_foogallery_video_migration() {
			if ( check_admin_referer( 'foogallery_video_migration' ) ) {
				$helper = new FooGallery_Pro_Video_Migration_Helper();
				$state = $helper->run_next_migration_step();
				header( 'Content-type: application/json' );
				echo json_encode( $state );
			}
			die();
		}

		/**
		 * Handle the Video Migration Reset from an AJAX call
		 */
		function ajax_foogallery_video_migration_reset() {
			if ( check_admin_referer( 'foogallery_video_migration' ) ) {
				$helper = new FooGallery_Pro_Video_Migration_Helper();
				$state = $helper->reset_state();
				header( 'Content-type: application/json' );
				echo json_encode( $state );
			}
			die();
		}

		/**
		 * Override the Discount Offer admin notice title
		 * @param $title
		 *
		 * @return string
		 */
		function override_discount_offer_notice_title( $title ) {
			$title = __( 'FooGallery Renewal Offer Available!', 'foogallery' );
			return $title;
		}

		/**
		 * Override the Discount Offer admin notice message
		 * @param $message
		 *
		 * @return string
		 */
		function override_discount_offer_notice_message( $message ) {
			$message = __( 'We noticed that you own licenses for FooVideo and FooGallery PRO. FooGallery PRO now has all the awesome features of FooVideo, plus more! And because you already own both, you are eligible for a free renewal on your existing FooGallery PRO license.', 'foogallery' );
			return $message;
		}

		/**
		 * Override the Discount Offer menu
		 * @param $menu
		 *
		 * @return string
		 */
		function override_discount_offer_menu( $menu ) {
			$menu = __( 'Renewal Offer', 'foogallery' );
			return $menu;
		}

		/**
		 * Override the Discount Offer page message
		 * @param $message
		 *
		 * @return string
		 */
		function override_discount_offer_message( $message ) {
			$message = __( 'Thank you for your support - you are awesome! FooGallery PRO now has all the awesome features of FooVideo, plus more! And because you already own both, you are eligible for a free renewal on your existing FooGallery PRO license.', 'foogallery' );
			return $message;
		}

		/**
		 * Override the pricing page menu text
		 * @param $text
		 *
		 * @return string
		 */
		function override_pricing_menu_text( $text ) {
			$text = __('FooGallery -> Pricing', 'foogallery');
			return $text;
		}
	}
}