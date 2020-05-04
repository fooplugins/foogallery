<?php
/**
 * FooGallery PRO global functions
 */

/**
 * Enqueue the FooGallery Media Views stylesheet
 */
function foogallery_enqueue_media_views_style() {
	wp_enqueue_style( 'foogallery-media-views', FOOGALLERY_PRO_URL . 'css/foogallery.media-views.min.css', array(), FOOGALLERY_VERSION );
}

/**
 * Enqueue the FooGallery Media Views script
 */
function foogallery_enqueue_media_views_script() {
	wp_enqueue_script( 'foogallery-media-views', FOOGALLERY_PRO_URL . 'js/foogallery.media-views.min.js', array( 'jquery', 'media-views', 'underscore' ), FOOGALLERY_VERSION );
}

/**
 * Include the media views templates used for the video import
 */
function foogallery_include_media_views_templates() {
	include FOOGALLERY_PRO_PATH . 'includes/foogallery-media-views-templates.php';
}


/**
 * Retrieve the Vimeo access code from the foogallery settings
 * @return mixed
 */
function foogallery_settings_get_vimeo_access_token() {
	return foogallery_get_setting( 'vimeo_access_token' );
}

/**
 * Save the Vimeo access token to the foogallery settings
 * @param $access_token
 */
function foogallery_settings_set_vimeo_access_token( $access_token ) {
	$foogallery = FooGallery_Plugin::get_instance();

	$foogallery->options()->save( 'vimeo_access_token', $access_token );
}

/**
 * Get terms hierarchy in a recursive way
 *
 * @param  string $taxonomy The taxonomy name
 * @param  array $args The arguments which should be passed to the get_terms function
 * @param  int $parent The terms parent id (for recursive usage)
 * @param  int $level The current level (for recursive usage)
 * @param  array $parents An array with all the parent terms (for recursive usage)
 *
 * @return array $terms_all An array with all the terms for this taxonomy
 */
function foogallery_build_terms_recursive($taxonomy, $args = array(), $parent = 0, $level = 1, $parents = array()) {
	global $foogallery_cached_terms;

	//check if the taxonomy terms have already been built up
	if ( 0 === $parent && isset( $foogallery_cached_terms ) && is_array( $foogallery_cached_terms ) && array_key_exists( $taxonomy, $foogallery_cached_terms ) ) {
		return $foogallery_cached_terms[$taxonomy];
	}

	$terms_all = array();

	$args['parent'] = $args['child_of'] = $parent;

	$terms = get_terms($taxonomy, $args);

	foreach($terms as $term) {
		$term->level = $level;
		$term->parents = $parents;
		$term_parents = $parents;
		$term_parents[] = $term->name;
		$terms_all[] = $term;
		$terms_sub = foogallery_build_terms_recursive($taxonomy, $args, $term->term_id, $level + 1, $term_parents);

		if(!empty($terms_sub)) {
			$terms_all = array_merge($terms_all, $terms_sub);
		}
	}

	//cache what we have built up
	if ( 0 === $parent ) {
		if ( !isset( $foogallery_cached_terms ) ) {
			$foogallery_cached_terms = array();
		}
		if (!array_key_exists( $taxonomy, $foogallery_cached_terms ) ) {
			$foogallery_cached_terms[ $taxonomy ] = $terms_all;
		}
	}

	return $terms_all;
}