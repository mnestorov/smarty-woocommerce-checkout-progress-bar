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

if (!function_exists('smarty_cpb_register_settings_page')) {
    /**
     * Registers a submenu page for plugin settings under WooCommerce.
     *
     * @return void
     */
    function smarty_cpb_register_settings_page() {
        add_submenu_page(
            'woocommerce',
            __('Checkout Progress Bar', 'smarty-woocommerce-checkout-progress-bar'),
            __('Checkout Progress Bar', 'smarty-woocommerce-checkout-progress-bar'),
            'manage_options',
            'smarty-cpb-settings',
            'smarty_cpb_settings_page_content',
        );
    }
    add_action('admin_menu', 'smarty_cpb_register_settings_page');
}

/**
 * Register plugin settings.
 */
function smarty_cpb_register_settings() {
    // General Section
    add_settings_section(
        'smarty_cpb_texts_section',
        __('Texts', 'smarty-woocommerce-checkout-progress-bar'),
        '__return_null',
        'smarty_cpb_settings'
    );

    add_settings_field(
        'smarty_cpb_info_text',
        __('Info Text', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_text_input_callback',
        'smarty_cpb_settings',
        'smarty_cpb_texts_section',
        array('id' => 'smarty_cpb_info_text', 'default' => 'Achieve rewards as you shop!')
    );
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_info_text');

    add_settings_field(
        'smarty_cpb_free_delivery_text',
        __('Free Delivery Text', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_text_input_callback',
        'smarty_cpb_settings',
        'smarty_cpb_texts_section',
        array('id' => 'smarty_cpb_free_delivery_text', 'default' => 'Free Delivery')
    );
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_free_delivery_text');

    add_settings_field(
        'smarty_cpb_free_gift_text',
        __('Free Gift Text', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_text_input_callback',
        'smarty_cpb_settings',
        'smarty_cpb_texts_section',
        array('id' => 'smarty_cpb_free_gift_text', 'default' => 'Free Gift')
    );
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_free_gift_text');

    // Colors Section
    add_settings_section(
        'smarty_cpb_colors_section',
        __('Colors', 'smarty-woocommerce-checkout-progress-bar'),
        '__return_null',
        'smarty_cpb_settings'
    );

    add_settings_field(
        'smarty_cpb_progress_fill_color',
        __('Progress Fill Color', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_color_input_callback',
        'smarty_cpb_settings',
        'smarty_cpb_colors_section',
        array('id' => 'smarty_cpb_progress_fill_color', 'default' => '#4caf50')
    );
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_progress_fill_color');

    add_settings_field(
        'smarty_cpb_tick_color',
        __('Tick Color', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_color_input_callback',
        'smarty_cpb_settings',
        'smarty_cpb_colors_section',
        array('id' => 'smarty_cpb_tick_color', 'default' => '#4caf50')
    );
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_tick_color');

    // Threshold Section
    add_settings_section(
        'smarty_cpb_thresholds_section',
        __('Thresholds', 'smarty-woocommerce-checkout-progress-bar'),
        '__return_null',
        'smarty_cpb_settings'
    );

    add_settings_field(
        'smarty_cpb_free_delivery_threshold',
        __('Free Delivery Threshold', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_number_input_callback',
        'smarty_cpb_settings',
        'smarty_cpb_thresholds_section',
        array('id' => 'smarty_cpb_free_delivery_threshold', 'default' => 100)
    );
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_free_delivery_threshold');

    add_settings_field(
        'smarty_cpb_free_gift_threshold',
        __('Free Gift Threshold', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_number_input_callback',
        'smarty_cpb_settings',
        'smarty_cpb_thresholds_section',
        array('id' => 'smarty_cpb_free_gift_threshold', 'default' => 200)
    );
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_free_gift_threshold');
}
add_action('admin_init', 'smarty_cpb_register_settings');

/**
 * Render the settings page.
 */
function smarty_cpb_settings_page_content() {
    ?>
    <div class="wrap">
        <h1><?php _e('Checkout Progress Bar | Settings', 'smarty-woocommerce-checkout-progress-bar'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('smarty_cpb_settings_group');
            do_settings_sections('smarty_cpb_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Text input callback.
 */
function smarty_cpb_text_input_callback($args) {
    $option = get_option($args['id'], $args['default']);
    printf('<input type="text" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
}

/**
 * Color input callback.
 */
function smarty_cpb_color_input_callback($args) {
    $option = get_option($args['id'], $args['default']);
    printf('<input type="color" id="%s" name="%s" value="%s" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
}

/**
 * Number input callback.
 */
function smarty_cpb_number_input_callback($args) {
    $option = get_option($args['id'], $args['default']);
    printf('<input type="number" id="%s" name="%s" value="%s" step="1" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
}

if (!function_exists('smarty_cpb_progress_bar_shortcode')) {
    /**
     * Shortcode to display the progress bar.
     *
     * @return string HTML for the progress bar.
     */
    function smarty_cpb_progress_bar_shortcode() {
        // Get settings
        $info_text = get_option('smarty_cpb_info_text', 'Achieve rewards as you shop!');
        $free_delivery_text = get_option('smarty_cpb_free_delivery_text', 'Free Delivery');
        $free_gift_text = get_option('smarty_cpb_free_gift_text', 'Free Gift');
        $progress_fill_color = get_option('smarty_cpb_progress_fill_color', '#4caf50');
        $tick_color = get_option('smarty_cpb_tick_color', '#4caf50');

        // Get thresholds
        $free_delivery_threshold = floatval(get_option('smarty_cpb_free_delivery_threshold', 100));
        $free_gift_threshold = floatval(get_option('smarty_cpb_free_gift_threshold', 0));
        if ($free_gift_threshold <= 0) {
            $free_gift_threshold = $free_delivery_threshold * 2;
        }

        // Get the current cart total
        $cart_total = WC()->cart->get_cart_contents_total();

        // Safeguard against division errors and logical inaccuracies
        if ($free_delivery_threshold <= 0) $free_delivery_threshold = 100;
        if ($free_gift_threshold <= $free_delivery_threshold) $free_gift_threshold = $free_delivery_threshold * 2;

        // Calculate progress percentage
        $progress = ($cart_total / $free_gift_threshold) * 100;
        $progress = min(max($progress, 0), 100);

        // Calculate remaining amount for the first gift
        $remaining_to_first_gift = max($free_delivery_threshold - $cart_total, 0);

        // Build the progress bar HTML
        ob_start();
        ?>
        <div id="smarty-cpb" data-free-delivery="<?php echo esc_attr($free_delivery_threshold); ?>" data-free-gift="<?php echo esc_attr($free_gift_threshold); ?>">
            <p class="smarty-cpb-info-text">
                <?php echo esc_html($info_text); ?>
            </p>
            <div class="smarty-cpb-wrapper">
                <div class="smarty-cpb-progress-bar" style="background-color: #e0e0e0;">
                    <div class="smarty-cpb-progress-bar-fill" style="width: <?php echo esc_attr($progress); ?>%; background-color: <?php echo esc_attr($progress_fill_color); ?>;"></div>
                </div>
                <div class="smarty-cpb-progress-bar-ticks">
                    <!-- Amount up to Free Delivery -->
                    <div class="tick">
                        <i class="dashicons dashicons-marker"></i>
                        <span>
                            <strong class="remaining-amount"><?php echo sprintf('$%0.2f', $remaining_to_first_gift); ?></strong><br>
                            <?php echo esc_html__('to the first Gift', 'smarty-woocommerce-checkout-progress-bar'); ?>
                        </span>
                    </div>

                    <!-- Free Delivery Tick -->
                    <div class="tick <?php echo $cart_total >= $free_delivery_threshold ? 'achieved' : ''; ?>" style="color: <?php echo esc_attr($tick_color); ?>;">
                        <i class="dashicons dashicons-yes-alt"></i>
                        <span><?php echo esc_html($free_delivery_text); ?></span>
                    </div>

                    <!-- Free Gift Tick -->
                    <div class="tick <?php echo $cart_total >= $free_gift_threshold ? 'achieved' : ''; ?>" style="color: <?php echo esc_attr($tick_color); ?>;">
                        <i class="dashicons dashicons-yes-alt"></i>
                        <span><?php echo esc_html($free_gift_text); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('smarty_cpb_progress_bar', 'smarty_cpb_progress_bar_shortcode');
}

if (!function_exists('smarty_cpb_enqueue_scripts')) {
    /**
     * Enqueue plugin styles and scripts.
     */
    if (!function_exists('smarty_cpb_enqueue_scripts')) {
        function smarty_cpb_enqueue_scripts() {
            global $post;
    
            // Check if the shortcode is used in the current content or on the checkout page
            if (is_checkout() || (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'smarty_cpb_progress_bar'))) {
                wp_enqueue_style('smarty-cpb-styles', plugin_dir_url(__FILE__) . 'css/smarty-cpb-admin.css');
                wp_enqueue_script('smarty-cpb-scripts', plugin_dir_url(__FILE__) . 'js/smarty-cpb-admin.js', array('jquery'), '1.0.1', true);
    
                // Pass thresholds to JavaScript
                wp_localize_script('smarty-cpb-scripts', 'smartyCheckoutProgressBar', array(
                    'free_delivery_threshold' => floatval(get_option('smarty_cpb_free_delivery_threshold', 100)),
                    'free_gift_threshold' => floatval(get_option('smarty_cpb_free_gift_threshold', 0)),
                ));
            }
        }
        add_action('wp_enqueue_scripts', 'smarty_cpb_enqueue_scripts');
    }
}