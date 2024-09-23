<?php

namespace Devnet\FSL\Includes;

use Devnet\FSL\Admin\Settings;
use Devnet\FSL\Admin\FSL_Admin;
use Devnet\FSL\Frontend\FSL_Public;
use Devnet\FSL\Frontend\FSL_Label;
use Devnet\FSL\Frontend\Bar\FSL_Bar;
use Devnet\FSL\Frontend\Bar\Gift_Bar;
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 */
class FSL_PLUGIN {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'DEVNET_FSL_VERSION' ) ) {
            $this->version = DEVNET_FSL_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        if ( defined( 'DEVNET_FSL_NAME' ) ) {
            $this->plugin_name = DEVNET_FSL_NAME;
        } else {
            $this->plugin_name = 'free-shipping-label';
        }
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Loader. Orchestrates the hooks of the plugin.
     * - i18n. Defines internationalization functionality.
     * - Admin. Defines all hooks for the admin area.
     * - Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fsl-loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fsl-i18n.php';
        /**
         * The class responsible for defining helper functions of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fsl-helper.php';
        /**
         * The class wrapper for handling WordPress Settings API.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-settings-api.php';
        /**
         * The class responsible for defining all options.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-options.php';
        /**
         * The class responsible for defining all actions that occur in the settings panel.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-settings.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-admin.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/fsl-public.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/fsl-label.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/bar/fsl-bar.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/bar/fsl-gift-bar.php';
        $this->loader = new Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new i18n();
        $this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $page = ( isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : null );
        $plugin_settings = new Settings();
        $this->loader->add_action( 'admin_init', $plugin_settings, 'admin_init' );
        $this->loader->add_action(
            'admin_menu',
            $plugin_settings,
            'admin_menu',
            100
        );
        $this->loader->add_action( 'devnet_fsl_form_top', $plugin_settings, 'panel_description' );
        $plugin_admin = new FSL_Admin($this->get_plugin_name(), $this->get_version());
        if ( $page === 'free-shipping-label-settings' ) {
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
            $this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'admin_credits' );
        }
        $this->loader->add_filter( 'plugin_action_links_' . DEVNET_FSL_PATH, $plugin_admin, 'plugin_action_links' );
        $this->loader->add_action( 'before_woocommerce_init', $plugin_admin, 'cot_compatible' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        if ( is_admin() && !wp_doing_ajax() ) {
            return;
        }
        $fsl_options = DEVNET_FSL_OPTIONS;
        $general_opt = $fsl_options['general'] ?? [];
        $progress_bar_opt = $fsl_options['progress_bar'] ?? [];
        $gift_bar_opt = $fsl_options['gift_bar'] ?? [];
        $label_opt = $fsl_options['label'] ?? [];
        $fsl_public = new FSL_Public($this->get_plugin_name(), $this->get_version());
        $fsl_label = new FSL_Label();
        $fsl_bar = new FSL_Bar();
        $fsl_gift_bar = new Gift_Bar();
        $only_logged_users = $general_opt['only_logged_users'] ?? false;
        if ( $only_logged_users && !is_user_logged_in() ) {
            return;
        }
        $this->loader->add_action( 'wp_enqueue_scripts', $fsl_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $fsl_public, 'enqueue_scripts' );
        $enabled_progress_bar = $progress_bar_opt['enable_bar'] ?? false;
        $this->configure_bar_positions( $progress_bar_opt, $enabled_progress_bar, $fsl_bar );
        $enabled_gift_bar = $gift_bar_opt['enable_bar'] ?? false;
        $gift_bar_after_threshold = $gift_bar_opt['after_threshold'] ?? '';
        if ( $enabled_gift_bar ) {
            $this->configure_bar_positions( $gift_bar_opt, $enabled_gift_bar, $fsl_gift_bar );
            $this->loader->add_filter( 'fsl_progress_bar_setup_data', $fsl_gift_bar, 'setup_data_for_gift_bar' );
        }
        $enable_label = $label_opt['enable_label'] ?? false;
        if ( $enable_label ) {
            $this->loader->add_action( 'wp_loaded', $fsl_label, 'public_data' );
            $this->loader->add_filter(
                'woocommerce_get_price_html',
                $fsl_label,
                'get_price_html',
                99999,
                2
            );
        }
        $hide_shipping_rates = $general_opt['hide_shipping_rates'] ?? false;
        add_filter(
            'transient_shipping-transient-version',
            function ( $value, $name ) {
                return false;
            },
            10,
            2
        );
        if ( $hide_shipping_rates ) {
            $this->loader->add_filter(
                'woocommerce_package_rates',
                $fsl_public,
                'hide_shipping_when_free_is_available',
                10,
                2
            );
        }
    }

    private function configure_bar_positions( $options, $enable, $fsl_bar ) {
        $show_on_cart = $options['show_on_cart'] ?? false;
        $show_on_minicart = $options['show_on_minicart'] ?? false;
        $show_on_checkout = $options['show_on_checkout'] ?? false;
        if ( $enable ) {
            if ( $show_on_cart ) {
                $cart_hook = 'woocommerce_proceed_to_checkout';
                $this->loader->add_action(
                    $cart_hook,
                    $fsl_bar,
                    'fsl_bar_cart',
                    10
                );
            }
            if ( $show_on_minicart ) {
                $minicart_hook = 'woocommerce_widget_shopping_cart_before_buttons';
                $this->loader->add_action( $minicart_hook, $fsl_bar, 'fsl_bar_minicart' );
            }
            if ( $show_on_checkout ) {
                $checkout_hook = 'woocommerce_review_order_before_submit';
                $this->loader->add_action(
                    $checkout_hook,
                    $fsl_bar,
                    'fsl_bar_checkout',
                    10
                );
            }
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
