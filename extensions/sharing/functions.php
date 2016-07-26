<?php




/**
 * Returns the sharing param that is used when sharing a URL
 *
 * @return string
 */
function foogallery_sharing_param() {
	return apply_filters( 'foogallery_sharing_param', FOOGALLERY_SHARING_PARAM );
}

/**
 * Returns the default network options used in every network
 *
 * @return array
 */
function foogallery_sharing_network_defaults() {
	return apply_filters( 'foogallery_sharing_network_defaults', array(
		'meta_tags'  => array(),
		'ua_regex'   => null,
		'url_format' => null
	) );
}

/**
 * Returns supported networks
 *
 * @return array
 */
function foogallery_sharing_supported_networks() {
	return apply_filters( 'foogallery_sharing_supported_networks', array() );
}

/**
 * Returns a specific network by key
 *
 * @param $network string The key of the network we are looking for
 *
 * @return mixed Returns an array of network info, else boolean false
 */
function foogallery_sharing_supported_network( $network ) {
	$networks = foogallery_sharing_supported_networks();

	if ( array_key_exists( $network, $networks ) ) {
		return $networks[$network];
	}

	return false;
}

/**
 * Generates a share URL for a network, based on the share object saved in the database
 *
 * @param $network string Name of the network
 * @param $share object The share object
 *
 * @return mixed Returns a URL string for a valid network, else boolean false
 */
function foogallery_sharing_generate_share_url( $network, $share ) {
	$network = foogallery_sharing_supported_network( $network );

	if ( $network !== false ) {
		$url = $network->url_format;

		$share = apply_filters( 'foogallery_sharing_generate_share_url-' . $network, $share );

		foreach($share as $key => $val){
			$key = sprintf('{%s}', $key);
			$url = str_replace($key, urlencode($val), $url);
		}
		return $url;
	}

	return false;
}

function foogallery_sharing_extract_share_request() {
	if ( isset( $_GET[foogallery_sharing_param()] ) ) {
		$id = base_convert( $_GET[foogallery_sharing_param()], 36, 10 );
		if (is_numeric( $id )){
			return $id;
		}
	}
	return false;
}