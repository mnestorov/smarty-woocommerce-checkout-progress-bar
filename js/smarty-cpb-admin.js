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

    // Handle tab switching
    $(".smarty-cpb-nav-tab").click(function (e) {
        e.preventDefault();
        $(".smarty-cpb-nav-tab").removeClass("smarty-cpb-nav-tab-active");
        $(this).addClass("smarty-cpb-nav-tab-active");

        $(".smarty-cpb-tab-content").removeClass("active");
        $($(this).attr("href")).addClass("active");
    });

    // Load README.md
    $("#smarty-cpb-load-readme-btn").click(function () {
        const $content = $("#smarty-cpb-readme-content");
        $content.html("<p>Loading...</p>");

        $.ajax({
            url: smartyCheckoutProgressBar.ajaxUrl,
            type: "POST",
            data: {
                action: "smarty_cpb_load_readme",
                nonce: smartyCheckoutProgressBar.nonce,
            },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $content.html(response.data);
                } else {
                    $content.html("<p>Error loading README.md</p>");
                }
            },
        });
    });

    // Load CHANGELOG.md
    $("#smarty-cpb-load-changelog-btn").click(function () {
        const $content = $("#smarty-cpb-changelog-content");
        $content.html("<p>Loading...</p>");

        $.ajax({
            url: smartyCheckoutProgressBar.ajaxUrl,
            type: "POST",
            data: {
                action: "smarty_cpb_load_changelog",
                nonce: smartyCheckoutProgressBar.nonce,
            },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $content.html(response.data);
                } else {
                    $content.html("<p>Error loading CHANGELOG.md</p>");
                }
            },
        });
    });
});
