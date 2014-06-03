<?php
/**
 * @TODO
 */
if (!class_exists('FooGallery_Nextgen_Gallery_Importer_Extension')) {

    require_once 'class-nextgen-helper.php';

    class FooGallery_Nextgen_Gallery_Importer_Extension {

        private $nextgen;

        function __construct() {
          $nextgen = new FooGallery_NextGen_Helper();



          //only do anything if NextGen is installed
          if ( $nextgen->is_nextgen_installed() ) {
            //hook into the foogallery menu
            add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );
            add_action( 'foogallery_extension_activated-nextgen',  array( $this, 'add_menu' ) );
          }
        }

        function add_menu() {
          $parent_slug = foogallery_admin_menu_parent_slug();
          add_submenu_page( $parent_slug,
            __('NextGen Gallery Importer', 'foogallery'),
            __('NextGen Importer', 'foogallery'),
            'manage_options',
            'foogallery-nextgen-importer',
            array($this, 'render_view') );
        }

        function render_view() {
          require_once 'view-importer.php';
        }
    }
}
