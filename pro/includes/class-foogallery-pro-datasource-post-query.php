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
			$totalPosts = ! empty( $foogallery->datasource_value['no_of_post'] ) ? $foogallery->datasource_value['no_of_post'] : -1;
			$postType   = ! empty( $foogallery->datasource_value['gallery_post_type'] ) ? $foogallery->datasource_value['gallery_post_type'] : 'post';
			$link_to    = ! empty( $foogallery->datasource_value['link_to'] ) ? $foogallery->datasource_value['link_to'] : 'image';
			$exclude    = ! empty( $foogallery->datasource_value['exclude'] ) ? $foogallery->datasource_value['exclude'] : '';

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

			$posts = get_posts( $query_args );

			foreach ( $posts as $post ) {
				$attachment = new FooGalleryAttachment();

				$post_thumbnail_id = get_post_thumbnail_id( $post );
				if ( 'attachment' === $postType ) {
					$post_thumbnail_id = $post->ID;
				}
				$attachment->load_attachment_image_data( $post_thumbnail_id );

				if ( $link_to == 'image' ) {
					$url = $attachment->url;
				} else {
					$url = get_permalink( $post->ID );
                }

				$attachment->ID            = $post_thumbnail_id;
				$attachment->title         = $post->post_title;
				$attachment->has_metadata  = false;
				$attachment->sort          = PHP_INT_MAX;
				$attachment->caption       = $post->post_title;
				$attachment->description   = $post->post_excerpt;
				$attachment->alt           = $post->post_title;
				$attachment->custom_url    = $url;
				$attachment->custom_target = '';
				$attachment->sort          = '';

				$attachment    = apply_filters( 'foogallery_datasource_post_query_build_attachment', $attachment, $post );
				$attachments[$post_thumbnail_id] = $attachment;
			}

			return $attachments;
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
			wp_enqueue_script( 'foogallery.admin.datasources.post.query', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.post.query.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}


		/**
		 * Output the datasource modal content
		 *
		 * @param $foogallery_id
		 */
		public function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
			?>
            <p>
				<?php esc_html_e('Choose the settings for your gallery below. The gallery will be dynamically populated using the post query settings below.', 'foogallery' ); ?>
            </p>
            <form action="" method="post" name="post_query_gallery_form">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Post types', 'foogallery' ) ?></th>
                        <td>
                            <select class="regular-text foogallery_post_query_input" name="post_type"
                                    id="gallery_post_type">
                                <option value=""><?php esc_html_e( 'Select a post type' ) ?></option>
								<?php
								foreach ( get_post_types( array( 'public' => true ) ) as $key => $value ) {
									$selected = '';
									if ( isset( $datasource_value['gallery_post_type'] ) && $key === $datasource_value['gallery_post_type'] ) {
										$selected = 'selected';
									}
									echo "<option value='$value' $selected>" . ucfirst( $value ) . '</option>';
								}
								?>
                            </select>
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
                                    value="<?php echo isset( $datasource_value['no_of_post'] ) ? $datasource_value['no_of_post'] : '' ?>"
                            />
                            <p class="description"><?php esc_html_e( 'Number of posts you want to include in the gallery.', 'foogallery' ) ?></p>
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
                                    value="<?php echo isset( $datasource_value['exclude'] ) ? $datasource_value['exclude'] : '' ?>"
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

			?>
            <div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-item foogallery-datasource-post_query">
                <h3>
					<?php esc_html_e( 'Datasource : Post Query', 'foogallery' ); ?>
                </h3>
                <p>
					<?php esc_html_e( 'This gallery will be dynamically populated with the featured images from the following post query:', 'foogallery' ); ?>
                </p>
                <div class="foogallery-items-html">
                    <?php echo __('Post Type : ', 'foogallery'); ?><span id="foogallery-datasource-post-query-gallery_post_type"><?php echo $gallery_post_type; ?></span><br />
	                <?php echo __('No. Of Posts : ', 'foogallery'); ?><span id="foogallery-datasource-post-query-no_of_post"><?php echo $no_of_post; ?></span><br />
	                <?php echo __('Excludes : ', 'foogallery'); ?><span id="foogallery-datasource-post-query-exclude"><?php echo $exclude; ?></span><br />
	                <?php echo __('Link To : ', 'foogallery'); ?><span id="foogallery-datasource-post-query-link_to"><?php echo $link_to; ?></span><br />
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
