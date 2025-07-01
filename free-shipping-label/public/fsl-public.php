<?php

namespace Devnet\FSL\Frontend;

use Devnet\FSL\Frontend\FSL_Label;
use Devnet\FSL\Frontend\Bar\FSL_Bar;
use Devnet\FSL\Frontend\Bar\Gift_Bar;


if (! defined('ABSPATH')) {
	exit;
}


class FSL_Public
{

	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version)
	{
		if (is_admin() && !wp_doing_ajax()) {
			return;
		}

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$options     		 = DEVNET_FSL_OPTIONS['general'] ?? [];
		$only_logged_users   = $options['only_logged_users'] ?? false;

		if ($only_logged_users && !is_user_logged_in()) return;

		add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_filter('woocommerce_package_rates', [$this, 'maybe_hide_shipping'], 10, 2);

		/**
		 * Initialize modules.
		 * 
		 */
		new FSL_Label();
		new FSL_Bar();
		new Gift_Bar();
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
	 * Conditionally hides shipping methods when free shipping is available.
	 *
	 */
	public function maybe_hide_shipping($rates)
	{

		$options     		 = DEVNET_FSL_OPTIONS['general'] ?? [];
		$hide_shipping_rates = $options['hide_shipping_rates'] ?? false;

		if (!$hide_shipping_rates) {
			return $rates;
		}

		if ('hide_all' === $hide_shipping_rates) {

			$free = [];

			foreach ($rates as $rate_id => $rate) {
				if ('free_shipping' === $rate->method_id) {
					$free[$rate_id] = $rate;
					break;
				}
			}
			return !empty($free) ? $free : $rates;
		}

		if ('hide_except_local' === $hide_shipping_rates) {

			$new_rates = [];

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
