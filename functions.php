<?php
//WP functions

// Disable auto-update emails.
add_filter( 'auto_core_update_send_email', '__return_false' );

// Disable auto-update emails for plugins.
add_filter( 'auto_plugin_update_send_email', '__return_false' );

// Disable auto-update emails for themes.
add_filter( 'auto_theme_update_send_email', '__return_false' );


/**
 * Set default sending email address and name
 * Hooks: wp_mail_from, wp_mail_from_name
 */
function wpb_sender_email( $original_email_address ) {
    return 'noreply@email.com';
}
// Function to change sender name
function wpb_sender_name( $original_email_from ) {
    return "NAME";
}
// Hooking up our functions to WordPress filters 
add_filter( 'wp_mail_from', 'wpb_sender_email' );
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );



// Custom wp-admin code output in head
add_action('admin_head', 'wsp_custom_admin_css');
function wsp_custom_admin_css() {
  echo '<style>
    .wp-list-table #featured_image {
		width: 150px;
  } 
		.wp-list-table .featured_image .attachment-thumbnail {
			height: 100px;
			width: 100px;
}
</style>';
}




// Mark contact form entries as spam 
add_filter( 'gform_entry_is_spam_3', 'filter_gform_entry_is_spam_urls', 11, 3 );
add_filter( 'gform_entry_is_spam_1', 'filter_gform_entry_is_spam_urls', 11, 3 );
function filter_gform_entry_is_spam_urls( $is_spam, $form, $entry ) {
    if ( $is_spam ) {
        return $is_spam;
    }
 
    $field_types_to_check = array(
        'hidden',
        'text',
        'textarea',
    );
 
	$field_id = '1'; // The ID of the field containing the first name to be checked.
	$fname    = rgar( $entry, $field_id );
	$field_id = '3'; // The ID of the field containing the last name to be checked.
	$lname    = rgar( $entry, $field_id );

	if ( $fname == $lname ) {
		// Mark entries with same first and last name as spam
		return true;
	}


	$field_id = '5'; // The ID of the field containing the email address to be checked.
	$email    = rgar( $entry, $field_id );
	// Mark entries with these email address endings as spam
	$endings = array('\.ru'); // you can add zones here
	if ( preg_match('/('.implode('|', $endings).')$/i', $email) ) {
		return true;
	}


    foreach ( $form['fields'] as $field ) {
        // Skipping fields which are administrative or the wrong type.
        if ( $field->is_administrative() || ! in_array( $field->get_input_type(), $field_types_to_check ) ) {
            continue;
        }
 
        // Skipping fields which don't have a value.
        $value = $field->get_value_export( $entry );
        if ( empty( $value ) ) {
            continue;
        }
 
        // If value contains a URL mark submission as spam.
        /*if ( preg_match( '~(https?|ftp):\/\/\S+~', $value ) ) {
            return true;
        }*/

        // If value contains Russian characters mark as spam.
        if ( preg_match( '/[А-Яа-яЁё]/u', $value ) ) {
            return true;
        }
    }
 
    return false;
}


?>
