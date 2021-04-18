<?php
/**
 * FooGallery_CSS_Load_Optimizer class which enqueues CSS in the head
 */
if (!class_exists('class-css-load-optimizer.php')) {

    class FooGallery_CSS_Load_Optimizer {

        function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'include_gallery_css' ) );
            add_action( 'foogallery_enqueue_style', array( $this, 'enqueue_style_to_persist' ), 10, 5 );
            add_action( 'wp_footer', array( $this, 'persist_enqueued_styles' ) );
        }

		/**
		 * Persist any styles that are enqueued to be persisted
		 */
        function persist_enqueued_styles() {
			global $wp_query, $foogallery_styles_to_persist;

			//we only want to do this if we are looking at a single post
			if ( ! is_singular() ) {
				return;
			}

			$post_id = $wp_query->post->ID;
			if ( $post_id  && is_array( $foogallery_styles_to_persist ) ) {
				foreach( $foogallery_styles_to_persist as $style_handle => $style ) {
					add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE_CSS, array( $style_handle => $style ), false );
				}
			}
		}

        /**
         * Get the current post ids for the view that is being shown
         */
        function get_post_ids_from_query() {
            global $wp_query;

            if ( is_singular() ) {
                return array( $wp_query->post->ID );
            } else if ( is_array( $wp_query->posts ) ) {
                return wp_list_pluck( $wp_query->posts, 'ID' );
            } else {
                return array();
            }
        }

        /**
         * Checks the post meta for any FooGallery CSS that needs to be added to the head
         */
        function include_gallery_css() {
            global $enqueued_foogallery_styles;

            $enqueued_foogallery_styles = array();

            foreach( $this->get_post_ids_from_query() as $post_id ) {
                $this->include_gallery_stylesheets_for_post( $post_id );
            }
        }

        /**
         * includes any CSS that needs to be added for a post
         *
         * @param $post_id int ID of the post
         */
        function include_gallery_stylesheets_for_post( $post_id ) {
            global $enqueued_foogallery_styles;

            if ( $post_id ) {
                //get any foogallery stylesheets that the post might need to include
                $css = get_post_meta($post_id, FOOGALLERY_META_POST_USAGE_CSS);

				if ( empty( $css ) || !is_array( $css ) ) return;

                foreach ($css as $css_item) {
                    if ( !$css_item ) continue;
	                if ( empty( $css_item ) || !is_array( $css_item ) ) return; //make sure we are dealing with an array
                    foreach ($css_item as $handle => $style) {
                        //only enqueue the stylesheet once
                        if ( !array_key_exists( $handle, $enqueued_foogallery_styles ) ) {
                            $cache_buster_key = $handle;
                            if ( is_array( $style ) ) {
                                $cache_buster_key = $this->create_cache_buster_key( $handle, $style['ver'], array_key_exists( 'site', $style ) ? $style['site'] : '' );
                                wp_enqueue_style( $handle, $style['src'], $style['deps'], $style['ver'], $style['media'] );
                            } else {
                                wp_enqueue_style( $handle, $style );
                            }

                            $enqueued_foogallery_styles[$handle] = $cache_buster_key;
                        }
                    }
                }
            }
        }

        /**
         * Check to make sure we have added the stylesheets to our custom post meta field,
         * so that on next render the stylesheet will be added to the page header
         *
         * @param $style_handle string The stylesheet handle
         * @param $src string The location for the stylesheet
         * @param array $deps
         * @param bool $ver
         * @param string $media
         */
        function enqueue_style_to_persist($style_handle, $src, $deps = array(), $ver = false, $media = 'all') {
            global $wp_query, $enqueued_foogallery_styles, $foogallery_styles_to_persist;

            //we only want to do this if we are looking at a single post
            if ( ! is_singular() ) {
                return;
            }

            $post_id = $wp_query->post->ID;
            if ( $post_id ) {

                //check if the saved stylesheet needs to be cache busted
                if ( is_array( $enqueued_foogallery_styles ) && array_key_exists( $style_handle, $enqueued_foogallery_styles ) ) {
                    $registered_cache_buster_key = $enqueued_foogallery_styles[$style_handle];

                    //generate the key we want
                    $cache_buster_key = $this->create_cache_buster_key( $style_handle, $ver, home_url() );

                    if ( $registered_cache_buster_key !== $cache_buster_key ) {
                        //we need to bust this cached stylesheet!
                        $style = $this->get_old_style_post_meta_value( $post_id, $style_handle );

                        if ( false !== $style ) {
                        	//delete it from the post
                            delete_post_meta( $post_id, FOOGALLERY_META_POST_USAGE_CSS, array( $style_handle => $style ) );

                            //unset the handle, to force the save of the post meta
                            unset( $enqueued_foogallery_styles[$style_handle] );
                        }
                    }
                }

                //first check that the template has not been enqueued before
                if ( is_array( $enqueued_foogallery_styles ) && ! array_key_exists( $style_handle, $enqueued_foogallery_styles ) ) {

                    $style = array(
                        'src'   => $src,
                        'deps'  => $deps,
                        'ver'   => $ver,
                        'media' => $media,
                        'site'  => home_url()
                    );

                    if ( !is_array( $foogallery_styles_to_persist ) ) {
						$foogallery_styles_to_persist = array();
					}

					if ( !array_key_exists( $style_handle, $foogallery_styles_to_persist ) ) {
						$foogallery_styles_to_persist[$style_handle] = $style;
					}
                }
            }
        }

	    /**
	     * Create a key that will be used to cache
	     *
	     * @param        $name
	     * @param        $version
	     * @param string $site
	     *
	     * @return string
	     */
        function create_cache_buster_key( $name, $version, $site = '' ) {
            return "{$site}::{$name}_{$version}";
        }

	    /**
	     * Get the old style handle that was linked to the post
	     *
	     * @param $post_id
	     * @param $handle_to_find
	     *
	     * @return false|mixed
	     */
        function get_old_style_post_meta_value( $post_id, $handle_to_find ) {
            $css = get_post_meta($post_id, FOOGALLERY_META_POST_USAGE_CSS);

            foreach ($css as $css_item) {
                if ( ! $css_item ) {
                    continue;
                }
                foreach ( $css_item as $handle => $style ) {
                    //only enqueue the stylesheet once
                    if ( $handle_to_find === $handle ) {
                        return $style;
                    }
                }
            }

            return false;
        }
    }
}