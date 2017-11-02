<?php

if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Fields' ) ) {

	class FooGallery_Admin_Gallery_MetaBox_Fields {

		function __construct() {
			//render the different types of fields for our gallery settings
			add_action( 'foogallery_render_gallery_template_field', array( $this, 'render_gallery_template_field' ), 10, 3 );
		}

		/**
		 * Renders a gallery template field into the gallery settings metabox for a FooGallery
		 *
		 * @param array $field
		 * @param       $gallery FooGallery
		 * @param       $template
		 */
		function render_gallery_template_field( $field = array(), $gallery, $template ) {
			$template_slug = $template['slug'];

			//only declare up front so no debug warnings are shown
			$type = $id = $desc = $default = $placeholder = $choices = $class = $spacer = $opactiy = null;

			extract( $field );

			$id = $template_slug . '_' . $id;

			$field['value'] = apply_filters( 'foogallery_render_gallery_template_field_value', $gallery->get_meta( $id, $default ), $field, $gallery, $template );

			$field_class = empty($class) ? '' : ' class="' . $class . '"';

			$field['choices'] = apply_filters( 'foogallery_render_gallery_template_field_choices', $choices, $field, $gallery );

			//allow for UI customization
			do_action( 'foogallery_render_gallery_template_field_before', $field, $gallery );

			echo '<div class="foogallery_metabox_field-' . $type . '">';

			switch ( $type ) {

				case 'html':
					echo $desc;
					$desc = '';
					break;

				case 'checkbox':
					if ( isset($gallery->settings[$id]) && $gallery->settings[$id] == 'on' ) {
						$field['value'] = 'on';
					} else if ( ! isset($gallery->settings) && $default == 'on' ) {
						$field['value'] = 'on';
					} else {
						$field['value'] = '';
					}

					$checked = 'on' === $field['value'] ? ' checked="checked"' : '';
					echo '<input' . $field_class . ' type="checkbox" id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" value="on"' . $checked . ' />';
					break;

				case 'select':
					echo '<select' . $field_class . ' id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']">';
					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $field['value'] == $value ) {
							$selected = ' selected="selected"';
						}
						echo '<option ' . $selected . ' value="' . $value . '">' . $label . '</option>';
					}

					echo '</select>';
					break;

				case 'radio':
					$i = 0;
					$spacer = isset($spacer) ? $spacer : '<br />';
					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $field['value'] == $value ) {
							$selected = ' checked="checked"';
						}
						echo '<input' . $field_class . $selected . ' type="radio" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']"  id="FooGallerySettings_' . $id . $i . '" value="' . $value . '"> <label for="FooGallerySettings_' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo $spacer;
						}
						$i++;
					}
					break;

				case 'textarea':
					echo '<textarea' . $field_class . ' id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" placeholder="' . $placeholder . '">' . esc_attr( $field['value'] ) . '</textarea>';

					break;

				case 'text':
					echo '<input' . $field_class . ' type="text" id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" value="' . esc_attr( $field['value'] ) . '" />';

					break;

				case 'colorpicker':

					$opacity_attribute = empty($opacity) ? '' : ' data-show-alpha="true"';

					echo '<input ' . $opacity_attribute . ' class="colorpicker" type="text" id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" value="' . esc_attr( $field['value'] ) . '" />';

					break;

				case 'number':
					$min = isset($min) ? $min : 0;
					$step = isset($step) ? $step : 1;
					echo '<input class="regular-text ' . $class . '" type="number" step="' . $step . '" min="' . $min .'" id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" placeholder="' . $placeholder . '" value="' . esc_attr( $field['value'] ) . '" />';

					break;

				case 'checkboxlist':
					$i = 0;
					foreach ( $choices as $value => $label ) {

						$checked = '';
						if ( isset($field['value'][$value]) && $field['value'][$value] == $value ) {
							$checked = 'checked="checked"';
						}

						echo '<input' . $field_class . ' ' . $checked . ' type="checkbox" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][' . $value . ']" id="FooGallerySettings_' . $id . $i . '" value="' . $value . '" data-value="' . $value . '"> <label for="FooGallerySettings_' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;
				case 'icon':
					$i = 0;
					$input_name = FOOGALLERY_META_SETTINGS . '[' . $id . ']';
					$icon_html = '';
					foreach ( $choices as $value => $icon ) {
						$selected = ( $field['value'] == $value ) ? ' checked="checked"' : '';
						$icon_html .= '<input style="display:none" name="' . $input_name. '" id="FooGallerySettings_' . $id . $i . '" ' . $selected . ' type="radio" value="' . $value . '" tabindex="' . $i . '"/>';
						$title = $icon['label'];
						$img = $icon['img'];
						$icon_html .= '<label for="FooGallerySettings_' . $id . $i . '" data-balloon-length="small" data-balloon-pos="down" data-balloon="' . $title . '"><img src="' . $img . '" /></label>';
						$i++;
					}
					echo $icon_html;
					break;

				case 'htmlicon':
					$i = 0;
					$input_name = FOOGALLERY_META_SETTINGS . '[' . $id . ']';
					$icon_html = '';
					foreach ( $choices as $value => $icon ) {
						$selected = ( $field['value'] == $value ) ? ' checked="checked"' : '';
						$icon_html .= '<input style="display:none" name="' . $input_name. '" id="FooGallerySettings_' . $id . $i . '" ' . $selected . ' type="radio" value="' . $value . '" tabindex="' . $i . '"/>';
						$title = $icon['label'];
						$html = $icon['html'];
						$icon_html .= '<label for="FooGallerySettings_' . $id . $i . '" data-balloon-length="small" data-balloon-pos="down" data-balloon="' . $title . '">' . $html . '</label>';
						$i++;
					}
					echo $icon_html;
					break;

				case 'thumb_size':
					$width = is_array( $field['value'] ) ? $field['value']['width'] : 150;
					$height = is_array( $field['value'] ) ? $field['value']['height'] : 150;
					$crop = is_array( $field['value'] ) && array_key_exists( 'crop', $field['value'] ) ? $field['value']['crop'] : 0;
					$crop_checked = ( $crop == 1 ) ? ' checked="checked"' : '';
					echo '<label for="FooGallerySettings_' . $id . '_width">' . __( 'Width', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . $id . '_width" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][width]" value="' . esc_attr( $width ) . '" />';
					echo '<label for="FooGallerySettings_' . $id . '_width">' . __( 'Height', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . $id . '_height" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][height]" value="' . esc_attr( $height ) . '" />';
					echo '<div class="foogallery-thumbsize-crop"><input name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][crop]" type="hidden" id="FooGallerySettings_' . $id . '_nocrop" value="0" />';
					echo '<input name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][crop]" type="checkbox" id="FooGallerySettings_' . $id . '_crop" value="1"' . $crop_checked . '>';
					echo '<label for="FooGallerySettings_' . $id . '_crop">' . __( 'Crop thumbnail to exact dimensions', 'foogallery' ) . '</label></div>';
					break;

				case 'thumb_size_no_crop':
					$width = is_array( $field['value'] ) ? $field['value']['width'] : 150;
					$height = is_array( $field['value'] ) ? $field['value']['height'] : 150;
					echo '<label for="FooGallerySettings_' . $id . '_width">' . __( 'Width', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . $id . '_width" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][width]" value="' . esc_attr( $width ) . '" />';
					echo '<label for="FooGallerySettings_' . $id . '_width">' . __( 'Height', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . $id . '_height" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '][height]" value="' . esc_attr( $height ) . '" />';
					break;

				default:
					do_action( 'foogallery_render_gallery_template_field_custom', $field, $gallery, $template );
					break;
			}

			if (!empty($suffix)) {
				echo $suffix;
			}

			echo '</div>';

			//allow for more customization
			do_action( 'foogallery_render_gallery_template_field_after', $field, $gallery );
		}
	}
}
