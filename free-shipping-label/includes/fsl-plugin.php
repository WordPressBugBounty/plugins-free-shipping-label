<?php

namespace Devnet\FSL\Includes;

use Devnet\FSL\Includes\Helper;
use Devnet\FSL\Admin\FSL_Admin;
use Devnet\FSL\Frontend\FSL_Public;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class FSL_PLUGIN {
    protected $plugin_name;

    protected $version;

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
    }

    private function load_dependencies() {
        // require_once plugin_dir_path(dirname(__FILE__)) . 'includes/fsl-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fsl-i18n.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fsl-compatibility.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fsl-helper.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fsl-icons.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-settings-api.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-options.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-settings.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fsl-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/fsl-public.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/fsl-label.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/bar/fsl-bar.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/bar/fsl-gift-bar.php';
    }

    public function run() {
        new Helper();
        new FSL_Admin($this->get_plugin_name(), $this->get_version());
        new FSL_Public($this->get_plugin_name(), $this->get_version());
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

}
