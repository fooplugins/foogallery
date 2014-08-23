<?php
/*
 * FooGallery Admin Columns class
 */

if ( ! class_exists( 'FooGallery_Albums_Admin_Columns' ) ) {

	class FooGallery_Albums_Admin_Columns {

		private $include_clipboard_script = false;

		function __construct() {
			add_filter( 'manage_edit-' . FOOGALLERY_CPT_ALBUM . '_columns', array( $this, 'album_custom_columns' ) );
			add_action( 'manage_posts_custom_column', array( $this, 'album_custom_column_content' ) );
			add_action( 'admin_footer', array( $this, 'include_clipboard_script' ) );
		}

		function album_custom_columns( $columns ) {
			$columns[FOOGALLERY_CPT_ALBUM . '_galleries'] = __( 'Galleries', 'foogallery' );
			$columns[FOOGALLERY_CPT_ALBUM . '_shortcode'] = __( 'Shortcode', 'foogallery' );

			return $columns;
		}

		function album_custom_column_content( $column ) {
			global $post;

			switch ( $column ) {
				case FOOGALLERY_CPT_ALBUM . '_galleries':
					$album = FooGalleryAlbum::get( $post );
					echo $album->gallery_count();
					break;
				case FOOGALLERY_CPT_ALBUM . '_shortcode':
					$album = FooGalleryAlbum::get( $post );
					$shortcode = $album->shortcode();
					echo '<code data-clipboard-text="' . esc_attr( $shortcode ) . '"
					  title="' . esc_attr__( 'Click to copy to your clipboard', 'foogallery' ) . '"
					  class="foogallery-shortcode">' . $shortcode . '</code>';

					$this->include_clipboard_script = true;

					break;
			}
		}

		function include_clipboard_script() {
			if ( $this->include_clipboard_script ) {
				//zeroclipboard needed for copy to clipboard functionality
				$url = FOOGALLERY_URL . 'lib/zeroclipboard/ZeroClipboard.min.js';
				wp_enqueue_script( 'foogallery-zeroclipboard', $url, array('jquery'), FOOGALLERY_VERSION );

				?>
				<script>
					jQuery(function($) {
						var $el = $('.foogallery-shortcode');
						ZeroClipboard.config({ moviePath: "<?php echo FOOGALLERY_URL; ?>lib/zeroclipboard/ZeroClipboard.swf" });
						var client = new ZeroClipboard($el);

						client.on( "load", function(client) {
							client.on( "complete", function(client, args) {
								$('.foogallery-shortcode-message').remove();
								$(this).after('<p class="foogallery-shortcode-message"><?php _e( 'Shortcode copied to clipboard :)','foogallery' ); ?></p>');
							} );
						} );
					});
				</script>
				<?php
			}
		}
	}
}
