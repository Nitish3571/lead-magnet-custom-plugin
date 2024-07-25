<?php
/*
Plugin Name: Lead Magnet
Description: Create and manage lead magnet forms dynamically.
Version: 1.4
Author: WebNX
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin path
define('LEAD_MAGNET_PRO_PATH', plugin_dir_path(__FILE__));

// Include necessary files
require_once LEAD_MAGNET_PRO_PATH . 'includes/admin.php';
require_once LEAD_MAGNET_PRO_PATH . 'includes/frontend.php';
require_once LEAD_MAGNET_PRO_PATH . 'includes/shortcode.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'lead_magnet_pro_activate');
register_deactivation_hook(__FILE__, 'lead_magnet_pro_deactivate');

// Register plugin activation function
function lead_magnet_pro_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lead_magnet_pro_leads';
    $forms_table = $wpdb->prefix . 'lead_magnet_pro_forms';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        form_id mediumint(9) NOT NULL,
        download_link text DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $forms_sql = "CREATE TABLE $forms_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title tinytext NOT NULL,
        shortcode text NOT NULL,
        download_link text DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($forms_sql);
}

// Register plugin deactivation function
function lead_magnet_pro_deactivate() {
    // Optionally, code to run on plugin deactivation
}

// Enqueue scripts and styles
function lead_magnet_pro_enqueue_scripts() {
    wp_enqueue_style('lead-magnet-pro-style', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('lead-magnet-pro-script', plugins_url('js/script.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'lead_magnet_pro_enqueue_scripts');

// Create admin menu
function lead_magnet_pro_admin_menu() {
    add_menu_page('Lead Magnet Pro', 'Lead Magnet Pro', 'manage_options', 'lead-magnet-pro', 'lead_magnet_pro_admin_page', 'dashicons-admin-generic', 110);
    add_submenu_page('lead-magnet-pro', 'Manage Forms', 'Manage Forms', 'manage_options', 'lead-magnet-forms', 'lead_magnet_pro_manage_forms');
    add_submenu_page('lead-magnet-pro', 'Manage Leads', 'Manage Leads', 'manage_options', 'lead-magnet-leads', 'lead_magnet_pro_manage_lead_magnets');
}
add_action('admin_menu', 'lead_magnet_pro_admin_menu');

// Admin page callback
function lead_magnet_pro_admin_page() {
    global $wpdb;
    $leads_table = $wpdb->prefix . 'lead_magnet_pro_leads';
    $forms_table = $wpdb->prefix . 'lead_magnet_pro_forms';

    // Get total number of leads
    $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table");

    // Get total number of forms
    $total_forms = $wpdb->get_var("SELECT COUNT(*) FROM $forms_table");

    $recent_submissions = $wpdb->get_results(
        "SELECT email, count(id) as total_leads FROM $leads_table Group by email ORDER BY total_leads DESC "
    );

    ?>
    <div class="wrap">
        <h1>Lead Magnet Pro Dashboard</h1>
        <p>Welcome to Lead Magnet Pro! Use the menu on the left to create and manage lead magnets and forms.</p>
        
        <h2>Dashboard Overview</h2>
        <div class="dashboard-overview">
            <ul>
                <li><strong>Total Lead Forms:</strong> <?php echo number_format($total_forms); ?></li>
                <li><strong>Total Leads Submissions:</strong> <?php echo number_format($total_leads); ?></li>
            </ul>
        </div>
        
        <h2>User Tracking</h2>
        <?php if (empty($recent_submissions)) : ?>
            <p>No recent submissions.</p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Total Leads</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_submissions as $submission) : ?>
                    <tr>
                        <td><?php echo esc_html($submission->email); ?></td>
                        <td><?php echo esc_html($submission->total_leads); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <style>
    .dashboard-overview {
       
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .dashboard-overview ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        align-items: center;
        
    }
    .dashboard-overview ul li {
        font-size: 18px;
        margin-bottom: 10px;
        min-height: 50px;
        min-width: 10%;
        display: flex;
        align-items: center;
        padding: 20px;
        background-color: #f1f1f1;
        border: 1px solid #ddd;
        margin-right: 20px;
    }

    .dashboard-overview ul li strong {
        display: inline-block;
        width: 150px;
    }
    @media only screen and (max-width: 600px) {
        .dashboard-overview ul {
        display: block;
        }
        .dashboard-overview ul li {
        margin-right: 0px;
    }
    }
</style>
    <?php
}
?>
