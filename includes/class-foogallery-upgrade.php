<?php
/**
 * Class used to upgrade internal gallery settings when needed
 * Date: 19/07/2017
 */
if ( ! class_exists( 'FooGallery_Upgrade' ) ) {

	class FooGallery_Upgrade {

		function __construct() {
			add_action( 'foogallery_admin_new_version_detected', array( $this, 'upgrade_all_galleries' ) );
		}

		function upgrade_all_galleries() {
			$galleries = foogallery_get_all_galleries();

			foreach ( $galleries as $gallery ) {
				$this->perform_gallery_settings_upgrade( $gallery );
			}
		}

		function perform_gallery_settings_upgrade( $foogallery ) {

			$mappings = array(
				array(
					'id' => 'border-style',
					'value' => 'border-style-square-white',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-light' ),
						array ( 'id' => 'border_size', 'value' => 'fg-border-thin' ),
						array ( 'id' => 'drop_shadow', 'value' => 'fg-shadow-small' ),
						array ( 'id' => 'rounded_corners', 'value' => '' ),
						array ( 'id' => 'inner_shadow', 'value' => '' ),
					)
				),
				array(
					'id' => 'border-style',
					'value' => 'border-style-circle-white',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-light' ),
						array ( 'id' => 'border_size', 'value' => 'fg-border-thin' ),
						array ( 'id' => 'drop_shadow', 'value' => 'fg-shadow-small' ),
						array ( 'id' => 'rounded_corners', 'value' => 'fg-round-full' ),
						array ( 'id' => 'inner_shadow', 'value' => '' ),
					)
				),
				array(
					'id' => 'border-style',
					'value' => 'border-style-square-black',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-dark' ),
						array ( 'id' => 'border_size', 'value' => 'fg-border-thin' ),
						array ( 'id' => 'drop_shadow', 'value' => 'fg-shadow-small' ),
						array ( 'id' => 'rounded_corners', 'value' => '' ),
						array ( 'id' => 'inner_shadow', 'value' => '' ),
					)
				),
				array(
					'id' => 'border-style',
					'value' => 'border-style-circle-black',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-dark' ),
						array ( 'id' => 'border_size', 'value' => 'fg-border-thin' ),
						array ( 'id' => 'drop_shadow', 'value' => 'fg-shadow-small' ),
						array ( 'id' => 'rounded_corners', 'value' => 'fg-round-full' ),
						array ( 'id' => 'inner_shadow', 'value' => '' ),
					)
				),
				array(
					'id' => 'border-style',
					'value' => 'border-style-inset',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-light' ),
						array ( 'id' => 'border_size', 'value' => '' ),
						array ( 'id' => 'drop_shadow', 'value' => 'fg-shadow-small' ),
						array ( 'id' => 'rounded_corners', 'value' => '' ),
						array ( 'id' => 'inner_shadow', 'value' => 'fg-shadow-inset-large' ),
					)
				),
				array(
					'id' => 'border-style',
					'value' => 'border-style-rounded',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-light' ),
						array ( 'id' => 'border_size', 'value' => '' ),
						array ( 'id' => 'drop_shadow', 'value' => '' ),
						array ( 'id' => 'rounded_corners', 'value' => 'fg-round-small' ),
						array ( 'id' => 'inner_shadow', 'value' => '' ),
					)
				),
				array(
					'id' => 'border-style',
					'value' => '',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-light' ),
						array ( 'id' => 'border_size', 'value' => '' ),
						array ( 'id' => 'drop_shadow', 'value' => '' ),
						array ( 'id' => 'rounded_corners', 'value' => '' ),
						array ( 'id' => 'inner_shadow', 'value' => '' ),
					)
				),

				array(
					'id' => 'spacing',
					'value' => 'spacing-width-0',
					'new' => array(
						array ( 'id' => 'spacing', 'value' => 'fg-gutter-0' )
					)
				),
				array(
					'id' => 'spacing',
					'value' => 'spacing-width-5',
					'new' => array(
						array ( 'id' => 'spacing', 'value' => 'fg-gutter-5' )
					)
				),
				array(
					'id' => 'spacing',
					'value' => 'spacing-width-10',
					'new' => array(
						array ( 'id' => 'spacing', 'value' => 'fg-gutter-10' )
					)
				),
				array(
					'id' => 'spacing',
					'value' => 'spacing-width-15',
					'new' => array(
						array ( 'id' => 'spacing', 'value' => 'fg-gutter-15' )
					)
				),
				array(
					'id' => 'spacing',
					'value' => 'spacing-width-20',
					'new' => array(
						array ( 'id' => 'spacing', 'value' => 'fg-gutter-20' )
					)
				),
				array(
					'id' => 'spacing',
					'value' => 'spacing-width-25',
					'new' => array(
						array ( 'id' => 'spacing', 'value' => 'fg-gutter-25' )
					)
				),

				array(
					'id' => 'alignment',
					'value' => 'alignment-left',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => 'fg-left' )
					)
				),
				array(
					'id' => 'alignment',
					'value' => 'alignment-center',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => 'fg-center' )
					)
				),
				array(
					'id' => 'alignment',
					'value' => 'alignment-right',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => 'fg-right' )
					)
				),

				array(
					'id' => 'loading_animation',
					'value' => 'yes',
					'new' => array(
						array ( 'id' => 'loading_icon', 'value' => 'fg-loading-default' )
					)
				),
				array(
					'id' => 'loading_animation',
					'value' => 'no',
					'new' => array(
						array ( 'id' => 'loading_icon', 'value' => 'fg-loading-none' )
					)
				),


				array(
					'id' => 'hover-effect-type',
					'value' => '', //Icon
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
						array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => 'fg-hover-zoom' ),
						array ( 'id' => 'hover_effect_title', 'value' => 'none' ),
						array ( 'id' => 'hover_effect_desc', 'value' => 'none' )
					)
				),

				array(
					'id' => 'hover-effect-type',
					'value' => 'hover-effect-tint', //Dark Tint
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => '' ),
						array ( 'id' => 'hover_effect', 'value' => 'fg-hover-tint' )
					)
				),

				array(
					'id' => 'hover-effect-type',
					'value' => 'hover-effect-color', //Colorize
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => '' ),
						array ( 'id' => 'hover_effect_color', 'value' => 'fg-hover-colorize' )
					)
				),

				array(
					'id' => 'hover-effect-type',
					'value' => 'hover-effect-none', //None
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => '' )
					)
				),

				array(
					'id' => 'hover-effect-type',
					'value' => 'hover-effect-caption', //Caption
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
					)
				),

				array(
					'id' => 'hover-effect',
					'value' => 'hover-effect-zoom',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => 'fg-hover-zoom' )
					)
				),

				array(
					'id' => 'hover-effect',
					'value' => 'hover-effect-zoom2',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => 'fg-hover-zoom2' )
					)
				),

				array(
					'id' => 'hover-effect',
					'value' => 'hover-effect-zoom3',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => 'fg-hover-zoom3' )
					)
				),

				array(
					'id' => 'hover-effect',
					'value' => 'hover-effect-plus',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => 'fg-hover-plus' )
					)
				),

				array(
					'id' => 'hover-effect',
					'value' => 'hover-effect-circle-plus',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => 'fg-hover-circle-plus' )
					)
				),

				array(
					'id' => 'hover-effect',
					'value' => 'hover-effect-eye',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => 'fg-hover-eye' )
					)
				),

				array(
					'id' => 'caption-hover-effect',
					'value' => 'hover-caption-simple',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => '' )
					)
				),
				array(
					'id' => 'caption-hover-effect',
					'value' => 'hover-caption-full-drop',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-slide-down' ),
						array ( 'id' => 'hover_effect_icon', 'value' => '' )
					)
				),
				array(
					'id' => 'caption-hover-effect',
					'value' => 'hover-caption-full-fade',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
						array ( 'id' => 'hover_effect_icon', 'value' => '' )
					)
				),
				array(
					'id' => 'caption-hover-effect',
					'value' => 'hover-caption-push',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
                        array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-push' ),
						array ( 'id' => 'hover_effect_icon', 'value' => '' )
					)
				),
				array(
					'id' => 'caption-hover-effect',
					'value' => 'hover-caption-simple-always',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
                        array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-always' ),
						array ( 'id' => 'hover_effect_icon', 'value' => '' )
					)
				),

				array(
					'id' => 'caption-content',
					'value' => 'title',
					'new' => array(
						array ( 'id' => 'hover_effect_title', 'value' => '' ),
						array ( 'id' => 'hover_effect_desc', 'value' => 'none' )
					)
				),
				array(
					'id' => 'caption-content',
					'value' => 'desc',
					'new' => array(
						array ( 'id' => 'hover_effect_title', 'value' => 'none' ),
						array ( 'id' => 'hover_effect_desc', 'value' => '' )
					)
				),
				array(
					'id' => 'caption-content',
					'value' => 'both',
					'new' => array(
						array ( 'id' => 'hover_effect_title', 'value' => '' ),
						array ( 'id' => 'hover_effect_desc', 'value' => '' )
					)
				),

				//masonry layout mappings
				array(
					'id' => 'layout',
					'value' => '2col',
					'new' => array(
						array ( 'id' => 'layout', 'value' => 'col2' )
					)
				),

				array(
					'id' => 'layout',
					'value' => '3col',
					'new' => array(
						array ( 'id' => 'layout', 'value' => 'col3' )
					)
				),

				array(
					'id' => 'layout',
					'value' => '4col',
					'new' => array(
						array ( 'id' => 'layout', 'value' => 'col4' )
					)
				),

				array(
					'id' => 'layout',
					'value' => '5col',
					'new' => array(
						array ( 'id' => 'layout', 'value' => 'col5' )
					)
				),

				array(
					'id' => 'gutter_percent',
					'value' => 'no-gutter',
					'new' => array(
						array ( 'id' => 'gutter_percent', 'value' => 'fg-gutter-none' )
					)
				),

				array(
					'id' => 'gutter_percent',
					'value' => 'large-gutter',
					'new' => array(
						array ( 'id' => 'gutter_percent', 'value' => 'fg-gutter-large' )
					)
				),

				array(
					'id' => 'center_align',
					'value' => 'default',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => '' )
					)
				),

				array(
					'id' => 'center_align',
					'value' => 'center',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => 'fg-center' )
					)
				),

				array(
					'id' => 'hover_zoom',
					'value' => 'default',
					'new' => array(
						array ( 'id' => 'hover_effect_scale', 'value' => 'fg-hover-scale' )
					)
				),

				array(
					'id' => 'hover_zoom',
					'value' => 'none',
					'new' => array(
						array ( 'id' => 'hover_effect_scale', 'value' => '' )
					)
				),


				//image viewer upgrades
				array(
					'id' => 'theme',
					'value' => 'fiv-dark',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-dark' )
					)
				),
				array(
					'id' => 'theme',
					'value' => '',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-light' )
					)
				),
				array(
					'id' => 'theme',
					'value' => 'fiv-custom',
					'new' => array(
						array ( 'id' => 'theme', 'value' => 'fg-light' )
					)
				),

				array(
					'id' => 'alignment',
					'value' => 'alignment-left',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => 'fg-left' )
					)
				),
				array(
					'id' => 'alignment',
					'value' => 'alignment-center',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => 'fg-center' )
					)
				),
				array(
					'id' => 'alignment',
					'value' => 'alignment-right',
					'new' => array(
						array ( 'id' => 'alignment', 'value' => 'fg-right' )
					)
				),

				//simple portfolio
				array(
					'id' => 'caption_position',
					'value' => 'bf-captions-above',
					'new' => array(
						array ( 'id' => 'caption_position', 'value' => 'fg-captions-top' )
					)
				),

				//single thumbnail
				array(
					'id' => 'caption_style',
					'value' => 'caption-simple',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
						array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-always' )
					)
				),
				array(
					'id' => 'caption_style',
					'value' => 'caption-slideup',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
						array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
						array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-slide-up' ),
					)
				),

				array(
					'id' => 'caption_style',
					'value' => 'caption-fall',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
						array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
						array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-slide-down' ),
					)
				),
				array(
					'id' => 'caption_style',
					'value' => 'caption-fade',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
						array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
						array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-fade' ),
					)
				),
				array(
					'id' => 'caption_style',
					'value' => 'caption-push',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
						array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
						array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-push' ),
					)
				),
				array(
					'id' => 'caption_style',
					'value' => 'caption-scale',
					'new' => array(
						array ( 'id' => 'hover_effect_preset', 'value' => 'fg-custom' ),
						array ( 'id' => 'hover_effect_caption_visibility', 'value' => 'fg-caption-hover' ),
						array ( 'id' => 'hover_effect_transition', 'value' => 'fg-hover-slide-left' ),
					)
				),

				//single thumbnail gallery
				array(
					'id' => 'position',
					'value' => 'position-block',
					'new' => array(
						array ( 'id' => 'position', 'value' => 'fg-center' ),
					)
				),
				array(
					'id' => 'position',
					'value' => 'position-float-left',
					'new' => array(
						array ( 'id' => 'position', 'value' => 'fg-left' ),
					)
				),
				array(
					'id' => 'position',
					'value' => 'position-float-right',
					'new' => array(
						array ( 'id' => 'position', 'value' => 'fg-right' ),
					)
				),

			);

			$new_settings = get_post_meta( $foogallery->ID, FOOGALLERY_META_SETTINGS, true );
			$old_settings = get_post_meta( $foogallery->ID, FOOGALLERY_META_SETTINGS_OLD, true );

			//only upgrade galleries that need to be
			if ( !is_array($new_settings) ) {

				//start with the old settings
				$new_settings = $old_settings;

				//upgrade all template settings
				foreach ( foogallery_gallery_templates() as $template ) {

					foreach ( $mappings as $mapping ) {

						$settings_key = "{$template['slug']}_{$mapping['id']}";

						//check if the settings exists
						if ( array_key_exists( $settings_key, $old_settings ) ) {

							$old_settings_value = $old_settings[$settings_key];

							if ( $mapping['value'] === $old_settings_value ) {
								//we have found a match!

								foreach ( $mapping['new'] as $setting_to_create ) {
									$new_setting_key                = "{$template['slug']}_{$setting_to_create['id']}";
									$new_setting_value              = $setting_to_create['value'];
									$new_settings[$new_setting_key] = $new_setting_value;
								}
							}
						}
					}
				}

				//template specific settings overrides

				if ( 'image-viewer' === $foogallery->template ) {
					$new_settings['image-viewer_theme'] = 'fg-light';
					$new_settings['image-viewer_border_size'] = '';
					$new_settings['image-viewer_drop_shadow'] = '';
					$new_settings['image-viewer_rounded_corners'] = '';
					$new_settings['image-viewer_inner_shadow'] = '';
				}

				if ( 'justified' === $foogallery->template ) {
					$new_settings['image-viewer_theme'] = 'fg-light';
					$new_settings['image-viewer_border_size'] = '';
					$new_settings['image-viewer_drop_shadow'] = '';
					$new_settings['image-viewer_rounded_corners'] = '';
					$new_settings['image-viewer_inner_shadow'] = '';
				}

				if ( 'masonry' === $foogallery->template ) {
					$new_settings['image-viewer_theme'] = 'fg-light';
					$new_settings['image-viewer_border_size'] = '';
					$new_settings['image-viewer_drop_shadow'] = '';
					$new_settings['image-viewer_rounded_corners'] = '';
					$new_settings['image-viewer_inner_shadow'] = '';
				}


				add_post_meta( $foogallery->ID, FOOGALLERY_META_SETTINGS, $new_settings, true );
			}
		}
	}
}