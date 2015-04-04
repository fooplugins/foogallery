<?php

/**
 * Class FooGalleryAlbum
 *
 * An easy to use wrapper class for a FooGallery Album post
 */
class FooGalleryAlbum extends stdClass {

	/**
	 * private constructor
	 *
	 * @param null $post
	 */
	private function __construct( $post = null ) {
		$this->set_defaults();

		if ( $post !== null ) {
			$this->load( $post );
		}
	}

	/**
	 *  Sets the default when a new album is instantiated
	 */
	private function set_defaults() {
		$this->_post = null;
		$this->ID = 0;
		$this->gallery_ids = array();
		$this->_galleries = false;
	}

	/**
	 * private gallery load function
	 * @param $post
	 */
	private function load( $post ) {
		$this->_post = $post;
		$this->ID = $post->ID;
		$this->slug = $post->post_name;
		$this->name = $post->post_title;
		$this->author = $post->post_author;
		$this->post_status = $post->post_status;
		$album_meta = get_post_meta( $this->ID, FOOGALLERY_ALBUM_META_GALLERIES, true );
		$this->gallery_ids = is_array( $album_meta ) ? array_filter( $album_meta ) : array();
		$this->album_template = get_post_meta( $post->ID, FOOGALLERY_ALBUM_META_TEMPLATE, true );
		$this->settings = get_post_meta( $post->ID, FOOGALLERY_META_SETTINGS, true );
		$this->custom_css = get_post_meta( $post->ID, FOOGALLERY_META_CUSTOM_CSS, true );
		$this->sorting = get_post_meta( $post->ID, FOOGALLERY_ALBUM_META_SORT, true );
		do_action( 'foogallery_foogallery-album_instance_after_load', $this, $post );
	}

	/**
	 * private function to load a album by an id
	 * @param $post_id
	 */
	private function load_by_id( $post_id ) {
		$post = get_post( $post_id );
		if ( $post ) {
			$this->load( $post );
		}
	}

	/**
	 * private function to load a album by the slug.
	 * Will be used when loading album shortcodes
	 * @param $slug
	 */
	private function load_by_slug( $slug ) {
		if ( ! empty( $slug ) ) {
			$args = array(
				'name'        => $slug,
				'numberposts' => 1,
				'post_type'   => FOOGALLERY_CPT_ALBUM,
			);

			$albums = get_posts( $args );

			if ( $albums ) {
				$this->load( $albums[0] );
			}
		}
	}

	/**
	 * Static function to load a Album instance by passing in a post object
	 * @static
	 *
	 * @param $post
	 *
	 * @return FooGalleryAlbum
	 */
	public static function get( $post ) {
		return new self( $post );
	}

	/**
	 * Static function to load an Album instance by post id
	 *
	 * @param $post_id
	 *
	 * @return FooGalleryAlbum
	 */
	public static function get_by_id( $post_id ) {
		$album = new self();
		$album->load_by_id( $post_id );
		if ( ! $album->does_exist() ) {
			return false;
		}
		return $album;
	}

	/**
	 * Static function to load a album instance by passing in a album slug
	 *
	 * @param string $slug
	 *
	 * @return FooGalleryAlbum
	 */
	public static function get_by_slug( $slug ) {
		$album = new self();
		$album->load_by_slug( $slug );
		if ( ! $album->does_exist() ) {
			return false;
		}
		return $album;
	}

	/**
	 * Checks if the album has galleries
	 * @return bool
	 */
	public function has_galleries() {
		return sizeof( $this->gallery_ids ) > 0;
	}

	/**
	 * Checks if the album exists
	 * @return bool
	 */
	public function does_exist() {
		return $this->ID > 0;
	}

	/**
	 * Returns true if the album is published
	 * @return bool
	 */
	public function is_published() {
		return $this->post_status === 'publish';
	}

	/**
	 * Get a comma separated list of gallery ids
	 * @return string
	 */
	public function gallery_id_csv() {
		if ( is_array( $this->gallery_ids ) ) {
			return implode( ',', $this->gallery_ids );
		}

		return '';
	}

	/**
	 * Lazy load the attachments for the gallery
	 *
	 * @return array
	 */
	public function galleries() {
		//lazy load the attachments for performance
		if ( $this->_galleries === false ) {
			$this->_galleries = array();

			if ( ! empty( $this->gallery_ids ) ) {

				$gallery_query_args = apply_filters( 'foogallery_album_gallery_get_posts_args', array(
					'post_type'      => FOOGALLERY_CPT_GALLERY,
					'posts_per_page' => -1,
					'post__in'       => $this->gallery_ids,
					'orderby'        => foogallery_sorting_get_posts_orderby_arg( $this->sorting ),
					'order'          => foogallery_sorting_get_posts_order_arg( $this->sorting )
				) );

				$galleries = get_posts( $gallery_query_args );

				$this->_galleries = array_map( array( 'FooGallery', 'get' ), $galleries );
			}
		}

		return $this->_galleries;
	}

	function includes_gallery( $gallery_id ) {
		if ( $this->has_galleries() ) {
			return in_array( $gallery_id, $this->gallery_ids );
		}
		return false;
	}

	public function gallery_count() {
		$count = sizeof( $this->gallery_ids );
		switch ( $count ) {
			case 0:
				return __( 'No galleries', 'foogallery' );
			case 1:
				return __( '1 gallery', 'foogallery' );
			default:
				return sprintf( __( '%s galleries', 'foogallery' ), $count );
		}
	}

	/**
	 * Output the shortcode for the gallery
	 *
	 * @return string
	 */
	public function shortcode() {
		return foogallery_build_album_shortcode( $this->ID );
	}

	function get_meta( $key, $default ) {
		if ( ! is_array( $this->settings ) ) {
			return $default;
		}

		$value = array_key_exists( $key, $this->settings ) ? $this->settings[ $key ] : null;

		if ( $value === null ) {
			return $default;
		}

		return $value;
	}

	function is_checked( $key, $default = false ) {
		if ( ! is_array( $this->settings ) ) {
			return $default;
		}

		return array_key_exists( $key, $this->settings );
	}

	public function album_template_details() {
		if ( ! empty( $this->album_template ) ) {

			foreach ( foogallery_album_templates() as $template ) {
				if ( $this->album_template == $template['slug'] ) {
					return $template;
				}
			}
		}

		return false;
	}
}
