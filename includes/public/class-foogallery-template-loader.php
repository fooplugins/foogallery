<?php
/**
 * Template loader for FooGallery
 *
 * Template loader based on Gamajo_Template_Loader
 *
 * @package FooGallery
 * @author  Brad vincent
 */
class FooGallery_Template_Loader extends Gamajo_Template_Loader {

	/**
	 * Prefix for filter names.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $filter_prefix = 'foogallery';

	/**
	 * Directory name where custom templates for foogallery should be found in the theme.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $theme_template_directory = 'foogallery';

	/**
	 * Reference to the root directory path of foogallery.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $plugin_directory = FOOGALLERY_PATH;

	public function get_template_part( $slug, $name = null, $load = true ) {
		//stop any developers from calling get_template_part directly on this class. Force them to use render_template
		throw new Exception( __( 'You cannot call the "get_template_part" function directly inside FooGallery_Template_Loader. Rather use "render_template"render_template"', 'foogallery' ) );
	}

	/**
	 * Locates and renders the gallery based on the template
	 *
	 * @param $args array       Arguments passed in from the shortcode
	 */
	public function render_template( $args ) {
		//do some work before we locate the template
		global $current_foogallery;
		global $current_foogallery_arguments;

		$current_foogallery_arguments = $args;
		$current_foogallery = $this->find_gallery( $args );

		$template_name = $this->get_arg( $args, 'template' );

		$template_location = $this->locate_gallery_template( $template_name );

		//if we have found something then load!
		if ( $template_location ) {
			load_template( $template_location, false );
		}
	}

	/**
	 * Try to locate the template first. Will look in the following locations
	 *  wp-content/themes/{theme}/foogallery/gallery-{template}.php
	 *  wp-content/plugins/foogallery/templates/gallery-{template}.php
	 *
	 * @param $template_name string The name of the template we want to locatae
	 *
	 * @return string The location of the template file
	 */
	private function locate_gallery_template( $template_name ) {
		$template_location = parent::get_template_part( 'gallery-' . $template_name, NULL, false );

		if ( !$template_location ) {
			//check if we are dealing with the default. To make sure we do not go into an infinite loop for some reason
			if ( $template_name === foogallery_get_default( 'gallery_template' ) ) { return false; }

			//we could not locate the template, so allow for a plugin to override the location
			$template_location = apply_filters( 'foogallery_gallery_template_location', $template_name );

			if ( !$template_location ) {
				//if no plugin overrode the location then let's use the default gallery template
				return $this->locate_gallery_template( foogallery_get_default( 'gallery_template' ) );
			}
		}

		return $template_location;
	}


	/**
	 * load the gallery based on either the id or slug, passed in via arguments
	 *
	 * @param $args array       Arguments passed in from the shortcode
	 *
	 * @return bool|FooGallery  The gallery object we want to render
	 */
	function find_gallery( $args ) {

		$id = intval( $this->get_arg($args, 'id'), 0 );

		if ($id > 0) {

			//load gallery by ID
			return FooGallery::get_by_id( $id );

		} else {

			//take into account the cases where id is passed in via the 'gallery' attribute
			$gallery = $this->get_arg('gallery', 0);

			if ( intval($gallery) > 0 ) {
				//we have an id, so load
				return FooGallery::get_by_id( intval( $gallery ) );
			}
			//we are dealing with a slug
			return FooGallery::get_by_slug( $gallery );
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
		if ( empty( $args ) || !array_key_exists( $key, $args ) ) {
			return $default;
		}

		return $args[$key];
	}
}