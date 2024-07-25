// JavaScript for Lead Magnet Pro Plugin
jQuery(document).ready(function($) {
    $('#lead-magnet-pro-form').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var data = form.serialize();

        $.post(form.attr('action'), data, function(response) {
            var responseDiv = $('#lead-magnet-pro-response');
            if (response.success) {
                responseDiv.html('<p>' + response.data.message + '</p><a href="' + response.data.download_link + '" target="_blank">Download your ebook</a>');
                form[0].reset();
            } else {
                responseDiv.html('<p>' + response.data + '</p>');
            }
        });
    });
});
