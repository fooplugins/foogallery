<?php
/**
 * FooGallery Pro Ribbons Class
 */
if ( ! class_exists( 'FooGallery_Pro_Ribbons' ) ) {

	class FooGallery_Pro_Ribbons {

		function __construct() {
			if ( is_admin() ) {
				// Add extra fields to the templates.
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_ribbon_fields' ), 29, 2 );

				// Add attachment custom fields.
				add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ), 40 );

				// Add some fields to the woocommerce product.
				add_action( 'foogallery_woocommerce_product_data_panels', array( $this, 'add_ribbon_fields_to_product' ) );

				// Save product meta.
				add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ), 10, 2 );
			}

			// Load ribbon meta after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_ribbon_meta' ), 10, 2 );

			// Append ribbon HTML to the gallery output.
			add_filter( 'foogallery_attachment_html_item_opening', array( $this, 'add_ribbon_html' ), 10, 3 );

			// Add ribbon data to the json output.
			add_filter( 'foogallery_build_attachment_json', array( $this, 'add_ribbon_to_json' ), 20, 6 );

			// Override the ribbon based on product metadata.
			add_filter( 'foogallery_datasource_woocommerce_build_attachment', array( $this, 'override_ribbon_from_product' ), 20, 2 );

            // Check if we need to enqueue dashicons.
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dashicons' ) );
		}

        /**
         * Enqueues dashicons CSS if they have been used in the ribbon html.
         *
         * @return void
         */
        public function enqueue_dashicons() {
            global $foogallery_ribbons_uses_dashicons;

            if ( true === $foogallery_ribbons_uses_dashicons ) {
                wp_enqueue_style('dashicons');
            }
        }

		/**
		 * Save the ribbon product meta
		 *
		 * @param $id
		 * @param $post
		 *
		 * @return void
		 */
		public function save_product_meta( $id, $post ){
			if ( isset( $_POST['foogallery_ribbon'] ) ) {
				$override_ribbon_type = wc_clean( $_POST['foogallery_ribbon'] );

				if ( !empty( $override_ribbon_type ) ) {
					update_post_meta( $id, '_foogallery_ribbon', $override_ribbon_type );
				} else {
					delete_post_meta( $id, '_foogallery_ribbon' );
				}
			}

			if ( isset( $_POST['foogallery_ribbon_text'] ) ) {
				$override_ribbon_text = wc_clean( $_POST['foogallery_ribbon_text'] );

				if ( !empty( $override_ribbon_text ) ) {
					update_post_meta( $id, '_foogallery_ribbon_text', $override_ribbon_text );
				} else {
					delete_post_meta( $id, '_foogallery_ribbon_text' );
				}
			}
		}

		/**
		 * Override the ribbon based on product metadata.
		 *
		 * @param $attachment
		 * @param $product
		 *
		 * @return FooGalleryAttachment
		 */
		public function override_ribbon_from_product( $attachment, $product ) {

			$override_ribbon_type = get_post_meta( $product->get_id(), '_foogallery_ribbon', true );

			if ( ! empty( $override_ribbon_type ) ) {
				$attachment->ribbon_type = $override_ribbon_type;
			}

			$override_ribbon_text = get_post_meta( $product->get_id(), '_foogallery_ribbon_text', true );

			if ( ! empty( $override_ribbon_text ) ) {
				$attachment->ribbon_text = $override_ribbon_text;
			}

			return $attachment;
		}

		/**
		 * Add ribbon fields to the product
		 *
		 * @return void
		 */
		public function add_ribbon_fields_to_product() {
			?>
			<p>
				<?php _e('By default, products that are on sale, will show a colorful ribbon to attract the visitors attention. You can override the default ribbon type and text for this product.', 'foogallery '); ?>
			</p>
			<?php

			$ribbon_choices = self::get_ribbon_choices();
			$ribbon_choices[''] = __( 'Do not override', 'foogallery' );

			woocommerce_wp_select( array(
				'id'          => 'foogallery_ribbon',
				'value'       => get_post_meta( get_the_ID(), '_foogallery_ribbon', true ),
				'label'       => __( 'Override Ribbon', 'foogallery' ),
				'options'     => $ribbon_choices,
			) );

			woocommerce_wp_text_input( array(
				'id'                => 'foogallery_ribbon_text',
				'value'             => get_post_meta( get_the_ID(), '_foogallery_ribbon_text', true ),
				'label'             => __( 'Override Ribbon Text', 'foogallery' ),
			) );
		}

		/**
		 * Loads any extra ribbon data for an attachment.
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function load_ribbon_meta( $foogallery_attachment, $post ) {
			$ribbon_type = get_post_meta( $post->ID, '_foogallery_ribbon', true );
			if ( !empty( $ribbon_type ) ) {
				$foogallery_attachment->ribbon_type = $ribbon_type;
				$foogallery_attachment->ribbon_text = get_post_meta( $post->ID, '_foogallery_ribbon_text', true );
				$foogallery_attachment->ribbon_override = true;
			}
		}

		/** 
         * Checking is ribbons are hidden
         *  
         * @return Boolean    
         */ 
        function is_ribbons_hidden() {
        	if ( !foogallery_current_gallery_has_cached_value('ribbons_hide') ) {

				$ribbons_hidden = 'hidden' === foogallery_gallery_template_setting( 'ribbons_hide' );

        		//set the toggle
		        foogallery_current_gallery_set_cached_value( 'ribbons_hide', $ribbons_hidden );
	        }

        	return foogallery_current_gallery_get_cached_value( 'ribbons_hide' );
        }

		function class_ribbon_data() {
			if ( !foogallery_current_gallery_has_cached_value('class_ribbon_data') ) {

				$class_ribbon_data = array(
					'type' => foogallery_gallery_template_setting( 'add_class_ribbon' ),
					'text' => foogallery_gallery_template_setting( 'class_ribbon_text' ),
					'rule' => foogallery_gallery_template_setting( 'class_ribbon_rule' ),
				);

        		//set the data
		        foogallery_current_gallery_set_cached_value( 'class_ribbon_data', $class_ribbon_data );
	        }

        	return foogallery_current_gallery_get_cached_value( 'class_ribbon_data' );
		}

		/**
		 * Builds up ribbon HTML and adds it to the output.
		 *
		 * @param $html
		 * @param $foogallery_attachment
		 * @param $args
		 *
		 * @return mixed
		 */
		public function add_ribbon_html( $html, $foogallery_attachment, $args ) {
			if ( $this->is_ribbons_hidden() ) {
				return $html;
			}

			//Only add a class ribbon if we don't have a ribbon already, and we have a class on the attachment
			if ( !isset( $foogallery_attachment->ribbon_type ) && !empty($foogallery_attachment->custom_class ) ) {

				//check if we need to add a class ribbon
				$class_ribbon_data = $this->class_ribbon_data();
				if ( '' !== $class_ribbon_data['type'] ) {
					$class_ribbon_rule = $class_ribbon_data['rule'];
					$class_ribbon_text = $class_ribbon_data['text'];
					if ( '' !== $class_ribbon_rule && '' !== $class_ribbon_text ) {
						//only apply the ribbon if the class rule is found!
						if ( strpos( $foogallery_attachment->custom_class, $class_ribbon_rule ) !== false ) {
							$foogallery_attachment->ribbon_type = $class_ribbon_data['type'];
							$foogallery_attachment->ribbon_text = esc_html( $class_ribbon_text );
						}
					}
				}
			}
			
			if ( isset( $foogallery_attachment->ribbon_type ) && isset( $foogallery_attachment->ribbon_text ) ) {
				//Add the ribbon HTML!!!
				$ribbon_html = '<div class="' . $foogallery_attachment->ribbon_type . '"><span>' . $this->generate_ribbon_html( $foogallery_attachment->ribbon_text ) . '</span></div>';
				$html = str_replace( '<figure class=',  $ribbon_html . '<figure class=', $html );
			}
			return $html;
		}

        /**
         * Generates the HTML for the ribbon text.
         *
         * @param $ribbon_text
         * @return array|string|string[]|null
         */
        private function generate_ribbon_html( $ribbon_text ) {
            global $foogallery_ribbons_uses_dashicons;

            $replacements = array(
                '/icon-star/' => 'icon-star-filled',
                '/icon-money/' => 'icon-money-alt',
                '/icon-(\S+)/' => '<i class="dashicons dashicons-$1"></i>',
            );

            $ribbon_text = esc_html( $ribbon_text );

            $ribbon_text = preg_replace( array_keys( $replacements ), array_values( $replacements ), $ribbon_text );

            if ( strpos( $ribbon_text, 'dashicons') !== false ) {
                $foogallery_ribbons_uses_dashicons = true;
            }

            return $ribbon_text;
        }

		/**
		 * Add the ribbon data to the json object.
		 *
		 * @param StdClass $json_object
		 * @param FooGalleryAttachment $foogallery_attachment
		 * @param array $args
		 * @param array $anchor_attributes
		 * @param array $image_attributes
		 * @param array $captions
		 *
		 * @return mixed
		 */
		public function add_ribbon_to_json(  $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions ) {
			if ( $this->is_ribbons_hidden() ) {
				return $json_object;
			}
			
			if ( isset( $foogallery_attachment->ribbon_type ) && isset( $foogallery_attachment->ribbon_text ) ) {
				$json_object->ribbon = array(
					'type' => $foogallery_attachment->ribbon_type,
                    'text' => $this->generate_ribbon_html( $foogallery_attachment->ribbon_text )
				);
			}

			return $json_object;
		}

		/**
		 * Add Ribbon specific custom fields.
		 *
		 * @uses "foogallery_attachment_custom_fields" filter
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public function attachment_custom_fields( $fields ) {
			$fields['foogallery_ribbon']  = array(
				'label'       => __( 'Ribbon Type', 'foogallery' ),
				'input'       => 'select',
				'application' => 'image/foogallery',
				'options'     => self::get_ribbon_choices(),
			);

			$fields['foogallery_ribbon_text']  = array(
				'label'       => __( 'Ribbon Text', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);

			return $fields;
		}

		/**
		 * Returns the list of ribbon choices.
		 *
		 * @return array
		 */
		public static function get_ribbon_choices() {
			return array(
				''            => __( 'None', 'foogallery' ),
				'fg-ribbon-5' => __( 'Type 1 (top-right, diagonal, green)', 'foogallery' ),
				'fg-ribbon-3' => __( 'Type 2 (top-left, small, blue)', 'foogallery' ),
				'fg-ribbon-4' => __( 'Type 3 (top, full-width, yellow)', 'foogallery' ),
				'fg-ribbon-6' => __( 'Type 4 (top-right, rounded, red)', 'foogallery' ),
				'fg-ribbon-2' => __( 'Type 5 (top-left, medium, pink)', 'foogallery' ),
				'fg-ribbon-1' => __( 'Type 6 (top-left, vertical, orange)', 'foogallery' ),
                'fg-ribbon-7' => __( 'Type 7 (bottom, full-width, grey)', 'foogallery' ),
			);
		}

		/**
		 * Add button fields to all gallery templates
		 *
		 * @param array  $fields The fields to override.
		 * @param string $template The gallery template.
		 *
		 * @return array
		 */
		public function add_ribbon_fields( $fields, $template ) {

			$new_fields = array();

			$new_fields[] = array(
				'id'      => 'ribbons_help',
				'title'   => __( 'Want to add custom ribbons?', 'foogallery' ),
				'desc'    => __( 'You can add a custom ribbon to each item in your gallery within the advanced attachments modal, under the "Ecommerce" tab. To open the advanced attachments modal, go to "Manage Items" and then click on the the "Edit Info" icon.', 'foogallery' ),
				'section' => __( 'Ecommerce', 'foogallery' ),
				'subsection' => array( 'ecommerce-ribbons' => __( 'Ribbons', 'foogallery' ) ),
				'type'    => 'help',
			);

			$new_fields[] = array(
				'id'       => 'ribbons_hide',
				'title'    => __( 'Hide All Ribbons', 'foogallery' ),
				'desc'     => __( 'You can choose to hide all ribbons for the gallery. This will hide all ribbons, including custom ribbons and WooCommerce ribbons.', 'foogallery' ),
				'section'  => __( 'Ecommerce', 'foogallery' ),
				'subsection' => array( 'ecommerce-ribbons' => __( 'Ribbons', 'foogallery' ) ),
				'type'     => 'radio',
				'default'  => '',
				'choices'  => array(
					'' => __( 'Shown', 'foogallery' ),
					'hidden' => __( 'Hidden', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
				),
			);

			$new_fields[] = array(
				'id'       => 'add_class_ribbon',
				'title'    => __( 'Add Custom Class Ribbon', 'foogallery' ),
				'desc'     => __( 'Add a custom ribbon based on the custom class added to the attachment.', 'foogallery' ),
				'section'  => __( 'Ecommerce', 'foogallery' ),
				'subsection' => array( 'ecommerce-ribbons' => __( 'Ribbons', 'foogallery' ) ),
				'type'     => 'select',
				'default'  => '',
				'choices'  => self::get_ribbon_choices(),
				'row_data' => array(
					'data-foogallery-change-selector' => 'select',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'select :selected',
				),
			);

			$new_fields[] = array(
				'id'       => 'class_ribbon_text',
				'title'    => __( 'Custom Class Ribbon Text', 'foogallery' ),
				'desc'     => __( 'Text for the custom class ribbon.', 'foogallery' ),
				'section'  => __( 'Ecommerce', 'foogallery' ),
				'subsection' => array( 'ecommerce-ribbons' => __( 'Ribbons', 'foogallery' ) ),
				'type'     => 'text',
				'default'  => 'icon-star',
				'row_data' => array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'add_class_ribbon',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => '',
					'data-foogallery-change-selector'          => 'input',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				),
			);

			$new_fields[] = array(
				'id'       => 'class_ribbon_rule',
				'title'    => __( 'Custom Class Ribbon Rule', 'foogallery' ),
				'desc'     => __( 'Which custom class should trigger the ribbon.', 'foogallery' ),
				'section'  => __( 'Ecommerce', 'foogallery' ),
				'subsection' => array( 'ecommerce-ribbons' => __( 'Ribbons', 'foogallery' ) ),
				'type'     => 'text',
				'default'  => 'featured',
				'row_data' => array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'add_class_ribbon',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => '',
					'data-foogallery-change-selector'          => 'input',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				),
			);

			// find the index of the advanced section.
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}
	}
}