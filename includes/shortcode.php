<?php

function lead_magnet_pro_form_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lead_magnet_pro_forms';

    // Extract attributes from shortcode
    $atts = shortcode_atts(array(
        'id' => ''
    ), $atts);

    $form_id = intval($atts['id']);
    if (!$form_id) {
        return '<p>Invalid form ID.</p>';
    }

    // Get specific form based on ID
    $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $form_id));
    if (!$form) {
        return '<p>Form not found.</p>'; 
    }

    ob_start();
    ?>
    <div class="lead-magnet-pro-form-container">
        <form class="lead-magnet-pro-form" data-form-id="<?php echo $form->id; ?>" method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>">
            <input type="hidden" name="action" value="lead_magnet_pro_form">
            <input type="hidden" name="form_id" value="<?php echo $form->id; ?>">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit" style="padding:10px">Download</button>
        </form>
        <div class="lead-magnet-pro-response" id="response-<?php echo $form->id; ?>"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('lead_magnet_pro_form', 'lead_magnet_pro_form_shortcode');

if (!function_exists('lead_magnet_pro_enqueue_form_script')) {
    // Enqueue form submission script
    function lead_magnet_pro_enqueue_form_script() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.lead-magnet-pro-form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formId = form.data('form-id');
                var data = form.serialize();

                $.post(form.attr('action'), data, function(response) {
                    var responseDiv = $('#response-' + formId);
                    if (response.success) {
                        responseDiv.html('<p>' + response.data.message + '</p>');
                        form[0].reset();
                    } else {
                        responseDiv.html('<p>' + response.data + '</p>');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'lead_magnet_pro_enqueue_form_script');
?>
