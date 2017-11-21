<?php
/*
 * FooGallery Admin Columns class
 */

if ( ! class_exists( 'FooGallery_Admin_Columns' ) ) {

	class FooGallery_Admin_Columns {

		private $include_clipboard_script = false;
		private $_foogallery = false;

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
						FOOGALLERY_CPT_GALLERY . '_usage' => __( 'Usage', 'foogallery' ),
					);
		}

		private function get_local_gallery( $post ) {
			if ( false === $this->_foogallery ) {
				$this->_foogallery = FooGallery::get( $post );
			} else if ( $this->_foogallery->ID !== $post->ID) {
				$this->_foogallery = FooGallery::get( $post );
			}

			return $this->_foogallery;
		}

		public function gallery_custom_column_content( $column ) {
			global $post;

			switch ( $column ) {
				case FOOGALLERY_CPT_GALLERY . '_template':
					$gallery = $this->get_local_gallery( $post );
					echo $gallery->gallery_template_name();
					break;
				case FOOGALLERY_CPT_GALLERY . '_count':
					$gallery = $this->get_local_gallery( $post );
					echo $gallery->image_count();
					break;
				case FOOGALLERY_CPT_GALLERY . '_shortcode':
					$gallery = $this->get_local_gallery( $post );
					$shortcode = $gallery->shortcode();

					echo '<input type="text" readonly="readonly" size="' . strlen( $shortcode )  . '" value="' . esc_attr( $shortcode ) . '" class="foogallery-shortcode" />';

					$this->include_clipboard_script = true;

					break;
				case 'icon':
					$gallery = $this->get_local_gallery( $post );
					$html_img = foogallery_find_featured_attachment_thumbnail_html( $gallery, array(
						'width' => 60,
						'height' => 60,
						'force_use_original_thumb' => true
					) );
					if ( $html_img ) {
						echo $html_img;
					}
					break;
				case FOOGALLERY_CPT_GALLERY . '_usage':
					$gallery = $this->get_local_gallery( $post );
					$posts = $gallery->find_usages();
					if ( $posts && count( $posts ) > 0 ) {
						echo '<ul class="ul-disc">';
						foreach ( $posts as $post ) {
							echo edit_post_link( $post->post_title, '<li>', '</li>', $post->ID );
						}
						echo '</ul>';
					} else {
						_e( 'Not used!', 'foogallery' );
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
