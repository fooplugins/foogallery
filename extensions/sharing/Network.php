<?php
if (!class_exists('FooShare_Network')){

	class FooShare_Network {
		public $plugin;
		public $name;
		public $options;
		public $crawler;
		public $enabled;

		public static function create($plugin, $name, $options){
			$class = 'FooShare_Networks_' . FooShare_Plugin::slug_to_class($name);
			return class_exists($class) ? new $class($plugin, $options) : new FooShare_Network($plugin, $name, $options);
		}

		function __construct($plugin, $name, $options) {
			$this->plugin = $plugin;
			$this->name = $name;
			$this->options = (object) wp_parse_args( $options, $plugin->options->defaults->network );
			$this->crawler = FooShare_Crawler::create($this, $options->crawler);
			$this->enabled = $this->options->enabled;
		}

		public function is_enabled(){
			return $this->enabled;
		}

		public function has_crawler(){
			return $this->crawler instanceof FooShare_Crawler;
		}

		public function get_url($share){
			$url = $this->options->url_format;
			foreach($share as $key => $val){
				$key = sprintf('{%s}', $key);
				$url = str_replace($key, urlencode($val), $url);
			}
			return $url;
		}
	}

}