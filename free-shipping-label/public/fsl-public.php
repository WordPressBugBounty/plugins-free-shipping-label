<?php

namespace Devnet\FSL\Frontend;

use Devnet\FSL\Frontend\Bar\FSL_Bar;

class FSL_Public
{

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style('free-shipping-label-public', plugin_dir_url(__DIR__) . 'assets/build/fsl-public.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		$script_asset_path = plugin_dir_url(__DIR__) . 'assets/build/fsl-public.asset.php';
		$script_info       = file_exists($script_asset_path)
			? include $script_asset_path
			: ['dependencies' => ['jquery', 'wp-data'], 'version' => $this->version];

		wp_enqueue_script(
			'fsl-public',
			plugin_dir_url(__DIR__) . 'assets/build/fsl-public.js',
			$script_info['dependencies'],
			$script_info['version'],
			true
		);

		wp_localize_script('fsl-public', 'devnet_fsl_ajax', [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'options' => DEVNET_FSL_OPTIONS,
			'placeholder_html' => FSL_Bar::fsl_bar_placeholder(),
		]);
	}


	/**
	 * Hide shipping rates when free shipping is available.
	 *
	 * @param array $rates Array of rates found for the package.
	 * @return array
	 */
	public function hide_shipping_when_free_is_available($rates)
	{
		$fsl_general         = DEVNET_FSL_OPTIONS['general'] ?? [];
		$hide_shipping_rates = isset($fsl_general['hide_shipping_rates']) ? $fsl_general['hide_shipping_rates'] : false;

		if (!$hide_shipping_rates) {
			return $rates;
		}

		$free = [];

		if ('hide_all' === $hide_shipping_rates) {
			foreach ($rates as $rate_id => $rate) {
				if ('free_shipping' === $rate->method_id) {
					$free[$rate_id] = $rate;
					break;
				}
			}
			return !empty($free) ? $free : $rates;
		}

		if ('hide_except_local' === $hide_shipping_rates) {
			foreach ($rates as $rate_id => $rate) {
				// Only modify rates if free_shipping is present.
				if ('free_shipping' === $rate->method_id) {
					$new_rates[$rate_id] = $rate;
					break;
				}
			}

			if (!empty($new_rates)) {
				//Save local pickup if it's present.
				foreach ($rates as $rate_id => $rate) {
					if ('local_pickup' === $rate->method_id) {
						$new_rates[$rate_id] = $rate;
						break;
					}
				}
				return $new_rates;
			}
		}

		return $rates;
	}
}
