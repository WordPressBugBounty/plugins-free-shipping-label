<?php

namespace Devnet\FSL\Includes;

class i18n
{

    public function __construct()
    {
        add_action('init', [$this, 'load_plugin_textdomain']);
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'free-shipping-label',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
