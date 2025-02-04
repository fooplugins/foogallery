<?php
/**
 * FooGallery class for Image Protection
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_Pro_Protection' ) ) {

	define( 'FOOGALLERY_META_WATERMARK', '_foogallery_watermark' );
	define( 'FOOGALLERY_META_WATERMARK_PROGRESS', '_foogallery_watermark_progress' );
	define( 'FOOGALLERY_PROTECTION_MAX_GENERATION_COUNT', 100 );

	/**
	 * Class FooGallery_Pro_Protection
	 */
	class FooGallery_Pro_Protection {

		/**
		 * Constructor for the class
		 *
		 * Sets up all the appropriate hooks and actions
		 */
		function __construct() {
            add_action( 'plugins_loaded', array( $this, 'load_feature' ) );

            add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

		function load_feature() {
            if ( foogallery_feature_enabled( 'foogallery-protection' ) ) {
                // Swap out HREF attributes for the watermarked images if available.
				add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'change_link_attributes' ), 10, 3 );

				// Add data options for protection.
				add_filter( 'foogallery_build_container_data_options', array( $this, 'add_protection_data_options' ) );

				if ( is_admin() ) {
					// Add extra fields to the templates.
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_protection_fields' ), 20, 2 );

					// Render a custom field for a gallery template.
					add_filter( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_custom_field' ), 10, 3 );

					// Set the settings icon for protection.
					add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

					// Append some custom script after the gallery settings metabox.
					add_action( 'foogallery_after_render_gallery_settings_metabox', array( $this, 'append_script_for_watermarking' ), 10, 1 );

					// Callback for the generate watermark ajax call.
					add_action( 'wp_ajax_foogallery_protection_generate', array( $this, 'ajax_generate_watermarks' ) );

					// Add some settings for watermarking.
					add_filter( 'foogallery_admin_settings_override', array( $this, 'add_watermark_settings' ) );

					// Add a watermark test menu and page.
					add_action( 'foogallery_admin_menu_after', array( $this, 'add_watermark_test_menu' ) );

					// Render some custom settings types.
					add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_custom_setting_types' ) );

					// Actions for the attachment modal.
					add_filter( 'foogallery_attachment_modal_data', array( $this, 'attachment_modal_data_watermark' ), 40, 4 );
					add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'attachment_modal_display_tab_watermark' ), 40 );
					add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'attachment_modal_display_tab_content_watermark' ), 40, 1 );
					add_action( 'foogallery_attachment_modal_after_tab_container', array( $this, 'attachment_modal_extra_content_for_watermark' ), 40, 1 );
					add_action( 'wp_ajax_foogallery_attachment_modal_watermark_generate', array( $this, 'attachment_modal_ajax_generate_watermark' ) );
				}
            }
        }

		function register_extension( $extensions_list ) {
			$pro_features = foogallery_pro_features();

            $extensions_list[] = array(
                'slug' => 'foogallery-protection',
                'class' => 'FooGallery_Pro_Protection',
                'categories' => array( 'Premium' ),
                'title' => __( 'Protection', 'foogallery' ),
                'description' => $pro_features['protection']['desc'],
                'external_link_text' => __( 'Read documentation', 'foogallery' ),
                'external_link_url' => $pro_features['protection']['link'],
				'dashicon'          => 'dashicons-lock',
                'tags' => array( 'Premium' ),
                'source' => 'bundled',
                'activated_by_default' => true,
                'feature' => true
            );

            return $extensions_list;
        }

		/**
		 * Add the required protection data options
		 *
		 * @param array $options The original array of options.
		 *
		 * @return array
		 */
		public function add_protection_data_options( $options ) {
			$enable_protection = foogallery_gallery_template_setting( 'protection_no_right_click', 'no' );

			if ( 'yes' === $enable_protection ) {
				$options['protected'] = true;
			}

			return $options;
		}

		/**
		 * Ajax callback for generating watermarked images.
		 */
		public function ajax_generate_watermarks() {
			if ( check_admin_referer( 'foogallery_protection_generate' ) ) {
				if ( isset( $_POST['foogallery'] ) ) {
					$foogallery_id = intval( sanitize_text_field( wp_unslash( $_POST['foogallery'] ) ) );

					// Save the watermark generation progress against the gallery.
					$progress = get_post_meta( $foogallery_id, FOOGALLERY_META_WATERMARK_PROGRESS, true );

					if ( empty( $progress ) ) {
						// there is no progress, so start!
						$gallery = FooGallery::get_by_id( $foogallery_id );

						if ( isset( $_POST['attachments'] ) ) {
							$attachments = sanitize_text_field( wp_unslash( $_POST['attachments'] ) );
							if ( !empty( $attachments ) ) {
								$attachments = explode(',', $attachments );
							} else {
								$attachments = $gallery->item_attachment_ids();
							}
						} else {
							$attachments = $gallery->item_attachment_ids();
						}

						$images  = count( $attachments);

						$progress = array(
							'total'       => $images,
							'percent'     => 0,
							'progress'    => 0,
							'count'       => 0,
							'message'     => sprintf( __( '%d watermarked images to generate...', 'foogallery' ), $images ),
							'attachments' => $attachments,
							'continue'    => true
						);

						// Check if there are no attachments to watermark.
						if ( $images === 0 ) {
							$progress['continue'] = false;
						}
					} else {
						// What if there are no attachments left?
						$next_attachment_id = intval( array_shift( $progress['attachments'] ) );
						if ( $next_attachment_id > 0 ) {
							// Generate watermark!
							$this->generate_watermark( $next_attachment_id );
							$progress['progress'] = $progress['progress'] + 1;
							$progress['count']    = $progress['count'] + 1;
							$progress['percent']  = intval( $progress['progress'] / $progress['total'] * 100 );
						}
						if ( 0 === $progress['count'] && 0 === $progress['total'] ) {
							$progress['percent'] = 100;
						}
						if ( !is_array( $progress['attachments'] ) || 0 === count( $progress['attachments'] ) ) {
							$progress['percent'] = 100;
						}
						if ( $progress['percent'] < 100 ) {
							$progress['message'] = sprintf( __( '%1$d / %2$d watermarked images generated...', 'foogallery' ), $progress['progress'], $progress['total'] );
						} else {
							$progress['message'] = sprintf( __( 'Completed. %d watermarked images generated.', 'foogallery' ), $progress['total'] );
						}

						if ( $progress['count'] > FOOGALLERY_PROTECTION_MAX_GENERATION_COUNT ) {
							$progress['continue'] = false;
							$progress['count']    = 0;  // Reset the counter.
						} else {
							$progress['continue'] = true;
						}
					}

					if ( 100 === $progress['percent'] ) {
						// Remove the post meta, because generation is now complete.
						delete_post_meta( $foogallery_id, FOOGALLERY_META_WATERMARK_PROGRESS );
						$progress['continue'] = false;
					} else {
						update_post_meta( $foogallery_id, FOOGALLERY_META_WATERMARK_PROGRESS, $progress );
					}

					// If we are not going to continue, then return what the field HTML is, so it can be updated.
					if ( false === $progress['continue'] ) {
						ob_start();
						$gallery = FooGallery::get_by_id( $foogallery_id );
						$this->render_watermark_status_field( $gallery, $progress['count'] );
						$progress['refreshfield'] = true;
						$progress['fieldhtml']    = ob_get_contents();
						ob_end_clean();
					}

					wp_send_json_success( $progress );
				}
			}

			die();
		}

		/**
		 * Append some script for the generation button.
		 *
		 * @param FooGallery $gallery The gallery.
		 */
		public function append_script_for_watermarking( $gallery ) {
			wp_nonce_field( 'foogallery_protection_generate', 'foogallery_nonce_protection_generate', false );
			?>
			<script>
				jQuery( function() {
					jQuery(document).on('click', '.protection_generate', function(e) {
						e.preventDefault();

						var $this = jQuery( this );

						jQuery('.foogallery_protection_generate_spinner').addClass('is-active');

						var nonce = jQuery('#foogallery_nonce_protection_generate').val(),
							data = 'action=foogallery_protection_generate' +
						           '&foogallery=<?php echo esc_attr( $gallery->ID ); ?>' +
								   '&attachments=' + FOOGALLERY.attachments +
						           '&_wpnonce=' + nonce +
						           '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name="_wp_http_referer"]').val() );

						jQuery.ajax({
							type: "POST",
							url: ajaxurl,
							data: data,
							success: function(result) {
								if ( result.data ) {
									jQuery( '.foogallery_protection_generate_progress' ).html( result.data.message );
									if ( result.data.continue ) {
										//send another ajax request!
										$this.click();
									} else {
										jQuery( '.foogallery_protection_generate_spinner' ).removeClass( 'is-active' );
									}
									if ( result.data.refreshfield ) {
										jQuery('.foogallery_metabox_field-watermark_status').html(result.data.fieldhtml);
									}
								}
							},
							error: function() {
								jQuery( '.foogallery_protection_generate_spinner' ).removeClass( 'is-active' );
								jQuery( '.foogallery_protection_generate_progress' ).html( '<?php echo esc_html( __( 'There was an error! Please try again.', 'foogallery' ) ); ?>' );
							}
						});
					});
				});
			</script>
			<?php
		}

		/**
		 * Swap out the HREF attribute for the watermarked image.
		 *
		 * @param array                $attr The current attributes for the anchor.
		 * @param array                $args The arguments used to build up the attributes.
		 * @param FooGalleryAttachment $foogallery_attachment The current attachment.
		 *
		 * @return mixed
		 */
		public function change_link_attributes( $attr, $args, $foogallery_attachment ) {
			// We only care about swapping out for a watermarked image if we have a href.
			if ( array_key_exists( 'href', $attr ) ) {

				// Check if watermark is enabled, and then swap out the full-size image with the watermarked image.
				if ( 'yes' === foogallery_gallery_template_setting( 'protection_watermarking' ) ) {
					$attachment_watermark = get_post_meta( $foogallery_attachment->ID, FOOGALLERY_META_WATERMARK, true );

					if ( is_array( $attachment_watermark ) && array_key_exists( 'has_watermark', $attachment_watermark ) && $attachment_watermark['has_watermark'] ) {
						$attr['href'] = $attachment_watermark['url'];
					}
				}
			}

			return $attr;
		}

		/**
		 * Render the watermark status field
		 *
		 * @param $field
		 * @param $gallery
		 * @param $template
		 */
		public function render_custom_field( $field, $gallery, $template ) {
			if ( isset( $field ) && is_array( $field ) && isset( $field['type'] ) && 'watermark_status' === $field['type'] ) {
				$this->render_watermark_status_field( $gallery );
			}
		}

		private function render_watermark_status_field( $gallery, $progress_count = 0 ) {
			$watermark_data = $this->build_watermark_data( $gallery );
			if ( is_array( $watermark_data ) && array_key_exists( 'summary', $watermark_data ) ) {
				$summary_watermark_data = $watermark_data['summary'];
				$image_count            = $summary_watermark_data['images'];
				$watermark_count        = $summary_watermark_data['watermarks'];
				$outdated_count         = $summary_watermark_data['outdated'];
				$error_count            = $summary_watermark_data['errors'];
				if ( $progress_count > 0 ) {
					$image_count = $watermark_count = $progress_count;
				}
				if ( 0 === $image_count ) {
					echo esc_html( __( 'No images found. Add images to the gallery first.', 'foogallery' ) );
				} else {
					echo esc_html(sprintf(__('%1$d / %2$d watermarked images have been generated.', 'foogallery'), $watermark_count, $image_count));
				}
				if ( $outdated_count > 0 ) {
					echo ' ' . esc_html( sprintf( __( '%d are outdated and need to be re-generated!', 'foogallery' ), $outdated_count ) );
				}
				if ( $error_count > 0 ) {
					echo '<br /><br />';
					echo esc_html( sprintf( __( '%d had errors and could not be generated!', 'foogallery' ), $error_count ) );
				}
				echo '<br /><br />';
				echo '<button type="button" class="button button-primary button-large protection_generate">';
				$progress = get_post_meta( $gallery->ID, FOOGALLERY_META_WATERMARK_PROGRESS, true );
				if ( empty( $progress ) ) {
					echo esc_html( __( 'Generate Watermarked Images', 'foogallery' ) );
				} else {
					echo esc_html( __( 'Continue Generating', 'foogallery' ) );
				}
				echo '</button>';
				echo '<span style="position: absolute" class="spinner foogallery_protection_generate_spinner"></span>';
				echo '<span style="padding-left: 40px; line-height: 25px;" class="foogallery_protection_generate_progress"></span>';

			} else {
				echo esc_html( __( 'Something went wrong!', 'foogallery' ) );
			}
		}

		/**
		 * Builds up the watermark options.
		 *
		 * @return array
		 */
		public static function get_watermark_options() {
			$options = array(
				'image_quality' => foogallery_get_setting( 'watermark_jpeg_quality', 90 ),
				'transparency'  => foogallery_get_setting( 'watermark_transparency', 50 ),
				'image'         => foogallery_get_setting( 'watermark_image', FOOGALLERY_PRO_URL . 'includes/protection/watermarks/watermark-camera.png' ),
				'mode'          => foogallery_get_setting( 'watermark_mode', 'repeat' ),
				'size_type'     => foogallery_get_setting( 'watermark_image_size', 'scale' ),
				'margins'       => foogallery_get_setting( 'watermark_margins', 10 ),
			);

			if ( 'custom' === $options['size_type'] ) {
				$options['custom_size_width']  = foogallery_get_setting( 'watermark_image_size_custom_width', 100 );
				$options['custom_size_height'] = foogallery_get_setting( 'watermark_image_size_custom_height', 100 );
			} elseif ( 'scale' === $options['size_type'] ) {
				$options['scale'] = foogallery_get_setting( 'watermark_image_size_scale', 50 );
			}

			if ( 'single' === $options['mode'] ) {
				$options['position']    = foogallery_get_setting( 'watermark_position', 'center,center' );
				$options['offset_unit'] = foogallery_get_setting( 'watermark_offset_unit', 'pixels' );
				$options['offset_x']    = foogallery_get_setting( 'watermark_offset_x', 0 );
				$options['offset_y']    = foogallery_get_setting( 'watermark_offset_y', 0 );
			}

			return apply_filters( 'foogallery_protection_watermark_options', $options );
		}

		/**
		 * Get the watermark data for a gallery.
		 *
		 * @param FooGallery $gallery The gallery we are working with.
		 *
		 * @return array
		 */
		private function build_watermark_data( $gallery ) {
			global $foogallery_watermark_data;

			// We do not want to fetch this info every time for every template, so store it globally to save time.
			if ( ! isset( $foogallery_watermark_data ) ) {
				$watermark_options = self::get_watermark_options();

				$foogallery_watermark_data = array();

				$image_count           = 0;
				$watermark_image_count = 0;
				$outdated_count        = 0;
				$error_count           = 0;

				// Generate a checksum we can use to check if the watermark is outdated.
				$watermark_checksum = crc32( foogallery_json_encode( $watermark_options ) );

				foreach ( $gallery->attachments() as $attachment ) {
					$image_count++;
					if ( $attachment->ID > 0 ) {

						// Check if the attachment has a watermark.
						$attachment_watermark = get_post_meta( $attachment->ID, FOOGALLERY_META_WATERMARK, true );
						if ( ! is_array( $attachment_watermark ) ) {
							$attachment_watermark = array(
								'has_watermark' => false,
							);
						}
						if ( isset( $attachment_watermark['has_watermark'] ) && $attachment_watermark['has_watermark'] ) {
							$watermark_image_count++;
							$attachment_watermark['outdated'] = $attachment_watermark['checksum'] !== $watermark_checksum;

							if ( $attachment_watermark['outdated'] ) {
								$outdated_count ++;
							}
						}
						if ( isset( $attachment_watermark['error'] ) ) {
							$error_count++;
						}

						$foogallery_watermark_data[ $attachment->ID ] = $attachment_watermark;
					} else {
						// TODO : we are not dealing with a media library attachment! Figure this out later!
						$error_count++;
					}
				}
				$foogallery_watermark_data['summary'] = array(
					'images'     => $image_count,
					'watermarks' => $watermark_image_count,
					'errors'     => $error_count,
					'outdated'   => $outdated_count,
				);
			}

			return $foogallery_watermark_data;
		}

		/**
		 * Generate a checksum based on the watermark options
		 *
		 * @param array $watermark_options The options used for generating watermarks.
		 *
		 * @return int
		 */
		private function generate_checksum( $watermark_options ) {
			return crc32( foogallery_json_encode( $watermark_options ) );
		}

		/**
		 * Generate the watermark image for the attachment
		 *
		 * @param FooGalleryAttachment|int $attachment The attachment we want to generate a watermark for.
		 * @param array                    $watermark_options The watermark options.
		 *
		 * @return array
		 */
		public function generate_watermark( $attachment, $watermark_options = null) {
			if ( ! is_object( $attachment ) && intval( $attachment ) > 0 ) {
				$attachment = FooGalleryAttachment::get_by_id( $attachment );
			}

            if ( ! isset( $watermark_options ) ) {
                $watermark_options = self::get_watermark_options();
            }

			// Generate the checksum before making any changes to the options!
			$watermark_checksum = $this->generate_checksum( $watermark_options );

			// Override the directory the watermark gets saved to, so that there is no conflict with generated thumbs.
			$watermark_options['override_directory'] = 'wm';
			$watermark_options['original']           = $attachment->url; // Also set the original url to options, so that watermarked images do not override each other.

			// Decide on the watermark URL for the attachment.
			$generator      = new FooGallery_Thumb_Generator( $attachment->url, $watermark_options, true );
			$watermark_path = $generator->get_cache_file_path();
			$watermark_url  = $generator->get_cache_file_url();

			$attachment_path = foogallery_local_url_to_path( $attachment->url );
			if ( $attachment_path === false ) {
				// Fallback to URL, if the path cannot be determined.
				$attachment_path = $attachment->url;
			}

			// Create the image.
			$editor = wp_get_image_editor( $attachment_path, array( 'methods' => array( 'get_image' ) ) );

			if ( ! is_wp_error( $editor ) ) {
				$watermark = new FooGallery_Watermark( $editor );
				$watermark_image_path = foogallery_local_url_to_path( $watermark_options['image'] );
				$watermark->apply_watermark_image( $watermark_image_path, $watermark_options );

				// Save the watermarked image to disk.
				$result = $editor->save( $watermark_path );
				if ( ! is_wp_error( $result ) ) {
					// All good so far!
					$attachment_watermark = array(
						'url'           => $watermark_url,
						'checksum'      => $watermark_checksum,
						'has_watermark' => true,
					);

					// Check if there was a previously generated watermark, and delete it.
					$old_attachment_watermark = get_post_meta( $attachment->ID, FOOGALLERY_META_WATERMARK, true );
					if ( is_array( $old_attachment_watermark ) && $old_attachment_watermark['has_watermark'] ) {
						$old_watermark_path = trailingslashit( $generator->get_cache_file_directory() ) . basename( $old_attachment_watermark['url'] );
						// If the old is not the same as the new, then delete the old watermarked file.
						if ( $old_watermark_path !== $watermark_path ) {
							$fs = foogallery_wp_filesystem();
							$fs->delete( $old_watermark_path );
						}
					}
				} else {
					$attachment_watermark['error'] = $result;
				}
			} else {
				$attachment_watermark['error'] = $editor;
			}
			// Save the watermark data so we can use it later.
			update_post_meta( $attachment->ID, FOOGALLERY_META_WATERMARK, $attachment_watermark );

			return $attachment_watermark;
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param string $section_slug The section we want to check.
		 *
		 * @return string
		 */
		public function add_section_icons( $section_slug ) {

			if ( 'protection' === strtolower( $section_slug ) ) {
				return 'dashicons-lock';
			}

			return $section_slug;
		}

		/**
		 * Add protection fields to all gallery templates
		 *
		 * @param array  $fields The fields to override.
		 * @param string $template The gallery template.
		 *
		 * @return array
		 */
		public function add_protection_fields( $fields, $template ) {

			$new_fields = array();

			$new_fields[] = array(
				'id'      => 'protection_help',
				'desc'    => __( 'Image protection is only enabled on your full size images. Protecting thumbnails is not necessary!', 'foogallery' ),
				'section' => __( 'Protection', 'foogallery' ),
				'type'    => 'help',
			);

			$new_fields[] = array(
				'id'       => 'protection_no_right_click',
				'title'    => __( 'Right Click Protection', 'foogallery' ),
				'desc'     => __( 'Disable right-click on full size images.', 'foogallery' ),
				'section'  => __( 'Protection', 'foogallery' ),
				'spacer'   => '<span class="spacer"></span>',
				'type'     => 'radio',
				'default'  => 'no',
				'choices'  => array(
					'yes' => __( 'Enabled', 'foogallery' ),
					'no'  => __( 'Disabled', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
				),
			);

			$new_fields[] = array(
				'id'       => 'protection_watermarking',
				'title'    => __( 'Watermark Images', 'foogallery' ),
				'desc'     => __( 'Your full size images will be watermarked according to the global watermark settings.', 'foogallery' ),
				'section'  => __( 'Protection', 'foogallery' ),
				'spacer'   => '<span class="spacer"></span>',
				'default'  => 'no',
				'type'     => 'radio',
				'choices'  => array(
					'yes' => __( 'Use Watermarked Images', 'foogallery' ),
					'no'  => __( 'Use Original Images', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
				),
			);

			$new_fields[] = array(
				'id'       => 'protection_watermarking_status',
				'title'    => __( 'Protection Status', 'foogallery' ),
				'desc'     => __( 'The status of watermark protection for the current gallery.', 'foogallery' ),
				'section'  => __( 'Protection', 'foogallery' ),
				'type'     => 'watermark_status',
				'row_data' => array(
					'data-foogallery-hidden'          => true,
					'data-foogallery-show-when-field' => 'protection_watermarking',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value' => 'yes',
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
				),
			);

			// find the index of the advanced section.
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}

		/**
		 * Add some watermark settings
		 *
		 * @param array $settings The settings array.
		 *
		 * @return array
		 */
		public function add_watermark_settings( $settings ) {
			$settings['tabs']['watermarks'] = __( 'Protection', 'foogallery' );

			$preview_html = '<a target="_blank" href="' . admin_url( add_query_arg( array( 'page' => 'foogallery_watermark_test' ), foogallery_admin_menu_parent_slug() ) ) . '">' . __( 'Open watermark preview test page', 'foogallery' ) . '</a>';

			$settings['settings'][] = array(
				'id'    => 'watermark_test',
				'title' => __( 'Watermark Preview', 'foogallery' ),
				'desc'  => $preview_html,
				'type'  => 'html',
				'tab'   => 'watermarks',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_jpeg_quality',
				'title'   => __( 'Watermarked Image JPEG Quality %', 'foogallery' ),
				'desc'    => __( 'The image quality to be used when generating full-sized watermarked images for JPEG images. This is different to the Thumbnail JPEG Quality.', 'foogallery' ),
				'type'    => 'text',
				'default' => '90',
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_image',
				'title'   => __( 'Watermark Image', 'foogallery' ),
				'type'    => 'watermark_image',
				'default' => FOOGALLERY_PRO_URL . 'includes/protection/watermarks/watermark-camera.png',
				'tab'     => 'watermarks',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_transparency',
				'title'   => __( 'Watermark Transparency %', 'foogallery' ),
				'desc'    => __( 'The % transparency applied to the watermark image.', 'foogallery' ),
				'type'    => 'text',
				'default' => 50,
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_margins',
				'title'   => __( 'Margins (pixels)', 'foogallery' ),
				'desc'    => __( 'The margin, in pixels, applied to the watermark image.', 'foogallery' ),
				'type'    => 'text',
				'default' => 10,
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_mode',
				'title'   => __( 'Watermark Mode', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'repeat',
				'tab'     => 'watermarks',
				'choices' => array(
					'repeat' => '<strong>' . __( 'Repeat', 'foogallery' ) . '</strong> - ' . __( 'The watermark image is repeated across the whole image.', 'foogallery' ),
					'single' => '<strong>' . __( 'Single', 'foogallery' ) . '</strong> - ' . __( 'The watermark image is placed once on the image.', 'foogallery' ),
				),
				'class'   => 'foogallery_settings_radio foogallery_settings_watermark_mode',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_position',
				'title'   => __( 'Watermark Position', 'foogallery' ),
				'desc'    => __( 'The position to place the watermark image, when in Single Mode.', 'foogallery' ),
				'type'    => 'crop',
				'default' => 'center,center',
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text foogallery_settings_watermark_mode_field foogallery_settings_watermark_mode_single',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_image_size',
				'title'   => __( 'Watermark Image Size', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'scale',
				'tab'     => 'watermarks',
				'choices' => array(
					'original' => '<strong>' . __( 'Original', 'foogallery' ) . '</strong> - ' . __( 'The original size watermark image is used.', 'foogallery' ),
					'scale'    => '<strong>' . __( 'Scale', 'foogallery' ) . '</strong> - ' . __( 'Scale the watermark image down.', 'foogallery' ),
					'custom'   => '<strong>' . __( 'Custom', 'foogallery' ) . '</strong> - ' . __( 'Choose a custom size for the watermark image.', 'foogallery' ),
				),
				'class'   => 'foogallery_settings_radio foogallery_settings_watermark_image_size',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_image_size_scale',
				'title'   => __( 'Size Scale %', 'foogallery' ),
				'type'    => 'text',
				'default' => '50',
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text foogallery_settings_watermark_image_size_field foogallery_settings_watermark_image_size_scale',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_image_size_custom_width',
				'title'   => __( 'Custom Size Width (pixels)', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text foogallery_settings_watermark_image_size_field foogallery_settings_watermark_image_size_custom',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_image_size_custom_height',
				'title'   => __( 'Custom Size Height (pixels)', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text foogallery_settings_watermark_image_size_field foogallery_settings_watermark_image_size_custom',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_offset_unit',
				'title'   => __( 'Offset Unit', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'pixels',
				'desc'    => __( 'The unit of measurement to offset the watermark image, when in Single Mode.', 'foogallery' ),
				'tab'     => 'watermarks',
				'choices' => array(
					'pixels' => '<strong>' . __( 'Pixels', 'foogallery' ) . '</strong> - ' . __( 'The watermark image will be offset in pixels.', 'foogallery' ),
					'perc'   => '<strong>' . __( 'Percentage', 'foogallery' ) . '</strong> - ' . __( 'The watermark image will be offset by % of the original image.', 'foogallery' ),
				),
				'class'   => 'foogallery_settings_radio foogallery_settings_watermark_mode_field foogallery_settings_watermark_mode_single',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_offset_x',
				'title'   => __( 'Offset X', 'foogallery' ),
				'desc'    => __( 'The horizontal offset (based on the offset unit), when in Single Mode.', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text foogallery_settings_watermark_mode_field foogallery_settings_watermark_mode_single',
			);

			$settings['settings'][] = array(
				'id'      => 'watermark_offset_y',
				'title'   => __( 'Offset Y', 'foogallery' ),
				'desc'    => __( 'The vertical offset (based on the offset unit), when in Single Mode.', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'tab'     => 'watermarks',
				'class'   => 'foogallery_settings_short_text foogallery_settings_watermark_mode_field foogallery_settings_watermark_mode_single',
			);

			// Generate for all images; Generate for all galleries set to used watermarks; Generate Missing/Outdated Watermark Images;
//			$settings['settings'][] = array(
//				'id'      => 'watermark_bulk',
//				'title'   => __( 'Generate Watermarks', 'foogallery' ),
//				'type'    => 'watermark_bulk',
//				'section' => __( 'Bulk Operations', 'foogallery' ),
//				'tab'     => 'watermarks',
//			);

			return $settings;
		}

		/**
		 * Render any custom setting types to the settings page
		 *
		 * @param array $args The arguments.
		 */
		public function render_custom_setting_types( $args ) {
			if ( 'watermark_image' === $args['type'] ) {

				// Make sure the media assets are enqueued.
				wp_enqueue_media();

				$watermark_image = foogallery_get_setting( 'watermark_image', FOOGALLERY_PRO_URL . 'includes/protection/watermarks/watermark-camera.png' );
				$predefined_watermark_images = array(
					FOOGALLERY_PRO_URL . 'includes/protection/watermarks/watermark-camera.png',
					FOOGALLERY_PRO_URL . 'includes/protection/watermarks/watermark-image.png',
					FOOGALLERY_PRO_URL . 'includes/protection/watermarks/watermark-copy.png',
					FOOGALLERY_PRO_URL . 'includes/protection/watermarks/watermark-copyright.png',
				);
				?>
				<input class="foogallery_settings_long_text" type="text" id="watermark_image" name="foogallery[watermark_image]" value="<?php echo esc_url( $watermark_image ); ?>" />
				<input type="button" class="button foogallery_settings_watermark_image_select" value="<?php echo esc_html( __( 'Select Image', 'foogallery' ) ); ?>" />
				<br /><small><?php echo esc_html( __( 'The URL of the image you want to use as a watermark. Or use one of our predefined watermarks:', 'foogallery' ) ); ?></small>
				<br />
				<div class="foogallery_settings_radioicon foogallery_settings_watermark_image_predefined">
					<?php
					foreach ( $predefined_watermark_images as $image ) {
						?>
						<label data-value="<?php echo esc_url( $image ); ?>">
							<img width="100" height="100" src="<?php echo esc_url( $image ); ?>" />
						</label>
						<?php
					}
					?>
				</div>
				<script>
					var foogallery_settings_media_uploader;
					jQuery( function() {

						jQuery('.foogallery_settings_watermark_image_select').on( 'click', function(e) {
							e.preventDefault();

							if ( foogallery_settings_media_uploader ) {
								foogallery_settings_media_uploader.open();
								return;
							}

							// Create the media frame.
							foogallery_settings_media_uploader = wp.media( {
								multiple: false
							} ).on( "select", function() {
								var attachment = foogallery_settings_media_uploader.state().get( 'selection' ).first().toJSON();
								jQuery('#watermark_image').val( attachment.url );
							} );

							// Finally, open the modal.
							foogallery_settings_media_uploader.open();
						});

						jQuery('.foogallery_settings_watermark_image_predefined label').on( 'click', function(e) {
							jQuery('#watermark_image').val( jQuery(this).data('value') );
						});

						jQuery('.foogallery_settings_watermark_image_size input').on( 'change', function(e) {
							foogallery_settings_watermark_sizes();
						});

						jQuery('.foogallery_settings_watermark_mode input').on( 'change', function(e) {
							foogallery_settings_watermark_modes();
						});

						foogallery_settings_watermark_sizes();
						foogallery_settings_watermark_modes();
					});

					function foogallery_settings_watermark_modes() {
						jQuery('.foogallery_settings_watermark_mode_field').hide();
						var mode = jQuery('.foogallery_settings_watermark_mode input:checked').val();
						jQuery('.foogallery_settings_watermark_mode_' + mode).show();
					}

					function foogallery_settings_watermark_sizes() {
						jQuery('.foogallery_settings_watermark_image_size_field').hide();
						var size = jQuery('.foogallery_settings_watermark_image_size input:checked').val();
						jQuery('.foogallery_settings_watermark_image_size_' + size).show();
					}
				</script>
				<?php
			}
		}

		/**
		 * Registers the test menu and page
		 */
		public function add_watermark_test_menu() {
			// register the menu and page.
			foogallery_add_submenu_page(
				__( 'Watermark Tests', 'foogallery' ),
				'manage_options',
				'foogallery_watermark_test',
				array( $this, 'render_watermark_test_page' )
			);

			// hide the menu, but still keep the page registered so it can be rendered.
			remove_submenu_page( foogallery_admin_menu_parent_slug(), 'foogallery_watermark_test' );
		}

		/**
		 *  Renders the test page.
		 */
		public function render_watermark_test_page() {

			echo '<h2>' . esc_html( __( 'Watermark Test Page', 'foogallery' ) ) . '</h2>';

			$test_image_url = FooGallery_Thumbnails::find_first_image_in_media_library();

			$file_path = foogallery_local_url_to_path( $test_image_url );

			echo esc_html( __('Test Image URL', 'foogallery') ) . ': ' . $test_image_url . '<br />';
			echo esc_html( __('Test Image Path', 'foogallery') ) . ': ' . $file_path . '<br />';

			// Create the image.
			$editor = wp_get_image_editor( $file_path, array( 'methods' => array( 'get_image' ) ) );

			if ( is_wp_error( $editor ) ) {
				echo '<h3>' . esc_html( __( 'Error', 'foogallery' ) ) . '</h3>';
				var_dump( $editor );
				return;
			}

			$watermark_options = FooGallery_Pro_Protection::get_watermark_options();

			$watermark = new FooGallery_Watermark( $editor );
			$watermark->apply_watermark_image( foogallery_local_url_to_path( $watermark_options['image'] ), $watermark_options );

			echo '<h3>' . esc_html( __( 'Watermarked Image', 'foogallery' ) ) . '</h3>';

			$image = $editor->get_image();

			$image_base64 = $watermark->get_image_editor_helper()->get_image_base64( $image );

			$watermark->get_image_editor_helper()->cleanup( $image );

			echo '<img src="data:image/png;base64,' . $image_base64 . '" />';

			echo '<h3>' . esc_html( __( 'Original Image', 'foogallery' ) ) . '</h3>';

			echo '<img src="' . esc_url( $test_image_url ) . '" />';
		}

        /**
         * Image modal watermark tab title
         */
        public function attachment_modal_display_tab_watermark() { ?>
            <div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-watermark">
                <input type="radio" name="tabset" id="foogallery-tab-watermark" aria-controls="foogallery-panel-watermark">
                <label for="foogallery-tab-watermark"><?php esc_html_e('Watermark', 'foogallery'); ?></label>
            </div>
        <?php }

        /**
         * Image modal watermark tab content
         */
        public function attachment_modal_display_tab_content_watermark( $modal_data ) {
            if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
                if ( $modal_data['img_id'] > 0 ) {
                    $has_watermark = false;
                    $watermark_status = __( 'No watermark has been generated! Please generate a watermark.', 'foogallery' );

                    if ( isset( $modal_data['attachment_watermark'] ) && is_array( $modal_data['attachment_watermark'] ) ) {
                        $has_watermark = isset( $modal_data['attachment_watermark']['has_watermark'] ) && $modal_data['attachment_watermark']['has_watermark'];
                        if ( $has_watermark ) {

                            $watermark_status = __( 'An up to date watermark has been generated.', 'foogallery' );

                            $protection = new FooGallery_Pro_Protection();
                            $watermark_options = $protection->get_watermark_options();
                            // Generate a checksum we can use to check if the watermark is outdated.
                            $checksum = crc32( foogallery_json_encode( $watermark_options ) );

                            $actual_checksum = isset( $modal_data['attachment_watermark']['checksum'] ) ? $modal_data['attachment_watermark']['checksum'] : '';

                            if ( $actual_checksum !== $checksum ) {
                                $watermark_status = __( 'An outdated watermark has been generated. Generate a new watermark to use the latest watermark settings.', 'foogallery' );
                            }
                        }
                    } ?>
                    <section id="foogallery-panel-watermark" class="tab-panel">
                        <div id="foogallery-panel-watermark-status" class="settings">
							<span class="setting" data-setting="watermark-status">
								<label for="attachments-watermark-status" class="name"><?php esc_html_e('Watermark Status', 'foogallery'); ?></label>
								<span><?php echo esc_html( $watermark_status ); ?></span>
							</span>
                        </div>
                        <?php if ( $has_watermark ) { ?>
                            <div id="foogallery-panel-watermark-preview" class="settings <?php echo isset( $modal_data['attachment_watermark']['url'] ) ? 'watermark-preview-show' : ''; ?>">
							<span class="setting" data-setting="watermark-image-preview">
								<label for="attachments-watermark-image-preview" class="name"><?php esc_html_e('Watermark Image Preview', 'foogallery'); ?></label>
								<a id="attachments-watermark-image-preview" href="<?php echo esc_url( $modal_data['attachment_watermark']['url'] ); ?>" target="_blank">
									<img width="150" src="<?php echo esc_url( $modal_data['attachment_watermark']['url'] ); ?>" alt="watermark">
								</a>
							</span>
                            </div>
                        <?php } ?>
                        <div class="foogallery_metabox_field-watermark_status settings">
							<span class="setting" data-setting="watermark-generate-button">
								<label class="name"><?php esc_html_e('Generate Watermark', 'foogallery'); ?></label>
                                <div>
                                    <button id="attachments-watermark-generate-btn" type="button" style="width: 100px"
                                            class="button button-primary button-large attachment_modal_watermark_generate"
                                            data-attach_id="<?php echo esc_attr( $modal_data['img_id'] ); ?>"
                                            data-nonce="<?php echo esc_attr( wp_create_nonce('foogallery_attachment_modal_watermark_generate') ); ?>">
                                    <?php echo esc_html( __( 'Generate', 'foogallery' ) ); ?>
                                    </button>
                                    <span style="position: absolute" class="spinner foogallery_protection_generate_spinner"></span>
                                </div>
							</span>
                        </div>
                    </section>
                    <?php
                }
            }
        }

        public function attachment_modal_extra_content_for_watermark( $modal_data ) {
            ?>
            <script>
                jQuery( function() {
                    jQuery(document).on('click', '.attachment_modal_watermark_generate', function(e) {
                        e.preventDefault();

                        var $this = jQuery( this ),
                            attach_id = $this.attr('data-attach_id'),
                            nonce = $this.attr('data-nonce');

                        jQuery('.foogallery_protection_generate_spinner').addClass('is-active');

                        var data = 'action=foogallery_attachment_modal_watermark_generate' +
                            '&attachment_id=' + attach_id +
                            '&nonce=' + nonce;

                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: data,
                            success: function(result) {
                                if ( result.html ) {
                                    jQuery( '#foogallery-panel-watermark' ).replaceWith( result.html );
                                    jQuery( '#foogallery-panel-watermark' ).addClass('active');
                                }
                            },
                            error: function() {
                                alert( 'Oops!' );
                            }
                        });
                    });
                });
            </script>
            <?php
        }

        /**
         * Image modal watermark tab data update
         */
        public function attachment_modal_data_watermark( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                if ( defined( 'FOOGALLERY_META_WATERMARK' ) ) {
                    $modal_data['attachment_watermark'] = get_post_meta($attachment_id, FOOGALLERY_META_WATERMARK, true);
                }
            }
            return $modal_data;
        }

        /**
         * Ajax callback for generating watermarked image for single attachment.
         */
        public function attachment_modal_ajax_generate_watermark() {
            // Check for nonce security
            if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery_attachment_modal_watermark_generate' ) ) {
                die ( 'Busted!');
            }

            if ( isset( $_POST['attachment_id'] ) ) {
                $attachment_id = intval( sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) ) );

                // Generate watermark image for given attachment id
                $this->generate_watermark( $attachment_id );

                ob_start();
                $modal_data = array(
                    'img_id' => $attachment_id
                );
                $modal_data = $this->attachment_modal_data_watermark( $modal_data, null, $attachment_id, 0 );
                $this->attachment_modal_display_tab_content_watermark( $modal_data );

                wp_send_json( array(
                    'html' => ob_get_clean()
                ) );
            }

            die();
        }
	}
}
