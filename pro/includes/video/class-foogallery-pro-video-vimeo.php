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

				// check if we are dealing with an album
				if (preg_match('/vimeo\.com\/album\/(?<id>[0-9]*?)$/i', $url, $matches)) {
					// for albums the id is the last part of the url
					return $this->fetch_stream("album", $matches["id"], $page, $offset);
				}

				// check if we are dealing with a channel
				if (preg_match('/vimeo\.com\/channels\/(?<id>[0-9]*?)$/i', $url, $matches)) {
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

			// we have as valid an id as we can hope for until we make the actual request so request it
			$url = "https://vimeo.com/api/oembed.json?url=" . urlencode("https://vimeo.com/" . $id);
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

			$endpoint = "https://player.vimeo.com/hubnut/config/%s/%s?page=%d";

			// we have as valid an id as we can hope for until we make the actual request so request it
			$url = sprintf($endpoint, $type, $id, $page);
			// get the json object from the supplied url
			$json = $this->get_json($url);

			// if an error occurred return it
			if ($this->is_error($json)) {
				return $json;
			}

			// do basic validation on the json object
			if (empty($json) || !is_object($stream = $json->stream) || !is_array($stream->clips)) {
				return $this->error_response("Invalid response.");
			}

			// if we get here we need to build a response to return to the frontend
			$response = array(
				"mode" => $type,
				"id" => $id,
				"total" => $stream->total_clips,
				"offset" => $offset,
				"page" => $page,
				"nextPage" => 0,
				"videos" => array()
			);

			$has_valid = false;
			// iterate each of the returned videos and add them to the response in our desired format
			foreach ($stream->clips as $video) {
				if (!empty($video->thumbnail) && !empty($video->id) && !empty($video->title)) {
					if (!$has_valid) $has_valid = true;
					$response["videos"][] = array(
						"provider" => "vimeo",
						"id" => $video->id,
						"url" => "https://player.vimeo.com/video/" . $video->id,
						"thumbnail" => $video->thumbnail,
						"title" => $video->title,
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


	}

}