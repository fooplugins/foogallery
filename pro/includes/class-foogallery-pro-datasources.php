<?php
/**
 * FooGallery Pro Datasources
 */
if ( ! class_exists( 'FooGallery_Pro_Datasources' ) ) {

    class FooGallery_Pro_Datasources {
        function __construct() {
            //add the datasources
            add_action( 'foogallery_gallery_datasources', array($this, 'add_datasources') );
            add_action( 'foogallery-datasource-modal-content', array($this, 'render_datasource_modal_content'), 10, 2 );
        }

        /**
         * Add the PRO datasources
         * @param $datasources
         * @return mixed
         */
        function add_datasources( $datasources ) {
            $datasources['media_tags'] = array(
                'id'     => 'media_tags',
                'name'   => __( 'Media Tags', 'foogalery' ),
                'menu'  => __( 'Media Tags', 'foogallery' ),
                'class'  => 'FooGallery_Pro_Datasource_MediaTags',
                'public' => true
            );

            $datasources['media_categories'] = array(
                'id'     => 'media_categories',
                'name'   => __( 'Media Categories', 'foogalery' ),
                'menu'  => __( 'Media Categories', 'foogallery' ),
                'class'  => 'FooGallery_Pro_Datasource_MediaCategories',
                'public' => true
            );

            return $datasources;
        }

        /**
         * Output the datasource modal content
         * @param $datasource
         */
        function render_datasource_modal_content( $datasource, $foogallery_id ) {
            if ( 'media_tags' === $datasource ) {
                ?>
                <style>
                    .datasource-taxonomy {
                        position: relative;
                        float: left;
                        margin-right: 10px;
                    }

                    .datasource-taxonomy a {
                        border: 1px solid #ddd;
                        border-radius: 5px;
                        padding: 4px 8px;
                        display: block;
                        padding: 10px;
                        text-align: center;
                        text-decoration: none;
                        font-size: 1.2em;
                    }

                    .datasource-taxonomy a.active {
                        color: #fff;
                        background: #0085ba;
                        border-color: #0073aa #006799 #006799;
                    }
                </style>
                <script type="text/javascript">
                    jQuery(function ($) {
                        $('.foogallery-datasource-modal-container').on('click', '.datasource-taxonomy a', function (e) {
                            e.preventDefault();
                            $(this).toggleClass('active');
                            $selected = $(this).parents('ul:first').find('a.active');

                            //validate if the OK button can be pressed.
                            if ( $selected.length > 0 ) {
                                $('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

                                var taxonomy_values = [],
                                    taxonomies = [];

                                $selected.each(function() {
                                    taxonomy_values.push( $(this).data('termId') );
                                    taxonomies.push( $(this).text() );
                                });

                                $('#foogallery_datasource_text').val( 'Tags : ' + taxonomies.join(', ') );

                                //set the selection
                                $('#foogallery_datasource_value').val( JSON.stringify( {
                                    "datasource" : "media_tags",
                                    "value" : taxonomy_values
                                } ) );
                            } else {
                                $('.foogallery-datasource-modal-insert').attr('disabled','disabled');

                                //clear the selection
                                $('#foogallery_datasource_value').val('');
                            }
                        });
                    });
                </script>
                <p><?php _e('Select a media tag from the list below. The gallery will then dynamically load all attachments that are associated to that tag.', 'foogallery'); ?></p>
                <ul>
                <?php

                $terms = get_terms( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, array('hide_empty' => false) );

                foreach($terms as $term) {
                    ?><li class="datasource-taxonomy media_tags">
                        <a href="#" data-term-id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
                    </li><?php
                }

                ?>
                </ul>
                <?php
            } else if ('media_categories' === $datasource ) {
                echo $datasource . ' content';
            }
        }
    }
}
