<?php

if ( !class_exists('FooGallery_NextGen_Helper' ) ) {

  class FooGallery_NextGen_Helper {

    const NEXTGEN_TABLE_GALLERY = 'ngg_gallery';
    const NEXTGEN_TABLE_PICTURES = 'ngg_pictures';
    const NEXTGEN_OPTION_IMPORT_PROGRESS = 'foogallery_nextgen_import_progress';
    const NEXTGEN_PROGRESS_NOT_STARTED = 'not_started';
    const NEXTGEN_PROGRESS_STARTED = 'started';
    const NEXTGEN_PROGRESS_COMPLETED = 'completed';
    const NEXTGEN_PROGRESS_ERROR = 'error';


    /**
     * @TODO
     */
    function is_nextgen_installed() {
        return class_exists( 'C_NextGEN_Bootstrap' ) ||
          class_exists('nggLoader');
    }


    function get_galleries() {
      global $wpdb;
      $gallery_table = $wpdb->prefix . self::NEXTGEN_TABLE_GALLERY;
      $picture_table = $wpdb->prefix . self::NEXTGEN_TABLE_PICTURES;
      return $wpdb->get_results( "select gid, name, title, galdesc,
(select count(*) from {$picture_table} where galleryid = gid) 'image_count'
from {$gallery_table}" );
    }

    function get_gallery( $id ) {
      global $wpdb;
      $gallery_table = $wpdb->prefix . self::NEXTGEN_TABLE_GALLERY;
      $picture_table = $wpdb->prefix . self::NEXTGEN_TABLE_PICTURES;
      return $wpdb->get_row( $wpdb->prepare( "select gid, name, title, galdesc, path, author,
(select count(*) from {$picture_table} where galleryid = gid) 'image_count'
from {$gallery_table}
where gid = %d", $id ) );
    }

    function get_gallery_images( $id ) {
        global $wpdb;
        $picture_table = $wpdb->prefix . self::NEXTGEN_TABLE_PICTURES;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$picture_table} WHERE galleryid = %d", $id));
    }

    function get_import_progress( $id ) {
      $progress = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS );
      if ( false !== $progress ) {
        if ( array_key_exists( $id, $progress ) ) {
          return $progress[$id];
        }
      }
      return array(
          'status' => self::NEXTGEN_PROGRESS_NOT_STARTED,
          'message' => __('Not imported', 'foogallery')
      );
    }

    function set_import_progress( $nextgen_gallery_id, $status, $message, $foogallery_id ) {
      $progress = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, array() );
      $progress[$nextgen_gallery_id] = array(
          'status' => $status,
          'message' => $message,
          'foogallery' => $foogallery_id
      );
      update_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, $progress );
    }

    function import_picture( $nextgen_gallery_path, $picture ) {
      $picture_url = trailingslashit( site_url() ) .
        trailingslashit( $nextgen_gallery_path ) . $picture->filename;

      $filename = basename( $picture_url );

      //@TODO
      //try and find an existing attachment and return that immediately

      // Get the contents of the picture
      $response = wp_remote_get( $picture_url );
      $contents = wp_remote_retrieve_body( $response );

      // Upload and get file data
      $upload = wp_upload_bits( basename( $picture_url ), null, $contents );
      $guid = $upload['url'];
      $file = $upload['file'];
      $file_type = wp_check_filetype( basename( $file ), null );

      // Create attachment
      $attachment = array(
        'ID' => 0,
        'guid' => $upload['url'],
        'post_title' => $picture->alttext != '' ? $picture->alttext : $picture->image_slug,
        'post_excerpt' => $picture->description,
        'post_content' => $picture->description,
        'post_date' => '',
        'post_mime_type' => $file_type['type']
      );

      // Include image.php so we can call wp_generate_attachment_metadata()
      require_once(ABSPATH . 'wp-admin/includes/image.php');

      // Insert the attachment
      $attachment_id = wp_insert_attachment($attachment, $file, 0);
      $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
      wp_update_attachment_metadata($attachment_id, $attachment_data);

      // Save alt text in the post meta and increment counter
      update_post_meta($attachment_id, '_wp_attachment_image_alt', $picture->alttext);

      return $attachment_id;
    }

    function import_gallery( $nextgen_gallery_id, $foogallery_title ) {
      //load the gallery and pictures
      $nextgen_gallery = $this->get_gallery( $nextgen_gallery_id );
      $nextgen_pictures = $this->get_gallery_images( $nextgen_gallery_id );

      $attachment_ids = array();

      //import all pictures from nextgen gallery into media attachments
      foreach ($nextgen_pictures as $picture) {
        $attachment_ids[] = $this->import_picture( $nextgen_gallery->path, $picture );
      }

      //create an empty foogallery
      $foogallery_args = array(
        'post_title' => $foogallery_title,
        'post_type' => FOOGALLERY_CPT_GALLERY
      );
      $foogallery_id = wp_insert_post( $foogallery_args );

      //link all attachments to foogallery
      add_post_meta($foogallery_id, FOOGALLERY_META_ATTACHMENTS, implode( $attachment_ids, ','), true);
      //set a default gallery template
      add_post_meta($foogallery_id, FOOGALLERY_META_TEMPLATE, foogallery_default_gallery_template(), true);

      //set the progress of the import for the gallery
      $this->set_import_progress( $nextgen_gallery_id,
        self::NEXTGEN_PROGRESS_COMPLETED,
        sprintf( __('Done! %d image(s) imported', 'foogallery'), count( $attachment_ids ) ),
        $foogallery_id );
    }
  }
}
