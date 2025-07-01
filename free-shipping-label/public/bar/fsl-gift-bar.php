<?php

namespace Devnet\FSL\Frontend\Bar;

use Devnet\FSL\Includes\Compatibility;
use Devnet\FSL\Includes\Defaults;
use Devnet\FSL\Includes\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Gift_Bar extends FSL_Bar {
    public $is_multilingual = false;

    public function __construct() {
        if ( defined( 'FSL_MODULE_GIFT_BAR' ) && FSL_MODULE_GIFT_BAR === false ) {
            return;
        }
        $this->is_multilingual = DEVNET_FSL_OPTIONS['general']['multilingual'] ?? false;
        $options = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        $enable = $options['enable_bar'] ?? false;
        $after_threshold = $options['after_threshold'] ?? '';
        $price_display = $options['price_display'] ?? 'label';
        if ( !$enable ) {
            return;
        }
        add_filter( 'fsl_progress_bar_setup_data', [$this, 'setup_data_for_gift_bar'] );
    }

    public function setup_data_for_gift_bar( $data ) {
        $gift_opt = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        $gift_opt = ( $gift_opt ? $gift_opt : [] );
        // if is empty string, ensure it is array.
        $enable = $gift_opt['enable_bar'] ?? false;
        $threshold = $gift_opt['threshold'] ?? 0;
        $display = $gift_opt['display'] ?? 'after';
        $inherit_pb = $gift_opt['inherit_progress_bar_settings'] ?? true;
        if ( !$enable || !$threshold ) {
            return $data;
        }
        $cart_subtotal = $data['cart_subtotal'] ?? null;
        $free_shipping_data = $data['modules']['free-shipping'] ?? [];
        $free_shipping_options = $free_shipping_data['options'] ?? [];
        $free_shipping_pass = $free_shipping_data['pass'] ?? false;
        $free_shipping_threshold = $free_shipping_data['threshold'] ?? false;
        $free_shipping_percent = $free_shipping_data['percent'] ?? 0;
        $free_shipping_reached = $free_shipping_options['qualified_message'] ?? '';
        $layout = $free_shipping_options['layout'] ?? 'list';
        $options = [
            'show_fsl_title'       => true,
            'show_fsl_description' => true,
        ];
        if ( $inherit_pb ) {
            $options = $free_shipping_options;
            $options['title'] = $gift_opt['title'] ?? '';
            $options['description'] = $gift_opt['description'] ?? '';
            $options['qualified_message'] = $gift_opt['qualified_message'] ?? '';
        } else {
            $gift_opt['name'] = 'gift_bar';
            $only_inheritable = true;
            $custom_options = $this->get_progress_bar_options( $gift_opt, $only_inheritable );
            // Override inheritable options with custom Gift Bar options.
            $options = array_replace( $free_shipping_options, $custom_options );
        }
        if ( empty( $free_shipping_data ) ) {
            $display = 'only';
            $options = $this->get_progress_bar_options( $gift_opt );
        }
        $options['label'] = $gift_opt['label'] ?? Defaults::gift_bar( 'label' );
        if ( $this->is_multilingual ) {
            $options['label'] = Defaults::gift_bar( 'label' );
            $options['title'] = Defaults::gift_bar( 'title' );
            $options['description'] = Defaults::gift_bar( 'description' );
            $options['qualified_message'] = Defaults::gift_bar( 'qualified_message' );
        }
        $calc = Helper::calculate_percentage( $threshold, $cart_subtotal );
        $percent = $calc['percent'];
        $remaining = $calc['remaining'];
        $pass = $percent === 100;
        // Set module name
        $options['module_name'] = 'gift-bar';
        $gift_bar_data = [
            'pass'             => $pass,
            'options'          => $options,
            'percent'          => $percent,
            'showing'          => 'gift-bar',
            'threshold'        => $threshold,
            'placeholder_args' => [
                'remaining' => $remaining,
                'threshold' => $threshold,
            ],
        ];
        if ( $pass ) {
            $gift_bar_data['reached']['qualified_message'] = $options['qualified_message'];
        }
        $setup_data = $data;
        $group = [
            'layout'          => $layout,
            'options'         => $options,
            'grouped_modules' => [
                'free-shipping' => $free_shipping_data,
                'gift-bar'      => $gift_bar_data,
            ],
            'percent'         => $percent,
        ];
        if ( $display === 'after' && $threshold >= $free_shipping_threshold ) {
            if ( $free_shipping_pass ) {
                unset($setup_data['modules']);
                $group['grouped_modules']['free-shipping']['reached']['qualified_message'] = $free_shipping_reached;
                $setup_data['modules']['group'] = $group;
            }
        }
        return $setup_data;
    }

    /**
     * Get Gift options.
     *
     * @since    3.1.0
     */
    public static function get_gift_bar_option( $option_name = '' ) {
        $options = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        if ( !$option_name ) {
            return $options;
        }
        return $options[$option_name] ?? null;
    }

    /**
     * Get Gift product.
     *
     * @since    3.1.0
     */
    private function get_gift_product( $return = 'id' ) {
        $gift_product = self::get_gift_bar_option( 'gift_product' );
        $product_id = intval( explode( '---', explode( '___', $gift_product ?? '' )[0] )[1] ?? '' );
        if ( !$product_id ) {
            return null;
        }
        if ( $return === 'id' ) {
            return $product_id;
        }
        $product = wc_get_product( $product_id );
        if ( !$product ) {
            return null;
        }
        if ( $return === 'product' ) {
            return $product;
        }
        if ( $return === 'price' ) {
            return $product->get_price();
        }
        return null;
    }

    /**
     * Check if qualified for the free gift.
     *
     * @since    3.0.0
     */
    public function gift_pass( $cart = null ) {
        if ( !$cart ) {
            return;
        }
        $cart_subtotal = $this->get_cart_subtotal();
        $threshold = self::get_gift_bar_option( 'threshold' );
        // Calculate the percentage
        $calc = Helper::calculate_percentage( $threshold, $cart_subtotal );
        $gift_pass = $calc['percent'] === 100;
        $gift_pass = apply_filters( 'fsl_gift_pass', $gift_pass, $cart );
        return $gift_pass;
    }

    /**
     * Free gift label in the cart/checkout and totals.
     *
     * @since    3.0.0
     */
    public function free_gift_label() {
        $label = self::get_gift_bar_option( 'label' );
        if ( $this->is_multilingual ) {
            $defaults = Defaults::gift_bar();
            $label = $defaults['label'];
        }
        return $label;
    }

    /**
     * Check cart and determine if gift product should be added or removed from the cart.
     *
     * @since    3.0.0
     */
    public function gift_product_in_cart( $cart ) {
        // Avoid adding the product during AJAX requests
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            return;
        }
        $gift_pass = $this->gift_pass( $cart );
        $gift_product = $this->get_gift_product( 'product' );
        $product_ids = $this->resolve_product_and_variation_id( $gift_product );
        $product_id = $product_ids['product_id'];
        $variation_id = $product_ids['variation_id'];
        // If no valid product ID, return
        if ( !$product_id ) {
            return;
        }
        // Check if the product is in the cart
        $product_in_cart = false;
        $product_cart_key = null;
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( $cart_item['product_id'] === $product_id && !empty( $cart_item['fsl_gift'] ) ) {
                $product_in_cart = true;
                $product_cart_key = $cart_item_key;
                break;
            }
        }
        // Remove or add the product based on the percentage
        if ( !$gift_pass && $product_in_cart ) {
            $cart->remove_cart_item( $product_cart_key );
        } elseif ( $gift_pass && !$product_in_cart ) {
            $args = [
                'price'    => 0,
                'fsl_gift' => true,
            ];
            /*
             * TODO: force product in cart, no mather if it is out of stock.
             */
            $cart->add_to_cart(
                $product_id,
                1,
                $variation_id,
                [],
                $args
            );
        }
    }

    /**
     * Resolve a WooCommerce product ID and variation ID from a given product input.
     *
     * @since 3.3.2
     */
    public function resolve_product_and_variation_id( $product ) {
        $output = [
            'product_id'   => 0,
            'variation_id' => 0,
        ];
        if ( !$product ) {
            return $output;
        }
        // If variation: set parent and itself
        if ( $product->is_type( 'variation' ) ) {
            $output['product_id'] = $product->get_parent_id();
            $output['variation_id'] = $product->get_id();
            return $output;
        }
        // If simple: product ID only
        if ( !$product->is_type( 'variable' ) ) {
            $output['product_id'] = $product->get_id();
            return $output;
        }
        // If variable: find variation
        $output['product_id'] = $product->get_id();
        $default_attributes = $product->get_default_attributes();
        $variation_ids = $product->get_children();
        if ( !empty( $default_attributes ) ) {
            foreach ( $variation_ids as $vid ) {
                $variation = wc_get_product( $vid );
                if ( !$variation || !$variation->is_in_stock() ) {
                    continue;
                }
                $match = true;
                foreach ( $default_attributes as $name => $value ) {
                    if ( sanitize_title( $variation->get_attribute( $name ) ) !== $value ) {
                        $match = false;
                        break;
                    }
                }
                if ( $match ) {
                    $output['variation_id'] = $vid;
                    return $output;
                }
            }
        }
        // Fallback: first in-stock variation
        foreach ( $variation_ids as $vid ) {
            $variation = wc_get_product( $vid );
            if ( $variation && $variation->is_in_stock() ) {
                $output['variation_id'] = $vid;
                break;
            }
        }
        return $output;
    }

    /**
     * Gift product price should be 0.
     *
     * @since    3.0.0
     */
    public function recalculate_gift_price( $cart ) {
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( !empty( $cart_item['fsl_gift'] ) ) {
                $cart_item['data']->set_price( 0 );
                // Set quantity to `1`, and `false` for recalculating totals to avoid infinite loop.
                $cart->set_quantity( $cart_item_key, 1, false );
            }
        }
    }

    /**
     * Prevent applying coupons on gift product.
     *
     * @since 3.1.0
     */
    public function prevent_coupon_on_gift_product( $valid, $product, $coupon ) {
        $product_id = $this->get_gift_product( 'id' );
        // If the current product is the gift product, disallow coupon
        if ( $product_id && $product->get_id() == $product_id ) {
            return false;
        }
        return $valid;
    }

    /**
     * We don't want any quantity info or input field for gift product.
     *
     * @since    3.0.0
     */
    public function gift_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
        if ( !empty( $cart_item['fsl_gift'] ) ) {
            $product_quantity = '';
        }
        return $product_quantity;
    }

    /**
     * Name under the product title in the cart.
     *
     * @since    3.0.0
     */
    public function gift_cart_item_name( $_product_title, $cart_item, $cart_item_key ) {
        if ( !empty( $cart_item['fsl_gift'] ) ) {
            $_product_title .= '<p>' . esc_html( $this->free_gift_label() ) . '</p>';
        }
        return $_product_title;
    }

    /**
     * Add meta to order item.
     *
     * @since    3.0.0
     */
    public function add_order_item_meta(
        $item,
        $cart_item_key,
        $values,
        $order
    ) {
        if ( !empty( $values['fsl_gift'] ) ) {
            $item->add_meta_data( '_fsl_gift', true );
        }
    }

    /**
     * Gift product price label - "value".
     *
     * @since    3.0.0
     */
    public function gift_cart_item_price_label( $price, $cart_item, $cart_item_key ) {
        if ( !empty( $cart_item['fsl_gift'] ) ) {
            $price = '<span class="amount">' . esc_html( $this->free_gift_label() ) . '</span>';
            if ( self::get_gift_bar_option( 'price_display' ) === 'crossed' ) {
                $regular_price = wc_get_price_to_display( $cart_item['data'], [
                    'price' => $cart_item['data']->get_regular_price(),
                ] );
                $zero_price = wc_price( 0 );
                $price = '<del>' . wc_price( $regular_price ) . '</del> <ins>' . $zero_price . '</ins>';
            }
        }
        return $price;
    }

    /**
     * Gift product subtotal label - "value".
     *
     * @since    3.0.0
     */
    public function order_gift_item( $subtotal, $item, $order ) {
        if ( !empty( $item['_fsl_gift'] ) ) {
            $subtotal = '<span class="amount">' . esc_html( $this->free_gift_label() ) . '</span>';
            if ( self::get_gift_bar_option( 'price_display' ) === 'crossed' ) {
                $product = $item->get_product();
                if ( is_null( $product ) ) {
                    return $subtotal;
                }
                $regular_price = wc_get_price_to_display( $product, [
                    'price' => $product->get_regular_price(),
                ] );
                $zero_price = wc_price( 0 );
                $subtotal = '<del>' . wc_price( $regular_price ) . '</del> <ins>' . $zero_price . '</ins>';
            }
        }
        return $subtotal;
    }

    /**
     * Gift added as additional Fee info.
     *
     * @since    3.0.0
     */
    public function add_gift_as_fee( $cart ) {
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            return;
        }
        if ( $this->gift_pass( $cart ) ) {
            WC()->session->set( 'fsl_gift', true );
            $cart->add_fee(
                esc_html( $this->free_gift_label() ),
                0,
                false,
                ''
            );
        } else {
            WC()->session->set( 'fsl_gift', null );
        }
    }

    /**
     * Order meta when we have free gift in the order.
     *
     * @since    3.0.0
     */
    public function add_order_meta( $order, $data ) {
        if ( WC()->session->get( 'fsl_gift' ) ) {
            $order->add_meta_data( 'fsl_gift', true );
        }
    }

    /**
     * Add gift to order total - set price to 0.
     *
     * @since    3.0.0
     */
    public function add_gift_to_item_totals( $total_rows, $order ) {
        if ( $order->get_meta( 'fsl_gift' ) ) {
            // Insert the custom fee row at the beginning
            $total_rows = [
                'fsl_gift' => [
                    'label' => $this->free_gift_label(),
                    'value' => wc_price( 0 ),
                ],
            ] + $total_rows;
        }
        return $total_rows;
    }

}
