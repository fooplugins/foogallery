<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 4/17/2018
 * Time: 10:09 PM
 */

if ( !class_exists("FooGallery_Pro_Video_Query") ){

	require_once dirname(__FILE__) . '/class-foogallery-pro-video-base.php';
	require_once dirname(__FILE__) . '/class-foogallery-pro-video-self-hosted.php';
	require_once dirname(__FILE__) . '/class-foogallery-pro-video-oembed.php';
	require_once dirname(__FILE__) . '/class-foogallery-pro-video-youtube.php';
	require_once dirname(__FILE__) . '/class-foogallery-pro-video-vimeo.php';

	class FooGallery_Pro_Video_Query extends FooGallery_Pro_Video_Base {

		public $self_hosted;
		public $oembed;
		public $youtube;
		public $vimeo;

		function __construct() {

			$this->self_hosted = new FooGallery_Pro_Video_Self_Hosted();
			$this->oembed = new FooGallery_Pro_Video_oEmbed();
			$this->youtube = new FooGallery_Pro_Video_YouTube();
			$this->vimeo = new FooGallery_Pro_Video_Vimeo();

			add_action('wp_ajax_fgi_query', array($this, 'ajax'));

		}

		/**
		 * Gets the query arguments from the variables supplied to the script via HTTP POST.
		 *
		 * @return array(
		 * 	"query" => string,
		 * 	"page" => number,
		 * 	"offset" => number,
		 * 	"nonce" => string
		 * )
		 */
		public function get_args() {
			return array(
				"query" => !empty($_POST["query"]) ? trim($_POST["query"]) : null,
				"page" => !empty($_POST["page"]) ? (int)$_POST["page"] : 1,
				"offset" => !empty($_POST["offset"]) ? (int)$_POST["offset"] : 0,
				"nonce" => !empty($_POST["fgi_nonce"]) ? $_POST["fgi_nonce"] : null
			);
		}

		/**
		 * The AJAX handler for the query side of the plugin that returns videos to the front-end so the user can decide what to import.
		 */
		public function ajax() {
			$args = $this->get_args();
			if (wp_verify_nonce($args["nonce"], "fgi_nonce")){
				if (empty($args["query"]) || mb_strlen($args["query"], "UTF-8") < 3) {
					wp_send_json_error("The 'query' argument is required and must be a minimum of 3 characters in length.");
					return;
				}
				$response = $this->handle_query($args["query"], $args["page"], $args["offset"]);
				// regardless of the request all successful queries contain the original query in the response
				$response["query"] = $args["query"];
				wp_send_json_success($response);
			}
			die();
		}

		/**
		 * @param $query
		 * @param int $page
		 * @param int $offset
		 * @return array
		 */
		public function handle_query($query, $page = 1, $offset = 0) {
			// parse the query to test if it is a valid url
			$url = wp_http_validate_url($query);

			// if we have a valid url then try fetch the required data
			if ($url !== false) {
				// handle YouTube specific urls as we parse playlists etc.
				if ($this->youtube->handles($url)) {
					return $this->youtube->fetch($url);
				}
				// handle Vimeo specific urls as we parse albums etc.
				if ($this->vimeo->handles($url)) {
					return $this->vimeo->fetch($url, $page, $offset);
				}
				// check if this is a self hosted video
				if ($this->self_hosted->handles($url)){
					return $this->self_hosted->fetch($url);
				}
				// if we get here let the url be handled by the built in WP_oEmbed class
				return $this->oembed->fetch($url);
			}

			// if we get here the query was a not a url
			return $this->youtube->query($query, $page, $offset);
		}
	}

}