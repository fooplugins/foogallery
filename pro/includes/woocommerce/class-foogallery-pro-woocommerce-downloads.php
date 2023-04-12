<?php
/**
 * FooGallery class for WooCommerce Data Transfer Feature
 * Where data from the attachment is transferred to the cart and order items.
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_Pro_Woocommerce_Downloads' ) ) {
	/**
	 * Class FooGallery_Pro_Woocommerce_Downloads
	 */
	class FooGallery_Pro_Woocommerce_Downloads extends FooGallery_Pro_Woocommerce_Base {

		/**
		 * Constructor for the class
		 *
		 * Sets up all the appropriate hooks and actions
		 */
		public function __construct() {
            // Create downloadable permissions when an order is completed or processed.
            add_action( 'woocommerce_grant_product_download_permissions', array( $this, 'add_files_to_order' ) );

            // Adjust the downloads for an order item.
            add_filter( 'woocommerce_get_item_downloads', array( $this, 'get_item_downloads' ), 10, 3 );

            // Adjust the downloadable files for an order.
            add_filter( 'woocommerce_order_get_downloadable_items', array( $this, 'adjust_order_downloadable_items' ), 10, 2 );

            // Make the customer downloads work.
            add_filter( 'woocommerce_customer_available_downloads', array( $this, 'adjust_customer_available_downloads' ), 10, 2 );

            // Override some product properties, to make downloads work.
            add_filter( 'woocommerce_is_downloadable', array( $this, 'override_is_downloadable' ), 10, 2 );
            add_filter( 'woocommerce_product_file', array( $this, 'override_product_file' ), 10, 3 );
            add_filter( 'woocommerce_product_get_downloads', array( $this, 'override_get_downloads' ), 10, 2 );
            add_filter( 'woocommerce_product_variation_get_downloads', array( $this, 'override_get_downloads' ), 10, 2 );

            if ( is_admin() ) {
                // Add some fields to the product.
                add_action( 'foogallery_woocommerce_product_data_panels', array( $this, 'add_file_download_fields_to_product' ) );

                // Save the new fields against the product.
                add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ), 10, 2);

                // Add some fields to the variations.
                add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variation_settings_fields'), 10, 3 );

                // Save the new variation fields.
                add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_settings_fields' ) , 10, 2 );

                // Override the product name that is shown in the "Downloadable product permissions" metabox.
                add_filter( 'woocommerce_admin_download_permissions_title', array( $this, 'override_download_permissions_title' ), 10, 5 );

                // Override the filename that is shown in the "Downloadable product permissions" metabox.
                add_filter( 'woocommerce_product_file_download_path', array( $this, 'override_download_permissions_file_download_path' ), 10, 5 );

                // Override the variation title within the admin.
                add_filter( 'woocommerce_product_variation_title', array( $this, 'override_variation_title' ), 10, 4 );
            }
		}

        /**
         * Overrides the product variation title.
         *
         * @param $title
         * @param $product
         * @param $title_base
         * @param $title_suffix
         * @return mixed
         */
        function override_variation_title( $title, $product, $title_base, $title_suffix ) {
            if ( !empty( $title_suffix ) ) {
                $product_id = $product->get_parent_id();

                $enable_downloads = get_post_meta($product_id, '_foogallery_enable_downloads', true);
                if (!empty($enable_downloads)) {
                    return $title_suffix;
                }
            }

            return $title;
        }

        /**
         * Overrides the downloads for a product when user is trying to download a file.
         *
         * @param $value
         * @param $data
         * @return array|mixed
         */
        function override_get_downloads( $value, $data ) {
            if ( isset( $_GET['download_file'], $_GET['order'] ) && ( isset( $_GET['email'] ) || isset( $_GET['uid'] ) ) ) {
                // We are dealing with a file download request, so let's do some magic!

                $product_id  = absint( $_GET['download_file'] );
                $product     = wc_get_product( $product_id );
                $order_id    = wc_get_order_id_by_order_key( wc_clean( wp_unslash( $_GET['order'] ) ) ); // WPCS: input var ok, CSRF ok.
                $download_id = empty( $_GET['key'] ) ? '' : sanitize_text_field( wp_unslash( $_GET['key'] ) );

                if ( $this->is_downloadable( $product ) ) {
                    $foogallery_files = get_post_meta( $order_id, '_foogallery_files' );

                    if ( !empty( $foogallery_files ) ) {
                        foreach ($foogallery_files as $file) {
                            $attachment_id = $file['attachment_id'];
                            $gallery_id = $file['gallery_id'];
                            $item_id = $file['item_id'];
                            if ( $download_id === $file['download_id'] ) {

                                $download_object = new WC_Product_Download();

                                $download_object->set_id( $download_id );
                                $download_object->set_name( $this->get_product_name( sprintf( __( 'Image %s', 'foogallery' ), $attachment_id ), $gallery_id, $attachment_id ) );
                                $download_object->set_file( $this->get_download_filepath( $attachment_id, $order_id, $item_id ) );
                                $download_object->set_enabled( true );

                                $downloads[$download_id] = $download_object;
                                return $downloads;
                            }
                        }
                    }
                }
            }

            return $value;
        }

        /**
         * Common function to get the real download path.
         *
         * @param $attachment_id
         * @return false|string
         */
        private function get_download_filepath( $attachment_id, $order_id, $item_id ) {
            $width = $height = 0;
            $order = wc_get_order( $order_id );

            foreach ( $order->get_items() as $item ) {
                if ( $item->get_id() === $item_id ) {
                    $product = $item->get_product();
                    $width = intval( get_post_meta( $product->get_id(), '_foogallery_width', true ) );
                    $height = intval( get_post_meta( $product->get_id(), '_foogallery_height', true ) );
                    break;
                }
            }

            $filepath = wp_get_attachment_url( $attachment_id );
            $filepath_override = get_post_meta( $attachment_id, '_foogallery_download_file', true );
            if ( !empty( $filepath_override ) ) {
                $filepath = $filepath_override;
            }

            if ( $width > 0 && $height > 0 ) {
                $args = array(
                    'width' => $width,
                    'height' => $height,
                    'resize' => true
                );
                $filepath = foogallery_thumb( $filepath, $args );
            }

            if ( 'on' !== foogallery_get_setting('ecommerce_alternative_download_paths' ) ) {
                // Do some clever replacements to avoid woocommerce bug for downloads.
                if (strpos($filepath, get_site_url()) === 0) {
                    $filepath = str_replace(get_site_url(), '', $filepath);
                }
            }

            return apply_filters('foogallery_woocommerce_download_filepath', $filepath, $attachment_id, $order_id, $item_id );
        }

        /***
         * Overrides what the download permission title is within admin.
         *
         * @param $product_name
         * @param $product_id
         * @param $order_id
         * @param $order_key
         * @param $download_id
         * @return mixed
         */
        function override_download_permissions_title( $product_name, $product_id, $order_id, $order_key, $download_id ) {
            if ( $this->is_foogallery_download( $download_id ) && $product_id > 0 ) {
                $product = wc_get_product( $product_id );

                if ( $this->is_downloadable( $product ) ) {
                    $foogallery_files = get_post_meta( $order_id, '_foogallery_files' );

                    if ( !empty( $foogallery_files ) ) {
                        foreach ( $foogallery_files as $file ) {
                            $attachment_id = $file['attachment_id'];
                            $gallery_id = $file['gallery_id'];
                            if ( $download_id === $file['download_id'] ) {
                                return $this->get_product_name( sprintf( __( 'Image %s', 'foogallery' ), $attachment_id ), $gallery_id, $attachment_id );
                            }
                        }
                    }
                }
            }

            return $product_name;
        }

        /**
         * Overrides what the file
         *
         * @param $file_path
         * @param $product WC_Product
         * @param $download_id
         * @return mixed
         *
         */
        function override_download_permissions_file_download_path( $file_path, $product, $download_id ) {
            if ( $this->is_foogallery_download( $download_id ) ) {
                return $product->get_name();
            }

            return $file_path;
        }

        /**
         * Determines if the download is a special FooGallery download ID.
         * @param $download_id
         * @return bool
         */
        private function is_foogallery_download( $download_id ) {
            return strpos( $download_id, 'foogallery_file_download|' ) === 0;
        }

        /**
         * Extracts the itemId from the download_id
         * @param $download_id
         * @return int
         */
        private function extract_item_id( $download_id ) {
            return intval( str_replace( 'foogallery_file_download|', '', $download_id ) );
        }

        /**
         * Overrides the products is_downloadable field.
         *
         * @param $downloadable
         * @param $product WC_Product
         * @return bool|mixed
         */
        function override_is_downloadable( $downloadable, $product ) {
            if ( !$downloadable ) {
                return $this->is_downloadable( $product );
            }

            return $downloadable;
        }

        /**
         * Common function to determine if the product is downloadable.
         *
         * @param $product WC_Product
         * @return bool
         */
        private function is_downloadable( $product ) {
            if ( is_a( $product, 'WC_Product' ) ) {
                $enable_downloads = get_post_meta( $this->get_product_id( $product ), '_foogallery_enable_downloads', true );
                if ( !empty( $enable_downloads ) ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Returns the parent product ID if the current product is a variation.
         *
         * @param $product
         * @return int
         */
        private function get_product_id( $product ) {
            if ( is_a( $product, 'WC_Product' ) ) {
                if ( $product->is_type('variation') ) {
                    return $product->get_parent_id();
                }
            }
            return $product->get_id();
        }

        /**
         * @param $file
         * @param $product WC_Product
         * @param $download_id string
         * @return mixed
         */
        function override_product_file( $file, $product, $download_id ) {
            if ( $this->is_downloadable( $product ) ) {
                //return true;
                $download_object = new WC_Product_Download();

                $download_object->set_id( $download_id );
                $download_object->set_name( $this->get_filename() );
                $download_object->set_enabled( true );

                return $download_object;
            }
            return $file;
        }

        /**
         * Adds download permissions for an order.
         *
         * @param $order_id
         * @return void
         */
        function add_files_to_order( $order_id ) {
            // Load the order.
            $order = new WC_Order( $order_id );

            if ( !$order ) {
                return;
            }

            foreach( $order->get_items() as $item ) {
                if ( $item->is_type( 'line_item' ) ) {
                    $attachment_id = intval( $item->get_meta( '_foogallery_attachment_id' ) );
                    $gallery_id = intval( $item->get_meta( '_foogallery_id') );
                    if ( $attachment_id > 0 && $gallery_id > 0 ) {
                        $product = $item->get_product();

                        if ( $this->is_downloadable( $product ) ) {

                            $download_id = 'foogallery_file_download|' . $item->get_id();

                            $file = array(
                                'download_id' => $download_id,
                                'name' => $this->get_product_name( sprintf( __( 'Image %s', 'foogallery' ), $attachment_id ), $gallery_id, $attachment_id ),
                                'product_id' => $product->get_id(),
                                'item_id' => $item->get_id(),
                                'gallery_id' => $gallery_id,
                                'attachment_id' => $attachment_id
                            );

                            $product_id = $this->get_product_id( $product );
                            $download_limit = get_post_meta( $product_id, '_foogallery_download_limit', true );
                            $product->set_download_limit( $download_limit );
                            $download_expiry = get_post_meta( $product_id, '_foogallery_download_expires', true );
                            $product->set_download_expiry( $download_expiry );

                            wc_downloadable_file_permission( $download_id, $product, $order, $item->get_quantity(), $item );
                            add_post_meta( $order_id, '_foogallery_files', $file );
                        }
                    }
                }
            }
        }

        /**
         * Get downloads for the order item product.
         *
         * @param $files array
         * @param $order_item_product WC_Order_Item_Product
         * @param $order WC_Order
         * @return array
         */
        function get_item_downloads( $files, $order_item_product, $order ) {

            // Check we have a downloadable file for the Order Item.
            if ( is_array( $files ) && count( $files ) > 0 ) {

                $product = $order_item_product->get_product();
                $item_id = $order_item_product->get_id();
                $product_id = $order_item_product->get_variation_id() ? $order_item_product->get_variation_id() : $order_item_product->get_product_id();

                if ( $product && $order && $product->is_downloadable() && $order->is_download_permitted() ) {

                    $foogallery_files = get_post_meta( $order->get_id(), '_foogallery_files' );

                    if ( !empty( $foogallery_files ) ) {
                        foreach ( $foogallery_files as $file ) {
                            if ( $product_id === $file['product_id'] &&
                                $item_id === $file['item_id'] ) {

                                $download = null;

                                // Get the correct file.
                                foreach ( $files as $download_file ) {
                                    $file_download_item_id = $this->extract_item_id( $download_file['id'] );
                                    if ( $file_download_item_id === $item_id ) {
                                        $download = $download_file;
                                    }
                                }

                                if ( !is_null( $download ) ) {

                                    $download_id = $download['id'];

                                    $download['name'] = $this->get_filename();
                                    $download['download_url'] = add_query_arg(
                                        array(
                                            'item_id' => $item_id
                                        ),
                                        $download['download_url']
                                    );

                                    $files = array();
                                    $files[$download_id] = $download;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            return $files;
        }

        /**
         * Gets the name of the downloadable file that is shown in the account section.
         *
         * @return mixed
         */
        function get_filename() {
            return foogallery_get_setting( 'woocommerce_frontend_filename', __( 'Image File', 'foogallery' ) );
        }

        /**
         * Adjust the order downloads, so that the product names are matched to the correct image.
         *
         * @param $downloads
         * @param $order
         * @return array
         */
        function adjust_order_downloadable_items( $downloads, $order ) {

            $foogallery_files = get_post_meta( $order->get_id(), '_foogallery_files' );

            if ( !empty( $foogallery_files ) ) {
                foreach ($downloads as &$download) {
                    $product_id = intval( $download['product_id'] );
                    $download_url_components = parse_url( $download['download_url'] );
                    parse_str( $download_url_components['query'], $params );
                    $item_id = intval( $params['item_id'] );
                    foreach ( $foogallery_files as $file ) {
                        if ( $product_id === intval( $file['product_id'] ) &&
                            $item_id === $file['item_id'] ) {

                            // We have a matching attachment for the download.

                            $gallery_id = intval( $file['gallery_id'] );
                            $attachment_id = intval( $file['attachment_id'] );
                            $download['product_name'] = $this->get_product_name( sprintf( __( 'Image %s', 'foogallery' ), $attachment_id ), $gallery_id, $attachment_id );
                        }
                    }
                }
            }

            return $downloads;
        }

        /**
         * Adjusts the downloads that the user sees within his account.
         *
         * @param $downloads
         * @param $customer_id
         * @return array|mixed
         */
        function adjust_customer_available_downloads( $downloads, $customer_id ) {
            if ( is_array( $downloads ) && count( $downloads ) > 0 ) {

                $linked_files = array();

                foreach ( $downloads as &$download ) {
                    $order_id = intval( $download['order_id'] );
                    $foogallery_files = get_post_meta( $order_id, '_foogallery_files' );

                    if ( !empty( $foogallery_files ) ) {
                        foreach ($foogallery_files as $file) {
                            $attachment_id = $file['attachment_id'];
                            $gallery_id = $file['gallery_id'];
                            $product_id = $file['product_id'];
                            $item_id = $file['item_id'];
                            $download_id = $file['download_id'];

                            $file_key = $order_id . '|'. $product_id . '|' . $download_id . '|' . $item_id . '|' . $gallery_id . '|' . $attachment_id;

                            if ( array_key_exists( $file_key, $linked_files ) ) {
                                // We have already linked this file, so move on.
                                continue;
                            }

                            if ( $download_id === $download['download_id'] &&
                                $product_id === $download['product_id'] ) {

                                // We have found a match, so let's update the download with more info.
                                $download['product_name'] = $this->get_product_name( sprintf( __( 'Image %s', 'foogallery' ), $attachment_id ), $gallery_id, $attachment_id );
                                $download['download_name'] = $this->get_filename();

                                $linked_files[$file_key] = $file;
                                break;
                            }
                        }
                    }
                }
            }

            return $downloads;
        }

        /**
         * Add file download fields to the product
         *
         * @return void
         */
        public function add_file_download_fields_to_product() {
            ?>
            <p>
                <?php _e('You can enable downloads, which will allow the customer to download the images that they purchased. ', 'foogallery '); ?>
            </p>
            <?php

            woocommerce_wp_checkbox( array(
                'id'      => 'foogallery_enable_downloads',
                'value'   => get_post_meta( get_the_ID(), '_foogallery_enable_downloads', true ),
                'label'   => __( 'Enable Downloads', 'foogallery' ),
                'desc_tip' => true,
                'description' => __( 'If this is enabled, files will be downloadable when ordered.', 'foogallery' ),
            ) );

            woocommerce_wp_text_input( array(
                'id'      => 'foogallery_download_limit',
                'value'   => get_post_meta( get_the_ID(), '_foogallery_download_limit', true ),
                'label'   => __( 'Download Limit', 'foogallery' ),
                'placeholder' => __( 'Unlimited', 'foogallery' ),
                'type'    => 'number',
                'desc_tip' => true,
                'description' => __( 'Leave blank for unlimited downloads.', 'foogallery' ),
            ) );

            woocommerce_wp_text_input( array(
                'id'      => 'foogallery_download_expires',
                'value'   => get_post_meta( get_the_ID(), '_foogallery_download_expires', true ),
                'label'   => __( 'Download Expiry', 'foogallery' ),
                'placeholder' => __( 'Never', 'foogallery' ),
                'type'    => 'number',
                'desc_tip' => true,
                'description' => __( 'The number of days before a download expires, or leave blank.', 'foogallery' ),
            ) );
        }

        /**
         * Save the file download product meta
         *
         * @param $id
         * @param $post
         *
         * @return void
         */
        public function save_product_meta( $id, $post ){
            if ( isset( $_POST['foogallery_enable_downloads'] ) ) {
                $foogallery_enable_downloads = wc_clean( $_POST['foogallery_enable_downloads'] );

                if ( !empty( $foogallery_enable_downloads ) ) {
                    update_post_meta( $id, '_foogallery_enable_downloads', $foogallery_enable_downloads );
                }
            } else {
                delete_post_meta( $id, '_foogallery_enable_downloads' );
            }

            if ( isset( $_POST['foogallery_download_limit'] ) ) {
                $download_limit = wc_clean( $_POST['foogallery_download_limit'] );

                if ( !empty( $download_limit ) ) {
                    update_post_meta( $id, '_foogallery_download_limit', $download_limit );
                } else {
                    delete_post_meta( $id, '_foogallery_download_limit' );
                }
            }

            if ( isset( $_POST['foogallery_download_expires'] ) ) {
                $download_expires = wc_clean( $_POST['foogallery_download_expires'] );

                if ( !empty( $download_expires ) ) {
                    update_post_meta( $id, '_foogallery_download_expires', $download_expires );
                } else {
                    delete_post_meta( $id, '_foogallery_download_expires' );
                }
            }
        }

        /**
         * Add fields to each variation for download dimensions.
         *
         * @param $loop
         * @param $variation_data
         * @param $variation
         * @return void
         */
        public function variation_settings_fields( $loop, $variation_data, $variation ) {
            woocommerce_wp_text_input(
                array(
                    'id'          => 'foogallery_width[' . $variation->ID . ']',
                    'label'       => __( 'Download Width', 'texdomain' ),
                    'placeholder' => '',
                    'desc_tip'    => 'true',
                    'description' => __( 'Restrict the file\'s download width. Leave blank to use the full size image.', 'texdomain' ),
                    'value'       => get_post_meta( $variation->ID, '_foogallery_width', true )
                )
            );

            woocommerce_wp_text_input(
                array(
                    'id'          => 'foogallery_height[' . $variation->ID . ']',
                    'label'       => __( 'Download Height', 'texdomain' ),
                    'placeholder' => '',
                    'desc_tip'    => 'true',
                    'description' => __( 'Restrict the file\'s download height. Leave blank to use the full size image.', 'texdomain' ),
                    'value'       => get_post_meta( $variation->ID, '_foogallery_height', true )
                )
            );
        }

        /**
         * Save new fields for variations
         *
         */
        public function save_variation_settings_fields( $post_id ) {

            if ( isset( $_POST['foogallery_height'][ $post_id ] ) ) {
                $foogallery_height = wc_clean( $_POST['foogallery_height'][ $post_id ] );

                if ( !empty( $foogallery_height ) ) {
                    update_post_meta( $post_id, '_foogallery_height', $foogallery_height );
                } else {
                    delete_post_meta( $post_id, '_foogallery_height' );
                }
            }

            if ( isset( $_POST['foogallery_width'][ $post_id ] ) ) {
                $foogallery_width = wc_clean( $_POST['foogallery_width'][ $post_id ] );

                if ( !empty( $foogallery_width ) ) {
                    update_post_meta( $post_id, '_foogallery_width', $foogallery_width );
                } else {
                    delete_post_meta( $post_id, '_foogallery_width' );
                }
            }
        }
	}
}