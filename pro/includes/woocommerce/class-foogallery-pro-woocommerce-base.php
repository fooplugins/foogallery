<?php
namespace FooPlugins\FooGallery\Pro\Woocommerce;

use FooGallery;

/**
 * Base class for common WooCommerce functions
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_Pro_Woocommerce_Base' ) ) {

	/**
	 * Class FooGallery_Pro_Woocommerce_Base
	 */
	class FooGallery_Pro_Woocommerce_Base {

		/**
		 * Get the product name for the cart or order item.
		 *
		 * @param $default_product_name
		 * @param $foogallery_id
		 * @param $attachment_id
		 *
		 * @return mixed|string
		 */
		protected function get_product_name($default_product_name, $foogallery_id, $attachment_id)
		{
			$foogallery = $this->get_gallery($foogallery_id);
			if ('title' === $foogallery->get_setting('ecommerce_transfer_product_name_source', '')) {
				return get_the_title($attachment_id);
			} elseif ('caption' === $foogallery->get_setting('ecommerce_transfer_product_name_source', '')) {
				return get_the_excerpt($attachment_id);
			}
			return $default_product_name;
		}

		/**
		 * Get the gallery from a cache to avoid loading it multiple times
		 *
		 * @param $foogallery_id
		 *
		 * @return bool|FooGallery|mixed
		 */
		protected function get_gallery($foogallery_id)
		{
			global $foogallery_gallery_cache;

			if (!is_array($foogallery_gallery_cache)) {
				$foogallery_gallery_cache = array();
			}

			if (!array_key_exists($foogallery_id, $foogallery_gallery_cache)) {
				$foogallery_gallery_cache[$foogallery_id] = FooGallery::get_by_id( $foogallery_id );
			}

			return $foogallery_gallery_cache[$foogallery_id];
		}
	}
}
