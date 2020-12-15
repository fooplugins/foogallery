<?php
/**
 * Polylang Compatibility Class
 * Credit : @Chrystl from wordpress.org - https://wordpress.org/support/topic/polylang-conflict-with-foo-gallery
 * Date: 30/08/2015
 */
if ( ! class_exists( 'FooGallery_Polylang_Compatibility' ) ) {

	class FooGallery_Polylang_Compatibility {

		function __construct() {

			if ( class_exists( 'Polylang' ) ) {

				add_filter( 'pll_get_post_types', array( $this, 'add_foogallery_cpt' ), 10, 2 );
				add_filter( 'pll_copy_post_metas', array( $this, 'ignore_foogallery_meta' ), 10, 2 );

				//whitelist the Polylang metabox
				add_filter( 'foogallery_metabox_sanity_foogallery', array( $this, 'add_pll_metaboxes' ) );

				add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			}
		}


		/**
		 * Adds Foogallery post type to Polylang settings as 'public' is set to false
		 *
		 * @param $post_types
		 * @param $settings
		 *
		 * @return mixed
		 */
		function add_foogallery_cpt( $post_types, $settings ) {
			if ( $settings ) {
				$post_types['foogallery'] = 'foogallery';
			}

			return $post_types;
		}

		/**
		 * Adds/whitelists polylang metabox 'ml_box' as Foogallery blocks it by default
		 *
		 * @param $metabox_ids
		 *
		 * @return array
		 */
		function add_pll_metaboxes ($metabox_ids) {
			$metabox_ids[] = 'ml_box';
			return $metabox_ids;
		}

		/**
		 * Unsets the copy and synchronization of the fooggallery post meta.
		 * A better solution will be to rewritte a copy function to get the translation
		 *
		 * @param $metas
		 * @param $sync
		 *
		 * @return mixed
		 */
		function ignore_foogallery_meta($metas, $sync) {

			$key = array_search( FOOGALLERY_META_SETTINGS, $metas );
			if ( $key ) unset( $metas[$key] );

			$key = array_search( FOOGALLERY_META_ATTACHMENTS, $metas );
			if ( $key ) unset( $metas[$key] );

			$key = array_search( FOOGALLERY_META_CUSTOM_CSS, $metas );
			if ( $key ) unset( $metas[$key] );

			$key = array_search( FOOGALLERY_META_TEMPLATE, $metas );
			if ( $key ) unset( $metas[$key] );

			$key = array_search( FOOGALLERY_META_SORT, $metas );
			if ( $key ) unset( $metas[$key] );

			return $metas;
		}

		/**
		 * Add an admin notice to FooGallery pages when Polylang setting media_support is set
		 */
		function admin_notice() {
			if ( FOOGALLERY_CPT_GALLERY === foo_current_screen_post_type() ) {

				$options = get_option('polylang');

				if ( array_key_exists( 'media_support', $options ) && 1 === $options['media_support'] ) {
					?>
					<div class="notice error">
						<p>
							<strong><?php _e( 'FooGallery + Polylang Alert : ', 'foogallery' ); ?></strong>
							<?php _e( 'We noticed that you have Polylang installed and you have chosen to activate languages and translations for media.', 'foogallery' ); ?><br />
							<?php _e( 'This may cause empty galleries on translated pages! To disable this feature, please visit Languages -> Settings.', 'foogallery' ); ?>
						</p>
					</div>
					<?php
				}
			}
		}
	}
}