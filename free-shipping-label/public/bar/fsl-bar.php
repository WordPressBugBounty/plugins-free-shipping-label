<?php

namespace Devnet\FSL\Frontend\Bar;

use Devnet\FSL\Includes\Defaults;
use Devnet\FSL\Includes\Helper;
use Devnet\FSL\Includes\Icons;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class FSL_Bar {
    public $is_multilingual = false;

    private $inheritable = [
        'zero_shipping',
        'title',
        'description',
        'qualified_message',
        'show_qualified_message',
        'show_full_progress_bar',
        'hide_border_shadow',
        'disable_animation',
        'disabled_animations',
        'bar_border_color',
        'bar_bg_color',
        'bar_inner_color',
        'bar_height',
        'bar_type',
        'indicator_icon',
        'indicator_icon_size',
        'indicator_icon_shape',
        'indicator_icon_bg_color',
        'circle_size',
        'inside_circle',
        'icon',
        'icon_color',
        'circle_bg_color',
        'text_color',
        'box_bg_color',
        'box_max_width',
        'box_alignment',
        'center_text',
        'bar_radius',
        'remove_bar_stripes'
    ];

    public function __construct( $skip_hooks = false ) {
        $this->is_multilingual = DEVNET_FSL_OPTIONS['general']['multilingual'] ?? false;
        if ( $skip_hooks ) {
            return;
        }
        $options = DEVNET_FSL_OPTIONS['progress_bar'] ?? [];
        $enable = $options['enable_bar'] ?? false;
        $show_on_cart = $options['show_on_cart'] ?? false;
        $show_on_minicart = $options['show_on_minicart'] ?? false;
        $show_on_checkout = $options['show_on_checkout'] ?? false;
        $cart_hook = 'woocommerce_proceed_to_checkout';
        $checkout_hook = 'woocommerce_review_order_before_submit';
        $minicart_hook = 'woocommerce_widget_shopping_cart_before_buttons';
        if ( !$enable ) {
            return;
        }
        if ( $show_on_cart ) {
            add_action( $cart_hook, [$this, 'fsl_bar_cart'], 10 );
        }
        if ( $show_on_minicart ) {
            add_action( $minicart_hook, [$this, 'fsl_bar_minicart'] );
        }
        if ( $show_on_checkout ) {
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
        $this->progress_bar( [
            'updatable' => $is_updatable,
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

    public function get_progress_bar_options( $args = [], $only_inheritable = false ) {
        $options = (DEVNET_FSL_OPTIONS['progress_bar'] ?? []) + Defaults::bar();
        $opt = [];
        foreach ( $options as $name => $value ) {
            $opt[$name] = ( isset( $value ) ? $value : Defaults::bar( $name ) );
        }
        $opt['name'] = 'progress_bar';
        $opt['show_fsl_title'] = true;
        $opt['show_fsl_description'] = true;
        $opt['label'] = esc_html__( 'Free Shipping', 'free-shipping-label' );
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
        if ( $this->is_multilingual ) {
            $opt['title'] = Defaults::bar( 'title' );
            $opt['description'] = Defaults::bar( 'description' );
            $opt['qualified_message'] = Defaults::bar( 'qualified_message' );
        }
        // Save Layout to variable.
        $layout = $opt['layout'] ?? Defaults::bar( 'layout' );
        if ( !in_array( $layout, ['list', 'list_alt'], true ) ) {
            $opt['layout'] = Defaults::bar( 'layout' );
        }
        if ( $only_inheritable ) {
            // Filter out non-inheritable options.
            foreach ( $opt as $key => $value ) {
                if ( !in_array( $key, $this->inheritable ) ) {
                    unset($opt[$key]);
                }
            }
        }
        return $opt;
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
    public function get_cart_subtotal() {
        $cart = WC()->cart;
        if ( !$cart ) {
            return;
        }
        $cart_subtotal = $cart->get_displayed_subtotal();
        $discount = $cart->get_discount_total();
        $discount_tax = $cart->get_discount_tax();
        $price_including_tax = $cart->display_prices_including_tax();
        $price_decimal = wc_get_price_decimals();
        if ( $price_including_tax ) {
            $cart_subtotal = round( $cart_subtotal - ($discount + $discount_tax), $price_decimal );
        } else {
            $cart_subtotal = round( $cart_subtotal - $discount, $price_decimal );
        }
        return apply_filters( 'fsl_get_cart_subtotal', $cart_subtotal, $cart );
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
        $amount_for_free_shipping = Helper::get_free_shipping_min_amount();
        // Replace comma with a dot
        if ( is_string( $amount_for_free_shipping ) ) {
            $amount_for_free_shipping = str_replace( ',', '.', $amount_for_free_shipping );
        }
        if ( !$amount_for_free_shipping || !is_numeric( $amount_for_free_shipping ) ) {
            return;
        }
        $amount_for_free_shipping = (float) $amount_for_free_shipping;
        // show_shipping_before_address
        if ( !WC()->cart->show_shipping() ) {
            return;
        }
        $chosen_shipping_method = Helper::chosen_shipping_method();
        $is_local_pickup = Helper::starts_with( $chosen_shipping_method, 'local_pickup' );
        $fs_instance = Helper::$free_shipping_instance;
        $fs_requires = ( isset( $fs_instance['requires'] ) ? $fs_instance['requires'] : '' );
        if ( $is_local_pickup && !$opt['local_pickup'] ) {
            return [
                'placeholder' => true,
            ];
        }
        if ( !$opt['enable_bar'] ) {
            return;
        }
        $calc = Helper::calculate_percentage( $amount_for_free_shipping, $cart_subtotal );
        $percent = $calc['percent'];
        $remaining = $calc['remaining'];
        $cart_reached_threshold = $percent === 100;
        $free_shipping_pass = $cart_reached_threshold;
        /**
         * Check shipping cost only if Zero Shipping Cost is allowed.
         * In most cases we don't care about shipping cost, 
         * but some shipping classes use 0,00 shipping value for free shipping.
         * Skip if currently chosen local pickup as shipping method.
         */
        if ( $chosen_shipping_method && $opt['zero_shipping'] && WC()->cart->get_shipping_total() == 0 && !$is_local_pickup ) {
            $free_shipping_pass = true;
        }
        if ( Helper::is_free_shipping_coupon_applied() ) {
            $free_shipping_pass = true;
            if ( $fs_requires === 'coupon' ) {
                $free_shipping_pass = true;
            }
            if ( $fs_requires === 'both' && $cart_reached_threshold ) {
                $free_shipping_pass = true;
            }
        } else {
            if ( $fs_requires === 'coupon' ) {
                return;
            }
            if ( $fs_requires === 'both' ) {
                $free_shipping_pass = false;
                if ( $cart_reached_threshold ) {
                    $percent = 100;
                    $opt['description'] = esc_html__( 'Waiting for Free Shipping coupon', 'free-shipping-label' );
                }
            }
        }
        return [
            'pass'             => $free_shipping_pass,
            'options'          => $opt,
            'percent'          => $percent,
            'reached'          => [],
            'threshold'        => $amount_for_free_shipping,
            'placeholder_args' => [
                'remaining' => $remaining,
                'threshold' => $amount_for_free_shipping,
            ],
            'cart_subtotal'    => $cart_subtotal,
        ];
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
            // $this->fsl_bar_placeholder(true);
            // return;
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
        echo '<div class="fsl-wrapper" data-updatable="' . esc_attr( $is_updatable ) . '">';
        foreach ( $setup_data['modules'] as $module => $module_data ) {
            echo $this->fsl_progress_bar_html( $module_data );
        }
        echo '</div>';
    }

    /**
     * Output progress bar html.
     *
     * @since    3.0.0
     */
    public function fsl_progress_bar_html( $args ) {
        $args = apply_filters( 'fsl_progress_bar_args', $args );
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
            $title = $options['title'] ?? '';
            $description = $options['description'] ?? '';
            $label = $options['label'] ?? '';
            $show_qualified_message = $options['show_qualified_message'] ?? false;
            $indicator_icon = $options['indicator_icon'] ?? false;
            $threshold_percentage = $threshold / $highest_threshold * 100;
            $text = [
                'qualified_message' => $this->get_fsl_qualified_message_html( $reached, $placeholder_args ),
                'title'             => $this->get_fsl_title_html( $title, $placeholder_args ),
                'description'       => $this->get_fsl_description_html( $description, $placeholder_args ),
            ];
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
                if ( $threshold_percentage === 100 ) {
                    $opt['bar_width_adjust'] = $indicator_icon_size / 2;
                }
            } elseif ( $is_threshold_bubbles_layout ) {
                $opt['threshold_indicators'][$module_name] = [
                    'threshold'            => $threshold,
                    'highest_threshold'    => $highest_threshold,
                    'threshold_percentage' => $threshold_percentage,
                    'focused'              => $focused_module === $module_name,
                    'text'                 => $text,
                ];
            }
            $show_full_progress_bar = $options['show_full_progress_bar'] ?? false;
            if ( $bar_type === 'linear' && !in_array( $layout, ['list', 'list_alt'], true ) ) {
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
     * Build vertical list.
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
        if ( $layout === 'list' && !$is_focused ) {
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
        $text = Helper::convert_placeholders( $text, $placeholder_args );
        // escape HTML
        $text = esc_html( $text );
        return '<h4 class="fsl-title title">' . $text . '</h4>';
    }

    /**
     * Output empty placeholder.
     *
     * @since    2.6.10
     */
    public static function fsl_bar_placeholder( $should_echo = false, $updatable = true ) {
        $placeholder = '<div class="fsl-wrapper" data-updatable="' . esc_attr( $updatable ) . '"><div class="devnet_fsl-free-shipping fsl-placeholder fsl-flat"></div></div>';
        $output = apply_filters( 'fsl_progress_bar_placeholder_html', $placeholder );
        if ( $should_echo ) {
            echo $output;
        } else {
            return $output;
        }
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
        $html .= '<span class="fsl-progress-amount progress-amount" style="' . esc_attr( implode( ' ', $style_pb_amount ) ) . '"></span>';
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
        return [
            'html'    => $html,
            'style'   => $style,
            'classes' => $classes,
        ];
    }

}
