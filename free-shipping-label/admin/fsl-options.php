<?php

namespace Devnet\FSL\Admin;

use Devnet\FSL\Includes\Defaults;
use Devnet\FSL\Includes\Icons;
/**
 * Options for settings panel
 *
 */
class Options {
    public static function common_options( $name, $premium = false, $type = '' ) {
        $is_disabled = ( $premium ? '__disabled' : '' );
        $desc_bar_placeholders = sprintf( '%s
            <input type="text" readonly="readonly" value="{free_shipping_amount}" />
            <input type="text" readonly="readonly" value="{remaining}" />', esc_html__( 'Placeholders: ', 'free-shipping-label' ) );
        $default_qualified_message = Defaults::bar( 'qualified_message' );
        $default_title = Defaults::bar( 'title' );
        $default_description = Defaults::bar( 'description' );
        if ( $type === 'gift_bar' ) {
            $desc_bar_placeholders = sprintf( '%s
                <input type="text" readonly="readonly" value="{threshold}" />
                <input type="text" readonly="readonly" value="{remaining}" />
                ', esc_html__( 'Placeholders: ', 'free-shipping-label' ) );
            $default_qualified_message = Defaults::gift_bar( 'qualified_message' );
            $default_title = Defaults::gift_bar( 'title' );
            $default_description = Defaults::gift_bar( 'description' );
        }
        $options = [
            'info-bar-after-threshold' => [
                'type'  => 'info',
                'name'  => 'info-bar-after-threshold' . $is_disabled,
                'label' => esc_html__( 'After free shipping threshold is reached', 'free-shipping-label' ),
                'class' => 'info',
            ],
            'show_qualified_message'   => [
                'type'    => 'checkbox',
                'name'    => 'show_qualified_message' . $is_disabled,
                'label'   => esc_html__( 'Show message', 'free-shipping-label' ),
                'default' => Defaults::bar( 'show_qualified_message' ),
            ],
            'show_full_progress_bar'   => [
                'type'    => 'checkbox',
                'name'    => 'show_full_progress_bar' . $is_disabled,
                'label'   => esc_html__( 'Show full progress bar', 'free-shipping-label' ),
                'default' => Defaults::bar( 'show_full_progress_bar' ),
            ],
            'info-bar-text'            => [
                'type'  => 'info',
                'name'  => 'info-bar-text' . $is_disabled,
                'label' => esc_html__( 'Text', 'free-shipping-label' ),
                'class' => 'info',
            ],
            'info-bar-placeholders'    => [
                'type'  => 'info',
                'name'  => 'info-bar-placeholders' . $is_disabled,
                'label' => '',
                'desc'  => $desc_bar_placeholders,
                'class' => 'info subinfo',
            ],
            'title'                    => [
                'type'              => 'text',
                'name'              => 'title' . $is_disabled,
                'label'             => esc_html__( 'Title', 'free-shipping-label' ),
                'default'           => $default_title,
                'sanitize_callback' => 'wp_filter_post_kses',
            ],
            'description'              => [
                'type'              => 'text',
                'name'              => 'description' . $is_disabled,
                'label'             => esc_html__( 'Description', 'free-shipping-label' ),
                'default'           => $default_description,
                'sanitize_callback' => 'wp_filter_post_kses',
            ],
            'qualified_message'        => [
                'type'              => 'text',
                'name'              => 'qualified_message' . $is_disabled,
                'label'             => esc_html__( 'Message after threshold is reached', 'free-shipping-label' ),
                'sanitize_callback' => 'sanitize_text_field',
                'max'               => 100,
                'default'           => $default_qualified_message,
            ],
            'info-bar-design'          => [
                'type'  => 'info',
                'name'  => 'info-bar-design' . $is_disabled,
                'label' => esc_html__( 'Design', 'free-shipping-label' ),
                'class' => 'info',
            ],
            'bar_inner_color'          => [
                'type'    => 'color',
                'name'    => 'bar_inner_color' . $is_disabled,
                'label'   => esc_html__( 'Progress bar inner color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'bar_inner_color' ),
            ],
            'bar_bg_color'             => [
                'type'    => 'color',
                'name'    => 'bar_bg_color' . $is_disabled,
                'label'   => esc_html__( 'Progress bar background color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'bar_bg_color' ),
            ],
            'bar_border_color'         => [
                'type'    => 'color',
                'name'    => 'bar_border_color' . $is_disabled,
                'label'   => esc_html__( 'Progress bar border color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'bar_border_color' ),
            ],
            'bar_height'               => [
                'type'              => 'number',
                'name'              => 'bar_height' . $is_disabled,
                'label'             => esc_html__( 'Progress bar height', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Height in pixels (px)', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'height in px', 'free-shipping-label' ),
                'unit'              => 'px',
                'min'               => 0,
                'step'              => '1',
                'default'           => Defaults::bar( 'bar_height' ),
                'sanitize_callback' => 'absint',
            ],
            'disable_animation'        => [
                'type'    => 'checkbox',
                'name'    => 'disable_animation' . $is_disabled,
                'label'   => esc_html__( 'Disable animation', 'free-shipping-label' ),
                'default' => Defaults::bar( 'disable_animation' ),
            ],
            'disabled_animations'      => [
                'type'    => 'select',
                'name'    => 'disabled_animations' . $is_disabled,
                'label'   => esc_html__( 'Disabled animations', 'free-shipping-label' ),
                'options' => [
                    'all'    => esc_html__( 'All', 'free-shipping-label' ),
                    'strips' => esc_html__( 'Moving strips ', 'free-shipping-label' ),
                    'shine'  => esc_html__( 'Shine ', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'disabled_animations' ),
            ],
            'hide_border_shadow'       => [
                'type'    => 'checkbox',
                'name'    => 'hide_border_shadow' . $is_disabled,
                'label'   => esc_html__( 'Hide border shadow', 'free-shipping-label' ),
                'default' => Defaults::bar( 'hide_border_shadow' ),
            ],
            'bar_type'                 => [
                'type'    => 'select',
                'name'    => 'bar_type' . $is_disabled,
                'label'   => esc_html__( 'Bar type', 'free-shipping-label' ),
                'options' => [
                    'linear'      => esc_html__( 'Linear', 'free-shipping-label' ),
                    '_disabled_1' => esc_html__( 'Circular ', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'bar_type' ),
            ],
            'indicator_icon'           => [
                'type'    => 'checkbox',
                'name'    => 'indicator_icon' . $is_disabled,
                'label'   => esc_html__( 'Threshold Indicator Icon', 'free-shipping-label' ),
                'default' => Defaults::bar( 'indicator_icon' ),
            ],
            'indicator_icon_size'      => [
                'type'              => 'number',
                'name'              => 'indicator_icon_size' . $is_disabled,
                'label'             => esc_html__( 'Indicator Icon size', 'free-shipping-label' ),
                'unit'              => 'px',
                'min'               => 1,
                'step'              => '1',
                'default'           => Defaults::bar( 'indicator_icon_size' ),
                'sanitize_callback' => 'absint',
            ],
            'indicator_icon_bg_color'  => [
                'type'    => 'color',
                'name'    => 'indicator_icon_bg_color' . $is_disabled,
                'label'   => esc_html__( 'Indicator Icon background color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'indicator_icon_bg_color' ),
            ],
            'circle_size'              => [
                'type'              => 'number',
                'name'              => 'circle_size' . $is_disabled,
                'label'             => esc_html__( 'Circle size', 'free-shipping-label' ),
                'desc'              => esc_html__( 'width and height in pixels', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'width and height in px', 'free-shipping-label' ),
                'unit'              => 'px',
                'min'               => 100,
                'max'               => 300,
                'step'              => '1',
                'default'           => Defaults::bar( 'circle_size' ),
                'sanitize_callback' => 'absint',
            ],
            'inside_circle'            => [
                'type'    => 'select',
                'name'    => 'inside_circle' . $is_disabled,
                'label'   => esc_html__( 'Inside circle', 'free-shipping-label' ),
                'options' => [
                    ''            => esc_html__( 'Nothing', 'free-shipping-label' ),
                    'title'       => esc_html__( 'Title ', 'free-shipping-label' ),
                    'description' => esc_html__( 'Description ', 'free-shipping-label' ),
                    'icon'        => esc_html__( 'Icon ', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'inside_circle' ),
            ],
            'icon'                     => [
                'type'    => 'radio_image',
                'name'    => 'icon' . $is_disabled,
                'label'   => esc_html__( 'Select icon', 'free-shipping-label' ),
                'options' => [],
                'default' => Defaults::bar( 'radio_image' ),
            ],
            'icon_color'               => [
                'type'    => 'color',
                'name'    => 'icon_color' . $is_disabled,
                'label'   => esc_html__( 'Icon color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'icon_color' ),
            ],
            'circle_bg_color'          => [
                'type'    => 'color',
                'name'    => 'circle_bg_color' . $is_disabled,
                'label'   => esc_html__( 'Circle background color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'circle_bg_color' ),
            ],
            'text_color'               => [
                'type'    => 'color',
                'name'    => 'text_color' . $is_disabled,
                'label'   => esc_html__( 'Text color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'text_color' ),
            ],
            'box_bg_color'             => [
                'type'    => 'color',
                'name'    => 'box_bg_color' . $is_disabled,
                'label'   => esc_html__( 'Box background color', 'free-shipping-label' ),
                'default' => Defaults::bar( 'box_bg_color' ),
            ],
            'box_max_width'            => [
                'type'              => 'number',
                'name'              => 'box_max_width' . $is_disabled,
                'label'             => esc_html__( 'Box maximal width', 'free-shipping-label' ),
                'desc'              => esc_html__( 'max width in pixels, set 0 for full width', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'max width in px', 'free-shipping-label' ),
                'unit'              => 'px',
                'min'               => 0,
                'step'              => '1',
                'default'           => Defaults::bar( 'box_max_width' ),
                'sanitize_callback' => 'absint',
            ],
            'box_alignment'            => [
                'type'    => 'select',
                'name'    => 'box_alignment' . $is_disabled,
                'label'   => esc_html__( 'Box alignment', 'free-shipping-label' ),
                'options' => [
                    'center'      => esc_html__( 'center', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Left', 'free-shipping-label' ),
                    '_disabled_3' => esc_html__( 'right', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'box_alignment' ),
            ],
            'center_text'              => [
                'type'    => 'checkbox',
                'name'    => 'center_text' . $is_disabled,
                'label'   => esc_html__( 'Center text', 'free-shipping-label' ),
                'default' => Defaults::bar( 'center_text' ),
            ],
            'bar_radius'               => [
                'type'              => 'number',
                'name'              => 'bar_radius' . $is_disabled,
                'label'             => esc_html__( 'Bar border radius', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Lower values create sharper corners. Set to 0 for a boxed shape.', 'free-shipping-label' ),
                'unit'              => 'px',
                'min'               => 0,
                'step'              => '1',
                'default'           => Defaults::bar( 'bar_radius' ),
                'sanitize_callback' => 'absint',
            ],
            'remove_bar_stripes'       => [
                'type'    => 'checkbox',
                'name'    => 'remove_bar_stripes' . $is_disabled,
                'label'   => esc_html__( 'Remove bar stripes', 'free-shipping-label' ),
                'default' => Defaults::bar( 'remove_bar_stripes' ),
            ],
        ];
        return $options[$name] ?? null;
    }

    /**
     * @since    1.0.0
     */
    public static function general() {
        $general = [
            [
                'type'    => 'select',
                'name'    => 'initial_zone',
                'label'   => esc_html__( 'Initial shipping zone', 'free-shipping-label' ),
                'desc'    => esc_html__( "This zone's free shipping threshold will be used only if customer didn't already enter address.", 'free-shipping-label' ),
                'options' => self::shipping_zones_option_list(),
                'default' => Defaults::general( 'initial_zone' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'enable_custom_threshold',
                'label'   => esc_html__( 'Enable custom threshold', 'free-shipping-label' ),
                'desc'    => esc_html__( 'This will ignore free shipping threshold in WooCommerce settings.', 'free-shipping-label' ),
                'default' => Defaults::general( 'enable_custom_threshold' ),
            ],
            [
                'type'        => 'number',
                'name'        => 'custom_threshold',
                'label'       => esc_html__( 'Custom threshold - global', 'free-shipping-label' ),
                'desc'        => esc_html__( "Set a Global Custom Threshold for manual control in situations where automatic detection isn't possible, ensuring accurate calculations for free shipping across all zones.", 'free-shipping-label' ),
                'placeholder' => esc_html__( 'Amount', 'free-shipping-label' ),
                'min'         => 0,
                'step'        => 0.01,
                'default'     => Defaults::general( 'custom_threshold' ),
            ],
            [
                'type'    => 'group',
                'name'    => 'custom_threshold_per_method__disabled',
                'label'   => esc_html__( 'Custom threshold per zone and shipping method', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Configure individual free shipping thresholds for each zone and its methods. Note that these settings take priority over the global threshold, providing specific criteria for shipping calculations within designated zones.', 'free-shipping-label' ),
                'fields'  => self::custom_threshold_fields(),
                'default' => Defaults::general( 'custom_thresholds' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'only_logged_users',
                'label'   => esc_html__( 'Visibility', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Show only for logged in users.', 'free-shipping-label' ),
                'default' => Defaults::general( 'only_logged_users' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'hide_shipping_rates',
                'label'   => esc_html__( 'Hide shipping rates when free shipping is available?', 'free-shipping-label' ),
                'desc'    => '',
                'options' => [
                    ''                  => esc_html__( 'No', 'free-shipping-label' ),
                    'hide_all'          => esc_html__( 'Hide all other shipping methods and only show "Free Shipping"', 'free-shipping-label' ),
                    'hide_except_local' => esc_html__( 'Hide all other shipping methods and only show "Free Shipping" and "Local Pickup"', 'free-shipping-label' ),
                ],
                'default' => Defaults::general( 'hide_shipping_rates' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'multilingual',
                'label'   => esc_html__( 'Multilingual', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Use your own translated strings.', 'free-shipping-label' ),
                'default' => Defaults::general( 'multilingual' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'delete_options',
                'label'   => esc_html__( 'Delete plugin data on deactivation', 'free-shipping-label' ),
                'default' => Defaults::general( 'delete_options' ),
            ]
        ];
        return apply_filters( 'fsl_settings_general', $general );
    }

    /**
     * @since    1.0.0
     */
    public static function progress_bar() {
        $minicart_optgroup = [];
        // FunnelKit positions
        if ( defined( 'FKCART_VERSION' ) ) {
            $minicart_optgroup = [[
                'label'    => esc_html__( '-- FunnelKit --', 'free-shipping-label' ),
                'options'  => [
                    '1' => esc_html__( 'After header', 'free-shipping-label' ),
                    '2' => esc_html__( 'Before cart items', 'free-shipping-label' ),
                    '3' => esc_html__( 'After cart items', 'free-shipping-label' ),
                    '4' => esc_html__( 'Before button', 'free-shipping-label' ),
                    '5' => esc_html__( 'After button', 'free-shipping-label' ),
                ],
                'disabled' => true,
            ]];
        }
        $progress_bar = [
            [
                'type'    => 'checkbox',
                'name'    => 'enable_bar',
                'label'   => esc_html__( 'Enable Progress Bar', 'free-shipping-label' ),
                'default' => Defaults::bar( 'enable_bar' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'local_pickup',
                'label'   => esc_html__( 'Enable on Local Pickup', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Show bar if Local Pickup is selected.', 'free-shipping-label' ),
                'default' => Defaults::bar( 'local_pickup' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'zero_shipping',
                'label'   => esc_html__( 'Allow zero shipping cost', 'free-shipping-label' ),
                'desc'    => sprintf(
                    '%s <br> %s <a href="https://devnet.hr/docs/free-shipping-label/progress-bar/#zero-shipping" target="_blank">%s</a> %s',
                    esc_html__( 'Free shipping is indicated by a shipping cost of zero. Enable this setting if a third-party shipping plugin is used and the progress bar appears despite selecting free shipping.', 'free-shipping-label' ),
                    esc_html__( 'Refer to the ', 'free-shipping-label' ),
                    esc_html__( 'documentation', 'free-shipping-label' ),
                    esc_html__( 'for more information.', 'free-shipping-label' )
                ),
                'default' => Defaults::bar( 'zero_shipping' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-bar-positions',
                'label' => esc_html__( 'Positions', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_cart',
                'label'   => esc_html__( 'Display on the cart page', 'free-shipping-label' ),
                'default' => Defaults::bar( 'show_on_cart' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'cart_position',
                'label'   => esc_html__( 'Cart position', 'free-shipping-label' ),
                'options' => [
                    '_disabled_1'                     => esc_html__( 'Before cart total', 'free-shipping-label' ),
                    'woocommerce_proceed_to_checkout' => esc_html__( 'Before button', 'free-shipping-label' ),
                    '_disabled_2'                     => esc_html__( 'After button', 'free-shipping-label' ),
                    '_disabled_3'                     => esc_html__( 'Before cart', 'free-shipping-label' ),
                    '_disabled_4'                     => esc_html__( 'After cart', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'cart_position' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_checkout',
                'label'   => esc_html__( 'Display on the checkout page', 'free-shipping-label' ),
                'default' => Defaults::bar( 'show_on_checkout' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'checkout_position',
                'label'   => esc_html__( 'Checkout position', 'free-shipping-label' ),
                'options' => [
                    '_disabled_1'                            => esc_html__( 'Before checkout form', 'free-shipping-label' ),
                    '_disabled_2'                            => esc_html__( 'After checkout form', 'free-shipping-label' ),
                    '_disabled_3'                            => esc_html__( 'Before order review', 'free-shipping-label' ),
                    'woocommerce_review_order_before_submit' => esc_html__( 'Before submit button', 'free-shipping-label' ),
                    '_disabled_4'                            => esc_html__( 'After submit button', 'free-shipping-label' ),
                    '_disabled_5'                            => esc_html__( 'Before payment', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'checkout_position' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_minicart',
                'label'   => esc_html__( 'Display in the mini cart widget', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Note: This option may not work if the mini-cart/side-cart lacks specific WooCommerce hook required for proper functionality, especially if customized by your theme or a third-party plugin.', 'free-shipping-label' ),
                'default' => Defaults::bar( 'show_on_minicart' ),
            ],
            [
                'type'     => 'select',
                'name'     => 'minicart_position',
                'label'    => esc_html__( 'Minicart position', 'free-shipping-label' ),
                'options'  => [
                    '_disabled_1'                                     => esc_html__( 'Before minicart', 'free-shipping-label' ),
                    '_disabled_2'                                     => esc_html__( 'Before content', 'free-shipping-label' ),
                    '_disabled_3'                                     => esc_html__( 'After content', 'free-shipping-label' ),
                    'woocommerce_widget_shopping_cart_before_buttons' => esc_html__( 'Before buttons', 'free-shipping-label' ),
                    '_disabled_4'                                     => esc_html__( 'After minicart', 'free-shipping-label' ),
                ],
                'optgroup' => $minicart_optgroup,
                'default'  => Defaults::bar( 'cart_position' ),
            ],
            [
                'type'  => 'text',
                'name'  => 'shortcode_info__disabled',
                'label' => esc_html__( 'Shortcode', 'free-shipping-label' ),
                'desc'  => esc_html( 'Copy the shortcode and integrate it into your site using your preferred editor.', 'free-shipping-label' ),
            ],
            self::common_options( 'info-bar-after-threshold' ),
            self::common_options( 'show_qualified_message' ),
            self::common_options( 'show_full_progress_bar', true ),
            self::common_options( 'info-bar-text' ),
            self::common_options( 'info-bar-placeholders' ),
            self::common_options( 'title' ),
            self::common_options( 'description' ),
            self::common_options( 'qualified_message' ),
            self::common_options( 'info-bar-design' ),
            self::common_options( 'bar_type' ),
            self::common_options( 'indicator_icon', true ),
            self::common_options( 'indicator_icon_size', true ),
            self::common_options( 'indicator_icon_bg_color', true ),
            self::common_options( 'circle_size', true ),
            self::common_options( 'inside_circle', true ),
            self::common_options( 'icon', true ),
            self::common_options( 'icon_color', true ),
            self::common_options( 'circle_bg_color', true ),
            self::common_options( 'bar_inner_color' ),
            self::common_options( 'bar_bg_color' ),
            self::common_options( 'bar_border_color' ),
            self::common_options( 'text_color', true ),
            self::common_options( 'box_bg_color', true ),
            self::common_options( 'box_max_width', true ),
            self::common_options( 'box_alignment' ),
            self::common_options( 'bar_height' ),
            self::common_options( 'bar_radius', true ),
            self::common_options( 'center_text', true ),
            self::common_options( 'disable_animation' ),
            self::common_options( 'disabled_animations' ),
            self::common_options( 'remove_bar_stripes', true ),
            self::common_options( 'hide_border_shadow' )
        ];
        return apply_filters( 'fsl_settings_progress_bar', $progress_bar );
    }

    /**
     * @since    2.4.4
     */
    public static function notice_bar() {
        $notice_bar = [
            [
                'type'    => 'checkbox',
                'name'    => 'enable_bar__disabled',
                'label'   => esc_html__( 'Enable Notice Bar', 'free-shipping-label' ),
                'default' => Defaults::notice_bar( 'enable_bar' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'position__disabled',
                'label'   => esc_html__( 'Position', 'free-shipping-label' ),
                'desc'    => '',
                'options' => [
                    'top-left'     => esc_html__( 'Top Left', 'free-shipping-label' ),
                    'top-right'    => esc_html__( 'Top Right', 'free-shipping-label' ),
                    'bottom-left'  => esc_html__( 'Bottom Left', 'free-shipping-label' ),
                    'bottom-right' => esc_html__( 'Bottom Right', 'free-shipping-label' ),
                ],
                'default' => Defaults::notice_bar( 'position' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_y__disabled',
                'label'             => esc_html__( 'Margin: ', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'Vertical margin', 'free-shipping-label' ),
                'default'           => Defaults::notice_bar( 'margin_y' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_x__disabled',
                'label'             => esc_html__( 'Margin: ', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'Horizontal margin', 'free-shipping-label' ),
                'default'           => Defaults::notice_bar( 'margin_x' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'autohide__disabled',
                'label'   => esc_html__( 'Hide automatically', 'free-shipping-label' ),
                'desc'    => esc_html__( 'After adding to cart the Notice Bar will be visible for 5 seconds.', 'free-shipping-label' ),
                'default' => Defaults::notice_bar( 'autohide' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'inherit_progress_bar_settings__disabled',
                'label'   => esc_html__( 'Inherit Progress Bar Settings', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Turn off for additional customization.', 'free-shipping-label' ),
                'default' => Defaults::notice_bar( 'inherit_progress_bar_settings' ),
            ],
            self::common_options( 'info-bar-after-threshold' ),
            self::common_options( 'show_qualified_message', true ),
            self::common_options( 'show_full_progress_bar', true ),
            self::common_options( 'info-bar-text', true ),
            self::common_options( 'info-bar-placeholders', true ),
            self::common_options( 'title', true ),
            self::common_options( 'description', true ),
            self::common_options( 'qualified_message', true ),
            self::common_options( 'info-bar-design', true ),
            self::common_options( 'bar_type', true ),
            self::common_options( 'indicator_icon', true ),
            self::common_options( 'indicator_icon_size', true ),
            self::common_options( 'indicator_icon_bg_color', true ),
            self::common_options( 'circle_size', true ),
            self::common_options( 'inside_circle', true ),
            self::common_options( 'icon', true ),
            self::common_options( 'icon_color', true ),
            self::common_options( 'circle_bg_color', true ),
            self::common_options( 'bar_inner_color', true ),
            self::common_options( 'bar_bg_color', true ),
            self::common_options( 'bar_border_color', true ),
            self::common_options( 'text_color', true ),
            self::common_options( 'box_bg_color', true ),
            self::common_options( 'box_max_width', true ),
            self::common_options( 'box_alignment', true ),
            self::common_options( 'bar_height', true ),
            self::common_options( 'bar_radius', true ),
            self::common_options( 'center_text', true ),
            self::common_options( 'disable_animation', true ),
            self::common_options( 'disabled_animations', true ),
            self::common_options( 'remove_bar_stripes', true ),
            self::common_options( 'hide_border_shadow', true )
        ];
        return apply_filters( 'fsl_settings_notice_bar', $notice_bar );
    }

    /**
     * @since    3.0.0
     */
    public static function gift_bar() {
        $gift_bar = [
            [
                'name'    => 'enable_bar',
                'label'   => esc_html__( 'Enable', 'free-shipping-label' ),
                'type'    => 'checkbox',
                'default' => Defaults::gift_bar( 'enable_bar' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'display',
                'label'   => esc_html__( 'Display', 'free-shipping-label' ),
                'options' => [
                    'after'       => esc_html__( 'After free shipping reached', 'free-shipping-label' ),
                    '_disabled_1' => esc_html__( 'Extend free shipping progress bar', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Only Gift Bar', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Standalone', 'free-shipping-label' ),
                ],
                'default' => Defaults::gift_bar( 'display' ),
            ],
            [
                'type'    => 'number',
                'name'    => 'threshold',
                'label'   => esc_html__( 'Threshold', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Min. cart amount for qualifying for the free gift product', 'free-shipping-label' ),
                'step'    => 0.01,
                'min'     => 0,
                'default' => Defaults::gift_bar( 'threshold' ),
            ],
            [
                'type'     => 'select2',
                'name'     => 'gift_product__disabled',
                'label'    => esc_html__( 'Gift product', 'free-shipping-label' ),
                'desc'     => esc_html__( 'Select the free gift product', 'free-shipping-label' ),
                'options'  => [],
                'multiple' => false,
                'default'  => Defaults::gift_bar( 'gift_product' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'after_threshold',
                'label'   => esc_html__( 'After threshold reached', 'free-shipping-label' ),
                'options' => [
                    ''            => esc_html__( 'Nothing', 'free-shipping-label' ),
                    '_disabled_1' => esc_html__( 'Automatically add to cart with price of 0', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Add as order additional information', 'free-shipping-label' ),
                ],
                'desc'    => esc_html__( 'What should we do with the gift product after the threshold is reached?', 'free-shipping-label' ),
                'default' => Defaults::gift_bar( 'after_threshold' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'label__disabled',
                'label'             => esc_html__( 'Label', 'free-shipping-label' ),
                'default'           => Defaults::gift_bar( 'label' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            self::common_options( 'info-bar-text' ),
            self::common_options( 'info-bar-placeholders', false, 'gift_bar' ),
            self::common_options( 'title', false, 'gift_bar' ),
            self::common_options( 'description', false, 'gift_bar' ),
            self::common_options( 'qualified_message', false, 'gift_bar' ),
            self::common_options( 'info-bar-design', true ),
            [
                'type'    => 'select',
                'name'    => 'layout',
                'label'   => esc_html__( 'Display Mode', 'free-shipping-label' ),
                'options' => [
                    'list'        => esc_html__( 'Vertical list', 'free-shipping-label' ),
                    '_disabled_1' => esc_html__( 'Horizontal Labels', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Horizontal Labels with description', 'free-shipping-label' ),
                    '_disabled_3' => esc_html__( 'Description Only Above', 'free-shipping-label' ),
                    '_disabled_4' => esc_html__( 'Description Only Beneath', 'free-shipping-label' ),
                ],
                'desc'    => esc_html__( 'Choose the layout style for displaying progress indicators and related text. Options include different arrangements for displaying free shipping and gift progress information.', 'free-shipping-label' ),
                'default' => Defaults::gift_bar( 'layout' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'inherit_progress_bar_settings__disabled',
                'label'   => esc_html__( 'Inherit Progress Bar Settings', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Turn off for additional customization.', 'free-shipping-label' ),
                'default' => Defaults::gift_bar( 'inherit_progress_bar_settings' ),
            ],
            self::common_options( 'bar_type', true ),
            self::common_options( 'indicator_icon', true ),
            self::common_options( 'indicator_icon_size', true ),
            self::common_options( 'indicator_icon_bg_color', true ),
            self::common_options( 'circle_size', true ),
            self::common_options( 'inside_circle', true ),
            self::common_options( 'icon', true ),
            self::common_options( 'icon_color', true ),
            self::common_options( 'circle_bg_color', true ),
            self::common_options( 'bar_inner_color', true ),
            self::common_options( 'bar_bg_color', true ),
            self::common_options( 'bar_border_color', true ),
            self::common_options( 'text_color', true ),
            self::common_options( 'box_bg_color', true ),
            self::common_options( 'box_max_width', true ),
            self::common_options( 'box_alignment', true ),
            self::common_options( 'bar_height', true ),
            self::common_options( 'bar_radius', true ),
            self::common_options( 'center_text', true ),
            self::common_options( 'disable_animation', true ),
            self::common_options( 'disabled_animations', true ),
            self::common_options( 'remove_bar_stripes', true ),
            self::common_options( 'hide_border_shadow', true )
        ];
        return apply_filters( 'fsl_settings_gift_bar', $gift_bar );
    }

    /**
     * @since    1.0.0
     */
    public static function product_label() {
        $product_label = [
            [
                'type'    => 'checkbox',
                'name'    => 'enable_label',
                'label'   => esc_html__( 'Enable Product Label', 'free-shipping-label' ),
                'default' => Defaults::label( 'enable_label' ),
            ],
            [
                'type'     => 'select2',
                'name'     => 'exclude__disabled',
                'label'    => esc_html__( 'Exclude', 'free-shipping-label' ),
                'desc'     => esc_html__( 'Select products or categories.', 'free-shipping-label' ),
                'options'  => [],
                'multiple' => true,
                'default'  => Defaults::label( 'exclude' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-label-single-product',
                'label' => esc_html__( 'Single product page', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_single_simple_product',
                'label'   => esc_html__( 'Enable for simple products', 'free-shipping-label' ),
                'default' => Defaults::label( 'show_on_single_simple_product' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_single_variable_product',
                'label'   => esc_html__( 'Enable for variable products', 'free-shipping-label' ),
                'desc'    => esc_html__( 'The label will only be displayed if the lowest variation price meets the requirements for free shipping.', 'free-shipping-label' ),
                'default' => Defaults::label( 'show_on_single_variable_product' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_single_variation',
                'label'   => esc_html__( 'Enable for single variation', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Customer needs to select a variation first.', 'free-shipping-label' ),
                'default' => Defaults::label( 'show_on_single_variation' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-label-listed-products',
                'label' => esc_html__( 'Listed products', 'free-shipping-label' ),
                'desc'  => esc_html__( 'Main shop page, category pages, archive pages, etc.', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_list_simple_products',
                'label'   => esc_html__( 'Enable for simple products', 'free-shipping-label' ),
                'default' => Defaults::label( 'show_on_list_simple_products' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_list_variable_products',
                'label'   => esc_html__( 'Enable for variable products', 'free-shipping-label' ),
                'desc'    => esc_html__( 'The label will only be displayed if the lowest variation price meets the requirements for free shipping.', 'free-shipping-label' ),
                'default' => Defaults::label( 'show_on_list_variable_products' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'label_over_image__disabled',
                'label'   => esc_html__( 'Label over image', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Show label over product image.', 'free-shipping-label' ),
                'default' => Defaults::label( 'label_over_image' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'position__disabled',
                'label'   => esc_html__( 'Position', 'free-shipping-label' ),
                'options' => [
                    'top-left'     => esc_html__( 'Top Left', 'free-shipping-label' ),
                    'top-right'    => esc_html__( 'Top Right', 'free-shipping-label' ),
                    'bottom-left'  => esc_html__( 'Bottom Left', 'free-shipping-label' ),
                    'bottom-right' => esc_html__( 'Bottom Right', 'free-shipping-label' ),
                ],
                'desc'    => sprintf(
                    '%s <a href="https://devnet.hr/docs/free-shipping-label/product-label/#position" target="_blank">%s</a> %s',
                    esc_html__( 'Top positions align well with most themes, while bottom positions may display a label close to the button or price. Margins can be adjusted for further customization. Refer to the ', 'free-shipping-label' ),
                    esc_html__( 'documentation', 'free-shipping-label' ),
                    esc_html__( 'for more information.', 'free-shipping-label' )
                ),
                'default' => Defaults::label( 'position' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_y__disabled',
                'label'             => esc_html__( 'Margin: ', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'Vertical margin', 'free-shipping-label' ),
                'default'           => Defaults::label( 'margin_y' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_x__disabled',
                'label'             => esc_html__( 'Margin: ', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'Horizontal margin', 'free-shipping-label' ),
                'default'           => Defaults::label( 'margin_x' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'  => 'info',
                'name'  => 'info-label-text',
                'label' => esc_html__( 'Text Label', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'              => 'text',
                'name'              => 'text',
                'label'             => esc_html__( 'Label Text', 'free-shipping-label' ),
                'desc'              => '',
                'placeholder'       => '',
                'max'               => 25,
                'default'           => Defaults::label( 'text' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'color',
                'name'    => 'text_color',
                'label'   => esc_html__( 'Text color', 'free-shipping-label' ),
                'default' => Defaults::label( 'color' ),
            ],
            [
                'type'    => 'color',
                'name'    => 'bg_color',
                'label'   => esc_html__( 'Background color', 'free-shipping-label' ),
                'default' => Defaults::label( 'bg_color' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'hide_border_shadow',
                'label'   => esc_html__( 'Hide border shadow', 'free-shipping-label' ),
                'default' => Defaults::label( 'hide_border_shadow' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-label-image',
                'label' => esc_html__( 'Image Label', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'enable_image_label__disabled',
                'label'   => esc_html__( 'Enable', 'free-shipping-label' ),
                'desc'    => esc_html__( 'If enabled, text label will be ignored.', 'free-shipping-label' ),
                'default' => Defaults::label( 'enable_image_label' ),
            ],
            [
                'type'    => 'file',
                'name'    => 'image__disabled',
                'label'   => esc_html__( 'Image', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Please select an image from the media library or paste the URL of an external image.', 'free-shipping-label' ),
                'default' => Defaults::label( 'image' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'image_width__disabled',
                'label'             => esc_html__( 'Width ', 'free-shipping-label' ),
                'desc'              => esc_html__( 'width in pixels (px)', 'free-shipping-label' ),
                'unit'              => 'px',
                'placeholder'       => esc_html__( 'width in px', 'free-shipping-label' ),
                'min'               => 0,
                'step'              => '1',
                'default'           => Defaults::label( 'image_width' ),
                'sanitize_callback' => 'absint',
            ]
        ];
        return apply_filters( 'fsl_settings_product_label', $product_label );
    }

    private static function shipping_zones_option_list() {
        $zones = \WC_Shipping_Zones::get_zones();
        $options = [
            '' => esc_html__( '-- None --', 'free-shipping-label' ),
        ];
        foreach ( $zones as $key => $zone ) {
            $id = ( isset( $zone['zone_id'] ) ? $zone['zone_id'] : null );
            $name = ( isset( $zone['zone_name'] ) ? $zone['zone_name'] : null );
            if ( $id && $name ) {
                $options[$id] = $name;
            }
        }
        return $options;
    }

    /**
     * Generate custom threshold fields per shipping method for each zone.
     *
     * @since    3.0.0
     */
    private static function custom_threshold_fields() {
        $fields = [];
        $is_pro = false;
        // Get all shipping zones
        $zones = \WC_Shipping_Zones::get_zones();
        foreach ( $zones as $zone ) {
            $fields[] = [
                'type'  => 'info',
                'name'  => 'info-bar-zone-name',
                'label' => esc_html( $zone['zone_name'] ),
                'class' => 'info',
            ];
            // Output shipping methods for each zone
            foreach ( $zone['shipping_methods'] as $shipping_method ) {
                $rate_id = $shipping_method->get_rate_id();
                $fields[] = [
                    'type'              => 'number',
                    'name'              => ( $is_pro ? $rate_id : '' ),
                    'label'             => $shipping_method->get_title(),
                    'min'               => 0,
                    'step'              => 0.01,
                    'sanitize_callback' => 'absint',
                ];
            }
        }
        return $fields;
    }

}
