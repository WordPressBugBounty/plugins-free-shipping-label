<?php

namespace Devnet\FSL\Includes;

class Defaults
{

    /**
     * @since    2.0.1
     */
    public static function general($option_name = '')
    {
        $options = [
            'initial_zone'            => '1',
            'enable_custom_threshold' => 0,
            'custom_threshold'        => '',
            'only_logged_users'       => 0,
            'hide_shipping_rates'     => 0,
            'multilingual'            => 0,
            'delete_options'          => 0
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }

    /**
     * @since    2.0.1
     */
    public static function bar($option_name = '')
    {
        $options = [
            'enable_bar'             => 0,
            'local_pickup'           => 0,
            'zero_shipping'          => 0,
            'show_on_cart'           => 1,
            'cart_position'          => 'woocommerce_proceed_to_checkout',
            'show_on_checkout'       => 1,
            'checkout_position'      => 'woocommerce_review_order_before_submit',
            'show_on_minicart'       => 1,
            'minicart_position'      => 'woocommerce_widget_shopping_cart_before_buttons',
            'show_qualified_message'  => 1,
            'show_full_progress_bar' => 0,
            'title'                  => esc_html__('Free delivery on orders over {free_shipping_amount}', 'free-shipping-label'),
            'description'            => esc_html__('Add at least {remaining} more to get free shipping!', 'free-shipping-label'),
            'qualified_message'       => esc_html__('You have free shipping!', 'free-shipping-label'),
            'bar_inner_color'        => '#95578a',
            'bar_bg_color'           => '#ecd4e5',
            'bar_border_color'       => '#333333',
            'hide_border_shadow'     => 0,
            'bar_height'             => 16,
            'center_text'            => 1,
            'disable_animation'      => 0,
            'disabled_animations'    => 'all',
            'bar_type'               => 'linear',
            'indicator_icon'         => '',
            'indicator_icon_size'    => 24,
            'indicator_icon_shape'   => 'rounded',
            'indicator_icon_bg_color' => '#95578a',
            'circle_size'            => 150,
            'inside_circle'          => 'icon',
            'icon'                   => 'delivery-truck-5',
            'icon_color'             => '#ffffff',
            'circle_bg_color'        => '#222222',
            'text_color'             => '',
            'box_bg_color'           => '',
            'box_max_width'          => '',
            'box_alignment'          => 'center',
            'bar_radius'             => 8,
            'remove_bar_stripes'     => 0,
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }

    /**
     * @since    3.0.0
     */
    public static function gift_bar($option_name = '')
    {
        $options = self::bar();

        unset($options['local_pickup']);
        unset($options['show_on_cart']);
        unset($options['show_on_checkout']);
        unset($options['show_on_minicart']);
        unset($options['title']);
        unset($options['description']);
        unset($options['qualified_message']);

        $options += [
            'enable_bar'                    => 0,
            'display'                       => 'after',
            'threshold'                     => '',
            'gift_product'                  => '',
            'after_threshold'               => '',
            'label'                         => esc_html__('Free Gift', 'free-shipping-label'),
            'title'                         => esc_html__('Free Gift on orders over {threshold}', 'free-shipping-label'),
            'description'                   => esc_html__('Add at least {remaining} more to get a free gift!', 'free-shipping-label'),
            'qualified_message'              => esc_html__("You've earned a free gift!", 'free-shipping-label'),
            'layout'                        => 'list',
            'inherit_progress_bar_settings' => 1
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }

    /**
     * @since    2.0.1
     */
    public static function notice_bar($option_name = '')
    {
        $options = self::bar();

        unset($options['local_pickup']);
        unset($options['show_on_cart']);
        unset($options['show_on_checkout']);
        unset($options['show_on_minicart']);

        $options += [
            'enable_bar'                    => 0,
            'position'                      => 'bottom-left',
            'margin_y'                      => '32px',
            'margin_x'                      => '32px',
            'autohide'                      => 1,
            'inherit_progress_bar_settings' => 1
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }

    /**
     * @since    2.0.1
     */
    public static function label($option_name = '')
    {
        $options = [
            'enable_label'                    => 0,
            'exclude'                         => [],
            'include_shipping_class'          => '',
            'show_on_single_simple_product'   => 1,
            'show_on_single_variable_product' => 1,
            'show_on_single_variation'        => 1,
            'show_on_list_simple_products'    => 1,
            'show_on_list_variable_products'  => 1,
            'text'                            => esc_html__('Free shipping!', 'free-shipping-label'),
            'text_color'                      => '#000000',
            'bg_color'                        => '#ffffff',
            'hide_border_shadow'              => 0,
            'label_over_image'                => 0,
            'position'                        => 'top-left',
            'margin_y'                        => '32px',
            'margin_x'                        => '32px',
            'enable_image_label'              => 0,
            'image'                           => '',
            'image_width'                     => 50
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }
}
