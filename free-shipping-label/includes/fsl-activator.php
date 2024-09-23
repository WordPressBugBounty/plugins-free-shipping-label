<?php

namespace Devnet\FSL\Includes;

use function PHPSTORM_META\type;

class Activator
{

	/**
	 * @since    1.0.0
	 */
	public static function activate()
	{

		// $fsl_options = get_option('devnet_fsl');

		// if (!$fsl_options) {

		// 	update_option('devnet_fsl', true);

		// 	update_option('devnet_fsl_general', Defaults::general());
		// 	update_option('devnet_fsl_bar', Defaults::bar());
		// 	update_option('devnet_fsl_label', Defaults::label());

		// 	if (fsl_fs()->is__premium_only()) {
		// 		if (fsl_fs()->can_use_premium_code()) {
		// 			update_option('devnet_fsl_notice_bar', Defaults::notice_bar());
		// 		}
		// 	}
		// }
	}

	/**
	 * @since    2.0.0
	 */
	public static function check_and_format_options()
	{
		self::multilingual_check();
	}

	/**
	 * Check if there was set `multilingual` option in any section.
	 * Update `multilingual` in general settings.
	 * 
	 * @since	3.0.0
	 */
	public static function multilingual_check()
	{

		$multilingual_check = get_option('devnet_fsl_multilingual_check');

		if (!$multilingual_check && defined('DEVNET_FSL_OPTIONS')) {

			update_option('devnet_fsl_multilingual_check', true);

			foreach (DEVNET_FSL_OPTIONS as $section => $options) {

				if (isset(DEVNET_FSL_OPTIONS[$section]['multilingual']) && DEVNET_FSL_OPTIONS[$section]['multilingual']) {

					$general_options = DEVNET_FSL_OPTIONS['general'] ?? [];

					if (empty($general_options)) {
						$general_options = [];
					}

					$general_options['multilingual'] = 1;

					update_option('devnet_fsl_general', $general_options);

					break;
				}
			}
		}
	}
}
