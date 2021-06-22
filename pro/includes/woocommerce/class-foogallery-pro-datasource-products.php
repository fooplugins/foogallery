<?php
/**
 * The Gallery Datasource which pulls product thumbnails from WooCommerce.
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Products' ) ) {

	class FooGallery_Pro_Datasource_Products {
		public function __construct() {
			add_action( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ) );
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

			add_filter( 'foogallery_datasource_woocommerce_item_count', array( $this, 'get_gallery_attachment_count'	), 10, 2 );
			add_filter( 'foogallery_datasource_woocommerce_attachments', array( $this, 'get_gallery_attachments'	), 10, 2 );
			add_filter( 'foogallery_datasource_woocommerce_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );

			add_action( 'foogallery-datasource-modal-content_woocommerce', array( $this, 'render_datasource_modal_content' ), 10, 2 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
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
			$transient_key = '_foogallery_datasource_woocommerce_' . $foogallery_id;
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
			$attachments = $this->get_gallery_attachments_from_products( $foogallery );
			if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
				return $attachments[0];
			}

			return false;
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
			return count( $this->get_gallery_attachments_from_products( $foogallery ) );
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
			return $this->get_gallery_attachments_from_products( $foogallery );
		}

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments_from_products( $foogallery ) {
			global $foogallery_gallery_preview;

			$attachments = array();

			if ( ! empty( $foogallery->datasource_value ) ) {
				$transient_key = '_foogallery_datasource_woocommerce_' . $foogallery->ID;

				//never get the cached results if we are doing a preview
				if ( isset( $foogallery_gallery_preview ) ) {
					$cached_attachments = false;
				} else {
					$cached_attachments = get_transient( $transient_key );
				}

				if ( false === $cached_attachments ) {
					$expiry_hours = apply_filters( 'foogallery_datasource_woocommerce_expiry', 24 );
					$expiry       = $expiry_hours * 60 * 60;

					//find all products
					$attachments = $this->build_attachments( $foogallery );

					//save a cached list of attachments
					set_transient( $transient_key, $attachments, $expiry );
				} else {
					$attachments = $cached_attachments;
				}
			}

			return $attachments;
		}

		/**
		 * Check if WooCommerce is activated
		 */
		function is_woocommerce_activated() {
			return class_exists( 'woocommerce' );
		}

		/**
		 * Add the woocommerce Datasource
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		public function add_datasource( $datasources ) {
			if ( $this->is_woocommerce_activated() ) {
				$datasources['woocommerce'] = array(
					'id'     => 'woocommerce',
					'name'   => __( 'WooCommerce Products', 'foogallery' ),
					'menu'   => __( 'WooCommerce Products', 'foogallery' ),
					'public' => true
				);
			}

			return $datasources;
		}

		/**
		 * Enqueues assets
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_script( 'foogallery.admin.datasources.woocommerce', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.woocommerce.js', array( 'jquery' ), FOOGALLERY_VERSION );
			wp_enqueue_style( 'foogallery.admin.datasources.woocommerce', FOOGALLERY_PRO_URL . 'css/foogallery.admin.datasources.woocommerce.css', array(), FOOGALLERY_VERSION );
		}

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		function build_attachments( $foogallery ) {
			$categories           = ! empty( $foogallery->datasource_value['categories'] ) ? $foogallery->datasource_value['categories'] : '';
			$no_of_post           = ! empty( $foogallery->datasource_value['no_of_post'] ) ? $foogallery->datasource_value['no_of_post'] : 20;
			$exclude              = ! empty( $foogallery->datasource_value['exclude'] ) ? $foogallery->datasource_value['exclude'] : '';
			$caption_title_source = ! empty( $foogallery->datasource_value['caption_title_source'] ) ? $foogallery->datasource_value['caption_title_source'] : 'post_title';
			$caption_desc_source  = ! empty( $foogallery->datasource_value['caption_desc_source'] ) ? $foogallery->datasource_value['caption_desc_source'] : 'post_content';

			$args = array(
				'posts_per_page' => $no_of_post,
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'post__not_in'   => explode( ',', $exclude ),
				'meta_query'     => array(
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS'
					)
				)
			);

			if ( ! empty( $categories ) ) {
				$args['tax_query'] = array(
					array(
						'taxonomy'  => 'product_cat',
						'field'     => 'id',
						'terms'     => $categories
					)
				);
			}

			$query_args = apply_filters( 'foogallery_datasource_woocommerce_arguments', $args, $foogallery->ID );

			$products = wc_get_products( $query_args );

			foreach ( $products as $product ) {
				$attachment = new FooGalleryAttachment();

				$post_thumbnail_id = get_post_thumbnail_id( $product->get_id() );
				$post_thumbnail_url = get_the_post_thumbnail_url( $product->get_id(),'full' );

				$attachment->ID            = $post_thumbnail_id;
				$attachment->title         = $product->get_title();
				$attachment->url           = $post_thumbnail_url;
				$attachment->has_metadata  = false;
				$attachment->sort          = PHP_INT_MAX;


				$attachment->caption       = $this->get_caption( $product, $caption_title_source );
				$attachment->description   = $this->get_caption( $product, $caption_desc_source );

				$attachment->alt           = $product->get_title();
				$attachment->custom_url    = get_permalink( $product->get_id() );
				$attachment->custom_target = '';

				$attachment    = apply_filters( 'foogallery_datasource_woocommerce_build_attachment', $attachment, $product );
				$attachments[] = $attachment;
			}

			return $attachments;
		}

		/**
		 * @param  $product
		 * @param string $source
		 *
		 * @return string
		 */
		private function get_caption( $product, $source ) {
			switch ( $source ) {
				case 'price':
					return $product->get_price_html();
				case 'title':
					return $product->get_title();
				case 'short_description':
					return $product->get_short_description();
				case 'description':
					return $product->get_description();
				default:
					return '';
			}
		}

		/**
		 * Output the datasource modal content
		 *
		 * @param $foogallery_id
		 */
		public function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
			$caption_sources = array(
				'title'             => __( 'Title', 'foogallery' ),
				'short_description' => __( 'Short Description', 'foogallery' ),
				'description'       => __( 'Content', 'foogallery' ),
				'price'             => __( 'Price', 'foogallery' ),
			);

			$selected_categories = array();
			if ( is_array( $datasource_value ) && array_key_exists( 'categories', $datasource_value ) ) {
				$selected_categories = $datasource_value['categories'];
			}
			$categories = get_terms( 'product_cat', array('hide_empty' => false) );
			$category_count = count( $categories );
			?>
            <p>
				<?php _e('Choose the settings for your gallery below. The gallery will be dynamically populated using the post query settings below.', 'foogallery' ); ?>
            </p>
            <form action="" method="post" name="woocommerce_gallery_form" class="foogallery-datasource-woocommerce-form">
                <table class="form-table">
                    <tbody>
                    <tr>
	                    <th scope="row"><?php _e( 'Product Categories', 'foogallery' ); ?></th>
	                    <td>
		                    <ul class="foogallery_woocommerce_categories">
			                    <?php
			                    foreach ($categories as $category) {
				                    $selected = in_array($category->term_id, $selected_categories);
				                    ?>
				                    <li>
					                    <a href="#" class="button button-small<?php echo $selected ? ' button-primary' : ''; ?>"
					                       data-term-id="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></a>
				                    </li><?php
			                    }
			                    ?>
		                    </ul>
	                    </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Number of Products', 'foogallery' ); ?></th>
                        <td>
                            <input
                                    type="number"
                                    class="regular-text foogallery_woocommerce_input"
                                    name="no_of_post"
                                    id="foogallery_woocommerce_no_of_post"
                                    value="<?php echo isset( $datasource_value['no_of_post'] ) ? $datasource_value['no_of_post'] : '' ?>"
                            />
                            <p class="description"><?php _e( 'Number of products you want to include in the gallery.', 'foogallery' ) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Exclude', 'foogallery' ); ?></th>
                        <td>
                            <input
                                    type="text"
                                    class="regular-text foogallery_woocommerce_input"
                                    name="exclude"
                                    id="foogallery_woocommerce_exclude"
                                    value="<?php echo isset( $datasource_value['exclude'] ) ? $datasource_value['exclude'] : '' ?>"
                            />
                            <p class="description"><?php _e( 'A comma separated list of product id\'s that you want to exclude from the gallery.', 'foogallery' ) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Caption Title Source', 'foogallery' ); ?></th>
                        <td>
                            <fieldset>
	                            <?php foreach ( $caption_sources as $caption_source_key => $caption_source_label ) { ?>
		                            <label style="padding-right: 10px">
			                            <input
				                            type="radio"
				                            name="caption_title_source"
				                            value="<?php echo $caption_source_key; ?>"
				                            class="foogallery_woocommerce_caption_title_source foogallery_woocommerce_input"
				                            <?php echo ( isset( $datasource_value['caption_title_source'] ) && $datasource_value['caption_title_source'] === $caption_source_key ) ? 'checked="checked"' : '' ?>
			                            />
			                            <span><?php echo $caption_source_label; ?></span>
		                            </label>
								<?php } ?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
	                    <th scope="row"><?php _e( 'Caption Description Source', 'foogallery' ); ?></th>
	                    <td>
		                    <fieldset>
			                    <?php foreach ( $caption_sources as $caption_source_key => $caption_source_label ) { ?>
				                    <label style="padding-right: 10px">
					                    <input
						                    type="radio"
						                    name="caption_desc_source"
						                    value="<?php echo $caption_source_key; ?>"
						                    class="foogallery_woocommerce_caption_desc_source foogallery_woocommerce_input"
						                    <?php echo ( isset( $datasource_value['caption_desc_source'] ) && $datasource_value['caption_desc_source'] === $caption_source_key ) ? 'checked="checked"' : '' ?>
					                    />
					                    <span><?php echo $caption_source_label; ?></span>
				                    </label>
			                    <?php } ?>
		                    </fieldset>
	                    </td>
                    </tr>
                    </tbody>
                </table>
            </form>
			<script type="text/javascript">
				foogallery_woocommerce_set_selected_categories();
			</script>
			<?php
		}

		/**
		 * Output the html required by the datasource in order to add item(s)
		 *
		 * @param FooGallery $gallery
		 */
		function render_datasource_item( $gallery ) {
			// Setup some defaults.
			$show_container = false;
			$no_of_post = 5;
			$exclude = '';
			$caption_title_source = 'post_title';
			$caption_desc_source = 'post_excerpt';

			if ( isset( $gallery->datasource_name ) ) {
				$show_container = 'woocommerce' === $gallery->datasource_name;
			}

			if ( isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) ) {
				$categories_html      = array_key_exists( 'categories_html', $gallery->datasource_value ) ? $gallery->datasource_value['categories_html'] : '';
				$no_of_post           = array_key_exists( 'no_of_post', $gallery->datasource_value ) ? $gallery->datasource_value['no_of_post'] : '';
				$exclude              = array_key_exists( 'exclude', $gallery->datasource_value ) ? $gallery->datasource_value['exclude'] : '';
				$caption_title_source = array_key_exists( 'caption_title_source', $gallery->datasource_value ) ? $gallery->datasource_value['caption_title_source'] : 'post_title';
				$caption_desc_source  = array_key_exists( 'caption_desc_source', $gallery->datasource_value ) ? $gallery->datasource_value['caption_desc_source'] : 'post_excerpt';
			}


			?>
            <div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-item foogallery-datasource-woocommerce">
                <h3>
					<?php _e( 'Datasource : WooCommerce Products', 'foogallery' ); ?>
                </h3>
                <p>
					<?php _e( 'This gallery will be dynamically populated with products, based on the following criteria:', 'foogallery' ); ?>
                </p>
                <div class="foogallery-items-html">
	                <?php echo __('Categories : ', 'foogallery'); ?><span id="foogallery-datasource-woocommerce-categories"><?php echo $categories_html; ?></span><br />
	                <?php echo __('No. of Products : ', 'foogallery'); ?><span id="foogallery-datasource-woocommerce-no_of_post"><?php echo $no_of_post; ?></span><br />
	                <?php if ( !empty( $exclude ) ) { ?>
	                <?php echo __('Excludes : ', 'foogallery'); ?><span id="foogallery-datasource-woocommerce-exclude"><?php echo $exclude; ?></span><br />
					<?php } ?>
	                <?php echo __('Caption Title Source : ', 'foogallery'); ?><span id="foogallery-datasource-woocommerce-caption_title_source"><?php echo $caption_title_source; ?></span><br />
	                <?php echo __('Caption Desc Source : ', 'foogallery'); ?><span id="foogallery-datasource-woocommerce-caption_desc_source"><?php echo $caption_desc_source; ?></span><br />
                </div>
                <br/>
                <button type="button" class="button edit">
					<?php _e( 'Change', 'foogallery' ); ?>
                </button>
                <button type="button" class="button remove">
					<?php _e( 'Remove', 'foogallery' ); ?>
                </button>
            </div>
			<?php
		}
	}
}
