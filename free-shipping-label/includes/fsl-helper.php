<?php

namespace Devnet\FSL\Includes;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Helper {
    /**
     * Save free shipping instance.
     */
    static $free_shipping_instance = [];

    public function __construct() {
    }

    /**
     * Check if string starts with string.
     *
     * @since    1.0.0
     * @return   boolean
     */
    static function starts_with( $string, $start_string ) {
        if ( !$string ) {
            return;
        }
        $len = strlen( $start_string );
        return substr( $string, 0, $len ) === $start_string;
    }

    /**
     * Get chosen shipping method.
     *
     * @since    2.1.0
     */
    static function chosen_shipping_method() {
        $wc_session = ( isset( WC()->session ) ? WC()->session : null );
        if ( !$wc_session ) {
            return;
        }
        $chosen_methods = $wc_session->get( 'chosen_shipping_methods' );
        if ( !$chosen_methods ) {
            return null;
        }
        $chosen_shipping_id = ( $chosen_methods ? $chosen_methods[0] : '' );
        return $chosen_shipping_id;
    }

    /**
     * Get minimal amount for free shipping.
     *
     * @since    1.0.0
     * @return   number  
     */
    static function get_free_shipping_min_amount() {
        $amount = null;
        $only_virtual_products_in_cart = self::only_virtual_products();
        $general_options = DEVNET_FSL_OPTIONS['general'] ?? [];
        $initial_zone = $general_options['initial_zone'] ?? '1';
        $enable_custom_threshold = $general_options['enable_custom_threshold'] ?? false;
        $custom_threshold = $general_options['custom_threshold'] ?? $amount;
        $custom_threshold_per_method = $general_options['custom_threshold_per_method'] ?? [];
        $chosen_shipping_id = self::chosen_shipping_method();
        /**
         * Custom threshold check.
         * 
         */
        if ( $enable_custom_threshold && !$only_virtual_products_in_cart ) {
            $amount = $custom_threshold;
            return apply_filters( 'fsl_min_amount', $amount );
        }
        /**
         * Third-part shipping methods check.
         * First check by chosen shipping method.
         */
        $amount = Compatibility::get_custom_shipping_min_amount( $chosen_shipping_id );
        if ( $amount !== null ) {
            if ( $only_virtual_products_in_cart ) {
                $amount = null;
            }
            return apply_filters( 'fsl_min_amount', $amount );
        }
        /**
         * Standard WooCommerce Shipping methods check.
         * 
         */
        $amount = null;
        $cart = WC()->cart;
        if ( $cart ) {
            $packages = $cart->get_shipping_packages();
            $package = reset( $packages );
            $zone = wc_get_shipping_zone( $package );
            $known_customer = self::destination_info_exists( $package );
            if ( !$known_customer && $initial_zone || $initial_zone == 0 ) {
                $init_zone = \WC_Shipping_Zones::get_zone_by( 'zone_id', $initial_zone );
                // Check if initial zone still exists.
                $zone = ( $init_zone ? $init_zone : $zone );
            }
            // $cache_key = 'fsl_min_amount_zone_' . $zone->get_id();
            // $amount = get_transient($cache_key);
            // if (false === $amount) {
            foreach ( $zone->get_shipping_methods() as $key => $method ) {
                if ( $method->id === 'free_shipping' ) {
                    $instance = ( isset( $method->instance_settings ) ? $method->instance_settings : null );
                    self::$free_shipping_instance = $instance;
                    $min_amount_key = apply_filters( 'fsl_free_shipping_instance_key', 'min_amount' );
                    $amount = ( isset( $instance[$min_amount_key] ) ? $instance[$min_amount_key] : null );
                    // If filter fails, go back to default 'min_amount' key.
                    if ( !$amount && isset( $instance['min_amount'] ) ) {
                        $amount = $instance['min_amount'];
                    }
                    break;
                }
                // Run compatibility checks
                $amount = Compatibility::get_custom_shipping_min_amount( $method->id, $method );
            }
            //     set_transient($cache_key, $amount, HOUR_IN_SECONDS);
            // }
        }
        if ( $only_virtual_products_in_cart ) {
            $amount = null;
        }
        return apply_filters( 'fsl_min_amount', $amount );
    }

    /**
     * Check if only a virtual product is in the cart.
     * 
     * @since   2.6.0 
     * 
     */
    static function only_virtual_products() {
        $only_virtual = false;
        $cart = WC()->cart;
        if ( $cart ) {
            // Allow filtering whether downloadable products should be treated as virtual
            $include_downloadable = apply_filters( 'fsl_treat_downloadables_as_virtual', true );
            foreach ( $cart->get_cart() as $cart_item ) {
                $product = $cart_item['data'];
                if ( $product->is_virtual() || $include_downloadable && $product->is_downloadable() ) {
                    $only_virtual = true;
                } else {
                    $only_virtual = false;
                    break;
                }
            }
        }
        return $only_virtual;
    }

    /**
     * Check package to determine if is a returning customer.
     * 
     * TODO: better check on more parameters.
     */
    static function destination_info_exists( $package = [] ) {
        $country = ( isset( $package['destination']['country'] ) ? $package['destination']['country'] : null );
        $state = ( isset( $package['destination']['state'] ) ? $package['destination']['state'] : null );
        $postcode = ( isset( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : null );
        $city = ( isset( $package['destination']['city'] ) ? $package['destination']['city'] : null );
        // If country is set to AF - this is probably default selection for the first country on the list.
        // Just to be sure, we'll check if city is empty or not.
        if ( $country === 'AF' && !$city ) {
            $country = null;
        }
        $exists = true;
        // If there's no country, state and postcode, we are probably dealing with "first-timer" or
        // a customer that hasn't filled out checkout form recently.
        if ( !$country && !$state && !$postcode ) {
            $exists = false;
        }
        return $exists;
    }

    /**
     * 
     * @since   2.4.0
     */
    static function is_free_shipping_coupon_applied() {
        $is_applied = false;
        $applied_coupons = WC()->cart->get_applied_coupons();
        foreach ( $applied_coupons as $coupon_code ) {
            $coupon = new \WC_Coupon($coupon_code);
            if ( $coupon->get_free_shipping() ) {
                $is_applied = true;
            }
        }
        return $is_applied;
    }

    /**
     * Search products by title only.
     * 
     * @since    2.6.0
     */
    static function search_product_titles( $find = '', $variations = false ) {
        global $wpdb;
        $wild = '%';
        $like = $wild . $wpdb->esc_like( $find ) . $wild;
        // Determine post types to include
        $post_types = ( $variations ? "('product', 'product_variation')" : "('product')" );
        // Build and prepare SQL
        $sql = $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts}\n             WHERE post_type IN {$post_types}\n             AND post_status = 'publish'\n             AND post_title LIKE %s", $like );
        return $wpdb->get_results( $sql );
    }

    /**
     * Search through products and product categories
     * 
     * @since    2.6.0
     */
    static function fsl_search() {
        $search_term = ( isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '' );
        $search_in = map_deep( $_GET['searchIn'], 'sanitize_text_field' );
        // we will pass post IDs and titles to this array
        $return = [];
        if ( in_array( 'category', $search_in ) ) {
            $categories = get_terms( [
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
            ] );
            // Prepare category array
            foreach ( $categories as $category ) {
                $name = $category->name;
                // Find category by slug or title
                if ( strpos( strtolower( $name ), strtolower( $search_term ) ) !== false ) {
                    $name = ( mb_strlen( $name ) > 50 ? mb_substr( $name, 0, 49 ) . '...' : $name );
                    $return[] = [$category->term_id, $name, 'category'];
                    // array( Category ID, Category Title, Type )
                }
            }
        }
        if ( in_array( 'product', $search_in ) ) {
            $variations = in_array( 'product_variation', $search_in );
            $found_products = self::search_product_titles( $search_term, $variations );
            if ( !empty( $found_products ) ) {
                foreach ( $found_products as $product ) {
                    $title = $product->post_title;
                    // shorten the title a little
                    $title = ( mb_strlen( $title ) > 50 ? mb_substr( $title, 0, 49 ) . '...' : $title );
                    $return[] = [$product->ID, $title, 'product'];
                    // array( Post ID, Post Title, Type )
                }
            }
        }
        echo wp_json_encode( $return );
        wp_die();
    }

    static function label_excluded( $output ) {
        $label_options = DEVNET_FSL_OPTIONS['label'] ?? [];
        $excluded = ( isset( $label_options['exclude'] ) ? $label_options['exclude'] : [] );
        $options = [];
        $excluded_on = [];
        foreach ( $excluded as $key ) {
            $parts = explode( '___', $key );
            $title = ( isset( $parts[1] ) ? $parts[1] : $key );
            $type_and_id = ( isset( $parts[0] ) ? $parts[0] : [] );
            $type_and_id_parts = explode( '---', $type_and_id );
            $type = ( isset( $type_and_id_parts[0] ) ? $type_and_id_parts[0] : '' );
            $id = ( isset( $type_and_id_parts[1] ) ? $type_and_id_parts[1] : '' );
            $options[$key] = $title;
            $excluded_on[$type][] = $id;
        }
        return ( $output === 'options' ? $options : $excluded_on );
    }

    /**
     * Convert array of strings with placeholders.
     *
     * @since    3.0.0
     */
    static function convert_placeholders_array( $input = [] ) {
        $output = [];
        foreach ( $input as $module => $sections ) {
            $placeholder_args = $sections['placeholder_args'] ?? [];
            $texts = $sections['text'] ?? [];
            foreach ( $texts as $section => $text ) {
                if ( $section === 'placeholder_args' ) {
                    continue;
                }
                $output[$module][$section] = self::convert_placeholders( $text, $placeholder_args );
            }
        }
        return $output;
    }

    /**
     * Convert placeholders to strings.
     *
     * @since    2.1.0
     */
    static function convert_placeholders( $input_string = '', $args = [] ) {
        $remaining = $args['remaining'] ?? '';
        $threshold = $args['threshold'] ?? '';
        $free_shipping_amount = $args['free_shipping_amount'] ?? '';
        if ( $remaining ) {
            $input_string = str_replace( '{remaining}', wc_price( $remaining ), $input_string );
        }
        if ( $threshold ) {
            $input_string = str_replace( ['{threshold}', '{free_shipping_amount}'], wc_price( $threshold ), $input_string );
        } elseif ( $free_shipping_amount ) {
            $input_string = str_replace( '{free_shipping_amount}', wc_price( $free_shipping_amount ), $input_string );
        }
        return $input_string;
    }

    /**
     * Calculate percentage and remaining amount.
     *
     * @since    3.0.0
     */
    static function calculate_percentage( $threshold, $cart_subtotal ) {
        $reached_threshold = $cart_subtotal >= $threshold;
        if ( $reached_threshold ) {
            $percent = 100;
            $remaining = '0,00';
        } else {
            $remaining = $threshold - $cart_subtotal;
            if ( $threshold != 0 ) {
                $percent = 100 - $remaining / $threshold * 100;
            }
        }
        return [
            'percent'   => $percent,
            'remaining' => $remaining,
        ];
    }

}
