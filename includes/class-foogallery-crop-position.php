<?php
/**
 * Class to provide a way to override the default attachment crop position. Handy if you want to override the default of center,center
 * Date: 19/02/2018
 *
 * @since 1.4.18
 */
if ( ! class_exists( 'FooGallery_Crop_Position' ) ) {

	class FooGallery_Crop_Position {

		const CROP_POSITION_META_KEY = 'foogallery_crop_pos';
		const CROP_POSITION_META_KEY_LEGACY = 'wpthumb_crop_pos';
		const CROP_POSITION_DEFAULT = 'center,center';

		function __construct() {
			if ( is_admin() ) {
				add_filter( 'foogallery_admin_settings_override', array( $this, 'add_default_crop_position_setting' ) );
				add_action( 'foogallery_admin_settings_custom_type_render_setting', array( $this, 'render_crop_position_setting' ) );

				add_filter( 'attachment_fields_to_edit', array( $this, 'media_form_crop_position' ), 10, 2 );
				add_filter( 'attachment_fields_to_save', array( $this, 'media_form_crop_position_save' ), 10, 2 );
			}
			add_filter( 'foogallery_thumbnail_resize_args_final', array( $this, 'add_crop_position_arguments' ), 10, 3 );
		}

		/**
		 * Append crop position arguments if they are saved
		 *
		 * @param $args
		 * @param $original_image_src
		 * @param $thumbnail_object
		 *
		 * @return mixed
		 */
		function add_crop_position_arguments( $args, $original_image_src, $thumbnail_object ) {
			if ( !foogallery_thumb_active_engine()->has_local_cache() ) {
				return $args;
			}

			if ( isset( $thumbnail_object ) && $thumbnail_object->ID > 0 ) {
				$crop_from_position = get_post_meta( $thumbnail_object->ID, self::CROP_POSITION_META_KEY, true );

				if ( !empty( $crop_from_position ) ) {
					$args['crop_from_position'] = $crop_from_position;
				}
			}

			return $args;
		}

		/**
		 * Adds the crop position setting to the settings array
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function add_default_crop_position_setting( $settings ) {
			if ( !foogallery_thumb_active_engine()->has_local_cache() ) {
				return $settings;
			}

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
				'default' => self::CROP_POSITION_DEFAULT,
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
				$current_position = foogallery_get_setting( $args['id'], $args['default'] ); ?>
				<style>.foogallery_crop_pos input { margin: 5px !important; width: auto; }</style>
				<div class="foogallery_crop_pos">
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="left,top" title="Left, Top" <?php checked( 'left,top', $current_position ) ?>/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="center,top" title="Center, Top" <?php checked( 'center,top', $current_position ) ?>/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="right,top" title="Right, Top" <?php checked( 'right,top', $current_position ) ?>/><br/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="left,center" title="Left, Center" <?php checked( 'left,center', $current_position ) ?>/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="center,center" title="Center, Center" <?php checked( 'center,center', $current_position ) ?>/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="right,center" title="Right, Center" <?php checked( 'right,center', $current_position ) ?>/><br/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="left,bottom" title="Left, Bottom" <?php checked( 'left,bottom', $current_position ) ?>/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="center,bottom" title="Center, Bottom" <?php checked( 'center,bottom', $current_position ) ?>/>
					<input type="radio" name="foogallery[<?php echo $args['id']; ?>]" value="right,bottom" title="Right, Bottom" <?php checked( 'right,bottom', $current_position ) ?>/>
				</div>
			<?php }
		}

		/**
		 * Return the default crop position
		 *
		 * @param $default
		 *
		 * @return mixed
		 */
		function default_crop_position() {
			$crop_position = foogallery_get_setting( 'default_crop_position', self::CROP_POSITION_DEFAULT );
			return $crop_position;
		}

		/**
		 * Adds a back end for selecting the crop position of images.
		 *
		 * @access public
		 *
		 * @param array $fields
		 * @param array $post
		 * @return $post
		 */
		function media_form_crop_position( $fields, $post ) {

			if ( !foogallery_thumb_active_engine()->has_local_cache() ) {
				return $fields;
			}

			// Ensure $post is not null before proceeding
			if ( ! is_a( $post, 'WP_Post' ) || ! wp_attachment_is_image( $post->ID ) ) {
				return $fields;
			}

			$crop_position = $this->get_crop_position_from_attachment( $post->ID );

			$html = '<style>#foogallery_crop_pos { padding: 5px; } #foogallery_crop_pos input { margin: 5px; width: auto; }</style>';
			$html .= '<div id="foogallery_crop_pos">';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="left,top" title="Left, Top" ' . checked( 'left,top', $crop_position, false ) . '/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="center,top" title="Center, Top" ' . checked( 'center,top', $crop_position, false ) . '/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="right,top" title="Right, Top" ' . checked( 'right,top', $crop_position, false ) . '/><br/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="left,center" title="Left, Center" ' . checked( 'left,center', $crop_position, false ) . '/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="center,center" title="Center, Center"' . checked( 'center,center', $crop_position, false ) . '/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="right,center" title="Right, Center" ' . checked( 'right,center', $crop_position, false ) . '/><br/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="left,bottom" title="Left, Bottom" ' . checked( 'left,bottom', $crop_position, false ) . '/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="center,bottom" title="Center, Bottom" ' . checked( 'center,bottom', $crop_position, false ) . '/>';
			$html .= '<input type="radio" name="attachments[' . $post->ID . '][foogallery_crop_pos]" value="right,bottom" title="Right, Bottom" ' . checked( 'right,bottom', $crop_position, false ) . '/>';
			$html .= '</div>';

			$fields['crop-from-position'] = array(
				'label' => __( 'Crop Position', 'foogallery' ),
				'input' => 'html',
				'html'  => $html
			);

			return $fields;
		}

		/**
		 * Gets the crop position from the attachment post meta, and defaults to what is set in settings
		 *
		 * @param $attachment_id
		 *
		 * @return mixed|string
		 */
		private function get_crop_position_from_attachment( $attachment_id ) {
			//first try to get the legacy value using the old key
			$crop_postion = get_post_meta( $attachment_id, self::CROP_POSITION_META_KEY_LEGACY, true );

			//check if we have a legacy value saved, so migrate to the new
			if ( $crop_postion ) {
				//remove the old post meta
				delete_post_meta( $attachment_id, self::CROP_POSITION_META_KEY_LEGACY );

				//add new post meta with the correct key
				update_post_meta( $attachment_id, self::CROP_POSITION_META_KEY, $crop_postion );
			} else {
				$current_position = get_post_meta( $attachment_id, self::CROP_POSITION_META_KEY, true );
			}

			if ( !$current_position ) {
				$current_position = $this->default_crop_position();
			}

			return $current_position;
		}

		/**
		 * wpthumb_media_form_crop_position_save function.
		 *
		 * Saves crop position in post meta.
		 *
		 * @access public
		 *
		 * @param array $post
		 * @param array $attachment
		 * @return $post
		 */
		function media_form_crop_position_save( $post, $attachment ) {

			if ( !foogallery_thumb_active_engine()->has_local_cache() ) {
				return $post;
			}

			if ( ! isset( $attachment['foogallery_crop_pos'] ) ) {
				return $post;
			}

			if ( $attachment['foogallery_crop_pos'] == $this->default_crop_position() ) {
				delete_post_meta( $post['ID'], self::CROP_POSITION_META_KEY );
			} else {
				update_post_meta( $post['ID'], self::CROP_POSITION_META_KEY, $attachment['foogallery_crop_pos'] );
			}

			return $post;
		}
	}
}