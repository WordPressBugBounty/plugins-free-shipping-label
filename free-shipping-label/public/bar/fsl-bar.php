<?php

namespace Devnet\FSL\Frontend\Bar;

use Devnet\FSL\Includes\Defaults;
use Devnet\FSL\Includes\Helper;
use Devnet\FSL\Includes\Icons;
use Devnet\FSL\Includes\Presets;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class FSL_Bar {
    /**
     * Multiple goals layouts in the Free version; the others are Pro.
     *
     * @since	4.0.0
     */
    const FREE_LAYOUTS = [
        'list',
        'list_alt',
        'checklist',
        'timeline'
    ];

    /**
     * Multiple goals layouts built from each goal's label and status (goals_layout_html()). Linear bar only.
     *
     * @since	4.0.0
     */
    const GOAL_LAYOUTS = [
        'checklist',
        'timeline',
        'milestones',
        'stepper',
        'segments',
        'next_goal',
        'split',
        'headline',
        'tiles',
        'stacked',
        'spotlight',
        'rewards_count'
    ];

    /**
     * Multiple goals layouts for the circular bar, on its ring (ring_layout_html__premium_only()). Pro.
     *
     * @since	4.0.0
     */
    const RING_LAYOUTS = [
        'ring_markers',
        'ring_activity',
        'ring_arcs',
        'ring_next',
        'ring_checklist',
        'ring_mini',
        'ring_gauge',
        'ring_stepper'
    ];

    /**
     * The settings for the look of a bar's threshold indicator icons, not the icon itself.
     *
     * @since	4.0.0
     */
    const ICON_LOOK = [
        'indicator_icon',
        'indicator_icon_size',
        'indicator_icon_shape',
        'indicator_icon_bg_color',
        'icon_color'
    ];

    /**
     * The old Multilingual setting (until 3.5.x). Not used by FSL since 4.0.0; FSL Discount 1.0.8 and older read it.
     */
    public $is_multilingual = false;

    public function __construct( $skip_hooks = false ) {
        $this->is_multilingual = DEVNET_FSL_OPTIONS['general']['multilingual'] ?? false;
        if ( $skip_hooks ) {
            return;
        }
        $options = DEVNET_FSL_OPTIONS['progress_bar'] ?? [];
        $enable = $options['enable_bar'] ?? false;
        $cart_hook = $options['cart_position'] ?? Defaults::bar( 'cart_position' );
        $checkout_hook = $options['checkout_position'] ?? Defaults::bar( 'checkout_position' );
        $minicart_hook = $options['minicart_position'] ?? Defaults::bar( 'minicart_position' );
        $product_hook = '';
        if ( !empty( $cart_hook ) ) {
            $cart_hook = 'woocommerce_proceed_to_checkout';
        }
        if ( !empty( $checkout_hook ) ) {
            $checkout_hook = 'woocommerce_review_order_before_submit';
        }
        if ( !empty( $minicart_hook ) ) {
            $minicart_hook = 'woocommerce_widget_shopping_cart_before_buttons';
        }
        if ( !$enable ) {
            return;
        }
        if ( !empty( $cart_hook ) ) {
            add_action( $cart_hook, [$this, 'fsl_bar_cart'], 10 );
        }
        if ( !empty( $minicart_hook ) ) {
            add_action( $minicart_hook, [$this, 'fsl_bar_minicart'] );
        }
        if ( !empty( $checkout_hook ) ) {
            add_action( $checkout_hook, [$this, 'fsl_bar_checkout'], 10 );
        }
    }

    /**
     * Output Progress Bar on the checkout page.
     *
     * @since    2.4.0
     */
    public function fsl_bar_checkout() {
        $is_updatable = false;
        $this->progress_bar( [
            'updatable' => $is_updatable,
        ] );
    }

    /**
     * Output Progress Bar on the cart page.
     *
     * @since    2.4.0
     */
    public function fsl_bar_cart() {
        $is_updatable = false;
        $in_table = false;
        $this->progress_bar( [
            'updatable' => $is_updatable,
            'in_table'  => $in_table,
        ] );
    }

    /**
     * Output Progress Bar on the mini-cart widget.
     *
     * @since    2.4.0
     */
    public function fsl_bar_minicart() {
        $this->progress_bar();
    }

    /**
     * Output Progress Bar on the product page.
     *
     * @since    3.5.0
     */
    public function fsl_bar_product() {
        if ( function_exists( 'is_product' ) && is_product() ) {
            $this->progress_bar( [
                'updatable' => true,
            ] );
        }
    }

    public function get_progress_bar_options( $args = [] ) {
        $options = (DEVNET_FSL_OPTIONS['progress_bar'] ?? []) + Defaults::bar();
        $opt = [];
        foreach ( $options as $name => $value ) {
            $opt[$name] = ( isset( $value ) ? $value : Defaults::bar( $name ) );
        }
        $opt['name'] = 'progress_bar';
        $opt['show_fsl_title'] = true;
        $opt['show_fsl_description'] = true;
        $bar_type = $opt['bar_type'] ?? Defaults::bar( 'bar_type' );
        if ( $bar_type === 'circular' ) {
            if ( $opt['inside_circle'] === 'title' ) {
                $opt['show_fsl_title'] = false;
            }
            if ( $opt['inside_circle'] === 'description' ) {
                $opt['show_fsl_description'] = false;
            }
        }
        // Override progress bar options if $args passed.
        if ( !empty( $args ) ) {
            foreach ( $args as $name => $value ) {
                $opt[$name] = $value;
            }
        }
        // Bars with their own texts (Gift Bar, Discount bars) have their own switches: not inherited, shown when missing.
        if ( array_key_exists( 'title', $args ) ) {
            $opt['show_title'] = $args['show_title'] ?? 1;
            $opt['show_description'] = $args['show_description'] ?? 1;
        }
        // A switched-off title or description stays hidden in every layout.
        if ( empty( $opt['show_title'] ) ) {
            $opt['show_fsl_title'] = false;
        }
        if ( empty( $opt['show_description'] ) ) {
            $opt['show_fsl_description'] = false;
        }
        // An empty text shows the translated default. Bars with their own texts (Gift Bar, Discount bars) fill in
        // their own defaults; the live preview passes the Progress Bar's texts.
        if ( !array_key_exists( 'title', $args ) || ($args['module_name'] ?? '') === 'free-shipping' ) {
            foreach ( [
                'label',
                'title',
                'description',
                'qualified_message',
                'close_message'
            ] as $text ) {
                if ( ($opt[$text] ?? '') === '' ) {
                    $opt[$text] = Defaults::bar( $text );
                }
            }
        }
        // Save Layout to variable.
        $layout = $opt['layout'] ?? Defaults::bar( 'layout' );
        if ( !in_array( $layout, self::FREE_LAYOUTS, true ) ) {
            $opt['layout'] = Defaults::bar( 'layout' );
        }
        // The ring layouts are for the circular bar, the others for the linear one; the Vertical list for both, and on
        // the other bar type instead.
        $is_ring = in_array( $opt['layout'], self::RING_LAYOUTS, true );
        if ( ( 'circular' === ($opt['bar_type'] ?? '') ? !$is_ring && 'list' !== $opt['layout'] : $is_ring ) ) {
            $opt['layout'] = 'list';
        }
        return $opt;
    }

    /**
     * A bar's own settings without its icons' look while "Same as Progress Bar" is on (Gift Bar, on by default;
     * Discount bars from FSL Discount 1.1.0): its icons then look like the Progress Bar's, only the icon is its own.
     *
     * @since	4.0.0
     */
    public static function inherit_icon_settings( $options, $default = 1 ) {
        return ( empty( $options['inherit_icon_settings'] ?? $default ) ? $options : array_diff_key( $options, array_flip( self::ICON_LOOK ) ) );
    }

    private function fsl_wrapper( $opt = [], $wrapper = '', $for = '' ) {
        $wrapper_class = $opt['wrapper_class'] ?? [];
        $wrapper_styles = $opt['wrapper_styles'] ?? [];
        $wrapper_class[] = 'devnet_fsl-free-shipping';
        $wrapper_styles[] = '--fsl-bar-border-color:' . $opt['bar_border_color'] . ';';
        $wrapper_styles[] = '--fsl-bar-bg-color:' . $opt['bar_bg_color'] . ';';
        $wrapper_styles[] = '--fsl-bar-inner-color:' . $opt['bar_inner_color'] . ';';
        $wrapper_styles[] = '--fsl-bar-border-radius:' . $opt['bar_radius'] . 'px;';
        $center_text_class = 'fsl-center-text';
        // Add center text class if not set otherwise in the pro version.
        $wrapper_class[] = $center_text_class;
        // A set text size instead of the theme's ("Theme default" adds nothing).
        if ( !empty( $opt['text_size'] ) ) {
            $wrapper_class[] = 'fsl-text-' . sanitize_html_class( $opt['text_size'] );
        }
        if ( $opt['hide_border_shadow'] ) {
            $wrapper_class[] = 'devnet_fsl-no-shadow';
        }
        if ( $opt['disable_animation'] ) {
            $wrapper_class[] = 'devnet_fsl-no-animation';
            $disabled_animation = $opt['disabled_animations'] ?? 'all';
            $wrapper_class[] = 'devnet_fsl-disabled-animation-' . $disabled_animation;
        }
        // Add in for progress bar.
        if ( $for === 'progress_bar' ) {
            $bar_type = $opt['bar_type'] ?? Defaults::bar( 'bar_type' );
            $wrapper_class[] = 'bar-type-' . $bar_type;
        }
        $output = [
            'class'  => implode( ' ', $wrapper_class ),
            'styles' => implode( ' ', $wrapper_styles ),
        ];
        return ( isset( $output[$wrapper] ) ? $output[$wrapper] : $output );
    }

    /**
     * Get cart subtotal depending on tax and discounts.
     * 
     * @since   3.1.0
     */
    public function get_cart_subtotal( $ignore_discounts = false ) {
        $cart = WC()->cart;
        if ( !$cart ) {
            return;
        }
        $cart_subtotal = $cart->get_displayed_subtotal();
        $discount = $cart->get_discount_total();
        $discount_tax = $cart->get_discount_tax();
        $price_including_tax = $cart->display_prices_including_tax();
        $price_decimal = wc_get_price_decimals();
        if ( $ignore_discounts ) {
            $discount = 0;
            $discount_tax = 0;
        }
        if ( $price_including_tax ) {
            $cart_subtotal = round( $cart_subtotal - ($discount + $discount_tax), $price_decimal );
        } else {
            $cart_subtotal = round( $cart_subtotal - $discount, $price_decimal );
        }
        return apply_filters( 'fsl_get_cart_subtotal', $cart_subtotal, $cart );
    }

    public function get_discount() {
        $cart = WC()->cart;
        if ( !$cart ) {
            return;
        }
        $discount = $cart->get_discount_total();
        $discount_tax = $cart->get_discount_tax();
        $price_including_tax = $cart->display_prices_including_tax();
        $price_decimal = wc_get_price_decimals();
        if ( $price_including_tax ) {
            $discount = round( $discount + $discount_tax, $price_decimal );
        } else {
            $discount = round( $discount, $price_decimal );
        }
        return (float) $discount;
    }

    /**
     * 
     * FREE SHIPPING BAR
     * 
     */
    public function setup_data_for_free_shipping_bar( $args = [] ) {
        $cart_subtotal = $args['cart_subtotal'] ?? null;
        $opt = $args['options'] ?? [];
        // Set module name
        $opt['module_name'] = 'free-shipping';
        // Is allowed zero `0` shipping cost – means free shipping.
        $zero_shipping = $opt['zero_shipping'];
        $amount_for_free_shipping = Helper::get_free_shipping_min_amount();
        // Normalize decimal separator
        if ( is_string( $amount_for_free_shipping ) ) {
            $amount_for_free_shipping = str_replace( ',', '.', $amount_for_free_shipping );
        }
        // Must be numeric
        if ( !is_numeric( $amount_for_free_shipping ) ) {
            return;
        }
        $amount_for_free_shipping = (float) $amount_for_free_shipping;
        // If zero shipping is not allowed, stop
        if ( !$zero_shipping && $amount_for_free_shipping <= 0 ) {
            return;
        }
        $threshold = $amount_for_free_shipping;
        // show_shipping_before_address
        if ( !WC()->cart->show_shipping() ) {
            return;
        }
        $chosen_shipping_method = Helper::chosen_shipping_method();
        $is_local_pickup = Helper::starts_with( $chosen_shipping_method, 'local_pickup' );
        $fs_instance = Helper::$free_shipping_instance;
        // Custom and third-party thresholds have no WooCommerce instance: a free shipping coupon still counts.
        $fs_requires = ( $fs_instance ? $fs_instance['requires'] ?? '' : 'either' );
        $ignore_discounts = ($fs_instance['ignore_discounts'] ?? 'no') === 'yes';
        if ( $is_local_pickup && !$opt['local_pickup'] ) {
            return [
                'placeholder' => true,
            ];
        }
        if ( !$opt['enable_bar'] ) {
            return;
        }
        if ( $ignore_discounts ) {
            $cart_subtotal = $this->get_cart_subtotal( $ignore_discounts );
            $threshold = $amount_for_free_shipping - $this->get_discount();
        }
        $calc = Helper::calculate_percentage( $amount_for_free_shipping, $cart_subtotal );
        $percent = $calc['percent'];
        $remaining = $calc['remaining'];
        $cart_reached_threshold = $percent === 100;
        $has_coupon = Helper::is_free_shipping_coupon_applied();
        // Same rules as WC_Shipping_Free_Shipping::is_available().
        switch ( $fs_requires ) {
            case '':
                // N/A: free shipping is always available, nothing to reach.
                return;
            case 'coupon':
                if ( !$has_coupon ) {
                    return;
                }
                $free_shipping_pass = true;
                break;
            case 'min_amount':
                $free_shipping_pass = $cart_reached_threshold;
                break;
            case 'both':
                $free_shipping_pass = $has_coupon && $cart_reached_threshold;
                if ( !$has_coupon && $cart_reached_threshold ) {
                    $opt['description'] = esc_html__( 'Waiting for Free Shipping coupon', 'free-shipping-label' );
                }
                break;
            default:
                // 'either'
                $free_shipping_pass = $has_coupon || $cart_reached_threshold;
        }
        /**
         * Allow free shipping when shipping cost is 0 and the option is enabled.
         * Some shipping methods return 0.00 as the shipping total when free shipping is applied.
         * Skip if the selected method is local pickup.
         */
        if ( $zero_shipping && !$is_local_pickup && $chosen_shipping_method && (float) WC()->cart->get_shipping_total() <= 0 ) {
            $free_shipping_pass = true;
        }
        return [
            'pass'             => $free_shipping_pass,
            'options'          => $opt,
            'percent'          => $percent,
            'reached'          => [],
            'threshold'        => $threshold,
            'placeholder_args' => [
                'remaining' => $remaining,
                'threshold' => $amount_for_free_shipping,
            ],
            'cart_subtotal'    => $cart_subtotal,
        ];
    }

    /**
     * Where the close message starts, in percent of the free shipping threshold.
     *
     * @since	4.0.0
     */
    public static function close_message_percent() {
        return (float) apply_filters( 'fsl_close_message_percent', 75 );
    }

    /**
     * Build progress bar and set styles.
     *
     * @since    3.0.0
     */
    public function progress_bar( $args = [] ) {
        if ( !WC()->cart ) {
            return;
        }
        $cart_subtotal = $this->get_cart_subtotal();
        $is_updatable = $args['updatable'] ?? false;
        $in_table = $args['in_table'] ?? false;
        $wrappers = $args['wrappers'] ?? [];
        if ( WC()->cart->get_cart_contents_count() === 0 ) {
            $this->fsl_bar_placeholder( true );
            return;
        }
        $options = $this->get_progress_bar_options( $args );
        $args = [
            'cart_subtotal' => $cart_subtotal,
            'options'       => $options,
        ];
        $args = apply_filters( 'fsl_progress_bar_setup_args', $args );
        $setup_data = [
            'cart_subtotal' => $args['cart_subtotal'],
            'modules'       => [],
        ];
        // Free Shipping Bar data
        $fs_data = $this->setup_data_for_free_shipping_bar( $args );
        $fs_placeholder = $fs_data['placeholder'] ?? false;
        if ( !$fs_placeholder ) {
            $setup_data['modules']['free-shipping'] = $fs_data;
        }
        $setup_data = apply_filters( 'fsl_progress_bar_setup_data', $setup_data );
        $grouped_modules = $setup_data['modules']['group']['grouped_modules'] ?? [];
        $stand_alone_modules = $setup_data['modules'] ?? [];
        // Sorting logic
        $sort_by_threshold = function ( $a, $b ) {
            return ($a['threshold'] ?? 0) <=> ($b['threshold'] ?? 0);
        };
        // Sort grouped modules if available; otherwise, sort stand-alone modules
        if ( !empty( $grouped_modules ) && count( $grouped_modules ) > 1 ) {
            uasort( $grouped_modules, $sort_by_threshold );
            $setup_data['modules']['group']['grouped_modules'] = $grouped_modules;
        } elseif ( !empty( $stand_alone_modules ) && count( $stand_alone_modules ) > 1 ) {
            uasort( $stand_alone_modules, $sort_by_threshold );
            $setup_data['modules'] = $stand_alone_modules;
        }
        if ( $in_table ) {
            echo '<tr><td colspan="10">';
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- cart_hash_attribute() escapes its value.
        echo '<div class="fsl-wrapper" data-updatable="' . esc_attr( $is_updatable ) . '"' . self::cart_hash_attribute( $is_updatable ) . '>';
        foreach ( $setup_data['modules'] as $module => $module_data ) {
            if ( !empty( $wrappers ) && isset( $module_data['options'] ) ) {
                $module_data['options'] += $wrappers;
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is fully escaped inside fsl_progress_bar_html().
            echo $this->fsl_progress_bar_html( $module_data );
        }
        echo '</div>';
        if ( $in_table ) {
            echo '</td></tr>';
        }
    }

    /**
     * Output progress bar html.
     *
     * @since    3.0.0
     */
    public function fsl_progress_bar_html( $args ) {
        $args = apply_filters( 'fsl_progress_bar_args', $args );
        $args = self::single_goal_group( $args );
        $opt = $args['options'] ?? [];
        $pass = $args['pass'] ?? false;
        $layout = $args['layout'] ?? 'default';
        $placeholder_args = $args['placeholder_args'] ?? [];
        if ( empty( $opt ) ) {
            return;
        }
        $wrapper = $this->fsl_wrapper( $opt, '', 'progress_bar' );
        $wrapper_class = $wrapper['class'] . ' fsl-layout-' . $layout;
        $wrapper_styles = $wrapper['styles'];
        $inner_html = '';
        if ( $pass ) {
            $wrapper_class .= ' fsl-threshold-reached';
            if ( $opt['show_qualified_message'] ) {
                $wrapper_class .= ' qualified_message';
                $inner_html = $this->get_fsl_qualified_message_html( $opt['qualified_message'], $placeholder_args );
                $args['options']['show_fsl_title'] = false;
                //$args['options']['title'] = $opt['qualified_message'];
                $args['options']['show_fsl_description'] = false;
            }
            if ( $opt['show_full_progress_bar'] ) {
                $inner_html .= $this->fsl_layout( $layout, $args );
            }
            if ( !$inner_html ) {
                return $this->fsl_bar_placeholder();
            }
        } else {
            $inner_html .= $this->fsl_layout( $layout, $args );
        }
        $output = '';
        $output .= '<div class="' . esc_attr( $wrapper_class ) . '" style="' . esc_attr( $wrapper_styles ) . '">';
        $output .= $inner_html;
        $output .= '</div>';
        return apply_filters( 'fsl_progress_bar_html', $output );
    }

    /**
     * One goal in a goals layout (GOAL_LAYOUTS, RING_LAYOUTS; the bar's layout after get_progress_bar_options()'s
     * fallbacks): a group of one, which the layout shows as it shows several goals. Once the goal is reached, only with
     * "Show full progress bar": else the reached message replaces the bar, as before. The Classic layouts keep the
     * single bar.
     *
     * @since	4.0.0
     */
    private static function single_goal_group( $args ) {
        $opt = $args['options'] ?? [];
        $layout = $opt['layout'] ?? '';
        $pass = !empty( $args['pass'] );
        if ( !empty( $args['grouped_modules'] ) || !in_array( $layout, array_merge( self::GOAL_LAYOUTS, self::RING_LAYOUTS ), true ) || $pass && empty( $opt['show_full_progress_bar'] ) ) {
            return $args;
        }
        if ( $pass ) {
            $args['reached']['qualified_message'] = $opt['qualified_message'] ?? '';
        }
        return [
            'layout'          => $layout,
            'options'         => $opt,
            'grouped_modules' => [
                $opt['module_name'] ?? 'free-shipping' => $args,
            ],
            'percent'         => $args['percent'] ?? 0,
        ];
    }

    /**
     * 
     * 
     * @since	3.0.0
     */
    public function fsl_layout( $layout, $args ) {
        $opt = $args['options'] ?? [];
        $percent = $args['percent'] ?? 0;
        $grouped_modules = $args['grouped_modules'] ?? [];
        $placeholder_args = $args['placeholder_args'] ?? [];
        $bar_type = $opt['bar_type'] ?? Defaults::bar( 'bar_type' );
        if ( !empty( $grouped_modules ) ) {
            return $this->fsl_grouped_modules_layout( $layout, $grouped_modules, $args );
        }
        $title_html = '';
        $description_html = '';
        $progress_bar_html = '';
        if ( $opt['show_fsl_title'] ) {
            $title_html = $this->get_fsl_title_html( $opt['title'], $placeholder_args );
        }
        if ( $opt['show_fsl_description'] ) {
            $description_html = $this->get_fsl_description_html( $opt['description'], $placeholder_args );
        }
        if ( 'linear' === $bar_type ) {
            /**
             * Adjust bar width if icon indicator is set.
             */
            $indicator_icon = $opt['indicator_icon'] ?? false;
            if ( $indicator_icon ) {
                $indicator_icon_size = ( isset( $opt['indicator_icon_size'] ) && (int) $opt['indicator_icon_size'] ? (int) $opt['indicator_icon_size'] : 1 );
                $opt['bar_width_adjust'] = $indicator_icon_size / 2;
            }
            // One goal: a design preset can show a marker at the progress (with more goals it would hit their icons).
            $opt['progress_marker'] = !empty( $opt['bar_preset'] ) && (Presets::all()[$opt['bar_preset']]['marker'] ?? false);
            $progress_bar_html = $this->linear_bar_html( $percent, $opt );
        }
        if ( 'circular' === $bar_type ) {
        }
        $html = $title_html . $progress_bar_html . $description_html;
        return $html;
    }

    /**
     * Build combined modules layout.
     * 
     * @since	3.0.0
     */
    public function fsl_grouped_modules_layout( $layout, $grouped_modules, $args ) {
        $opt = $args['options'] ?? [];
        $percent = $args['percent'] ?? 0;
        $bar_type = $opt['bar_type'] ?? Defaults::bar( 'bar_type' );
        // Pass layout value to options.
        $opt['layout'] = $layout;
        $is_threshold_bubbles_layout = $layout === 'threshold_bubbles';
        $is_ring_layout = 'circular' === $bar_type && in_array( $layout, self::RING_LAYOUTS, true );
        $is_goal_layout = in_array( $layout, self::GOAL_LAYOUTS, true ) || $is_ring_layout;
        $thresholds = [];
        $focused_module = '';
        $higher_percentage = -1;
        // Initialize to a low value to ensure comparison works
        foreach ( $grouped_modules as $module_name => $module ) {
            $thresholds[$module_name] = $module['threshold'] ?? null;
            $percent = $module['percent'] ?? null;
            if ( $percent !== null && $percent < 100 && $percent > $higher_percentage ) {
                $higher_percentage = $percent;
                $focused_module = $module_name;
            }
        }
        // Ensure $thresholds is not empty before calling max
        $highest_threshold = ( !empty( $thresholds ) ? max( $thresholds ) : null );
        $show_full_progress_bar = false;
        $display_data = [];
        $longest_text = '';
        foreach ( $grouped_modules as $module_name => $module ) {
            if ( empty( $module ) ) {
                continue;
            }
            $options = $module['options'] ?? [];
            $placeholder_args = $module['placeholder_args'] ?? [];
            $threshold = $module['threshold'] ?? '';
            $reached = $module['reached']['qualified_message'] ?? '';
            $percent = $module['percent'] ?? null;
            $is_reached = $percent === 100;
            $title = ( !empty( $options['show_title'] ?? 1 ) ? $options['title'] ?? '' : '' );
            $description = ( !empty( $options['show_description'] ?? 1 ) ? $options['description'] ?? '' : '' );
            $label = $options['label'] ?? '';
            $show_qualified_message = $options['show_qualified_message'] ?? false;
            $indicator_icon = $options['indicator_icon'] ?? false;
            // Cast as PHP arithmetic does ('' or null = 0), minus its TypeError for '' thresholds from
            // fsl_progress_bar_setup_data modules. No highest threshold above 0: every threshold is at the end.
            $threshold_percentage = ( (float) $highest_threshold > 0 ? (float) $threshold / (float) $highest_threshold * 100 : 100 );
            $text = [
                'qualified_message' => $this->get_fsl_qualified_message_html( $reached, $placeholder_args ),
                'title'             => $this->get_fsl_title_html( $title, $placeholder_args ),
                'description'       => $this->get_fsl_description_html( $description, $placeholder_args ),
            ];
            // The goal layouts show a goal's label and status, and its threshold until it's reached.
            if ( $is_goal_layout ) {
                if ( $is_reached ) {
                    $status = esc_html__( 'Unlocked', 'free-shipping-label' );
                } else {
                    /* translators: {remaining}: the amount left to reach this goal. */
                    $status = Helper::convert_placeholders( esc_html__( '{remaining} to go', 'free-shipping-label' ), $placeholder_args );
                }
                $goal = [
                    'name'    => $label,
                    'label'   => esc_html( $label ),
                    'status'  => $status,
                    'amount'  => ( $is_reached ? '' : Helper::convert_placeholders( '{threshold}', $placeholder_args ) ),
                    'at'      => $threshold_percentage,
                    'percent' => $percent,
                    'args'    => $placeholder_args,
                ];
                // The layouts with each goal's icon: a gift box or a package for a goal without one.
                if ( $is_ring_layout || in_array( $layout, [
                    'tiles',
                    'stacked',
                    'spotlight',
                    'rewards_count'
                ], true ) ) {
                    $goal['icon'] = Icons::get_svg( ( !empty( $options['icon'] ) ? $options['icon'] : (( 'gift-bar' === $module_name ? 'gift-box-1' : 'package-1' )) ), 20 );
                }
            }
            if ( $is_threshold_bubbles_layout ) {
                if ( $is_reached ) {
                    $combined_text = $text['qualified_message'];
                } else {
                    $combined_text = $text['title'] . ' ' . $text['description'];
                }
                if ( strlen( $combined_text ) > strlen( $longest_text ) ) {
                    $longest_text = $combined_text;
                }
            }
            if ( $indicator_icon ) {
                $indicator_icon_size = ( isset( $options['indicator_icon_size'] ) && (int) $options['indicator_icon_size'] ? (int) $options['indicator_icon_size'] : 1 );
                $opt['threshold_indicators'][$module_name] = [
                    'indicator_icon'          => $options['indicator_icon'],
                    'indicator_icon_size'     => $indicator_icon_size,
                    'indicator_icon_shape'    => $options['indicator_icon_shape'],
                    'indicator_icon_bg_color' => $options['indicator_icon_bg_color'],
                    'icon'                    => $options['icon'],
                    'icon_color'              => $options['icon_color'],
                    'threshold'               => $threshold,
                    'highest_threshold'       => $highest_threshold,
                    'threshold_percentage'    => $threshold_percentage,
                    'focused'                 => $focused_module === $module_name,
                    'text'                    => $text,
                ];
                // The highest threshold, at the end of the bar (a float, so not === 100).
                if ( $threshold_percentage >= 100 ) {
                    $opt['bar_width_adjust'] = $indicator_icon_size / 2;
                }
            } elseif ( $is_threshold_bubbles_layout || in_array( $layout, ['milestones', 'stepper'], true ) ) {
                $opt['threshold_indicators'][$module_name] = [
                    'threshold'            => $threshold,
                    'highest_threshold'    => $highest_threshold,
                    'threshold_percentage' => $threshold_percentage,
                    'focused'              => $focused_module === $module_name,
                    'text'                 => $text,
                ];
            }
            $show_full_progress_bar = $options['show_full_progress_bar'] ?? false;
            if ( $bar_type === 'linear' && !in_array( $layout, ['list', 'list_alt', 'timeline'], true ) ) {
                $title = null;
                $description = null;
                if ( $layout === 'horizontal_1' || $layout === 'horizontal_2' ) {
                    $title = $label;
                    $show_qualified_message = true;
                }
            }
            $display_data[$module_name] = [
                'text'                      => $text,
                'is_reached'                => $is_reached,
                'reached_class'             => ( $is_reached ? 'fsl-reached' : '' ),
                'display_title'             => $title,
                'display_description'       => $description,
                'display_qualified_message' => $reached && $show_qualified_message,
                'focused'                   => $focused_module === $module_name,
                'layout'                    => $layout,
            ];
            if ( $is_goal_layout ) {
                $display_data[$module_name]['goal'] = $goal;
            }
        }
        if ( $is_ring_layout ) {
            return '';
        }
        if ( $is_goal_layout ) {
            return $this->goals_layout_html(
                $layout,
                $display_data,
                $opt,
                $percent,
                $focused_module,
                $reached && !$show_full_progress_bar
            );
        }
        // Start building HTML
        $html = '';
        if ( $layout === 'list' || $layout === 'list_alt' ) {
            $html .= $this->build_vertical_list_html( $display_data );
        } elseif ( $is_threshold_bubbles_layout ) {
            $show_full_progress_bar = true;
            $html .= $this->render_height_adjuster( $longest_text );
        } else {
            foreach ( $display_data as $data ) {
                $html .= $this->build_module_text_html( $data );
            }
        }
        if ( !$reached || $show_full_progress_bar ) {
            $progress_bar_html = '';
            if ( 'linear' === $bar_type ) {
                $progress_bar_html = $this->linear_bar_html( $percent, $opt );
                if ( $focused_module ) {
                    $fsl_description_html = '';
                    // Layouts that include descriptions
                    $layouts_with_desc = ['horizontal_2', 'desc_only_above', 'desc_only_beneath'];
                    if ( in_array( $layout, $layouts_with_desc ) ) {
                        $fsl_description_html = $display_data[$focused_module]['text']['description'] ?? '';
                    }
                    // Append or prepend the description based on layout
                    switch ( $layout ) {
                        case 'desc_only_above':
                            $progress_bar_html = $fsl_description_html . $progress_bar_html;
                            break;
                        case 'desc_only_beneath':
                        case 'horizontal_2':
                            $progress_bar_html .= $fsl_description_html;
                            break;
                    }
                }
            }
            if ( 'circular' === $bar_type ) {
            }
            $html .= $progress_bar_html;
        }
        return $html;
    }

    /**
     * Build vertical list.
     * 
     * @since	3.4.0
     */
    public function build_vertical_list_html( $modules_data = [] ) {
        $html = '<ul class="fsl-modules-list">';
        foreach ( $modules_data as $module ) {
            $reached_class = $module['reached_class'] ?? '';
            $html .= '<li class="' . esc_attr( $reached_class ) . '">';
            $html .= $this->build_module_text_html( $module );
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * The goal layouts (GOAL_LAYOUTS): each goal's label and status, the next goal's description as a headline.
     *
     * @since	4.0.0
     */
    private function goals_layout_html(
        $layout,
        $display_data,
        $opt,
        $percent,
        $focused_module,
        $hide_bar
    ) {
        if ( 'stepper' === $layout ) {
            return $this->stepper_html( $display_data, $opt, $percent );
        }
        if ( 'segments' === $layout ) {
            return $this->segments_html( $display_data, $opt, $percent );
        }
        $last = end( $display_data );
        // The next goal's description; once every goal is reached, the last one's message.
        if ( $focused_module ) {
            $headline = $display_data[$focused_module]['text']['description'] ?? '';
        } else {
            $headline = ( !empty( $last['display_qualified_message'] ) ? $last['text']['qualified_message'] : '' );
        }
        // Milestones and Split header hang on the bar, so it stays.
        $bar = ( $hide_bar && !in_array( $layout, ['milestones', 'split'], true ) ? '' : $this->linear_bar_html( $percent, $opt ) );
        switch ( $layout ) {
            case 'checklist':
                return $this->goals_list_html( $display_data, ['label', 'status'] ) . $bar;
            case 'timeline':
                return $this->build_vertical_list_html( $display_data );
            case 'next_goal':
                return $headline . $bar . $this->goals_list_html( $display_data, ['label'] );
            case 'split':
                return $this->goals_list_html( $display_data, ['label', 'amount'] ) . $bar;
            case 'headline':
                return $headline . $bar;
            case 'tiles':
                return $this->goals_list_html( $display_data, ['icon', 'label', 'status'] ) . $bar;
            case 'stacked':
                // Each goal's own bar, without indicators.
                unset($opt['threshold_indicators'], $opt['bar_width_adjust']);
                $opt['indicator_icon'] = 0;
                return $this->goals_list_html(
                    $display_data,
                    [
                        'icon',
                        'label',
                        'status',
                        'bar'
                    ],
                    '',
                    $opt
                );
            case 'spotlight':
                return $this->spotlight_html( $display_data ) . $bar;
            case 'rewards_count':
                return $this->rewards_count_html( $display_data ) . $bar . $headline;
            default:
                // Milestones: the labels under the bar, each ending at its goal.
                // As wide as the bar, which leaves room for the last icon.
                $width = ( empty( $opt['bar_width_adjust'] ) ? '' : 'width:calc(100% - ' . (float) $opt['bar_width_adjust'] . 'px);' );
                return $bar . $this->goals_list_html( $display_data, ['label', 'status'], $this->goal_columns( $display_data ) . $width );
        }
    }

    /**
     * Grid columns for the goals: one per goal, from the goal before it to this one.
     *
     * @since	4.0.0
     */
    private function goal_columns( $display_data ) {
        $columns = [];
        $from = 0;
        foreach ( $display_data as $module ) {
            $columns[] = 'minmax(0,' . round( max( 1, $module['goal']['at'] - $from ), 3 ) . 'fr)';
            $from = $module['goal']['at'];
        }
        return 'grid-template-columns:' . implode( ' ', $columns ) . ';';
    }

    /**
     * Stepper: the steps evenly spaced, each in the middle of its share of the bar, and the fill running from step
     * to step; each step's label and threshold under it.
     *
     * @since	4.0.0
     */
    private function stepper_html( $display_data, $opt, $percent ) {
        $count = count( $display_data );
        $ats = array_column( array_column( $display_data, 'goal' ), 'at' );
        $step = function ( $i ) use($count) {
            return ($i + 0.5) * 100 / $count;
        };
        // The fill: between the steps around the progress, as far as it is between their thresholds.
        $fill = 100;
        foreach ( $ats as $i => $at ) {
            if ( $percent < $at ) {
                $from_at = ( $i ? $ats[$i - 1] : 0 );
                $from_step = ( $i ? $step( $i - 1 ) : 0 );
                $fill = $from_step + ($percent - $from_at) / ($at - $from_at) * ($step( $i ) - $from_step);
                break;
            }
        }
        foreach ( array_keys( $display_data ) as $i => $module ) {
            if ( isset( $opt['threshold_indicators'][$module] ) ) {
                $opt['threshold_indicators'][$module]['threshold_percentage'] = $step( $i );
            }
        }
        // No step at the end of the bar to leave room for.
        unset($opt['bar_width_adjust']);
        return $this->linear_bar_html( $fill, $opt ) . $this->goals_list_html( $display_data, ['label', 'amount'], 'grid-template-columns:repeat(' . $count . ',minmax(0,1fr));' );
    }

    /**
     * Tier segments: a bar per goal, from the goal before it to this one, and each goal's label under its bar.
     *
     * @since	4.0.0
     */
    private function segments_html( $display_data, $opt, $percent ) {
        // Each part is its own bar: no indicators on them.
        unset($opt['threshold_indicators'], $opt['bar_width_adjust']);
        $opt['indicator_icon'] = 0;
        $columns = $this->goal_columns( $display_data );
        $bars = '';
        $from = 0;
        foreach ( $display_data as $module ) {
            $at = $module['goal']['at'];
            $fill = ( $at > $from ? max( 0, min( 100, ($percent - $from) / ($at - $from) * 100 ) ) : 100 );
            $bars .= $this->linear_bar_html( $fill, [
                'label' => $module['goal']['name'],
            ] + $opt );
            $from = $at;
        }
        return '<div class="fsl-goal-segments" style="' . esc_attr( $columns ) . '">' . $bars . '</div>' . $this->goals_list_html( $display_data, ['label', 'status'], $columns );
    }

    /**
     * Spotlight: a card per goal, side by side where there's room, with its icon, what's left to reach it and its
     * label; the next goal outlined, as in Goal tiles.
     *
     * @since	4.0.0
     */
    private function spotlight_html( $display_data ) {
        $html = '';
        foreach ( $display_data as $data ) {
            $goal = $data['goal'];
            $classes = [
                'fsl-goal',
                'fsl-goal-spotlight',
                $data['reached_class'],
                ( empty( $data['focused'] ) ? '' : 'fsl-next-goal' )
            ];
            /* translators: {remaining}: the amount left to reach the goal. */
            $note = ( $data['is_reached'] ? esc_html__( 'Unlocked', 'free-shipping-label' ) : Helper::convert_placeholders( esc_html__( 'Add {remaining} more to get', 'free-shipping-label' ), $goal['args'] ) );
            $html .= '<li class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">';
            $html .= '<span class="fsl-goal-media">' . ($goal['icon'] ?? '') . '</span>';
            $html .= '<span class="fsl-goal-text"><span class="fsl-goal-note">' . $note . '</span> <span class="fsl-goal-name">' . $goal['label'] . '</span></span>';
            $html .= '</li>';
        }
        return '<ul class="fsl-goals">' . $html . '</ul>';
    }

    /**
     * Rewards count: how many of the goals are reached, and their icons.
     *
     * @since	4.0.0
     */
    private function rewards_count_html( $display_data ) {
        $total = count( $display_data );
        $done = count( array_filter( array_column( $display_data, 'is_reached' ) ) );
        if ( !$done ) {
            /* translators: %d: the number of rewards. */
            $count = sprintf( _n(
                'Unlock %d reward',
                'Unlock %d rewards',
                $total,
                'free-shipping-label'
            ), $total );
        } elseif ( $done < $total ) {
            /* translators: 1: the rewards unlocked, 2: all the rewards. */
            $count = sprintf( _n(
                '%1$d of %2$d reward unlocked',
                '%1$d of %2$d rewards unlocked',
                $total,
                'free-shipping-label'
            ), $done, $total );
        } else {
            $count = __( 'All rewards unlocked', 'free-shipping-label' );
        }
        return '<div class="fsl-goal-count">' . esc_html( $count ) . '</div>' . $this->goals_list_html( $display_data, ['icon', 'label'] );
    }

    /**
     * The goals as a list, with these parts of each: icon, label, status, amount, bar (its own, with these options).
     *
     * @since	4.0.0
     */
    private function goals_list_html(
        $display_data,
        $parts,
        $style = '',
        $opt = []
    ) {
        $html = '<ul class="fsl-goals"' . (( $style ? ' style="' . esc_attr( $style ) . '"' : '' )) . '>';
        foreach ( $display_data as $module ) {
            $goal = $module['goal'] ?? [];
            $classes = ['fsl-goal', $module['reached_class'] ?? '', ( empty( $module['focused'] ) ? '' : 'fsl-next-goal' )];
            $spans = [];
            foreach ( $parts as $part ) {
                if ( 'bar' === $part ) {
                    $spans[] = $this->linear_bar_html( $goal['percent'], [
                        'label' => $goal['name'],
                    ] + $opt );
                } elseif ( ($goal[$part] ?? '') !== '' ) {
                    $spans[] = '<span class="fsl-goal-' . $part . '">' . $goal[$part] . '</span>';
                }
            }
            $html .= '<li class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">' . implode( ' ', $spans ) . '</li>';
        }
        return $html . '</ul>';
    }

    /**
     * Build module text.
     * 
     * @since	3.4.0
     */
    public function build_module_text_html( $module_data ) {
        $reached_class = $module_data['reached_class'] ?? '';
        $display_title = $module_data['display_title'] ?? false;
        $display_description = $module_data['display_description'] ?? false;
        $display_qualified_message = $module_data['display_qualified_message'] ?? false;
        $title = $module_data['text']['title'] ?? '';
        $description = $module_data['text']['description'] ?? '';
        $qualified_message = $module_data['text']['qualified_message'] ?? '';
        $layout = $module_data['layout'] ?? '';
        $is_focused = $module_data['focused'] ?? false;
        if ( in_array( $layout, ['list', 'timeline'], true ) && !$is_focused ) {
            $display_description = false;
        }
        $html = '<div class="fsl-module-block ' . esc_attr( $reached_class ) . '">';
        if ( $display_qualified_message ) {
            $html .= $qualified_message;
        } else {
            if ( $display_title ) {
                $html .= $title;
            }
            if ( $display_description ) {
                $html .= $description;
            }
        }
        $html .= '</div>';
        return $html;
    }

    /** 
     * Build hidden height adjuster.
     * 
     * @since	3.4.0
     */
    public function render_height_adjuster( $text ) {
        return '<div class="fsl-height-adjuster" style="visibility:hidden; opacity:0;">' . wp_kses_post( $text ) . '</div>';
    }

    public function get_fsl_title_html( $text, $placeholder_args ) {
        if ( !$text ) {
            return;
        }
        $text = Helper::convert_placeholders( $text, $placeholder_args );
        return '<h4 class="fsl-title title">' . wp_kses_post( $text ) . '</h4>';
    }

    public function get_fsl_description_html( $text, $placeholder_args ) {
        if ( !$text ) {
            return;
        }
        $text = Helper::convert_placeholders( $text, $placeholder_args );
        return '<span class="fsl-description fsl-notice notice">' . wp_kses_post( $text ) . '</span>';
    }

    public function get_fsl_qualified_message_html( $text, $placeholder_args ) {
        if ( !$text ) {
            return;
        }
        // plain text: HTML is Pro, so drop tags saved under Pro instead of printing them
        $html = esc_html( wp_strip_all_tags( $text ) );
        // Placeholders last: esc_html() would show their wc_price() markup as text, and kses strips its <bdi>.
        $html = Helper::convert_placeholders( $html, $placeholder_args );
        return '<h4 class="fsl-title title">' . $html . '</h4>';
    }

    /**
     * The cart an updatable bar was rendered for (WooCommerce's cart hash, '' for an empty cart).
     * fsl-public.js compares it with the woocommerce_cart_hash cookie to skip refreshes that change nothing.
     *
     * @since	4.0.0
     */
    private static function cart_hash_attribute( $updatable ) {
        if ( !$updatable || !function_exists( 'WC' ) || !WC()->cart ) {
            return '';
        }
        return ' data-cart-hash="' . esc_attr( WC()->cart->get_cart_hash() ) . '"';
    }

    /**
     * Output empty placeholder.
     *
     * @since    2.6.10
     */
    public static function fsl_bar_placeholder( $should_echo = false, $updatable = true ) {
        $placeholder = '<div class="fsl-wrapper" data-updatable="' . esc_attr( $updatable ) . '"' . self::cart_hash_attribute( $updatable ) . '><div class="devnet_fsl-free-shipping fsl-placeholder fsl-flat"></div></div>';
        $output = apply_filters( 'fsl_progress_bar_placeholder_html', $placeholder );
        if ( $should_echo ) {
            echo wp_kses_post( $output );
        } else {
            return $output;
        }
    }

    /**
     * Progress bar semantics for screen readers ("Free Shipping, 67%"). Attributes only: no visual change.
     * Goes on an element without text: screen readers skip what is inside a progressbar
     * (in-circle title, threshold bubbles).
     *
     * @since	4.0.0
     */
    private static function progressbar_aria( $progress, $opt = [] ) {
        $aria = sprintf( ' role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%d"', max( 0, min( 100, round( (float) $progress ) ) ) );
        if ( !empty( $opt['label'] ) ) {
            $aria .= ' aria-label="' . esc_attr( wp_strip_all_tags( $opt['label'] ) ) . '"';
        }
        return $aria;
    }

    /**
     * Build linear progress bar html.
     * 
     * @since	2.6.0
     */
    public function linear_bar_html( $progress, $opt = [] ) {
        if ( empty( $opt['bar_height'] ) ) {
            return '';
        }
        $bar_height = $opt['bar_height'];
        $bar_inner_color = $opt['bar_inner_color'] ?? '';
        $bar_bg_color = $opt['bar_bg_color'] ?? '';
        $bar_border_color = $opt['bar_border_color'] ?? '';
        $style_pb = ['--fsl-percent:' . $progress . ';', '--fsl-bar-inner-color:' . $bar_inner_color . ';'];
        $classes_pb = [
            'fsl-progress-bar',
            'progress-bar',
            'shine',
            'stripes'
        ];
        if ( $bar_bg_color ) {
            $style_pb[] = 'background-color:' . $bar_bg_color . ';';
        }
        if ( $bar_border_color ) {
            $style_pb[] = 'border-color:' . $bar_border_color . ';';
        }
        $style_pb_amount = ['width:' . $progress . '%;', 'height:' . $bar_height . 'px;', 'background-color:' . $bar_inner_color . ';'];
        $threshold_data = [
            'html'    => '',
            'style'   => [],
            'classes' => [],
        ];
        $threshold_data = apply_filters(
            'fsl_progress_bar_threshold_data',
            $threshold_data,
            $progress,
            $opt
        );
        $indicators_html = $threshold_data['html'] ?? '';
        $style_pb = array_merge( $style_pb, $threshold_data['style'] ?? [] );
        $classes_pb = array_merge( $classes_pb, $threshold_data['classes'] ?? [] );
        $html = '<div class="' . esc_attr( implode( ' ', $classes_pb ) ) . '" style="' . esc_attr( implode( ' ', $style_pb ) ) . '">';
        $html .= '<span class="fsl-progress-amount progress-amount" style="' . esc_attr( implode( ' ', $style_pb_amount ) ) . '"' . self::progressbar_aria( $progress, $opt ) . '></span>';
        $html .= $indicators_html;
        $html .= '</div>';
        return $html;
    }

    public static function set_threshold_indicators( $progress, $opt ) {
        $layout = $opt['layout'] ?? '';
        $bar_height = $opt['bar_height'] ?? '';
        $threshold_bubbles = $layout === 'threshold_bubbles';
        $indicator_icon = $opt['indicator_icon'] ?? false;
        $threshold_indicators = $opt['threshold_indicators'] ?? [];
        $bar_width_adjust = $opt['bar_width_adjust'] ?? 0;
        $module_name = $opt['module_name'] ?? '';
        $style = [];
        $classes = [];
        $html = '';
        $is_fallback = false;
        // Add fallback indicator if missing
        if ( empty( $threshold_indicators ) && $indicator_icon && $module_name ) {
            $threshold_indicators[$module_name] = $opt;
            $is_fallback = true;
        }
        if ( !empty( $threshold_indicators ) ) {
            if ( $bar_width_adjust ) {
                $classes[] = 'fsl-bar-width-adjust';
                $style[] = '--fsl-bar-width-adjust:' . $bar_width_adjust . 'px;';
            }
            foreach ( $threshold_indicators as $module => $data ) {
                $threshold_percentage = $data['threshold_percentage'] ?? 100;
                $is_reached = $progress >= $threshold_percentage;
                $reached_class = ( $is_reached ? 'fsl-threshold-reached' : '' );
                $indicator_style = ['--fsl-module-threshold:' . $threshold_percentage . '%;', '--fsl-bar-height:' . $bar_height . 'px;'];
                $indicator_classes = ['fsl-threshold-indicator', 'fsl-' . sanitize_html_class( $module ), $reached_class];
                $indicator_inner = '';
                // ICON
                if ( !empty( $data['icon'] ) ) {
                    $icon_name = $data['icon'];
                    $icon_size = $data['indicator_icon_size'] ?? 24;
                    $icon_shape = $data['indicator_icon_shape'] ?? 'round';
                    $icon_bg_color = $data['indicator_icon_bg_color'] ?? '#ffffff';
                    $icon_color = $data['icon_color'] ?? '#333333';
                    $icon_html = Icons::get_svg( $icon_name, $icon_size );
                    $indicator_inner .= apply_filters(
                        'fsl_threshold_indicator_icon_html',
                        $icon_html,
                        $module,
                        $data
                    );
                    $indicator_style[] = '--fsl-icon-bg-color:' . $icon_bg_color . ';';
                    $indicator_style[] = '--fsl-icon-color:' . $icon_color . ';';
                    $indicator_style[] = '--fsl-icon-size:' . $icon_size . 'px;';
                    $indicator_classes[] = 'fsl-icon-shape-' . $icon_shape;
                    $indicator_classes[] = 'fsl-has-threshold-icon';
                }
                // BUBBLE
                if ( $threshold_bubbles && !$is_fallback ) {
                    $is_focused = $data['focused'] ?? false;
                    $z_index = ( $is_focused ? 1 : 0 );
                    $indicator_style[] = '--fsl-indicator-z-index:' . $z_index . ';';
                    $indicator_classes[] = 'fsl-has-threshold-bubble';
                    $text = $data['text'] ?? [];
                    $title = $text['title'] ?? '';
                    $description = $text['description'] ?? '';
                    $qualified_message = $text['qualified_message'] ?? '';
                    $message = ( $is_reached ? $qualified_message : $title . $description );
                    $indicator_inner .= '<span class="fsl-threshold-bubble">' . $message . '</span>';
                }
                $html .= '<span class="' . esc_attr( implode( ' ', array_filter( $indicator_classes ) ) ) . '" style="' . esc_attr( implode( ' ', $indicator_style ) ) . '">' . $indicator_inner . '</span>';
            }
        }
        // The progress marker (Delivery Road's truck): the bar's icon at the progress, styled by the icon settings.
        if ( !empty( $opt['progress_marker'] ) ) {
            $marker_size = ( isset( $opt['indicator_icon_size'] ) && (int) $opt['indicator_icon_size'] ? (int) $opt['indicator_icon_size'] : 16 );
            $marker_style = [
                '--fsl-module-threshold:' . $progress . '%;',
                '--fsl-bar-height:' . $bar_height . 'px;',
                '--fsl-icon-bg-color:' . ($opt['indicator_icon_bg_color'] ?? '#ffffff') . ';',
                '--fsl-icon-color:' . ($opt['icon_color'] ?? '#333333') . ';',
                '--fsl-icon-size:' . $marker_size . 'px;'
            ];
            $marker_class = 'fsl-threshold-indicator fsl-progress-marker fsl-has-threshold-icon fsl-icon-shape-' . sanitize_html_class( $opt['indicator_icon_shape'] ?? 'round' );
            $html .= '<span class="' . esc_attr( $marker_class ) . '" style="' . esc_attr( implode( ' ', $marker_style ) ) . '">' . Icons::get_svg( ( !empty( $opt['icon'] ) ? $opt['icon'] : Defaults::bar( 'icon' ) ), $marker_size ) . '</span>';
        }
        return [
            'html'    => $html,
            'style'   => $style,
            'classes' => $classes,
        ];
    }

    /**
     * The ring's width in pixels: Ring thickness, in percent of the circle size.
     *
     * @since	4.0.0
     */
    private static function ring_thickness( $opt ) {
        return round( (float) $opt['circle_size'] * (float) ($opt['ring_thickness'] ?? Defaults::bar( 'ring_thickness' )) / 100, 2 );
    }

    /**
     * The design preset that draws this circular bar as a ring, '' for Classic.
     *
     * @since	4.0.0
     */
    private static function circular_preset( $opt ) {
        $preset = Presets::sanitize( $opt['bar_preset'] ?? '' );
        return ( $preset && Presets::supports( $preset, 'circular' ) ? $preset : '' );
    }

    /**
     * A goal in the ring's center: its icon, the amount left and its label.
     *
     * @since	4.0.0
     */
    private function ring_goal_center( $goal ) {
        return '<span class="fsl-ring-icon">' . ($goal['icon'] ?? '') . '</span><span class="fsl-ring-big">' . Helper::convert_placeholders( '{remaining}', $goal['args'] ) . '</span><span class="fsl-ring-small">' . $goal['label'] . '</span>';
    }

}
