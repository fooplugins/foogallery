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
    		add_action( 'wp_ajax_foogallery_datasource_instagram_get_images', array( $this, 'fetch_images_from_instagram' ) );
    		add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
    		add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );
    		add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
    	}

    	/**
    	 * Add the instagrams Datasource
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
			//wp_enqueue_style( 'foogallery.admin.datasources.folders', FOOGALLERY_PRO_URL . 'css/foogallery.admin.datasources.folders.css', array(), FOOGALLERY_VERSION );
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
    				$attachments = $this->build_attachments_from_instagram( $datasource_value['image_count'], $datasource_value['image_resolution'] );

    				//save a cached list of attachments
    				set_transient( $transient_key, $attachments, $expiry );
    			} else {
    				$attachments = $cached_attachments;
    			}
    		}

    		return $attachments;
    	}

    	/**
    	 * returns the supported image types that will be pulled from a instagram
    	 *
    	 * @return array
    	 */
    	public function supported_image_types() {
    		return apply_filters(
    			'foogallery_datasource_instagram_supported_image_types', array(
    			'gif',
    			'jpg',
    			'jpeg',
    			'png'
    		)
    		);
    	}

    	/**
    	 * Generates the option key used to store the response for a instagram
    	 *
    	 * @param $instagram
    	 *
    	 * @return string
    	 */
    	private function build_database_options_key( $gallery_id) {
    		return '_foogallery_datasource_instagram_' . $gallery_id;
    	}

    	
    	/**
    	 * Extract the correct json data for the file
    	 *
    	 * @param $filename
    	 * @param $json_data
    	 *
    	 * @return bool
    	 */
    	public function find_json_data_for_file( $filename, $json_data ) {
    		if ( array_key_exists( 'items', $json_data ) ) {
    			foreach ( $json_data['items'] as $position => $item ) {
    				//allow for an index to be specified, otherwise set the index to be the position in the array
    				if ( ! array_key_exists( 'index', $item ) ) {
    					$item['index'] = $position;
    				}

    				if ( array_key_exists( 'file', $item ) && $item['file'] === $filename ) {
    					return $item;
    				}
    			}
    		}

    		return false;
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
    	 * Output the datasource modal content
    	 *
    	 * @param $foogallery_id
    	 */
    	function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
    		if(get_option('instagram_token') != ''){

    			?>
    			<script type="text/javascript">
    				$(document).on('change','.foogallery_instagram_input',function(){
                        $('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );
    				});
    			</script>
    			<table>
    				<tr>
    					<th align="left"><label for="instagram_image_count"><?php echo __("Number of Images to Display","foogallery"); ?></label></th>
    					<td><input class="foogallery_instagram_input" type="number" name="instagram_image_count" min="0" id="instagram_image_count"></td>
    				</tr>

    				<tr>
    					<th align="left"><label for="instagram_image_resolution"><?php echo  __("Image Resolution","foogallery"); ?></label></th>
    					<td>
    						<select class="foogallery_instagram_input" name="instagram_image_resolution" id="instagram_image_resolution">
    							<option value="thumbnail"><?php echo __("Thumbnail","foogallery"); ?></option>
    							<option value="low_resolution"><?php echo __("Low Resolution","foogallery"); ?></option>
    							<option value="standard_resolution"><?php echo __("Standard Resolution","foogallery"); ?></option>
    						</select> 
    					</td>
    				</tr>
    			</table>
    			<?php
    		}else{

    		}
    	}

    	function get_root_instagram() {
    		return trailingslashit( apply_filters( 'foogallery_filesystem_root', ABSPATH ) );
    	}

    	function fetch_images_from_instagram() {
    		if ( check_admin_referer( 'foogallery_datasource_instagram_get_images', 'nonce' ) ) {
    			$data = array();
    			
    			$instagram_token = get_option('instagram_token');
    			$instagram_array = json_decode($instagram_token);
    			   			
    			$respose = wp_remote_get("https://api.instagram.com/v1/users/self/media/recent/?access_token=".$instagram_array->access_token."&count=".$_POST['image_count']);
    			$respose_array = json_decode($respose['body'],true);
    			 
    			if($respose_array['meta']['code'] == '200'){
    				foreach ($respose_array['data'] as $instagram_images) {
    					$data[] = array(
    						"file" => $instagram_images['images'][$_POST['resolution']]['url'],
    						"caption" => $instagram_images['caption'],
    						"description" => "",
    						"alt" => "", 
    						"custom_url" => "",
    						"custom_target" => ""
    					);
    				}
    			}
    			$json = json_encode($data);
    			$option_key = $this->build_database_options_key( $_POST['gallery_id'] );
    			update_option( $option_key, $json );
    			update_option("instagram_count_".$_POST['gallery_id'],$_POST['image_count']);
    			update_option("instagram_resolution_".$_POST['gallery_id'],$_POST['resolution']);
    			$this->get_gallery_attachments_from_instagram($_POST['gallery_id']);
    		
    		}

    		die();
    	}


    	function build_attachments_from_instagram($image_count, $image_resolution) {
    		
    			$data = array();
    			
    			$instagram_token = get_option('instagram_token');
    			$instagram_array = json_decode($instagram_token);
    			   			
    			$respose = wp_remote_get("https://api.instagram.com/v1/users/self/media/recent/?access_token=".$instagram_array->access_token."&count=".$image_count);
    			$respose_array = json_decode($respose['body'],true);
    			 
    			if($respose_array['meta']['code'] == '200'){
    				foreach ($respose_array['data'] as $instagram_images) {

    					$attachment               = new FooGalleryAttachment();
    					$attachment->ID           = 0;
    					$attachment->title        = '';
    					$attachment->url          = $instagram_images['images'][$image_resolution]['url'];
    					$attachment->has_metadata = false;
    					$attachment->sort         = PHP_INT_MAX;    				  							
						$attachment->has_metadata = false;					
						$attachment->caption = $instagram_images['caption']['text'];
						$attachment->description = '';					
						$attachment->alt = '';					
						$attachment->custom_url = $instagram_images['images'][$image_resolution]['url'];
						$attachment->custom_target = '';					
						$attachment->sort = '';
    					$attachments[] = $attachment;
    					
    				}
    			}
    			/*$json = json_encode($data);
    			$option_key = $this->build_database_options_key($gallery_id );
    			update_option( $option_key, $json );*/
    		
    		
    		//usort( $attachments, array( $this, 'sort_attachments' ) );
    		
    		return $attachments;
    		
    	}


    	/**
    	 * Output the html required by the datasource in order to add item(s)
    	 *
    	 * @param FooGallery $gallery
    	 */
    	function render_datasource_item( $gallery ) {
    		$show_container = isset( $gallery->datasource_name ) && 'instagram' === $gallery->datasource_name;
    		$value          = ( $show_container && isset( $gallery->datasource_value['value'] ) ) ? $gallery->datasource_value['value'] : '';
    		?>
    		<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-instagram">
    		<h3><?php _e( 'Datasource : Instagram', 'foogallery' ); ?></h3>
    		<p><?php _e( 'This gallery will be dynamically populated with all images within the following instagram on your server:', 'foogallery' ); ?></p>
    		<div class="foogallery-items-html"><?php echo $value ?></div>
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
