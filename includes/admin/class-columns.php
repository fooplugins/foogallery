<?php
/*
 * FooGallery Admin Columns class
 */

if ( ! class_exists( 'FooGallery_Admin_Columns' ) ) {

	class FooGallery_Admin_Columns {

		private $include_clipboard_script = false;

		function __construct() {
			//add_filter( 'manage_upload_columns', array($this, 'setup_media_columns') );
			//add_action( 'manage_media_custom_column', array($this, 'media_columns_content'), 10, 2 );
			add_filter( 'manage_edit-' . FOOGALLERY_CPT_GALLERY . '_columns', array(
				$this,
				'gallery_custom_columns'
			) );
			add_action( 'manage_posts_custom_column', array( $this, 'gallery_custom_column_content' ) );
			add_action( 'admin_footer', array($this, 'include_clipboard_script') );
		}

		function setup_media_columns( $columns ) {
			$columns['_galleries'] = __( 'Galleries', 'foogallery' );

			return $columns;
		}

		function media_columns_content( $column_name, $post_id ) {

		}

		function gallery_custom_columns( $columns ) {
			return array_slice( $columns, 0, 1, true ) +
			       array( 'icon' => '' ) +
			       array_slice( $columns, 1, null, true ) +
			       array(
				       FOOGALLERY_CPT_GALLERY . '_count' => __( 'Media', 'foogallery' ),
				       FOOGALLERY_CPT_GALLERY . '_shortcode' => __( 'Shortcode', 'foogallery' )
			       );
		}

		function gallery_custom_column_content( $column ) {
			global $post;

			switch ( $column ) {
				case FOOGALLERY_CPT_GALLERY . '_count':
					$gallery = FooGallery::get( $post );
					echo $gallery->image_count();
					break;
				case FOOGALLERY_CPT_GALLERY . '_shortcode':
					$gallery = FooGallery::get( $post );
					$shortcode = $gallery->shortcode();

					echo '<code id="foogallery-copy-shortcode" data-clipboard-text="' . esc_attr( $shortcode ) . '"
					  title="' . esc_attr__('Click to copy to your clipboard', 'foogallery') . '"
					  class="foogallery-shortcode">' . $shortcode . '</code>';

					$this->include_clipboard_script = true;

					break;
				case 'icon':
					$gallery = FooGallery::get( $post );
					$img = $gallery->featured_image_html( array(80, 60), true );
					if ( $img ) {
						echo $img;
					}
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