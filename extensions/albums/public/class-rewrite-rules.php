<?php
/**
 * FooGallery Album Rewrite Rules
 */
if (!class_exists('FooGallery_Album_Rewrite_Rules')) {

    class FooGallery_Album_Rewrite_Rules {

        function __construct() {
	        add_action( 'init',  array( $this, 'add_gallery_endpoint' ) );
			add_filter( 'redirect_canonical', array( $this, 'disable_canonical_redirect_for_front_page' ), 10, 2 );
			add_action( 'update_option_page_on_front', array( $this, 'flush_rules' ) );
        }

	    function add_gallery_endpoint() {
	    	$gallery_slug = foogallery_album_gallery_url_slug();

			// Ensures the $query_vars['item'] is available
			add_rewrite_tag( "%{$gallery_slug}%", '([^&]+)' );

			// Requires flushing endpoints whenever the front page is switched to a different page
			$page_on_front = get_option( 'page_on_front' );

			// Match the front page and pass item value as a query var.
			add_rewrite_rule( "^{$gallery_slug}/([^/]*)/?", 'index.php?page_id='.$page_on_front.'&'.$gallery_slug.'=$matches[1]', 'top' );
			// Match non-front page pages.
			add_rewrite_rule( "^(.*)/{$gallery_slug}/([^/]*)/?", 'index.php?pagename=$matches[1]&static=true&'.$gallery_slug.'=$matches[2]', 'top' );
	    }

		// http://wordpress.stackexchange.com/a/220484/52463
		// In order to keep WordPress from forcing a redirect to the canonical
		// home page, the redirect needs to be disabled.
		function disable_canonical_redirect_for_front_page( $redirect_url, $requested_url ) {
			if ( is_page() && $front_page = get_option( 'page_on_front' ) ) {
				if ( is_page( $front_page ) ) {
					$redirect_url = false;
				}
			}

			return $redirect_url;
		}

	    function flush_rules() {
			flush_rewrite_rules();
		}
    }
}