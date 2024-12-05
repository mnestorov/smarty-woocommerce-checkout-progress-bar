jQuery(document).ready(function ($) {
    function updateProgressBar() {
        const freeDeliveryThreshold = parseFloat($('#smarty-cpb').data('free-delivery'));
        const freeGiftThreshold = parseFloat($('#smarty-cpb').data('free-gift'));

        let cartTotal = 0;

        // Try to get cart total from the page
        if ($('.woocommerce-cart-form').length) {
            // On Cart page
            cartTotal = parseFloat($('.order-total .woocommerce-Price-amount bdi').first().text().replace(/[^0-9.-]+/g, ''));
        } else if ($('.woocommerce-checkout-review-order-table').length) {
            // On Checkout page
            cartTotal = parseFloat($('.order-total .woocommerce-Price-amount bdi').first().text().replace(/[^0-9.-]+/g, ''));
        } else {
            // Fallback
            cartTotal = parseFloat($('.woocommerce-Price-amount bdi').first().text().replace(/[^0-9.-]+/g, ''));
        }

        if (isNaN(cartTotal)) {
            cartTotal = 0;
        }

        const remainingToFirstGift = Math.max(freeDeliveryThreshold - cartTotal, 0).toFixed(2);
        const progress = Math.min((cartTotal / freeGiftThreshold) * 100, 100);

        $('.smarty-cpb-progress-bar-fill').css('width', progress + '%');
        $('.remaining-amount').text(`$${remainingToFirstGift}`);

        // Update ticks
        $('.tick').removeClass('achieved');
        if (cartTotal >= freeDeliveryThreshold) {
            $('.tick:eq(1)').addClass('achieved');
        }
        if (cartTotal >= freeGiftThreshold) {
            $('.tick:eq(2)').addClass('achieved');
        }
    }

    // Initial load
    updateProgressBar();

    // Update on cart changes
    $(document.body).on('updated_cart_totals updated_checkout', function () {
        updateProgressBar();
    });
});
