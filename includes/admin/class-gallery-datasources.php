<?php
/**
 * Class to handle all interactions for Gallery datasources
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_Datasources' ) ) {

    class FooGallery_Admin_Gallery_Datasources {

        /**
         * Primary class constructor.
         */
        public function __construct() {
            //render the datasource modal
            add_action( 'admin_footer', array( $this, 'render_datasource_modal' ) );
            add_action( 'wp_footer', array( $this, 'render_datasource_modal' ) );
            add_action( 'foogallery_gallery_metabox_items', array( $this, 'add_datasources_hidden_inputs' ) );
            add_action( 'foogallery_gallery_metabox_items_add', array( $this, 'add_datasources_button' ) );
            add_action( 'wp_ajax_foogallery_load_datasource_content', array( $this, 'ajax_load_datasource_content' ) );
            add_action( 'foogallery_before_save_gallery', array( $this, 'save_gallery_datasource' ), 8, 2 );
            add_filter( 'foogallery_preview_arguments', array( $this, 'include_datasource_in_preview' ), 10, 3 );
            add_filter( 'foogallery_render_template_argument_overrides', array( $this, 'override_datasource_arguments' ), 10, 2 );
        }

        /**
         * Include the datasource arguments for previews
         *
         * @param $args
         * @param $form_post
         * @param $template
         * @return array
         */
        public function include_datasource_in_preview( $args, $form_post, $template ) {
            if ( isset( $form_post['foogallery_datasource'] ) ) {
                $args['datasource'] = $form_post['foogallery_datasource'];
            }
            if ( isset( $form_post['foogallery_datasource_value'] ) ) {
                $args['datasource_value'] = $form_post['foogallery_datasource_value'];
            }

            return $args;
        }

        /**
         * Allow the gallery to render using an override for the datasource
         * @param $foogallery
         * @param $args
         * @return FooGallery
         */
        public function override_datasource_arguments( $foogallery, $args ) {
            if ( isset( $args['datasource'] ) ) {
                $foogallery->datasource_name = $args['datasource'];
            }
            if ( isset( $args['datasource_value'] ) ) {
                $foogallery->datasource_value = $this->get_json_datasource_value( $args['datasource_value'] );
            }

            return $foogallery;
        }

		/**
		 * Save the datasource name and value for the gallery
		 * @param $post_id
		 * @param $_post
		 */
        public function save_gallery_datasource( $post_id, $_post ) {
            //action pre-save
            do_action( 'foogallery_before_save_gallery_datasource', $post_id );

            //set some defaults
            $datasource = '';
            $datasource_value = array();

            if ( isset( $_POST[FOOGALLERY_META_DATASOURCE] ) ) {
				$datasource = $_POST[FOOGALLERY_META_DATASOURCE];
				update_post_meta( $post_id, FOOGALLERY_META_DATASOURCE, $datasource );

                if ( isset( $_POST[FOOGALLERY_META_DATASOURCE_VALUE] ) ) {
                    $datasource_value = $this->get_json_datasource_value( $_POST[FOOGALLERY_META_DATASOURCE_VALUE] );

                    if ( !empty( $datasource_value ) ) {
                        update_post_meta( $post_id, FOOGALLERY_META_DATASOURCE_VALUE, $datasource_value );
                    } else {
                        delete_post_meta( $post_id, FOOGALLERY_META_DATASOURCE_VALUE );
                    }
                }

			} else {
                delete_post_meta( $post_id, FOOGALLERY_META_DATASOURCE );
            }

            //action for post-save
            do_action( 'foogallery_after_save_gallery_datasource', $post_id, $datasource, $datasource_value );
        }

        /**
         * Safely returns an array from the json string
         * @param $datasource_value_string
         * @return mixed
         */
        public function get_json_datasource_value( $datasource_value_string ) {
            $datasource_value = array();

            //check if the value is JSON and convert to object if needed
            if ( is_string($datasource_value_string) && is_array( json_decode( stripslashes( $datasource_value_string ), true ) ) ) {
                $datasource_value = json_decode( stripslashes( $datasource_value_string ), true );
            }
            return $datasource_value;
        }

        /**
         * Outputs the modal content for the specific datasource
         */
        public function ajax_load_datasource_content() {
            $nonce = safe_get_from_request( 'nonce' );
            $datasource = safe_get_from_request( 'datasource' );
            $datasource_value = $this->get_json_datasource_value( safe_get_from_request( 'datasource_value' ) );
            $foogallery_id = intval( safe_get_from_request( 'foogallery_id' ) );

            if ( wp_verify_nonce( $nonce, 'foogallery-datasource-content' ) ) {
                do_action( 'foogallery-datasource-modal-content_'. $datasource, $foogallery_id, $datasource_value );
            }

            die();
        }

        /**
         * Adds the datasource hidden inputs to the page
         * @param FooGallery $gallery
         */
        public function add_datasources_hidden_inputs( $gallery ) {
            $datasources = foogallery_gallery_datasources();
            if ( count( $datasources ) > 1 ) {
                $datasource_value = get_post_meta( $gallery->ID, FOOGALLERY_META_DATASOURCE_VALUE, true );
                if ( is_array( $datasource_value ) ) {
                    $datasource_value = json_encode( $datasource_value );
                } ?>
            <input type="hidden" data-foogallery-preview="include" name="<?php echo FOOGALLERY_META_DATASOURCE; ?>" value="<?php echo $gallery->datasource_name; ?>" id="<?php echo FOOGALLERY_META_DATASOURCE; ?>" />
            <input type="hidden" data-foogallery-preview="include" value="<?php echo esc_attr( $datasource_value ); ?>" name="<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>" id="<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>" />
            <?php }
        }

        /**
         * Add the datasources button to the items metabox
         */
        public function add_datasources_button() {
            $datasources = foogallery_gallery_datasources();
            //we only want to show the datasources button if there are more than 1 datasources
            if ( count( $datasources ) > 1 ) { ?>
				<p><?php _e('or', 'foogallery');?></p>
				<button type="button" class="button button-secondary button-hero gallery_datasources_button">
					<span class="dashicons dashicons-format-gallery"></span><?php _e( 'Add From Another Source', 'foogallery' ); ?>
				</button>
            <?php }
        }

        /**
         * Renders the datasource modal for use on the gallery edit page
         */
        public function render_datasource_modal() {

            global $post;

            //check if the gallery edit page is being shown
            $screen = get_current_screen();
            if ( 'foogallery' !== $screen->id ) {
                return;
            }

            $datasources = foogallery_gallery_datasources();

            ?>
            <style>
                .foogallery-gallery-select.selected {
                    border-color: #1E8CBE;
                }

                .foogallery-gallery-select.selected::before {
                    content: "\f147";
                    display: inline-block;
                    font: normal 100px/110px 'dashicons';
                    position: absolute;
                    color: #FFF;
                    top: 40%;
                    left: 50%;
                    margin-left: -50px;
                    margin-top: -50px;
                    speak: none;
                    -webkit-font-smoothing: antialiased;
                    background: #1E8CBE;
                    border-radius: 50%;
                    width: 100px;
                    height: 100px;
                    z-index: 4;
                }

                .foogallery-gallery-select-inner {
                    opacity: 0.8;
                    position: absolute;
                    bottom: 8px;
                    left: 8px;
                    right: 8px;
                    padding: 5px;
                    background: #FFF;
                    text-align: center;
                }

                .foogallery-gallery-select-inner h3 {
                    display: block;
                    margin: 0;
                }

                .foogallery-gallery-select-inner span {
                    display: block;
                    font-size: 0.9em;
                }

                .foogallery-add-gallery {
                    background: #444;
                }

                .foogallery-add-gallery span::after {
                    background: #ddd;
                    -webkit-border-radius: 50%;
                    border-radius: 50%;
                    display: inline-block;
                    content: '\f132';
                    -webkit-font-smoothing: antialiased;
                    font: normal 75px/115px 'dashicons';
                    width: 100px;
                    height: 100px;
                    vertical-align: middle;
                    text-align: center;
                    color: #999;
                    position: absolute;
                    top: 40%;
                    left: 50%;
                    margin-left: -50px;
                    margin-top: -50px;
                    padding: 0;
                    text-shadow: none;
                    z-index: 4;
                    text-indent: -4px;
                }

                .foogallery-add-gallery:hover span::after {
                    background: #1E8CBE;
                    color: #444;
                }

                .foogallery-datasource-modal-title {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 50px;
                    z-index: 200;
                    padding-left: 16px;
                    border-bottom: 1px solid #ddd;
                }

                .foogallery-datasource-modal-sidebar {
                    position: absolute;
                    top: 51px;
                    left: 0;
                    bottom: 0;
                    width: 200px;
                    z-index: 75;
                    background: #f3f3f3;
                    overflow: auto;
                    -webkit-overflow-scrolling: touch;
                }

                .foogallery-datasource-modal-sidebar-menu {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    margin: 0;
                    padding: 10px 0;
                    background: #f3f3f3;
                    border-right-width: 1px;
                    border-right-style: solid;
                    border-right-color: #ccc;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none
                }

                .foogallery-datasource-modal-sidebar-menu>a {
                    display: block;
                    position: relative;
                    padding: 8px 20px;
                    margin: 0;
                    line-height: 18px;
                    font-size: 14px;
                    color: #0073aa;
                    text-decoration: none;
                }

                .foogallery-datasource-modal-sidebar-menu>a:hover {
                    color: #0073aa;
                    background: rgba(0,0,0,.04)
                }

                .foogallery-datasource-modal-sidebar-menu .active,
                .foogallery-datasource-modal-sidebar-menu .active:hover{
                    color: #23282d;
                    font-weight: 600;
                    background: rgba(0,0,0,.04);
                }

                .foogallery-datasource-modal-sidebar-menu>a,
                .foogallery-datasource-modal-sidebar-menu>a:active,
                .foogallery-datasource-modal-sidebar-menu>a:hover,
                .foogallery-datasource-modal-sidebar-menu>a:focus {
                    box-shadow: none;
                    outline: 0
                }

                .media-menu .separator {
                    height: 0;
                    margin: 12px 20px;
                    padding: 0;
                    border-top: 1px solid #ddd
                }

                .foogallery-datasource-modal-container {
                    position: absolute;
                    top: 51px;
                    padding: 16px;
                    left: 200px;
                    right: 0;
                    bottom: 0;
                    overflow: auto;
                    outline: 0;
                }

                .foogallery-datasource-modal-toolbar {
                    position: absolute;
                    left: 200px;
                    right: 0;
                    bottom: 0;
                    height: 60px;
                    z-index: 100;
                    bottom: 60px;
                    height: auto;
                    border-top: 1px solid #ddd;
                }

                .foogallery-datasource-modal-toolbar-inner {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: -45px;
                    height: 60px;
                    padding: 0 16px;
                    overflow: hidden;
                }

                .foogallery-datasource-modal-container-inner .spinner {
                    float: left;
                }

                .foogallery-datasource-modal-insert {
                    float: right;
                }

                .datasource-info {
                    background: #c8daec;
                    box-shadow: 0 0 0 1px #ccc;
                    width: 150px;
                    position: relative;
                    float: left;
                    padding: 0;
                    color: #464646;
                    list-style: none;
                    text-align: center;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    -o-user-select: none;
                    user-select: none;
                }

                .datasource-info>div {
                    display: table-cell;
                    vertical-align: middle;
                    height: 150px;
                    text-align: center;
                    width: 150px;
                    height: 150px;
                    color: #666;
                    text-decoration: none;
                }

				.datasource-info .center {
					padding: 5px;
				}

                .datasource-info a.remove {
                    display: none;
                    top: 5px;
                    right: 5px;
                    position: absolute;
                    padding: 0;
                    font-size: 20px;
                    line-height: 20px;
                    text-align: center;
                    text-decoration: none;
                    background-color: #444;
                    border-top-right-radius: 50%;
                    border-top-left-radius: 50%;
                    border-bottom-right-radius: 50%;
                    border-bottom-left-radius: 50%;
                    color: #fff;
                }

                .datasource-info:hover a.remove {
                    display: block;
                }

                .datasource-info a.edit {
                    display: none;
                    top: 5px;
                    left: 5px;
                    position: absolute;
                    padding: 0;
                    font-size: 20px;
                    line-height: 20px;
                    text-align: center;
                    text-decoration: none;
                    background-color: #444;
                    border-top-right-radius: 50%;
                    border-top-left-radius: 50%;
                    border-bottom-right-radius: 50%;
                    border-bottom-left-radius: 50%;
                    color: #fff;
                }

                .datasource-info:hover a.edit {
                    display: block;
                }
            </style>
            <script type="text/javascript">
                jQuery(function ($) {
                    $('.gallery_datasources_button').on('click', function(e) {
                        e.preventDefault();
                        $('.foogallery-datasources-modal-wrapper').show();
                        $('.foogallery-datasource-modal-selector:first').click();
                    });

                    $('.foogallery-datasources-modal-wrapper').on('click', '.media-modal-close, .foogallery-datasource-modal-cancel', function(e) {
                        $('.foogallery-datasources-modal-wrapper').hide();
                    });

                    $('.foogallery-datasources-modal-wrapper').on('click', '.foogallery-datasource-modal-insert', function(e) {
                        //alert( $('#foogallery_datasource_text').val() + ' --- ' + $('#foogallery_datasource_value').val() );
						var activeDatasource = $('.foogallery-datasource-modal-selector.active').data('datasource');

						//set the datasource
						$('#foogallery_datasource').val( activeDatasource );

						//raise a general event so that other datasources can clean up
						$(document).trigger('foogallery-datasource-changed');

						//raise a specific event for the new datasource so that things can be done
						$(document).trigger('foogallery-datasource-changed-' + activeDatasource);

						//hide the datasource modal
						$('.foogallery-datasources-modal-wrapper').hide();
                    });

                    $('.foogallery-datasource-modal-selector').on('click', function(e) {
                        e.preventDefault();

                        var datasource = $(this).data('datasource'),
                            $content = $('.foogallery-datasource-modal-container-inner.' + datasource);

                        $('.foogallery-datasource-modal-selector').removeClass('active');
                        $(this).addClass('active');

                        $('.foogallery-datasource-modal-container-inner').hide();

                        $content.show();

                        $('#<?php echo FOOGALLERY_META_DATASOURCE; ?>').val(datasource);

                        var datasource_value = $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val();

                        if ( $content.hasClass('not-loaded') ) {
                            $content.find('.spinner').addClass('is-active');

                            $content.removeClass('not-loaded');

                            var data = 'action=foogallery_load_datasource_content' +
                                '&datasource=' + datasource +
                                '&datasource_value=' + encodeURIComponent(datasource_value) +
                                '&foogallery_id=<?php echo $post->ID; ?>' +
                                '&nonce=<?php echo wp_create_nonce( 'foogallery-datasource-content' ); ?>';

                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: data,
                                success: function(data) {
                                    $content.html(data);
                                }
                            });
                        }
                    });
                });
            </script>
            <?php wp_nonce_field('foogallery_load_galleries', 'foogallery_load_galleries', false); ?>
            <div class="foogallery-datasources-modal-wrapper" style="display: none;">
                <div class="media-modal wp-core-ui">
                    <button type="button" class="media-modal-close">
                        <span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span>
                    </button>
                    <div class="media-modal-content">
                        <div class="media-frame wp-core-ui">
                            <div class="foogallery-datasource-modal-title">
                                <h1><?php _e('Add To Gallery From Another Source', 'foogallery'); ?></h1>
                            </div>
                            <div class="foogallery-datasource-modal-sidebar">
                                <div class="foogallery-datasource-modal-sidebar-menu">
                                    <?php foreach ( $datasources as $key=>$datasource ) {
                                    if ( $datasource['public'] ) { ?>
                                    <a href="#" class="media-menu-item foogallery-datasource-modal-selector" data-datasource="<?php echo $key; ?>"><?php echo $datasource['menu']; ?></a>
                                        <?php } } ?>
                                </div>
                            </div>
                            <div class="foogallery-datasource-modal-container">
                                <?php foreach ( $datasources as $key=>$datasource ) {
                                    if ( $datasource['public'] ) { ?>
                                        <div class="foogallery-datasource-modal-container-inner <?php echo $key; ?> not-loaded">
                                            <div class="spinner"></div>
                                        </div>
                                    <?php } } ?>
                            </div>
                            <div class="foogallery-datasource-modal-toolbar">
                                <div class="foogallery-datasource-modal-toolbar-inner">
                                    <div class="media-toolbar-secondary">
                                        <a href="#"
                                           class="foogallery-datasource-modal-cancel button media-button button-large button-secondary media-button-insert"
                                           title="<?php esc_attr_e('Cancel', 'foogallery'); ?>"><?php _e('Cancel', 'foogallery'); ?></a>
                                    </div>
                                    <div class="media-toolbar-primary">
                                        <a href="#"
                                           class="foogallery-datasource-modal-insert button media-button button-large button-primary media-button-insert"
                                           disabled="disabled"
                                           title="<?php esc_attr_e('OK', 'foogallery'); ?>"><?php _e('OK', 'foogallery'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="media-modal-backdrop"></div>
            </div>
            <?php
        }
    }
}
