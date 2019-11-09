<?php
/**
 * The Gallery Datasource which pulls images using WP/LR Sync
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Instagram' ) ) {

    class FooGallery_Pro_Datasource_Instagram {

    	public function __construct() {
    		add_action( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ) );
    		add_filter( 'foogallery_datasource_instagram_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
    		add_filter( 'foogallery_datasource_instagram_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
    		add_filter( 'foogallery_datasource_instagram_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );
    		add_action( 'foogallery-datasource-modal-content_instagram', array( $this, 'render_datasource_modal_content' ), 10, 3 );
    		add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
    		add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );
    		add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
    	}

    	/**
    	 * Add the Instagram Datasource
    	 *
    	 * @param $datasources
    	 *
    	 * @return mixed
    	 */
    	function add_datasource( $datasources ) {
    		$datasources['instagram'] = array(
    			'id'     => 'instagram',
    			'name'   => __( 'Instagram', 'foogallery' ),
    			'menu'   => __( 'Instagram', 'foogallery' ),
    			'public' => true
    		);

    		return $datasources;
    	}


    	/**
		 * Enqueues folders-specific assets
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_script( 'foogallery.admin.datasources.instagram', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.instagram.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}
    	

    	/**
    	 * Clears the cache for the specific instagram
    	 *
    	 * @param $foogallery_id
    	 */
    	public function before_save_gallery_datasource_clear_datasource_cached_images( $foogallery_id ) {
    		$this->clear_gallery_transient( $foogallery_id );
    	}

    	public function clear_gallery_transient( $foogallery_id ) {
    		$transient_key = '_foogallery_datasource_instagram_' . $foogallery_id;
    		delete_transient( $transient_key );
    	}

    	/**
    	 * Returns the number of attachments used for the gallery
    	 *
    	 * @param int        $count
    	 * @param FooGallery $foogallery
    	 *
    	 * @return int
    	 */
    	public function get_gallery_attachment_count( $count, $foogallery ) {
    		return count( $this->get_gallery_attachments_from_instagram( $foogallery ) );
    	}

    	/**
    	 * Returns an array of FooGalleryAttachments from the datasource
    	 *
    	 * @param array      $attachments
    	 * @param FooGallery $foogallery
    	 *
    	 * @return array(FooGalleryAttachment)
    	 */
    	public function get_gallery_attachments( $attachments, $foogallery ) {
    		return $this->get_gallery_attachments_from_instagram( $foogallery );
    	}

    	/**
    	 * Returns a cached array of FooGalleryAttachments from the datasource
    	 *
    	 * @param FooGallery $foogallery
    	 *
    	 * @return array(FooGalleryAttachment)
    	 */
    	public function get_gallery_attachments_from_instagram( $foogallery ) {
    		global $foogallery_gallery_preview;

    		$attachments = array();

    		if ( ! empty( $foogallery->datasource_value ) ) {
    			$transient_key = '_foogallery_datasource_instagram_' . $foogallery->ID;

    			//never get the cached results if we are doing a preview
    			if ( isset( $foogallery_gallery_preview ) ) {
    				$cached_attachments = false;
    			} else {
    				$cached_attachments = get_transient( $transient_key );
    			}

    			if ( false === $cached_attachments ) {
    				$datasource_value = $foogallery->datasource_value;
    				
    				$expiry_hours = apply_filters( 'foogallery_datasource_instagram_expiry', 24 );
    				$expiry       = $expiry_hours * 60 * 60;

    				//find all image files in the instagram
    				$attachments = $this->build_attachments_from_instagram( $datasource_value );

    				//save a cached list of attachments
    				set_transient( $transient_key, $attachments, $expiry );
    			} else {
    				$attachments = $cached_attachments;
    			}
    		}

    		return $attachments;
    	}

    	/**
    	 * Returns the featured FooGalleryAttachment from the datasource
    	 *
    	 * @param FooGalleryAttachment $default
    	 * @param FooGallery           $foogallery
    	 *
    	 * @return bool|FooGalleryAttachment
    	 */
    	public function get_gallery_featured_attachment( $default, $foogallery ) {
    		$attachments = $this->get_gallery_attachments_from_instagram( $foogallery );
    		if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
    			return $attachments[0];
    		}

    		return false;
    	}

	    /**
         * Returns the stored Instagram Account token
	     * @return array|boolean
	     */
    	function get_instagram_token() {
		    $instagram_token = get_option('foogallery_instagram_token');
		    if ( false !== $instagram_token ) {
			    return @json_decode( $instagram_token, true );
		    }

		    return false;
    	}

    	/**
    	 * Output the datasource modal content
    	 *
    	 * @param $foogallery_id
    	 */
    	function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
    	    ?>
            <p>
                <?php _e('Choose the settings for your gallery below. The gallery will be dynamically loaded from Instagram.', 'foogallery' ); ?>
            </p>
            <script type="text/javascript">
                $(document).on('change','.foogallery_instagram_input',function(){
                    $('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );
                });
            </script>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="instagram_account"> <?php _e( 'Instagram Account', 'foogallery' ) ?></label>
                    </th>
                    <td>
                        <input
                                type="text"
                                class="regular-text foogallery_instagram_input"
                                name="instagram_account"
                                id="instagram_account"
                                value="<?php echo isset( $datasource_value['account'] ) ? $datasource_value['account'] : '' ?>"
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instagram_image_count"> <?php _e( 'Number of Images', 'foogallery' ) ?></label>
                    </th>
                    <td>
                        <input
                                type="number"
                                class="regular-text foogallery_instagram_input"
                                name="instagram_image_count"
                                id="instagram_image_count"
                                value="<?php echo isset( $datasource_value['image_count'] ) ? $datasource_value['image_count'] : '20' ?>"
                        />
                        <p class="description"><?php _e( 'Max number allowed by the Instagram is 33 images, and only 20 if your Instagram client is in Sandbox mode.', 'foogallery' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instagram_image_resolution"> <?php _e( 'Image Resolution', 'foogallery' ) ?></label>
                    </th>
                    <td>
                        <select class="regular-text foogallery_instagram_input" name="instagram_image_resolution" id="instagram_image_resolution">
                            <?php
                            $resolutions['thumbnail'] =  __( 'Thumbnail', 'foogallery' );
                            $resolutions['low_resolution'] =  __( 'Low Resolution', 'foogallery' );
                            $resolutions['standard_resolution'] =  __( 'Standard Resolution', 'foogallery' );

                            foreach ( $resolutions as $key => $value ) {
                                $selected = '';
                                if ( isset( $datasource_value['image_resolution'] ) && $key === $datasource_value['image_resolution'] ) {
                                    $selected = 'selected';
                                }
                                echo "<option value='$key' $selected>" . $value . '</option>';
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e( 'The resolution of the thumbs that will be used in the gallery.', 'foogallery' ) ?></p>
                    </td>
                </tr>
            </table>
            <?php
        }

    	function build_attachments_from_instagram( $datasource_value ) {
		    $account = $datasource_value['account'];
		    $image_count = $datasource_value['image_count'];
		    $image_resolution = $datasource_value['image_resolution'];

		    $instagram_helper = new FooGallery_Pro_Instagram_Helper();
		    $instagram_images = $instagram_helper->find_user_images_by_username( $account, $image_count, $image_resolution );

		    $attachments = array();

            foreach ($instagram_images['media'] as $instagram_image) {
                $attachment               = new FooGalleryAttachment();
                $attachment->ID           = 0;
                $attachment->title        = $instagram_image['id'];
	            $attachment->url          = $instagram_image['thumbnail_src'];
                $attachment->sort         = PHP_INT_MAX;
                $attachment->has_metadata = false;
                $attachment->caption      = $instagram_image['caption'];
                $attachment->description  = '';
                $attachment->alt          = $attachment->caption;
                $attachment->custom_url   = $instagram_image['display_url'];
                $attachment->custom_target = '';
                $attachment->sort = '';
                $attachment->instagram_image = true;
                $attachments[] = $attachment;
            }

    		return $attachments;
    	}


    	/**
    	 * Output the html required by the datasource in order to add item(s)
    	 *
    	 * @param FooGallery $gallery
    	 */
    	function render_datasource_item( $gallery ) {
    		$show_container = isset( $gallery->datasource_name ) && 'instagram' === $gallery->datasource_name;


    		$account = isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) && array_key_exists( 'account', $gallery->datasource_value ) ? $gallery->datasource_value['account'] : '';
		    $image_count = isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) && array_key_exists( 'image_count', $gallery->datasource_value ) ? $gallery->datasource_value['image_count'] : 0;
		    $image_resolution = isset( $gallery->datasource_value ) && is_array( $gallery->datasource_value ) && array_key_exists( 'image_resolution', $gallery->datasource_value ) ? $gallery->datasource_value['image_resolution'] : 'thumbnail';
    		?>
    		<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-item foogallery-datasource-instagram">
    		<h3><?php _e( 'Datasource : Instagram', 'foogallery' ); ?></h3>
    		<p><?php _e( 'This gallery will be dynamically populated with images from Instagram', 'foogallery' ); ?></p>
            <div class="foogallery-items-html">
			    <?php echo __('Account : ', 'foogallery'); ?><span id="foogallery-datasource-instagram-account"><?php echo esc_html($account); ?></span><br />
	            <?php echo __('No. Of Images : ', 'foogallery'); ?><span id="foogallery-datasource-instagram-number"><?php echo esc_html($image_count); ?></span><br />
	            <?php echo __('Resolution : ', 'foogallery'); ?><span id="foogallery-datasource-instagram-resolution"><?php echo esc_html($image_resolution); ?></span><br />
            </div>
    		<br />
    		<button type="button" class="button edit">
    			<?php _e( 'Change', 'foogallery' ); ?>
    		</button>
    		<button type="button" class="button remove">
    			<?php _e( 'Remove', 'foogallery' ); ?>
    		</button>
    		</div><?php
    	}
    }
}
