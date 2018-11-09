<?php
/**
 * ElasticPress Compatibility Class
 * Date: 14/10/2018
 */
if ( ! class_exists( 'FooGallery_ElasticPress_Compatibility' ) ) {

	class FooGallery_ElasticPress_Compatibility {
		function __construct() {
			add_action( 'ep_indexable_post_types', array( $this, 'exclude_foogallery_from_index' ), 99 );
		}

		/*
		 * Do not include FooGallery posts in the ElasticPress index
		 */
		function exclude_foogallery_from_index( $post_types ) {
			if ( array_key_exists( 'foogallery',  $post_types ) ) {
				unset( $post_types['foogallery'] );
			}
			return $post_types;
		}
	}
}