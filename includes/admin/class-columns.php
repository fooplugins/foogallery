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
						FOOGALLERY_CPT_GALLERY . '_template' => __( 'Layout', 'foogallery' ),
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

					$copy_label = __( 'Copy shortcode', 'foogallery' );

					echo '<div class="foogallery-shortcode-wrapper">';
					echo '<input type="text" readonly="readonly" value="' . esc_attr( $shortcode ) . '" class="foogallery-shortcode foogallery-shortcode-input" />';
					echo '<button type="button" class="button button-small foogallery-shortcode-button" data-shortcode="' . esc_attr( $shortcode ) . '" aria-label="' . esc_attr( $copy_label ) . '">';
					echo '<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>';
					echo '<span class="screen-reader-text">' . esc_html( $copy_label ) . '</span>';
					echo '</button>';
					echo '</div>';

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
				<style>
					.foogallery-shortcode-wrapper {
						display: flex;
						align-items: center;
						gap: 6px;
					}

					.foogallery-shortcode-input {
						max-width: 100%;
					}

					.foogallery-shortcode-button {
						display: none !important;
						align-items: center;
						justify-content: center;
						width: 20px;
						height: 25px;
						min-width: 30px;
						padding: 0 !important;
						background: #f8fbff;
						border: 1px solid #aaa !important;
						color: #aaa !important;
						box-shadow: none;
						transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
						cursor: pointer;
					}

					.foogallery-shortcode-button .dashicons {
						font-size: 16px;
						line-height: 1;
						width: 16px;
						height: 16px;
						pointer-events: none;
					}

					.foogallery-shortcode-message {
						margin: 6px 0 0;
					}

					@media screen and (max-width: 960px) {
						.foogallery-shortcode-button {
							display: inline-flex !important;	
						}

						.column-foogallery_shortcode .foogallery-shortcode-wrapper {
							
						}

						.column-foogallery_shortcode .foogallery-shortcode-input {
							position: absolute;
							width: 1px;
							height: 1px;
							padding: 0;
							margin: -1px;
							border: 0;
							clip: rect(0, 0, 0, 0);
							clip-path: inset(50%);
							overflow: hidden;
						}
					}
				</style>
				<script>
					jQuery(function($) {
						var copiedMessage = '<?php echo esc_js( __( 'Shortcode copied to clipboard :)', 'foogallery' ) ); ?>';
						var messageTimeout;

						function showCopyMessage($trigger) {
							if (messageTimeout) {
								clearTimeout(messageTimeout);
							}

							$('.foogallery-shortcode-message').remove();

							var $wrapper = $trigger.closest('.foogallery-shortcode-wrapper');
							var $message = $('<p class="foogallery-shortcode-message"></p>').text(copiedMessage);

							if ($wrapper.length) {
								$wrapper.after($message);
							} else {
								$trigger.after($message);
							}

							messageTimeout = setTimeout(function () {
								$message.fadeOut(200, function () {
									$(this).remove();
								});
							}, 2500);
						}

						function copyShortcode(shortcode, onSuccess) {
							if (navigator.clipboard && navigator.clipboard.writeText) {
								navigator.clipboard.writeText(shortcode).then(onSuccess).catch(fallbackCopy);
							} else {
								fallbackCopy();
							}

							function fallbackCopy() {
								var $temp = $('<textarea class="foogallery-shortcode-hidden"></textarea>');
								$temp.val(shortcode)
									.attr('readonly', 'readonly')
									.css({ position: 'absolute', left: '-9999px', top: '0' });
								$('body').append($temp);
								$temp[0].select();

								try {
									if (document.execCommand('copy')) {
										onSuccess();
									}
								} catch (err) {
									console.log('Oops, unable to copy!');
								}

								$temp.remove();
							}
						}

						$('.foogallery-shortcode-input').on('click', function () {
							var $input = $(this);
							$input[0].select();
							copyShortcode($input.val(), function () {
								showCopyMessage($input);
							});
						});

						$('.foogallery-shortcode-button').on('click', function () {
							var $button = $(this);
							copyShortcode($button.data('shortcode'), function () {
								showCopyMessage($button);
							});
						});
					});
				</script>
				<?php
			}
		}
	}
}
