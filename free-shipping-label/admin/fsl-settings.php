<?php

namespace Devnet\FSL\Admin;

use Devnet\FSL\Frontend\Bar\Gift_Bar;
use Devnet\FSL\Includes\Icons;
use Devnet\FSL\Includes\Presets;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Settings {
    private $settings_api;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct() {
        $this->settings_api = new Settings_API('devnet_fsl');
        $this->admin_init();
    }

    public function admin_init() {
        $page = ( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' );
        $option_page = ( isset( $_REQUEST['option_page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['option_page'] ) ) : '' );
        $is_settings_page = $page === 'free-shipping-label-settings';
        // When saving options.
        $is_option_page = !empty( $option_page ) && strpos( $option_page, 'devnet_fsl' ) === 0;
        if ( !$is_settings_page && !$is_option_page ) {
            return;
        }
        //set the settings
        $sections = $this->get_settings_sections();
        $this->settings_api->set_sections( $sections );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();
        // options.php saves with `manage_options` unless told otherwise: use the menu capability for every
        // section (add-on tabs included), so users who can open the page can also save it.
        $capability = apply_filters( 'fsl_admin_menu_user_capability', 'manage_options' );
        foreach ( $sections as $section ) {
            add_filter( 'option_page_capability_' . $section['id'], function () use($capability) {
                return $capability;
            } );
        }
        // Calling it from here to avoid unnecessary code execution.
        add_action( 'devnet_fsl_form_top', [$this, 'panel_description'] );
        add_action( 'devnet_fsl_form_top_devnet_fsl_bar', [Options::class, 'live_preview'] );
        add_action( 'devnet_fsl_form_top_devnet_fsl_general', [Threshold_Status::class, 'render'] );
    }

    public function get_settings_sections() {
        $sections[] = [
            'id'    => 'devnet_fsl_general',
            'title' => esc_html__( 'Free Shipping', 'free-shipping-label' ),
        ];
        $sections[] = [
            'id'    => 'devnet_fsl_bar',
            'title' => esc_html__( 'Progress Bar', 'free-shipping-label' ),
        ];
        if ( Gift_Bar::module_active() ) {
            $sections[] = [
                'id'    => 'devnet_fsl_gift_bar',
                'title' => esc_html__( 'Gift Bar', 'free-shipping-label' ),
            ];
        }
        $sections[] = [
            'id'    => 'devnet_fsl_notice_bar',
            'title' => esc_html__( 'Notice Bar', 'free-shipping-label' ),
        ];
        $sections[] = [
            'id'    => 'devnet_fsl_label',
            'title' => esc_html__( 'Product Label', 'free-shipping-label' ),
        ];
        return apply_filters( 'fsl_settings_sections', $sections );
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields() {
        $settings_fields = [
            'devnet_fsl_general'    => Options::general(),
            'devnet_fsl_bar'        => Options::progress_bar(),
            'devnet_fsl_notice_bar' => Options::notice_bar(),
            'devnet_fsl_label'      => Options::product_label(),
        ];
        if ( Gift_Bar::module_active() ) {
            $settings_fields['devnet_fsl_gift_bar'] = Options::gift_bar();
        }
        return apply_filters( 'fsl_settings_fields', $settings_fields );
    }

    public function settings_page() {
        // settings.js needs data-id to scope its localStorage tab-state keys per plugin (see its top comment).
        echo '<div class="fsl-wrap devnet-plugin-settings-page" data-id="devnet_fsl">';
        printf(
            '<h1 class="fsl-page-title"><span class="fsl-page-mark" aria-hidden="true">%1$s</span>%2$s <span class="fsl-page-version">%3$s</span></h1>',
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The plugin's own SVG.
            Icons::get_svg( 'delivery-truck-5', 22 ),
            esc_html__( 'Free Shipping Label', 'free-shipping-label' ),
            esc_html( DEVNET_FSL_VERSION )
        );
        $this->settings_api->show_navigation();
        // Admin notices go under the header: WordPress moves them after .wp-header-end.
        echo '<hr class="wp-header-end">';
        // "Settings saved." and the fields' errors show beside the Save button (Settings_API::save_messages()).
        $this->settings_api->show_forms();
        echo '</div>';
    }

    /**
     * Output description text under panel title.
     * 
     * @since   2.6.0
     */
    public function panel_description( $form ) {
        $id = ( isset( $form['id'] ) ? $form['id'] : '' );
        $inner = '';
        $html = '<div class="devnet-plugin-panel-description">';
        if ( 'devnet_fsl_general' === $id ) {
            $inner .= '<p>';
            $inner .= sprintf( 
                /* translators: 1: <a> to the documentation, 2: </a> */
                esc_html__( 'New here? The %1$sdocumentation%2$s explains every option. Questions? Contact our support.', 'free-shipping-label' ),
                '<a href="https://devnet.hr/docs/free-shipping-label/woocommerce-settings/" target="_blank">',
                '</a>'
             );
            $inner .= '</p>';
            $html .= $inner;
        }
        if ( 'devnet_fsl_gift_bar' === $id ) {
            $inner .= '<p>';
            $inner .= sprintf( 
                /* translators: 1: <a> to the documentation, 2: </a> */
                esc_html__( 'The Gift Bar shows with the Progress Bar, so keep it enabled. See the %1$sdocumentation%2$s.', 'free-shipping-label' ),
                '<a href="https://devnet.hr/docs/free-shipping-label/gift-bar/" target="_blank">',
                '</a>'
             );
            $inner .= '</p>';
            $html .= $inner;
        }
        if ( 'devnet_fsl_notice_bar' === $id ) {
            $inner .= '<p>';
            $inner .= sprintf( 
                /* translators: 1: <a> to the documentation, 2: </a> */
                esc_html__( 'The Notice Bar pops up when a product is added to the cart without a page reload, so your theme needs AJAX add to cart. See the %1$sdocumentation%2$s.', 'free-shipping-label' ),
                '<a href="https://devnet.hr/docs/free-shipping-label/notice-bar/" target="_blank">',
                '</a>'
             );
            $inner .= '</p>';
            $html .= $inner;
        }
        $html .= '</div>';
        if ( !$inner ) {
            $html = '';
        }
        // The "Hide shipping costs until an address is entered" alert is in the Free shipping status (Threshold_Status).
        echo wp_kses_post( $html );
    }

}
