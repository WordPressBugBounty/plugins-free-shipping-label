<?php

namespace Devnet\FSL\Includes;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Activator {
    /**
     * @since    1.0.0
     */
    public static function activate() {
    }

    /**
     * @since    2.0.0
     */
    public static function check_and_format_options() {
        self::migrate_layout_option();
        self::migrate_pb_and_gift_bar_options();
        // Keep last: the migrations above write from the DEVNET_FSL_OPTIONS snapshot.
        self::repair_options();
    }

    /**
     * Check if there was set `layout` option in GiftBar or in FSL Discount.
     * Update `layout` in Progress Bar settings.
     * 
     * @since	3.4.0
     */
    public static function migrate_layout_option() {
        $layout_check = get_option( 'devnet_fsl_layout_check' );
        if ( $layout_check || !defined( 'DEVNET_FSL_OPTIONS' ) ) {
            return;
        }
        // One-time check: mark it done even when there's nothing to migrate. A missing flag is a DB query on every request.
        update_option( 'devnet_fsl_layout_check', true );
        $gift_bar_options = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        $options_with_layout = [
            'gift_bar' => $gift_bar_options,
        ];
        if ( defined( 'DEVNET_FSL_DISCOUNT_OPTIONS' ) ) {
            $options_with_layout = array_merge( $options_with_layout, DEVNET_FSL_DISCOUNT_OPTIONS );
        }
        $layout = self::get_layout_with_highest_enabled_threshold( $options_with_layout );
        if ( !$layout ) {
            return;
        }
        $progress_bar_options = DEVNET_FSL_OPTIONS['progress_bar'] ?? [];
        if ( empty( $progress_bar_options ) ) {
            return;
        }
        $progress_bar_options['layout'] = $layout;
        update_option( 'devnet_fsl_bar', $progress_bar_options );
    }

    private static function get_layout_with_highest_enabled_threshold( array $options_with_layout ) : ?string {
        $enabled = [];
        $all = [];
        foreach ( $options_with_layout as $key => $group ) {
            if ( !isset( $group['threshold'], $group['layout'] ) ) {
                continue;
            }
            $entry = [
                'key'       => $key,
                'threshold' => (float) $group['threshold'],
                'layout'    => $group['layout'],
            ];
            $all[] = $entry;
            if ( !empty( $group['enable_bar'] ) ) {
                $enabled[] = $entry;
            }
        }
        $target = ( !empty( $enabled ) ? $enabled : $all );
        if ( empty( $target ) ) {
            return null;
        }
        usort( $target, fn( $a, $b ) => $b['threshold'] <=> $a['threshold'] );
        return $target[0]['layout'];
    }

    /**
     * 
     * @since	3.5.0
     */
    public static function migrate_disabled_position_options() {
        // Read fresh, not DEVNET_FSL_OPTIONS: migrate_layout_option() may have updated it in this request.
        $progress_bar_options = get_option( 'devnet_fsl_bar' );
        // Fresh install
        if ( empty( $progress_bar_options ) || !is_array( $progress_bar_options ) ) {
            return;
        }
        $show_on_cart = $progress_bar_options['show_on_cart'] ?? null;
        $show_on_checkout = $progress_bar_options['show_on_checkout'] ?? null;
        $show_on_minicart = $progress_bar_options['show_on_minicart'] ?? null;
        if ( empty( $show_on_cart ) ) {
            $progress_bar_options['cart_position'] = '';
        }
        if ( empty( $show_on_checkout ) ) {
            $progress_bar_options['checkout_position'] = '';
        }
        if ( empty( $show_on_minicart ) ) {
            $progress_bar_options['minicart_position'] = '';
        }
        unset($progress_bar_options['show_on_cart']);
        unset($progress_bar_options['show_on_checkout']);
        unset($progress_bar_options['show_on_minicart']);
        update_option( 'devnet_fsl_bar', $progress_bar_options );
    }

    /**
     * 
     * @since	3.5.0
     */
    public static function migrate_gift_bar_styles() {
        // Read fresh, not DEVNET_FSL_OPTIONS: earlier migrations may have updated it in this request.
        $progress_bar_options = get_option( 'devnet_fsl_bar' );
        $progress_bar_options = ( is_array( $progress_bar_options ) ? $progress_bar_options : [] );
        $gift_bar_options = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
        // Fresh install or not set
        if ( empty( $gift_bar_options ) ) {
            return;
        }
        $inherit_progress_bar_settings = $gift_bar_options['inherit_progress_bar_settings'] ?? true;
        // Skip if settings are inherited
        if ( $inherit_progress_bar_settings ) {
            return;
        }
        $inheritable = [
            'hide_border_shadow',
            'disable_animation',
            'disabled_animations',
            'bar_border_color',
            'bar_bg_color',
            'bar_inner_color',
            'bar_height',
            'bar_type',
            // 'indicator_icon',
            // 'indicator_icon_size',
            // 'indicator_icon_shape',
            // 'indicator_icon_bg_color',
            'circle_size',
            'inside_circle',
            // 'icon',
            // 'icon_color',
            'circle_bg_color',
            'text_color',
            'box_bg_color',
            'box_max_width',
            'box_alignment',
            'center_text',
            'bar_radius',
            'remove_bar_stripes',
        ];
        foreach ( $inheritable as $opt ) {
            // Only copy set values; a stored null would override Defaults::bar().
            if ( isset( $gift_bar_options[$opt] ) ) {
                $progress_bar_options[$opt] = $gift_bar_options[$opt];
            }
            unset($gift_bar_options[$opt]);
        }
        update_option( 'devnet_fsl_gift_bar', $gift_bar_options );
        $gift_bar_display = $gift_bar_options['display'] ?? '';
        $gift_bar_enabled = $gift_bar_options['enable_bar'] ?? false;
        if ( $gift_bar_enabled && in_array( $gift_bar_display, ['extend', 'only'], true ) ) {
            update_option( 'devnet_fsl_bar', $progress_bar_options );
        }
    }

    /**
     * Migrate disabled positions inside position select.
     * Migrate GiftBar styles back into progress bar styles (only pro users with enabled gift bar and custom styles).
     * 
     * @since	3.5.0
     */
    public static function migrate_pb_and_gift_bar_options() {
        $migration_350 = get_option( 'devnet_fsl_migration_350' );
        if ( $migration_350 || !defined( 'DEVNET_FSL_OPTIONS' ) ) {
            return;
        }
        self::migrate_disabled_position_options();
        self::migrate_gift_bar_styles();
        update_option( 'devnet_fsl_migration_350', true );
    }

    /**
     * Run one-time DB repairs. Each repair runs once per site; done repairs are stored in one option.
     * Add new repairs to the end of `$repairs`. Read options with get_option(), not DEVNET_FSL_OPTIONS.
     *
     * @since	4.0.0
     */
    public static function repair_options() {
        $repairs = [
            'unslash_texts',
            'clear_unused_global_threshold',
            'text_switches',
            'clear_multilingual_texts',
            'gift_icon_settings'
        ];
        $done = (array) get_option( 'devnet_fsl_repairs', [] );
        $pending = array_diff( $repairs, $done );
        if ( !$pending ) {
            return;
        }
        // One request runs them: they read and write whole options, so requests running them at once (the first
        // visits after an update) would overwrite each other's work. INSERT IGNORE on the unique option name lets one
        // request in (add_option() wouldn't: it updates an existing row); the others leave the repairs to it. A lock
        // older than 5 minutes (a request that died) is taken over the same way, by one request.
        global $wpdb;
        $lock = 'devnet_fsl_repairs_lock';
        $now = time();
        // phpcs:disable WordPress.DB.DirectDatabaseQuery -- An atomic lock: no options API call inserts without updating.
        $locked = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')", $lock, $now ) ) || $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value < %d",
            $now,
            $lock,
            $now - 5 * MINUTE_IN_SECONDS
        ) );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery
        if ( !$locked ) {
            return;
        }
        // Another request may have finished them since this one read the list: read it again, not from the cache.
        wp_cache_delete( 'devnet_fsl_repairs', 'options' );
        wp_cache_delete( 'alloptions', 'options' );
        $done = (array) get_option( 'devnet_fsl_repairs', [] );
        $pending = array_diff( $repairs, $done );
        foreach ( $pending as $repair ) {
            self::$repair();
        }
        update_option( 'devnet_fsl_repairs', array_merge( $done, $pending ) );
        delete_option( $lock );
    }

    /**
     * The Gift Bar's icons look like the Progress Bar's by default ("Same as Progress Bar"). A Gift Bar whose saved
     * icon look differs keeps it, with the setting off.
     *
     * @since	4.0.0
     */
    private static function gift_icon_settings() {
        $gift = get_option( 'devnet_fsl_gift_bar' );
        if ( !is_array( $gift ) || isset( $gift['inherit_icon_settings'] ) ) {
            return;
        }
        $bar = get_option( 'devnet_fsl_bar' );
        $bar = (( is_array( $bar ) ? $bar : [] )) + Defaults::bar();
        foreach ( \Devnet\FSL\Frontend\Bar\FSL_Bar::ICON_LOOK as $key ) {
            if ( !isset( $gift[$key] ) ) {
                continue;
            }
            // The icons' switch is off as '0' once saved, as '' by default.
            $differs = ( 'indicator_icon' === $key ? empty( $gift[$key] ) !== empty( $bar[$key] ) : (string) $gift[$key] !== (string) $bar[$key] );
            if ( $differs ) {
                $gift['inherit_icon_settings'] = 0;
                update_option( 'devnet_fsl_gift_bar', $gift );
                return;
            }
        }
    }

    /**
     * Remove backslashes that `wp_filter_post_kses` added to Title/Description on every save.
     *
     * @since	4.0.0
     */
    private static function unslash_texts() {
        // Discount add-on bars were saved through the same sanitizer (Settings API matches fields by name).
        $option_names = [
            'devnet_fsl_bar',
            'devnet_fsl_gift_bar',
            'devnet_fsl_discount_bar',
            'devnet_fsl_discount_bar_2',
            'devnet_fsl_discount_bar_3'
        ];
        foreach ( $option_names as $option_name ) {
            $options = get_option( $option_name );
            if ( !is_array( $options ) ) {
                continue;
            }
            foreach ( ['title', 'description'] as $key ) {
                if ( isset( $options[$key] ) && is_string( $options[$key] ) ) {
                    $options[$key] = wp_unslash( $options[$key] );
                }
            }
            update_option( $option_name, $options );
        }
    }

    /**
     * Methods without their own threshold now use the global custom threshold. Before 4.0.0 the global
     * threshold was ignored once any method had a value: clear it there, so the storefront stays the same.
     *
     * @since	4.0.0
     */
    private static function clear_unused_global_threshold() {
    }

    /**
     * Texts got a show/hide switch (title, description, message after the threshold): set them to match today's
     * storefront for each bar. The Product Label has no switch: an emptied text turns it off. Before
     * clear_multilingual_texts(), which empties texts that showed the defaults.
     *
     * @since	4.0.0
     */
    private static function text_switches() {
        $general = get_option( 'devnet_fsl_general' );
        $multilingual = is_array( $general ) && !empty( $general['multilingual'] );
        $progress_bar = get_option( 'devnet_fsl_bar' );
        $show_message = ( is_array( $progress_bar ) && array_key_exists( 'show_qualified_message', $progress_bar ) ? $progress_bar['show_qualified_message'] : Defaults::bar( 'show_qualified_message' ) );
        $full_bar = ( is_array( $progress_bar ) && array_key_exists( 'show_full_progress_bar', $progress_bar ) ? $progress_bar['show_full_progress_bar'] : Defaults::bar( 'show_full_progress_bar' ) );
        // The reached message only without the full bar (below): "Show message" on also hides the title and
        // description above the full bar, even with an empty message, so switching it off would bring them back.
        $switches = [
            'title'       => 'show_title',
            'description' => 'show_description',
        ];
        $option_names = [
            'devnet_fsl_bar',
            'devnet_fsl_gift_bar',
            'devnet_fsl_discount_bar',
            'devnet_fsl_discount_bar_2',
            'devnet_fsl_discount_bar_3'
        ];
        foreach ( $option_names as $option_name ) {
            $options = get_option( $option_name );
            // Never saved: the defaults show every text.
            if ( !is_array( $options ) ) {
                continue;
            }
            // Gift and Discount bars had no "Show message" of their own; they used the Progress Bar's.
            if ( $option_name !== 'devnet_fsl_bar' && !array_key_exists( 'show_qualified_message', $options ) ) {
                $options['show_qualified_message'] = $show_message;
            }
            // The form fills in every default, so an empty text was cleared on purpose: switch it off.
            // Not with Multilingual on, where an empty text shows the translated default.
            if ( !$multilingual ) {
                foreach ( $switches as $text => $switch ) {
                    if ( ($options[$text] ?? null) === '' ) {
                        $options[$switch] = 0;
                    }
                }
                // An emptied reached message showed nothing; it now shows the default. Without the full bar (the
                // Progress Bar's, for every bar), "Show message" off shows nothing too.
                if ( empty( $full_bar ) && ($options['qualified_message'] ?? null) === '' && !empty( $options['show_qualified_message'] ) ) {
                    $options['show_qualified_message'] = 0;
                }
            }
            update_option( $option_name, $options );
        }
        // An emptied label text showed an empty box; it now shows the default. Not for an image label (the image
        // showed) or a label with its own Multilingual (sites from before 3.5.0: the default showed).
        $label = get_option( 'devnet_fsl_label' );
        if ( is_array( $label ) && !empty( $label['enable_label'] ) && ($label['text'] ?? null) === '' && !(!empty( $label['enable_image_label'] ) && !empty( $label['image'] )) && empty( $label['multilingual'] ) ) {
            $label['enable_label'] = 0;
            update_option( 'devnet_fsl_label', $label );
        }
    }

    /**
     * Multilingual (until 3.5.x) showed the translated defaults instead of the saved texts. Since 4.0.0 an empty
     * text shows them, so empty the texts it replaced: the storefront stays the same. FSL Discount clears its own.
     * The `multilingual` value stays saved (FSL Discount 1.0.8 and older, downgrades).
     *
     * The Product Label followed its own `multilingual` key (sites from before 3.5.0), not General's: with it, the label
     * showed the default and its text is emptied. With General's only, the label showed the saved text; it's emptied
     * only when it's the default, so translations take over without a visible change, and a custom text stays.
     *
     * @since	4.0.0
     */
    private static function clear_multilingual_texts() {
        $general = get_option( 'devnet_fsl_general' );
        $multilingual = is_array( $general ) && !empty( $general['multilingual'] );
        $texts = ( $multilingual ? [
            'devnet_fsl_bar'      => ['title', 'description', 'qualified_message'],
            'devnet_fsl_gift_bar' => [
                'label',
                'title',
                'description',
                'qualified_message'
            ],
        ] : [] );
        foreach ( $texts as $option_name => $keys ) {
            $options = get_option( $option_name );
            // Never saved: the defaults already show.
            if ( !is_array( $options ) ) {
                continue;
            }
            foreach ( $keys as $key ) {
                if ( array_key_exists( $key, $options ) ) {
                    $options[$key] = '';
                }
            }
            update_option( $option_name, $options );
        }
        $label = get_option( 'devnet_fsl_label' );
        if ( !is_array( $label ) || ($label['text'] ?? '') === '' ) {
            return;
        }
        // The default as saved: in English, or in the language the settings were saved in.
        $is_default = in_array( trim( $label['text'] ), ['Free shipping!', Defaults::label( 'text' ), html_entity_decode( Defaults::label( 'text' ), ENT_QUOTES )], true );
        if ( !empty( $label['multilingual'] ) || $multilingual && $is_default ) {
            $label['text'] = '';
            update_option( 'devnet_fsl_label', $label );
        }
    }

}
