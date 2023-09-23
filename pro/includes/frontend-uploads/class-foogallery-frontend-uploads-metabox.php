<?php

class FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes extends FooGallery_Admin_Gallery_MetaBoxes {

    private $gallery_id;

    public function __construct() {
        parent::__construct();
        $this->gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
    }

    public function add_meta_boxes_to_gallery($post) {
        parent::add_meta_boxes_to_gallery($post);

        add_meta_box(
            'custom_metabox_id',
            __('Front End Upload', 'foogallery'),
            array($this, 'render_frontend_upload_metabox'),
            FOOGALLERY_CPT_GALLERY,
            'normal',
            'low'
        );
    }

    public function render_frontend_upload_metabox($post) {
        // You can now access $this->gallery_id to use the gallery ID in your metabox.
    }
}



$custom_foogallery_meta_boxes = new FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes();
