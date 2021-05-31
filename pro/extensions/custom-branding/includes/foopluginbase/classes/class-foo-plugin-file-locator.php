<?php
/**
 * File Locator for Plugins.
 * Based on ideas found in Gamajo-Template-Loader by Gary Jones http://github.com/GaryJones/Gamajo-Template-Loader
 *
 * @package   Foo_Plugin_Base
 * @author    Brad Vincent
 * @link      https://github.com/fooplugins/Foo_Plugin_Base
 * @copyright 2014 Brad Vincent
 * @license   GPL-2.0+
 * @version   1.0.0
 */

if ( !class_exists( 'Foo_Plugin_File_Locator_v1' ) ) {

	/**
	 * File loader.
	 *
	 * Create a new class that extends this one and override the properties.
	 */
	class Foo_Plugin_File_Locator_v1 {

		/**
		 * Prefix for filter names.
		 *
		 * @since 1.0.0
		 *
		 * @type string
		 */
		protected $plugin_slug;

		/**
		 * Directory name where custom files for this plugin should be found in the theme.
		 *
		 * @since 1.0.0
		 *
		 * @type string
		 */
		protected $theme_file_directory; //will use the plugin_slug if not set

		/**
		 * Reference to the plugin root file.
		 *
		 * @since 1.0.0
		 *
		 * @type string
		 */
		protected $plugin_file; // usually you would just pass in __FILE__

		/**
		 * Directory name where files are found in this plugin.
		 *
		 * Can either be a defined constant, or a relative reference from where the subclass lives.
		 *
		 * @since 1.1.0
		 *
		 * @type string
		 */
		protected $plugin_file_directory; // or includes/templates, etc.

		protected $extra_locations = array();

		public function __construct($plugin_slug, $plugin_file, $plugin_file_directory = '', $theme_file_directory = '') {
			$this->plugin_file = $plugin_file;
			$this->plugin_slug = $plugin_slug;

			//if we did not pass in a theme directory, then default it to the plugin slug
			if ( empty($theme_file_directory) ) {
				$theme_file_directory = $plugin_slug;
			}
			$this->theme_file_directory  = $theme_file_directory;
			$this->plugin_file_directory = $plugin_file_directory;
		}

		/**
		 * Locates a file
		 *
		 * @since 1.0.0
		 *
		 * @param string $filename
		 *
		 * @return string
		 */
		public function locate_file($filename) {

			//allow the filename to be overridden
			$filename = apply_filters( $this->plugin_slug . '_locate_file_filename', $filename );

			// No file found yet
			$located = false;

			$possible_locations = $this->get_locations();

			// try locating the file by looping through the file paths
			foreach ( $possible_locations as $location ) {
				$path = trailingslashit( $location['path'] );
				if ( file_exists( $path . $filename ) ) {
					$located = array(
						'path' => $path . $filename,
						'url'  => trailingslashit( $location['url'] ) . $filename
					);
					break;
				}
			}

			if ( $located ) {
				do_action( $this->plugin_slug . '_located_file', $filename, $located );
			} else {
				do_action( $this->plugin_slug . '_not_located_file', $filename );
			}

			return $located;
		}

		/**
		 * Return a list of paths to check for file locations.
		 *
		 * Default is to check in a child theme (if relevant) before a parent theme, so that themes which inherit from a
		 * parent theme can just overload one file. If the file is not found in either of those, it looks in the
		 * theme-compat folder last.
		 *
		 * @since 1.0.0
		 *
		 * @return mixed|void
		 */
		protected function get_locations() {
			$theme_directory = trailingslashit( $this->theme_file_directory );

			$locations = array(
				10  => array(
					'path' => trailingslashit( get_template_directory() ) . $theme_directory,
					'url'  => trailingslashit( get_template_directory_uri() ) . $theme_directory
				),
				1000 => array(
					'path' => trailingslashit( plugin_dir_path( $this->plugin_file ) ) . $this->plugin_file_directory,
					'url'  => trailingslashit( plugin_dir_url( $this->plugin_file ) ) . $this->plugin_file_directory
				)
			);

			// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
			if ( is_child_theme() ) {
				$locations[1] = array(
					'path' => trailingslashit( get_stylesheet_directory() ) . $theme_directory,
					'url'  => trailingslashit( get_stylesheet_directory_uri() ) . $theme_directory
				);
			}

			//add any extra locations that may have been added
			$locations = $locations + $this->extra_locations;

			/**
			 * Allow ordered list of template paths to be amended.
			 *
			 * @since 1.0.0
			 *
			 * @param array $var Default is directory in child theme at index 1, parent theme at 10, and plugin at 100.
			 */
			$locations = apply_filters( $this->plugin_slug . '_file_locator_pickup_locations', $locations );

			// sort the file paths based on priority
			ksort( $locations, SORT_NUMERIC );

			return $locations;
		}

		public function add_location( $position, $location ) {
			$this->extra_locations[$position] = $location;
		}
	}
}
