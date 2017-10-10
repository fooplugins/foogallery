<?php
/**
 * Class used to upgrade internal gallery settings when needed
 * Date: 19/07/2017
 */
if ( ! class_exists( 'FooGallery_Upgrade' ) ) {

	class FooGallery_Upgrade {

		function __construct() {
			//add_action( 'foogallery_admin_new_version_detected', array( $this, 'upgrade_all_galleries' ) );
			add_filter( 'foogallery_settings_upgrade', array( $this, 'upgrade_gallery_settings' ), 10, 2 );

			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_force_upgrade_setting' ) );
			add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_force_upgrades_settings' ) );
			add_action( 'wp_ajax_foogallery_force_upgrade', array( $this, 'ajax_force_upgrade' ) );

			add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_meta_boxes_to_gallery' ) );
		}

		public function upgrade_gallery_settings( $settings, $foogallery ) {
			$old_settings = get_post_meta( $foogallery->ID, FOOGALLERY_META_SETTINGS_OLD, true );

			//we have old settings - so upgrade them!!!
			if ( !empty( $old_settings ) ) {
				$upgrade_helper = new FooGallery_Upgrade_Helper();
				$settings = $upgrade_helper->perform_gallery_settings_upgrade( $foogallery );
			}

			return $settings;
		}

		public function add_meta_boxes_to_gallery( $post ) {

			if ( foogallery_get_setting( 'enable_debugging' ) ) {
				add_meta_box(
					'foogallery_upgrade_debug',
					__( 'Settings Upgrade Debugging', 'foogallery' ),
					array( $this, 'render_upgrade_debug_metabox' ),
					FOOGALLERY_CPT_GALLERY,
					'normal',
					'low'
				);
			}
		}

		public function render_upgrade_debug_metabox( $post ) {
			$gallery = FooGallery::get( $post );

			if ( $gallery->is_new() ) {
				return;
			}

			$old_settings = get_post_meta( $gallery->ID, FOOGALLERY_META_SETTINGS_OLD, true );
			$new_settings = get_post_meta( $gallery->ID, FOOGALLERY_META_SETTINGS, true );
			$upgrade_helper = new FooGallery_Upgrade_Helper();
			$upgrade_settings = $upgrade_helper->build_new_settings( $gallery );

			ksort( $old_settings );
			ksort( $new_settings );
			ksort( $upgrade_settings );
			?>
			<style>
				#foogallery_upgrade_debug .inside { overflow: scroll; }
				#foogallery_upgrade_debug table { font-size: 0.8em; }
				#foogallery_upgrade_debug td { vertical-align: top; }
			</style>
			<table>
				<tr>
					<td><h3>Old Settings</h3></td>
					<td><h3>New Settings</h3></td>
					<td><h3>Upgrade Settings</h3></td>
				</tr>
				<tr>
					<td><?php var_dump( $old_settings ); ?></td>
					<td><?php var_dump( $new_settings ); ?></td>
					<td><?php var_dump( $upgrade_settings ); ?></td>
				</tr>
			</table>
			<?php
		}

		function ajax_force_upgrade() {
			if ( check_admin_referer( 'foogallery_force_upgrade' ) && current_user_can( 'install_plugins' ) ) {

				//clear any and all previous upgrades!
				delete_post_meta_by_key( '_foogallery_settings' );
				$this->upgrade_all_galleries();

				_e('The BETA upgrade process has been run!', 'foogallery' );
				die();
			}
		}

		function add_force_upgrade_setting( $settings ) {
			$settings['settings'][] = array(
				'id'      => 'force_upgrade',
				'title'   => __( 'Force Upgrade', 'foogallery' ),
				'desc'    => sprintf( __( 'Force the BETA upgrade process to run. This may sometimes be needed if the upgrade did not run automatically. Any changes you have made to galleries after updating will be lost. THERE IS NO UNDO.', 'foogallery' ), foogallery_plugin_name() ),
				'type'    => 'force_upgrade',
				'tab'     => 'advanced'
			);

			return $settings;
		}

		function render_force_upgrades_settings( $args ) {
			if ( 'force_upgrade' === $args['type'] ) { ?>
				<div class="foogallery_settings_ajax_container">
					<input type="button" data-action="foogallery_force_upgrade" data-confirm="<?php _e('Are you sure? Any changes you have made since updating will be lost. There is no undo!', 'foogallery'); ?>" data-response="replace_container" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_force_upgrade' ) ); ?>" class="button-primary foogallery_settings_ajax foogallery_force_upgrade" value="<?php _e( 'Run Upgrade Process', 'foogallery' ); ?>">
					<span style="position: absolute" class="spinner"></span>
				</div>
			<?php }
		}

		function upgrade_all_galleries() {
			$galleries = foogallery_get_all_galleries();

			foreach ( $galleries as $gallery ) {
				$new_settings = get_post_meta( $gallery->ID, FOOGALLERY_META_SETTINGS, true );
				$old_settings = get_post_meta( $gallery->ID, FOOGALLERY_META_SETTINGS_OLD, true );

				//only upgrade galleries that need to be
				if ( !is_array($new_settings) && is_array($old_settings) ) {
					$upgrade_helper = new FooGallery_Upgrade_Helper();
					$upgrade_helper->perform_gallery_settings_upgrade( $gallery );
				}
			}
		}
	}
}

if ( ! class_exists( 'FooGallery_Upgrade_Helper' ) ) {

	class FooGallery_Upgrade_Helper {

		function perform_gallery_settings_upgrade( $foogallery ) {
			//build up the new settings
			$new_settings = $this->build_new_settings( $foogallery );

			//save the new settings
			add_post_meta( $foogallery->ID, FOOGALLERY_META_SETTINGS, $new_settings, true );

			//clear any cache that may be saved for the gallery
			delete_post_meta( $foogallery->ID, FOOGALLERY_META_CACHE );

			//clear any previously calculated thumb dimensions
			delete_post_meta( $foogallery->ID, FOOGALLERY_META_THUMB_DIMENSIONS );

			//calculate new thumb dimensions if needed
			$thumb_dimensions = new FooGallery_Thumbnail_Dimensions();
			$thumb_dimensions->calculate_thumbnail_dimensions( $foogallery->ID );

			return $new_settings;
		}

		function build_new_settings( $foogallery ) {
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
						array ( 'id' => 'caption_title', 'value' => 'none' ),
						array ( 'id' => 'caption_desc', 'value' => 'none' )
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
						array ( 'id' => 'caption_title', 'value' => '' ),
						array ( 'id' => 'caption_desc', 'value' => 'none' )
					)
				),
				array(
					'id' => 'caption-content',
					'value' => 'desc',
					'new' => array(
						array ( 'id' => 'caption_title', 'value' => 'none' ),
						array ( 'id' => 'caption_desc', 'value' => '' )
					)
				),
				array(
					'id' => 'caption-content',
					'value' => 'both',
					'new' => array(
						array ( 'id' => 'caption_title', 'value' => '' ),
						array ( 'id' => 'caption_desc', 'value' => '' )
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

			$old_settings = get_post_meta( $foogallery->ID, FOOGALLERY_META_SETTINGS_OLD, true );

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
			if ( 'image-viewer' === $foogallery->gallery_template ) {
				$new_settings['image-viewer_theme'] = 'fg-light';
				$new_settings['image-viewer_border_size'] = '';
				$new_settings['image-viewer_drop_shadow'] = '';
				$new_settings['image-viewer_rounded_corners'] = '';
				$new_settings['image-viewer_inner_shadow'] = '';
			}

			if ( 'justified' === $foogallery->gallery_template ) {
				$new_settings['image-viewer_theme'] = 'fg-light';
				$new_settings['image-viewer_border_size'] = '';
				$new_settings['image-viewer_drop_shadow'] = '';
				$new_settings['image-viewer_rounded_corners'] = '';
				$new_settings['image-viewer_inner_shadow'] = '';
			}

			if ( 'masonry' === $foogallery->gallery_template ) {
				$new_settings['image-viewer_theme'] = 'fg-light';
				$new_settings['image-viewer_border_size'] = '';
				$new_settings['image-viewer_drop_shadow'] = '';
				$new_settings['image-viewer_rounded_corners'] = '';
				$new_settings['image-viewer_inner_shadow'] = '';
			}

			return $new_settings;
		}
	}
}