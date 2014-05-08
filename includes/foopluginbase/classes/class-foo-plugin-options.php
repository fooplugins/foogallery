<?php
/*
 * Foo_Plugin_Options class
 *
 * A helper class for storing all your plugin options as a single WP option. Multi-site friendly.
 *
 * Version: 2.1
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !class_exists( 'Foo_Plugin_Options_v2_1' ) ) {
	class Foo_Plugin_Options_v2_1 {

		/**
		 * @var string The name of the option that will be saved to the options table.
		 */
		protected $option_name;

		/**
		 * Foo_Plugin_Options Constructor
		 *
		 * @param string $option_name The name of the single option we want to save in the options table. Usually the plugin slug.
		 */
		function __construct($option_name) {
			$this->option_name = $option_name;
		}

		/**
		 * Private function used to return the merged array of all options.
		 * @return array
		 */
		private function get_options() {

			//get the options based on the type of install (multisite or not)
			if ( is_network_admin() ) {
				$options = get_site_option( $this->option_name );
			} else {
				$options = wp_parse_args( get_option( $this->option_name ), get_site_option( $this->option_name ) );
			}

			//get some defaults (if available)
			$default_options = apply_filters( $this->option_name . '-default_options', array() );

			//merge!
			return wp_parse_args( $options, $default_options );
		}

		/**
		 * Returns all the options in an array
		 * @return array
		 */
		public function get_all() {
			return $this->get_options();
		}

		/**
		 * Save an individual option.
		 *
		 * @param string $key   The key of the individual option that will be stored.
		 * @param mixed  $value The value of the individual option that will be stored.
		 */
		public function save($key, $value) {
			//first get the options
			$options = $this->get_options();

			if ( !$options ) {
				//no options have been saved yet, so add it

				if ( is_network_admin() ) {
					add_site_option( $this->option_name, array($key => $value) );
				} else {
					add_option( $this->option_name, array($key => $value) );
				}

			} else {
				//update the existing option
				$options[$key] = $value;

				if ( is_network_admin() ) {
					update_site_option( $this->option_name, $options );
				} else {
					update_option( $this->option_name, $options );
				}
			}
		}

		/**
		 * Get an individual option.
		 *
		 * @param string $key     The key of the individual option that will be stored.
		 * @param mixed  $default Optional. The default value to return if the key was not found.
		 *
		 * @return mixed
		 */
		public function get($key, $default = false) {
			$options = $this->get_options();

			if ( $options ) {
				return (array_key_exists( $key, $options )) ? $options[$key] : $default;
			}

			return $default;
		}

		/**
		 * Delete an individual option.
		 *
		 * @param $key The key of the individual option we want to delete.
		 */
		public function delete($key) {
			$options = $this->get_options();

			if ( $options ) {
				unset($options[$key]);

				if ( is_network_admin() ) {
					update_site_option( $this->option_name, $options );
				} else {
					update_option( $this->option_name, $options );
				}
			}
		}

		/**
		 * Used to determine if an option is checked (for checkbox options only).
		 * @param string $key
		 * @param bool $default
		 *
		 * @return bool
		 */
		function is_checked($key, $default = false) {
			$options = $this->get_options();

			if ($options) {
				return array_key_exists($key, $options);
			}

			return $default;
		}
	}
}