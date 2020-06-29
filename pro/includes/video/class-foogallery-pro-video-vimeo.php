<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 4/17/2018
 * Time: 10:09 PM
 */

if ( !class_exists("FooGallery_Pro_Video_Vimeo") ){

	require_once dirname(__FILE__) . '/class-foogallery-pro-video-base.php';

	class FooGallery_Pro_Video_Vimeo extends FooGallery_Pro_Video_Base {

		// region Properties

		/**
		 * The regular expression used to match a Vimeo video URL.
		 * @var string
		 */
		public $regex_pattern;

		// endregion

		function __construct() {
			$this->regex_pattern = '/(player\.)?vimeo\.com/i';

			add_action('wp_ajax_fgi_save', array($this, 'ajax'));
		}

		public function get_args(){
			return array(
				"access_token" => !empty($_POST["access_token"]) ? trim($_POST["access_token"]) : null,
				"nonce" => !empty($_POST["fgi_nonce"]) ? $_POST["fgi_nonce"] : null
			);
		}

		/**
		 * Save the Vimeo Access Token to the foogallery settings
		 */
		public function ajax() {

			$args = $this->get_args();
			if (wp_verify_nonce($args["nonce"], "fgi_nonce")){
				if (empty($args["access_token"]) || mb_strlen($args["access_token"], "UTF-8") < 30) {
					wp_send_json_error("The 'access_token' argument is required and must be a minimum of 30 characters in length.");
					return;
				}

				$response = $this->verify_token($args["access_token"]);

				if ($response["mode"] == "verified"){
					foogallery_settings_set_vimeo_access_token($args["access_token"]);
				}
				wp_send_json_success($response);
			}
			die();
		}

		public function verify_token($token){

			$remote = wp_remote_get("https://api.vimeo.com/oauth/verify", array(
				"headers" => array(
					"Authorization" => "Bearer " . $token
				)
			));

			if (is_wp_error($remote)) {
				return $this->error_response("Error validating token: " . $remote->get_error_message());
			}

			if (wp_remote_retrieve_response_code($remote) == "401"){
				return $this->error_response("Invalid token supplied unable to verify.");
			}

			return array(
				"mode" => "verified",
				"message" => __( 'Verified access token.' , 'foogallery' )
			);
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
		 * Takes the supplied Vimeo url and attempts to fetch its' data.
		 *
		 * @description At present this method supports the following url patterns:
		 *
		 * Single video urls
		 *
		 * - http(s)://vimeo.com/[VIDEO_ID]
		 * - http(s)://vimeo.com/album/[ALBUM_ID]/video/[VIDEO_ID]
		 * - http(s)://vimeo.com/channels/[CHANNEL_ID]/[VIDEO_ID]
		 *
		 * User urls
		 *
		 * - http(s)://vimeo.com/[USER_ID]
		 * - http(s)://vimeo.com/[USER_ID]/videos
		 *
		 * Album url
		 *
		 * - http(s)://vimeo.com/album/[ALBUM_ID]
		 *
		 * @param string $url The url to fetch the data for.
		 * @param int [$page=1] If this is a album or user url the page number could also be supplied.
		 * @param int [$offset=0] The number of items already retrieved for the query.
		 * @return array
		 */
		public function fetch($url, $page = 1, $offset = 0) {
			if ($this->handles($url)) {
				$matches = array();

				// check if we are dealing with an album/showcase
				if (preg_match('/vimeo\.com\/(album|showcase)\/(?<id>[0-9]*?)$/i', $url, $matches)) {
					// for albums the id is the last part of the url
					return $this->fetch_stream("album", $matches["id"], $page, $offset);
				}

				// check if we are dealing with a channel
				if (preg_match('/vimeo\.com\/channels\/(?<id>[a-zA-Z0-9]*?)$/i', $url, $matches)) {
					// for albums the id is the last part of the url
					return $this->fetch_stream("channel", $matches["id"], $page, $offset);
				}

				// check if we are dealing with a video
				if (preg_match('/vimeo\.com\/(?:.*?\/)?(?<id>[0-9]*?)(?:\/)?$/i', $url, $matches)) {
					return $this->fetch_video($matches["id"]);
				}

				// check if we are dealing with a user url
				if (preg_match('/vimeo\.com\/(?<id>[a-zA-Z0-9]*?)(?:\/)?$/i', $url, $matches) || preg_match('/vimeo\.com\/(?<id>[a-zA-Z0-9]*?)\/videos(?:\/)?$/i', $url, $matches)) {
					return $this->fetch_stream("user", $matches["id"], $page, $offset);
				}
			}
			return $this->error_response("Unrecognized Vimeo url.");
		}

		public function fetch_video($id) {
			if (!is_numeric($id)) {
				return $this->error_response("Invalid video id supplied.");
			}

			$vimeo_url = apply_filters( 'foogallery_video_json_get_vimeo_url', 'https://vimeo.com/api/oembed.json?width=1920&height=1080&url=' );

			// we have as valid an id as we can hope for until we make the actual request so request it
			$url =  $vimeo_url . urlencode( 'https://vimeo.com/' . $id );

			//filter the args so we can change per site if needed
			$args = apply_filters( 'foogallery_video_json_get_vimeo', array( "headers" => array(  "Referer" => get_site_url() ) ) );

			// get the json object from the supplied url
			$json = $this->json_get( $url, $args );

			// if an error occurred return it
			if ($this->is_error($json)) {
				return $json;
			}

			// do basic validation on the parsed object
			if (empty($json) || empty($json->thumbnail_url) || empty($json->title)) {
				//check if the video has been set to private
				if ( !empty($json->domain_status_code) ) {
					if ( 403 === $json->domain_status_code ) {
						return $this->error_response("The privacy settings for the video do not allow embedding on this website. Please change the video's embedding privacy setting.");
					}
				}

				return $this->error_response("No video was returned in the Vimeo API response.");
			}

			$response = array(
				"mode" => "single",
				"videos" => array()
			);

			$response["videos"][] = array(
				"provider" => "vimeo",
				"id" => $id,
				"url" => "https://player.vimeo.com/video/" . $id,
				"thumbnail" => $json->thumbnail_url,
				"title" => $json->title,
				"description" => !empty($json->description) ? $json->description : ""
			);

			return $response;
		}

		public function fetch_stream($type, $id, $page = 1, $offset = 0) {
			$supports = array("album", "channel", "user");

			if (empty($type) || !is_string($type) || !in_array($type, $supports)) {
				return $this->error_response("Invalid type supplied.");
			}

			if (empty($id)) {
				return $this->error_response("Invalid id supplied.");
			}

			$access_token = foogallery_settings_get_vimeo_access_token();
			if (empty($access_token)){
				return array(
					"mode" => "vimeo",
					"access_token" => ""
				);
			}

			$endpoint = "https://api.vimeo.com/{$type}s/{$id}/videos?page={$page}&fields=uri,name,description,pictures.sizes.width,pictures.sizes.link";

			// get the json object from the supplied url
			$json = $this->json_get($endpoint, array(
				"headers" => array(
					"Authorization" => "Bearer " . $access_token
				)
			));

			// if an error occurred return it
			if ($this->is_error($json)) {
				return $json;
			}

			// do basic validation on the json object
			if (empty($json) || !is_array($json->data)) {
				return $this->error_response("Invalid response.");
			}

			// if we get here we need to build a response to return to the frontend
			$response = array(
				"mode" => $type,
				"id" => $id,
				"total" => $json->total,
				"offset" => $offset,
				"page" => $page,
				"nextPage" => 0,
				"videos" => array()
			);

			$has_valid = false;
			// iterate each of the returned videos and add them to the response in our desired format
			foreach ($json->data as $video) {
				$video_id = $this->get_video_id($video);
				$thumbnail = $this->get_video_thumbnail($video);
				if (!empty($video_id) && !empty($thumbnail) && !empty($video->name)) {
					if (!$has_valid) $has_valid = true;
					$response["videos"][] = array(
						"provider" => "vimeo",
						"id" => $video_id,
						"url" => "https://player.vimeo.com/video/" . $video_id,
						"thumbnail" => $thumbnail,
						"title" => $video->name,
						"description" => !empty($video->description) ? $video->description : ""
					);
				}
			}

			if (!$has_valid) {
				return $this->error_response("No valid videos in response.");
			}

			$response["offset"] = $response["offset"] + count($response["videos"]);
			if ($response["total"] > $response["offset"]){
				$response["nextPage"] = $response["page"] + 1;
			}

			return $response;
		}

		private function get_video_id($video){
			if (!is_object($video) || !is_string($video->uri)) return false;
			$exploded = explode("/", $video->uri);
			return $exploded[count($exploded) - 1];
		}

		private function get_video_thumbnail($video){
			if (!is_object($video) || !is_object($video->pictures) || !is_array($sizes = $video->pictures->sizes)) return false;
			$image = false;
			foreach ($sizes as $size){
				if ($image == false || $size->width > $image->width ){
					$image = $size;
				}
			}
			if ($image == false || !is_string($image->link)) return false;
			return $image->link;
		}
	}

}