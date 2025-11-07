<?php
/**
 * The Gallery Datasource which pulls Post thumbnail of all the post.
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Post_Query' ) ) {

	class FooGallery_Pro_Datasource_Post_Query {
		public function __construct() {
			add_filter( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ) );
			add_action( 'foogallery-datasource-modal-content_post_query', array( $this, 'render_datasource_modal_content' ), 10, 2 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
			add_filter( 'foogallery_datasource_post_query_item_count', array( $this, 'get_gallery_attachment_count'	), 10, 2 );
			add_filter( 'foogallery_datasource_post_query_attachment_ids', array( $this, 'get_gallery_attachment_ids' ), 10, 2 );
			add_filter( 'foogallery_datasource_post_query_attachments', array( $this, 'get_gallery_attachments'	), 10, 2 );
			add_filter( 'foogallery_datasource_post_query_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			add_filter( 'foogallery_filtering_get_terms_for_attachment', array( $this, 'get_terms_for_post_query_attachment' ), 10, 3 );
		}

		/**
		 * Clears the cache for the specific post query
		 *
		 * @param $foogallery_id
		 */
		public function before_save_gallery_datasource_clear_datasource_cached_images( $foogallery_id ) {
			$this->clear_gallery_transient( $foogallery_id );
		}

		/**
		 * Clears the cache for the specific post query
		 *
		 * @param $foogallery_id
		 */
		public function clear_gallery_transient( $foogallery_id ) {
			$transient_key = '_foogallery_datasource_post_query_' . $foogallery_id;
			delete_transient( $transient_key );
		}

		/**
		 * Returns the featured FooGalleryAttachment from the datasource
		 *
		 * @param FooGalleryAttachment $default
		 * @param FooGallery $foogallery
		 *
		 * @return bool|FooGalleryAttachment
		 */
		public function get_gallery_featured_attachment( $default, $foogallery ) {
			$attachments = $this->get_gallery_attachments_from_post_query( $foogallery );
			return reset( $attachments );
		}

		/**
		 * Returns the number of attachments used for the gallery
		 *
		 * @param int $count
		 * @param FooGallery $foogallery
		 *
		 * @return int
		 */
		public function get_gallery_attachment_count( $count, $foogallery ) {
			return count( $this->get_gallery_attachments_from_post_query( $foogallery ) );
		}

		/**
		 * Returns an array of the attachment ID's for the gallery
		 *
		 * @param $attachment_ids
		 * @param $foogallery
		 *
		 * @return array
		 */
		public function get_gallery_attachment_ids( $attachment_ids, $foogallery ) {
			return array_keys( $this->get_gallery_attachments_from_post_query( $foogallery ) );
		}

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 *
		 * @param array $attachments
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments( $attachments, $foogallery ) {
			return $this->get_gallery_attachments_from_post_query( $foogallery );
		}

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments_from_post_query( $foogallery ) {
			global $foogallery_gallery_preview;

			$attachments = array();

			if ( ! empty( $foogallery->datasource_value ) ) {
				$transient_key = '_foogallery_datasource_post_query_' . $foogallery->ID;

				//never get the cached results if we are doing a preview
				if ( isset( $foogallery_gallery_preview ) ) {
					$cached_attachments = false;
				} else {
					$cached_attachments = get_transient( $transient_key );
				}

				if ( false === $cached_attachments ) {
					$datasource_value = $foogallery->datasource_value;

					$expiry_hours = apply_filters( 'foogallery_datasource_post_query_expiry', 24 );
					$expiry       = $expiry_hours * 60 * 60;

					//find all image files in the post_query
					$attachments = $this->build_attachments_from_post_query( $foogallery );

					//save a cached list of attachments
					set_transient( $transient_key, $attachments, $expiry );
				} else {
					$attachments = $cached_attachments;
				}
			}

			return $attachments;
		}

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		function build_attachments_from_post_query( $foogallery ) {
			$totalPosts              = ! empty( $foogallery->datasource_value['no_of_post'] ) ? $foogallery->datasource_value['no_of_post'] : -1;
			$postType                = ! empty( $foogallery->datasource_value['gallery_post_type'] ) ? $foogallery->datasource_value['gallery_post_type'] : 'post';
			$link_to                 = ! empty( $foogallery->datasource_value['link_to'] ) ? $foogallery->datasource_value['link_to'] : 'image';
			$exclude                 = ! empty( $foogallery->datasource_value['exclude'] ) ? $foogallery->datasource_value['exclude'] : '';
			$override_link_property  = ! empty( $foogallery->datasource_value['override_link_property'] ) ? $foogallery->datasource_value['override_link_property'] : '';
			$override_desc_property  = ! empty( $foogallery->datasource_value['override_desc_property'] ) ? $foogallery->datasource_value['override_desc_property'] : '';
			$override_title_property = ! empty( $foogallery->datasource_value['override_title_property'] ) ? $foogallery->datasource_value['override_title_property'] : '';
			$override_class_property = ! empty( $foogallery->datasource_value['override_class_property'] ) ? $foogallery->datasource_value['override_class_property'] : '';
			$override_sort_property  = ! empty( $foogallery->datasource_value['override_sort_property'] ) ? $foogallery->datasource_value['override_sort_property'] : '';
			$custom_target           = isset( $foogallery->datasource_value['custom_target'] ) ? sanitize_text_field( $foogallery->datasource_value['custom_target'] ) : '';
			$taxonomy                = ! empty( $foogallery->datasource_value['taxonomy'] ) ? sanitize_key( $foogallery->datasource_value['taxonomy'] ) : '';

			if ( ! empty( $taxonomy ) && ! taxonomy_exists( $taxonomy ) ) {
				$taxonomy = '';
			}

			if ( ! empty( $taxonomy ) ) {
				$foogallery->taxonomy = $taxonomy;
			}

			$query_args = apply_filters( 'foogallery_datasource_post_query_arguments', array(
				'posts_per_page' => $totalPosts,
				'post_type'      => $postType,
				'post_status'    => 'publish',
				'post__not_in'   => explode( ',', $exclude ),
				'meta_query'     => array(
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS'
					)
				)
			), $foogallery->ID );

			// Make some exceptions for attachments!
			if ( 'attachment' === $postType ) {
				unset( $query_args['meta_query'] );
				$query_args['post_status'] = 'inherit';
			}

			// Make some exceptions for foogalleries!
			if ( FOOGALLERY_CPT_GALLERY === $postType ) {
				unset( $query_args['meta_query'] );
				$query_args['post__not_in'][] = $foogallery->ID;
			}

			$posts = get_posts( $query_args );
			$attachments = array();

			foreach ( $posts as $post ) {
				$attachment = new FooGalleryAttachment();
				$attachment->post_query_datasource_used = true;
				
				if ( 'attachment' === $postType ) {
					$post_thumbnail_id = $post->ID;
				} elseif ( FOOGALLERY_CPT_GALLERY === $postType ) {
					$foogallery = FooGallery::get_by_id( $post->ID );
					$post_thumbnail_id = $foogallery->featured_attachment()->ID;
				} else {
					$post_thumbnail_id = get_post_thumbnail_id( $post );
				}

				$attachment->load_attachment_image_data( $post_thumbnail_id );

				if ( $link_to == 'image' ) {
					$url = $attachment->url;
				} else {
					$url = get_permalink( $post->ID );
				}

				$url_override         = $this->get_override_property_value( $post, $override_link_property, $url );
				$title_override       = $this->get_override_property_value( $post, $override_title_property, $post->post_title );
				$description_override = $this->get_override_property_value( $post, $override_desc_property, $post->post_excerpt );
				$class_override       = $this->get_override_property_value( $post, $override_class_property, '' );
				$sort_override        = $this->get_override_property_value( $post, $override_sort_property, '' );

				$attachment->ID            = $post_thumbnail_id;
				$attachment->post_id       = $post->ID;
				$attachment->title         = $title_override;
				$attachment->has_metadata  = false;
				$attachment->caption       = $title_override;
				$attachment->description   = $description_override;
				$attachment->alt           = $title_override;
				$attachment->custom_class  = $class_override;
				$attachment->date          = !empty( $post->post_date_gmt ) ? $post->post_date_gmt : $post->post_date;
				$attachment->modified      = !empty( $post->post_modified_gmt ) ? $post->post_modified_gmt : $post->post_modified;
				$attachment->custom_url    = $url_override;
				$attachment->custom_target = $custom_target;
				$attachment->sort          = '' !== $sort_override ? $sort_override : '';

				if ( ! empty( $taxonomy ) ) {
					$terms = wp_get_post_terms( $post->ID, $taxonomy, array( 'fields' => 'names' ) );
					if ( is_wp_error( $terms ) ) {
						$terms = array();
					}

					$attachment->post_query_taxonomy        = $taxonomy;
					$attachment->post_query_taxonomy_terms  = $terms;
				}

				$attachment = apply_filters( 'foogallery_datasource_post_query_build_attachment', $attachment, $post );
				$attachments[$post_thumbnail_id] = $attachment;
			}

			return $attachments;
		}

		/**
		 * Returns taxonomy terms for attachments generated by the post query datasource.
		 *
		 * @param mixed                 $terms
		 * @param string                $taxonomy
		 * @param FooGalleryAttachment  $attachment
		 *
		 * @return array|mixed
		 */
		public function get_terms_for_post_query_attachment( $terms, $taxonomy, $attachment ) {
			if ( ! isset( $attachment->post_query_datasource_used ) ) {
				return $terms;
			}

			if ( isset( $attachment->post_query_taxonomy_terms ) && is_array( $attachment->post_query_taxonomy_terms ) ) {
				return $attachment->post_query_taxonomy_terms;
			}

			return $terms;
		}

		/**
		 * Determine the override value for the given post property.
		 *
		 * @param WP_Post $post
		 * @param string  $field_key
		 * @param string  $default
		 *
		 * @return string
		 */
		protected function get_override_property_value( $post, $field_key, $default ) {
			$field_key = is_string( $field_key ) ? trim( $field_key ) : '';

			if ( '' === $field_key ) {
				return $default;
			}

			$value = '';

			if ( 0 === strpos( $field_key, 'acf:' ) ) {
				$acf_field = trim( substr( $field_key, 4 ) );

				if ( '' === $acf_field || ! function_exists( 'get_field' ) ) {
					return $default;
				}

				$value = get_field( $acf_field, $post->ID );
			} else {
				if ( isset( $post->$field_key ) ) {
					$value = $post->$field_key;
				} else {
					$meta_value = get_post_meta( $post->ID, $field_key, true );

					if ( '' !== $meta_value && null !== $meta_value ) {
						$value = $meta_value;
					}
				}
			}

			if ( is_array( $value ) ) {
				if ( isset( $value['url'] ) ) {
					$value = $value['url'];
				} elseif ( isset( $value['value'] ) ) {
					$value = $value['value'];
				} else {
					$value = implode( ', ', array_map( 'wp_strip_all_tags', array_filter( array_map( 'strval', $value ) ) ) );
				}
			} elseif ( is_object( $value ) ) {
				if ( isset( $value->url ) ) {
					$value = $value->url;
				} elseif ( method_exists( $value, '__toString' ) ) {
					$value = (string) $value;
				} else {
					$value = '';
				}
			}

			if ( is_scalar( $value ) ) {
				$value = trim( (string) $value );
			} else {
				$value = '';
			}

			return '' !== $value ? $value : $default;
		}

		/**
		 * Add the post_querys Datasource
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		public function add_datasource( $datasources ) {
			$datasources['post_query'] = array(
				'id'     => 'post_query',
				'name'   => __( 'Post Query', 'foogallery' ),
				'menu'   => __( 'Post Query', 'foogallery' ),
				'public' => true
			);

			return $datasources;
		}

		/**
		 * Enqueues post query assets
		 */
		public function enqueue_scripts_and_styles() {
			$dependencies = array( 'jquery' );

			wp_enqueue_script( 'foogallery.admin.datasources.post.query', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.post.query.js', $dependencies, FOOGALLERY_VERSION );
		}


		/**
		 * Output the datasource modal content
		 *
		 * @param $foogallery_id
		 */
		public function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
			?>
            <p>
				<?php esc_html_e('Choose the settings for your gallery below. The gallery will be dynamically populated using the post query settings below.', 'foogallery' ); ?><br />
				<?php esc_html_e('Please Note : Only posts with a featured image will be displayed!', 'foogallery' ); ?>
            </p>
	            <form action="" method="post" name="post_query_gallery_form">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Post type', 'foogallery' ) ?></th>
                        <td>
                            <select class="regular-text foogallery_post_query_input" name="post_type"
                                    id="gallery_post_type">
                                <option value=""><?php esc_html_e( 'Select a post type' ) ?></option>
								<?php
								$current_post_type = isset( $datasource_value['gallery_post_type'] ) ? $datasource_value['gallery_post_type'] : '';
								$post_types        = get_post_types( array(), 'objects' );
								$public_types      = array();
								$private_types     = array();

								foreach ( $post_types as $post_type ) {
									if ( ! $post_type instanceof WP_Post_Type ) {
										continue;
									}

									$label = ! empty( $post_type->labels->singular_name ) ? $post_type->labels->singular_name : $post_type->label;
									if ( empty( $label ) ) {
										$label = $post_type->name;
									}

									$prepared = array(
										'name'  => $post_type->name,
										'label' => $label,
									);

									if ( ! empty( $post_type->public ) ) {
										$public_types[] = $prepared;
									} else {
										$private_types[] = $prepared;
									}
								}

								if ( ! empty( $public_types ) ) {
									echo '<optgroup label="' . esc_attr__( 'Public', 'foogallery' ) . '">';
									foreach ( $public_types as $public_type ) {
										$display_text = sprintf( '%1$s (%2$s)', $public_type['label'], $public_type['name'] );
										printf(
											'<option value="%1$s"%2$s>%3$s</option>',
											esc_attr( $public_type['name'] ),
											selected( $current_post_type, $public_type['name'], false ),
											esc_html( $display_text )
										);
									}
									echo '</optgroup>';
								}

								if ( ! empty( $private_types ) ) {
									echo '<optgroup label="' . esc_attr__( 'Private', 'foogallery' ) . '">';
									foreach ( $private_types as $private_type ) {
										$display_text = sprintf( '%1$s (%2$s)', $private_type['label'], $private_type['name'] );
										printf(
											'<option value="%1$s"%2$s>%3$s</option>',
											esc_attr( $private_type['name'] ),
											selected( $current_post_type, $private_type['name'], false ),
											esc_html( $display_text )
										);
									}
									echo '</optgroup>';
								}
								?>
                            </select>
							<p class="description"><?php esc_html_e( 'The post type you want to query for the gallery. Only published posts will be included.', 'foogallery' ) ?></p>
						</td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Number of posts', 'foogallery' ); ?></th>
                        <td>
                            <input
                                    type="number"
                                    class="regular-text foogallery_post_query_input"
                                    name="no_of_post"
                                    id="no_of_post"
                                    value="<?php echo isset( $datasource_value['no_of_post'] ) ? esc_attr( $datasource_value['no_of_post'] ) : ''; ?>"
                            />
                            <p class="description"><?php esc_html_e( 'Number of posts you want to include in the gallery. Leave empty to include all published posts.', 'foogallery' ) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Exclude', 'foogallery' ); ?></th>
                        <td>
                            <input
                                    type="text"
                                    class="regular-text foogallery_post_query_input"
                                    name="exclude"
                                    id="exclude"
                                    value="<?php echo isset( $datasource_value['exclude'] ) ? esc_attr( $datasource_value['exclude'] ) : ''; ?>"
                            />
                            <p class="description"><?php esc_html_e( 'A comma separated list of post id\'s that you want to exclude from the gallery.', 'foogallery' ) ?></p>
                        </td>
                    </tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Link To', 'foogallery' ); ?></th>
						<td>
							<fieldset>
								<label>
                                    <input
                                            type="radio"
                                            name="link_to"
                                            value="post"
                                            class="link_to foogallery_post_query_input"
									<?php echo ( isset( $datasource_value['link_to'] ) && $datasource_value['link_to'] === 'post' ) ? 'checked="checked"' : '' ?>
                                    />
                                    <span><?php esc_html_e( 'Post Permalink', 'foogallery' ) ?></span>
                                </label>
                                <br>
                                <label>
                                    <input
                                            type="radio"
                                            name="link_to"
                                            value="image"
                                            class="link_to foogallery_post_query_input"
									<?php echo ( isset( $datasource_value['link_to'] ) && $datasource_value['link_to'] === 'image' ) ? 'checked="checked"' : '' ?>
                                    />
                                    <span><?php esc_html_e( 'Featured Image', 'foogallery' ) ?></span>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Taxonomy', 'foogallery' ); ?></th>
						<td>
							<input
								type="text"
								class="regular-text foogallery_post_query_input"
								name="taxonomy"
								id="taxonomy"
								value="<?php echo isset( $datasource_value['taxonomy'] ) ? esc_attr( $datasource_value['taxonomy'] ) : ''; ?>"
							/>
							<p class="description"><?php esc_html_e( 'Provide the taxonomy slug to use for gallery filtering (for example, category or post_tag). Leave blank to not include taxonomy terms.', 'foogallery' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Custom Target', 'foogallery' ); ?></th>
						<td>
							<input
								type="text"
								class="regular-text foogallery_post_query_input"
								name="custom_target"
								id="custom_target"
								value="<?php echo isset( $datasource_value['custom_target'] ) ? esc_attr( $datasource_value['custom_target'] ) : ''; ?>"
							/>
							<p class="description"><?php esc_html_e( 'Specify a custom target attribute for the link (for example, _blank). Leave empty to use the default behaviour.', 'foogallery' ); ?></p>
						</td>
					</tr>					
					<tr>
						<th scope="row"><?php esc_html_e( 'Override Link Property', 'foogallery' ); ?></th>
						<td>
							<input
								type="text"
								class="regular-text foogallery_post_query_input"
								name="override_link_property"
								id="override_link_property"
								value="<?php echo isset( $datasource_value['override_link_property'] ) ? esc_attr( $datasource_value['override_link_property'] ) : ''; ?>"
							/>
							<p class="description"><?php esc_html_e( 'Override the property of the post object to use as the link. By default, the Link To option will be used.', 'foogallery' ) ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Override Desc Property', 'foogallery' ); ?></th>
						<td>
							<input
								type="text"
								class="regular-text foogallery_post_query_input"
								name="override_desc_property"
								id="override_desc_property"
								value="<?php echo isset( $datasource_value['override_desc_property'] ) ? esc_attr( $datasource_value['override_desc_property'] ) : ''; ?>"
							/>
							<p class="description"><?php esc_html_e( 'Override the property of the post object to use as the description. By default, post_excerpt will be used.', 'foogallery' ) ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Override Title Property', 'foogallery' ); ?></th>
						<td>
							<input
								type="text"
								class="regular-text foogallery_post_query_input"
								name="override_title_property"
								id="override_title_property"
								value="<?php echo isset( $datasource_value['override_title_property'] ) ? esc_attr( $datasource_value['override_title_property'] ) : ''; ?>"
							/>
							<p class="description"><?php esc_html_e( 'Override the property of the post object to use as the title. By default, post_title will be used.', 'foogallery' ) ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Override Class Property', 'foogallery' ); ?></th>
						<td>
							<input
								type="text"
								class="regular-text foogallery_post_query_input"
								name="override_class_property"
								id="override_class_property"
								value="<?php echo isset( $datasource_value['override_class_property'] ) ? esc_attr( $datasource_value['override_class_property'] ) : ''; ?>"
							/>
							<p class="description"><?php esc_html_e( 'Override the property of the post object to use for the CSS class(es). Classes will be added to the anchor link.', 'foogallery' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Override Sort Property', 'foogallery' ); ?></th>
						<td>
							<input
								type="text"
								class="regular-text foogallery_post_query_input"
								name="override_sort_property"
								id="override_sort_property"
								value="<?php echo isset( $datasource_value['override_sort_property'] ) ? esc_attr( $datasource_value['override_sort_property'] ) : ''; ?>"
							/>
							<p class="description"><?php esc_html_e( 'Override the property of the post object to use for the sort order, eg. "menu_order". Leave blank to keep the default sort.', 'foogallery' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Override Help', 'foogallery' ); ?></th>
						<td>
							<p class="description"><?php esc_html_e( 'For all overrides, you can provide the name of a property of the post object to use as the value. For example, "post_title".', 'foogallery' ) ?></p>
							<p class="description"><?php esc_html_e( 'You can also use the name of a post meta field to use as the value. Simply provide the name of the meta key.', 'foogallery' ) ?></p>
							<p class="description"><?php esc_html_e( 'You can also use the name of an ACF field to use as the value. Simply provide the name of the ACF field prefixed with "acf:", for example "acf:my_acf_field".', 'foogallery' ) ?></p>
						</td>
					</tr>
					</tbody>
				</table>
			</form>
			<?php
		}

		/**
		 * Output the html required by the datasource in order to add item(s)
		 *
		 * @param FooGallery $gallery
		 */
		function render_datasource_item( $gallery ) {
			$show_container = isset( $gallery->datasource_name ) && 'post_query' === $gallery->datasource_name;

			$gallery_post_type = isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) && array_key_exists( 'gallery_post_type', $gallery->datasource_value ) ? $gallery->datasource_value['gallery_post_type'] : '';
			$no_of_post = isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) && array_key_exists( 'no_of_post', $gallery->datasource_value ) ? $gallery->datasource_value['no_of_post'] : '';
			$exclude = isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) && array_key_exists( 'exclude', $gallery->datasource_value ) ? $gallery->datasource_value['exclude'] : '';
			$link_to = isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) && array_key_exists( 'link_to', $gallery->datasource_value ) ? $gallery->datasource_value['link_to'] : '';
			$override_link_property  = isset( $gallery->datasource_value['override_link_property'] ) ? $gallery->datasource_value['override_link_property'] : '';
			$override_desc_property  = isset( $gallery->datasource_value['override_desc_property'] ) ? $gallery->datasource_value['override_desc_property'] : '';
			$override_title_property = isset( $gallery->datasource_value['override_title_property'] ) ? $gallery->datasource_value['override_title_property'] : '';
			$override_class_property = isset( $gallery->datasource_value['override_class_property'] ) ? $gallery->datasource_value['override_class_property'] : '';
			$override_sort_property  = isset( $gallery->datasource_value['override_sort_property'] ) ? $gallery->datasource_value['override_sort_property'] : '';
			$taxonomy                = isset( $gallery->datasource_value['taxonomy'] ) ? $gallery->datasource_value['taxonomy'] : '';
			$custom_target           = isset( $gallery->datasource_value['custom_target'] ) ? $gallery->datasource_value['custom_target'] : '';

			?>
            <div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-item foogallery-datasource-post_query">
                <h3>
					<?php esc_html_e( 'Datasource : Post Query', 'foogallery' ); ?>
                </h3>
                <p>
					<?php esc_html_e( 'This gallery will be dynamically populated with the featured images from the following post query:', 'foogallery' ); ?>
                </p>
	                <div class="foogallery-items-html">
	                <?php
	                $summary_fields = array(
		                array( 'label' => __( 'Post Type : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-gallery_post_type', 'value' => $gallery_post_type ),
		                array( 'label' => __( 'No. Of Posts : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-no_of_post', 'value' => $no_of_post ),
		                array( 'label' => __( 'Excludes : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-exclude', 'value' => $exclude ),
		                array( 'label' => __( 'Link To : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-link_to', 'value' => $link_to ),
		                array( 'label' => __( 'Override Link Property : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-override_link_property', 'value' => $override_link_property ),
		                array( 'label' => __( 'Override Desc Property : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-override_desc_property', 'value' => $override_desc_property ),
		                array( 'label' => __( 'Override Title Property : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-override_title_property', 'value' => $override_title_property ),
		                array( 'label' => __( 'Override Class Property : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-override_class_property', 'value' => $override_class_property ),
		                array( 'label' => __( 'Override Sort Property : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-override_sort_property', 'value' => $override_sort_property ),
		                array( 'label' => __( 'Taxonomy : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-taxonomy', 'value' => $taxonomy ),
		                array( 'label' => __( 'Custom Target : ', 'foogallery' ), 'id' => 'foogallery-datasource-post-query-custom_target', 'value' => $custom_target ),
	                );

	                foreach ( $summary_fields as $field ) {
		                $value = $field['value'];
		                $has_value = is_scalar( $value ) ? '' !== trim( (string) $value ) : ! empty( $value );

		                echo '<div data-summary-field="' . esc_attr( $field['id'] ) . '"' . ( $has_value ? '' : ' style="display:none"' ) . '>';
		                echo esc_html( $field['label'] );
		                echo '<span id="' . esc_attr( $field['id'] ) . '">' . esc_html( $value ) . '</span>';
		                echo '</div>';
	                }
	                ?>
	                </div>
                <br/>
                <button type="button" class="button edit">
					<?php esc_html_e( 'Change', 'foogallery' ); ?>
                </button>
                <button type="button" class="button remove">
					<?php esc_html_e( 'Remove', 'foogallery' ); ?>
                </button>
            </div>
			<?php
		}
	}
}
