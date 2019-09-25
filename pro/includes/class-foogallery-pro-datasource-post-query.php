<?php
/**
 * The Gallery Datasource which pulls Post thumbnail of all the post.
 */
if (!class_exists('FooGallery_Pro_Datasource_Post_Query')) {

    class FooGallery_Pro_Datasource_Post_Query
    {
        public function __construct()
        {
            add_action( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ) );
            add_action( 'foogallery-datasource-modal-content_post_query', array( $this, 'render_datasource_modal_content' ), 10, 2 );
            add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
            add_filter( 'foogallery_datasource_post_query_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
            add_filter( 'foogallery_datasource_post_query_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );
            add_filter( 'foogallery_datasource_post_query_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
            add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );
            add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
        }
        
        public function before_save_gallery_datasource_clear_datasource_cached_images( $foogallery_id )
        {
            $this->clear_gallery_transient( $foogallery_id );
        }

    	public function clear_gallery_transient( $foogallery_id ) {
    		$transient_key = '_foogallery_datasource_post_query_' . $foogallery_id;
    		delete_transient( $transient_key );
    	}

        public function get_gallery_featured_attachment()
        {
            return $this->get_gallery_attachments_from_post_query( $foogallery );
        }
        
        public function get_gallery_attachment_count( $count, $foogallery ){
            return count( $this->get_gallery_attachments_from_post_query( $foogallery ) );
        }

    	public function get_gallery_attachments( $attachments, $foogallery ) {
            return $this->get_gallery_attachments_from_post_query( $foogallery );
    	}

        public function get_gallery_attachments_from_post_query($foogallery) {
            global $foogallery_gallery_preview;
            
            $attachments = array();

            if (!empty($foogallery->datasource_value))
            {
                $transient_key = '_foogallery_datasource_post_query_' . $foogallery->ID;

                //never get the cached results if we are doing a preview
                if (isset($foogallery_gallery_preview))
                {
                    $cached_attachments = false;
                }
                else
                {
                    $cached_attachments = get_transient($transient_key);
                }

                if (false === $cached_attachments)
                {
                    $datasource_value = $foogallery->datasource_value;

                    $expiry_hours = apply_filters('foogallery_datasource_post_query_expiry', 24);
                    $expiry = $expiry_hours * 60 * 60;

                    //find all image files in the post_query
                    $attachments = $this->build_attachments_from_post_query($foogallery->datasource_value);

                    //save a cached list of attachments
                    set_transient($transient_key, $attachments, $expiry);
                }
                else
                {
                    $attachments = $cached_attachments;
                }
            }
            return $attachments;
        }
        
        
        function build_attachments_from_post_query($settings)
        {
            $data = array();
            $posts = get_posts(array(
                'posts_per_page' => $settings['no_of_post'],
                'post_type'      => $settings['gallery_post_type'],
                'post_status'    => 'publish',
                'post__not_in'   => explode(',', $settings['exclude'])
            ));
            
            foreach ($posts as $post)
            {
                $attachment = new FooGalleryAttachment();
                $url = get_permalink($post->ID);
                if( $settings['link_to'] == 'image')
                    $url = get_the_post_thumbnail_url($post->ID);

                if( !empty(get_the_post_thumbnail_url( $post->ID )) )
                {
                    $attachment->ID             = $post->ID;
                    $attachment->title          = $post->post_title;
                    $attachment->url            = get_the_post_thumbnail_url($post->ID);
                    $attachment->has_metadata   = false;
                    $attachment->sort           = PHP_INT_MAX;
                    $attachment->caption        = '';
                    $attachment->description    = $post->post_excerpt;
                    $attachment->alt            = '';
                    $attachment->custom_url     = $url;
                    $attachment->custom_target  = '';
                    $attachment->sort           = '';
                    $attachments[] = $attachment;
                }
            }
            return $attachments;
        }

        /**
         * Add the post_querys Datasource
         * @param $datasources
         * @return mixed
         */
        public function add_datasource($datasources)
        {
            $datasources['post_query'] = array(
                'id' => 'post_query',
                'name' => __('Post Query', 'foogallery'),
                'menu' => __('Post Query', 'foogallery'),
                'public' => true
            );

            return $datasources;
        }

    	/**
         * Enqueues folders-specific assets
         */
        public function enqueue_scripts_and_styles() {
            wp_enqueue_script( 'foogallery.admin.datasources.post.query', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.post.query.js', array( 'jquery' ), FOOGALLERY_VERSION );
        }
        
        
    	/**
    	 * Output the datasource modal content
    	 *
    	 * @param $foogallery_id
    	 */
        public function render_datasource_modal_content( $foogallery_id, $datasource_value )
        {
?>
            <form action="" method="post" name="post_query_gallery_form">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><?php _e('Post types', 'foogallery') ?></th>
                            <td>
                                <select class="regular-text" name="post_type" id="gallery_post_type">
                                    <option value=""><?php _e('Select a post type') ?></option>
                                    <?php
                                        foreach( get_post_types(array('public'=>true)) as $key => $value){
                                            $selected = '';
                                            if( isset($datasource_value['gallery_post_type']) )
                                                $selected = 'selected';
                                            echo "<option value='$value' $selected>" . ucfirst($value) . '</option>';
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Number of posts', 'foogallery'); ?></th>
                            <td>
                                <input
                                    type="text"
                                    class="regular-text"
                                    name="no_of_post"
                                    id="no_of_post"
                                    value="<?php echo isset($datasource_value['no_of_post']) ? $datasource_value['no_of_post'] : '' ?>"
                                />
                                <p class="description"><?php _e('Number of images to show', 'foogallery') ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Exclude', 'foogallery'); ?></th>
                            <td>
                                <input
                                    type="text"
                                    class="regular-text"
                                    name="exclude"
                                    id="exclude"
                                    value="<?php echo isset($datasource_value['exclude']) ? $datasource_value['exclude'] : '' ?>"
                                />
                                <p class="description"><?php _e('Write comma seperated id\'s of the post which you want to exclude', 'foogallery') ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Link To', 'foogallery'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input
                                            type="radio"
                                            name="link_to"
                                            value="post"
                                            class="link_to"
                                            <?php echo (isset($datasource_value['link_to']) && $datasource_value['link_to'] == 'post') ? 'checked="checked"' : '' ?>"
                                        />
                                        <span><?php _e('Post', 'foogallery') ?></span>
                                    </label>
                                    <br>
                                    <label>
                                        <input
                                            type="radio"
                                            name="link_to"
                                            value="image"
                                            class="link_to"
                                            <?php echo (isset($datasource_value['link_to']) && $datasource_value['link_to'] == 'image') ? 'checked="checked"' : '' ?>"
                                        />
                                        <span><?php _e('Images', 'foogallery') ?></span>
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
        function render_datasource_item( $gallery )
        {
            $show_container = isset( $gallery->datasource_name ) && 'post_query' === $gallery->datasource_name;
            $value          = ( $show_container && isset( $gallery->datasource_value['value'] ) ) ? $gallery->datasource_value['value'] : '';
?>
            <div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-post_query">
                <h3>
                    <?php _e( 'Datasource : Post Query', 'foogallery' ); ?>
                </h3>
                <p>
                    <?php _e( 'This gallery will be dynamically populated with all images within the selected post type:', 'foogallery' ); ?>
                </p>
                <div class="foogallery-items-html">
                    <?php echo $value ?>
                </div>
                <br />
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
