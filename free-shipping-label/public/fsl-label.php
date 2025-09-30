<?php

namespace Devnet\FSL\Frontend;

use Devnet\FSL\Includes\Defaults;
use Devnet\FSL\Includes\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class FSL_Label {
    /**
     * Free Shipping minimum amount/threshold.
     *
     * @since    2.6.0
     */
    private $free_shipping_min_amount;

    /**
     * Product Label options.
     * 
     */
    private $fsl_label_options;

    /**
     * Excluded categories.
     * 
     */
    private $excluded_categories;

    /**
     * Excluded products.
     * 
     */
    private $excluded_products;

    /**
     * Included shipping class.
     * 
     */
    private $include_shipping_class;

    public function __construct() {
        $options = DEVNET_FSL_OPTIONS['label'] ?? [];
        $enable_label = $options['enable_label'] ?? false;
        $label_over_image = $options['label_over_image'] ?? false;
        if ( !$enable_label ) {
            return;
        }
        add_action( 'wp_loaded', [$this, 'public_data'] );
        add_filter(
            'woocommerce_get_price_html',
            [$this, 'get_price_html'],
            99999,
            2
        );
    }

    /**
     * Get all reusable public data in one call.
     * 
     * currently used only for Product Labels.
     * 
     * @since	2.6.0
     */
    public function public_data() {
        $this->fsl_label_options = (DEVNET_FSL_OPTIONS['label'] ?? []) + Defaults::label();
        $this->free_shipping_min_amount = (float) Helper::get_free_shipping_min_amount();
        $this->include_shipping_class = DEVNET_FSL_OPTIONS['label']['include_shipping_class'] ?? '';
        $excluded = Helper::label_excluded( 'ids' );
        if ( isset( $excluded['category'] ) ) {
            $this->excluded_categories = array_flip( $excluded['category'] );
        }
        if ( isset( $excluded['product'] ) ) {
            $this->excluded_products = array_flip( $excluded['product'] );
        }
    }

    /**
     * Check conditions for displaying the product label.
     * 
     * @since	2.6.0
     */
    public function product_label_output( $product = null ) {
        if ( !$product ) {
            global $product;
        }
        if ( $product->is_virtual() || $product->is_downloadable() ) {
            return null;
        }
        $opt = $this->fsl_label_options;
        $show_on_single_simple_product = $opt['show_on_single_simple_product'] ?? false;
        $show_on_single_variable_product = $opt['show_on_single_variable_product'] ?? false;
        $show_on_single_variation = $opt['show_on_single_variation'] ?? false;
        $show_on_list_variable_products = $opt['show_on_list_variable_products'] ?? false;
        $show_on_list_simple_products = $opt['show_on_list_simple_products'] ?? false;
        $type = $product->get_type();
        if ( 'variable' === $type ) {
            $min_or_max = 'min';
            $_var_reg_price = $product->get_variation_regular_price( $min_or_max, true );
            $_var_sale_price = $product->get_variation_sale_price( $min_or_max, true );
            $price = ( $_var_sale_price ? $_var_sale_price : $_var_reg_price );
        } else {
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $price = ( $sale_price ? $sale_price : $regular_price );
        }
        if ( !empty( $this->include_shipping_class ) ) {
            $shipping_class_id = (int) $product->get_shipping_class_id();
            if ( (int) $this->include_shipping_class === $shipping_class_id ) {
                // Set to the highest value that will be much higher than free shipping threshold.
                $price = PHP_INT_MAX;
            }
        }
        $price = apply_filters( 'fsl_product_price', $price, $product );
        $label_html = '';
        if ( is_product() ) {
            if ( 'simple' === $type && $show_on_single_simple_product || 'variable' === $type && $show_on_single_variable_product || 'variation' === $type && $show_on_single_variation ) {
                $label_html = $this->product_label_html( $price );
            }
        } else {
            if ( 'simple' === $type && $show_on_list_simple_products || 'variable' === $type && $show_on_list_variable_products ) {
                $label_html = $this->product_label_html( $price );
            }
        }
        return $label_html;
    }

    /**
     * Product label html and styles.
     *
     * @since    2.0.0
     */
    public function product_label_html( $price = 0 ) {
        $amount_for_free_shipping = $this->free_shipping_min_amount;
        $fsl = '';
        if ( $price && $amount_for_free_shipping && $price >= $amount_for_free_shipping ) {
            $opt = $this->fsl_label_options;
            $text_color = $opt['text_color'] ?? Defaults::label( 'text_color' );
            $bg_color = $opt['bg_color'] ?? Defaults::label( 'bg_color' );
            $hide_border_shadow = $opt['hide_border_shadow'] ?? Defaults::label( 'hide_border_shadow' );
            $multilingual = $opt['multilingual'] ?? Defaults::label( 'multilingual' );
            $text = $opt['text'] ?? Defaults::label( 'text' );
            if ( $multilingual ) {
                $text = Defaults::label( 'text' );
            }
            $styles = '';
            if ( $text_color ) {
                $styles .= 'color:' . $text_color . ';';
            }
            if ( $bg_color ) {
                $styles .= 'background-color:' . $bg_color . ';';
            }
            if ( $hide_border_shadow ) {
                $styles .= 'box-shadow:none;';
            }
            $fsl .= '<span class="devnet_fsl-label" style="' . esc_attr( $styles ) . '">';
            $fsl .= esc_html( $text );
            $fsl .= '</span>';
        }
        return apply_filters( 'fsl_product_label_html', $fsl, $price );
    }

    /**
     * Add sufix (free shipping label) to product after the price.
     *
     * @since    2.0.0
     */
    public function get_price_html( $price_html, $product ) {
        if ( is_admin() && !wp_doing_ajax() ) {
            return $price_html;
        }
        $label_over_image = $this->fsl_label_options['label_over_image'] ?? false;
        // Show label as price sufix if not enabled label over image,
        // if it is enabled, show it only on the single product page,
        // but avoid any other products on the page (sliders, sidebars, etc.).
        // on single product page - page_id/queried_object_id must match with the product_id.
        // if (!$label_over_image || ($label_over_image && is_product() && $product->get_id() === get_queried_object_id())) {
        //     $price_html = $price_html . $this->product_label_output($product);
        // }
        if ( !$label_over_image || $label_over_image && is_product() && ($product->get_id() === get_queried_object_id() || $product->is_type( 'variation' ) && $product->get_parent_id() === get_queried_object_id()) ) {
            $price_html .= $this->product_label_output( $product );
        }
        return $price_html;
    }

}
