<?php
/**
 * pixabay-php-api
 * PixabayClient API
 *
 * PHP Version 5
 *
 * @category Production
 * @package  Default
 * @author   Philipp Tkachev <zoonman@gmail.com>
 * @date     12/14/14 9:18 AM
 * @license  https://www.zoonman.com/projects/pixabay/license.txt MIT
 * @version  GIT: 1.0
 * @link     https://www.zoonman.com/projects/pixabay/
 */

/**
 * Class PixabayClient
 * @package Pixabay\PixabayClient
 */
class FooGallery_PixabayClient {
	/**
	 * @var array
	 */
	private $optionsList = [
		'key',
		'response_group',
		'id',
		'q',
		'lang',
		'callback',
		'image_type',
		'orientation',
		'category',
		'min_width',
		'min_height',
		'editors_choice',
		'safesearch',
		'page',
		'per_page',
		'pretty',
		'response_group',
		'order',
		'video_type'
	];

	/**
	 * Root of Pixabay REST API
	 */
	const API_ROOT = 'https://pixabay.com/api/';

	/**
	 * Get Data from Pixabay API
	 *
	 * @return mixed
	 */
	public function search( $key, $query )
	{
		$options['key'] = $key;
		$options['q'] = $query;

		$url = add_query_arg( array(
			'key' => $key,
			'q' => $query,
		), self::API_ROOT );

		$transient_key = 'foogallery-pixabay-' . esc_attr($query);

		if ( false === ( $response_data = get_transient( $transient_key ) ) ) {
			$response = wp_remote_get( esc_url( $url ) );
			$response_data = wp_remote_retrieve_body( $response );

			$expires = 60 * 60 * 24; //cache for 24 hours

			//Cache the result
			set_transient( $transient_key, $response_data, $expires );
		}

		return json_decode( $response_data, false );
	}
}