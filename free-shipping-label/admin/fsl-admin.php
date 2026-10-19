<?php

namespace Devnet\FSL\Admin;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class FSL_Admin {
    private $plugin_name;

    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $page = ( isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : null );
        if ( $page === 'free-shipping-label-settings' ) {
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
            add_filter( 'admin_footer_text', [$this, 'admin_credits'] );
        }
        add_action( 'admin_menu', [$this, 'admin_menu'], 100 );
        add_action( 'wp_ajax_devnet_fsl_bar_preview', [$this, 'bar_preview'] );
        if ( is_admin() ) {
            add_action( 'init', [$this, 'more_free_plugins_submenu'] );
        }
        add_filter( 'plugin_action_links_' . DEVNET_FSL_PATH, [$this, 'plugin_action_links'] );
        add_action( 'before_woocommerce_init', [$this, 'cot_compatible'] );
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style(
            'free-shipping-label-admin',
            plugin_dir_url( __DIR__ ) . 'assets/build/fsl-admin.css',
            [],
            $this->version,
            'all'
        );
        // The design previews are real bars, with the theme's palette variables for Theme Match, as on the storefront.
        wp_enqueue_style(
            'free-shipping-label-public',
            plugin_dir_url( __DIR__ ) . 'assets/build/fsl-public.css',
            [],
            $this->version,
            'all'
        );
        wp_add_inline_style( 'free-shipping-label-public', wp_get_global_stylesheet( ['variables'] ) );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_register_script(
            'wp-color-picker-alpha',
            plugin_dir_url( __DIR__ ) . 'assets/color-picker/wp-color-picker-alpha.min.js',
            ['wp-color-picker'],
            $this->version,
            true
        );
        if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'free-shipping-label-settings' ) {
            wp_enqueue_script( 'wp-color-picker-alpha' );
        }
        $script_asset_path = plugin_dir_path( __DIR__ ) . 'assets/build/fsl-admin.asset.php';
        $script_info = ( file_exists( $script_asset_path ) ? include $script_asset_path : [
            'dependencies' => ['jquery'],
            'version'      => $this->version,
        ] );
        wp_enqueue_script(
            'fsl-admin',
            plugin_dir_url( __DIR__ ) . 'assets/build/fsl-admin.js',
            $script_info['dependencies'],
            $script_info['version'],
            true
        );
        // Outside the array: Freemius's build drops comments on array items, and the translators comment must stay.
        /* translators: 1: the number of characters that fit on one line, 2: the text's number of characters. */
        $length_tip = __( '%2$d characters: may wrap to two lines in a narrow mini-cart (about %1$d fit).', 'free-shipping-label' );
        wp_localize_script( 'fsl-admin', 'devnet_fsl_admin_ajax', [
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'devnet_fsl_search' ),
            'lengthTip' => $length_tip,
        ] );
    }

    public function admin_menu() {
        // Loaded here instead of on every request; add-ons use Options only in their `fsl_settings_fields` callbacks.
        require_once plugin_dir_path( __FILE__ ) . 'fsl-settings-api.php';
        require_once plugin_dir_path( __FILE__ ) . 'fsl-options.php';
        require_once plugin_dir_path( __FILE__ ) . 'fsl-settings.php';
        require_once plugin_dir_path( __FILE__ ) . 'fsl-threshold-status.php';
        $plugin_settings = new Settings();
        add_submenu_page(
            'woocommerce',
            'Free Shipping Label',
            'Free Shipping Label',
            apply_filters( 'fsl_admin_menu_user_capability', 'manage_options' ),
            'free-shipping-label-settings',
            [$plugin_settings, 'settings_page']
        );
    }

    /**
     * The Progress Bar live preview and the layout cards: the form's values, sanitized as a save sanitizes them, at
     * the cart total of the preview's slider.
     *
     * @since	4.0.0
     */
    public function bar_preview() {
        check_ajax_referer( 'devnet_fsl_bar_preview' );
        if ( !current_user_can( apply_filters( 'fsl_admin_menu_user_capability', 'manage_options' ) ) ) {
            wp_send_json_error( null, 403 );
        }
        require_once plugin_dir_path( __FILE__ ) . 'fsl-settings-api.php';
        require_once plugin_dir_path( __FILE__ ) . 'fsl-options.php';
        $settings_api = new Settings_API('devnet_fsl');
        $settings_api->set_fields( [
            'devnet_fsl_bar' => Options::progress_bar(),
        ] );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field's own sanitize callback, as on save.
        $values = $settings_api->sanitize_options( (array) wp_unslash( $_POST['devnet_fsl_bar'] ?? [] ) );
        // The preview slider's progress, 0 to 100%. fsl-admin.js sends it as fsl_preview_cart and shows `cart` next to it.
        $percent = ( isset( $_POST['fsl_preview_cart'] ) ? min( 100, absint( wp_unslash( $_POST['fsl_preview_cart'] ) ) ) : Options::PREVIEW_PERCENT );
        // The goals the preview's buttons show (Options::preview_goals() names); none sent, all of them.
        $shown = ( isset( $_POST['fsl_preview_goals'] ) ? array_filter( explode( ',', sanitize_text_field( wp_unslash( $_POST['fsl_preview_goals'] ) ) ) ) : [] );
        wp_send_json_success( [
            'bar'     => Options::bar_preview_html( $values, $percent, $shown ),
            'layouts' => Options::layout_previews( $values, $percent ),
            'cart'    => esc_html( $percent . '%' ),
        ] );
    }

    /**
     * Add the "More Free Plugins" submenu after Freemius adds its submenu items.
     *
     */
    public function more_free_plugins_submenu() {
        $url = add_query_arg( [
            'tab'      => 'favorites',
            'user'     => 'devnethr',
            'save'     => 0,
            '_wpnonce' => wp_create_nonce( 'save_wporg_username_' . get_current_user_id() ),
        ], self_admin_url( 'plugin-install.php' ) );
        fsl_fs()->add_submenu_link_item(
            esc_html__( 'More Free Plugins', 'free-shipping-label' ),
            $url,
            'devnet-plugins',
            'install_plugins',
            PHP_INT_MAX
        );
    }

    /**
     * Add plugin action link.
     *
     * @since    1.0.0
     */
    public function plugin_action_links( $links ) {
        $custom_links = [];
        $custom_links[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=free-shipping-label-settings' ) ) . '">' . esc_html__( 'Settings', 'free-shipping-label' ) . '</a>';
        return array_merge( $custom_links, $links );
    }

    /**
     * Modifies the admin credits.
     * 
     * * @since    1.0.0
     */
    public function admin_credits( $footer_text ) {
        $footer_text = '';
        $footer_text .= '<div class="fsl-admin-footer">';
        $footer_text .= '<div class="fsl-footer-message">';
        $footer_text .= 'Please rate <strong>Free Shipping Label</strong> <a href="https://wordpress.org/support/plugin/free-shipping-label/reviews/" target="_blank">★★★★★</a> on <a href="https://wordpress.org/support/plugin/free-shipping-label/reviews/" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the <a href="https://devnet.hr/" target="_blank">Devnet</a> team!';
        $footer_text .= '</div>';
        $footer_text .= '<div class="fsl-donation">';
        $footer_text .= '<a href="https://devnet.hr/plugins/free-shipping-label/" target="_blank">Go PRO: More Features, More Power!</a>';
        $footer_text .= '</div>';
        $footer_text .= '</div>';
        return $footer_text;
    }

    /**
     * Declare that plugin is COT (HPOS) and Cart & Checkout blocks compatible.
     * 
     * @since	2.6.0
     */
    public function cot_compatible() {
        if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
            $plugin_file = plugin_dir_path( dirname( __FILE__ ) ) . 'free-shipping-label.php';
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $plugin_file, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $plugin_file, true );
        }
    }

}
