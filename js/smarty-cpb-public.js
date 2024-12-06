jQuery(document).ready(function ($) {
    function updateProgressBar() {
        // Get thresholds and texts from localized settings
        const giftOneThreshold = parseFloat(smartyCpbSettings.giftOneThreshold);
        const giftTwoThreshold = parseFloat(smartyCpbSettings.giftTwoThreshold);
        const giftOneText = smartyCpbSettings.giftOneText;
        const giftTwoText = smartyCpbSettings.giftTwoText;
        const allRewardsText = smartyCpbSettings.allRewardsText;
		const customTextFormat = smartyCpbSettings.customTextFormat;

        let cartTotal = 0;

        // Get cart total from checkout or cart page
        if ($('.woocommerce-checkout-review-order-table').length) {
            // Checkout page
            const cartTotalText = $('.order-total .woocommerce-Price-amount bdi').first().text();
            cartTotal = parseFloat(cartTotalText.replace(/[^0-9.,]+/g, '').replace(',', '.'));
        } else if ($('.woocommerce-cart-form').length) {
            // Cart page
            const cartTotalText = $('.order-total .woocommerce-Price-amount bdi').first().text();
            cartTotal = parseFloat(cartTotalText.replace(/[^0-9.,]+/g, '').replace(',', '.'));
        }

        if (isNaN(cartTotal)) {
            cartTotal = 0;
        }

        // Calculate remaining amounts
        const remainingToGiftOne = Math.max(giftOneThreshold - cartTotal, 0).toFixed(2);
        const remainingToGiftTwo = Math.max(giftTwoThreshold - cartTotal, 0).toFixed(2);

        // Determine the info text
        let infoText = '';
        if (cartTotal < giftOneThreshold) {
            const priceSpan = `<span style="color: ${smartyCpbSettings.giftOneColor};">${remainingToGiftOne} ${smartyCpbSettings.currencySymbol}</span>`;
            infoText = customTextFormat.replace('%s', priceSpan).replace('%s', giftOneText);
        } else if (cartTotal < giftTwoThreshold) {
            const priceSpan = `<span style="color: ${smartyCpbSettings.giftTwoColor};">${remainingToGiftTwo} ${smartyCpbSettings.currencySymbol}</span>`;
            infoText = customTextFormat.replace('%s', priceSpan).replace('%s', giftTwoText);
        } else {
            infoText = `<span style="color: ${smartyCpbSettings.allRewardsColor};">${allRewardsText}</span>`;
        }

        // Update the progress bar
        const progress = Math.min((cartTotal / giftTwoThreshold) * 100, 100);
        $('.smarty-cpb-progress-bar-fill').css('width', progress + '%');

        // Update the info text
        $('.smarty-cpb-info-text').html(infoText);

        // Update icon states
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

    // Initial update on page load
    updateProgressBar();

    // Recalculate progress bar on WooCommerce cart and checkout updates
    $(document.body).on('updated_cart_totals updated_checkout updated_shipping_method', function () {
        updateProgressBar();
    });
});
