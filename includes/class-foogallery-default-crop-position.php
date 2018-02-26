<?php
/**
 * Class to provide a way to override the default attachment crop position. Handy if you want to override the default of center,center
 * Date: 19/02/2018
 *
 * @since 1.4.18
 */
if ( ! class_exists( 'FooGallery_Default_Crop_Position' ) ) {

	class FooGallery_Default_Crop_Position {

		function __construct() {
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_default_crop_position_setting' ) );
			add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_crop_position_setting' ) );
			add_filter( 'wpthumb_default_crop_position', array( $this, 'override_default_crop_position' ) );
		}

		/**
		 * Adds the crop position setting to the settings array
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function add_default_crop_position_setting( $settings ) {
			$just_settings = $settings['settings'];
			$position = 0;
			//find the position of the 'thumb_jpeg_quality' setting
			foreach( $just_settings as $setting ) {
				$position++;
				if ( 'thumb_jpeg_quality' === $setting['id'] ) {
					break;
				}
			}

			$new_settings[] = array(
				'id'      => 'default_crop_position',
				'title'   => __( 'Default Crop Position', 'foogallery' ),
				'desc'    => __( 'The default crop position when resizing thumbnails.', 'foogallery' ),
				'type'    => 'crop',
				'default' => 'center,center',
				'tab'     => 'thumb'
			);

			array_splice( $just_settings, $position, 0, $new_settings );

			$settings['settings'] = $just_settings;

			return $settings;
		}

		/**
		 * Render the custom crop position to the settings page
		 *
		 * @param array $args
		 */
		function render_crop_position_setting( $args ) {
			 if ( 'crop' === $args['type'] ) {
				$current_position = foogallery_get_setting( 'default_crop_position', 'center,center'); ?>
				<style>.foogallery_crop_pos input { margin: 5px !important; width: auto; }</style>
				<div class="foogallery_crop_pos">
					<input type="radio" name="foogallery[default_crop_position]" value="left,top" title="Left, Top" <?php checked( 'left,top', $current_position ) ?>/>
					<input type="radio" name="foogallery[default_crop_position]" value="center,top" title="Center, Top" <?php checked( 'center,top', $current_position ) ?>/>
					<input type="radio" name="foogallery[default_crop_position]" value="right,top" title="Right, Top" <?php checked( 'right,top', $current_position ) ?>/><br/>
					<input type="radio" name="foogallery[default_crop_position]" value="left,center" title="Left, Center" <?php checked( 'left,center', $current_position ) ?>/>
					<input type="radio" name="foogallery[default_crop_position]" value="center,center" title="Center, Center" <?php checked( 'center,center', $current_position ) ?>/>
					<input type="radio" name="foogallery[default_crop_position]" value="right,center" title="Right, Center" <?php checked( 'right,center', $current_position ) ?>/><br/>
					<input type="radio" name="foogallery[default_crop_position]" value="left,bottom" title="Left, Bottom" <?php checked( 'left,bottom', $current_position ) ?>/>
					<input type="radio" name="foogallery[default_crop_position]" value="center,bottom" title="Center, Bottom" <?php checked( 'center,bottom', $current_position ) ?>/>
					<input type="radio" name="foogallery[default_crop_position]" value="right,bottom" title="Right, Bottom" <?php checked( 'right,bottom', $current_position ) ?>/>
				</div>
			<?php }
		}

		function override_default_crop_position( $default ) {
			$crop_position = foogallery_get_setting( 'default_crop_position', 'center,center');
			return $crop_position;
		}
	}
}