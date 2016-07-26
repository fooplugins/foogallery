<?php
/**
 * Base class for all crawlers used in FooGallery Sharing
 *
 */

if ( ! class_exists( 'FooGallery_Sharing_Crawler' ) ) {

	class FooGallery_Sharing_Crawler {
		protected $ua_regex; //user agent regex
		public $options;

		public static function create( $network, $options ) {
			$class = 'FooShare_Crawlers_' . FooShare_Plugin::slug_to_class( $network->name );

			return class_exists( $class ) ? new $class( $network, $options ) : null;
		}

		function __construct( $crawler_specific_options ) {
			$this->options = (object) wp_parse_args( $crawler_specific_options, foogallery_sharing_crawler_defaults() );
		}

		public function handles_request() {
			// first check if the ua_regex option is set and the current request's HTTP_USER_AGENT string matches it.
			if ( isset( $this->ua_regex ) && preg_match( $this->ua_regex, $_SERVER['HTTP_USER_AGENT'] ) ) {
				// then check if an id can be extracted from the request
				$id = $this->get_request_id();
				if (is_numeric( $id )){
					do_action( 'foogallery_sharing_crawler_handle_request', $id, $this );
					return true;
				}
			}

			return false;
		}

		public function get_request_id() {
			$id = $_GET[ foogallery_sharing_param() ];
			if ( isset( $id ) ) {
				return base_convert( $id, 36, 10 );
			}

			return null;
		}

		public function map_meta_props( $share ) {
			return (object) array(
				$share->content_type => $share->content_url,
				'title'              => $share->title,
				'description'        => $share->description
			);
		}

		public function send_response( $share ) {
			$mapped = $this->map_meta_props( $share );
			status_header( 200 );
			if (FooShare_Plugin::DEBUG){
				nocache_headers();
			}
			$this->doctype($mapped);
			$this->begin_html($mapped);
			$this->begin_head($mapped);
			$this->title($mapped);
			$this->head($mapped);
			$this->end_head($mapped);
			$this->begin_body($mapped);
			$this->body($mapped);
			$this->end_body($mapped);
			$this->end_html($mapped);
			exit;
		}

		public function doctype($mapped){
			echo '<!DOCTYPE html>';
		}

		public function title($mapped){
			echo '<title>' . $mapped->title . '</title>';
		}

		public function begin_html($mapped){
			echo '<html prefix="og: http://ogp.me/ns#">';
		}

		public function end_html($mapped){
			echo '</html>';
		}

		public function begin_head($mapped){
			echo '<head>';
		}

		public function head($mapped){
			foreach ( $this->options->meta_tags as $prop => $tags ) {
				foreach ( $tags as $tag => $names ) {
					foreach ( $names as $name ) {
						if ( isset( $mapped->$name ) ) {
							echo sprintf( '<meta %s="%s:%s" content="%s">', $prop, $tag, $name, $mapped->$name );
						}
					}
				}
			}
		}

		public function end_head($mapped){
			echo '</head>';
		}

		public function begin_body($mapped){
			echo '<body>';
		}

		public function body($mapped){
		}

		public function end_body($mapped){
			echo '</body>';
		}
	}

}