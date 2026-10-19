<?php

namespace Devnet\FSL\Frontend\Bar;

use Devnet\FSL\Includes\Compatibility;
use Devnet\FSL\Includes\Defaults;
use Devnet\FSL\Includes\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Gift_Bar extends FSL_Bar {
    public function __construct() {
        if ( !self::module_active() ) {
            return;
        }
        $options = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        $enable = $options['enable_bar'] ?? false;
        $after_threshold = $options['after_threshold'] ?? '';
        $price_display = $options['price_display'] ?? 'label';
        $auto_add = false;
        // Auto-add is off (mode changed, Gift Bar disabled, no license): drop gift items left in carts.
        if ( !$auto_add ) {
            add_filter(
                'woocommerce_pre_remove_cart_item_from_session',
                [$this, 'remove_gift_from_session'],
                10,
                3
            );
        }
        if ( !$enable ) {
            return;
        }
        add_filter( 'fsl_progress_bar_setup_data', [$this, 'setup_data_for_gift_bar'] );
    }

    public function setup_data_for_gift_bar( $data ) {
        $gift_opt = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        $gift_opt = ( $gift_opt ? $gift_opt : [] );
        // if is empty string, ensure it is array.
        $gift_opt['name'] = 'gift_bar';
        $enable = $gift_opt['enable_bar'] ?? false;
        $threshold = $gift_opt['threshold'] ?? 0;
        $display = $gift_opt['display'] ?? 'after';
        // A non-numeric threshold saved before 4.0.0 would throw a TypeError in calculate_percentage().
        if ( !$enable || !$threshold || !is_numeric( $threshold ) ) {
            return $data;
        }
        $cart_subtotal = $data['cart_subtotal'] ?? null;
        $free_shipping_data = $data['modules']['free-shipping'] ?? [];
        $free_shipping_options = $free_shipping_data['options'] ?? [];
        $free_shipping_pass = $free_shipping_data['pass'] ?? false;
        $free_shipping_threshold = $free_shipping_data['threshold'] ?? false;
        $free_shipping_percent = $free_shipping_data['percent'] ?? 0;
        $free_shipping_reached = $free_shipping_options['qualified_message'] ?? '';
        // An empty text shows the translated default.
        foreach ( [
            'label',
            'title',
            'description',
            'qualified_message'
        ] as $text ) {
            if ( ($gift_opt[$text] ?? '') === '' ) {
                $gift_opt[$text] = Defaults::gift_bar( $text );
            }
        }
        $options = $this->get_progress_bar_options( self::inherit_icon_settings( $gift_opt, Defaults::gift_bar( 'inherit_icon_settings' ) ) );
        $options['label'] = $gift_opt['label'];
        // The Progress Bar's layout, also in a zone without free shipping.
        $layout = $free_shipping_options['layout'] ?? $options['layout'] ?? 'list';
        $bar_type = $options['bar_type'] ?? 'linear';
        // Classic's one circle shows the free shipping icon until free shipping is reached; the ring layouts show each
        // goal's own icon.
        if ( $bar_type === 'circular' && $free_shipping_percent < 100 && !in_array( $layout, self::RING_LAYOUTS, true ) ) {
            // No free-shipping module (no threshold, no address, local pickup): no icon, as before, without the warning.
            $options['icon'] = $free_shipping_options['icon'] ?? '';
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
            'display'          => $display,
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
        // Free shipping as the last goal: the bar shows its progress.
        if ( $free_shipping_threshold >= $threshold ) {
            $group['percent'] = $free_shipping_percent;
            $group['grouped_modules'] = array_reverse( $group['grouped_modules'] );
        }
        if ( $free_shipping_pass ) {
            $group['grouped_modules']['free-shipping']['reached']['qualified_message'] = $free_shipping_reached;
        }
        // After the previous goal, as the Discount bars: once free shipping is reached when it comes first, else at once
        // (the gift comes first, or the zone has no free shipping). The gift then extends the bar.
        if ( $display === 'after' && (!$free_shipping_data || $free_shipping_threshold > $threshold || $free_shipping_pass) ) {
            $setup_data['modules'] = [
                'group' => $group,
            ];
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
    public static function get_gift_product( $return = 'id' ) {
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
        // Empty threshold = gift not configured; 0 = gift with every order.
        if ( !is_numeric( $threshold ) ) {
            return apply_filters( 'fsl_gift_pass', false, $cart );
        }
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
    public static function free_gift_label() {
        $label = self::get_gift_bar_option( 'label' );
        // An empty label shows the translated default.
        return ( $label === null || $label === '' ? Defaults::gift_bar( 'label' ) : $label );
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
        // Cart items store a variation's parent ID. The variation itself is resolved only when the gift is added:
        // for a variable gift that loads variations, which would otherwise run on every totals calculation.
        $product_id = ( $gift_product ? ( $gift_product->is_type( 'variation' ) ? $gift_product->get_parent_id() : $gift_product->get_id() ) : 0 );
        $product_in_cart = false;
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( empty( $cart_item['fsl_gift'] ) ) {
                continue;
            }
            // Keep one gift of the current gift product while the cart qualifies.
            if ( $gift_pass && $product_id && !$product_in_cart && $cart_item['product_id'] === $product_id ) {
                $product_in_cart = true;
                continue;
            }
            // Not qualified anymore, gift product changed, or a duplicate.
            $cart->remove_cart_item( $cart_item_key );
        }
        if ( $gift_pass && $product_id && !$product_in_cart ) {
            $variation_id = $this->resolve_product_and_variation_id( $gift_product )['variation_id'];
            $args = [
                'price'    => 0,
                'fsl_gift' => true,
            ];
            // A gift WooCommerce can't add (out of stock, not purchasable, no variation) is skipped quietly:
            // this runs on every totals calculation, and each failed add_to_cart() leaves an error notice.
            $notices = wc_get_notices();
            if ( !$cart->add_to_cart(
                $product_id,
                1,
                $variation_id,
                [],
                $args
            ) ) {
                wc_set_notices( $notices );
            }
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
                $attributes = $variation->get_attributes();
                foreach ( $default_attributes as $name => $value ) {
                    // Stored values like the defaults (slugs for global attributes); get_attribute() returns term names.
                    if ( ($attributes[$name] ?? '') !== $value ) {
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
                // Block Cart and Checkout cross out the regular price and add a "Save" badge whenever it's
                // higher than the price; only the "crossed" display wants that. The classic cart doesn't read it.
                if ( 'crossed' !== self::get_gift_bar_option( 'price_display' ) ) {
                    $cart_item['data']->set_regular_price( 0 );
                }
                // Set quantity to `1`, and `false` for recalculating totals to avoid infinite loop.
                $cart->set_quantity( $cart_item_key, 1, false );
            }
        }
    }

    /**
     * Drop gift items while auto-add is off, so they don't stay in carts at full price.
     *
     * @since    4.0.0
     */
    public function remove_gift_from_session( $remove, $key, $values ) {
        return $remove || !empty( $values['fsl_gift'] );
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
     * Block Cart and Mini-Cart (Store API): the gift's quantity can't be changed, as in the classic cart.
     *
     * @since    4.0.0
     */
    public function gift_store_api_quantity_editable( $editable, $product, $cart_item ) {
        return ( empty( $cart_item['fsl_gift'] ) ? $editable : false );
    }

    /**
     * Store API: with a maximum of 1, a read-only gift rejects any higher quantity ("cannot be changed").
     *
     * @since    4.0.0
     */
    public function gift_store_api_quantity_maximum( $maximum, $product, $cart_item ) {
        return ( empty( $cart_item['fsl_gift'] ) ? $maximum : 1 );
    }

    /**
     * Block Cart, Checkout and Mini-Cart: the "Free Gift" label as the item's first detail line.
     * `hidden` keeps it out of the classic cart, which shows the label under the name (gift_cart_item_name());
     * the Store API replaces `hidden` with `__experimental_woocommerce_blocks_hidden`.
     *
     * @since    4.0.0
     */
    public function gift_item_data_for_blocks( $item_data, $cart_item ) {
        if ( !empty( $cart_item['fsl_gift'] ) ) {
            array_unshift( $item_data, [
                'key'                                      => '',
                'value'                                    => esc_html( self::free_gift_label() ),
                'className'                                => 'wc-block-components-product-details__fsl-gift',
                'hidden'                                   => true,
                '__experimental_woocommerce_blocks_hidden' => false,
            ] );
        }
        return $item_data;
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
     * Whether the built-in Gift Bar runs. An add-on turns it off (storefront, orders, settings tab, preview) by
     * defining FSL_MODULE_GIFT_BAR as false.
     *
     * @since    4.0.0
     */
    public static function module_active() {
        return !(defined( 'FSL_MODULE_GIFT_BAR' ) && FSL_MODULE_GIFT_BAR === false);
    }

    /**
     * Gift product subtotal label - "value".
     *
     * @since    3.0.0
     */
    public static function order_gift_item( $subtotal, $item, $order ) {
        if ( !empty( $item['_fsl_gift'] ) && self::get_gift_bar_option( 'price_display' ) !== 'zero' ) {
            $subtotal = '<span class="amount">' . esc_html( self::free_gift_label() ) . '</span>';
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
            // Stored as typed: WooCommerce escapes fee names on output.
            $cart->add_fee(
                $this->free_gift_label(),
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
    public static function add_gift_to_item_totals( $total_rows, $order ) {
        if ( $order->get_meta( 'fsl_gift' ) ) {
            // Insert the custom fee row at the beginning
            $total_rows = [
                'fsl_gift' => [
                    'label' => self::free_gift_label(),
                    'value' => wc_price( 0 ),
                ],
            ] + $total_rows;
        }
        return $total_rows;
    }

}
