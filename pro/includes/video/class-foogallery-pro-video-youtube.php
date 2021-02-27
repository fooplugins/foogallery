<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 4/17/2018
 * Time: 10:09 PM
 */

if ( !class_exists("FooGallery_Pro_Video_YouTube") ){

	require_once dirname(__FILE__) . '/class-foogallery-pro-video-base.php';

	class FooGallery_Pro_Video_YouTube extends FooGallery_Pro_Video_Base {

		// region Properties

		/**
		 * The regular expression used to match a YouTube video URL.
		 * @var string
		 */
		public $regex_pattern;

		// endregion

		function __construct() {
			$this->regex_pattern = '/(www\.)?youtube|youtu\.be/i';

			add_action('wp_ajax_fgi_save_youtube_api_key', array($this, 'ajax'));
		}

		public function get_args(){
			return array(
				"api_key" => !empty($_POST["api_key"]) ? trim($_POST["api_key"]) : null,
				"nonce" => !empty($_POST["fgi_nonce"]) ? $_POST["fgi_nonce"] : null
			);
		}

		public function ajax(){

			$args = $this->get_args();
			if (wp_verify_nonce($args["nonce"], "fgi_nonce")){
				if (empty($args["api_key"]) || mb_strlen($args["api_key"], "UTF-8") < 30) {
					wp_send_json_error("The 'api_key' argument is required and must be a minimum of 30 characters in length.");
					return;
				}

				$response = $this->verify_api_key($args["api_key"]);

				if ($response["mode"] == "verified"){
					foogallery_settings_set_youtube_api_key($args["api_key"]);
				}
				wp_send_json_success($response);
			}
			die();

		}

		public function verify_api_key($key){

			$remote = wp_remote_get("https://youtube.googleapis.com/youtube/v3/videos?part=id&id=Ks-_Mh1QhMc&key=" . $key);

			if (is_wp_error($remote)) {
				return $this->error_response("Error verifying api key: " . $remote->get_error_message());
			}

			if (wp_remote_retrieve_response_code($remote) !== 200){
				return $this->error_response("Unable to verify api key.");
			}

			return array(
				"mode" => "verified",
				"message" => __( 'Verified api key.' , 'foogallery' )
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
		 * Takes a string value and determines whether or not it could possibly be a YouTube video id.
		 *
		 * @param string $value The value to test.
		 * @return bool
		 *
		 * @see https://stackoverflow.com/questions/6180138/whats-the-maximum-length-of-a-youtube-video-id
		 *
		 * @description At present YouTube's video ids always have 11 characters, while this is not
		 * guaranteed it should stay that way until all 73,786,976,294,838,206,464 possible combinations
		 * are exhausted.
		 */
		function possible_video($value) {
			return !empty($value) && is_string($value) && strlen($value) === 11 && strpos($value, " ") === false;
		}

		function possible_playlist($value) {
			return !empty($value) && is_string($value) && strlen($value) >= 11 && strpos($value, " ") === false && (strncmp($value, "PL", 2) === 0 || strncmp($value, "R", 1) === 0);
		}

		/**
		 * Takes the supplied query and optional page number, determines the correct method to call and then returns its' data.
		 *
		 * @param string $query The query value to parse.
		 * @param int [$page=1] If this is a search query the page number could also be supplied.
		 * @param int [$offset=0] The number of items already retrieved for the query.
		 * @return array
		 */
		function query($query, $page = 1, $offset = 0) {

			if ($this->possible_video($query)){
				$response = $this->fetch_video($query);
				if ($response["mode"] !== "error") {
					return $response;
				}
			}

			if ($this->possible_playlist($query)){
				$response = $this->fetch_stream("playlistItems", $query, $page, $offset);
				if ($response["mode"] !== "error") {
					return $response;
				}
			}

			// if we get here we assume the query is a search
			return $this->fetch_stream("search", $query, $page, $offset);
		}

		/**
		 * Takes the supplied YouTube url and attempts to fetch its' data.
		 *
		 * @description At present this method supports the following url patterns:
		 *
		 * - http(s)://www.youtube.com/watch?v=[ID]
		 * - http(s)://youtu.be/[ID]
		 * - http(s)://www.youtube.com/embed/[ID]
		 * - http(s)://www.youtube.com/playlist?list=[ID]
		 *
		 * @param string $url The url to fetch the data for.
		 * @return array
		 */
		function fetch($url, $page, $offset) {
			// make sure we're dealing with a YouTube url in case this method is called externally
			if (preg_match($this->regex_pattern, $url)) {
				$query_string = array();
				$url_parts = parse_url($url);
				// check if we were supplied a query string i.e. anything after ?
				if (!empty($url_parts["query"])) {
					// if we have a query string then parse it into an array of key value pairs
					parse_str($url_parts["query"], $query_string);
				}

				// check if we are dealing with a playlist url
				if (preg_match('/(www\.)?youtube\.com\/playlist/i', $url)) {
					return $this->fetch_stream("playlistItems", $query_string["list"], $page, $offset);
				}

				// otherwise we are dealing with one of the single video supported formats
				$id = $query_string["v"];
				$list = $query_string["list"];
				// if the id does not exist in the query string then we are dealing with a YouTube
				// short or embed url so grab the id from the last part of the url
				if (empty($id) && preg_match('/(www\.)?youtube\.com\/embed|youtu\.be/i', $url)) {
					// here we split the url on all forward-slashes
					$parts = explode("/", $url_parts["path"]);
					// then grab the last part to use as the id
					$id = end($parts);
				}

				if ($this->possible_video($id)){
					return $this->fetch_video($id);
				}

				if (!empty($list)){
					return $this->fetch_stream("playlistItems", $query_string["list"], $page, $offset);
				}

			}
			return $this->error_response("Unrecognized YouTube url.");
		}

		/**
		 * Takes the supplied YouTube video id and fetches its' data.
		 *
		 * @param string $id The video id to fetch.
		 * @return array(
		 *  "mode" => "video",
		 *  "url" => string,
		 *  "thumbnail" => string,
		 *  "title" => string,
		 *  "description" => string
		 * )
		 * @return array(
		 *  "mode" => "error",
		 *  "message" => string
		 * )
		 */
		function fetch_video($id) {

			if (empty($id) || !is_string($id)) {
				return $this->error_response("Invalid id supplied.");
			}

			// we have as valid an id as we can hope for until we make the actual request so request it
			$api_key = foogallery_settings_get_youtube_api_key();
			if (!empty($api_key)){
				$api_response = $this->fetch_stream("videos", $id, 1, 0);
				if ($api_response["mode"] === "videos"){
					return array(
						"mode" => "single",
						"videos" => $api_response["videos"]
					);
				} else {
					return $api_response;
				}
			} else {
				$url = "https://www.youtube.com/oembed?url=" . urlencode("https://www.youtube.com/watch?v=" . $id);
			}

			// get the json object from the supplied url
			$json = $this->json_get($url);

			// if an error occurred return it
			if ($this->is_error($json)) {
				return $json;
			}

			// do basic validation on the parsed object
			if (empty($json) || empty($json->thumbnail_url) || empty($json->title)) {
				return $this->error_response("No video in response.");
			}

			$response = array(
				"mode" => "single",
				"videos" => array()
			);

			$response["videos"][] = array(
				"provider" => "youtube",
				"id" => $id,
				"url" => "https://www.youtube.com/embed/" . $id,
				"thumbnail" => $this->get_oembed_video_thumbnail($id, $json->thumbnail_url),
				"title" => $json->title,
				"description" => !empty($json->description) ? $json->description : ""
			);

			return $response;
		}

		function fetch_stream($type, $value, $page, $offset) {
			$supports = array("videos","playlistItems","search");

			if (empty($type) || !is_string($type) || !in_array($type, $supports)) {
				return $this->error_response("Invalid type supplied.");
			}

			if (empty($value)) {
				return $this->error_response("Invalid value supplied.");
			}

			$api_key = foogallery_settings_get_youtube_api_key();
			if (empty($api_key)){
				return array(
					"mode" => "youtube",
					"api_key" => ""
				);
			}

			$page_size = 25;
			$type_param = false;
			switch ($type){
				case "videos":
					$type_param = "id=" . $value;
					break;
				case "playlistItems":
					$type_param = "playlistId=" . $value;
					break;
				case "search":
					$type_param = "type=video&q=" . $value;
					break;
			}

			if (empty($type_param)) {
				return $this->error_response("Unable to create type parameter.");
			}

			// we have as valid an id as we can hope for until we make the actual request so request it
			if (is_string($page)){
				$url = "https://youtube.googleapis.com/youtube/v3/{$type}?key={$api_key}&part=snippet&maxResults={$page_size}&pageToken={$page}&{$type_param}";
			} else {
				$url = "https://youtube.googleapis.com/youtube/v3/{$type}?key={$api_key}&part=snippet&maxResults={$page_size}&{$type_param}";
			}

			// get the json object from the supplied url
			$json = $this->json_get($url);

			// if an error occurred return it
			if ($this->is_error($json)) {
				return $json;
			}

			// do basic validation on the json object
			if (empty($json) || empty($json->items) || !is_array($json->items)) {
				return $this->error_response("No videos in response.");
			}

			// if we get here we need to build a response to return to the frontend
			$response = array(
				"mode" => $type,
				"total" => 0,
				"page" => $page,
				"nextPage" => 0,
				"offset" => $offset,
				"videos" => array()
			);

			if ($type !== "search"){
				$response["id"] = $value;
			}

			// iterate each of the returned videos and add them to the response in our desired format
			foreach ($json->items as $item) {
				if (is_object($item) && property_exists($item, "snippet") && is_object($item->snippet)){
					$snippet = $item->snippet;
					if (property_exists($snippet, "thumbnails") && is_object($snippet->thumbnails)){

						$video_id = $this->get_video_id($type, $item);
						$thumbnail = $this->get_video_thumbnail($snippet->thumbnails);

						if (!empty($video_id) && !empty($thumbnail) && !empty($snippet->title)) {
							$response["videos"][] = array(
								"provider" => "youtube",
								"id" => $video_id,
								"url" => "https://www.youtube.com/embed/" . $video_id,
								"thumbnail" => $thumbnail,
								"title" => $snippet->title,
								"description" => !empty($snippet->description) ? $snippet->description : ""
							);
						}
					}
				}
			}

			// update the offset and total with the current video count

			if (property_exists($json, "pageInfo") && is_object($json->pageInfo)
			    && property_exists($json->pageInfo, "totalResults") && is_numeric($json->pageInfo->totalResults)){
				$response["offset"] = $response["offset"] + count($response["videos"]);
				$response["total"] = $json->pageInfo->totalResults;
				if (property_exists($json, "nextPageToken")){
					$response["nextPage"] = $json->nextPageToken;
				}
			} else {
				$response["offset"] = $response["total"] = count($response["videos"]);
			}

			// if we have no videos then none were valid
			if ($response["total"] === 0) {
				return $this->error_response("No valid videos in response.");
			}

			return $response;
		}

		private function get_video_id($type, $item){
			if ($type === "videos"
			    && property_exists($item, "id") && is_string($item->id)){
				return $item->id;
			}
			if ($type === "playlistItems"
			    && property_exists($item->snippet, "resourceId") && is_object($item->snippet->resourceId)
			    && property_exists($item->snippet->resourceId, "kind") && $item->snippet->resourceId->kind === "youtube#video"
			    && property_exists($item->snippet->resourceId, "videoId")){
				return $item->snippet->resourceId->videoId;
			}
			if ($type === "search"
			    && property_exists($item, "id") && is_object($item->id)
			    && property_exists($item->id, "kind") && $item->id->kind === "youtube#video"
			    && property_exists($item->id, "videoId")){
				return $item->id->videoId;
			}
			return false;
		}

		private function get_video_thumbnail($thumbnails){
			if (!is_object($thumbnails)) return false;
			if (property_exists($thumbnails, "maxres") && is_object($thumbnails->maxres)
			    && property_exists($thumbnails->maxres, "url") && is_string($thumbnails->maxres->url)){
				return $thumbnails->maxres->url;
			}
			if (property_exists($thumbnails, "standard") && is_object($thumbnails->standard)
			    && property_exists($thumbnails->standard, "url") && is_string($thumbnails->standard->url)){
				return $thumbnails->standard->url;
			}
			if (property_exists($thumbnails, "high") && is_object($thumbnails->high)
			    && property_exists($thumbnails->high, "url") && is_string($thumbnails->high->url)){
				return $thumbnails->high->url;
			}
			if (property_exists($thumbnails, "medium") && is_object($thumbnails->medium)
			    && property_exists($thumbnails->medium, "url") && is_string($thumbnails->medium->url)){
				return $thumbnails->medium->url;
			}
			if (property_exists($thumbnails, "default") && is_object($thumbnails->default)
			    && property_exists($thumbnails->default, "url") && is_string($thumbnails->default->url)){
				return $thumbnails->default->url;
			}
			return false;
		}

		private function get_oembed_video_thumbnail($id, $default){
			$format = "https://img.youtube.com/vi/%1s/%2s.jpg";
			/**
			 * Possible filenames for images, in order of desirability. Should only ever use first one.
			 * @see https://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
			 */
			$sizes = array(
				'maxresdefault',
				'hqdefault',
				'sddefault',
				'default',
				'0'
			);
			foreach ( $sizes as $size ) {
				$url = sprintf( $format, $id, $size );
				if ( $this->url_exists( $url ) ) {
					return $url;
				}
			}
			return $default;
		}
	}

}