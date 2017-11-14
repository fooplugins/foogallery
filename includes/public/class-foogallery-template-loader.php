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
	 *    wp-content/themes/{theme}/foogallery/gallery-{template}.php
	 *  wp-content/plugins/foogallery/templates/gallery-{template}.php
	 *
	 * @param $args array       Arguments passed in from the shortcode
	 */
	public function render_template( $args ) {
		//do some work before we locate the template
		global $current_foogallery;
		global $current_foogallery_arguments;
		global $current_foogallery_template;

		//set the arguments
		$current_foogallery_arguments = $args;

		//load our gallery
		$current_foogallery = $this->find_gallery( $args );

		if ( false === $current_foogallery ) {
			//we could not find the gallery!
			_e( 'The gallery was not found!', 'foogallery' );
			return;
		}

		//check if the gallery is password protected
		if ( post_password_required( $current_foogallery->_post ) ) {
			echo get_the_password_form( $current_foogallery->_post );
			return;
		}

		//find the gallery template we will use to render the gallery
		$current_foogallery_template = $this->get_arg( $args, 'template', $current_foogallery->gallery_template );
		//set a default if we have no gallery template
		if ( empty( $current_foogallery_template ) ) {
			$current_foogallery_template = foogallery_get_default( 'gallery_template' );
		}

		//override the template if needed
		if ( $current_foogallery->gallery_template !== $current_foogallery_template ) {
			$current_foogallery->gallery_template = $current_foogallery_template;
		}

		//potentially override attachment_ids from arguments
		$attachment_ids = $this->get_arg( $args, 'attachment_ids', false );
		if ( $attachment_ids ) {
			$current_foogallery->attachment_ids = explode( ',', $attachment_ids );
		}

		//check if we have any attachments
		if ( ! $current_foogallery->has_attachments() ) {
			//no attachments!
			do_action( "foogallery_template_no_attachments-($current_foogallery_template)", $current_foogallery );
			do_action( "foogallery_template_no_attachments", $current_foogallery );
		} else {

			//create locator instance
			$loader = $this->create_locator_instance();

			if ( false !== ( $template_location = $loader->locate_file( "gallery-{$current_foogallery_template}.php" ) ) ) {

				//we have found a template!
				do_action( 'foogallery_located_template', $current_foogallery );
				do_action( "foogallery_located_template-{$current_foogallery_template}", $current_foogallery );

				//try to include some JS, but allow template to opt-out based on some condition
				if ( false !== apply_filters( "foogallery_template_load_js-{$current_foogallery_template}", true, $current_foogallery ) ) {
					if ( false !== ( $js_location = $loader->locate_file( "gallery-{$current_foogallery_template}.js" ) ) ) {
						$js_deps = apply_filters( "foogallery_template_js_deps-{$current_foogallery_template}", array(), $current_foogallery );
						$js_ver = apply_filters( "foogallery_template_js_ver-{$current_foogallery_template}", FOOGALLERY_VERSION, $current_foogallery );
						wp_enqueue_script( "foogallery-template-{$current_foogallery_template}", $js_location['url'], $js_deps, $js_ver );
						do_action( 'foogallery_template_enqueue_script', $current_foogallery_template, $js_location['url'] );
					}
				}

				//try to include some CSS, but allow template to opt-out based on some condition
				if ( false !== apply_filters( "foogallery_template_load_css-{$current_foogallery_template}", true, $current_foogallery ) ) {
					if ( false !== ( $css_location = $loader->locate_file( "gallery-{$current_foogallery_template}.css" ) ) ) {
						$css_deps = apply_filters( "foogallery_template_css_deps-{$current_foogallery_template}", array(), $current_foogallery );
						$css_ver = apply_filters( "foogallery_template_css_ver-{$current_foogallery_template}", FOOGALLERY_VERSION, $current_foogallery );
						foogallery_enqueue_style( "foogallery-template-{$current_foogallery_template}", $css_location['url'], $css_deps, $css_ver );
					}
				}

				//finally include the actual php template!
				if ( $template_location ) {
					$this->load_gallery_template( $current_foogallery, $template_location['path'] );
				}

				//cater for lightbox extensions needing to add styles and javascript
				$lightbox = foogallery_gallery_template_setting( 'lightbox' );
				if ( !empty( $lightbox ) ) {
					do_action( "foogallery_template_lightbox-{$lightbox}", $current_foogallery );
				}

				//we have loaded all files, now let extensions do some stuff
				do_action( "foogallery_loaded_template", $current_foogallery );
				do_action( "foogallery_loaded_template-($current_foogallery_template)", $current_foogallery );
			} else {
				//we could not find a template!
				_e( 'No gallery template found!', 'foogallery' );
			}
		}

		//cleanup globals in case there are multiple galleries on a page
        $current_foogallery = null;
        $current_foogallery_arguments = null;
        $current_foogallery_template = null;
	}

	/***
	 * Loads a gallery template location and wraps the calls so that it can be intercepted
	 *
	 * @param FooGallery $gallery
	 * @param string $template_location
	 */
	function load_gallery_template($gallery, $template_location) {

		$override_load_template = apply_filters( 'foogallery_load_gallery_template', false, $gallery, $template_location );

		if ( $override_load_template ) {
			//if we have overridden the loading of the template, then we can exit without doing anything further
			return;
		}

		//if we get to this point, then we need to load the template as per normal
		load_template( $template_location, false );
	}

    /**
     * Creates a locator instance used for including template files
     *
     *
     */
    public function create_locator_instance() {
        $instance_name = FOOGALLERY_SLUG . '_gallery_templates';
        $loader        = new Foo_Plugin_File_Locator_v1( $instance_name, FOOGALLERY_FILE, 'templates', FOOGALLERY_SLUG );

        //allow extensions to very easily add pickup locations for their files
        $this->add_extension_pickup_locations( $loader, apply_filters( $instance_name . '_files', array() ) );

        return $loader;
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
	function find_gallery( $args ) {

		$id = intval( $this->get_arg( $args, 'id' ), 0 );
		$gallery = $this->get_arg( $args, 'gallery', 0 );

		if ( $id > 0 ) {
			//load gallery by ID
			return FooGallery::get_by_id( $id );
		}

		//take into account the cases where id is passed in via the 'gallery' attribute
		if ( intval( $gallery ) > 0 ) {
			//we have an id, so load
			return FooGallery::get_by_id( intval( $gallery ) );
		} else if ( !empty( $gallery ) ) {
			//we are dealing with a slug
			return FooGallery::get_by_slug( $gallery );
		}

		//if we get here then we have no id or gallery attribute, so try to build a dynamic gallery

		//we can only build up a dynamic gallery if attachment_ids are passed in
		$attachment_ids = $this->get_arg( $args, 'attachment_ids', false );

		if ( $attachment_ids ) {
			$template = $this->get_arg( $args, 'template', foogallery_get_default( 'gallery_template' ) );
			return FooGallery::dynamic( $template, explode( ',', $attachment_ids) );
		}

		return false;
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
		if ( empty($args) || ! array_key_exists( $key, $args ) ) {
			return $default;
		}

		return $args[ $key ];
	}
}
