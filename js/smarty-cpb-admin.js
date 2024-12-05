jQuery(document).ready(function ($) {
    $('.upload-svg-button').on('click', function (e) {
        e.preventDefault();

        const targetInput = $(this).data('target');
        const fileFrame = wp.media({
            title: 'Select or Upload an SVG',
            button: {
                text: 'Use this SVG',
            },
            library: {
                type: 'image',
            },
            multiple: false,
        });

        fileFrame.on('select', function () {
            const attachment = fileFrame.state().get('selection').first().toJSON();
            $('#' + targetInput).val(attachment.url);
            $('#' + targetInput)
                .siblings('.svg-preview')
                .html('<img src="' + attachment.url + '" alt="SVG Preview" style="max-width: 100px; max-height: 100px;" />');
        });

        fileFrame.open();
    });
});
