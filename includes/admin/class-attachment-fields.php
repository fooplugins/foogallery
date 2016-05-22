<?php
/**
 * class FooGallery_Attachment_Fields
 *
 * Add custom fields to media attachments
 */
if (!class_exists('FooGallery_Attachment_Fields')) {

    class FooGallery_Attachment_Fields {

        function __construct() {
	        add_filter( 'attachment_fields_to_edit', array( $this, 'add_fields' ), 9, 2 );
	        add_filter( 'attachment_fields_to_save', array( $this, 'save_fields' ), 11, 2 );
        }

	    public function get_custom_fields() {

		    $target_options = apply_filters( 'foogallery_attachment_field_custom_target_options',  array(
			    'default' => __( 'Default', 'foogallery' ),
			    '_blank' => __( 'New tab (_blank)', 'foogallery' ),
			    '_self' => __( 'Same tab (_self)', 'foogallery' ),
			    'foobox' => __( 'FooBox', 'foogallery' )
		    ) );

		    $fields = array(
			    'foogallery_custom_url' => array(
				    'label'       =>  __( 'Custom URL', 'foogallery' ),
				    'input'       => 'text', //other types are 'textarea', 'checkbox', 'radio', 'select',
				    'helps'       => __( 'Point your attachment to a custom URL', 'foogallery' ),
				    'exclusions'  => array( 'audio', 'video' ),
			    ),

			    'foogallery_custom_target' => array(
				    'label'       =>  __( 'Custom Target', 'foogallery' ),
				    'input'       => 'select',
				    'helps'       => __( 'Set a custom target for your attachment', 'foogallery' ),
				    'exclusions'  => array( 'audio', 'video' ),
				    'options'     => $target_options
			    )
		    );

		    return apply_filters( 'foogallery_attachment_custom_fields', $fields );
	    }

	    /**
	     * @param      $form_fields
	     * @param WP_Post $post
	     *
	     * @return mixed
	     */
	    public function add_fields( $form_fields, $post = null ) {
		    $custom_fields = $this->get_custom_fields();

		    // If our fields array is not empty
		    if ( ! empty( $custom_fields ) ) {
			    // We browse our set of options
			    foreach ( $custom_fields as $field => $values ) {
				    // If the field matches the current attachment mime type
				    // and is not one of the exclusions
				    if ( !in_array( $post->post_mime_type, $values['exclusions'] ) ) {
					    // We get the already saved field meta value
					    $meta = apply_filters( 'foogallery_attachment_custom_field_value', get_post_meta( $post->ID, '_' . $field, true ), $post->ID, $field, $values );

					    switch ( $values['input'] ) {
						    default:
						    case 'text':
							    $values['input'] = 'text';
							    break;

						    case 'textarea':
							    $values['input'] = 'textarea';
							    break;

						    case 'select':

							    // Select type doesn't exist, so we will create the html manually
							    // For this, we have to set the input type to 'html'
							    $values['input'] = 'html';

							    // Create the select element with the right name (matches the one that wordpress creates for custom fields)
							    $html = '<select name="attachments[' . $post->ID . '][' . $field . ']">';

							    // If options array is passed
							    if ( isset( $values['options'] ) ) {
								    // Browse and add the options
								    foreach ( $values['options'] as $k => $v ) {
									    // Set the option selected or not
									    if ( $meta == $k )
										    $selected = ' selected="selected"';
									    else
										    $selected = '';

									    $html .= '<option' . $selected . ' value="' . $k . '">' . $v . '</option>';
								    }
							    }

							    $html .= '</select>';

							    // Set the html content
							    $values['html'] = $html;

							    break;

						    case 'checkbox':

							    // Checkbox type doesn't exist either
							    $values['input'] = 'html';

							    // Set the checkbox checked or not
							    if ( $meta == 'on' )
								    $checked = ' checked="checked"';
							    else
								    $checked = '';

							    $html = '<input' . $checked . ' type="checkbox" name="attachments[' . $post->ID . '][' . $field . ']" id="attachments-' . $post->ID . '-' . $field . '" />';

							    $values['html'] = $html;

							    break;

						    case 'radio':

							    // radio type doesn't exist either
							    $values['input'] = 'html';

							    $html = '';

							    if ( ! empty( $values['options'] ) ) {
								    $i = 0;

								    foreach ( $values['options'] as $k => $v ) {
									    if ( $meta == $k )
										    $checked = ' checked="checked"';
									    else
										    $checked = '';

									    $html .= '<input' . $checked . ' value="' . $k . '" type="radio" name="attachments[' . $post->ID . '][' . $field . ']" id="' . sanitize_key( $field . '_' . $post->ID . '_' . $i ) . '" /> <label for="' . sanitize_key( $field . '_' . $post->ID . '_' . $i ) . '">' . $v . '</label><br />';
									    $i++;
								    }
							    }

							    $values['html'] = $html;

							    break;
					    }

					    // And set it to the field before building it
					    $values['value'] = $meta;

					    // We add our field into the $form_fields array
					    $form_fields[$field] = apply_filters( 'foogallery_attachment_field_' . $field, $values, $post->ID );
				    }
			    }
		    }

		    // We return the completed $form_fields array
		    return apply_filters( 'foogallery_attachment_add_fields', $form_fields );
	    }

	    function save_fields( $post, $attachment ) {
		    $custom_fields = $this->get_custom_fields();

		    // If our fields array is not empty
		    if ( ! empty( $custom_fields ) ) {
			    // We browse our set of options
			    foreach ( $custom_fields as $field => $values ) {
				    switch ( $values['input'] ) {
					    case 'text':
					    case 'textarea':
					    case 'select':
					    case 'radio':
					    case 'checkbox':
						    // If this field has been submitted (is present in the $attachment variable)
						    if ( isset( $attachment[$field] ) ) {
							    // If submitted field is empty
							    // We add errors to the post object with the "error_text" parameter if set in the options
							    if ( strlen( trim( $attachment[$field] ) ) == 0 && isset( $values['error_text'] ) ) {
								    $post['errors'][ $field ]['errors'][] = __( $values['error_text'] );
								    // Otherwise we update the custom field
							    } else {
								    update_post_meta( $post['ID'], '_' . $field, $attachment[ $field ] );
							    }
						    }
						    // Otherwise, we delete it if it already existed
						    else {
							    delete_post_meta( $post['ID'], $field );
						    }
					        break;

					    default:
						    do_action( 'foogallery_attachment_save_field_' . $values['input'], $field, $values, $post, $attachment);
				    }
			    }
		    }

		    return $post;
	    }
    }
}