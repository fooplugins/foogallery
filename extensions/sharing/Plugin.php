<?php
/*
Plugin Name: FooShare
Plugin URI: http://wordpress.org/plugins/fooshare/
Description: This is a plugin which exposes some endpoints to make sharing to social sites simpler.
Author: Steve Usher & Brad Vincent
Version: 0.1
*/
if ( ! class_exists( 'FooShare_Plugin' ) ) {

	define( 'FOOSHARE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	require_once FOOSHARE_PLUGIN_PATH . 'Database.php';
	require_once FOOSHARE_PLUGIN_PATH . 'Crawler.php';
	// load all crawlers
	foreach ( glob( FOOSHARE_PLUGIN_PATH . 'crawlers/*.php' ) as $file_name ) {
		require_once $file_name;
	}
	require_once FOOSHARE_PLUGIN_PATH . 'Network.php';
	// load all networks
	foreach ( glob( FOOSHARE_PLUGIN_PATH . 'networks/*.php' ) as $file_name ) {
		require_once $file_name;
	}

	class FooShare_Plugin {

		const DEBUG = true; // when enabled you can pass "?crawler=(facebook|google)&_escaped_fragment_=foo/ID" to test crawler output.
		const VERSION = '0.0.1';
		const NAME = 'FooShare';
		const OPTIONS_NAME = 'fooshare_options';

		public $options;
		public $networks;
		public $db;

		function __construct( $options ) {

			$this->options = (object) wp_parse_args( $options, array(
				'args' => (object) array(
					'key' => '__foo',
					'required' => array( 'network', 'content_url', 'content_type' ),
					'optional' => array( 'hash', 'title', 'description', 'post_id' )
				),
				'param' => 'fooshare',
				'networks' => (object) array(
					'facebook',
					'twitter',
					'google',
					'reddit',
					'buffer',
					'delicious',
					'digg',
					'linked_in',
					'pinterest',
					'stumble_upon',
					'tumblr'
				),
				'defaults' => (object) array(
					'network' => array(
						'enabled' => true,
						'url_format' => null,
						'crawler' => null
					),
					'crawler' => array(
						'meta_tags' => array(),
						'ua_regex'  => null
					)
				)
			) );
			// load the networks
			$this->networks = (object) array();
			foreach ( $this->options->networks as $name => $value ) {
				if ( is_integer( $name ) ) {
					$name  = $value;
					$value = array();
				}
				$this->networks->$name = FooShare_Network::create( $this, $name, $value );
			}
			$this->db = new FooShare_Database($this);

			add_action( 'init', array( $this, 'sniff' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_resources' ) );
			add_filter( 'the_content', array( $this, 'add_content' ), 99 );
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
		}

		/**
		 * On plugin activation in the wp_admin.
		 */
		public function activate() {
			$this->db->create_table();
		}

		/**
		 * When the user removes the plugin completely from wp_admin.
		 */
		static function uninstall() {
			FooShare_Database::drop_table();
		}

		/**
		 * Enqueue client side resources (JS, CSS)
		 */
		public function enqueue_resources() {
			wp_enqueue_style( 'fooshare_css', plugins_url( 'assets/css/fooshare.css', __FILE__ ), array(), self::VERSION );
			wp_enqueue_script( 'fooshare_js', plugins_url( 'assets/js/fooshare.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_localize_script( 'fooshare_js', 'FOOSHARE', array(
				'key'      => $this->options->args->key,
				'networks' => $this->enabled_networks()
			) );
		}

		/**
		 * Adds a hidden input with the post ID to all pages.
		 * @param $content
		 *
		 * @return string
		 */
		public function add_content($content){
			global $post;
			return '<input class="fooshare_post_id" type="hidden" value="' . $post->ID . '"/>' . $content;
		}

		/**
		 * Sniffs all get requests checking for supported crawlers and all post requests checking for new shares.
		 */
		public function sniff() {
			if ( $this->is_crawler( $network, $id ) ) {
				if ( $this->db->fetch( $id, $share ) ) {
					$network->crawler->send_response( $share );
				}
			}
			if ( $this->is_handled( $args ) ) {
				if ( $this->db->save( $args, $share ) ) {
					$network = $this->networks->{$args->network};
					if ( isset( $network ) ) {

						$url = foogallery_sharing_url_for_sharing( $network, $share );

						$this->send_redirect($network->get_url($share));
					} else {
						$this->send_status( 400, 'Bad Request' );
					}
				} else {
					$this->send_status( 500, 'Database Error' );
				}
			}
			if ( $this->is_redirect( $share ) ) {
				$this->send_redirect( $share->redirect_url );
			}
		}

		/**
		 * Checks if the current request is a supported web crawler. If true the supplied parameter $network is set, you
		 * can access all supplied options for the network from this object.
		 *
		 * @param $network
		 *
		 * @param $id
		 *
		 * @return bool
		 */
		private function is_crawler( &$network, &$id ) {
			if ( strtolower( $_SERVER['REQUEST_METHOD'] ) === 'get' ) { // crawlers only make GET requests
				if ( self::DEBUG && isset( $_GET['crawler'] ) ) {
					return $this->is_crawler_debug( $network, $id );
				}
				foreach ( $this->networks as $name => $n ) {
					if ( $n->has_crawler() && $n->crawler->handles_request( $id ) ) {
						$network = $n;

						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Used if the self::DEBUG flag is set to true and there is a "crawler" query param.
		 * e.g. ?crawler=[TYPE]&_escaped_fragment_=foo/[ID]
		 *
		 * @param $network
		 *
		 * @param $id
		 *
		 * @return bool
		 */
		private function is_crawler_debug( &$network, &$id ) {
			$name = $_GET['crawler'];
			if ( isset( $this->networks->$name ) ) {
				$n = $this->networks->$name;
				if ( $n->has_crawler() ) {
					$tmp = $n->crawler->get_request_id();
					if ( is_numeric( $tmp ) ) {
						$id      = $tmp;
						$network = $n;

						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Checks if the current request should be handled by the plugin and if it does returns true and populates the $args object.
		 *
		 * @param $args
		 *
		 * @return bool
		 */
		private function is_handled( &$args ) {
			if ( $this->has_required( $args ) ) {
				$this->merge_optional( $args );
				if ( isset($args->post_id) && is_numeric($args->post_id) && intval($args->post_id) > 0){
					$args->url = get_permalink($args->post_id);
				} else {
					$args->url = strtok( self::get_current_url(), '?' );
				}

				return true;
			}

			return false;
		}

		/**
		 * Checks if the current request has the required params and if it does returns true and populates the $args object.
		 *
		 * @param $args
		 *
		 * @return bool
		 */
		private function has_required( &$args ) {
			$args      = (object) array();
			if ( ! isset( $_GET[ $this->options->args->key ] ) ) {
				return false;
			}
			foreach ( $this->options->args->required as $key ) {
				if ( ! isset( $_GET[ $key ] ) ) {
					return false;
				} else {
					$args->$key = $_GET[ $key ];
				}
			}

			return true;
		}

		/**
		 * Checks if the current request has any optional params and if it does merges them into the supplied $args object.
		 *
		 * @param $args
		 */
		private function merge_optional( &$args ) {
			if ( ! is_object( $args ) ) {
				$args = (object) array();
			}
			foreach ( $this->options->args->optional as $key ) {
				if ( isset( $_GET[ $key ] ) ) {
					$args->$key = $_GET[ $key ];
				}
			}
		}

		private function is_redirect( &$share ) {
			if ( isset( $_GET[ $this->options->param ] ) ) {
				$id = base_convert( $_GET[ $this->options->param ], 36, 10 );
				if ( is_numeric( $id ) && $this->db->fetch( $id, $share ) ) {

					return true;
				}
			}

			return false;
		}

		private function share_url( $network ) {
			$share_url = '?' . urlencode( $this->options->args->key ) . '=true&network=' . $network->name;
			$params    = array_merge( $this->options->args->required, $this->options->args->optional );
			foreach ( $params as $key ) {
				if ( $key === 'network' ) {
					continue;
				}
				$share_url .= sprintf( '&%1$s={%1$s}', $key );
			}

			return $share_url;
		}

		private function enabled_networks() {
			$networks = (object) array();
			foreach ( $this->networks as $name => $network ) {
				if ( $network->is_enabled() ) {
					$networks->$name = $this->share_url( $network );
				}
			}

			return $networks;
		}

		const STATUS_HEADER = '%s %d %s';

		private function send_status( $code, $text = '', $nocache = true ) {
			header( sprintf( self::STATUS_HEADER, $_SERVER["SERVER_PROTOCOL"], $code, $text ) );
			if ( $nocache ) {
				nocache_headers();
			}
			if ( isset( $text ) ) {
				echo $text;
			}
			exit;
		}

		const REDIRECT_HEADER = 'Location: %s';

		private function send_redirect( $url, $nocache = true ) {
			header( sprintf( self::REDIRECT_HEADER, $url ) );
			if ( $nocache ) {
				nocache_headers();
			}
			exit;
		}

		/**
		 * Simple method to return the current requests full url.
		 * @return string
		 */
		public static function get_current_url() {
			$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';

			return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		public static function slug_to_class( $slug, $strip_underscore = true ) {
			return preg_replace_callback(
				'/(^.)|(_.)/',
				function ( $m ) use ( &$strip_underscore ) {
					$s = sizeof( $m );

					return strtoupper( $s === 2 ? $m[1] : ( $s === 3 ? ( $strip_underscore ? $m[2][1] : $m[2] ) : $m[0] ) );
				},
				$slug );
		}
	}

	new FooShare_Plugin( array() );
}