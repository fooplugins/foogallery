<?php

if (!class_exists('FooShare_Database')) {

	class FooShare_Database {
		const TABLE_NAME = 'fp_shares';
		const CREATE_TABLE = 'CREATE TABLE IF NOT EXISTS %s (
              id  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
              url TEXT NOT NULL,
              hash TEXT NOT NULL,
              content_url TEXT NOT NULL,
              content_type VARCHAR(20) NOT NULL,
              title TEXT,
              description TEXT
            ) AUTO_INCREMENT = 1000000 %s;'; // this is on multiple lines as dbDelta requires the line breaks to work correctly.
		const DROP_TABLE = 'DROP TABLE IF EXISTS %s';
		const GET_ROW = 'SELECT * FROM %s WHERE id = %%d'; // id = %%d as we want the sprintf result to look like id = %d
		const EXISTS = 'SELECT * FROM %s WHERE url = %%s AND content_url = %%s AND content_type = %%s';
		const INSERT = 'INSERT INTO %s (url, hash, content_url, content_type, title, description) VALUES (%%s, %%s, %%s, %%s, %%s, %%s)';
		const UPDATE = 'UPDATE %s SET hash = %%s, title = %%s, description = %%s WHERE id = %%d';

		public $plugin;
		function __construct($plugin) {
			$this->plugin = $plugin;

		}

		public function create_table(){
			global $wpdb;
			$sql = sprintf(self::CREATE_TABLE, self::TABLE_NAME, $wpdb->get_charset_collate());
			// instead of using $wpdb->query($sql) use the below to allow for simple updates to table schema in the future
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}

		static function drop_table(){
			global $wpdb;
			$sql = sprintf(self::DROP_TABLE, self::TABLE_NAME);
			$wpdb->query($sql);
		}

		private function hydrate(&$share){
			if (isset($share)){
				if (is_string($share->id)){
					$share->id = intval($share->id);// in case mysql driver only supports returning strings >_<
				}
				$share->redirect_url = $share->url . (isset($share->hash) && $share->hash != '' ? $share->hash : '');
				$share->share_url = $share->url . (parse_url($share->url, PHP_URL_QUERY) ? '&' : '?') . $this->plugin->options->param . '=' . base_convert( $share->id, 10, 36 );
			}
		}

		public function fetch($id, &$share){
			global $wpdb;
			$sql = sprintf(self::GET_ROW, self::TABLE_NAME);
			$sql = $wpdb->prepare($sql, intval($id) );
			$share = $wpdb->get_row($sql);
			if (isset($share)){
				$this->hydrate($share);
				return true;
			}
			return false;
		}

		public function exists($args, &$share){
			global $wpdb;
			$params = array();
			$params[] = $args->url;
			$sql = sprintf(self::EXISTS, self::TABLE_NAME);
			$sql = $wpdb->prepare($sql, $args->url, $args->content_url, $args->content_type );
			$share = $wpdb->get_row($sql);
			if (isset($share)){
				$this->hydrate($share);
				return true;
			}
			return false;
		}

		public function save($args, &$share){
			global $wpdb;
			if ($this->exists($args, $share)){
				$sql = sprintf(self::UPDATE, self::TABLE_NAME);
				$sql = $wpdb->prepare($sql, $args->hash, $args->title, $args->description, $share->id );
				$wpdb->query($sql);
				$share->title = $args->title;
				$share->description = $args->description;
			} else {
				$sql = sprintf(self::INSERT, self::TABLE_NAME);
				$sql = $wpdb->prepare($sql, $args->url, $args->hash, $args->content_url, $args->content_type, $args->title, $args->description );
				$result = $wpdb->query($sql);
				if ($result === 1){
					$share = $args;
					$share->id = $wpdb->insert_id;
				} else {
					return false;
				}
			}
			$this->hydrate($share);
			return is_integer($share->id);
		}
	}

}