<?php

namespace Devnet\FSL\Includes;


if (! defined('ABSPATH')) {
    exit;
}


class Uninstaller
{

    /**	
     * @since   3.5.0
     */
    public static function cleanup()
    {

        $all_plugins = get_plugins();

        $fsl_slug = 'free-shipping-label';

        $pph_plugin = [$fsl_slug . '/' . $fsl_slug . '.php', $fsl_slug . '-pro/' . $fsl_slug . '.php',];

        // Ensure no data has ben deleted if both plugins are installed.
        if (!isset($all_plugins[$pph_plugin[0]], $all_plugins[$pph_plugin[1]])) {

            self::delete_options();
        }
    }

    public static function delete_options()
    {
        delete_option('devnet_fsl');
        delete_option('devnet_fsl_general');
        delete_option('devnet_fsl_bar');
        delete_option('devnet_fsl_gift_bar');
        delete_option('devnet_fsl_notice_bar');
        delete_option('devnet_fsl_label');
        delete_option('devnet_fsl_multilingual_check');
        delete_option('devnet_fsl_layout_check');
        delete_option('devnet_fsl_migration_350');
    }
}
