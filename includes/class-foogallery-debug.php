<?php
/**
 * Class used to help with debugging issues in FooGallery
 *
 * @package foogallery
 *
 * Date: 03/05/2021
 */

if ( ! class_exists( 'FooGallery_Debug' ) ) {

	/**
	 * Class FooGallery_Debug
	 */
	class FooGallery_Debug {

		/**
		 * FooGallery_Debug constructor.
		 */
		public function __construct() {
			if ( is_admin() ) {
				add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_meta_boxes_to_gallery' ) );
			}

			add_action( 'foogallery_loaded_template_after', array( $this, 'output_gallery_debug_info' ), 10, 1 );

			add_filter( 'foogallery_build_class_attribute', array( $this, 'add_debug_class' ), 10, 2 );
		}

		/**
		 * Add a debug class onto the container if in debug
		 *
		 * @param array      $classes The list of classes.
		 * @param FooGallery $gallery The gallery we are working with.
		 *
		 * @return array
		 */
		public function add_debug_class( $classes, $gallery ) {
			if ( foogallery_is_debug() ) {
				$classes[] = 'fg-debug';
			}

			return $classes;
		}

		/**
		 * Output an HTML comment containing some debug info for the gallery
		 *
		 * @param FooGallery $gallery the current gallery being output.
		 */
		public function output_gallery_debug_info( $gallery ) {
			if ( ! foogallery_is_debug() ) {
				return; // Get out early if we are not in debug mode!
			}
			echo '<!-- FooGallery Debug Info : Start';
			echo "\r\n";
			echo "\r\n";
			$main = array(
				'ID'         => $gallery->ID,
				'Template'   => $gallery->gallery_template,
				'Datasource' => $gallery->datasource_name,
			);
			if ( isset( $gallery->datasource_value ) ) {
				$main['Datasource Value'] = $gallery->datasource_value;
			}
			foogallery_render_debug_array( $main );
			echo "\r\n";
			echo 'FooGallery Settings';
			echo "\r\n";
			echo '===================';
			echo "\r\n";
			$settings = $gallery->settings;
			if ( is_array( $settings ) ) {
				ksort( $settings );
			}
			foogallery_render_debug_array( $settings );
			echo "\r\n";

			echo 'Attachment Info';
			echo "\r\n";
			echo '===============';
			echo "\r\n";
			$dimensions = array();
			foreach ( $gallery->attachments() as $attachment ) {
				$dimension = array(
					'url'  => $attachment->url,
					'type' => $attachment->type,
				);
				if ( isset( $attachment->has_thumbnail_dimensions ) && $attachment->has_thumbnail_dimensions ) {
					$dimension['width']  = $attachment->thumb_width;
					$dimension['height'] = $attachment->thumb_height;
				}
				if ( $attachment->ID > 0 ) {
					$dimensions[ $attachment->ID ] = $dimension;
				} else {
					$dimensions[] = $dimension;
				}
			}
			foogallery_render_debug_array( $dimensions );
			echo "\r\n";


			do_action( 'foogallery_gallery_debug_output', $gallery );
			echo 'FooGallery Debug Info : End -->';
		}

		/**
		 * Add debug metabox to foogallery edit screen in admin
		 *
		 * @param WP_Post $post the current post being edited.
		 */
		public function add_meta_boxes_to_gallery( $post ) {

			if ( foogallery_is_debug() ) {
				add_meta_box(
					'foogallery_debug',
					__( 'Gallery Debugging', 'foogallery' ),
					array( $this, 'render_upgrade_debug_metabox' ),
					FOOGALLERY_CPT_GALLERY,
					'normal',
					'low'
				);
			}
		}

		/**
		 * Render the debug metabox
		 *
		 * @param WP_Post $post the current post being edited.
		 */
		public function render_upgrade_debug_metabox( $post ) {
			$gallery = FooGallery::get( $post );

			if ( ! $gallery->is_new() ) {
				$settings = $gallery->settings;

				if ( is_array( $settings ) ) {
					ksort( $settings );
				}
				?>
				<style>
					#foogallery_debug .inside { overflow: scroll; }
					#foogallery_debug table { font-size: 0.8em; }
					#foogallery_debug td { vertical-align: top; }
				</style>
				<h3>Template</h3>
				<?php echo esc_html( $gallery->gallery_template ); ?>
				<h3>Datasource</h3>
				<?php echo esc_html( $gallery->datasource_name ); ?>
				<h3>Settings</h3>
				<div style="width:100%; height: 300px; overflow: scroll">
					<?php var_dump( $settings ); ?>
				</div>
				<?php
			}

			do_action( 'foogallery_admin_gallery_debug_output', $gallery );
		}
	}
}
