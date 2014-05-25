<?php
/**
 * Template loader for FooGallery
 *
 * @package FooGallery
 * @author  Brad vincent
 */
class FooGallery_Template_Loader {
	/**
	 * Locates and renders the gallery based on the template
	 * Will look in the following locations
	 *  wp-content/themes/{child-theme}/foogallery/gallery-{template}.php
	 * 	wp-content/themes/{theme}/foogallery/gallery-{template}.php
	 *  wp-content/plugins/foogallery/templates/gallery-{template}.php
	 *
	 * @param $args array       Arguments passed in from the shortcode
	 */
	public function render_template( $args ) {
		//do some work before we locate the template
		global $current_foogallery;
		global $current_foogallery_arguments;

		//set the arguments
		$current_foogallery_arguments = $args;

		//load our gallery
		$current_foogallery = $this->find_gallery( $args );

		//find the gallery template we will use to render the gallery
		$template_name = $this->get_arg( $args, 'template',
			$current_foogallery->gallery_template );

		//check if we have any attachments
		if (!$current_foogallery->has_attachments()) {
			//no attachments!
			do_action("foogallery_template_no_attachments-($template_name)", $current_foogallery);
		} else {

			//load any JS & CSS needed by the gallery
			$loader = new Foo_Plugin_File_Loader_v1( FOOGALLERY_SLUG, FOOGALLERY_FILE, 'templates', FOOGALLERY_SLUG );

			if ( false !== ( $template_location = $loader->locate_file( "gallery-{$template_name}.php" ) ) ) {

				//we have found a template!
				do_action("foogallery_template-($template_name)", $current_foogallery);

				//try to include some JS
				if ( false !== ( $js_location = $loader->locate_file( "gallery-{$template_name}.js" ) ) ) {
					wp_enqueue_script( "foogallery-template-{$template_name}", $js_location['url'] );
				}

				//try to include some CSS
				if ( false !== ( $css_location = $loader->locate_file( "gallery-{$template_name}.css" ) ) ) {
					wp_enqueue_style( "foogallery-template-{$template_name}", $css_location['url'] );
				}

				//finally include the actual php template!
				if ( $template_location ) {
					load_template( $template_location['path'], false );
				}
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