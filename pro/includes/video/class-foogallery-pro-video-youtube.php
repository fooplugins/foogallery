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
		 * Takes a string value and determines whether or not it is a YouTube video id.
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
		function is_video_id($value) {
			return !empty($value) && is_string($value) && strlen($value) === 11 && strpos($value, " ") === false && $this->url_exists("https://www.youtube.com/watch?v=" . $value);
		}

		/**
		 * Takes a string value and determines whether or not it is a YouTube playlist id.
		 *
		 * @param string $value The value to test.
		 * @return bool
		 *
		 * @description At present YouTube's playlist ids always have 34 characters and begin with "PL", while
		 * this is not guaranteed it should stay that way until all possible combinations are exhausted.
		 */
		function is_playlist_id($value) {
			return !empty($value) && is_string($value) && strlen($value) === 34 && strpos($value, " ") === false && $this->url_exists("https://www.youtube.com/playlist?list=" . $value);
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
			if ($this->is_playlist_id($query)) {
				return $this->fetch_playlist($query);
			}

			if ($this->is_video_id($query)) {
				return $this->fetch_video($query);
			}

			// if we get here we assume the query is a search
			return $this->search($query, $page, $offset);
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
		function fetch($url) {
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
					return $this->fetch_playlist($query_string["list"]);
				}

				// otherwise we are dealing with one of the single video supported formats
				$id = $query_string["v"];
				// if the id does not exist in the query string then we are dealing with a YouTube
				// short or embed url so grab the id from the last part of the url
				if (empty($id) && preg_match('/(www\.)?youtube\.com\/embed|youtu\.be/i', $url)) {
					// here we split the url on all forward-slashes
					$parts = explode("/", $url);
					// then grab the last part to use as the id
					$id = end($parts);
				}

				return $this->fetch_video($id);
			}
			return $this->error_response("Unrecognized YouTube url.");
		}

		/**
		 * Takes the supplied YouTube playlist id and fetches its' data.
		 *
		 * @param string $id The playlist id to fetch.
		 * @return array(
		 *  "mode" => "playlist",
		 *  "thumbnail" => string,
		 *  "title" => string,
		 *  "description" => string,
		 *  "videos" => array,
		 *  "total" => number
		 * )
		 * @return array(
		 *  "mode" => "error",
		 *  "message" => string
		 * )
		 */
		function fetch_playlist($id) {

			if (!$this->is_playlist_id($id)) {
				return $this->error_response("Invalid playlist id supplied.");
			}

			// we have as valid an id as we can hope for until we make the actual request so request it
			$url = "https://www.youtube.com/list_ajax?style=json&action_get_list=true&list=" . $id;
			// get the json object from the supplied url
			$json = $this->get_json($url);

			// if an error occurred return it
			if ($this->is_error($json)) {
				return $json;
			}

			// do basic validation on the json object
			if (empty($json) || empty($json->video) || !is_array($json->video)) {
				return $this->error_response("No videos in response.");
			}

			// if we get here we need to build a response to return to the frontend
			$response = array(
				"mode" => "playlist",
				"id" => $id,
				"total" => 0,
				"page" => 1,
				"nextPage" => 0,
				"offset" => 0,
				"videos" => array()
			);

			// iterate each of the returned videos and add them to the response in our desired format
			foreach ($json->video as $video) {
				if (!empty($video->thumbnail) && !empty($video->encrypted_id) && !empty($video->title)) {
					$response["videos"][] = array(
						"provider" => "youtube",
						"id" => $video->encrypted_id,
						"url" => "https://www.youtube.com/embed/" . $video->encrypted_id,
						"thumbnail" => $video->thumbnail,
						"title" => $video->title,
						"description" => !empty($video->description) ? $video->description : ""
					);
				}
			}

			// update the offset and total with the current video count
			$response["offset"] = $response["total"] = count($response["videos"]);

			// if we have no videos then none were valid
			if ($response["total"] === 0) {
				return $this->error_response("No valid videos in response.");
			}

			return $response;
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

			if (!$this->is_video_id($id)) {
				return $this->error_response("Invalid video id supplied.");
			}

			// we have as valid an id as we can hope for until we make the actual request so request it
			$url = "http://www.youtube.com/oembed?url=" . urlencode("https://www.youtube.com/watch?v=" . $id);
			// get the json object from the supplied url
			$json = $this->get_json($url);

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
				"thumbnail" => $json->thumbnail_url,
				"title" => $json->title,
				"description" => !empty($json->description) ? $json->description : ""
			);

			return $response;
		}

		/**
		 * Takes the supplied query and optional page number and performs a YouTube search.
		 *
		 * @param string $query The query to use as a search term.
		 * @param int [$page=1] The page number to retrieve.
		 * @param int [$offset=0] The number of items already retrieved for the query.
		 * @return array(
		 *  "mode" => "search",
		 *  "total" => number,
		 *  "page" => number,
		 *  "offset" => number,
		 *  "nextPage" => number,
		 *  "videos" => array(
		 * 		array(
		 * 			"provider" => "youtube",
		 * 			"id" => string,
		 * 			"url" => string,
		 * 			"thumbnail" => string,
		 * 			"title" => string,
		 * 			"description" => string
		 * 		)
		 * 	)
		 * )
		 * @return array(
		 *  "mode" => "error",
		 *  "title" => string,
		 *  "message" => string
		 * )
		 */
		function search($query, $page = 1, $offset = 0) {
			if (empty($query)) {
				return $this->error_response("Empty search query.");
			}

			if ($query === "!ERROR"){
				return $this->error_response("Dummy error.");
			}

			$url = "https://www.youtube.com/search_ajax?style=json&search_query=" . urlencode($query) . "&page=" . $page;
			// get the json object from the supplied url
			$json = $this->get_json($url);

			// if an error occurred return it
			if ($this->is_error($json)) {
				return $json;
			}

			// do basic validation on the parsed object
			if (empty($json) || empty($json->hits) || empty($json->video) || !is_array($json->video)) {
				return $this->error_response("No videos in response.");
			}

			// if we get here we need to build a response to return to the frontend
			$response = array(
				"mode" => "search",
				"total" => $json->hits,
				"page" => $page,
				"offset" => $offset,
				"nextPage" => 0,
				"videos" => array()
			);

			// iterate each of the returned videos and add them to the response in our desired format
			foreach ($json->video as $video) {
				if (!empty($video->thumbnail) && !empty($video->encrypted_id) && !empty($video->title)) {
					$response["videos"][] = array(
						"provider" => "youtube",
						"id" => $video->encrypted_id,
						"url" => "https://www.youtube.com/embed/" . $video->encrypted_id,
						"thumbnail" => $video->thumbnail,
						"title" => $video->title,
						"description" => !empty($video->description) ? $video->description : ""
					);
				}
			}

			// if we have no videos then none of the videos were valid
			if ($response["total"] === 0) {
				return $this->error_response("No valid videos in response.");
			}

			$response["offset"] = $response["offset"] + count($response["videos"]);
			if ($response["total"] > $response["offset"]){
				$response["nextPage"] = $response["page"] + 1;
			}

			return $response;
		}

	}

}