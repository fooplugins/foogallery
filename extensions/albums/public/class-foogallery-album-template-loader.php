<?php

/**
 * Template loader for FooGallery Albums
 *
 * @package FooGallery
 * @author  Brad vincent
 */
class FooGallery_Album_Template_Loader {

	/**
	 * Locates and renders the album based on the template
	 * Will look in the following locations
	 *  wp-content/themes/{child-theme}/foogallery/album-{template}.php
	 *    wp-content/themes/{theme}/foogallery/album-{template}.php
	 *  wp-content/plugins/foogallery/templates/album-{template}.php
	 *
	 * @param $args array       Arguments passed in from the shortcode
	 */
	public function render_template( $args ) {
		//do some work before we locate the template
		global $current_foogallery_album;
		global $current_foogallery_album_arguments;
		global $current_foogallery_album_template;

		//set the arguments
		$current_foogallery_album_arguments = $args;

		//load our album
		$current_foogallery_album = $this->find_album( $args );

		if ( false === $current_foogallery_album ) {
			_e( 'Could not load the album!', 'foogallery' );
			return;
		}

		//find the gallery template we will use to render the gallery
		$current_foogallery_album_template = $this->get_arg( $args, 'template', $current_foogallery_album->album_template );

		//set a default if we have no gallery template
		if ( empty($current_foogallery_album_template) ) {
			$current_foogallery_album_template = foogallery_get_default( 'album_template' );
		}

		//if we still have not default, then use the first one we can find
		if ( empty($current_foogallery_album_template) ) {
			$available_templates = foogallery_album_templates();
			$current_foogallery_album_template = $available_templates[0]['slug'];
		}

		//check if we have any galleries
		if ( ! $current_foogallery_album->has_galleries() ) {
			//no galleries!
			do_action( "foogallery_album_template_no_galleries-($current_foogallery_album_template)", $current_foogallery_album );
		} else {

			//create locator instance
			$instance_name = FOOGALLERY_SLUG . '_album_templates';
			$loader = new Foo_Plugin_File_Locator_v1( $instance_name, FOOGALLERY_FILE, 'templates', FOOGALLERY_SLUG );

			//allow extensions to very easily add pickup locations for their files
			$this->add_extension_pickup_locations( $loader, apply_filters( $instance_name . '_files', array() ) );

			if ( false !== ($template_location = $loader->locate_file( "album-{$current_foogallery_album_template}.php" )) ) {

				//we have found a template!
				do_action( 'foogallery_located_album_template', $current_foogallery_album );
				do_action( "foogallery_located_album_template-{$current_foogallery_album_template}", $current_foogallery_album );

				//try to include some JS
				if ( false !== ($js_location = $loader->locate_file( "album-{$current_foogallery_album_template}.js" )) ) {
					wp_enqueue_script( "foogallery-album-template-{$current_foogallery_album_template}", $js_location['url'] );
				}

				//try to include some CSS
				if ( false !== ($css_location = $loader->locate_file( "album-{$current_foogallery_album_template}.css" )) ) {
					foogallery_enqueue_style( "foogallery-album-template-{$current_foogallery_album_template}", $css_location['url'] );
				}

				//finally include the actual php template!
				if ( $template_location ) {
					load_template( $template_location['path'], false );
				}

				//we have loaded all files, now let extensions do some stuff
				do_action( "foogallery_loaded_album_template", $current_foogallery_album );
				do_action( "foogallery_loaded_album_template-($current_foogallery_album_template)", $current_foogallery_album );

			} else {
				//we could not find a template!
				_e( 'No album template found!', 'foogallery' );
			}
		}
	}

	/**
	 * Add pickup locations to the loader to make it easier for extensions
	 *
	 * @param $loader Foo_Plugin_File_Locator_v1
	 * @param $extension_files array
	 */
	function add_extension_pickup_locations( $loader, $extension_files ) {
		if ( count( $extension_files ) > 0 ) {
			$position = 120;
			foreach ( $extension_files as $file ) {

				//add pickup location for php template
				$loader->add_location( $position, array(
					'path' => trailingslashit( plugin_dir_path( $file ) ),
					'url'  => trailingslashit( plugin_dir_url( $file ) )
				) );

				$position++;

				//add pickup location for extensions js folder
				$loader->add_location( $position, array(
					'path' => trailingslashit( plugin_dir_path( $file ) . 'js' ),
					'url'  => trailingslashit( plugin_dir_url( $file ) . 'js' )
				) );

				$position++;

				//add pickup location for extension css folder
				$loader->add_location( $position, array(
					'path' => trailingslashit( plugin_dir_path( $file ) . 'css' ),
					'url'  => trailingslashit( plugin_dir_url( $file ) . 'css' )
				) );

				$position++;

			}
		}
	}

	/**
	 * load the gallery based on either the id or slug, passed in via arguments
	 *
	 * @param $args array       Arguments passed in from the shortcode
	 *
	 * @return bool|FooGallery  The gallery object we want to render
	 */
	function find_album( $args ) {

		$id = intval( $this->get_arg( $args, 'id' ), 0 );

		if ( $id > 0 ) {

			//load album by ID
			return FooGalleryAlbum::get_by_id( $id );

		} else {

			//take into account the cases where id is passed in via the 'album' attribute
			$album = $this->get_arg( 'album', 0 );

			if ( intval( $album ) > 0 ) {
				//we have an id, so load
				return FooGalleryAlbum::get_by_id( intval( $album ) );
			}

			//we are dealing with a slug
			return FooGalleryAlbum::get_by_slug( $album );
		}
	}

	/**
	 * Helper to get an argument value from an array arguments
	 *
	 * @param $args    Array    the array of arguments to search
	 * @param $key     string   the key of the argument you are looking for
	 * @param $default string   a default value if the argument is not found
	 *
	 * @return string
	 */
	function get_arg( $args, $key, $default = '' ) {
		if ( empty($args)
		     || !is_array( $args )
		     || !array_key_exists( $key, $args ) ) {
			return $default;
		}

		return $args[ $key ];
	}
}
