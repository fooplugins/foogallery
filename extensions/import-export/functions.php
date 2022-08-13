<?php

/**
 * Generate the JSON export for a gallery
 *
 * @param int[] $foogallery_ids The IDs of the gallery to export.
 *
 * @return string
 */
function foogallery_generate_export_json( $foogallery_ids ) {
	global $current_foogallery;

	$exported_galleries = array();

	if ( ! is_array( $foogallery_ids ) && intval( $foogallery_ids ) > 0 ) {
		$foogallery_ids = array( $foogallery_ids );
	}

	foreach ( $foogallery_ids as $foogallery_id ) {

		$current_foogallery = FooGallery::get_by_id( $foogallery_id );
		do_action( 'foogallery_located_template', $current_foogallery );

		$source_settings   = get_post_meta( $foogallery_id, FOOGALLERY_META_SETTINGS, true );
		$source_sorting    = get_post_meta( $foogallery_id, FOOGALLERY_META_SORT, true );
		$source_retina     = get_post_meta( $foogallery_id, FOOGALLERY_META_RETINA, true );
		$source_custom_css = get_post_meta( $foogallery_id, FOOGALLERY_META_CUSTOM_CSS, true );

		$export = array(
			'ID'              => $foogallery_id,
			'template'        => $current_foogallery->gallery_template,
			'name'            => $current_foogallery->name,
			'datasource_name' => $current_foogallery->datasource_name,
		);

		if ( 'media_library' === $current_foogallery->datasource_name ) {
			$export['attachment_ids'] = $current_foogallery->attachment_ids;
			$attachments              = array();
			foreach ( $current_foogallery->attachments() as $attachment ) {

				$attachment_object = array(
					'url'         => $attachment->url,
					'title'       => $attachment->title,
					'caption'     => $attachment->caption,
					'description' => $attachment->description,
					'alt'         => $attachment->alt,
				);

				if ( ! empty( $attachment->custom_url ) ) {
					$attachment_object['custom_url'] = $attachment->custom_url;
				}
				if ( ! empty( $attachment->custom_target ) ) {
					$attachment_object['custom_target'] = $attachment->custom_target;
				}

				if ( defined( 'FOOGALLERY_ATTACHMENT_TAXONOMY_TAG' ) ) {
					$tags = wp_get_post_terms( $attachment->ID, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, array( 'fields' => 'names' ) );
					if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
						$attachment_object['tags'] = $tags;
					}

					$categories = wp_get_post_terms( $attachment->ID, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, array( 'fields' => 'names' ) );
					if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
						$attachment_object['categories'] = $categories;
					}
				}

				$attachments[ $attachment->ID ] = $attachment_object;
			}
		} else {
			$export['datasource_value'] = $current_foogallery->datasource_value;
		}

		$export['settings']   = $source_settings;
		$export['sorting']    = $source_sorting;
		$export['retina']     = $source_retina;
		$export['custom_css'] = $source_custom_css;
		if ( isset( $attachments ) ) {
			$export['attachments'] = $attachments;
		}

		$exported_galleries[] = $export;
	}

	return foogallery_json_encode( $exported_galleries );
}
