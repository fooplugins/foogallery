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
 * Class FooGallery_PixabayClient
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
	 * @param        $key
	 * @param        $query
	 * @param int    $count
	 * @param string $image_type
	 * @param string $response_group
	 *
	 * @param string $safesearch
	 *
	 * @return mixed
	 */
	public function search( $key, $query, $count = 20, $image_type = 'photo', $response_group = 'high_resolution', $safesearch = 'true')
	{
		$url = add_query_arg( array(
			'key' => $key,
			'q' => urlencode( $query ),
			'per_page' => $count,
			'image_type' => $image_type,
			'response_group' => $response_group,
			'safesearch' => $safesearch
		), self::API_ROOT );

		$transient_key = 'foogallery-pixabay-' . urlencode($query) . '-' . $count;

		if ( false === ( $response_data = get_transient( $transient_key ) ) ) {
			$response = wp_remote_get( $url );
			$response_data = wp_remote_retrieve_body( $response );

			$expires = 60 * 60 * 24; //cache for 24 hours

			//Cache the result
			set_transient( $transient_key, $response_data, $expires );
		}

		return json_decode( $response_data, false );
	}
}