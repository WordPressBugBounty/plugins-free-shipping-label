<?php

/**
 * Plugin Name:          Free Shipping Label
 * Plugin URI:           https://devnet.hr/plugins/free-shipping-label/
 * Description:          Increase order revenue in WooCommerce store by showing your customers just how close they are to your free shipping threshold.
 * Version:              3.3.2
 * Author:               Devnet
 * Author URI:           https://devnet.hr
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:          /languages
 * Requires Plugins:     woocommerce
 * WC tested up to:      9.8
 *
 */
use Devnet\FSL\Includes\Activator;
use Devnet\FSL\Includes\Deactivator;
use Devnet\FSL\Includes\FSL_PLUGIN;
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    exit;
}
if ( function_exists( 'fsl_fs' ) ) {
    fsl_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'fsl_fs' ) ) {
        // Create a helper function for easy SDK access.
        function fsl_fs() {
            global $fsl_fs;
            if ( !isset( $fsl_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/fs/freemius/start.php';
                $fsl_fs = fs_dynamic_init( [
                    'id'             => '11160',
                    'slug'           => 'free-shipping-label',
                    'premium_slug'   => 'free-shipping-label-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_87b72065d40019ae11ff0dab36c7b',
                    'is_premium'     => false,
                    'premium_suffix' => '(Pro)',
                    'has_addons'     => true,
                    'has_paid_plans' => true,
                    'trial'          => [
                        'days'               => 7,
                        'is_require_payment' => true,
                    ],
                    'menu'           => [
                        'slug'   => 'free-shipping-label-settings',
                        'parent' => [
                            'slug' => 'woocommerce',
                        ],
                    ],
                    'is_live'        => true,
                ] );
            }
            return $fsl_fs;
        }

        // Init Freemius.
        fsl_fs();
        // Signal that SDK was initiated.
        do_action( 'fsl_fs_loaded' );
    }
    /**
     * Show the contact submenu item only when the user have a valid non-expired license.
     *
     * @param $is_visible The filtered value. Whether the submenu item should be visible or not.
     * @param $menu_id    The ID of the submenu item.
     *
     * @return bool If true, the menu item should be visible.
     */
    if ( !function_exists( 'fsl_is_submenu_visible' ) ) {
        function fsl_is_submenu_visible(  $is_visible, $menu_id  ) {
            if ( 'contact' != $menu_id ) {
                return $is_visible;
            }
            return fsl_fs()->can_use_premium_code();
        }

    }
    /**
     * TODO: do uninstall logic.
     */
    if ( !function_exists( 'fsl_fs_uninstall_cleanup' ) ) {
        function fsl_fs_uninstall_cleanup() {
        }

    }
    /**
     * Run Freemius actions and filters.
     */
    if ( function_exists( 'fsl_fs' ) ) {
        fsl_fs()->add_filter(
            'is_submenu_visible',
            'fsl_is_submenu_visible',
            10,
            2
        );
        fsl_fs()->add_action( 'after_uninstall', 'fsl_fs_uninstall_cleanup' );
    }
    define( 'DEVNET_FSL_VERSION', '3.3.2' );
    define( 'DEVNET_FSL_NAME', 'free-shipping-label' );
    define( 'DEVNET_FSL_PATH', plugin_basename( __FILE__ ) );
    define( 'DEVNET_FSL_OPTIONS', [
        'general'      => get_option( 'devnet_fsl_general' ),
        'progress_bar' => get_option( 'devnet_fsl_bar' ),
        'gift_bar'     => get_option( 'devnet_fsl_gift_bar' ),
        'notice_bar'   => get_option( 'devnet_fsl_notice_bar' ),
        'label'        => get_option( 'devnet_fsl_label' ),
    ] );
    /**
     * Default settings class
     * 
     */
    require_once plugin_dir_path( __FILE__ ) . 'includes/fsl-defaults.php';
    /**
     * The code that runs during plugin initiation.
     * 
     */
    add_action( 'init', function () {
        require_once plugin_dir_path( __FILE__ ) . 'includes/fsl-activator.php';
        Activator::check_and_format_options();
    } );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/fsl-activator.php
     */
    function activate_devnet_fsl() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/fsl-activator.php';
        Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/fsl-deactivator.php
     */
    function deactivate_devnet_fsl() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/fsl-deactivator.php';
        Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'activate_devnet_fsl' );
    register_deactivation_hook( __FILE__, 'deactivate_devnet_fsl' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/fsl-plugin.php';
    /**
     * Begins execution of the plugin.
     *
     * @since    1.0.0
     */
    function run_devnet_fsl() {
        $plugin = new FSL_PLUGIN();
        $plugin->run();
    }

    add_action( 'plugins_loaded', function () {
        // Go ahead only if WooCommerce is activated.
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            run_devnet_fsl();
        } else {
            // no woocommerce :(
            add_action( 'admin_notices', function () {
                $class = 'notice notice-error';
                $message = esc_html__( 'The “Free Shipping Label” plugin cannot run without WooCommerce. Please install and activate WooCommerce plugin.', 'free-shipping-label' );
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
            } );
            return;
        }
    } );
}