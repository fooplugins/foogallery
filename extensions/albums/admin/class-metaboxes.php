<?php

/*
 * FooGallery Admin Album MetaBoxes class
 */

if ( ! class_exists( 'FooGallery_Admin_Album_MetaBoxes' ) ) {

	class FooGallery_Admin_Album_MetaBoxes {

		private $_album;

		public function __construct() {
			//add our foogallery metaboxes
			add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_ALBUM, array( $this, 'add_meta_boxes' ) );

			//save extra post data for a gallery
			add_action( 'save_post', array( $this, 'save_album' ) );

			//add scripts used by metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'include_required_scripts' ) );

			// Ajax call for getting gallery details
			add_action( 'wp_ajax_foogallery_get_gallery_details', array( $this, 'ajax_get_gallery_details' ) );

			// Ajax call for saving gallery details
			add_action( 'wp_ajax_foogallery_save_gallery_details', array( $this, 'ajax_save_gallery_details' ) );

			// Save details for the gallery
			add_action( 'foogallery_album_gallery_details_save', array( $this, 'gallery_details_save' ), 10, 3 );
		}

		public function add_meta_boxes( $post ) {
			add_meta_box(
				'foogalleryalbum_galleries',
				__( 'Galleries - click a gallery to add it to your album.', 'foogallery' ),
				array( $this, 'render_gallery_metabox' ),
				FOOGALLERY_CPT_ALBUM,
				'normal',
				'high'
			);

			add_meta_box(
				'foogalleryalbum_settings',
				__( 'Settings', 'foogallery' ),
				array( $this, 'render_settings_metabox' ),
				FOOGALLERY_CPT_ALBUM,
				'normal',
				'high'
			);

			add_meta_box(
				'foogalleryalbum_customcss',
				__( 'Custom CSS', 'foogallery' ),
				array( $this, 'render_customcss_metabox' ),
				FOOGALLERY_CPT_ALBUM,
				'normal',
				'low'
			);

			add_meta_box(
				'foogalleryalbum_shortcode',
				__( 'Album Shortcode', 'foogallery' ),
				array( $this, 'render_shortcode_metabox' ),
				FOOGALLERY_CPT_ALBUM,
				'side',
				'default'
			);

			add_meta_box(
				'foogalleryalbum_sorting',
				__( 'Album Sorting', 'foogallery' ),
				array( $this, 'render_sorting_metabox' ),
				FOOGALLERY_CPT_ALBUM,
				'side',
				'default'
			);
		}

		public function get_album( $post ) {
			if ( ! isset( $this->_album ) ) {
				$this->_album = FooGalleryAlbum::get( $post );
			}

			return $this->_album;
		}

		public function save_album( $post_id ) {
			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// verify nonce
			if ( array_key_exists( FOOGALLERY_CPT_ALBUM . '_nonce', $_POST ) &&
			     wp_verify_nonce( $_POST[ FOOGALLERY_CPT_ALBUM . '_nonce' ], plugin_basename( FOOGALLERY_FILE ) )
			) {
				//if we get here, we are dealing with the Album custom post type

				$galleries = apply_filters( 'foogallery_save_album_galleries', explode( ',', $_POST[ FOOGALLERY_ALBUM_META_GALLERIES ] ) );
				update_post_meta( $post_id, FOOGALLERY_ALBUM_META_GALLERIES, $galleries );

				if ( !empty( $_POST[FOOGALLERY_ALBUM_META_TEMPLATE] ) ) {
					update_post_meta( $post_id, FOOGALLERY_ALBUM_META_TEMPLATE, $_POST[FOOGALLERY_ALBUM_META_TEMPLATE] );
				}

				if ( isset( $_POST[FOOGALLERY_ALBUM_META_SORT] ) ) {
					update_post_meta( $post_id, FOOGALLERY_ALBUM_META_SORT, $_POST[FOOGALLERY_ALBUM_META_SORT] );
				}

				$settings = isset($_POST['_foogallery_settings']) ?
					$_POST['_foogallery_settings'] : array();

				$settings = apply_filters( 'foogallery_save_album_settings', $settings );

				if ( !empty( $settings ) ) {
					update_post_meta( $post_id, FOOGALLERY_META_SETTINGS_OLD, $settings );
				} else {
					delete_post_meta( $post_id, FOOGALLERY_META_SETTINGS_OLD );
				}

				$custom_css = foogallery_sanitize_full( isset( $_POST[FOOGALLERY_META_CUSTOM_CSS] ) ?
					$_POST[FOOGALLERY_META_CUSTOM_CSS] : '' );

				if ( empty( $custom_css ) ) {
					delete_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS );
				} else {
					update_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS, $custom_css );
				}

				// update usage for each of the galleries.
				foreach ( $galleries as $gallery_id ) {
					add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE, $gallery_id, false );
				}

				do_action( 'foogallery_after_save_album', $post_id, $_POST );
			}
		}

		public function get_ordered_galleries( $album ) {
		    //exclude the galleries already added to the album
            $excluded_galleries = $album->gallery_ids;

            //allow more galleries to be excluded
            $excluded_galleries = apply_filters( 'foogallery_album_excluded_galleries', $excluded_galleries, $album );

            $args = array();

            $limit = intval( foogallery_get_setting( 'album_limit_galleries', '' ) );

            if ( $limit > 0 ) {
                $args['nopaging'] = false;
                $args['posts_per_page'] = $limit;
            }

			//get all other galleries
			$galleries = foogallery_get_all_galleries( $excluded_galleries, $args );

			$album_galleries = $album->galleries();

			return array_merge( $album_galleries, $galleries );
		}

		public function render_gallery_metabox( $post ) {
			$album = $this->get_album( $post );

			$galleries = $this->get_ordered_galleries( $album );

			wp_enqueue_style( 'media-views' );

			?>
			<input type="hidden" name="<?php echo FOOGALLERY_CPT_ALBUM; ?>_nonce"
			       id="<?php echo FOOGALLERY_CPT_ALBUM; ?>_nonce"
			       value="<?php echo wp_create_nonce( plugin_basename( FOOGALLERY_FILE ) ); ?>"/>
			<input type="hidden" name='foogallery_album_galleries' id="foogallery_album_galleries"
			       value="<?php echo $album->gallery_id_csv(); ?>"/>
			<div>
				<?php if ( !$album->has_galleries() ) { ?>
					<div class="foogallery-album-error">
						<?php _e( 'There are no galleries selected for your album yet! Click any gallery to add it to your album.', 'foogallery' ); ?>
					</div>
				<?php } ?>

				<div class="foogallery-album-info-modal media-modal">
					<div class="media-modal-content">
						<div class="media-frame mode-select">
							<div class="media-frame-title">
								<h1><?php _e('Edit Gallery Details', 'foogallery'); ?></h1>
								<span class="spinner is-active"></span>
							</div>
							<div class="modal-content">
								<?php wp_nonce_field( 'foogallery_album_gallery_details', 'foogallery_album_gallery_details_nonce', false ); ?>
								<div class="gallery-details" data-loading="<?php _e( 'Loading details for ', 'foogallery' ); ?>"></div>
							</div>
						</div>
						<div class="media-frame-toolbar">
							<div class="media-toolbar">
								<div class="media-toolbar-secondary"></div>
								<div class="media-toolbar-primary search-form">
									<button type="button" class="button media-button button-primary button-large media-button-select gallery-details-save"><?php _e('Save Gallery Details', 'foogallery'); ?></button>
									<span class="spinner"></span>
								</div>
							</div>
						</div>
					</div>
					<button type="button" class="button-link media-modal-close">
						<span class="media-modal-icon"><span class="screen-reader-text"><?php _e('Close media panel', 'foogallery'); ?></span></span>
					</button>

				</div>
				<div class="foogallery-album-info-modal-backdrop media-modal-backdrop"></div>


				<ul class="foogallery-album-gallery-list">
					<?php
					foreach ( $galleries as $gallery ) {
						$img_src  = foogallery_find_featured_attachment_thumbnail_src( $gallery );
						$images   = $gallery->image_count();
						$selected = $album->includes_gallery( $gallery->ID ) ? ' selected' : '';
						$title = $gallery->safe_name();
						?>
						<li class="foogallery-pile">
							<div class="foogallery-gallery-select landscape<?php echo $selected; ?>" data-foogallery-id="<?php echo $gallery->ID; ?>">
								<div style="display: table;">
									<div style="display: table-cell; vertical-align: middle; text-align: center;">
										<img src="<?php echo $img_src; ?>"/>
										<h3>
                                            <?php echo esc_html( $title ); ?>
                                            <span><?php echo $images; ?></span>
                                        </h3>
									</div>
								</div>
								<a class="info foogallery-album-info" href="#"
								   title="<?php _e( 'Edit Album Info', 'foogallery' ); ?>"
								   data-gallery-title="<?php echo $title; ?>"
								   data-gallery-id="<?php echo $gallery->ID; ?>"><span class="dashicons dashicons-info"></span>
                                </a>
							</div>
						</li>
					<?php } ?>
				</ul>
				<div style="clear: both;"></div>
			</div>
		<?php
		}

		public function render_shortcode_metabox( $post ) {
			$album   = $this->get_album( $post );
			$shortcode = $album->shortcode();
			?>
			<p class="foogallery-shortcode">
				<input type="text" id="foogallery_copy_shortcode" size="<?php echo strlen( $shortcode ); ?>" value="<?php echo htmlspecialchars( $shortcode ); ?>" readonly="readonly" />
			</p>
			<p>
				<?php _e( 'Paste the above shortcode into a post or page to show the album.', 'foogallery' ); ?>
			</p>
			<script>
				jQuery(function($) {
					var shortcodeInput = document.querySelector('#foogallery_copy_shortcode');
					shortcodeInput.addEventListener('click', function () {
						try {
							// select the contents
							shortcodeInput.select();
							//copy the selection
							document.execCommand('copy');
							//show the copied message
							$('.foogallery-shortcode-message').remove();
							$(shortcodeInput).after('<p class="foogallery-shortcode-message"><?php _e( 'Shortcode copied to clipboard :)','foogallery' ); ?></p>');
						} catch(err) {
							console.log('Oops, unable to copy!');
						}
					}, false);
				});
			</script>
		<?php
		}

		public function render_sorting_metabox( $post ) {
			$album = $this->get_album( $post );
			$sorting_options = foogallery_sorting_options(); ?>
			<p>
				<?php _e('Change the way galleries are sorted within your album. By default, they are sorted in the order you see them.', 'foogallery'); ?>
			</p>
			<?php
			foreach ( $sorting_options as $sorting_key => $sorting_label ) { ?>
				<p>
				<input type="radio" value="<?php echo $sorting_key; ?>" <?php checked( $sorting_key === $album->sorting ); ?> id="FooGallerySettings_AlbumSort_<?php echo $sorting_key; ?>" name="<?php echo FOOGALLERY_ALBUM_META_SORT; ?>" />
				<label for="FooGallerySettings_AlbumSort_<?php echo $sorting_key; ?>"><?php echo $sorting_label; ?></label>
				</p><?php
			}
		}

		public function render_settings_metabox( $post ) {
			$album = $this->get_album( $post );
			$available_templates = foogallery_album_templates();
			$album_template = foogallery_default_album_template();
			if ( ! empty($album->album_template) ) {
				$album_template = $album->album_template;
			}
			if ( false === $album_template ) {
				$album_template = $available_templates[0]['slug'];
			}
			$hide_help = 'on' == foogallery_get_setting( 'hide_gallery_template_help' );
			?>
			<table class="foogallery-album-metabox-settings">
				<tbody>
				<tr class="foogallery_template_field foogallery_template_field_selector">
					<th>
						<label for="FooGallerySettings_AlbumTemplate"><?php _e( 'Album Template', 'foogallery' ); ?></label>
					</th>
					<td>
						<select id="FooGallerySettings_AlbumTemplate" name="<?php echo FOOGALLERY_ALBUM_META_TEMPLATE; ?>">
							<?php
							foreach ( $available_templates as $template ) {
								$selected = ($album_template === $template['slug']) ? 'selected' : '';
								echo "<option {$selected} value=\"{$template['slug']}\">{$template['name']}</option>";
							}
							?>
						</select>
						<br />
						<small><?php _e( 'The album template that will be used when the album is output to the frontend.', 'foogallery' ); ?></small>
					</td>
				</tr>
				<?php
				foreach ( $available_templates as $template ) {
					$field_visibility = ($album_template !== $template['slug']) ? 'style="display:none"' : '';
					$section          = '';
					$fields = isset( $template['fields'] ) ? $template['fields'] : array();
					foreach ( $fields as $field ) {
						//allow for the field to be altered by extensions.
						$field = apply_filters( 'foogallery_alter_gallery_template_field', $field, $album );

						$class ="foogallery_template_field foogallery_template_field-{$template['slug']} foogallery_template_field-{$template['slug']}-{$field['id']}";

						if ( isset($field['section']) && $field['section'] !== $section ) {
							$section = $field['section'];
							?>
							<tr class="<?php echo $class; ?>" <?php echo $field_visibility; ?>>
								<td colspan="2"><h4><?php echo $section; ?></h4></td>
							</tr>
						<?php }
						if (isset($field['type']) && 'help' == $field['type'] && $hide_help) {
							continue; //skip help if the 'hide help' setting is turned on
						}
						?>
						<tr class="<?php echo $class; ?>" <?php echo $field_visibility; ?>>
							<?php if ( isset($field['type']) && 'help' == $field['type'] ) { ?>
								<td colspan="2">
									<div class="foogallery-help">
										<?php echo $field['desc']; ?>
									</div>
								</td>
							<?php } else { ?>
								<th>
									<label for="FooGallerySettings_<?php echo $template['slug'] . '_' . $field['id']; ?>"><?php echo $field['title']; ?></label>
								</th>
								<td>
									<?php do_action( 'foogallery_render_gallery_template_field', $field, $album, $template ); ?>
								</td>
							<?php } ?>
						</tr>
					<?php
					}
				}
				?>
				</tbody>
			</table>
		<?php
		}

		public function render_customcss_metabox( $post ) {
			$album = $this->get_album( $post );
			$custom_css = $album->custom_css;
			$example = '<code>#foogallery-album-' . $post->ID . ' { }</code>';
			?>
			<p>
				<?php printf( __( 'Add any custom CSS to target this specific album. For example %s', 'foogallery' ), $example ); ?>
			</p>
			<table id="table_styling" class="form-table">
				<tbody>
					<tr>
						<td>
							<textarea class="foogallery_metabox_custom_css" name="<?php echo FOOGALLERY_META_CUSTOM_CSS; ?>" type="text"><?php echo $custom_css; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
		}

		public function include_required_scripts() {
			if ( FOOGALLERY_CPT_ALBUM === foo_current_screen_post_type() ) {
				//include album selection script
				$url = FOOGALLERY_ALBUM_URL . 'js/admin-foogallery-album.js';
				wp_enqueue_script( 'admin-foogallery-album', $url, array( 'jquery', 'jquery-ui-core','jquery-ui-sortable' ), FOOGALLERY_VERSION );

				//include album selection css
				$url = FOOGALLERY_ALBUM_URL . 'css/admin-foogallery-album.css';
				wp_enqueue_style( 'admin-foogallery-album', $url, array(), FOOGALLERY_VERSION );
				$url = FOOGALLERY_URL . 'css/admin-foogallery-gallery-piles.css';
				wp_enqueue_style( 'admin-foogallery-gallery-piles', $url, array(), FOOGALLERY_VERSION );

				//spectrum needed for the colorpicker field
				$url = FOOGALLERY_URL . 'lib/spectrum/spectrum.js';
				wp_enqueue_script( 'foogallery-spectrum', $url, array('jquery'), FOOGALLERY_VERSION );
				$url = FOOGALLERY_URL . 'lib/spectrum/spectrum.css';
				wp_enqueue_style( 'foogallery-spectrum', $url, array(), FOOGALLERY_VERSION );
			}
		}

		public function ajax_get_gallery_details() {
			if ( check_admin_referer( 'foogallery_album_gallery_details' ) ) {
				$foogallery_id = intval( $_POST['foogallery_id'] );
				$gallery = FooGallery::get_by_id( $foogallery_id );

				if ( false !== $gallery ) {
					$fields = $this->get_gallery_detail_fields( $gallery ); ?>
					<form name="foogallery_gallery_details">
					<input type="hidden" name="foogallery_id" id="foogallery_id" value="<?php echo esc_attr( $foogallery_id ); ?>" />
					<table class="gallery-detail-fields">
						<tbody>
							<?php foreach ( $fields as $field => $values ) {
								$value = get_post_meta( $gallery->ID, $field, true );
								$input_id = 'foogallery-gallery-detail-fields-' . $field;
								switch ( $values['input'] ) {
									case 'text':
										$values['html'] = '<input type="text" id="' . $input_id . '" name="' . $field . '" value="' . esc_attr( foogallery_sanitize_javascript( $value ) ) . '" />';
										break;

									case 'textarea':
										$values['html'] = '<textarea id="' . $input_id . '" name="' . $field . '">' . esc_attr( foogallery_sanitize_javascript( $value ) ) . '</textarea>';
										break;

									case 'select':
										$html = '<select id="' . $input_id . '" name="' . $field . '">';

										// If options array is passed
										if ( isset( $values['options'] ) ) {
											// Browse and add the options
											foreach ( $values['options'] as $k => $v ) {
												// Set the option selected or not
												if ( $value == $k )
													$selected = ' selected="selected"';
												else
													$selected = '';

												$html .= '<option' . $selected . ' value="' . $k . '">' . $v . '</option>';
											}
										}

										$html .= '</select>';

										// Set the html content
										$values['html'] = $html;

										break;

									case 'checkbox':
										// Set the checkbox checked or not
										if ( $value == 'on' )
											$checked = ' checked="checked"';
										else
											$checked = '';

										$html = '<input' . $checked . ' type="checkbox" name="' . $field . ']" id="' . $input_id . '" />';

										$values['html'] = $html;

										break;

									case 'radio':
										$html = '';

										if ( ! empty( $values['options'] ) ) {
											$i = 0;

											foreach ( $values['options'] as $k => $v ) {
												if ( $value == $k )
													$checked = ' checked="checked"';
												else
													$checked = '';

												$html .= '<input' . $checked . ' value="' . $k . '" type="radio" name="' . $field . ']" id="' . sanitize_key( $field . '_' . $i ) . '" /> <label for="' . sanitize_key( $field . '_' . $i ) . '">' . $v . '</label><br />';
												$i++;
											}
										}

										$values['html'] = $html;

										break;
								} ?>
							<tr class="foogallery-gallery-detail-fields-<?php echo $field; ?>">
								<th scope="row" class="label">
									<label for="foogallery-gallery-detail-fields-<?php echo $field; ?>"><?php echo $values['label']; ?></label>
								</th>
								<td>
									<?php echo $values['html']; ?>
									<?php if ( !empty( $values['help'] ) ) { ?><p class="help"><?php echo $values['help']; ?></p><?php } ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					</form><?php
				} else {
					echo '<h2>' . __( 'Invalid Gallery!', 'foogallery' ) . '</h2>';
				}
			}
			die();
		}

		public function ajax_save_gallery_details() {
			if ( check_admin_referer( 'foogallery_album_gallery_details' ) ) {
				$foogallery_id = $_POST['foogallery_id'];
				$gallery       = FooGallery::get_by_id( $foogallery_id );
				if ( false !== $gallery ) {
					$fields = $this->get_gallery_detail_fields( $gallery );

					foreach ( $fields as $field => $values ) {
						//for every field, save some info
						do_action( 'foogallery_album_gallery_details_save', $field, $values, $gallery );
					}
				}
			}
		}

		public function gallery_details_save($field, $field_args, $gallery) {
			if ( 'custom_url' === $field || 'custom_target' === $field ) {
				$value = $_POST[$field];
				update_post_meta( $gallery->ID, $field, $value );
			}
		}

		/**
		 * Get the fields that we want to edit for a gallery from the album management page
		 * @param $gallery FooGallery
		 *
		 * @return mixed|void
		 */
		public function get_gallery_detail_fields($gallery) {

			$target_options = apply_filters( 'foogallery_gallery_detail_fields_custom_target_options',  array(
				'default' => __( 'Default', 'foogallery' ),
				'_blank' => __( 'New tab (_blank)', 'foogallery' ),
				'_self' => __( 'Same tab (_self)', 'foogallery' )
			) );

			$edit_url = get_edit_post_link( $gallery->ID );

			$fields = array(
				'gallery_title' => array(
					'label' => __( 'Gallery Title', 'foogallery' ),
					'input' => 'html',
					'html'  => '<strong>' . $gallery->safe_name() . ' <a href="' . $edit_url . '" target="_blank">' . __( 'Edit Gallery', 'foogallery' ) . '</a></strong>',
				),

				'gallery_template' => array(
					'label' => __( 'Gallery Template', 'foogallery' ),
					'input' => 'html',
					'html'  => '<strong>' . $gallery->gallery_template_name() . '</strong>',
				),

				'gallery_media' => array(
					'label' => __( 'Media', 'foogallery' ),
					'input' => 'html',
					'html'  => '<strong>' . $gallery->image_count() . '</strong>'
				),

				'custom_url' => array(
					'label' =>  __( 'Custom URL', 'foogallery' ),
					'input' => 'text',
					'help'  => __( 'Point your gallery to a custom URL', 'foogallery' )
				),

				'custom_target' => array(
					'label'   =>  __( 'Custom Target', 'foogallery' ),
					'input'   => 'select',
					'help'    => __( 'Set a custom target for your gallery', 'foogallery' ),
					'options' => $target_options
				)
			);

			return apply_filters( 'foogallery_gallery_detail_fields', $fields );
		}
	}
}
