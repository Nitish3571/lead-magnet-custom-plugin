<?php

// Function to manage Lead Magnet forms
function lead_magnet_pro_manage_forms() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lead_magnet_pro_forms';

    if (isset($_POST['add_form'])) {
        $title = sanitize_text_field($_POST['form_title']);
        $shortcode = sanitize_textarea_field($_POST['form_shortcode']);
        $download_link = sanitize_text_field($_POST['form_download_link']);

        $wpdb->insert(
            $table_name,
            array(
                'title' => $title,
                'shortcode' => $shortcode,
                'download_link' => $download_link
            )
        );
        echo '<div class="notice notice-success is-dismissible"><p>Form added successfully.</p></div>';
    }

    if (isset($_POST['delete_form'])) {
        $id = intval($_POST['form_id']);
        $wpdb->delete($table_name, array('id' => $id));
        echo '<div class="notice notice-success is-dismissible"><p>Form deleted successfully.</p></div>';
    }

    if (isset($_POST['update_form'])) {
        $id = intval($_POST['form_id']);
        $title = sanitize_text_field($_POST['form_title']);
        $shortcode = sanitize_textarea_field($_POST['form_shortcode']);
        $download_link = sanitize_text_field($_POST['form_download_link']);

        $wpdb->update(
            $table_name,
            array(
                'title' => $title,
                'shortcode' => $shortcode,
                'download_link' => $download_link
            ),
            array('id' => $id)
        );
        echo '<div class="notice notice-success is-dismissible"><p>Form updated successfully.</p></div>';
    }

    $results = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div class="wrap">
        <h1>Manage Lead Magnet Forms</h1>
        <form method="POST">
            <h2>Add New Form</h2>
            <input type="text" name="form_title" placeholder="Form Title" required>
            <input type="text" name="form_shortcode" placeholder="Form Shortcode" required></input>
            <input type="text" name="form_download_link" placeholder="Download Link" required>
            <input type="submit" name="add_form" class="button button-primary" value="Add Form">
        </form>
        <h2>Existing Forms</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Shortcode</th>
                    <th>Download Link</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $form) { ?>
                <tr>
                    <td><?php echo $form->id; ?></td>
                    <td><?php echo esc_html($form->title); ?></td>
                    <td><?php echo esc_html($form->shortcode); ?></td>
                    <td><?php echo esc_html($form->download_link); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="form_id" value="<?php echo $form->id; ?>">
                            <input type="text" name="form_title" value="<?php echo esc_attr($form->title); ?>" placeholder="Title">
                            <input type="text" name="form_shortcode" placeholder="Shortcode" value="<?php echo esc_attr($form->shortcode); ?>"></input>
                            <input type="text" name="form_download_link" value="<?php echo esc_attr($form->download_link); ?>" placeholder="Download Link">
                            <input type="submit" name="update_form" class="button button-primary" value="Update">
                            <input type="submit" name="delete_form" class="button button-danger" value="Delete">
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Function to export all leads as CSV
function lmp_export_csv() {
    // Verify nonce
    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'lmp_export_csv_nonce')) {
        wp_die('Invalid nonce');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'lead_magnet_pro_leads';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="leads-' . date("Y-m-d") . '.csv"');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $output = fopen('php://output', 'w');

    // Output column headers
    fputcsv($output, array('ID', 'Name', 'Email', 'Submitted On', 'Form ID' , 'Download Link'));

    // Fetch all leads
    $leads = $wpdb->get_results(
        "SELECT id, name, email, time, form_id , download_link FROM $table_name",
        ARRAY_A
    );

    // Output data
    foreach ($leads as $lead) {
        fputcsv($output, array(
            $lead['id'],
            $lead['name'],
            $lead['email'],
            $lead['time'],
            $lead['form_id'],
            $lead['download_link']
        ));
    }

    fclose($output);
    exit;
}

// Hook the CSV export function to admin_post
add_action('admin_post_lmp_export_csv', 'lmp_export_csv');

// Function to manage Lead Magnet leads
function lead_magnet_pro_manage_lead_magnets() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lead_magnet_pro_leads';

    if (isset($_POST['bulk_action']) && $_POST['bulk_action'] === 'delete') {
        if (isset($_POST['lead_ids'])) {
            foreach ($_POST['lead_ids'] as $lead_id) {
                $wpdb->delete($table_name, array('id' => intval($lead_id)));
            }
            echo '<div class="notice notice-success is-dismissible"><p>Selected leads deleted successfully.</p></div>';
        }
    }

    $results = $wpdb->get_results("SELECT * FROM $table_name");

    // Generate nonce for CSV export
    $nonce = wp_create_nonce('lmp_export_csv_nonce');

    // URL for exporting CSV
    $export_url = admin_url('admin-post.php?action=lmp_export_csv&nonce=' . $nonce);

    ?>
    <div class="wrap">
        <h1>Manage Lead Magnets</h1>

        <!-- Export CSV Link -->
        <a href="<?php echo esc_url($export_url); ?>" class="button">Export CSV</a>
        
        <form method="POST">
            
            <input type="hidden" name="bulk_action" value="delete">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="manage-column check-column"><input type="checkbox" id="cb-select-all-1"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Submitted On</th>
                        <th>Form ID</th>
                        <th>Download Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row) { ?>
                    <tr>
                        <th class="check-column"><input type="checkbox" name="lead_ids[]" value="<?php echo esc_attr($row->id); ?>"></th>
                        <td><?php echo esc_html($row->id); ?></td>
                        <td><?php echo esc_html($row->name); ?></td>
                        <td><?php echo esc_html($row->email); ?></td>
                        <td><?php echo esc_html($row->time); ?></td>
                        <td><?php echo esc_html($row->form_id); ?></td>
                        <td><?php echo esc_html($row->download_link); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <input type="submit" class="button button-danger" value="Delete Selected">
        </form>
    </div>
    <?php
}
?>
