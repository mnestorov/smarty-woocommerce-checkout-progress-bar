<?php
/**
 * Plugin Name: SM - WooCommerce Checkout Progress Bar
 * Plugin URI:  https://github.com/mnestorov/smarty-woocommerce-checkout-progress-bar
 * Description: Adds a progress bar to the WooCommerce checkout page indicating free delivery and free gift eligibility.
 * Version:     1.0.0
 * Author:      Smarty Studio | Martin Nestorov
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

if (!function_exists('smarty_cpb_enqueue_scripts')) {
    /**
     * Enqueue plugin styles and scripts.
     */
    function smarty_cpb_enqueue_scripts() {
        global $post;
    
        // Check if the shortcode is used in the current content or on the checkout page
        if (is_checkout() || (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'smarty_cpb_progress_bar'))) {
            wp_enqueue_script('smarty-cpb-scripts', plugin_dir_url(__FILE__) . 'js/smarty-cpb-admin.js', array('jquery'), '1.0.1', true);
    
            // Pass thresholds and dynamic texts to JavaScript
            wp_localize_script('smarty-cpb-scripts', 'smartyCpbSettings', array(
                'giftOneThreshold' => floatval(get_option('smarty_cpb_gift_one_threshold', 100)),
                'giftTwoThreshold' => floatval(get_option('smarty_cpb_gift_two_threshold', 200)),
                'giftOneText' => get_option('smarty_cpb_gift_one_text', 'Free Shipping'),
                'giftTwoText' => get_option('smarty_cpb_gift_two_text', 'Free Gift'),
                'allRewardsText' => get_option('smarty_cpb_all_rewards_text', 'Congratulations! You have unlocked all rewards!')
            ));
        }
    }
    add_action('wp_enqueue_scripts', 'smarty_cpb_enqueue_scripts');
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
        __('Cart Info', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_text_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_texts_section',
        array('id' => 'smarty_cpb_info_text', 'default' => 'Achieve rewards as you shop!')
    );

    add_settings_field(
        'smarty_cpb_all_rewards_text',
        __('All Rewards Unlocked Text', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_text_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_texts_section',
        array('id' => 'smarty_cpb_all_rewards_text', 'default' => 'Congratulations! You have unlocked all rewards!')
    );

    add_settings_section(
        'smarty_cpb_font_size_section',
        __('Font Size', 'smarty-woocommerce-checkout-progress-bar'),
        '__return_null',
        'smarty_cpb_settings'
    );

    add_settings_field(
        'smarty_cpb_info_text_font_size',
        __('Info Text (px)', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_number_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_font_size_section',
        array('id' => 'smarty_cpb_info_text_font_size', 'default' => '16')
    );

    add_settings_field(
        'smarty_cpb_icon_size',
        __('Icon Size (px)', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_number_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_font_size_section',
        array('id' => 'smarty_cpb_icon_size', 'default' => '24')
    );

    add_settings_field(
        'smarty_cpb_icon_text_font_size',
        __('Icon Text (px)', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_number_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_font_size_section',
        array('id' => 'smarty_cpb_icon_text_font_size', 'default' => '14')
    );
    
    add_settings_field(
        'smarty_cpb_gift_one_text',
        __('Gift One', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_text_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_texts_section',
        array('id' => 'smarty_cpb_gift_one_text', 'default' => 'Gift One')
    );
    
    add_settings_field(
        'smarty_cpb_gift_two_text',
        __('Gift Two', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_text_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_texts_section',
        array('id' => 'smarty_cpb_gift_two_text', 'default' => 'Gift Two')
    );

    // Colors Section
    add_settings_section(
        'smarty_cpb_colors_section',
        __('Colors', 'smarty-woocommerce-checkout-progress-bar'),
        '__return_null',
        'smarty_cpb_settings'
    );

    add_settings_field(
        'smarty_cpb_info_text_color',
        __('Info Text', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_color_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_colors_section',
        array('id' => 'smarty_cpb_info_text_color', 'default' => '#333333')
    );

    add_settings_field(
        'smarty_cpb_progress_fill_color',
        __('Progress Bar', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_color_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_colors_section',
        array('id' => 'smarty_cpb_progress_fill_color', 'default' => '#4caf50')
    );

	add_settings_field(
		'smarty_cpb_icon_achieved_color',
		__('Icon Color (Achieved)', 'smarty-woocommerce-checkout-progress-bar'),
		'smarty_cpb_color_input_cb',
		'smarty_cpb_settings',
		'smarty_cpb_colors_section',
		array('id' => 'smarty_cpb_icon_achieved_color', 'default' => '#4caf50')
	);
    
	add_settings_field(
		'smarty_cpb_icon_not_achieved_color',
		__('Icon Color (Not Achieved)', 'smarty-woocommerce-checkout-progress-bar'),
		'smarty_cpb_color_input_cb',
		'smarty_cpb_settings',
		'smarty_cpb_colors_section',
		array('id' => 'smarty_cpb_icon_not_achieved_color', 'default' => '#cccccc')
	);
    
    add_settings_field(
        'smarty_cpb_icon_text_color',
        __('Icon Text', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_color_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_colors_section',
        array('id' => 'smarty_cpb_icon_text_color', 'default' => '#4caf50')
    );

    // Threshold Section
    add_settings_section(
        'smarty_cpb_thresholds_section',
        __('Thresholds', 'smarty-woocommerce-checkout-progress-bar'),
        '__return_null',
        'smarty_cpb_settings'
    );

    add_settings_field(
        'smarty_cpb_gift_one_threshold',
        __('Gift One', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_number_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_thresholds_section',
        array('id' => 'smarty_cpb_gift_one_threshold', 'default' => 100)
    );
    
    add_settings_field(
        'smarty_cpb_gift_two_threshold',
        __('Gift Two', 'smarty-woocommerce-checkout-progress-bar'),
        'smarty_cpb_number_input_cb',
        'smarty_cpb_settings',
        'smarty_cpb_thresholds_section',
        array('id' => 'smarty_cpb_gift_two_threshold', 'default' => 200)
    );

    register_setting('smarty_cpb_settings_group', 'smarty_cpb_info_text');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_all_rewards_text');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_gift_one_text');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_gift_two_text');

    register_setting('smarty_cpb_settings_group', 'smarty_cpb_info_text_font_size');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_icon_size');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_icon_text_font_size');
    
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_info_text_color');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_progress_fill_color');
	register_setting('smarty_cpb_settings_group', 'smarty_cpb_icon_achieved_color');
	register_setting('smarty_cpb_settings_group', 'smarty_cpb_icon_not_achieved_color');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_icon_text_color');
    
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_gift_one_threshold');
    register_setting('smarty_cpb_settings_group', 'smarty_cpb_gift_two_threshold');
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
function smarty_cpb_text_input_cb($args) {
    $option = get_option($args['id'], $args['default']);
    printf('<input type="text" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
}

/**
 * Color input callback.
 */
function smarty_cpb_color_input_cb($args) {
    $option = get_option($args['id'], $args['default']);
    printf('<input type="color" id="%s" name="%s" value="%s" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
}

/**
 * Number input callback.
 */
function smarty_cpb_number_input_cb($args) {
    $option = get_option($args['id'], $args['default']);
    printf('<input type="number" id="%s" name="%s" value="%s" step="1" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
}

/**
 * Enqueue inline styles with dynamic CSS from plugin settings.
 */
function smarty_cpb_public_css() {
    // Get plugin settings for dynamic CSS
    $info_text = get_option('smarty_cpb_info_text', 'Add $XX.XX to unlock XX!');
    $all_rewards_text = get_option('smarty_cpb_all_rewards_text', 'Congratulations! You have unlocked all rewards!');
    
    $info_text_font_size = get_option('smarty_cpb_info_text_font_size', '12px');
    $info_text_color = get_option('smarty_cpb_info_text_color', '#333333');

    $icon_size = get_option('smarty_cpb_icon_size', '24px');
    $icon_text_font_size = get_option('smarty_cpb_icon_text_font_size', '14px');
    $icon_text_color = get_option('smarty_cpb_icon_text_color', '#888');
    $icon_color_achieved = get_option('smarty_cpb_icon_achieved_color', '#4caf50');
    $icon_color_not_achieved = get_option('smarty_cpb_icon_not_achieved_color', '#cccccc');
    $progress_fill_color = get_option('smarty_cpb_progress_fill_color', '#4caf50');

    // Inline CSS
    $custom_css = "
        #smarty-cpb {
            background: rgba(223, 240, 216, .2);
            padding: 20px 10px 70px;
            font-weight: bold;
            border: 2px dashed var(--brdcolor-gray-300);
            border-radius: var(--wd-brd-radius);
        }

        #smarty-cpb .smarty-cpb-info-text {
            font-size: {$info_text_font_size}px;
            color: {$text_color};
            text-align: center;
            margin-bottom: 10px;
        }

        #smarty-cpb .smarty-cpb-wrapper {
            position: relative;
            width: 100%;
        }

        #smarty-cpb .smarty-cpb-progress-icons {
            display: flex;
            justify-content: space-between;
            position: absolute;
            top: 50px !important;
            width: 99%;
            pointer-events: none;
            z-index: 1;
        }

        #smarty-cpb .smarty-cpb-progress-icons .icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin: 0; /* Reset default margins */
        }

        #smarty-cpb .smarty-cpb-progress-icons .icon i {
            font-size: {$icon_size}px;
            color: {$icon_color_not_achieved};
        }

        #smarty-cpb .smarty-cpb-progress-icons .icon.achieved i {
            color: {$icon_color_achieved};
        }

        #smarty-cpb .smarty-cpb-progress-icons .icon span {
            font-size: {$icon_text_font_size}px;
            color: {$icon_text_color};
            margin-top: 5px;
        }

        #smarty-cpb .smarty-cpb-progress-bar {
            width: 100%;
            height: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
        }

        #smarty-cpb .smarty-cpb-progress-bar-fill {
            height: 100%;
            background-color: {$progress_fill_color};
            transition: width 0.3s ease-in-out;
        }

        @media only screen and (max-width: 600px) {
            #smarty-cpb .smarty-cpb-progress-icons {
                top: 75px !important;
            }
        }
    ";

    // Echo the CSS inside <style> tags
    echo "<style type='text/css'>{$custom_css}</style>";
}
add_action('wp_head', 'smarty_cpb_public_css');

if (!function_exists('smarty_cpb_progress_bar_shortcode')) {
    /**
     * Shortcode to display the progress bar.
     *
     * @return string HTML for the progress bar.
     */
    function smarty_cpb_progress_bar_shortcode() {
        // Get settings
        $gift_one_text = get_option('smarty_cpb_gift_one_text', 'Free Shipping');
        $gift_two_text = get_option('smarty_cpb_gift_two_text', 'Free Gift');
        $progress_fill_color = get_option('smarty_cpb_progress_fill_color', '#4caf50');
        $icon_color_achieved = get_option('smarty_cpb_icon_achieved_color', '#4caf50');
        $icon_color_not_achieved = get_option('smarty_cpb_icon_not_achieved_color', '#cccccc');

        // Get thresholds
        $gift_one_threshold = floatval(get_option('smarty_cpb_gift_one_threshold', 100));
        $gift_two_threshold = floatval(get_option('smarty_cpb_gift_two_threshold', 200));

        // Get the current cart total
        $cart_total = WC()->cart->get_cart_contents_total();

        // Calculate remaining amounts
        $remaining_to_gift_one = max($gift_one_threshold - $cart_total, 0);
        $remaining_to_gift_two = max($gift_two_threshold - $cart_total, 0);

        // Determine the info text
        $info_text = '';
        if ($cart_total < $gift_one_threshold) {
            $info_text = sprintf('Add %s to unlock %s!', wc_price($remaining_to_gift_one), $gift_one_text);
        } elseif ($cart_total < $gift_two_threshold) {
            $info_text = sprintf('Add %s to unlock %s!', wc_price($remaining_to_gift_two), $gift_two_text);
        } else {
            $info_text = 'Congratulations! You have unlocked all rewards!';
        }

        // Calculate progress percentage
        $progress = min(($cart_total / $gift_two_threshold) * 100, 100);

        // Build the HTML for the progress bar
        ob_start();
        ?>
        <div id="smarty-cpb" 
            data-gift-one="<?php echo esc_attr($gift_one_threshold); ?>" 
            data-gift-two="<?php echo esc_attr($gift_two_threshold); ?>" 
            style="--progress-fill-color: <?php echo esc_attr($progress_fill_color); ?>;">
            <div class="smarty-cpb-wrapper">
                <p class="smarty-cpb-info-text"><?php echo esc_html($info_text); ?></p>
                <div class="smarty-cpb-progress-bar">
                    <div class="smarty-cpb-progress-bar-fill" style="width: <?php echo esc_attr($progress); ?>%;"></div>
                </div>
                <div class="smarty-cpb-progress-icons">
                    <div class="icon achieved">
                        <span>&nbsp;</span>
                    </div>
                    <div class="icon <?php echo $cart_total >= $gift_one_threshold ? 'achieved' : ''; ?>">
                        <i class="dashicons dashicons-airplane"></i>
                        <span><?php echo esc_html($gift_one_text); ?></span>
                    </div>
                    <div class="icon <?php echo $cart_total >= $gift_two_threshold ? 'achieved' : ''; ?>">
                        <i class="dashicons dashicons-archive"></i>
                        <span><?php echo esc_html($gift_two_text); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('smarty_cpb_progress_bar', 'smarty_cpb_progress_bar_shortcode');
}