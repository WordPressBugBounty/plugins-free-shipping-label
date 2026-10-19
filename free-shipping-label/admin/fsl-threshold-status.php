<?php

namespace Devnet\FSL\Admin;

use Devnet\FSL\Includes\Compatibility;
use Devnet\FSL\Includes\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Free shipping status at the top of the Free Shipping tab: for each shipping zone, the free shipping amount the bar
 * uses and where it comes from, and settings that keep the bar from showing. Read-only, admin only.
 *
 * Follows Helper::get_free_shipping_min_amount() without a cart (custom threshold, Advanced Free Shipping rules, then
 * the zone's shipping methods) and Bar\FSL_Bar::setup_data_for_free_shipping_bar(): keep them in step.
 *
 * @since	4.0.0
 */
class Threshold_Status {
    /**
     * Shipping methods FSL knows: they never give free shipping, or FSL reads their amount (Compatibility).
     */
    const KNOWN_METHODS = [
        'free_shipping',
        'flat_rate',
        'local_pickup',
        'pickup_location',
        'advanced_free_shipping',
        'flexible_shipping',
        'gls_shipping_method',
        'europarcelcom_wc_shipping'
    ];

    public static function render() {
        $status = self::get_status();
        $attention = count( array_filter( array_merge( array_column( $status['notices'], 'level' ), ...array_map( function ( $zone ) {
            return array_column( $zone['notes'], 'level' );
        }, $status['zones'] ) ), function ( $level ) {
            return in_array( $level, ['alert', 'warning'], true );
        } ) );
        echo '<div class="fsl-status">';
        printf( '<h3>%s</h3>', esc_html__( 'Free shipping status', 'free-shipping-label' ) );
        printf( '<p>%s %s</p>', wp_kses_post( ( $attention ? self::icon( 'warning' ) : self::icon( 'ok' ) ) ), ( $attention ? esc_html__( 'Some settings need a look.', 'free-shipping-label' ) : esc_html__( 'Everything looks fine.', 'free-shipping-label' ) ) );
        foreach ( $status['notices'] as $notice ) {
            printf(
                '<div class="devnet-plugin-panel-description%s"><p>%s %s</p></div>',
                ( $notice['level'] === 'info' ? '' : ' devnet-plugin-' . esc_attr( $notice['level'] ) ),
                wp_kses_post( self::icon( $notice['level'] ) ),
                wp_kses_post( $notice['text'] )
            );
        }
        if ( $status['zones'] ) {
            printf(
                '<table class="widefat striped"><thead><tr><th>%s</th><th>%s</th><th>%s</th></tr></thead><tbody>',
                esc_html__( 'Shipping zone', 'free-shipping-label' ),
                esc_html__( 'Free shipping from', 'free-shipping-label' ),
                esc_html__( 'Notes', 'free-shipping-label' )
            );
            foreach ( $status['zones'] as $zone ) {
                $notes = '';
                foreach ( $zone['notes'] as $note ) {
                    $notes .= '<li>' . self::icon( $note['level'] ) . ' ' . wp_kses_post( $note['text'] ) . '</li>';
                }
                printf(
                    '<tr><td>%1$s %2$s%3$s</td><td>%4$s</td><td><ul class="fsl-status__notes">%5$s</ul></td></tr>',
                    wp_kses_post( self::icon( $zone['level'] ) ),
                    esc_html( $zone['name'] ),
                    ( $zone['start'] ? ' <em>(' . esc_html__( 'new visitors', 'free-shipping-label' ) . ')</em>' : '' ),
                    ( $zone['amount'] === null ? '&mdash;' : wp_kses_post( wc_price( $zone['amount'] ) ) ),
                    wp_kses_post( $notes )
                );
            }
            echo '</tbody></table>';
            printf( '<p class="description">%s</p>', esc_html__( 'Amounts are in your store currency. The Progress Bar and the product label use them.', 'free-shipping-label' ) );
        }
        echo '</div>';
    }

    /**
     * Notices and a row per shipping zone (level: ok, warning or none); every notice and zone note is a message().
     */
    public static function get_status() {
        $notices = [];
        if ( !wc_shipping_enabled() ) {
            $notices[] = self::message( 'alert', __( 'Shipping is disabled in WooCommerce (<strong>WooCommerce → Settings → General → Shipping location(s)</strong>), so the Progress Bar doesn\'t show.', 'free-shipping-label' ) );
            return [
                'notices' => $notices,
                'zones'   => [],
            ];
        }
        // Before 4.0.0 at the top of every tab.
        if ( get_option( 'woocommerce_shipping_cost_requires_address' ) === 'yes' ) {
            $notices[] = self::message( 'alert', __( 'Customers see the bar and label only after entering an address, because WooCommerce hides shipping costs until then. To change this, uncheck <strong>“Hide shipping costs until an address is entered”</strong> in <strong>WooCommerce → Settings → Shipping → Shipping options</strong>.', 'free-shipping-label' ) );
        }
        $general = DEVNET_FSL_OPTIONS['general'] ?? [];
        $custom = null;
        if ( !empty( $general['enable_custom_threshold'] ) ) {
            $custom = [
                'global'  => self::amount( $general['custom_threshold'] ?? null ),
                'methods' => [],
            ];
            if ( $custom['global'] === null ) {
                $notices[] = ( $custom['methods'] ? self::message( 'info', __( 'The custom threshold is empty: the bar shows only with shipping methods that have their own custom threshold.', 'free-shipping-label' ) ) : self::message( 'alert', __( 'The custom threshold is on but empty, so the bar doesn\'t show. Enter an amount, or turn the custom threshold off.', 'free-shipping-label' ) ) );
            }
        }
        // Advanced Free Shipping rules come before the zone's methods; a custom threshold comes before both.
        $zone_rules = [];
        foreach ( ( $custom ? [] : self::wafs_rules() ) as $rule ) {
            if ( !$rule['countries'] ) {
                $notices[] = self::message( 'info', sprintf( 
                    /* translators: 1: rule name, 2: amount */
                    __( 'Advanced Free Shipping rule “%1$s” (%2$s) can apply in every zone: when its conditions match, the bar uses its amount.', 'free-shipping-label' ),
                    esc_html( $rule['title'] ),
                    wc_price( $rule['amount'] )
                 ) );
                continue;
            }
            foreach ( $rule['countries'] as $country ) {
                $zone_id = self::zone_for( $country )->get_id();
                $zone_rules[$zone_id][] = [
                    'country' => $country,
                ] + $rule;
            }
        }
        $zones = array_map( function ( $zone ) {
            return new \WC_Shipping_Zone($zone['zone_id']);
        }, \WC_Shipping_Zones::get_zones() );
        // get_zones() leaves out "Locations not covered by your other zones".
        $zones[] = new \WC_Shipping_Zone(0);
        $start = self::start_zone( $general, $notices );
        $rows = [];
        foreach ( $zones as $zone ) {
            $row = self::zone_row( $zone, $custom, $zone_rules[$zone->get_id()] ?? [] );
            $row['level'] = self::row_level( $row );
            $row['start'] = $start !== null && $start === $zone->get_id();
            $rows[] = $row;
            if ( $row['start'] && $row['amount'] === null && !$row['chosen'] ) {
                $notices[] = self::message( 'warning', sprintf( 
                    /* translators: %s: shipping zone name */
                    __( 'New visitors start in “%s”, which has no free shipping amount: they see the bar only after entering an address.', 'free-shipping-label' ),
                    esc_html( $zone->get_zone_name() )
                 ) );
            }
        }
        $amounts = array_filter( $rows, function ( $row ) {
            return $row['amount'] !== null || $row['chosen'];
        } );
        if ( !$custom && !$amounts ) {
            $notices[] = self::message( 'alert', __( 'No shipping zone has a free shipping amount, so the bar doesn\'t show. Add a WooCommerce Free shipping method with a minimum order amount, or set a custom threshold below.', 'free-shipping-label' ) );
        }
        if ( has_filter( 'fsl_min_amount' ) ) {
            $notices[] = self::message( 'info', __( 'A plugin or custom code changes the free shipping amount (the <code>fsl_min_amount</code> filter), so your store can show other amounts than this table.', 'free-shipping-label' ) );
        }
        return [
            'notices' => $notices,
            'zones'   => $rows,
        ];
    }

    /**
     * The zone new visitors start in, before they enter an address; null when unknown (geolocation) or none.
     * WooCommerce gives new visitors a country unless "Default customer location" is "No location by default", and
     * then the Initial shipping zone isn't used (Helper::destination_info_exists()).
     */
    private static function start_zone( $general, &$notices ) {
        $initial = (string) ($general['initial_zone'] ?? '1');
        $location = get_option( 'woocommerce_default_customer_address', 'base' );
        if ( in_array( $location, ['geolocation', 'geolocation_ajax'], true ) ) {
            $notices[] = self::message( 'info', __( '“Initial shipping zone” has no effect: WooCommerce locates new visitors by their IP address (<strong>WooCommerce → Settings → General → Default customer location</strong>), so the bar starts with their country\'s zone.', 'free-shipping-label' ) );
            return null;
        }
        $default = [
            'country' => '',
            'state'   => '',
        ];
        $country = '';
        if ( $location !== '' ) {
            // wc_get_customer_default_location() without geolocation.
            $default = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country', 'US:CA' ) ) );
            $country = ( array_key_exists( $default['country'], WC()->countries->get_allowed_countries() ) ? $default['country'] : '' );
        }
        if ( $country ) {
            $zone = self::zone_for( $country, $default['state'] );
            if ( $initial !== (string) $zone->get_id() ) {
                $notices[] = self::message( 'info', sprintf( 
                    /* translators: %s: shipping zone name */
                    __( '“Initial shipping zone” has no effect: WooCommerce gives new visitors your store\'s country (<strong>WooCommerce → Settings → General → Default customer location</strong>), so the bar starts with “%s”. To use the initial zone, set that option to “No location by default”.', 'free-shipping-label' ),
                    esc_html( $zone->get_zone_name() )
                 ) );
            }
            return $zone->get_id();
        }
        if ( $initial === '' ) {
            $notices[] = self::message( 'info', __( '“Initial shipping zone” is “None”: the bar shows once customers enter an address.', 'free-shipping-label' ) );
            return null;
        }
        if ( !\WC_Shipping_Zones::get_zone_by( 'zone_id', $initial ) ) {
            $notices[] = self::message( 'warning', __( 'The initial shipping zone doesn\'t exist anymore. Choose another one below.', 'free-shipping-label' ) );
            return null;
        }
        return (int) $initial;
    }

    /**
     * A zone's row: the amount the bar uses (null: no bar), its notes, and whether chosen methods have their own.
     */
    private static function zone_row( $zone, $custom, $rules ) {
        $methods = $zone->get_shipping_methods();
        $own = self::own_free_shipping( $methods );
        $chosen = self::chosen_amounts( $methods, $own['amount'] );
        $unknown = self::unknown_methods( $methods );
        $row = [
            'name'   => $zone->get_zone_name(),
            'amount' => $own['amount'],
            'notes'  => $own['notes'],
            'chosen' => (bool) $chosen,
        ];
        if ( $custom ) {
            return self::custom_row(
                $row,
                $own,
                $custom,
                $methods,
                $unknown
            );
        }
        foreach ( $rules as $rule ) {
            $text = sprintf(
                /* translators: 1: rule name, 2: amount, 3: country */
                __( 'Advanced Free Shipping rule “%1$s”: %2$s in %3$s', 'free-shipping-label' ),
                esc_html( $rule['title'] ),
                wc_price( $rule['amount'] ),
                esc_html( WC()->countries->get_countries()[$rule['country']] ?? $rule['country'] )
            );
            $text .= ( $rule['other'] ? esc_html__( ', when its other conditions match.', 'free-shipping-label' ) : '.' );
            if ( $row['amount'] === null ) {
                $row['amount'] = $rule['amount'];
                $row['notes'][] = self::message( 'info', $text );
            } elseif ( abs( $rule['amount'] - $row['amount'] ) > 0.001 ) {
                $row['notes'][] = self::message( 'warning', $text . ' ' . esc_html__( 'It comes first, so the bar can show its amount instead.', 'free-shipping-label' ) );
            } else {
                $row['notes'][] = self::message( 'info', $text );
            }
        }
        foreach ( $chosen as $note ) {
            $row['notes'][] = $note;
        }
        if ( $row['amount'] === null && !$chosen && !$own['found'] && $unknown ) {
            $row['notes'][] = self::message( 'warning', self::unknown_note( $unknown ) );
        } elseif ( $row['amount'] === null && !$chosen && !$own['found'] ) {
            $row['notes'][] = self::message( 'info', esc_html__( 'No free shipping found: the bar doesn\'t show for addresses in this zone.', 'free-shipping-label' ) );
        }
        return $row;
    }

    /**
     * With the custom threshold on, the bar uses it everywhere (per method in Pro): compare it with each zone's own
     * free shipping.
     */
    private static function custom_row(
        $row,
        $own,
        $custom,
        $methods,
        $unknown
    ) {
        $amount = $custom['global'];
        $notes = [self::message( 'info', ( $amount === null ? esc_html__( 'No custom threshold.', 'free-shipping-label' ) : esc_html__( 'Your custom threshold.', 'free-shipping-label' ) ) )];
        $chosen = false;
        foreach ( $methods as $method ) {
            if ( isset( $custom['methods'][$method->get_rate_id()] ) ) {
                $chosen = true;
                $notes[] = self::message( 'info', sprintf( 
                    /* translators: 1: shipping method, 2: amount */
                    __( 'With “%1$s”: %2$s (its own custom threshold).', 'free-shipping-label' ),
                    esc_html( $method->get_title() ),
                    wc_price( $custom['methods'][$method->get_rate_id()] )
                 ) );
            }
        }
        if ( $amount !== null ) {
            if ( $own['amount'] !== null && abs( $own['amount'] - $amount ) > 0.001 ) {
                $notes[] = self::message( 'warning', sprintf( 
                    /* translators: 1: amount, 2: where it is set */
                    __( 'Free shipping here starts at %1$s (%2$s), but the bar uses your custom threshold.', 'free-shipping-label' ),
                    wc_price( $own['amount'] ),
                    $own['source']
                 ) );
            } elseif ( $own['amount'] === null && $unknown ) {
                $notes[] = self::message( 'info', sprintf( 
                    /* translators: %s: shipping methods */
                    __( 'Make sure one of %s gives free shipping from this amount.', 'free-shipping-label' ),
                    self::quote( $unknown )
                 ) );
            } elseif ( $own['amount'] === null ) {
                $notes[] = self::message( 'warning', esc_html__( 'The plugin found no free shipping in this zone, but the bar shows your custom threshold here.', 'free-shipping-label' ) );
            }
        }
        // The custom threshold comes first: per-method values are the only amounts that apply when chosen.
        return [
            'amount' => $amount,
            'notes'  => $notes,
            'chosen' => $chosen,
        ] + $row;
    }

    /**
     * The zone's own free shipping, as Helper::get_free_shipping_min_amount() takes it from the zone's methods: the
     * first enabled WooCommerce Free shipping method, else the first method a compatible plugin has an amount for.
     * The amount is null when the bar doesn't show (FSL_Bar::setup_data_for_free_shipping_bar()).
     */
    private static function own_free_shipping( $methods ) {
        $result = [
            'amount' => null,
            'notes'  => [],
            'source' => '',
            'found'  => false,
        ];
        $amount = null;
        $source = null;
        foreach ( $methods as $method ) {
            if ( $method->id === 'free_shipping' && $method->enabled === 'yes' ) {
                $instance = $method->instance_settings ?? null;
                $key = apply_filters( 'fsl_free_shipping_instance_key', 'min_amount' );
                $amount = $instance[$key] ?? null;
                if ( !$amount && isset( $instance['min_amount'] ) ) {
                    $amount = $instance['min_amount'];
                }
                $source = $method;
                break;
            }
            $amount = self::method_amount( $method, $methods );
            if ( $amount ) {
                $source = $method;
                break;
            }
        }
        foreach ( $methods as $method ) {
            if ( $method->id === 'free_shipping' && $method->enabled !== 'yes' ) {
                $result['notes'][] = self::message( 'info', esc_html__( 'WooCommerce Free shipping is turned off here.', 'free-shipping-label' ) );
                break;
            }
        }
        if ( !$source ) {
            return $result;
        }
        $result['found'] = true;
        $usable = self::amount( $amount );
        if ( $source->id !== 'free_shipping' ) {
            $result['amount'] = $usable;
            $result['source'] = sprintf( '“%s”', esc_html( $source->get_title() ) );
            /* translators: %s: shipping method */
            $result['notes'][] = self::message( 'info', sprintf( __( 'From “%s”.', 'free-shipping-label' ), esc_html( $source->get_title() ) ) );
            if ( $source->enabled !== 'yes' ) {
                $result['notes'][] = self::message( 'warning', esc_html__( 'That method is turned off, but the bar still uses its amount.', 'free-shipping-label' ) );
            }
            return $result;
        }
        $requires = $source->instance_settings['requires'] ?? '';
        $coupons = ( wc_coupons_enabled() ? '' : ' ' . esc_html__( 'But coupons are disabled in WooCommerce, so customers can\'t get it.', 'free-shipping-label' ) );
        $result['source'] = esc_html__( 'WooCommerce Free shipping', 'free-shipping-label' );
        if ( $requires === '' ) {
            $result['notes'][] = self::message( 'info', esc_html__( 'WooCommerce Free shipping here requires nothing (“Free shipping requires…”: N/A), so shipping is always free and the bar doesn\'t show.', 'free-shipping-label' ) );
        } elseif ( in_array( $requires, ['min_amount', 'either'], true ) && $usable === null ) {
            $result['notes'][] = self::message( 'info', esc_html__( 'WooCommerce Free shipping here has no minimum order amount, so shipping is always free and the bar doesn\'t show.', 'free-shipping-label' ) );
        } elseif ( $requires === 'coupon' || $usable === null ) {
            $result['notes'][] = self::message( ( $coupons ? 'warning' : 'info' ), esc_html__( 'WooCommerce Free shipping here needs a coupon only, so there is no amount to reach.', 'free-shipping-label' ) . $coupons );
        } else {
            $result['amount'] = $usable;
            $result['notes'][] = self::message( 'info', esc_html__( 'WooCommerce Free shipping.', 'free-shipping-label' ) );
            if ( $requires === 'both' ) {
                $result['notes'][] = self::message( ( $coupons ? 'warning' : 'info' ), esc_html__( 'It also needs a free shipping coupon.', 'free-shipping-label' ) . $coupons );
            }
        }
        return $result;
    }

    /**
     * A compatible plugin's free shipping amount for a method, as Compatibility::get_custom_shipping_min_amount()
     * reads it in the zone loop (Flexible Shipping, GLS), in the store currency.
     */
    private static function method_amount( $method, $methods ) {
        if ( Helper::starts_with( $method->id, 'flexible_shipping' ) ) {
            return Compatibility::get_flexible_shipping_method_min_amount( $method->id . ':' . $method->instance_id );
        }
        if ( !Helper::starts_with( $method->id, 'gls_shipping_method' ) ) {
            return null;
        }
        // GLS per-zone methods: the zone's first method of that kind with an amount (gls_zones_free_shipping_threshold()).
        if ( Helper::ends_with( $method->id, 'zones' ) ) {
            foreach ( $methods as $zone_method ) {
                if ( $zone_method->id === $method->id && !empty( $zone_method->instance_settings['free_shipping_threshold'] ) ) {
                    return $zone_method->instance_settings['free_shipping_threshold'];
                }
            }
            return null;
        }
        $settings = get_option( 'woocommerce_' . $method->id . '_settings', [] );
        $value = (float) ($settings['free_shipping_threshold'] ?? 0);
        return ( $value > 0 ? $value : null );
    }

    /**
     * Notes for methods with their own amount when the customer picks them (the chosen rate in
     * Compatibility::get_custom_shipping_min_amount()), other than the zone's amount.
     */
    private static function chosen_amounts( $methods, $zone_amount ) {
        $amounts = [];
        foreach ( $methods as $method ) {
            if ( $method->enabled !== 'yes' ) {
                continue;
            }
            $amount = self::amount( self::method_amount( $method, $methods ) );
            if ( $amount !== null ) {
                $amounts[$method->get_title()] = $amount;
            }
        }
        $notes = [];
        foreach ( $amounts as $title => $amount ) {
            if ( $zone_amount === null || abs( $amount - $zone_amount ) > 0.001 ) {
                /* translators: 1: shipping method, 2: amount */
                $notes[] = self::message( 'info', sprintf( __( 'With “%1$s”: %2$s.', 'free-shipping-label' ), esc_html( $title ), wc_price( $amount ) ) );
            }
        }
        return $notes;
    }

    /**
     * Advanced Free Shipping rules with a subtotal, as Compatibility::wafs_threshold() reads them: the last subtotal
     * condition's value, the countries its conditions name ([] when a group names none), and whether other
     * conditions apply.
     */
    private static function wafs_rules() {
        if ( !function_exists( 'wafs_get_rates' ) || !function_exists( 'wpc_match_conditions' ) ) {
            return [];
        }
        $rules = [];
        foreach ( wafs_get_rates() as $rate ) {
            $groups = get_post_meta( $rate->ID, '_wafs_shipping_method_conditions', true );
            if ( !is_array( $groups ) ) {
                continue;
            }
            $amount = null;
            $countries = [];
            $anywhere = false;
            $other = false;
            foreach ( $groups as $group ) {
                $group_countries = [];
                foreach ( (array) $group as $condition ) {
                    $type = $condition['condition'] ?? '';
                    if ( $type === 'subtotal' ) {
                        $amount = $condition['value'] ?? null;
                    } elseif ( $type === 'country' && ($condition['operator'] ?? '') === '==' && is_string( $condition['value'] ?? null ) ) {
                        $group_countries[] = $condition['value'];
                    } else {
                        $other = true;
                    }
                }
                $anywhere = $anywhere || !$group_countries;
                $countries = array_merge( $countries, $group_countries );
            }
            if ( self::amount( $amount ) !== null ) {
                $rules[] = [
                    'title'     => get_the_title( $rate ),
                    'amount'    => self::amount( $amount ),
                    'countries' => ( $anywhere ? [] : array_values( array_unique( $countries ) ) ),
                    'other'     => $other,
                ];
            }
        }
        return $rules;
    }

    /**
     * Enabled methods FSL can't read (D3: no guessing).
     */
    private static function unknown_methods( $methods ) {
        $titles = [];
        foreach ( $methods as $method ) {
            if ( $method->enabled !== 'yes' ) {
                continue;
            }
            foreach ( self::KNOWN_METHODS as $known ) {
                if ( Helper::starts_with( $method->id, $known ) ) {
                    continue 2;
                }
            }
            $titles[] = $method->get_title();
        }
        return array_values( array_unique( $titles ) );
    }

    private static function unknown_note( $titles ) {
        return sprintf( 
            /* translators: %s: shipping methods */
            __( 'The plugin can\'t read free shipping from %s. If one of them gives free shipping, set a custom threshold.', 'free-shipping-label' ),
            self::quote( $titles )
         );
    }

    private static function quote( $titles ) {
        return implode( ', ', array_map( function ( $title ) {
            return '“' . esc_html( $title ) . '”';
        }, $titles ) );
    }

    /**
     * The zone WooCommerce gives an address in this country.
     */
    private static function zone_for( $country, $state = '' ) {
        return \WC_Shipping_Zones::get_zone_matching_package( [
            'destination' => [
                'country'  => $country,
                'state'    => $state,
                'postcode' => '',
            ],
        ] );
    }

    /**
     * An amount the bar can use, as FSL_Bar::setup_data_for_free_shipping_bar() checks it: a number over 0.
     */
    private static function amount( $value ) {
        $value = ( is_string( $value ) ? str_replace( ',', '.', $value ) : $value );
        return ( is_numeric( $value ) && (float) $value > 0 ? (float) $value : null );
    }

    /**
     * A notice or a zone note. Levels: alert (the bar doesn't show anywhere), warning (needs a look), info.
     */
    private static function message( $level, $text ) {
        return [
            'level' => $level,
            'text'  => $text,
        ];
    }

    /**
     * A row's icon: warning when one of its notes is, else whether the bar has an amount there.
     */
    private static function row_level( $row ) {
        if ( in_array( 'warning', array_column( $row['notes'], 'level' ), true ) ) {
            return 'warning';
        }
        return ( $row['amount'] !== null || $row['chosen'] ? 'ok' : 'none' );
    }

    private static function icon( $level ) {
        $icons = [
            'ok'      => ['yes-alt', __( 'OK', 'free-shipping-label' )],
            'none'    => ['minus', __( 'No free shipping', 'free-shipping-label' )],
            'alert'   => ['warning', __( 'Problem', 'free-shipping-label' )],
            'warning' => ['warning', __( 'Needs a look', 'free-shipping-label' )],
            'info'    => ['info-outline', __( 'Info', 'free-shipping-label' )],
        ];
        [$icon, $label] = $icons[$level];
        return sprintf(
            '<span class="dashicons dashicons-%1$s fsl-status__icon--%2$s" aria-hidden="true"></span><span class="screen-reader-text">%3$s</span>',
            esc_attr( $icon ),
            esc_attr( $level ),
            esc_html( $label )
        );
    }

}
