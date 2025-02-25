<?php

namespace Devnet\FSL\Includes;

use Devnet\FSL\Includes\Helper;
class Compatibility {
    public static function get_custom_shipping_min_amount( $chosen_shipping_id, $method = null ) {
        /**
         * Try to get threshold from WAFS (if applicable)
         * 
         */
        $amount = self::wafs_threshold( null );
        if ( !is_null( $amount ) ) {
            return $amount;
        }
        /**
         * Check if the method is Flexible Shipping
         * 
         */
        if ( $method && is_object( $method ) ) {
            if ( Helper::starts_with( $method->id, 'flexible_shipping' ) ) {
                $chosen_shipping_id = $method->id . ':' . $method->instance_id;
            }
        }
        if ( Helper::starts_with( $chosen_shipping_id, 'flexible_shipping' ) ) {
            $amount = self::get_flexible_shipping_method_min_amount( $chosen_shipping_id );
        }
        return $amount;
    }

    /**
     * Get information about shipping method.
     *
     * @package     Flexible Shipping                               
     */
    public static function get_flexible_shipping_method_min_amount( $shipping_id ) {
        $option_name = 'woocommerce_' . str_replace( ':', '_', $shipping_id ) . '_settings';
        $option = get_option( $option_name );
        $amount = ( isset( $option['method_free_shipping'] ) ? $option['method_free_shipping'] : null );
        return $amount;
    }

    /**
     * Check for free shipping subtotal (threshold),
     * only if there are matched conditions.
     * 
     * @package     Advanced Free Shipping
     */
    public static function wafs_threshold( $amount ) {
        if ( !function_exists( 'wafs_get_rates' ) && !function_exists( 'wpc_match_conditions' ) ) {
            return $amount;
        }
        $methods = wafs_get_rates();
        $matched_methods = false;
        $thresholds = [];
        foreach ( $methods as $method ) {
            $condition_groups = get_post_meta( $method->ID, '_wafs_shipping_method_conditions', true );
            // Ensure condition groups is an array to avoid potential warnings
            if ( !is_array( $condition_groups ) ) {
                continue;
            }
            $subtotal_value = null;
            // Loop through the condition groups
            foreach ( $condition_groups as &$inner_array ) {
                foreach ( $inner_array as $inner_key => $item ) {
                    if ( isset( $item['condition'] ) && $item['condition'] === 'subtotal' ) {
                        $subtotal_value = $item['value'];
                        // Store the value
                        unset($inner_array[$inner_key]);
                        // Remove the item
                    }
                }
            }
            $thresholds[$method->ID] = $subtotal_value;
            // Check if conditions match
            if ( wpc_match_conditions( $condition_groups, [
                'context' => 'wafs',
            ] ) ) {
                $matched_methods = $method->ID;
            }
        }
        if ( $matched_methods && isset( $thresholds[$matched_methods] ) ) {
            $amount = $thresholds[$matched_methods];
        }
        return $amount;
    }

}
