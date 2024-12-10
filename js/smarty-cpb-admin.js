jQuery(document).ready(function ($) {
    // Media uploader
    $('.smarty-cpb-upload-button').on('click', function (e) {
        e.preventDefault();
        const targetInput = $($(this).data('target'));
        const mediaUploader = wp.media({
            title: 'Select or Upload Icon',
            button: {
                text: 'Use This Icon'
            },
            multiple: false
        }).on('select', function () {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            targetInput.val(attachment.url);
        }).open();
    });

    // Remove icon
    $('.smarty-cpb-remove-button').on('click', function (e) {
        e.preventDefault();
        const targetInput = $($(this).data('target'));
        targetInput.val(''); // Clear the URL input
    });
});
