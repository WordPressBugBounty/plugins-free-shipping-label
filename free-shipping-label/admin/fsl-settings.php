<?php

namespace Devnet\FSL\Admin;


class Settings
{

    private $settings_api;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct()
    {

        $this->settings_api = new Settings_API('devnet_fsl');
    }

    public function admin_init()
    {
        $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
        $option_page = isset($_REQUEST['option_page']) ? sanitize_text_field($_REQUEST['option_page']) : '';

        $is_settings_page = $page === 'free-shipping-label-settings';

        // When saving options.
        $is_option_page = !empty($option_page) && strpos($option_page, 'devnet_fsl') === 0;


        if (!$is_settings_page && !$is_option_page) {
            return;
        }

        //set the settings
        $this->settings_api->set_sections($this->get_settings_sections());
        $this->settings_api->set_fields($this->get_settings_fields());

        //initialize settings
        $this->settings_api->admin_init();
    }

    public function admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            'Free Shipping Label',
            'Free Shipping Label',
            apply_filters('fsl_admin_menu_user_capability', 'manage_options'),
            'free-shipping-label-settings',
            [$this, 'plugin_page']
        );
    }

    public function get_settings_sections()
    {

        $sections[] = [
            'id'    => 'devnet_fsl_general',
            'title' => esc_html__('General Settings', 'free-shipping-label')
        ];

        $sections[] = [
            'id'    => 'devnet_fsl_bar',
            'title' => esc_html__('Progress Bar', 'free-shipping-label')
        ];

        $sections[] = [
            'id'    => 'devnet_fsl_gift_bar',
            'title' => esc_html__('Gift Bar', 'free-shipping-label')
        ];

        $sections[] = [
            'id'    => 'devnet_fsl_notice_bar',
            'title' => esc_html__('Notice Bar', 'free-shipping-label')
        ];

        $sections[] = [
            'id'    => 'devnet_fsl_label',
            'title' => esc_html__('Product Label', 'free-shipping-label')
        ];

        return apply_filters('fsl_settings_sections', $sections);
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields()
    {
        $settings_fields = [
            'devnet_fsl_general'    => Options::general(),
            'devnet_fsl_bar'        => Options::progress_bar(),
            'devnet_fsl_gift_bar'   => Options::gift_bar(),
            'devnet_fsl_notice_bar' => Options::notice_bar(),
            'devnet_fsl_label'      => Options::product_label(),
        ];

        return apply_filters('fsl_settings_fields', $settings_fields);
    }

    public function plugin_page()
    {
        echo '<div class="fsl-wrap devnet-plugin-settings-page" data-id="devnet_fsl">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    public function get_pages()
    {
        $pages = get_pages();
        $pages_options = [];
        if ($pages) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

    /**
     * Output description text under panel title.
     * 
     * @since   2.6.0
     */
    public function panel_description($form)
    {

        $id = isset($form['id']) ? $form['id'] : '';


        $inner = '';

        $html = '<div class="devnet-plugin-panel-description">';

        if ('devnet_fsl_general' === $id) {
            $inner .= '<p>';

            $inner .= sprintf(
                '%s <a href="https://devnet.hr/docs/free-shipping-label/woocommerce-settings/" target="_blank">%s</a> %s',
                esc_html__('To ensure a smooth setup process, we recommend reviewing the ', 'free-shipping-label'),
                esc_html__('documentation', 'free-shipping-label'),
                esc_html__('for more detailed information on the available options and how to use them. Should you have any further questions or need assistance, please don\'t hesitate to reach out to our support team.', 'free-shipping-label')
            );

            $inner .= '</p>';

            $html .= $inner;
        }

        if ('devnet_fsl_gift_bar' === $id) {
            $inner .= '<p>';

            $inner .= sprintf(
                '%s <a href="https://devnet.hr/docs/free-shipping-label/gift-bar/" target="_blank">%s</a> %s',
                esc_html__('To display the Gift Bar, make sure the Progress Bar is enabled. Take a moment to explore the', 'free-shipping-label'),
                esc_html__('documentation', 'free-shipping-label'),
                esc_html__('for in-depth details.', 'free-shipping-label')
            );

            $inner .= '</p>';

            $html .= $inner;
        }

        if ('devnet_fsl_notice_bar' === $id) {
            $inner .= '<p>';

            $inner .= sprintf(
                '%s <a href="https://devnet.hr/docs/free-shipping-label/notice-bar/" target="_blank">%s</a> %s',
                esc_html__('In order for Notice Bar to work properly, your theme should support AJAX add-to-cart option. Meaning, there is no page reload after product is added to the cart. Check ', 'free-shipping-label'),
                esc_html__('documentation', 'free-shipping-label'),
                esc_html__('for more detailed information.', 'free-shipping-label')
            );

            $inner .= '</p>';

            $html .= $inner;
        }

        $html .= '</div>';

        if (!$inner) {
            $html = '';
        }

        if (get_option('woocommerce_shipping_cost_requires_address') === 'yes') {
            $alert_html = '';

            $alert_html = '<div class="devnet-plugin-panel-description devnet-plugin-alert">';
            $alert_html .= '<p>';

            $alert_html .= wp_kses_post(__('<strong>Attention:</strong> Visibility of Progress Bar and Label is Dependent on Customer Address Entry.<br>
            The Progress Bar and Label will not be visible until the customer enters an address. This behavior is influenced by your WooCommerce settings. To resolve this, navigate to <strong>WooCommerce → Settings → Shipping → Shipping Options</strong>, and ensure that the option <strong>“Hide shipping costs until an address is entered”</strong> is unchecked.', 'free-shipping-label'));

            $alert_html .= '</p>';
            $alert_html .= '</div>';

            $html = $alert_html . $html;
        }

        echo $html;
    }
}
