<?php

namespace Devnet\FSL\Includes;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Progress Bar positions on the block-based Cart, Checkout and Mini-Cart.
 *
 * Each setting value names a WooCommerce block (the Block Hooks anchor) and where the
 * Progress Bar goes relative to it: before, after, first_child or last_child.
 * The values share the classic position settings; Free lists them disabled.
 *
 * @since	4.0.0
 */
class Placement {
    /**
     * Setting value => [anchor block, relation, label].
     */
    public static function positions( $page ) {
        $positions = [
            'cart'     => [
                'block_above_cart'             => ['woocommerce/cart', 'before', __( 'Above the cart', 'free-shipping-label' )],
                'block_top_of_product_list'    => ['woocommerce/cart-items-block', 'first_child', __( 'Top of the product list', 'free-shipping-label' )],
                'block_below_product_list'     => ['woocommerce/cart-line-items-block', 'after', __( 'Below the product list', 'free-shipping-label' )],
                'block_top_of_totals'          => ['woocommerce/cart-totals-block', 'first_child', __( 'Top of the totals sidebar', 'free-shipping-label' )],
                'block_after_totals_heading'   => ['woocommerce/cart-order-summary-heading-block', 'after', __( 'After the "Cart totals" heading', 'free-shipping-label' )],
                'block_before_totals'          => ['woocommerce/cart-order-summary-totals-block', 'before', __( 'Before the totals', 'free-shipping-label' )],
                'block_before_checkout_button' => ['woocommerce/proceed-to-checkout-block', 'before', __( 'Before "Proceed to checkout"', 'free-shipping-label' )],
                'block_after_checkout_button'  => ['woocommerce/proceed-to-checkout-block', 'after', __( 'After "Proceed to checkout"', 'free-shipping-label' )],
                'block_below_cart'             => ['woocommerce/cart', 'after', __( 'Below the cart', 'free-shipping-label' )],
            ],
            'checkout' => [
                'block_above_checkout'         => ['woocommerce/checkout', 'before', __( 'Above the checkout', 'free-shipping-label' )],
                'block_top_of_form'            => ['woocommerce/checkout-fields-block', 'first_child', __( 'Top of the checkout form', 'free-shipping-label' )],
                'block_after_shipping_options' => ['woocommerce/checkout-shipping-methods-block', 'after', __( 'After the shipping options', 'free-shipping-label' )],
                'block_before_payment'         => ['woocommerce/checkout-payment-block', 'before', __( 'Before the payment options', 'free-shipping-label' )],
                'block_before_place_order'     => ['woocommerce/checkout-actions-block', 'before', __( 'Before "Place order"', 'free-shipping-label' )],
                'block_after_place_order'      => ['woocommerce/checkout-actions-block', 'after', __( 'After "Place order"', 'free-shipping-label' )],
                'block_top_of_summary'         => ['woocommerce/checkout-totals-block', 'first_child', __( 'Top of the order summary column', 'free-shipping-label' )],
                'block_below_checkout'         => ['woocommerce/checkout', 'after', __( 'Below the checkout', 'free-shipping-label' )],
            ],
            'minicart' => [
                'block_after_minicart_title'     => ['woocommerce/mini-cart-title-block', 'after', __( 'After the title', 'free-shipping-label' )],
                'block_before_minicart_products' => ['woocommerce/mini-cart-items-block', 'first_child', __( 'Before the products', 'free-shipping-label' )],
                'block_after_minicart_products'  => ['woocommerce/mini-cart-items-block', 'last_child', __( 'After the products', 'free-shipping-label' )],
                'block_before_minicart_subtotal' => ['woocommerce/mini-cart-footer-block', 'before', __( 'Before the subtotal', 'free-shipping-label' )],
                'block_below_minicart_buttons'   => ['woocommerce/mini-cart-footer-block', 'after', __( 'Below the buttons', 'free-shipping-label' )],
            ],
        ];
        return $positions[$page] ?? [];
    }

}
