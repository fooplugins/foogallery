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
			$columns[FOOGALLERY_CPT_ALBUM . '_template'] = __( 'Template', 'foogallery' );
			$columns[FOOGALLERY_CPT_ALBUM . '_galleries'] = __( 'Galleries', 'foogallery' );
			$columns[FOOGALLERY_CPT_ALBUM . '_shortcode'] = __( 'Shortcode', 'foogallery' );

			return $columns;
		}

		function album_custom_column_content( $column ) {
			global $post;

			switch ( $column ) {
				case FOOGALLERY_CPT_ALBUM . '_template':
					$album = FooGalleryAlbum::get( $post );
					$template = $album->album_template_details();
					if ( false !== $template ) {
						echo $template['name'];
					}
					break;
				case FOOGALLERY_CPT_ALBUM . '_galleries':
					$album = FooGalleryAlbum::get( $post );
					echo $album->gallery_count();
					break;
				case FOOGALLERY_CPT_ALBUM . '_shortcode':
					$album = FooGalleryAlbum::get( $post );
					$shortcode = $album->shortcode();
					echo '<input type="text" readonly="readonly" size="' . strlen( $shortcode )  . '" value="' . esc_attr( $shortcode ) . '" class="foogallery-shortcode" />';
					$this->include_clipboard_script = true;
					break;
			}
		}

		function include_clipboard_script() {
			if ( $this->include_clipboard_script ) { ?>
				<script>
					jQuery(function($) {
						$('.foogallery-shortcode').on('click', function () {
							try {
								//select the contents
								this.select();
								//copy the selection
								document.execCommand('copy');
								//show the copied message
								$('.foogallery-shortcode-message').remove();
								$(this).after('<p class="foogallery-shortcode-message"><?php _e( 'Shortcode copied to clipboard :)','foogallery' ); ?></p>');
							} catch(err) {
								console.log('Oops, unable to copy!');
							}
						});
					});
				</script>
				<?php
			}
		}
	}
}
