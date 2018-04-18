<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 4/17/2018
 * Time: 10:09 PM
 */

if ( !class_exists("FooGallery_Pro_Video_Self_Hosted") ){

	require_once dirname(__FILE__) . '/class-foogallery-pro-video-base.php';

	class FooGallery_Pro_Video_Self_Hosted extends FooGallery_Pro_Video_Base {

		// region Properties

		/**
		 * The regular expression used to match a self-hosted video URL.
		 * @var string
		 */
		public $regex_pattern;
		/**
		 * The array of supported MIME types for self-hosted videos.
		 * @var array
		 */
		public $mime_types;

		// endregion

		function __construct() {
			$this->regex_pattern = '/\/(?<name>[^\/]+?)\.(?<ext>mp4|ogg|ogv|webm)(?:$|\?|#|,)/i';
			$this->mime_types = array("video/mp4","video/ogg","video/ogv","video/webm");
		}

		/**
		 * Takes a URL and checks if this class handles it.
		 *
		 * @param string $url The URL to check.
		 * @param array &$matches Optional. If matches is provided, it is passed to the `preg_match` call used to check the URL.
		 * @return int Returns 1 if the URL is handled, 0 if it is not, or FALSE if an error occurred.
		 */
		function handles($url, &$matches = array()){
			return preg_match($this->regex_pattern, $url, $matches);
		}

		/**
		 * Takes the supplied URL and fetches a self-hosted video object.
		 *
		 * @param string $url The URL to retrieve the video object for.
		 *
		 * @return array(
		 * 	"mode" => "self-hosted",
		 * 	"id" => string,
		 * 	"thumbnail" => string,
		 * 	"title" => string,
		 * 	"description" => string,
		 * 	"urls" => array(
		 * 		"mp4" => string,
		 * 		"ogg" => string,
		 * 		"webm" => string
		 * 	)
		 * )
		 * @return array(
		 * 	"mode" => "error",
		 * 	"title" => string,
		 * 	"message" => string
		 * )
		 */
		function fetch($url){
			$matches = array();
			if ($this->handles($url, $matches)){
				$mime_type = $this->get_mime_type($url);
				if ($mime_type === false){
					return $this->error_response("Unable to retrieve the MIME type for the supplied URL.");
				}
				if (!in_array($mime_type, $this->mime_types)){
					return $this->error_response("The MIME type for the supplied URL is not supported.");
				}
				$result = array(
					"mode" => "self-hosted",
					"id" => $matches["name"] . "." . $matches["ext"],
					"thumbnail" => "",
					"title" => $matches["name"],
					"description" => "",
					"urls" => array(
						"mp4" => "",
						"ogg" => "",
						"webm" => ""
					)
				);
				$type = $matches["ext"] === "ogv" ? "ogg" : $matches["ext"];
				$result["urls"][$type] = $url;
				return $result;
			}
			return $this->error_response("The supplied URL is not supported.");
		}

		/**
		 * Takes the supplied URL and retrieves its' MIME type.
		 *
		 * @param string $url The URL to fetch the MIME type for.
		 *
		 * @return bool|string Returns false if the MIME type could not be retrieved.
		 */
		function get_mime_type($url){
			$remote = wp_safe_remote_head($url);
			if (is_wp_error($remote)) {
				return false;
			}
			if ( !empty($remote) && is_array($remote["response"]) && $remote["response"]["code"] === 200 ){
				return wp_remote_retrieve_header($remote, "content-type");
			}
			return false;
		}

	}

}