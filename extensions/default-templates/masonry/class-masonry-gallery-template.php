<?php

if ( !class_exists( 'FooGallery_Masonry_Gallery_Template' ) ) {

	define('FOOGALLERY_MASONRY_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Masonry_Gallery_Template {

		const template_id = 'masonry';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_action( 'foogallery_enqueue_preview_dependencies', array( $this, 'enqueue_preview_dependencies' ) );

			add_filter( 'foogallery_located_template-masonry', array( $this, 'enqueue_dependencies' ) );

			add_filter( 'foogallery_template_thumbnail_dimensions-masonry', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//add the data options needed for masonry
			add_filter( 'foogallery_build_container_data_options-masonry', array( $this, 'add_masonry_options' ), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-masonry', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-masonry', array( $this, 'build_gallery_template_arguments' ) );

            //add extra fields to the templates
            add_filter( 'foogallery_override_gallery_template_fields-masonry', array( $this, 'add_masonry_fields' ), 10, 2 );

			//remove the captions if the captions are below thumbs
			add_filter( 'foogallery_build_attachment_html_caption', array( $this, 'remove_captions' ), 10, 3 );

			//add a style block for the gallery based on the field settings
			add_action( 'foogallery_loaded_template_before', array( $this, 'add_style_block' ), 10, 1 );

			add_filter( 'foogallery_build_class_attribute', array( $this, 'override_class_attributes' ), 99, 2 );
        }

		/**
		 * Override the classes for the layout
		 *
		 * @param $classes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function override_class_attributes( $classes, $gallery ) {
			if ( self::template_id === $gallery->gallery_template ) {
				$classes[] = 'fg-' . foogallery_gallery_template_setting( 'layout', 'fixed' );
			}

			return $classes;
		}

		/**
		 * Add a style block based on the field settings
		 *
		 * @param $gallery FooGallery
		 */
		function add_style_block( $gallery ) {
			if ( self::template_id !== $gallery->gallery_template ) {
				return;
			}

			$id = $gallery->container_id();
			$layout = foogallery_gallery_template_setting( 'layout', 'fixed' );

			//get out early if the layout is not fixed
			if ( 'fixed' !== $layout ) {
				return;
			}

			$thumbnail_width = intval( foogallery_gallery_template_setting( 'thumbnail_width', 250 ) );
			$gutter_width = intval( foogallery_gallery_template_setting( 'gutter_width', 10 ) );

			?>
			<style>
                #<?php echo $id; ?>.fg-masonry .fg-item {
                    width: <?php echo $thumbnail_width; ?>px;
                    margin-right: <?php echo $gutter_width; ?>px;
                    margin-bottom: <?php echo $gutter_width; ?>px;
                }
			</style>
			<?php
		}


		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 * @param $extensions
		 *
		 * @return array
		 */
		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		/**
		 * Add our gallery template to the list of templates available for every gallery
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
                'slug'        => self::template_id,
                'name'        => __( 'Masonry Image Gallery', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-masonry',
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
                'fields'	  => array(
                    array(
                        'id'      => 'thumbnail_width',
                        'title'   => __( 'Thumb Width', 'foogallery' ),
                        'desc'    => __( 'Choose the width of your thumbnails. Thumbnails will be generated on the fly and cached once generated', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 250,
                        'step'    => '1',
                        'min'     => '0',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'layout',
                        'title'   => __( 'Masonry Layout', 'foogallery' ),
                        'desc'    => __( 'Choose a fixed width thumb layout, or responsive columns.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'radio',
                        'choices' => array(
                            'fixed'  => __( 'Fixed Width', 'foogallery' ),
                            'col6'   => __( '6 Columns', 'foogallery' ),
                            'col5'   => __( '5 Columns', 'foogallery' ),
                            'col4'   => __( '4 Columns', 'foogallery' ),
                            'col3'   => __( '3 Columns', 'foogallery' ),
                            'col2'   => __( '2 Columns', 'foogallery' ),
                        ),
                        'default' => 'fixed',
                        'row_data'=> array(
                            'data-foogallery-change-selector' => 'input:radio',
                            'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
                        )
                    ),
                    array(
	                    'id'      => 'horizontal',
	                    'title'   => __( 'Horizontal Layout', 'foogallery' ),
	                    'desc'    => __( 'You can choose to lay out items to (mostly) maintain horizontal left-to-right order.', 'foogallery' ),
	                    'section' => __( 'General', 'foogallery' ),
	                    'type'    => 'radio',
	                    'choices' => array(
		                    ''  => __( 'Disabled', 'foogallery' ),
		                    'yes'   => __( 'Try to maintain lef-to-right order', 'foogallery' ),
	                    ),
	                    'default' => '',
	                    'row_data'=> array(
		                    'data-foogallery-change-selector' => 'input:radio',
		                    'data-foogallery-value-selector' => 'input:checked',
		                    'data-foogallery-preview' => 'shortcode'
	                    )
                    ),
                    array(
                        'id'      => 'gutter_width',
                        'title'   => __( 'Gutter Width', 'foogallery' ),
                        'desc'    => __( 'The spacing between your thumbnails. Only applicable when using a fixed layout!', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 10,
                        'step'    => '1',
                        'min'     => '0',
                        'row_data'=> array(
                            'data-foogallery-hidden' => true,
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
                            'data-foogallery-show-when-field' => 'layout',
                            'data-foogallery-show-when-field-value' => 'fixed',
							'data-foogallery-preview' => 'shortcode',
                        )
                    ),
                    array(
                        'id'      => 'gutter_percent',
                        'title'   => __( 'Gutter Size', 'foogallery' ),
                        'desc'    => __( 'Choose a gutter size when using responsive columns.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'radio',
                        'choices' => array(
                            'fg-gutter-none'   => __( 'No Gutter', 'foogallery' ),
                            ''  => __( 'Normal Size Gutter', 'foogallery' ),
                            'fg-gutter-large'   => __( 'Larger Gutter', 'foogallery' )
                        ),
                        'default' => '',
                        'row_data'=> array(
                            'data-foogallery-hidden' => true,
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector' => 'input:checked',
                            'data-foogallery-show-when-field' => 'layout',
                            'data-foogallery-show-when-field-operator' => '!==',
                            'data-foogallery-show-when-field-value' => 'fixed',
							'data-foogallery-preview' => 'shortcode'
                        )
                    ),
                    array(
                        'id'      => 'alignment',
                        'title'   => __( 'Alignment', 'foogallery' ),
                        'desc'    => __( 'You can choose to center align your images or leave them at the default (left). Only applicable when using a fixed layout!', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
                        'choices' => array(
                            ''  => __( 'Left', 'foogallery' ),
                            'fg-center'   => __( 'Center', 'foogallery' )
                        ),
                        'default' => 'fg-center',
						'row_data'=> array(
							'data-foogallery-hidden' => true,
							'data-foogallery-show-when-field' => 'layout',
							'data-foogallery-show-when-field-value' => 'fixed',
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Thumbnail Link', 'foogallery' ),
                        'default' => 'image' ,
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything', 'foogallery' ),
                    ),
                    array(
                        'id'      => 'lightbox',
                        'type'    => 'lightbox',
                    ),
                ),
			);


			return $gallery_templates;
		}

		/**
		 * Enqueue scripts that the masonry gallery template relies on
		 */
		function enqueue_preview_dependencies() {
			wp_enqueue_script( 'masonry' );
		}

		/**
		 * Enqueue scripts that the masonry gallery template relies on
		 */
		function enqueue_dependencies() {
			wp_enqueue_script( 'masonry' );

			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script( array('jquery', 'masonry' ) );
		}

		/**
		 * Get the thumb dimensions arguments saved for the gallery for this gallery template
		 *
		 * @param array $dimensions
		 * @param FooGallery $foogallery
		 *
		 * @return mixed
		 */
		function get_thumbnail_dimensions( $dimensions, $foogallery ) {
			$width = $foogallery->get_meta( 'masonry_thumbnail_width', false );
			return array(
				'height' => 0,
				'width'  => intval( $width ),
				'crop'   => false
			);
		}

		/**
		 * Add the required masonry options if needed
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_masonry_options($options, $gallery, $attributes) {
			$layout = foogallery_gallery_template_setting( 'layout', 'fixed' );
			if ( 'fixed' === $layout ) {
				$width = foogallery_gallery_template_setting( 'thumbnail_width', '250' );
				$gutter_width = foogallery_gallery_template_setting( 'gutter_width', '10' );
				$options['template']['columnWidth'] = intval($width);
				$options['template']['gutter'] = intval($gutter_width);
			}
			$horizontal = foogallery_gallery_template_setting( 'horizontal', '' );
			if ( 'yes' === $horizontal ) {
				$options['template']['horizontalOrder'] = true;
			}
			return $options;
		}

		/**
		 * Builds thumb dimensions from arguments
		 *
		 * @param array $dimensions
		 * @param array $arguments
		 *
		 * @return mixed
		 */
		function build_thumbnail_dimensions_from_arguments( $dimensions, $arguments ) {
            if ( array_key_exists( 'thumbnail_width', $arguments) ) {
                return array(
                    'height' => 0,
                    'width' => intval($arguments['thumbnail_width']),
                    'crop' => false
                );
            }
            return null;
		}

        /**
         * Build up the arguments needed for rendering this gallery template
         *
         * @param $args
         * @return array
         */
        function build_gallery_template_arguments( $args ) {
            $args = array(
                'width' => foogallery_gallery_template_setting( 'thumbnail_width', '250' ),
                'link' => foogallery_gallery_template_setting( 'thumbnail_link', 'image' ),
                'crop' => false
            );

            return $args;
        }

        /**
         * Add masonry-specific fields to the gallery template
         *
         * @uses "foogallery_override_gallery_template_fields"
         * @param $fields
         * @param $template
         *
         * @return array
         */
        function add_masonry_fields( $fields, $template ) {
            //update specific fields
            foreach ($fields as &$field) {
                if ( 'hover_effect_caption_visibility' === $field['id'] ) {
                	//add a new choice for captions to show below the thumbs
                    $field['choices']['fg-captions-bottom'] = __( 'Below Thumbnail', 'foogallery' );
	                $field['default'] = 'fg-captions-bottom';
                } else if ( 'theme' === $field['id'] ) {
	                $field['default'] = 'fg-dark';
	                $field['choices'] = array(
		                'fg-light'  => __( 'Light', 'foogallery' ),
		                'fg-dark'   => __( 'Dark', 'foogallery' ),
		                'fg-transparent' => __( 'Transparent', 'foogallery' ),
		                'fg-custom' => __( 'Custom', 'foogallery' )
	                );
                } else if ( 'drop_shadow' === $field['id'] ) {
	                $field['default'] = 'fg-shadow-small';
                } else if ( 'hover_effect_icon' === $field['id'] ) {
	                $field['default'] = 'fg-hover-plus';
                }
            }

            return $fields;
        }

        function remove_captions( $captions, $foogallery_attachment, $args ) {
			global $current_foogallery_template;

        	//check if masonry
			if ( 'masonry' === $current_foogallery_template ) {

				$hover_effect_caption_visibility = foogallery_gallery_template_setting( 'hover_effect_caption_visibility', 'fg-caption-hover' );

				//check if captions are set to show below the thumbs
				if ( 'fg-captions-bottom' === $hover_effect_caption_visibility ) {
					//if we have no captions then do not output captions at all
					if ( !array_key_exists( 'title', $captions ) && !array_key_exists( 'desc', $captions ) ) {
						$captions = false;
					}
				}
			}

        	return $captions;
		}
	}
}