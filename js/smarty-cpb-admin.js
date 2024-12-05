jQuery(document).ready(function ($) {
    function updateProgressBar() {
        // Get thresholds and texts from localized settings
        const giftOneThreshold = parseFloat(smartyCpbSettings.giftOneThreshold);
        const giftTwoThreshold = parseFloat(smartyCpbSettings.giftTwoThreshold);
        const giftOneText = smartyCpbSettings.giftOneText;
        const giftTwoText = smartyCpbSettings.giftTwoText;
        const allRewardsText = smartyCpbSettings.allRewardsText;

        let cartTotal = 0;

        // Try to get cart total from the page
        if ($('.woocommerce-cart-form').length) {
            cartTotal = parseFloat($('.order-total .woocommerce-Price-amount bdi').first().text().replace(/[^0-9.-]+/g, ''));
        } else if ($('.woocommerce-checkout-review-order-table').length) {
            cartTotal = parseFloat($('.order-total .woocommerce-Price-amount bdi').first().text().replace(/[^0-9.-]+/g, ''));
        }

        if (isNaN(cartTotal)) {
            cartTotal = 0;
        }

        const remainingToGiftOne = Math.max(giftOneThreshold - cartTotal, 0).toFixed(2);
        const remainingToGiftTwo = Math.max(giftTwoThreshold - cartTotal, 0).toFixed(2);

        let infoText = '';
        if (cartTotal < giftOneThreshold) {
            infoText = `Add $${remainingToGiftOne} to unlock ${giftOneText}!`;
        } else if (cartTotal < giftTwoThreshold) {
            infoText = `Add $${remainingToGiftTwo} to unlock ${giftTwoText}!`;
        } else {
            infoText = allRewardsText;
        }

        // Update the progress bar
        const progress = Math.min((cartTotal / giftTwoThreshold) * 100, 100);
        $('.smarty-cpb-progress-bar-fill').css('width', progress + '%');

        // Update the info text
        $('.smarty-cpb-info-text').text(infoText);

        // Update icons and ticks
        $('.icon').each(function (index) {
            if (index === 0) {
                $(this).addClass('achieved');
            } else if (index === 1) {
                if (cartTotal >= giftOneThreshold) {
                    $(this).addClass('achieved');
                } else {
                    $(this).removeClass('achieved');
                }
            } else if (index === 2) {
                if (cartTotal >= giftTwoThreshold) {
                    $(this).addClass('achieved');
                } else {
                    $(this).removeClass('achieved');
                }
            }
        });
    }

    // Initial load
    updateProgressBar();

    // Update on cart changes
    $(document.body).on('updated_cart_totals updated_checkout', function () {
        updateProgressBar();
    });
});
