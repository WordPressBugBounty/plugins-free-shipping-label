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
    public static function starts_with( $string, $start_string ) {
        if ( !$string ) {
            return false;
        }
        $len = strlen( $start_string );
        return substr( $string, 0, $len ) === $start_string;
    }

    /**
     * Check if a string ends with a given suffix.
     *
     */
    public static function ends_with( string $haystack, string $needle ) {
        if ( $needle === '' ) {
            return true;
        }
        return substr( $haystack, -strlen( $needle ) ) === $needle;
    }

    /**
     * Get chosen shipping method.
     *
     * @since    2.1.0
     */
    public static function chosen_shipping_method() {
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

    public static function normalize_shipping_method_id( string $chosen ) : array {
        $parts = explode( ':', $chosen, 2 );
        return [
            'method_id'   => $parts[0],
            'instance_id' => $parts[1] ?? null,
        ];
    }

    /**
     * Extract shipping instance ID from shipping method string.
     *
     */
    public static function get_shipping_instance_id( $string ) {
        if ( preg_match( '/:(\\d+)_/', $string, $matches ) ) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Get minimal amount for free shipping.
     *
     * @since    1.0.0
     * @return   number
     */
    public static function get_free_shipping_min_amount() {
        $amount = null;
        // Set again below only when a WooCommerce Free Shipping method provides the threshold.
        self::$free_shipping_instance = [];
        $only_virtual_products_in_cart = self::only_virtual_products();
        $general_options = DEVNET_FSL_OPTIONS['general'] ?? [];
        $initial_zone = $general_options['initial_zone'] ?? '1';
        $enable_custom_threshold = $general_options['enable_custom_threshold'] ?? false;
        $custom_threshold = $general_options['custom_threshold'] ?? $amount;
        $custom_threshold_per_method = $general_options['custom_threshold_per_method'] ?? [];
        $chosen_shipping_id = (string) self::chosen_shipping_method();
        /*
         * Custom threshold check.
         *
         */
        if ( $enable_custom_threshold && !$only_virtual_products_in_cart ) {
            $amount = $custom_threshold;
            return apply_filters( 'fsl_min_amount', $amount );
        }
        /*
         * Third-party shipping methods check.
         * First check by chosen shipping method.
         */
        $amount = Compatibility::get_custom_shipping_min_amount( $chosen_shipping_id );
        if ( $amount !== null ) {
            if ( $only_virtual_products_in_cart ) {
                $amount = null;
            }
            return apply_filters( 'fsl_min_amount', $amount );
        }
        /*
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
            // No address yet: use the initial zone.
            if ( !$known_customer ) {
                if ( $initial_zone === '' ) {
                    // "-- None --": no threshold until the customer enters an address.
                    $zone = null;
                } else {
                    $init_zone = \WC_Shipping_Zones::get_zone_by( 'zone_id', $initial_zone );
                    // Check if initial zone still exists.
                    $zone = ( $init_zone ? $init_zone : $zone );
                }
            }
            foreach ( ( $zone ? $zone->get_shipping_methods() : [] ) as $key => $method ) {
                if ( $method->id === 'free_shipping' && $method->enabled === 'yes' ) {
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
                if ( $amount ) {
                    break;
                }
            }
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
    public static function only_virtual_products() {
        $only_virtual = false;
        $cart = WC()->cart;
        if ( $cart ) {
            // Opt-in: treat downloadable products as virtual even when they ship (the default before 4.0.0).
            $include_downloadable = apply_filters( 'fsl_treat_downloadables_as_virtual', false );
            foreach ( $cart->get_cart() as $cart_item ) {
                $product = $cart_item['data'];
                // WooCommerce's rule: a downloadable product that isn't virtual still needs shipping.
                if ( !$product->needs_shipping() || $include_downloadable && $product->is_downloadable() ) {
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
    public static function destination_info_exists( $package = [] ) {
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
    public static function is_free_shipping_coupon_applied() {
        foreach ( WC()->cart->get_coupons() as $coupon ) {
            // Same check as WC_Shipping_Free_Shipping::is_available().
            if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Search products by title only.
     *
     * @since    2.6.0
     */
    public static function search_product_titles( $find = '', $variations = false ) {
        global $wpdb;
        $like = '%' . $wpdb->esc_like( $find ) . '%';
        $types = ( $variations ? ['product', 'product_variation'] : ['product'] );
        $placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );
        $sql = "\n        SELECT ID, post_title\n        FROM {$wpdb->posts}\n        WHERE post_type IN ({$placeholders})\n          AND post_status = 'publish'\n          AND post_title LIKE %s\n        LIMIT 50\n    ";
        $params = array_merge( $types, [$like] );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared via $wpdb->prepare() with dynamic placeholders.
        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    /**
     * Search through products and product categories
     *
     * @since    2.6.0
     */
    public static function fsl_search() {
        check_ajax_referer( 'devnet_fsl_search' );
        if ( !current_user_can( apply_filters( 'fsl_admin_menu_user_capability', 'manage_options' ) ) ) {
            wp_send_json_error( null, 403 );
        }
        $search_term = ( isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '' );
        $search_in = map_deep( (array) wp_unslash( $_GET['searchIn'] ?? [] ), 'sanitize_text_field' );
        // we will pass post IDs and titles to this array
        $return = [];
        if ( in_array( 'category', $search_in ) ) {
            // Find category by slug or title
            $categories = get_terms( [
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'search'     => $search_term,
                'number'     => 50,
            ] );
            // Prepare category array
            foreach ( $categories as $category ) {
                $name = $category->name;
                $name = ( mb_strlen( $name ) > 50 ? mb_substr( $name, 0, 49 ) . '...' : $name );
                $return[] = [$category->term_id, $name, 'category'];
                // array( Category ID, Category Title, Type )
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
        wp_send_json( $return );
    }

    public static function label_excluded( $output ) {
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
     * Convert placeholders to strings.
     *
     * @since    2.1.0
     */
    public static function convert_placeholders( $input_string = '', $args = [] ) {
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
    public static function calculate_percentage( $threshold, $cart_subtotal ) {
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

    public static function detect_wc_cart_checkout_type() {
        $result = [
            'cart'     => 'unknown',
            'checkout' => 'unknown',
        ];
        if ( !function_exists( 'wc_get_page_id' ) ) {
            return $result;
            // WooCommerce not active
        }
        $wc_utils = 'Automattic\\WooCommerce\\Blocks\\Utils\\CartCheckoutUtils';
        foreach ( ['cart', 'checkout'] as $page ) {
            $page_id = wc_get_page_id( $page );
            if ( $page_id <= 0 ) {
                $result[$page] = 'not_assigned';
                continue;
            }
            // WooCommerce's own check also finds the Cart/Checkout block in block-theme templates.
            $wc_check = "is_{$page}_block_default";
            if ( method_exists( $wc_utils, $wc_check ) && $wc_utils::$wc_check() ) {
                $result[$page] = 'block';
                continue;
            }
            $content = (string) get_post_field( 'post_content', $page_id );
            if ( $content === '' ) {
                $result[$page] = 'empty';
                continue;
            }
            // Shortcode detection
            if ( has_shortcode( $content, 'woocommerce_' . $page ) ) {
                $result[$page] = 'shortcode';
                continue;
            }
            // Block detection
            if ( $page === 'cart' && has_block( 'woocommerce/cart', $content ) ) {
                $result[$page] = 'block';
                continue;
            }
            if ( $page === 'checkout' && has_block( 'woocommerce/checkout', $content ) ) {
                $result[$page] = 'block';
                continue;
            }
            $result[$page] = 'unknown';
        }
        return $result;
    }

}
