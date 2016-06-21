<?php
/*
 * FooGallery Admin Columns class
 */

if ( ! class_exists( 'FooGallery_Admin_Columns' ) ) {

	class FooGallery_Admin_Columns {

		private $include_clipboard_script = false;

		public function __construct() {
			add_filter( 'manage_edit-' . FOOGALLERY_CPT_GALLERY . '_columns', array( $this, 'gallery_custom_columns' ) );
			add_action( 'manage_posts_custom_column', array( $this, 'gallery_custom_column_content' ) );
			add_action( 'admin_footer', array( $this, 'include_clipboard_script' ) );
		}

		public function gallery_custom_columns( $columns ) {
			return array_slice( $columns, 0, 1, true ) +
					array( 'icon' => '' ) +
					array_slice( $columns, 1, null, true ) +
					array(
						FOOGALLERY_CPT_GALLERY . '_template' => __( 'Template', 'foogallery' ),
						FOOGALLERY_CPT_GALLERY . '_count' => __( 'Media', 'foogallery' ),
						FOOGALLERY_CPT_GALLERY . '_shortcode' => __( 'Shortcode', 'foogallery' ),
					);
		}

		public function gallery_custom_column_content( $column ) {
			global $post;

			switch ( $column ) {
				case FOOGALLERY_CPT_GALLERY . '_template':
					$gallery = FooGallery::get( $post );
					echo $gallery->gallery_template_name();
					break;
				case FOOGALLERY_CPT_GALLERY . '_count':
					$gallery = FooGallery::get( $post );
					echo $gallery->image_count();
					break;
				case FOOGALLERY_CPT_GALLERY . '_shortcode':
					$gallery = FooGallery::get( $post );
					$shortcode = $gallery->shortcode();

					echo '<input type="text" readonly="readonly" size="' . strlen( $shortcode )  . '" value="' . esc_attr( $shortcode ) . '" class="foogallery-shortcode" />';

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

		public function include_clipboard_script() {
			if ( $this->include_clipboard_script ) { ?>
				<script>
					jQuery(function($) {
						$('.foogallery-shortcode').click( function () {
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
