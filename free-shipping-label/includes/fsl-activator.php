<?php

namespace Devnet\FSL\Includes;


if (! defined('ABSPATH')) {
	exit;
}


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
		//self::multilingual_check();
		self::migrate_layout_option();
		self::migrate_pb_and_gift_bar_options();
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

	/**
	 * Check if there was set `layout` option in GiftBar or inFSL Discount.
	 * Update `layout` in Progress Bar settings.
	 * 
	 * @since	3.4.0
	 */
	public static function migrate_layout_option()
	{
		$layout_check = get_option('devnet_fsl_layout_check');

		if ($layout_check || !defined('DEVNET_FSL_OPTIONS')) {
			return;
		}

		$gift_bar_options = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];
		$options_with_layout = ['gift_bar' => $gift_bar_options];

		if (defined('DEVNET_FSL_DISCOUNT_OPTIONS')) {
			$options_with_layout = array_merge($options_with_layout, DEVNET_FSL_DISCOUNT_OPTIONS);
		}

		$layout = self::get_layout_with_highest_enabled_threshold($options_with_layout);

		if (!$layout) {
			return;
		}

		$progress_bar_options = DEVNET_FSL_OPTIONS['progress_bar'] ?? [];

		if (empty($progress_bar_options)) {
			return;
		}

		$progress_bar_options['layout'] = $layout;

		update_option('devnet_fsl_bar', $progress_bar_options);
		update_option('devnet_fsl_layout_check', true);
	}


	private static function get_layout_with_highest_enabled_threshold(array $options_with_layout): ?string
	{
		$enabled = [];
		$all = [];

		foreach ($options_with_layout as $key => $group) {
			if (!isset($group['threshold'], $group['layout'])) {
				continue;
			}

			$entry = [
				'key'       => $key,
				'threshold' => (float) $group['threshold'],
				'layout'    => $group['layout'],
			];

			$all[] = $entry;

			if (!empty($group['enable_bar'])) {
				$enabled[] = $entry;
			}
		}

		$target = !empty($enabled) ? $enabled : $all;

		if (empty($target)) {
			return null;
		}

		usort($target, fn($a, $b) => $b['threshold'] <=> $a['threshold']);

		return $target[0]['layout'];
	}

	/**
	 * 
	 * @since	3.5.0
	 */
	public static function migrate_disabled_position_options()
	{

		$progress_bar_options = DEVNET_FSL_OPTIONS['progress_bar'] ?? [];

		// Fresh install
		if (empty($progress_bar_options)) {
			return;
		}

		$show_on_cart     = $progress_bar_options['show_on_cart'] ?? null;
		$show_on_checkout = $progress_bar_options['show_on_checkout'] ?? null;
		$show_on_minicart = $progress_bar_options['show_on_minicart'] ?? null;

		if (empty($show_on_cart)) {
			$progress_bar_options['cart_position'] = '';
		}

		if (empty($show_on_checkout)) {
			$progress_bar_options['checkout_position'] = '';
		}

		if (empty($show_on_minicart)) {
			$progress_bar_options['minicart_position'] = '';
		}

		unset($progress_bar_options['show_on_cart']);
		unset($progress_bar_options['show_on_checkout']);
		unset($progress_bar_options['show_on_minicart']);

		update_option('devnet_fsl_bar', $progress_bar_options);
	}

	/**
	 * 
	 * @since	3.5.0
	 */
	public static function migrate_gift_bar_styles()
	{
		$progress_bar_options = DEVNET_FSL_OPTIONS['progress_bar'] ?? [];
		$gift_bar_options     = DEVNET_FSL_OPTIONS['gift_bar'] ?? [];

		// Fresh install or not set
		if (empty($gift_bar_options)) {
			return;
		}

		$inherit_progress_bar_settings = $gift_bar_options['inherit_progress_bar_settings'] ?? true;

		// Skip if settings are inherited
		if ($inherit_progress_bar_settings) {
			return;
		}

		$inheritable = [
			'hide_border_shadow',
			'disable_animation',
			'disabled_animations',
			'bar_border_color',
			'bar_bg_color',
			'bar_inner_color',
			'bar_height',
			'bar_type',
			// 'indicator_icon',
			// 'indicator_icon_size',
			// 'indicator_icon_shape',
			// 'indicator_icon_bg_color',
			'circle_size',
			'inside_circle',
			// 'icon',
			// 'icon_color',
			'circle_bg_color',
			'text_color',
			'box_bg_color',
			'box_max_width',
			'box_alignment',
			'center_text',
			'bar_radius',
			'remove_bar_stripes',
		];

		foreach ($inheritable as $opt) {
			// Save current
			$_pb_option = $progress_bar_options[$opt];

			$progress_bar_options[$opt] = $gift_bar_options[$opt] ?? $_pb_option;

			unset($gift_bar_options[$opt]);
		}

		update_option('devnet_fsl_gift_bar', $gift_bar_options);

		$gift_bar_display = $gift_bar_options['display'] ?? '';
		$gift_bar_enabled = $gift_bar_options['enable_bar'] ?? false;

		if (
			$gift_bar_enabled &&
			in_array($gift_bar_display, ['extend', 'only'], true)
		) {
			update_option('devnet_fsl_bar', $progress_bar_options);
		}
	}

	/**
	 * Migrate disabled positions inside position select.
	 * Migrate GiftBar styles back into progress bar styles (only pro users with enabled gift bar and custom styles).
	 * 
	 * @since	3.5.0
	 */
	public static function migrate_pb_and_gift_bar_options()
	{
		$migration_350 = get_option('devnet_fsl_migration_350');

		if ($migration_350 || !defined('DEVNET_FSL_OPTIONS')) {
			return;
		}

		self::migrate_disabled_position_options();
		self::migrate_gift_bar_styles();

		update_option('devnet_fsl_migration_350', true);
	}
}
