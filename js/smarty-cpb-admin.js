jQuery(document).ready(function ($) {
    function updateProgressBar(cartTotal) {
        const freeDeliveryThreshold = parseFloat(wcProgressBar.free_delivery_threshold);
        const freeGiftThreshold = parseFloat(wcProgressBar.free_gift_threshold);
        const progress = Math.min(100, (cartTotal / freeGiftThreshold) * 100);

        $(".progress-bar-fill").css("width", progress + "%");

        if (cartTotal >= freeDeliveryThreshold) {
            $(".tick:first").addClass("achieved");
        } else {
            $(".tick:first").removeClass("achieved");
        }

        if (cartTotal >= freeGiftThreshold) {
            $(".tick:last").addClass("achieved");
        } else {
            $(".tick:last").removeClass("achieved");
        }
    }

    // Initial load
    const initialCartTotal = parseFloat(wc_cart_params.total);
    updateProgressBar(initialCartTotal);

    // Update on cart changes
    $(document.body).on("updated_cart_totals", function () {
        const cartTotal = parseFloat($(".woocommerce-Price-amount bdi").text().replace(/[^0-9.-]+/g, ""));
        updateProgressBar(cartTotal);
    });
});
