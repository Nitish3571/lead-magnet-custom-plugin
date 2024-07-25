<?php

function lead_magnet_pro_handle_form_submission() {
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['form_id'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lead_magnet_pro_leads';
        $forms_table = $wpdb->prefix . 'lead_magnet_pro_forms';

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $form_id = intval($_POST['form_id']);

        // Retrieve download link based on form ID
        $form = $wpdb->get_row($wpdb->prepare("SELECT download_link FROM $forms_table WHERE id = %d", $form_id));
        if (!$form) {
            wp_send_json_error('Invalid form ID.');
            return;
        }

        $download_link = $form->download_link;

        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'time' => current_time('mysql'),
                'form_id' => $form_id,
                'download_link' => $download_link
            )
        );

        // Output the download link
        wp_send_json_success(array('message' => 'Thank you for your submission!', 'download_link' => $download_link));
    } else {
        wp_send_json_error('Invalid form submission.');
    }
}
add_action('wp_ajax_lead_magnet_pro_form', 'lead_magnet_pro_handle_form_submission');
add_action('wp_ajax_nopriv_lead_magnet_pro_form', 'lead_magnet_pro_handle_form_submission');
?>
