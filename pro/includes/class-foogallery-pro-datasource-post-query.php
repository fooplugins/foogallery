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
            add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
            add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
        }

    	/**
         * Add the instagrams Datasource
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
                                <select class="regular-text" name="post_type">
                                    <option value=""><?php _e('Select post type') ?></option>
                                    <?php
                                        foreach( get_post_types(array('public'=>true)) as $key => $value){
                                            echo '<option>' . $value . '</option>';
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Number of posts', 'foogallery'); ?></th>
                            <td>
                                <input type="text" class="regular-text" name="no_of_post" />
                                <p class="description"><?php _e('Number of images to show', 'foogallery') ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Exclude', 'foogallery'); ?></th>
                            <td>
                                <input type="text" class="regular-text" name="exclude" />
                                <p class="description"><?php _e('Write comma seperated names of the post type which you want to exclude', 'foogallery') ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Link To', 'foogallery'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="link_to" value="post" checked="checked">
                                        <span><?php _e('Post', 'foogallery') ?></span>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="link_to" value="image">
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