<?php
/**
 * FooGallery FooGrid Pro Extension
 *
 * A gallery with inline preview based on the Google image search results.
 *
 * @package   FooGrid_Template_FooGallery_Extension
 * @author     FooPlugins
 * @license   GPL-2.0+
 * @link      https://fooplugins.com
 * @copyright 2014  FooPlugins
 *
 * @wordpress-plugin
 * Plugin Name: FooGallery - FooGrid
 * Description: A gallery with inline preview based on Google\'s image search results.
 * Version:     1.0.0
 * Author:       FooPlugins
 * Author URI:  https://fooplugins.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( !class_exists( 'FooGallery_FooGrid_Gallery_Template' ) ) {

	define('FOOGALLERY_FOOGRID_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));
	define('FOOGALLERY_FOOGRID_GALLERY_TEMPLATE_PATH', plugin_dir_path( __FILE__ ));

	class FooGallery_FooGrid_Gallery_Template {

		const template_id = 'foogridpro';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 100, 1 );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//get thumbnail dimensions
			add_filter( 'foogallery_template_thumbnail_dimensions-foogridpro', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//add the data options needed for grid pro
			add_filter( 'foogallery_build_container_data_options-foogridpro', array( $this, 'add_data_options' ), 10, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-foogridpro', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-foogridpro', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//check if the old FooGrid is installed
			if ( is_admin() ) {
                add_action( 'admin_notices', array( $this, 'display_foogrid_notice') );
            }

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-foogridpro', array( $this, 'build_gallery_template_arguments' ) );

			add_filter( 'foogallery_render_gallery_template_field_value', array( $this, 'alter_old_field_values'), 10, 4 );

			add_filter( 'foogallery_build_class_attribute', array( $this, 'append_classes' ), 10, 2 );
        }

        /*
         * Map old field values
         */
		function alter_old_field_values( $value, $field, $gallery, $template ) {
			//only do something if we are dealing with the grid pro template
			if ( 'foogridpro' === $template['slug'] ) {
				return $this->get_correct_field_value( $field['id'], $value );
			}

			return $value;
		}

		function get_correct_field_value( $field, $original_value ) {
			//mappings for transitions
            if ( 'transition' === $field ) {
	            if ( 'foogrid-transition-horizontal' === $original_value ) {
		            return 'horizontal';
	            } else if ( 'foogrid-transition-vertical' === $original_value ) {
		            return 'vertical';
	            } else if ( 'foogrid-transition-fade' === $original_value ) {
		            return 'fade';
	            } else if ( '' === $original_value ) {
		            return 'none';
	            }
            }

			//mappings for captions
			if ( 'captions' === $field ) {
				if ( 'foogrid-caption-below' === $original_value ) {
					return 'bottom';
				} else if ( 'foogrid-caption-right' === $original_value ) {
					return 'right';
				} else if ( '' === $original_value ) {
					return 'none';
				}
			}

            return $original_value;
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
				'name'        => __( 'Grid PRO', 'foogallery'),
                'preview_support' => true,
                'common_fields_support' => true,
                'lazyload_support' => true,
                'paging_support' => true,
                'thumbnail_dimensions' => true,
				'mandatory_classes' => 'foogrid',
				'filtering_support' => true,
				'embed_support' => true,
				'panel_support' => true,
				'enqueue_core' => true,
				'fields'	  => array(
					array(
						'id'      => 'thumbnail_size',
						'title'   => __('Thumbnail Size', 'foogallery'),
						'desc'    => __('Choose the size of your thumbs.', 'foogallery'),
						'type'    => 'thumb_size',
						'default' => array(
							'width' => 320,
							'height' => 180,
							'crop' => true
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'aspect-ratio',
						'section' => __( 'Panel', 'foogallery' ),
						'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
						'title'   => __('Aspect Ratio', 'foogallery'),
						'desc' => __('Select the aspect ratio the panel will use, to best suit your content.', 'foogallery'),
						'default' => 'fg-16-9',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'fg-16-9' => __( '16:9', 'foogallery' ),
							'fg-16-10' => __( '16:10', 'foogallery' ),
							'fg-4-3' => __( '4:3', 'foogallery' ),
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery'),
						'default' => 'image' ,
						'type'    => 'thumb_link',
						'desc'	  => __('You can choose to either link each thumbnail to the full size image or to the image\'s attachment page.', 'foogallery')
					),
					array(
						'id'      => 'transition',
						'section' => __( 'General', 'foogallery' ),
						'title'   => __('Transition', 'foogallery'),
						'desc' => __('Transition type to use switching between items, or no transitions at all.', 'foogallery'),
						'default' => 'fade',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'fade' => __( 'Fade', 'foogallery' ),
							'horizontal' => __( 'Horizontal', 'foogallery' ),
							'vertical' => __( 'Vertical', 'foogallery' ),
							'none' => __( 'None', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id' => 'loop',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Loop', 'foogallery'),
						'desc' => __('Whether the slider should loop (i.e. the first slide goes to the last, the last slide goes to the first).', 'foogallery'),
						'default' => 'yes',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'yes' => __( 'Yes', 'foogallery' ),
							'no' => __( 'No', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'columns',
						'section' => __( 'General', 'foogallery' ),
						'title'   => __('Max Columns', 'foogallery'),
						'desc' => __('The maximum number of thumbnail columns to display. * This amount is automatically reduced on small screen sizes.', 'foogallery'),
						'default' => 'foogrid-cols-4',
						'type'    => 'select',
						'choices' => array(
							'foogrid-cols-2' => __( '2 Columns', 'foogallery' ),
							'foogrid-cols-3' => __( '3 Columns', 'foogallery' ),
							'foogrid-cols-4' => __( '4 Columns', 'foogallery' ),
							'foogrid-cols-5' => __( '5 Columns', 'foogallery' ),
							'foogrid-cols-6' => __( '6 Columns', 'foogallery' ),
							'foogrid-cols-7' => __( '7 Columns', 'foogallery' ),
							'foogrid-cols-8' => __( '8 Columns', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'captions',
						'section' => __( 'General', 'foogallery' ),
						'title'   => __('Stage Caption', 'foogallery'),
						'desc' => __('The position of caption in the stage or no captions at all. * The caption will automatically switch to below the item on small screen sizes.', 'foogallery'),
						'default' => 'bottom',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'bottom' => __( 'Below', 'foogallery' ),
							'right' => __( 'Right', 'foogallery' ),
							'top' => __( 'Top', 'foogallery' ),
							'left' => __( 'Left', 'foogallery' ),
							'none' => __( 'None', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id' => 'scroll',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Scroll', 'foogallery'),
						'desc' => __('Whether the page is scrolled to the selected item.', 'foogallery'),
						'default' => 'yes',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'yes' => __( 'Yes', 'foogallery' ),
							'no' => __( 'No', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id' => 'scroll_smooth',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Smooth Scroll', 'foogallery'),
						'desc' => __('Whether or not to perform a smooth scrolling animation to the selected item.', 'foogallery'),
						'default' => 'yes',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'yes' => __( 'Yes', 'foogallery' ),
							'no' => __( 'No', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id' => 'scroll_offset',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Scroll Offset', 'foogallery'),
						'desc' => __('The amount to offset scrolling by. * This can be used to counter fixed headers.', 'foogallery'),
						'class'   => 'small-text',
						'type' => 'number',
						'default' => 0,
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
					),
				)
			);

			return $gallery_templates;
		}

		/**
		 * Add the required data options if needed
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_data_options($options, $gallery, $attributes) {
			$loop = foogallery_gallery_template_setting( 'loop', 'yes' ) === 'yes';
			$scroll = foogallery_gallery_template_setting( 'scroll', 'yes' ) === 'yes';
			$scroll_smooth = foogallery_gallery_template_setting( 'scroll_smooth', 'yes' ) === 'yes';
			$scroll_offset = foogallery_gallery_template_setting( 'scroll_offset', 0 );
			$transition = foogallery_gallery_template_setting( 'transition', 'fade' );

			//map to correct values
			$transition = $this->get_correct_field_value( 'transition', $transition );

			$options['template']['loop'] = $loop;
			$options['template']['scroll'] = $scroll;
			$options['template']['scrollSmooth'] = $scroll_smooth;
			$options['template']['scrollOffset'] = intval( $scroll_offset );
			$options['template']['transition'] = $transition;

			return $options;
		}

		/**
		 * Override specific settings so that the gallery template will always work
		 *
		 * @param $settings
		 * @param $post_id
		 * @param $form_data
		 *
		 * @return mixed
		 */
		function override_settings($settings, $post_id, $form_data) {
			return $settings;
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
            if ( array_key_exists( 'thumbnail_size', $arguments) ) {
            	$thumbnail_size = $arguments['thumbnail_size'];
                return array(
                    'height' => intval( $thumbnail_size['height'] ),
                    'width' => intval( $thumbnail_size['width'] ),
                    'crop' => $thumbnail_size['crop'] === '1'
                );
            }
            return null;
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
			$dimensions = $foogallery->get_meta( 'foogridpro_thumbnail_size', false );
			return $dimensions;
		}

        /**
         * Display a message if the FooGrid extension is also installed
         */
		function display_foogrid_notice() {
		    if ( class_exists('FooGrid_Template_FooGallery_Extension') ) {
                ?>
                <div class="notice error">
                    <p>
                        <strong><?php _e('FooGrid Extension Redundant!', 'foogallery'); ?></strong><br/>
                        <?php _e('You have both FooGallery PRO and the FooGrid extension activated. FooGallery PRO includes the Grid PRO gallery template, which makes the FooGrid extension redundant.', 'foogallery'); ?>
                        <br/>
                        <?php _e('Please edit all galleries that use the FooGrid gallery template and change them to use the Grid PRO gallery template. Once this is done, you can delete the FooGrid extension.', 'foogallery'); ?>
                        <br/>
                    </p>
                </div>
                <?php
            }
        }

        /**
         * Build up the arguments needed for rendering this gallery template
         *
         * @param $args
         * @return array
         */
        function build_gallery_template_arguments( $args ) {
            $args = foogallery_gallery_template_setting( 'thumbnail_size', array() );
            $args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

            return $args;
        }

		/**
		 * Adds the classes onto the container
		 *
		 * @param $classes
		 * @param $foogallery FooGallery
		 *
		 * @return array
		 */
		function append_classes( $classes, $foogallery ) {
			if ( isset( $foogallery ) && isset( $foogallery->gallery_template ) && $foogallery->gallery_template === self::template_id ) {

				//add a class for the columns
				$columns = foogallery_gallery_template_setting( 'columns', '' );
				if ( $columns !== '' ) {
					$classes[] = $columns;
				}

				//add a class for the aspect ratio
				$aspect_ratio = foogallery_gallery_template_setting( 'aspect-ratio', '' );
				if ( $aspect_ratio !== '' ) {
					$classes[] = $aspect_ratio;
				}

				//add a class for transition in the panel
				$transition = foogallery_gallery_template_setting( 'lightbox_transition', '' );
				if ( $transition !== '' ) {
					$classes[] = "foogrid-transition-" . $transition;
				}
			}

			return $classes;
		}
	}
}