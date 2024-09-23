<?php

namespace Devnet\FSL\Admin;

class FSL_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
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
        if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'free-shipping-label-settings' ) {
            wp_enqueue_script( 'wp-color-picker-alpha' );
        }
        $script_asset_path = plugin_dir_url( __DIR__ ) . 'assets/build/fsl-admin.asset.php';
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
        wp_localize_script( 'fsl-admin', 'devnet_fsl_admin_ajax', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ] );
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
        $footer_text .= 'Please rate <strong>Free Shipping Label</strong> <a href="https://wordpress.org/support/plugin/free-shipping-label/reviews/?rate=5#new-post" target="_blank">★★★★★</a> on <a href="https://wordpress.org/support/plugin/free-shipping-label/reviews/?rate=5#new-post" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the <a href="https://devnet.hr/" target="_blank">Devnet</a> team!';
        $footer_text .= '</div>';
        $footer_text .= '<div class="fsl-donation">';
        $footer_text .= '<a href="https://devnet.hr/plugins/free-shipping-label/" target="_blank">Go PRO: More Features, More Power!</a>';
        $footer_text .= '</div>';
        $footer_text .= '</div>';
        return $footer_text;
    }

    /**
     * Declare that plugin is COT compatible.
     * 
     * @since	2.6.0
     */
    public function cot_compatible() {
        if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
            $plugin_file = plugin_dir_path( dirname( __FILE__ ) ) . 'free-shipping-label.php';
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $plugin_file, true );
        }
    }

}
