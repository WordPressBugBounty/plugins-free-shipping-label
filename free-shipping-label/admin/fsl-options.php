<?php

namespace Devnet\FSL\Admin;

use Devnet\FSL\Includes\Defaults;
use Devnet\FSL\Includes\Icons;
use Devnet\FSL\Includes\Helper;
use Devnet\FSL\Frontend\Bar\Gift_Bar;
use Devnet\FSL\Frontend\Bar\FSL_Bar;
use Devnet\FSL\Frontend\FSL_Label;
use Devnet\FSL\Includes\Placement;
use Devnet\FSL\Includes\Presets;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Options for settings panel
 *
 */
class Options {
    /**
     * The previews' progress until the slider moves, in percent of their last goal: under the first goal (free
     * shipping at 50, the gift at 90).
     *
     * @since	4.0.0
     */
    const PREVIEW_PERCENT = 50;

    /**
     * The "no icon" choice of an icon field.
     *
     * @since	4.0.0
     */
    const NO_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="#8c8f94" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M6.5 17.5l11-11"/></svg>';

    public static function common_options( $name, $premium = false, $type = '' ) {
        $is_disabled = ( $premium ? '__disabled' : '' );
        $desc_bar_placeholders = sprintf( '%s
            <input type="text" readonly="readonly" size="22" value="{free_shipping_amount}" />
            <input type="text" readonly="readonly" size="11" value="{remaining}" />', esc_html__( 'Placeholders:', 'free-shipping-label' ) );
        $default_qualified_message = Defaults::bar( 'qualified_message' );
        $default_title = Defaults::bar( 'title' );
        $default_description = Defaults::bar( 'description' );
        if ( $type === 'gift_bar' ) {
            $desc_bar_placeholders = sprintf( '%s
                <input type="text" readonly="readonly" size="11" value="{threshold}" />
                <input type="text" readonly="readonly" size="11" value="{remaining}" />
                ', esc_html__( 'Placeholders:', 'free-shipping-label' ) );
            $default_qualified_message = Defaults::gift_bar( 'qualified_message' );
            $default_title = Defaults::gift_bar( 'title' );
            $default_description = Defaults::gift_bar( 'description' );
        }
        $options = [
            'info-bar-after-threshold' => [
                'type'  => 'info',
                'name'  => 'info-bar-after-threshold' . $is_disabled,
                'label' => esc_html__( 'After free shipping is reached', 'free-shipping-label' ),
                'class' => 'info',
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
                'desc'  => esc_html__( 'Leave a text empty to use the default text, translated for each language.', 'free-shipping-label' ),
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
                'default'           => '',
                'placeholder'       => $default_title,
                'sanitize_callback' => 'wp_kses_post',
                'toggle'            => [
                    'name'    => 'show_title' . $is_disabled,
                    'label'   => __( 'Show title', 'free-shipping-label' ),
                    'default' => Defaults::bar( 'show_title' ),
                ],
            ],
            'description'              => [
                'type'              => 'text',
                'name'              => 'description' . $is_disabled,
                'label'             => esc_html__( 'Description', 'free-shipping-label' ),
                'default'           => '',
                'placeholder'       => $default_description,
                'sanitize_callback' => 'wp_kses_post',
                'toggle'            => [
                    'name'    => 'show_description' . $is_disabled,
                    'label'   => __( 'Show description', 'free-shipping-label' ),
                    'default' => Defaults::bar( 'show_description' ),
                ],
            ],
            'qualified_message'        => [
                'type'              => 'text',
                'name'              => 'qualified_message' . $is_disabled,
                'label'             => esc_html__( 'Message when the goal is reached', 'free-shipping-label' ),
                'sanitize_callback' => 'wp_kses_post',
                'max'               => 100,
                'default'           => '',
                'placeholder'       => $default_qualified_message,
                'toggle'            => [
                    'name'    => 'show_qualified_message' . $is_disabled,
                    'label'   => __( 'Show message', 'free-shipping-label' ),
                    'default' => Defaults::bar( 'show_qualified_message' ),
                ],
            ],
            'info-bar-design'          => [
                'type'  => 'info',
                'name'  => 'info-bar-design' . $is_disabled,
                'label' => esc_html__( 'Design', 'free-shipping-label' ),
                'class' => 'info',
            ],
            'bar_inner_color'          => [
                'type'              => 'color',
                'name'              => 'bar_inner_color' . $is_disabled,
                'label'             => esc_html__( 'Bar fill color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'bar_inner_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'bar_bg_color'             => [
                'type'              => 'color',
                'name'              => 'bar_bg_color' . $is_disabled,
                'label'             => esc_html__( 'Bar background color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'bar_bg_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'bar_border_color'         => [
                'type'              => 'color',
                'name'              => 'bar_border_color' . $is_disabled,
                'label'             => esc_html__( 'Bar border color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'bar_border_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'bar_height'               => [
                'type'              => 'number',
                'name'              => 'bar_height' . $is_disabled,
                'label'             => esc_html__( 'Bar height', 'free-shipping-label' ),
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
                'label'   => esc_html__( 'Animations to turn off', 'free-shipping-label' ),
                'options' => [
                    'all'    => esc_html__( 'All', 'free-shipping-label' ),
                    'strips' => esc_html__( 'Moving stripes', 'free-shipping-label' ),
                    'shine'  => esc_html__( 'Shine', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'disabled_animations' ),
            ],
            'hide_border_shadow'       => [
                'type'    => 'checkbox',
                'name'    => 'hide_border_shadow' . $is_disabled,
                'label'   => esc_html__( 'Hide border and shadow', 'free-shipping-label' ),
                'default' => Defaults::bar( 'hide_border_shadow' ),
            ],
            'bar_type'                 => [
                'type'    => 'select',
                'name'    => 'bar_type' . $is_disabled,
                'label'   => esc_html__( 'Bar type', 'free-shipping-label' ),
                'options' => [
                    'linear'      => esc_html__( 'Linear', 'free-shipping-label' ),
                    '_disabled_1' => esc_html__( 'Circular', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'bar_type' ),
            ],
            'indicator_icon'           => [
                'type'    => 'checkbox',
                'name'    => 'indicator_icon' . $is_disabled,
                'label'   => esc_html__( 'Goal icons', 'free-shipping-label' ),
                'default' => Defaults::bar( 'indicator_icon' ),
            ],
            'indicator_icon_shape'     => [
                'type'    => 'select',
                'name'    => 'indicator_icon_shape' . $is_disabled,
                'label'   => esc_html__( 'Icon shape', 'free-shipping-label' ),
                'options' => [
                    'round'   => esc_html__( 'Round', 'free-shipping-label' ),
                    'rounded' => esc_html__( 'Rounded', 'free-shipping-label' ),
                    'square'  => esc_html__( 'Square', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'indicator_icon_shape' ),
            ],
            'indicator_icon_size'      => [
                'type'              => 'number',
                'name'              => 'indicator_icon_size' . $is_disabled,
                'label'             => esc_html__( 'Icon size', 'free-shipping-label' ),
                'unit'              => 'px',
                'min'               => 1,
                'step'              => '1',
                'default'           => Defaults::bar( 'indicator_icon_size' ),
                'sanitize_callback' => 'absint',
            ],
            'indicator_icon_bg_color'  => [
                'type'              => 'color',
                'name'              => 'indicator_icon_bg_color' . $is_disabled,
                'label'             => esc_html__( 'Icon background color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'indicator_icon_bg_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'circle_size'              => [
                'type'              => 'number',
                'name'              => 'circle_size' . $is_disabled,
                'label'             => esc_html__( 'Circle size', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'width and height in px', 'free-shipping-label' ),
                'unit'              => 'px',
                'min'               => 100,
                'max'               => 300,
                'step'              => '1',
                'default'           => Defaults::bar( 'circle_size' ),
                'sanitize_callback' => 'absint',
            ],
            'ring_thickness'           => [
                'type'              => 'number',
                'name'              => 'ring_thickness' . $is_disabled,
                'label'             => esc_html__( 'Ring thickness', 'free-shipping-label' ),
                'desc'              => esc_html__( 'In percent of the circle size.', 'free-shipping-label' ),
                'unit'              => '%',
                'min'               => 2,
                'max'               => 30,
                'step'              => '0.5',
                'default'           => Defaults::bar( 'ring_thickness' ),
                'sanitize_callback' => [self::class, 'sanitize_ring_thickness'],
            ],
            'inside_circle'            => [
                'type'    => 'select',
                'name'    => 'inside_circle' . $is_disabled,
                'label'   => esc_html__( 'Inside circle', 'free-shipping-label' ),
                'options' => [
                    ''            => esc_html__( 'Nothing', 'free-shipping-label' ),
                    'title'       => esc_html__( 'Title', 'free-shipping-label' ),
                    'description' => esc_html__( 'Description', 'free-shipping-label' ),
                    'icon'        => esc_html__( 'Icon', 'free-shipping-label' ),
                    'amount'      => esc_html__( 'Amount left', 'free-shipping-label' ),
                    'percent'     => esc_html__( 'Percent', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'inside_circle' ),
            ],
            'icon'                     => [
                'type'    => 'radio_image',
                'name'    => 'icon' . $is_disabled,
                'label'   => esc_html__( 'Icon', 'free-shipping-label' ),
                'options' => Icons::get_all(),
                'default' => ( 'gift_bar' === $type ? '' : Defaults::bar( 'icon' ) ),
            ],
            'icon_color'               => [
                'type'              => 'color',
                'name'              => 'icon_color' . $is_disabled,
                'label'             => esc_html__( 'Icon color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'icon_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'circle_bg_color'          => [
                'type'              => 'color',
                'name'              => 'circle_bg_color' . $is_disabled,
                'label'             => esc_html__( 'Circle background color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'circle_bg_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'text_color'               => [
                'type'              => 'color',
                'name'              => 'text_color' . $is_disabled,
                'label'             => esc_html__( 'Text color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'text_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'box_bg_color'             => [
                'type'              => 'color',
                'name'              => 'box_bg_color' . $is_disabled,
                'label'             => esc_html__( 'Box background color', 'free-shipping-label' ),
                'default'           => Defaults::bar( 'box_bg_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            'box_max_width'            => [
                'type'              => 'number',
                'name'              => 'box_max_width' . $is_disabled,
                'label'             => esc_html__( 'Box maximum width', 'free-shipping-label' ),
                'desc'              => esc_html__( '0 = full width', 'free-shipping-label' ),
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
                    'center'      => esc_html__( 'Center', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Left', 'free-shipping-label' ),
                    '_disabled_3' => esc_html__( 'Right', 'free-shipping-label' ),
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
                'label'             => esc_html__( 'Bar corners', 'free-shipping-label' ),
                'desc'              => esc_html__( '0 = square corners', 'free-shipping-label' ),
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
                'type'  => 'info',
                'name'  => 'info-general-threshold',
                'label' => esc_html__( 'Threshold', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'    => 'select',
                'name'    => 'initial_zone',
                'label'   => esc_html__( 'Initial shipping zone', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Used until customers enter their address. With "None", the bar shows only after they do.', 'free-shipping-label' ),
                'options' => self::shipping_zones_option_list(),
                'default' => Defaults::general( 'initial_zone' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'custom_threshold',
                'label'             => esc_html__( 'Custom threshold', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Your own free shipping amount for all shipping zones, instead of your WooCommerce shipping settings.', 'free-shipping-label' ),
                'placeholder'       => esc_html__( 'Amount', 'free-shipping-label' ),
                'toggle'            => [
                    'name'    => 'enable_custom_threshold',
                    'label'   => __( 'Use a custom threshold', 'free-shipping-label' ),
                    'default' => Defaults::general( 'enable_custom_threshold' ),
                ],
                'min'               => 0,
                'step'              => 0.01,
                'default'           => Defaults::general( 'custom_threshold' ),
                'sanitize_callback' => [self::class, 'sanitize_amount'],
            ],
            [
                'type'              => 'group',
                'name'              => 'custom_threshold_per_method__disabled',
                'label'             => esc_html__( 'Amount per shipping method', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Overrides the amount above for that method. Methods left empty use the amount above.', 'free-shipping-label' ),
                'fields'            => self::custom_threshold_fields(),
                'sanitize_callback' => [self::class, 'sanitize_amount'],
            ],
            [
                'type'  => 'info',
                'name'  => 'info-general-checkout',
                'label' => esc_html__( 'Cart and checkout', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'    => 'select',
                'name'    => 'hide_shipping_rates',
                'label'   => esc_html__( 'When free shipping is available', 'free-shipping-label' ),
                'desc'    => '',
                'options' => [
                    ''                  => esc_html__( 'Show all shipping methods', 'free-shipping-label' ),
                    'hide_all'          => esc_html__( 'Show only free shipping', 'free-shipping-label' ),
                    'hide_except_local' => esc_html__( 'Show only free shipping and local pickup', 'free-shipping-label' ),
                ],
                'default' => Defaults::general( 'hide_shipping_rates' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-general-visibility',
                'label' => esc_html__( 'Visibility', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'only_logged_users',
                'label'   => esc_html__( 'Logged-in users only', 'free-shipping-label' ),
                'default' => Defaults::general( 'only_logged_users' ),
            ],
            // Multilingual (until 3.5.x) is replaced by "empty text = translated default". The value stays saved for
            // FSL Discount 1.0.8 and older, and for a downgrade.
            [
                'type'    => 'hidden',
                'name'    => 'multilingual',
                'class'   => 'hidden',
                'default' => Defaults::general( 'multilingual' ),
            ],
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
                'label'    => esc_html__( 'FunnelKit', 'free-shipping-label' ),
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
        // WPC Fly Cart positions
        if ( defined( 'WOOFC_VERSION' ) ) {
            $minicart_optgroup = [[
                'label'    => esc_html__( 'WPC Fly Cart', 'free-shipping-label' ),
                'options'  => [
                    '1' => esc_html__( 'Top of the side cart', 'free-shipping-label' ),
                    '2' => esc_html__( 'Above cart items', 'free-shipping-label' ),
                    '3' => esc_html__( 'Below cart items', 'free-shipping-label' ),
                    '4' => esc_html__( 'Above the footer', 'free-shipping-label' ),
                    '5' => esc_html__( 'Above buttons', 'free-shipping-label' ),
                    '6' => esc_html__( 'Below buttons', 'free-shipping-label' ),
                    '7' => esc_html__( 'Below the footer', 'free-shipping-label' ),
                    '8' => esc_html__( 'Bottom of the side cart', 'free-shipping-label' ),
                ],
                'disabled' => true,
            ]];
        }
        // Side Cart
        if ( defined( 'XOO_WSC_PLUGIN_FILE' ) ) {
            $minicart_optgroup = [[
                'label'    => esc_html__( 'Side Cart', 'free-shipping-label' ),
                'options'  => [
                    '1' => esc_html__( 'Header', 'free-shipping-label' ),
                    '2' => esc_html__( 'Before products', 'free-shipping-label' ),
                    '3' => esc_html__( 'After products', 'free-shipping-label' ),
                    '4' => esc_html__( 'Footer start', 'free-shipping-label' ),
                    '5' => esc_html__( 'Footer end', 'free-shipping-label' ),
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
                'label'   => esc_html__( 'Show with local pickup', 'free-shipping-label' ),
                'default' => Defaults::bar( 'local_pickup' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'zero_shipping',
                'label'   => esc_html__( 'Treat a 0 shipping cost as free shipping', 'free-shipping-label' ),
                'desc'    => sprintf( '%s <br> %s', esc_html__( 'Turn on if another shipping plugin gives free shipping as a 0 rate and the bar still shows.', 'free-shipping-label' ), sprintf( 
                    /* translators: 1: <a> to the documentation, 2: </a> */
                    esc_html__( 'Refer to the %1$sdocumentation%2$s for more information.', 'free-shipping-label' ),
                    '<a href="https://devnet.hr/docs/free-shipping-label/progress-bar/#zero-shipping" target="_blank">',
                    '</a>'
                 ) ),
                'default' => Defaults::bar( 'zero_shipping' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-bar-positions',
                'label' => esc_html__( 'Positions', 'free-shipping-label' ),
                'class' => 'info',
                'desc'  => self::progress_bar_position_notice(),
            ],
            [
                'type'    => 'select',
                'name'    => 'cart_position',
                'label'   => esc_html__( 'Cart position', 'free-shipping-label' ),
                'options' => [
                    ''                                => esc_html__( '— Do not display —', 'free-shipping-label' ),
                    '_disabled_1'                     => esc_html__( 'Before cart total', 'free-shipping-label' ),
                    'woocommerce_proceed_to_checkout' => esc_html__( 'Before "Proceed to checkout"', 'free-shipping-label' ),
                    '_disabled_2'                     => esc_html__( 'After "Proceed to checkout"', 'free-shipping-label' ),
                    '_disabled_3'                     => esc_html__( 'Before cart', 'free-shipping-label' ),
                    '_disabled_4'                     => esc_html__( 'After cart', 'free-shipping-label' ),
                    '_disabled_5'                     => esc_html__( 'Before the cart table', 'free-shipping-label' ),
                    '_disabled_6'                     => esc_html__( 'After the cart table', 'free-shipping-label' ),
                    '_disabled_7'                     => esc_html__( 'Cart table (before items)', 'free-shipping-label' ),
                    '_disabled_8'                     => esc_html__( 'Cart table (after items)', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'cart_position' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'checkout_position',
                'label'   => esc_html__( 'Checkout position', 'free-shipping-label' ),
                'options' => [
                    ''                                       => esc_html__( '— Do not display —', 'free-shipping-label' ),
                    '_disabled_1'                            => esc_html__( 'Before checkout form', 'free-shipping-label' ),
                    '_disabled_2'                            => esc_html__( 'After checkout form', 'free-shipping-label' ),
                    '_disabled_3'                            => esc_html__( 'Before the order summary', 'free-shipping-label' ),
                    'woocommerce_review_order_before_submit' => esc_html__( 'Before "Place order"', 'free-shipping-label' ),
                    '_disabled_4'                            => esc_html__( 'After "Place order"', 'free-shipping-label' ),
                    '_disabled_5'                            => esc_html__( 'Before payment', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'checkout_position' ),
            ],
            [
                'type'     => 'select',
                'name'     => 'minicart_position',
                'label'    => esc_html__( 'Mini-cart position', 'free-shipping-label' ),
                'options'  => [
                    ''                                                => esc_html__( '— Do not display —', 'free-shipping-label' ),
                    '_disabled_1'                                     => esc_html__( 'Before the mini-cart', 'free-shipping-label' ),
                    '_disabled_2'                                     => esc_html__( 'Before content', 'free-shipping-label' ),
                    '_disabled_3'                                     => esc_html__( 'After content', 'free-shipping-label' ),
                    'woocommerce_widget_shopping_cart_before_buttons' => esc_html__( 'Before buttons', 'free-shipping-label' ),
                    '_disabled_4'                                     => esc_html__( 'After the mini-cart', 'free-shipping-label' ),
                ],
                'optgroup' => $minicart_optgroup,
                'default'  => Defaults::bar( 'minicart_position' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'product_position',
                'label'   => esc_html__( 'Product page position', 'free-shipping-label' ),
                'options' => [
                    ''             => esc_html__( '— Do not display —', 'free-shipping-label' ),
                    '_disabled_1'  => esc_html__( 'Before product', 'free-shipping-label' ),
                    '_disabled_2'  => esc_html__( 'After product', 'free-shipping-label' ),
                    '_disabled_3'  => esc_html__( 'Before the product images', 'free-shipping-label' ),
                    '_disabled_4'  => esc_html__( 'Inside product summary', 'free-shipping-label' ),
                    '_disabled_5'  => esc_html__( 'After product summary', 'free-shipping-label' ),
                    '_disabled_6'  => esc_html__( 'Before add to cart form', 'free-shipping-label' ),
                    '_disabled_7'  => esc_html__( 'After add to cart form', 'free-shipping-label' ),
                    '_disabled_8'  => esc_html__( 'Before add to cart button', 'free-shipping-label' ),
                    '_disabled_9'  => esc_html__( 'After add to cart button', 'free-shipping-label' ),
                    '_disabled_10' => esc_html__( 'Before the SKU and categories', 'free-shipping-label' ),
                    '_disabled_11' => esc_html__( 'After the SKU and categories', 'free-shipping-label' ),
                ],
                'default' => Defaults::bar( 'product_position' ),
            ],
            [
                'type'  => 'text',
                'name'  => 'shortcode_info__disabled',
                'label' => esc_html__( 'Shortcode', 'free-shipping-label' ),
                'desc'  => esc_html__( 'Paste it into a page, widget or template to show the bar there.', 'free-shipping-label' ),
            ],
            // Texts while shopping, then what customers see once they qualify.
            self::common_options( 'info-bar-text' ),
            [
                'type'              => 'text',
                'name'              => 'label',
                'label'             => esc_html__( 'Label', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Short name of this goal in the layouts.', 'free-shipping-label' ),
                'default'           => '',
                'placeholder'       => Defaults::bar( 'label' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            self::common_options( 'info-bar-placeholders' ),
            self::common_options( 'title' ),
            self::common_options( 'description' ),
            [
                'type'              => 'text',
                'name'              => 'close_message__disabled',
                'label'             => esc_html__( 'Message when close to free shipping', 'free-shipping-label' ),
                'desc'              => sprintf( 
                    /* translators: %s: part of the free shipping threshold, e.g. 75% */
                    esc_html__( 'Shows from %s of the way to free shipping.', 'free-shipping-label' ),
                    FSL_Bar::close_message_percent() . '%'
                 ),
                'default'           => '',
                'placeholder'       => Defaults::bar( 'close_message' ),
                'sanitize_callback' => 'wp_kses_post',
                'toggle'            => [
                    'name'    => 'close_message_replaces__disabled',
                    'label'   => __( 'Where to show the message', 'free-shipping-label' ),
                    'default' => Defaults::bar( 'close_message_replaces' ),
                    'options' => [
                        ''            => __( 'Off', 'free-shipping-label' ),
                        'title'       => __( 'Instead of the title', 'free-shipping-label' ),
                        'description' => __( 'Instead of the description', 'free-shipping-label' ),
                    ],
                ],
            ],
            self::common_options( 'info-bar-after-threshold' ),
            self::common_options( 'qualified_message' ),
            self::common_options( 'show_full_progress_bar', true ),
            self::common_options( 'info-bar-design' ),
            self::common_options( 'bar_type' ),
            [
                'type'              => 'html',
                'name'              => 'bar_preset',
                'label'             => esc_html__( 'Design preset', 'free-shipping-label' ),
                'callback'          => [self::class, 'preset_picker'],
                'class'             => 'bar_preset fsl-pro-preview',
                'default'           => '',
                'sanitize_callback' => [Presets::class, 'sanitize'],
            ],
            [
                'type'              => 'color',
                'name'              => 'bar_accent_color__disabled',
                'label'             => esc_html__( 'Accent color', 'free-shipping-label' ),
                'desc'              => esc_html__( 'The second color of gradient presets.', 'free-shipping-label' ),
                'default'           => '',
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            [
                'type'              => 'html',
                'name'              => 'layout',
                'label'             => esc_html__( 'Layout', 'free-shipping-label' ),
                'callback'          => [self::class, 'layout_picker'],
                'default'           => Defaults::bar( 'layout' ),
                'sanitize_callback' => [self::class, 'sanitize_layout'],
            ],
            [
                'type'     => 'html',
                'name'     => 'design_toggle',
                'label'    => esc_html__( 'Customize design', 'free-shipping-label' ),
                'callback' => [self::class, 'design_toggle'],
            ],
            self::common_options( 'indicator_icon', true ),
            self::common_options( 'indicator_icon_size', true ),
            self::common_options( 'indicator_icon_shape', true ),
            self::common_options( 'circle_size', true ),
            self::common_options( 'ring_thickness', true ),
            self::common_options( 'inside_circle', true ),
            self::common_options( 'icon', true ),
            self::common_options( 'icon_color', true ),
            self::common_options( 'indicator_icon_bg_color', true ),
            self::common_options( 'circle_bg_color', true ),
            self::common_options( 'bar_inner_color' ),
            self::common_options( 'bar_bg_color' ),
            self::common_options( 'bar_border_color' ),
            self::common_options( 'text_color', true ),
            [
                'type'              => 'select',
                'name'              => 'text_size',
                'label'             => esc_html__( 'Text size', 'free-shipping-label' ),
                'options'           => [
                    ''       => esc_html__( 'Theme default', 'free-shipping-label' ),
                    'small'  => esc_html__( 'Small', 'free-shipping-label' ),
                    'medium' => esc_html__( 'Medium', 'free-shipping-label' ),
                    'large'  => esc_html__( 'Large', 'free-shipping-label' ),
                ],
                'default'           => Defaults::bar( 'text_size' ),
                'sanitize_callback' => 'sanitize_key',
            ],
            self::common_options( 'box_bg_color', true ),
            self::common_options( 'box_max_width', true ),
            self::common_options( 'box_alignment' ),
            self::common_options( 'bar_height' ),
            self::common_options( 'bar_radius', true ),
            self::common_options( 'center_text', true ),
            self::common_options( 'disable_animation' ),
            self::common_options( 'disabled_animations' ),
            self::common_options( 'remove_bar_stripes', true ),
            self::common_options( 'hide_border_shadow' ),
        ];
        $block_positions_disabled = true;
        $progress_bar = self::group_block_positions( $progress_bar, $block_positions_disabled );
        return apply_filters( 'fsl_settings_progress_bar', $progress_bar );
    }

    /**
     * Design preset picker: a card per preset with a preview of the merchant's own bar in that style. Free sees
     * the previews; choosing one is Pro (fsl-admin.js writes its values into the design fields).
     *
     * @since	4.0.0
     */
    public static function preset_picker() {
        $can_apply = false;
        $current = ( $can_apply ? Presets::sanitize( DEVNET_FSL_OPTIONS['progress_bar']['bar_preset'] ?? '' ) : '' );
        $presets = [
            '' => [
                'label'  => __( 'Custom', 'free-shipping-label' ),
                'accent' => false,
                'values' => [],
            ],
        ] + Presets::all();
        $bar = new FSL_Bar(true);
        // Indicator icons are Pro on the storefront; the Free previews still show them on the presets.
        $preview_icons = function ( $data, $progress, $opt ) {
            return ( empty( $opt['bar_preset'] ) ? $data : FSL_Bar::set_threshold_indicators( $progress, $opt ) );
        };
        if ( !$can_apply ) {
            add_filter(
                'fsl_progress_bar_threshold_data',
                $preview_icons,
                10,
                3
            );
        }
        $current_preview = '';
        ob_start();
        echo '<fieldset id="fsl-preset-gallery" class="fsl-preset-gallery" hidden><legend class="screen-reader-text">' . esc_html__( 'Design preset', 'free-shipping-label' ) . '</legend>';
        $saved_type = DEVNET_FSL_OPTIONS['progress_bar']['bar_type'] ?? 'linear';
        foreach ( $presets as $slug => $preset ) {
            // The circular bar is Pro: Free doesn't get its presets.
            if ( !$can_apply && !empty( $preset['circular_only'] ) ) {
                continue;
            }
            // Custom and the saved preset show the saved design, the others their own values.
            $values = ( $slug === $current ? [] : $preset['values'] );
            // Each design on its own: the single bar, whatever the layout (the live preview shows them together).
            $opt = $bar->get_progress_bar_options( [
                'bar_preset'  => $slug,
                'module_name' => 'free-shipping',
                'bar_type'    => 'linear',
                'layout'      => 'list',
            ] + $values );
            $types = ( $slug ? array_filter( ['linear', 'circular'], fn( $type ) => Presets::supports( $slug, $type ) ) : ['linear', 'circular'] );
            // Pro: a preview on the circular bar too, the one the form's bar type shows. A preset saved on the linear bar
            // gets its circular values, as choosing it on the circular bar would write them.
            $circular_preview = '';
            if ( $can_apply && in_array( 'circular', $types, true ) ) {
                $circular_values = ( $slug === $current ? ( 'circular' === $saved_type ? [] : $preset['circular'] ?? [] ) : ($preset['circular'] ?? []) + $preset['values'] );
                $circular_preview = self::render_preview( $bar, $bar->get_progress_bar_options( [
                    'bar_preset'  => $slug,
                    'module_name' => 'free-shipping',
                    'bar_type'    => 'circular',
                    'layout'      => 'list',
                ] + $circular_values ) );
            }
            if ( !$can_apply && $slug ) {
                // What fsl_wrapper() adds in Pro only.
                $opt['wrapper_class'] = array_filter( ['fsl-preset', 'fsl-preset-' . $slug, ( $opt['remove_bar_stripes'] ? 'fsl-no-stripes' : '' )] );
                $opt['wrapper_styles'] = array_filter( [
                    ( $opt['box_bg_color'] ? 'background-color:' . $opt['box_bg_color'] . ';' : '' ),
                    ( $opt['text_color'] ? '--fsl-text-color:' . $opt['text_color'] . ';' : '' ),
                    ( $opt['bar_accent_color'] ? '--fsl-accent-color:' . $opt['bar_accent_color'] . ';' : '' ),
                    ( $opt['box_bg_color'] ? '--fsl-box-bg-color:' . $opt['box_bg_color'] . ';' : '' )
                ] );
            }
            $preview = ( in_array( 'linear', $types, true ) ? self::render_preview( $bar, $opt ) : '' );
            if ( !$can_apply && $slug && !$opt['center_text'] ) {
                // The Free bar always centers its text; the preset doesn't.
                $preview = str_replace( ' fsl-center-text', '', $preview );
            }
            $preview = '<div class="fsl-preset-preview-linear">' . $preview . '</div>' . (( $circular_preview ? '<div class="fsl-preset-preview-circular">' . $circular_preview . '</div>' : '' ));
            if ( $slug === $current ) {
                $current_preview = $preview;
            }
            echo '<div class="fsl-preset-card" data-types="' . esc_attr( implode( ' ', $types ) ) . '">';
            // A preset that needs a certain background says so. Free: the presets are Pro, Custom (its own bar) isn't.
            $name = esc_html( $preset['label'] ) . (( !$can_apply && $slug ? ' <span class="fsl-layout-card__pro">PRO</span>' : '' )) . (( empty( $preset['hint'] ) ? '' : '<span class="fsl-preset-card__hint">' . esc_html( $preset['hint'] ) . '</span>' ));
            if ( $can_apply ) {
                printf(
                    '<input type="radio" id="fsl-preset-%1$s" name="devnet_fsl_bar[bar_preset]" value="%2$s" data-values="%3$s" data-circular="%4$s"%5$s%6$s /><label for="fsl-preset-%1$s">%7$s</label>',
                    esc_attr( ( $slug ? $slug : 'custom' ) ),
                    esc_attr( $slug ),
                    esc_attr( wp_json_encode( $preset['values'] ) ),
                    esc_attr( wp_json_encode( $preset['circular'] ?? [] ) ),
                    ( $preset['accent'] ? ' data-accent="1"' : '' ),
                    checked( $current, $slug, false ),
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
                    $name
                );
            } else {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
                echo '<span class="fsl-preset-card__name">' . $name . '</span>';
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is fully escaped inside fsl_progress_bar_html().
            echo '<div class="fsl-preset-card__preview" aria-hidden="true">' . $preview . '</div></div>';
        }
        echo '</fieldset>';
        $gallery = ob_get_clean();
        remove_filter( 'fsl_progress_bar_threshold_data', $preview_icons );
        // Free can't choose a preset: its summary is its own bar.
        self::picker_summary(
            'fsl-preset-gallery',
            ( $can_apply ? $presets[$current]['label'] : __( 'Your bar', 'free-shipping-label' ) ),
            $current_preview,
            'fsl-preset-card__preview',
            ( $can_apply ? __( 'Change preset', 'free-shipping-label' ) : __( 'Browse presets', 'free-shipping-label' ) )
        );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped as it's built above.
        echo $gallery;
        if ( $can_apply ) {
            // The plugin's default design, for the settings presets write.
            $reset = self::default_design();
            echo '<input type="hidden" name="fsl_preset_applied" value="" />';
            echo '<div class="fsl-preset-status" aria-live="polite"><p hidden>' . esc_html__( 'Preset applied. Save to keep it.', 'free-shipping-label' ) . ' <button type="button" class="button-link fsl-preset-undo">' . esc_html__( 'Undo', 'free-shipping-label' ) . '</button></p></div>';
            echo '<p class="description">' . esc_html__( 'A preset fills the design settings; adjust them under Customize design.', 'free-shipping-label' ) . ' <button type="button" class="button-link fsl-preset-reset" data-values="' . esc_attr( wp_json_encode( $reset ) ) . '">' . esc_html__( 'Reset to the default design', 'free-shipping-label' ) . '</button></p>';
            return;
        }
        echo '<p class="description">' . wp_kses_post( sprintf( 
            /* translators: 1: <a> to Pro, 2: </a> */
            __( 'Your bar in each preset. Design presets are Pro. %1$sUpgrade →%2$s', 'free-shipping-label' ),
            '<a href="' . esc_url( 'https://devnet.hr/plugins/free-shipping-label/' ) . '" target="_blank" rel="noopener noreferrer">',
            '</a>'
         ) ) . '</p>';
    }

    /**
     * Multiple goals layout picker: a card per layout with a preview of two goals in the saved design and texts; the
     * form's bar type shows its layouts (the circular ones Pro only). Free sees the Pro layouts too; choosing one is Pro.
     *
     * @since	4.0.0
     */
    public static function layout_picker() {
        $pro = false;
        $current = self::current_layout( $pro );
        $previews = self::layout_previews();
        $layouts = self::shown_layouts( $current );
        self::picker_summary(
            'fsl-layout-gallery',
            $layouts[$current]['name'] ?? '',
            $previews[$current] ?? '',
            'fsl-layout-card__preview',
            __( 'Change layout', 'free-shipping-label' )
        );
        echo '<fieldset id="fsl-layout-gallery" class="fsl-layout-gallery" hidden><legend class="screen-reader-text">' . esc_html__( 'Layout', 'free-shipping-label' ) . '</legend>';
        $classic = false;
        foreach ( $layouts as $layout => $data ) {
            $locked = !$pro && !in_array( $layout, FSL_Bar::FREE_LAYOUTS, true );
            // The circular bar is Pro: Free doesn't get its layouts.
            if ( !$pro && !empty( $data['circular'] ) ) {
                continue;
            }
            if ( !empty( $data['classic'] ) && !$classic ) {
                $classic = true;
                echo '<p class="fsl-layout-gallery__group">' . esc_html__( 'Classic', 'free-shipping-label' ) . '</p>';
            }
            // The bar types it's for (the form's bar type shows them): of Classic, the Vertical list both.
            $types = ( 'list' === $layout ? 'linear circular' : (( !empty( $data['circular'] ) ? 'circular' : 'linear' )) );
            printf(
                '<div class="fsl-layout-card" data-types="%7$s"%8$s><input type="radio" id="fsl-layout-%1$s" name="devnet_fsl_bar[layout]" value="%1$s"%2$s%3$s /><label for="fsl-layout-%1$s">%4$s%5$s%6$s</label>',
                esc_attr( $layout ),
                checked( $current, $layout, false ),
                disabled( $locked, true, false ),
                esc_html( $data['name'] ),
                ( $locked ? ' <span class="fsl-layout-card__pro">PRO</span>' : '' ),
                ( empty( $data['hint'] ) ? '' : '<span class="fsl-preset-card__hint">' . esc_html( $data['hint'] ) . '</span>' ),
                esc_attr( $types ),
                ( empty( $data['pair'] ) ? '' : ' data-pair="' . esc_attr( $data['pair'] ) . '"' )
            );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is fully escaped inside fsl_progress_bar_html().
            echo '<div class="fsl-layout-card__preview" aria-hidden="true">' . $previews[$layout] . '</div></div>';
        }
        echo '</fieldset>';
        echo '<p class="description">' . esc_html__( 'How the bar shows your goals.', 'free-shipping-label' );
        if ( !$pro ) {
            echo ' ' . wp_kses_post( sprintf( 
                /* translators: 1: <a> to Pro, 2: </a> */
                __( 'Layouts marked PRO are Pro. %1$sUpgrade →%2$s', 'free-shipping-label' ),
                '<a href="' . esc_url( 'https://devnet.hr/plugins/free-shipping-label/' ) . '" target="_blank" rel="noopener noreferrer">',
                '</a>'
             ) );
        }
        echo '</p>';
    }

    /**
     * A picker's current choice, with the button that opens its gallery under it (fsl-admin.js). The preview takes
     * the card previews' class, and with it their styles.
     *
     * @since	4.0.0
     */
    private static function picker_summary(
        $gallery_id,
        $name,
        $preview,
        $preview_class,
        $open_label
    ) {
        printf(
            '<div class="fsl-picker-summary"><div class="fsl-picker-summary__preview %1$s" aria-hidden="true">%2$s</div><div><span class="fsl-picker-summary__name">%3$s</span><button type="button" class="button fsl-picker-toggle" aria-expanded="false" aria-controls="%4$s" data-show="%5$s" data-hide="%6$s">%5$s</button></div></div>',
            esc_attr( $preview_class ),
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is fully escaped inside fsl_progress_bar_html().
            $preview,
            esc_html( $name ),
            esc_attr( $gallery_id ),
            esc_attr( $open_label ),
            esc_attr__( 'Close', 'free-shipping-label' )
        );
    }

    /**
     * Multiple goals layouts: value => name, and a hint on the texts it shows; the ones before 4.0.0 are Classic, the
     * circular bar's are marked, with their counterpart on the linear bar (the picker checks it when the bar type
     * changes). A replaced one keeps working where it's saved (shown_layouts()).
     *
     * @since	4.0.0
     */
    private static function layouts() {
        return [
            'checklist'         => [
                'name' => __( 'Checklist', 'free-shipping-label' ),
                'hint' => __( 'Label and amount left', 'free-shipping-label' ),
            ],
            'timeline'          => [
                'name' => __( 'Timeline', 'free-shipping-label' ),
                'hint' => __( 'Title and description, no bar', 'free-shipping-label' ),
            ],
            'milestones'        => [
                'name' => __( 'Milestones', 'free-shipping-label' ),
                'hint' => __( 'Label, with icons or dots', 'free-shipping-label' ),
            ],
            'stepper'           => [
                'name' => __( 'Stepper', 'free-shipping-label' ),
                'hint' => __( 'Label and threshold, with icons or numbers', 'free-shipping-label' ),
            ],
            'segments'          => [
                'name' => __( 'Tier segments', 'free-shipping-label' ),
                'hint' => __( 'A bar per goal, label and amount left', 'free-shipping-label' ),
            ],
            'next_goal'         => [
                'name' => __( 'Next goal', 'free-shipping-label' ),
                'hint' => __( 'Description and labels', 'free-shipping-label' ),
            ],
            'split'             => [
                'name' => __( 'Split header', 'free-shipping-label' ),
                'hint' => __( 'Label and threshold', 'free-shipping-label' ),
            ],
            'headline'          => [
                'name' => __( 'Headline', 'free-shipping-label' ),
                'hint' => __( 'Description, with icons', 'free-shipping-label' ),
            ],
            'tiles'             => [
                'name' => __( 'Goal tiles', 'free-shipping-label' ),
                'hint' => __( 'Icon, label and amount left', 'free-shipping-label' ),
            ],
            'stacked'           => [
                'name' => __( 'Stacked bars', 'free-shipping-label' ),
                'hint' => __( 'A bar per goal, with icon and label', 'free-shipping-label' ),
            ],
            'spotlight'         => [
                'name' => __( 'Spotlight', 'free-shipping-label' ),
                'hint' => __( 'Each goal with its icon and amount left', 'free-shipping-label' ),
            ],
            'rewards_count'     => [
                'name' => __( 'Rewards count', 'free-shipping-label' ),
                'hint' => __( 'Goals reached, icons and description', 'free-shipping-label' ),
            ],
            'ring_markers'      => [
                'name'     => __( 'Goal markers', 'free-shipping-label' ),
                'hint'     => __( 'One ring, a marker at each goal', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'milestones',
            ],
            'ring_activity'     => [
                'name'     => __( 'Activity rings', 'free-shipping-label' ),
                'hint'     => __( 'A ring per goal', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'stacked',
            ],
            'ring_arcs'         => [
                'name'     => __( 'Goal arcs', 'free-shipping-label' ),
                'hint'     => __( 'The ring split at the goals', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'segments',
            ],
            'ring_next'         => [
                'name'     => __( 'Next goal', 'free-shipping-label' ),
                'hint'     => __( 'The next goal, then the one after', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'next_goal',
            ],
            'ring_checklist'    => [
                'name'     => __( 'Ring + checklist', 'free-shipping-label' ),
                'hint'     => __( 'The next goal and the list', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'checklist',
            ],
            'ring_mini'         => [
                'name'     => __( 'Mini rings', 'free-shipping-label' ),
                'hint'     => __( 'A small ring per goal', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'tiles',
            ],
            'ring_gauge'        => [
                'name'     => __( 'Gauge', 'free-shipping-label' ),
                'hint'     => __( 'A half circle with the goals', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'milestones',
            ],
            'ring_stepper'      => [
                'name'     => __( 'Stepper ring', 'free-shipping-label' ),
                'hint'     => __( 'The goals as steps around the ring', 'free-shipping-label' ),
                'circular' => true,
                'pair'     => 'stepper',
            ],
            'list'              => [
                'name'    => __( 'Vertical list', 'free-shipping-label' ),
                'classic' => true,
            ],
            'list_alt'          => [
                'name'        => __( 'Vertical list (alt)', 'free-shipping-label' ),
                'classic'     => true,
                'replaced_by' => 'list',
            ],
            'horizontal_1'      => [
                'name'        => __( 'Horizontal Labels', 'free-shipping-label' ),
                'classic'     => true,
                'replaced_by' => 'split',
            ],
            'horizontal_2'      => [
                'name'        => __( 'Horizontal Labels with description', 'free-shipping-label' ),
                'classic'     => true,
                'replaced_by' => 'next_goal',
            ],
            'desc_only_above'   => [
                'name'        => __( 'Description Only Above', 'free-shipping-label' ),
                'classic'     => true,
                'replaced_by' => 'headline',
            ],
            'desc_only_beneath' => [
                'name'    => __( 'Description Only Beneath', 'free-shipping-label' ),
                'classic' => true,
            ],
            'threshold_bubbles' => [
                'name'    => __( 'Threshold Bubbles', 'free-shipping-label' ),
                'classic' => true,
            ],
        ];
    }

    /**
     * The layouts the picker shows: a replaced one only where it's the saved layout, with what replaces it.
     *
     * @since	4.0.0
     */
    private static function shown_layouts( $current ) {
        $layouts = self::layouts();
        foreach ( $layouts as $layout => $data ) {
            if ( empty( $data['replaced_by'] ) ) {
                continue;
            }
            if ( $layout !== $current ) {
                unset($layouts[$layout]);
                continue;
            }
            /* translators: %s: the name of the layout that replaces this one. */
            $layouts[$layout]['hint'] = sprintf( __( 'Replaced by %s', 'free-shipping-label' ), $layouts[$data['replaced_by']]['name'] );
        }
        return $layouts;
    }

    /**
     * The saved layout as the store renders it: Free renders the default for a Pro layout
     * (FSL_Bar::get_progress_bar_options()).
     *
     * @since	4.0.0
     */
    private static function current_layout( $pro ) {
        $current = DEVNET_FSL_OPTIONS['progress_bar']['layout'] ?? Defaults::bar( 'layout' );
        return ( $pro || in_array( $current, FSL_Bar::FREE_LAYOUTS, true ) ? $current : Defaults::bar( 'layout' ) );
    }

    /**
     * A known layout, or the default.
     *
     * @since	4.0.0
     */
    public static function sanitize_layout( $value ) {
        return ( is_string( $value ) && array_key_exists( $value, self::layouts() ) ? $value : Defaults::bar( 'layout' ) );
    }

    /**
     * A preview of each layout the picker shows, with these settings over the saved ones, at this progress (the
     * live preview's slider).
     *
     * @since	4.0.0
     */
    public static function layout_previews( $values = [], $percent = self::PREVIEW_PERCENT ) {
        $pro = false;
        $bar = new FSL_Bar(true);
        $previews = [];
        // Threshold indicators are Pro on the storefront; Free previews them on the Pro layouts.
        $pro_indicators = function ( $data, $progress, $opt ) {
            return FSL_Bar::set_threshold_indicators( $progress, $opt );
        };
        foreach ( array_keys( self::shown_layouts( self::current_layout( $pro ) ) ) as $layout ) {
            // The circular layouts are Pro and draw the ring only in Pro (layout_picker() skips them in Free).
            if ( !$pro && in_array( $layout, FSL_Bar::RING_LAYOUTS, true ) ) {
                continue;
            }
            if ( !$pro && !in_array( $layout, FSL_Bar::FREE_LAYOUTS, true ) ) {
                add_filter(
                    'fsl_progress_bar_threshold_data',
                    $pro_indicators,
                    10,
                    3
                );
            }
            $previews[$layout] = self::layout_preview(
                $bar,
                $layout,
                $values,
                $percent
            );
            remove_filter( 'fsl_progress_bar_threshold_data', $pro_indicators );
        }
        return $previews;
    }

    /**
     * Free shipping at 50 and a gift at 90 in this layout, at this progress towards the gift.
     */
    private static function layout_preview(
        $bar,
        $layout,
        $values,
        $percent
    ) {
        // The ring layouts are for the circular bar, the others for the linear one; the Vertical list for both
        // (FSL_Bar::get_progress_bar_options()).
        $type = ( 'list' === $layout ? [] : [
            'bar_type' => ( in_array( $layout, FSL_Bar::RING_LAYOUTS, true ) ? 'circular' : 'linear' ),
        ] );
        return self::goals_preview_html(
            $bar,
            $layout,
            [
                'free-shipping' => [
                    'threshold' => 50,
                    'options'   => $bar->get_progress_bar_options( [
                        'module_name' => 'free-shipping',
                    ] + $type + $values ),
                ],
                'gift-bar'      => [
                    'threshold' => 90,
                    'options'   => self::gift_preview_options( $bar, $values, $type ),
                ],
            ],
            $percent
        );
    }

    /**
     * The Gift Bar's options over these settings, its texts as the storefront fills them
     * (FSL_Gift_Bar::setup_data_for_gift_bar()).
     *
     * @since	4.0.0
     */
    private static function gift_preview_options( $bar, $values, $type = [] ) {
        $gift = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        $gift = FSL_Bar::inherit_icon_settings( ( $gift ? $gift : [] ), Defaults::gift_bar( 'inherit_icon_settings' ) );
        foreach ( [
            'label',
            'title',
            'description',
            'qualified_message'
        ] as $text ) {
            if ( ($gift[$text] ?? '') === '' ) {
                $gift[$text] = Defaults::gift_bar( $text );
            }
        }
        return $bar->get_progress_bar_options( [
            'name'        => 'gift_bar',
            'module_name' => 'gift-bar',
        ] + $type + $gift + $values );
    }

    /**
     * These goals (name => threshold, options) in this layout, at this progress towards the highest one: one goal as
     * the single bar, several as a group, as the storefront sets them up. The group takes the highest goal's options
     * (the gift's, in combined bars).
     *
     * @since	4.0.0
     */
    private static function goals_preview_html(
        $bar,
        $layout,
        $goals,
        $percent
    ) {
        uasort( $goals, fn( $a, $b ) => $a['threshold'] <=> $b['threshold'] );
        $cart = max( array_column( $goals, 'threshold' ) ) * $percent / 100;
        // A goal's data as the storefront sets it up.
        $goal = function ( $opt, $threshold ) use($cart) {
            $pass = $cart >= $threshold;
            return [
                'pass'             => $pass,
                'options'          => $opt,
                'percent'          => ( $pass ? 100 : $cart / $threshold * 100 ),
                'reached'          => ( $pass ? [
                    'qualified_message' => $opt['qualified_message'],
                ] : [] ),
                'threshold'        => $threshold,
                'placeholder_args' => [
                    'remaining' => max( 0, $threshold - $cart ),
                    'threshold' => $threshold,
                ],
            ];
        };
        if ( 1 === count( $goals ) ) {
            $only = reset( $goals );
            $opt = $only['options'];
            return $bar->fsl_progress_bar_html( $goal( $opt, $only['threshold'] ) );
        }
        $grouped = [];
        foreach ( $goals as $name => $data ) {
            $grouped[$name] = $goal( $data['options'], $data['threshold'] );
        }
        return $bar->fsl_progress_bar_html( [
            'layout'          => $layout,
            'options'         => end( $goals )['options'],
            'percent'         => $percent,
            'grouped_modules' => $grouped,
        ] );
    }

    /**
     * The goals the live preview can show: free shipping at its amount (in the zone new visitors start in, else the
     * first zone with one; 50 without), the Gift Bar and FSL Discount's bars when they're on, and other add-ons'
     * (fsl_preview_goals: name => label, threshold, options). The options are the bar's over these settings.
     *
     * @since	4.0.0
     */
    public static function preview_goals( $bar, $values = [] ) {
        require_once __DIR__ . '/fsl-threshold-status.php';
        $zones = Threshold_Status::get_status()['zones'];
        $start = array_filter( $zones, fn( $zone ) => $zone['start'] && $zone['amount'] );
        $any = array_filter( array_column( $zones, 'amount' ) );
        $fs = $bar->get_progress_bar_options( [
            'module_name' => 'free-shipping',
        ] + $values );
        $goals = [
            'free-shipping' => [
                'label'     => $fs['label'],
                'threshold' => (float) (( $start ? reset( $start )['amount'] : (( $any ? reset( $any ) : 50 )) )),
                'options'   => $fs,
            ],
        ];
        $gift = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        if ( Gift_Bar::module_active() && !empty( $gift['enable_bar'] ) && is_numeric( $gift['threshold'] ?? '' ) && (float) $gift['threshold'] > 0 ) {
            $gift_opt = self::gift_preview_options( $bar, $values );
            $goals['gift-bar'] = [
                'label'     => $gift_opt['label'],
                'threshold' => (float) $gift['threshold'],
                'options'   => $gift_opt,
            ];
        }
        // FSL Discount's bars that are on, from its settings (its code isn't called): their texts, the empty ones as the
        // add-on fills them, and their icons. Not their saved bar type, an old hidden field.
        if ( defined( 'DEVNET_FSL_DISCOUNT_VERSION' ) ) {
            $defaults = [
                'label'             => __( 'Discount', 'free-shipping-label' ),
                'title'             => __( 'Discount on orders over {threshold}', 'free-shipping-label' ),
                'description'       => __( 'Add at least {remaining} more to get a discount!', 'free-shipping-label' ),
                'qualified_message' => __( "You've earned a discount!", 'free-shipping-label' ),
            ];
            foreach ( ['', '_2', '_3'] as $i => $suffix ) {
                $discount = get_option( 'devnet_fsl_discount_bar' . $suffix );
                if ( !is_array( $discount ) || empty( $discount['enable_bar'] ) || !is_numeric( $discount['threshold'] ?? '' ) || (float) $discount['threshold'] <= 0 ) {
                    continue;
                }
                $texts = [];
                foreach ( $defaults as $text => $default ) {
                    $texts[$text] = ( ($discount[$text] ?? '') === '' ? $default : $discount[$text] );
                }
                $name = 'discount-' . ($i + 1);
                $opt = $bar->get_progress_bar_options( [
                    'module_name' => $name,
                    'icon'        => ( empty( $discount['icon'] ) ? 'discount' : $discount['icon'] ),
                ] + array_intersect_key( $discount, array_flip( ['show_title', 'show_description', 'show_qualified_message'] ) ) + $texts + $values );
                $goals[$name] = [
                    'label'     => $opt['label'],
                    'threshold' => (float) $discount['threshold'],
                    'options'   => $opt,
                ];
            }
        }
        return apply_filters(
            'fsl_preview_goals',
            $goals,
            $bar,
            $values
        );
    }

    /**
     * The plugin's default design: the settings the presets write (their values and circular values, the accent
     * color), as a new install has them.
     *
     * @since	4.0.0
     */
    private static function default_design() {
        $keys = array_merge( ['bar_accent_color'], ...array_map( 'array_keys', array_merge( array_column( Presets::all(), 'values' ), array_column( Presets::all(), 'circular' ) ) ) );
        return array_intersect_key( Defaults::bar(), array_flip( $keys ) );
    }

    /**
     * "Customize design": folds the design settings after it (fsl-admin.js). With a preset the preset is the
     * look, so they start folded; a custom design, a preset without a look for the bar type and Free start open. A
     * visitor's own choice is remembered in their browser.
     *
     * @since	4.0.0
     */
    public static function design_toggle() {
        $open = true;
        printf(
            '<button type="button" class="button fsl-design-toggle" aria-expanded="%1$s" data-show="%2$s" data-hide="%3$s">%4$s</button>',
            ( $open ? 'true' : 'false' ),
            esc_attr__( 'Show design options', 'free-shipping-label' ),
            esc_attr__( 'Hide design options', 'free-shipping-label' ),
            ( $open ? esc_html__( 'Hide design options', 'free-shipping-label' ) : esc_html__( 'Show design options', 'free-shipping-label' ) )
        );
    }

    /**
     * Live preview at the top of the Progress Bar form: the saved design, then the form's values on every change
     * (fsl-admin.js, FSL_Admin::bar_preview()).
     *
     * @since	4.0.0
     */
    public static function live_preview() {
        $goals = self::preview_goals( new FSL_Bar(true) );
        $buttons = '';
        // Several goals: a button each, to show or hide it in the preview (fsl-admin.js).
        if ( count( $goals ) > 1 ) {
            foreach ( $goals as $name => $goal ) {
                $buttons .= sprintf(
                    '<button type="button" class="fsl-live-preview__goal" data-goal="%1$s" aria-pressed="true">%2$s <span>%3$s</span></button>',
                    esc_attr( $name ),
                    esc_html( $goal['label'] ),
                    wp_kses_post( wc_price( $goal['threshold'] ) )
                );
            }
            $buttons = '<div class="fsl-live-preview__goals" role="group" aria-label="' . esc_attr__( 'Goals in the preview', 'free-shipping-label' ) . '">' . $buttons . '</div>';
        }
        printf(
            '<div class="fsl-live-preview" data-nonce="%1$s"><p class="fsl-live-preview__title">%2$s</p>%7$s<div class="fsl-live-preview__bar" aria-hidden="true">%3$s</div><label class="fsl-live-preview__cart"><span>%4$s</span> <input type="range" min="0" max="100" step="1" value="%5$d" /> <output>%6$s</output></label></div>',
            esc_attr( wp_create_nonce( 'devnet_fsl_bar_preview' ) ),
            esc_html__( 'Preview', 'free-shipping-label' ),
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is fully escaped inside fsl_progress_bar_html().
            self::bar_preview_html(),
            esc_html__( 'Progress', 'free-shipping-label' ),
            (int) self::PREVIEW_PERCENT,
            esc_html( self::PREVIEW_PERCENT . '%' ),
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped as it's built above.
            $buttons
        );
    }

    /**
     * The Progress Bar with these settings over the saved ones, as the storefront renders it, with these goals of
     * preview_goals() (all of them by default), at this progress towards the highest one.
     *
     * @since	4.0.0
     */
    public static function bar_preview_html( $values = [], $percent = self::PREVIEW_PERCENT, $shown = [] ) {
        $bar = new FSL_Bar(true);
        $goals = self::preview_goals( $bar, $values );
        $some = array_intersect_key( $goals, array_flip( $shown ) );
        return self::goals_preview_html(
            $bar,
            $goals['free-shipping']['options']['layout'],
            ( $some ? $some : $goals ),
            $percent
        );
    }

    /**
     * One goal at 50, at this progress.
     */
    private static function render_preview( $bar, $opt, $percent = self::PREVIEW_PERCENT ) {
        $pass = $percent >= 100;
        return $bar->fsl_progress_bar_html( [
            'options'          => $opt,
            'pass'             => $pass,
            'percent'          => $percent,
            'placeholder_args' => [
                'remaining' => max( 0, 50 - 50 * $percent / 100 ),
                'threshold' => 50,
            ],
        ] );
    }

    /**
     * Whether the Cart or Checkout page uses blocks; for the Mini-Cart, whether the theme is a block theme.
     *
     * @since	4.0.0
     */
    private static function uses_blocks( $page ) {
        static $page_types = null;
        if ( 'minicart' === $page ) {
            return wp_is_block_theme();
        }
        $page_types ??= Helper::detect_wc_cart_checkout_type();
        return 'block' === ($page_types[$page] ?? '');
    }

    /**
     * Block-based Cart, Checkout or Mini-Cart: its position select gets the block positions on top and the
     * classic ones below (third-party carts stay last). The block group is disabled in Free and without a
     * valid license, like the other Pro positions. Classic pages keep the plain list.
     *
     * @since	4.0.0
     */
    private static function group_block_positions( $fields, $block_disabled ) {
        $pages = [
            'cart_position'     => 'cart',
            'checkout_position' => 'checkout',
            'minicart_position' => 'minicart',
        ];
        $labels = [
            'cart'     => [esc_html__( 'CART BLOCK', 'free-shipping-label' ), esc_html__( 'CLASSIC CART', 'free-shipping-label' )],
            'checkout' => [esc_html__( 'CHECKOUT BLOCK', 'free-shipping-label' ), esc_html__( 'CLASSIC CHECKOUT', 'free-shipping-label' )],
            'minicart' => [esc_html__( 'MINI-CART BLOCK', 'free-shipping-label' ), esc_html__( 'CLASSIC MINI-CART', 'free-shipping-label' )],
        ];
        foreach ( $fields as $i => $field ) {
            $page = $pages[$field['name'] ?? ''] ?? '';
            if ( !$page || !self::uses_blocks( $page ) ) {
                continue;
            }
            $block_options = [];
            foreach ( Placement::positions( $page ) as $value => $position ) {
                $block_options[$value] = esc_html( $position[2] );
            }
            $classic_options = $field['options'];
            unset($classic_options['']);
            $fields[$i]['options'] = [
                '' => $field['options'][''],
            ];
            $fields[$i]['optgroup'] = array_merge( [[
                'label'    => $labels[$page][0],
                'options'  => $block_options,
                'disabled' => $block_disabled,
            ], [
                'label'   => $labels[$page][1],
                'options' => $classic_options,
            ]], $field['optgroup'] ?? [] );
        }
        return $fields;
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
                    'top-left'     => esc_html__( 'Top left', 'free-shipping-label' ),
                    'top-right'    => esc_html__( 'Top right', 'free-shipping-label' ),
                    'bottom-left'  => esc_html__( 'Bottom left', 'free-shipping-label' ),
                    'bottom-right' => esc_html__( 'Bottom right', 'free-shipping-label' ),
                ],
                'default' => Defaults::notice_bar( 'position' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_y__disabled',
                'label'             => esc_html__( 'Vertical margin', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Distance from the top or bottom edge', 'free-shipping-label' ),
                'default'           => Defaults::notice_bar( 'margin_y' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_x__disabled',
                'label'             => esc_html__( 'Horizontal margin', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Distance from the side', 'free-shipping-label' ),
                'default'           => Defaults::notice_bar( 'margin_x' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'autohide__disabled',
                'label'   => esc_html__( 'Hide automatically', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Hides 5 seconds after a product is added to the cart.', 'free-shipping-label' ),
                'default' => Defaults::notice_bar( 'autohide' ),
            ]
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
                    'after'       => esc_html__( 'After the previous goal is reached', 'free-shipping-label' ),
                    '_disabled_1' => esc_html__( 'In the free shipping bar', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Gift Bar only', 'free-shipping-label' ),
                    '_disabled_3' => esc_html__( 'As a separate bar', 'free-shipping-label' ),
                ],
                'desc'    => esc_html__( 'After the previous goal: once free shipping is reached, or right away when the gift comes first or there\'s no free shipping.', 'free-shipping-label' ),
                'default' => Defaults::gift_bar( 'display' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'threshold',
                'label'             => esc_html__( 'Threshold', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Cart amount that unlocks the gift', 'free-shipping-label' ),
                'step'              => 0.01,
                'min'               => 0,
                'default'           => Defaults::gift_bar( 'threshold' ),
                'sanitize_callback' => [self::class, 'sanitize_amount'],
            ],
            [
                'type'     => 'select2',
                'name'     => 'gift_product__disabled',
                'label'    => esc_html__( 'Gift product', 'free-shipping-label' ),
                'options'  => [],
                'multiple' => false,
                'default'  => Defaults::gift_bar( 'gift_product' ),
            ],
            ...self::variable_gift_notice(),
            [
                'type'    => 'select',
                'name'    => 'after_threshold',
                'label'   => esc_html__( 'When the gift is unlocked', 'free-shipping-label' ),
                'options' => [
                    ''            => esc_html__( 'Do nothing', 'free-shipping-label' ),
                    '_disabled_1' => esc_html__( 'Add it to the cart for free', 'free-shipping-label' ),
                    '_disabled_2' => esc_html__( 'Note it on the order', 'free-shipping-label' ),
                ],
                'default' => Defaults::gift_bar( 'after_threshold' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'label__disabled',
                'label'             => esc_html__( 'Label', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Leave empty to use the default text, translated for each language.', 'free-shipping-label' ),
                'default'           => '',
                'placeholder'       => Defaults::gift_bar( 'label' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'select',
                'name'    => 'price_display',
                'label'   => esc_html__( 'Price display', 'free-shipping-label' ),
                'options' => [
                    'label'   => esc_html__( 'Label only', 'free-shipping-label' ),
                    'zero'    => esc_html__( '0.00', 'free-shipping-label' ),
                    'crossed' => esc_html__( 'Crossed-out price and 0.00', 'free-shipping-label' ),
                ],
                'desc'    => esc_html__( 'How the gift\'s price shows in the cart and checkout.', 'free-shipping-label' ),
                'default' => Defaults::gift_bar( 'price_display' ),
            ],
            self::common_options( 'info-bar-text' ),
            self::common_options( 'info-bar-placeholders', false, 'gift_bar' ),
            self::common_options( 'title', false, 'gift_bar' ),
            self::common_options( 'description', false, 'gift_bar' ),
            self::common_options( 'qualified_message', false, 'gift_bar' ),
            [
                'type'  => 'info',
                'name'  => 'info-bar-icon',
                'label' => esc_html__( 'Icons', 'free-shipping-label' ),
                'class' => 'info',
                'desc'  => '',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'inherit_icon_settings__disabled',
                'label'   => esc_html__( 'Same as Progress Bar', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Use the Progress Bar\'s icon style. The gift keeps its own icon.', 'free-shipping-label' ),
                'default' => Defaults::gift_bar( 'inherit_icon_settings' ),
            ],
            self::common_options( 'indicator_icon', true ),
            self::common_options( 'indicator_icon_size', true ),
            self::common_options( 'indicator_icon_shape', true ),
            self::common_options( 'icon', true, 'gift_bar' ),
            self::common_options( 'icon_color', true ),
            self::common_options( 'indicator_icon_bg_color', true )
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
                'desc'     => esc_html__( 'These products and categories never show the label.', 'free-shipping-label' ),
                'options'  => [],
                'multiple' => true,
                'default'  => Defaults::label( 'exclude' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'include_shipping_class__disabled',
                'label'   => esc_html__( 'Always show for shipping class', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Products in this class show the label whatever their price.', 'free-shipping-label' ),
                'options' => self::shipping_classes_option_list(),
                'default' => Defaults::label( 'include_shipping_class' ),
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
                'desc'    => esc_html__( 'Shows when the cheapest variation qualifies.', 'free-shipping-label' ),
                'default' => Defaults::label( 'show_on_single_variable_product' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'show_on_single_variation',
                'label'   => esc_html__( 'Enable for the selected variation', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Shows once the customer picks a qualifying variation.', 'free-shipping-label' ),
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
                'desc'    => esc_html__( 'Shows when the cheapest variation qualifies.', 'free-shipping-label' ),
                'default' => Defaults::label( 'show_on_list_variable_products' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'label_over_image__disabled',
                'label'   => esc_html__( 'Label over image', 'free-shipping-label' ),
                'default' => Defaults::label( 'label_over_image' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'position__disabled',
                'label'   => esc_html__( 'Position', 'free-shipping-label' ),
                'options' => [
                    'top-left'     => esc_html__( 'Top left', 'free-shipping-label' ),
                    'top-right'    => esc_html__( 'Top right', 'free-shipping-label' ),
                    'bottom-left'  => esc_html__( 'Bottom left', 'free-shipping-label' ),
                    'bottom-right' => esc_html__( 'Bottom right', 'free-shipping-label' ),
                ],
                'desc'    => sprintf( '%s %s', esc_html__( 'Top positions suit most themes; bottom ones can sit close to the price or button.', 'free-shipping-label' ), sprintf( 
                    /* translators: 1: <a> to the documentation, 2: </a> */
                    esc_html__( 'Refer to the %1$sdocumentation%2$s for more information.', 'free-shipping-label' ),
                    '<a href="https://devnet.hr/docs/free-shipping-label/product-label/#label-over-image" target="_blank">',
                    '</a>'
                 ) ),
                'default' => Defaults::label( 'position' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_y__disabled',
                'label'             => esc_html__( 'Vertical margin', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Distance from the top or bottom edge', 'free-shipping-label' ),
                'default'           => Defaults::label( 'margin_y' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            [
                'type'              => 'text',
                'name'              => 'margin_x__disabled',
                'label'             => esc_html__( 'Horizontal margin', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Distance from the side', 'free-shipping-label' ),
                'default'           => Defaults::label( 'margin_x' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            [
                'type'  => 'info',
                'name'  => 'info-label-text',
                'label' => esc_html__( 'Text label', 'free-shipping-label' ),
                'class' => 'info',
            ],
            [
                'type'              => 'html',
                'name'              => 'label_preset',
                'label'             => esc_html__( 'Design preset', 'free-shipping-label' ),
                'callback'          => [self::class, 'label_preset_picker'],
                'default'           => Defaults::label( 'label_preset' ),
                'sanitize_callback' => [Presets::class, 'sanitize_label'],
            ],
            [
                'type'              => 'text',
                'name'              => 'text',
                'label'             => esc_html__( 'Text', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Leave empty to use the default text, translated for each language.', 'free-shipping-label' ),
                'placeholder'       => Defaults::label( 'text' ),
                'max'               => 25,
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'radio_image',
                'name'              => 'label_icon__disabled',
                'label'             => esc_html__( 'Icon', 'free-shipping-label' ),
                'desc'              => esc_html__( 'Shown before the text.', 'free-shipping-label' ),
                'options'           => [
                    '' => self::NO_ICON,
                ] + Icons::get_all(),
                'default'           => Defaults::label( 'label_icon' ),
                'sanitize_callback' => [self::class, 'sanitize_icon'],
            ],
            [
                'type'              => 'color',
                'name'              => 'text_color',
                'label'             => esc_html__( 'Text color', 'free-shipping-label' ),
                'default'           => Defaults::label( 'text_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            [
                'type'              => 'color',
                'name'              => 'bg_color',
                'label'             => esc_html__( 'Background color', 'free-shipping-label' ),
                'default'           => Defaults::label( 'bg_color' ),
                'sanitize_callback' => [self::class, 'sanitize_css_value'],
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'hide_border_shadow',
                'label'   => esc_html__( 'Hide border and shadow', 'free-shipping-label' ),
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
                'desc'    => esc_html__( 'Shows the image instead of the text label.', 'free-shipping-label' ),
                'default' => Defaults::label( 'enable_image_label' ),
            ],
            [
                'type'    => 'file',
                'name'    => 'image__disabled',
                'label'   => esc_html__( 'Image', 'free-shipping-label' ),
                'desc'    => esc_html__( 'Choose from the media library or paste an image URL.', 'free-shipping-label' ),
                'options' => [
                    'button_label' => esc_html__( 'Choose image', 'free-shipping-label' ),
                ],
                'default' => Defaults::label( 'image' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'image_width__disabled',
                'label'             => esc_html__( 'Width', 'free-shipping-label' ),
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

    /**
     * Product Label preset picker: a card per preset with the label on a product as the shop shows it, and filters by
     * look. Free chooses the free presets and sees the others. Choosing one writes its colors into the color fields
     * (fsl-admin.js).
     *
     * @since	4.0.0
     */
    public static function label_preset_picker() {
        $pro = false;
        $saved = (DEVNET_FSL_OPTIONS['label'] ?? []) + Defaults::label();
        $current = Presets::sanitize_label( $saved['label_preset'] );
        $presets = Presets::labels();
        $filters = [
            'all'      => __( 'All', 'free-shipping-label' ),
            'simple'   => __( 'Simple', 'free-shipping-label' ),
            'colorful' => __( 'Colorful', 'free-shipping-label' ),
            'shapes'   => __( 'Shapes', 'free-shipping-label' ),
            'animated' => __( 'Animated', 'free-shipping-label' ),
            'images'   => __( 'Over images', 'free-shipping-label' ),
        ];
        $counts = [
            'all' => count( $presets ),
        ];
        $cards = '';
        $current_preview = '';
        if ( !$pro ) {
            $filters['free'] = __( 'Free', 'free-shipping-label' );
        }
        foreach ( $presets as $slug => $preset ) {
            $tags = ( $preset['free'] ? array_merge( $preset['tags'], ['free'] ) : $preset['tags'] );
            foreach ( $tags as $tag ) {
                $counts[$tag] = ($counts[$tag] ?? 0) + 1;
            }
            // Classic and the saved preset show the saved colors, the others their own.
            $preview = self::label_preview( (( $slug === $current ? [] : $preset['values'] )) + $saved, $slug );
            $locked = !$pro && !$preset['free'];
            if ( $slug === $current ) {
                $current_preview = $preview;
            }
            $cards .= sprintf(
                '<div class="fsl-label-card" data-tags="%1$s"><input type="radio" id="fsl-label-preset-%2$s" name="devnet_fsl_label[label_preset]" value="%3$s" data-values="%4$s"%5$s%6$s /><label for="fsl-label-preset-%2$s">%7$s%8$s%9$s</label><div class="fsl-label-card__preview" aria-hidden="true">%10$s</div></div>',
                esc_attr( implode( ' ', $tags ) ),
                esc_attr( ( $slug ? $slug : 'classic' ) ),
                esc_attr( $slug ),
                esc_attr( wp_json_encode( $preset['values'] ) ),
                checked( $current, $slug, false ),
                disabled( $locked, true, false ),
                esc_html( $preset['name'] ),
                ( $locked ? ' <span class="fsl-layout-card__pro">PRO</span>' : '' ),
                ( empty( $preset['hint'] ) ? '' : '<span class="fsl-preset-card__hint">' . esc_html( $preset['hint'] ) . '</span>' ),
                $preview
            );
        }
        self::picker_summary(
            'fsl-label-gallery',
            $presets[$current]['name'],
            $current_preview,
            'fsl-label-card__preview',
            __( 'Change preset', 'free-shipping-label' )
        );
        echo '<fieldset id="fsl-label-gallery" class="fsl-label-gallery" hidden><legend class="screen-reader-text">' . esc_html__( 'Design preset', 'free-shipping-label' ) . '</legend>';
        echo '<div class="fsl-label-filters" role="group" aria-label="' . esc_attr__( 'Filter presets', 'free-shipping-label' ) . '">';
        foreach ( $filters as $filter => $name ) {
            printf(
                '<button type="button" class="fsl-label-filter" data-filter="%1$s" aria-pressed="%2$s">%3$s <span class="fsl-label-filter__count">%4$d</span></button>',
                esc_attr( $filter ),
                ( 'all' === $filter ? 'true' : 'false' ),
                esc_html( $name ),
                (int) ($counts[$filter] ?? 0)
            );
        }
        echo '</div>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped as it's built above.
        echo $cards;
        echo '</fieldset>';
        echo '<div class="fsl-label-preset-status" aria-live="polite"><p hidden>' . esc_html__( 'Preset applied. Save to keep it.', 'free-shipping-label' ) . ' <button type="button" class="button-link fsl-label-preset-undo">' . esc_html__( 'Undo', 'free-shipping-label' ) . '</button></p></div>';
        echo '<p class="description">' . esc_html__( 'A preset sets the text and background colors below; change them as you like.', 'free-shipping-label' );
        if ( !$pro ) {
            echo ' ' . wp_kses_post( sprintf( 
                /* translators: 1: <a> to Pro, 2: </a> */
                __( 'Presets marked PRO are Pro. %1$sUpgrade →%2$s', 'free-shipping-label' ),
                '<a href="' . esc_url( 'https://devnet.hr/plugins/free-shipping-label/' ) . '" target="_blank" rel="noopener noreferrer">',
                '</a>'
             ) );
        }
        echo '</p>';
    }

    /**
     * The label on a product card, as the shop shows it: after the price, or over the image (Pro).
     *
     * @since	4.0.0
     */
    private static function label_preview( $opt, $preset ) {
        $label = FSL_Label::text_label_html( $opt, $preset );
        $over = false;
        if ( $over ) {
            $label = '<div class="devnet_fsl-label-over-image ' . esc_attr( $opt['position'] ) . '">' . $label . '</div>';
        }
        return sprintf(
            '<div class="fsl-label-product">%1$s<span class="fsl-label-product__image"></span><span class="fsl-label-product__title">%2$s</span><span class="price">%3$s%4$s</span></div>',
            ( $over ? $label : '' ),
            esc_html__( 'Product name', 'free-shipping-label' ),
            wc_price( 59 ),
            ( $over ? '' : $label )
        );
    }

    /**
     * The ring's thickness in percent of the circle size, 2 to 30 (the default when not a number).
     *
     * @since	4.0.0
     */
    public static function sanitize_ring_thickness( $value ) {
        return ( is_numeric( $value ) ? max( 2, min( 30, round( (float) $value, 1 ) ) ) : Defaults::bar( 'ring_thickness' ) );
    }

    /**
     * One of the plugin's icons, or '' (no icon).
     *
     * @since	4.0.0
     */
    public static function sanitize_icon( $value ) {
        return ( is_string( $value ) && array_key_exists( $value, Icons::get_all() ) ? $value : '' );
    }

    /**
     * Amounts (thresholds): '' or a number, a decimal comma becomes a dot. Anything else is saved as '' (not set),
     * so it can't reach the threshold arithmetic. Decimals and 0 stay as typed. Arrays: per-method thresholds.
     *
     * @since    4.0.0
     */
    public static function sanitize_amount( $value ) {
        if ( is_array( $value ) ) {
            return array_map( [self::class, 'sanitize_amount'], $value );
        }
        if ( !is_scalar( $value ) ) {
            return '';
        }
        $value = str_replace( ',', '.', trim( (string) $value ) );
        return ( is_numeric( $value ) ? $value : '' );
    }

    /**
     * Values printed into inline styles (colors, margins): hex, rgb()/hsl(), color names, lengths, calc() and
     * !important pass; `;`, `:`, braces, quotes and url() don't, so a value can't add another CSS declaration.
     *
     * @since    4.0.0
     */
    public static function sanitize_css_value( $value ) {
        $value = trim( sanitize_text_field( (string) $value ) );
        if ( !preg_match( '/^[#a-zA-Z0-9.,%()\\/\\s+*!-]*$/', $value ) || preg_match( '/(url|expression)\\s*\\(/i', $value ) ) {
            return '';
        }
        return $value;
    }

    /**
     * Notice under "Gift product" when the saved gift is a variable product (allowed, but a variation or a
     * simple product decides exactly what gets added).
     *
     * @since    4.0.0
     */
    private static function variable_gift_notice() {
        $gift_product = Gift_Bar::get_gift_product( 'product' );
        if ( !$gift_product || !$gift_product->is_type( 'variable' ) ) {
            return [];
        }
        $notice = sprintf( 
            /* translators: %s: gift product name */
            esc_html__( '%s is a variable product: the cart gets its default variation, or the first one in stock. Select a variation to choose which one.', 'free-shipping-label' ),
            '<strong>' . esc_html( $gift_product->get_name() ) . '</strong>'
         );
        return [[
            'type'  => 'info',
            'name'  => 'info-gift-variable',
            'label' => '',
            'desc'  => '<div class="devnet-plugin-option-notice--warning">' . $notice . '</div>',
        ]];
    }

    private static function shipping_zones_option_list() {
        $zones = \WC_Shipping_Zones::get_zones();
        $options = [
            '' => esc_html__( '— None —', 'free-shipping-label' ),
        ];
        foreach ( $zones as $key => $zone ) {
            $id = ( isset( $zone['zone_id'] ) ? $zone['zone_id'] : null );
            $name = ( isset( $zone['zone_name'] ) ? $zone['zone_name'] : null );
            if ( $id && $name ) {
                $options[$id] = $name;
            }
        }
        // get_zones() leaves out zone 0, "Locations not covered by your other zones".
        $options[0] = ( new \WC_Shipping_Zone(0) )->get_zone_name();
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
                    'sanitize_callback' => [self::class, 'sanitize_amount'],
                ];
            }
        }
        return $fields;
    }

    private static function shipping_classes_option_list() {
        $shipping_classes = get_terms( [
            'taxonomy'   => 'product_shipping_class',
            'hide_empty' => false,
        ] );
        if ( is_wp_error( $shipping_classes ) ) {
            $shipping_classes = [];
        }
        $options = [
            '' => esc_html__( '— None —', 'free-shipping-label' ),
        ];
        if ( !empty( $shipping_classes ) && !is_wp_error( $shipping_classes ) ) {
            foreach ( $shipping_classes as $key => $class ) {
                $options[esc_attr( $class->term_id )] = esc_html( $class->name );
            }
        }
        return $options;
    }

    private static function progress_bar_position_notice() {
        $is_pro_active = false;
        $link = '<a href="%s" target="_blank" rel="noopener noreferrer">';
        $page_types = Helper::detect_wc_cart_checkout_type();
        $cart = 'block' === ($page_types['cart'] ?? '');
        $checkout = 'block' === ($page_types['checkout'] ?? '');
        // Free, and Pro without a valid license, on block pages: only this warning. Licensed Pro gets a line
        // under each select instead.
        if ( !$is_pro_active && ($cart || $checkout) ) {
            $upgrade = sprintf( $link, esc_url( 'https://devnet.hr/plugins/free-shipping-label/' ) );
            if ( $cart && $checkout ) {
                $message = sprintf( 
                    /* translators: 1: <a> to Pro, 2: </a> */
                    __( 'Your Cart and Checkout use blocks: block positions are Pro. The free version works with the classic Cart and Checkout, so you can switch to those. %1$sUpgrade →%2$s', 'free-shipping-label' ),
                    $upgrade,
                    '</a>'
                 );
            } else {
                $message = sprintf(
                    /* translators: 1: "Cart" or "Checkout", 2: <a> to Pro, 3: </a> */
                    __( 'Your %1$s uses blocks: block positions are Pro. The free version works with the classic %1$s, so you can switch to it. %2$sUpgrade →%3$s', 'free-shipping-label' ),
                    ( $cart ? __( 'Cart', 'free-shipping-label' ) : __( 'Checkout', 'free-shipping-label' ) ),
                    $upgrade,
                    '</a>'
                );
            }
            return '<div class="devnet-plugin-option-notice--warning"><p>' . $message . '</p></div>';
        }
        return '<div class="devnet-plugin-option-notice--info"><p>' . sprintf( 
            /* translators: 1: <a> to the documentation, 2: </a> */
            __( 'Some themes leave out these spots. Not showing? %1$sSee the docs →%2$s', 'free-shipping-label' ),
            sprintf( $link, esc_url( 'https://devnet.hr/docs/free-shipping-label/progress-bar/' ) ),
            '</a>'
         ) . '</p></div>';
    }

}
