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

if (!function_exists('smarty_cpb_enqueue_public_scripts')) {
    /**
     * Enqueue plugin styles and scripts for the public-facing side.
     *
     * @return void
     */
    function smarty_cpb_enqueue_public_scripts() {
        // Check if the plugin is enabled
        if (get_option('smarty_cpb_enable_progressbar', '1') !== '1') {
            return;
        }

        global $post;

        // Enqueue Bootstrap Icons
        wp_enqueue_style('bootstrap-icons', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css', array(), null);

    
        // Check if the shortcode is used in the current content or on the checkout page
        if (is_checkout() || (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'smarty_cpb_progress_bar'))) {
            wp_enqueue_script('smarty-cpb-public-js', plugin_dir_url(__FILE__) . 'js/smarty-cpb-public.js', array('jquery'), null, true);
    
            // Pass thresholds and dynamic texts to JavaScript
            wp_localize_script('smarty-cpb-public-js', 'smartyCpbSettings', array(
                'giftOneThreshold' => floatval(get_option('smarty_cpb_gift_one_threshold', 100)),
                'giftTwoThreshold' => floatval(get_option('smarty_cpb_gift_two_threshold', 200)),
                'giftOneText' => get_option('smarty_cpb_gift_one_text', 'Free Shipping'),
                'giftTwoText' => get_option('smarty_cpb_gift_two_text', 'Free Gift'),
                'allRewardsText' => get_option('smarty_cpb_all_rewards_text', 'Congratulations! You have unlocked all rewards!')
            ));
        }
    }
    add_action('wp_enqueue_scripts', 'smarty_cpb_enqueue_public_scripts');
}

if (!function_exists('smarty_cpb_register_settings')) {
    /**
     * Register plugin settings.
     *
     * @return void
     */
    function smarty_cpb_register_settings() {
        register_setting('smarty_cpb_settings_group', 'smarty_cpb_enable_progressbar', array(
            'type'              => 'string',
            'sanitize_callback' => function($value) {
                return $value === '1' ? '1' : '0';
            },
            'default' => '1'
        ));
        
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
        register_setting('smarty_cpb_settings_group', 'smarty_cpb_icon_text_achieved_color');
        
        register_setting('smarty_cpb_settings_group', 'smarty_cpb_gift_one_threshold');
        register_setting('smarty_cpb_settings_group', 'smarty_cpb_gift_two_threshold');

        add_settings_section(
            'smarty_cpb_general_section', 
            __('General', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_general_section_cb', 
            'smarty_cpb_settings'
        );

        add_settings_field(
            'smarty_cpb_enable_progressbar', 
            __('Disable/Enable', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_checkbox_field_cb', 
            'smarty_cpb_settings', 
            'smarty_cpb_general_section', 
            array(
                'id' => 'smarty_cpb_enable_progressbar'
            )
        );
        
        add_settings_section(
            'smarty_cpb_texts_section',
            __('Texts', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_texts_section_cb',
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
            __('All Rewards Unlocked', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_text_input_cb',
            'smarty_cpb_settings',
            'smarty_cpb_texts_section',
            array('id' => 'smarty_cpb_all_rewards_text', 'default' => 'Congratulations! You have unlocked all rewards!')
        );

        add_settings_section(
            'smarty_cpb_font_size_section',
            __('Font Size', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_font_size_section_cb',
            'smarty_cpb_settings'
        );

        add_settings_field(
            'smarty_cpb_info_text_font_size',
            __('Info Text', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_slider_input_cb',
            'smarty_cpb_settings',
            'smarty_cpb_font_size_section',
            array('id' => 'smarty_cpb_info_text_font_size', 'default' => '16', 'min' => '10', 'max' => '30', 'step' => '1')
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
            __('Icon Text', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_slider_input_cb',
            'smarty_cpb_settings',
            'smarty_cpb_font_size_section',
            array('id' => 'smarty_cpb_icon_text_font_size', 'default' => '14', 'min' => '10', 'max' => '30', 'step' => '1')
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

        add_settings_section(
            'smarty_cpb_colors_section',
            __('Colors', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_colors_section_cb',
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

        add_settings_field(
            'smarty_cpb_icon_text_achieved_color',
            __('Icon Text (Achieved)', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_color_input_cb',
            'smarty_cpb_settings',
            'smarty_cpb_colors_section',
            array('id' => 'smarty_cpb_icon_text_achieved_color', 'default' => '#4caf50')
        );

        add_settings_section(
            'smarty_cpb_thresholds_section',
            __('Thresholds', 'smarty-woocommerce-checkout-progress-bar'),
            'smarty_cpb_thresholds_section_cb',
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
    }
    add_action('admin_init', 'smarty_cpb_register_settings');
}

if (!function_exists('smarty_cpb_settings_page_content')) {
    /**
     * Render the settings page content.
     *
     * @return void
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
}

if (!function_exists('smarty_cpb_general_section_cb')) {
    function smarty_cpb_general_section_cb() {
        echo '<p>Enable or disable checkout progress bar for the site.</p>';
    }
}

if (!function_exists('smarty_cpb_checkbox_field_cb')) {
    function smarty_cpb_checkbox_field_cb($args) {
        $option = get_option($args['id'], '1'); // Retrieve saved value or default to '1'
        $checked = checked(1, $option, false); // Check if the option value is '1'
        echo "<label class='smarty-toggle-switch'>";
        echo "<input type='checkbox' id='{$args['id']}' name='{$args['id']}' value='1' {$checked} />";
        echo "<span class='smarty-slider round'></span>";
        echo "</label>";
    }
}

if (!function_exists('smarty_cpb_texts_section_cb')) {
    function smarty_cpb_texts_section_cb() {
        echo '<p>' . __('Set the text displayed in the progress bar, such as the unlock messages and rewards.', 'smarty-woocommerce-checkout-progress-bar') . '</p>';
    }
}

if (!function_exists('smarty_cpb_text_input_cb')) {
    function smarty_cpb_text_input_cb($args) {
        $option = get_option($args['id'], $args['default']);
        printf('<input type="text" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
    }
}

if (!function_exists('smarty_cpb_colors_section_cb')) {
    function smarty_cpb_colors_section_cb() {
        echo '<p>' . __('Set the colors for different elements in the progress bar, such as icons, text, and the progress bar itself.', 'smarty-woocommerce-checkout-progress-bar') . '</p>';
    }
}

if (!function_exists('smarty_cpb_color_input_cb')) {
    function smarty_cpb_color_input_cb($args) {
        $option = get_option($args['id'], $args['default']);
        printf('<input type="color" id="%s" name="%s" value="%s" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
    }
}

if (!function_exists('smarty_cpb_font_size_section_cb')) {
    function smarty_cpb_font_size_section_cb() {
        echo '<p>' . __('Customize the font size for the progress bar elements, including info text and icon text.', 'smarty-woocommerce-checkout-progress-bar') . '</p>';
    }
}

if (!function_exists('smarty_cpb_slider_input_cb')) {
    function smarty_cpb_slider_input_cb($args) {
        $option = get_option($args['id'], $args['default']);
        $min = isset($args['min']) ? $args['min'] : '0';
        $max = isset($args['max']) ? $args['max'] : '100';
        $step = isset($args['step']) ? $args['step'] : '1';

        printf(
            '<input type="range" id="%s" name="%s" value="%s" min="%s" max="%s" step="%s" oninput="document.getElementById(\'%s-output\').innerText = this.value;" />',
            esc_attr($args['id']),
            esc_attr($args['id']),
            esc_attr($option),
            esc_attr($min),
            esc_attr($max),
            esc_attr($step),
            esc_attr($args['id'])
        );

        printf(
            '<span id="%s-output" style="margin-left: 5px;">%spx</span>',
            esc_attr($args['id']),
            esc_html($option)
        );
    }
}

if (!function_exists('smarty_cpb_thresholds_section_cb')) {
    function smarty_cpb_thresholds_section_cb() {
        echo '<p>' . __('Define the thresholds for unlocking rewards. These thresholds determine when users see progress updates.', 'smarty-woocommerce-checkout-progress-bar') . '</p>';
    }
}

if (!function_exists('smarty_cpb_number_input_cb')) {
    function smarty_cpb_number_input_cb($args) {
        $option = get_option($args['id'], $args['default']);
        printf('<input type="number" id="%s" name="%s" value="%s" step="1" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr($option));
    }
}

if (!function_exists('smarty_cpb_admin_css')) {
    function smarty_cpb_admin_css() { 
        if (is_admin()) { ?>
            <style>
                 input[type="range"] {
                    width: 300px;
                }

                /* The switch - the box around the slider */
                .smarty-toggle-switch {
                    position: relative;
                    display: inline-block;
                    width: 60px;
                    height: 34px;
                }

                /* Hide default HTML checkbox */
                .smarty-toggle-switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                /* The slider */
                .smarty-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                    border-radius: 34px;
                }

                .smarty-slider:before {
                    position: absolute;
                    content: "";
                    height: 26px;
                    width: 26px;
                    left: 4px;
                    bottom: 4px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }

                input:checked + .smarty-slider {
                    background-color: #2196F3;
                }

                input:checked + .smarty-slider:before {
                    transform: translateX(26px);
                }

                /* Rounded sliders */
                .smarty-slider.round {
                    border-radius: 34px;
                }

                .smarty-slider.round:before {
                    border-radius: 50%;
                }
            </style><?php
        } 
    }
    add_action('admin_head', 'smarty_cpb_admin_css');
}

if (!function_exists('smarty_cpb_public_css')) {
    /**
     * Enqueue dynamic CSS from plugin settings.
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
        $icon_text_achieved_color = get_option('smarty_cpb_icon_text_achieved_color', '#4caf50');
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

            .smarty-cpb-info-text span {
                color: {$icon_text_achieved_color} !important;
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

            #smarty-cpb .smarty-cpb-progress-icons .icon span.gift-text {
                font-size: {$icon_text_font_size}px;
                color: {$icon_text_color};
                margin-top: -5px;
            }

            #smarty-cpb .smarty-cpb-progress-icons .icon.achieved span.gift-text {
                color: {$icon_text_achieved_color};
            }

            #smarty-cpb .smarty-cpb-progress-bar {
                width: 100%;
                height: 10px;
                background-color: rgba(var(--bgcolor-black-rgb), 0.06);
                border-radius: 5px;
                position: relative;
                overflow: hidden;
            }

            #smarty-cpb .smarty-cpb-progress-bar-fill {
                height: 100%;
                background-color: {$progress_fill_color};
                background-image: linear-gradient(135deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%, transparent);
                background-size: 15px 15px;
                transition: width 0.3s ease-in-out;
            }

            @media only screen and (max-width: 600px) {
                #smarty-cpb .smarty-cpb-progress-icons {
                    top: 45px !important;
                }
            }
        ";

        // Echo the CSS inside <style> tags
        echo "<style type='text/css'>{$custom_css}</style>";
    }
    add_action('wp_head', 'smarty_cpb_public_css');
}

if (!function_exists('smarty_cpb_progress_bar_shortcode')) {
    /**
     * Shortcode to display the progress bar.
     *
     * @return string HTML for the progress bar.
     */
    function smarty_cpb_progress_bar_shortcode() {
        // Check if the plugin is enabled
        if (get_option('smarty_cpb_enable_progressbar', '1') !== '1') {
            return ''; // Or return a message like: return '<p>Progress bar is disabled.</p>';
        }
        
        // Get settings
        $gift_one_text = get_option('smarty_cpb_gift_one_text', 'Gift One');
        $gift_two_text = get_option('smarty_cpb_gift_two_text', 'Gift Two');
        $all_rewards_text = get_option('smarty_cpb_all_rewards_text', 'Congratulations! You have unlocked all rewards!');
        $progress_fill_color = get_option('smarty_cpb_progress_fill_color', '#4caf50');

        // Get thresholds
        $gift_one_threshold = floatval(get_option('smarty_cpb_gift_one_threshold', 100));
        $gift_two_threshold = floatval(get_option('smarty_cpb_gift_two_threshold', 200));

        // Get the current cart total
        $cart_total = WC()->cart->get_cart_contents_total();

        // Calculate remaining amounts
        $remaining_to_gift_one = max($gift_one_threshold - $cart_total, 0);
        $remaining_to_gift_two = max($gift_two_threshold - $cart_total, 0);

        // Determine the info text
        if ($cart_total < $gift_one_threshold) {
            $info_text = sprintf('Add <span>%s</span> to unlock %s!', wc_price($remaining_to_gift_one), $gift_one_text);
        } elseif ($cart_total < $gift_two_threshold) {
            $info_text = sprintf('Add <span>%s</span> to unlock %s!', wc_price($remaining_to_gift_two), $gift_two_text);
        } else {
            // Use the dynamic text from the plugin settings
            $info_text = esc_html($all_rewards_text);
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
                <p class="smarty-cpb-info-text"><?php echo wp_kses_post($info_text); ?></p>
                <div class="smarty-cpb-progress-bar">
                    <div class="smarty-cpb-progress-bar-fill" style="width: <?php echo esc_attr($progress); ?>%;"></div>
                </div>
                <div class="smarty-cpb-progress-icons">
                    <div class="icon achieved">
                        <span>&nbsp;</span>
                    </div>
                    <div class="icon gift-one <?php echo $cart_total >= $gift_one_threshold ? 'achieved' : ''; ?>">
                        <i class="bi bi-truck"></i>
                        <span class="gift-text"><?php echo esc_html($gift_one_text); ?></span>
                    </div>
                    <div class="icon gift-two <?php echo $cart_total >= $gift_two_threshold ? 'achieved' : ''; ?>">
                        <i class="bi bi-gift"></i>
                        <span class="gift-text"><?php echo esc_html($gift_two_text); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('smarty_cpb_progress_bar', 'smarty_cpb_progress_bar_shortcode');
}