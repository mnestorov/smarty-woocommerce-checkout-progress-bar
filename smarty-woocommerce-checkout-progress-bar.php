<?php
/**
 * Plugin Name: SM - WooCommerce Checkout Progress Bar
 * Plugin URI:  https://github.com/mnestorov/smarty-woocommerce-checkout-progress-bar
 * Description: Adds a progress bar to the WooCommerce checkout page indicating free delivery and free gift eligibility.
 * Version:     1.0.0
 * Author:      Martin Nestorov
 * Author URI:  https://github.com/mnestorov
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: smarty-woocommerce-checkout-progress-bar
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!function_exists('smarty_cpb_wc_checkout_progress_bar')) {
    /**
     * Add the progress bar to the checkout page.
     */
    function smarty_cpb_wc_checkout_progress_bar() {
        // Set thresholds
        $free_delivery_threshold = 50;  // Adjust the threshold for free delivery
        $free_gift_threshold     = 100; // Adjust the threshold for free gift

        // Get the current cart total
        $cart_total = WC()->cart->get_cart_contents_total();

        // Calculate the progress percentage
        $progress = min(100, ($cart_total / $free_gift_threshold) * 100);
        ?>
        <div id="smarty-cpb">
            <p class="smarty-cpb-info-text">
                <?php _e('Achieve rewards as you shop!', 'smarty-woocommerce-checkout-progress-bar'); ?>
            </p>
            <div class="smarty-cpb-wrapper">
                <div class="smarty-cpb-progress-bar">
                    <div class="smarty-cpb-progress-bar-fill" style="width: <?php echo esc_attr($progress); ?>%;"></div>
                </div>
                <div class="smarty-cpb-progress-bar-ticks">
                    <div class="tick <?php echo $cart_total >= $free_delivery_threshold ? 'achieved' : ''; ?>">
                        <i class="dashicons dashicons-car"></i>
                        <span><?php _e('Free Delivery', 'smarty-woocommerce-checkout-progress-bar'); ?></span>
                    </div>
                    <div class="tick <?php echo $cart_total >= $free_gift_threshold ? 'achieved' : ''; ?>">
                        <i class="dashicons dashicons-gift"></i>
                        <span><?php _e('Free Gift', 'smarty-woocommerce-checkout-progress-bar'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    add_action('woocommerce_before_checkout_form', 'smarty_cpb_wc_checkout_progress_bar', 5);
}

if (!function_exists('smarty_cpb_enqueue_scripts')) {
    /**
     * Enqueue plugin styles and scripts.
     */
    function smarty_cpb_enqueue_scripts() {
        if (is_checkout()) {
            wp_enqueue_style('smarty-cpb-styles', plugin_dir_url(__FILE__) . 'css/smarty-cpb-admin.css');
            wp_enqueue_script('smarty-cpb-scripts', plugin_dir_url(__FILE__) . 'js/smarty-cpb-admin.js', array('jquery'), '1.0.0', true);

            // Pass thresholds to JavaScript
            wp_localize_script('smarty-cpb-script', 'smartyCheckoutProgressBar', array(
                'free_delivery_threshold' => 50,  // Adjust the threshold for free delivery
                'free_gift_threshold'     => 100, // Adjust the threshold for free gift
            ));
        }
    }
    add_action('wp_enqueue_scripts', 'smarty_cpb_enqueue_scripts');
}
