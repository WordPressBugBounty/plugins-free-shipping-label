<?php

namespace Devnet\FSL\Includes;


class Deactivator
{

	/**
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		$general_options = get_option('devnet_fsl_general');
		$delete_options  = $general_options['delete_options'] ?? false;

		if ($delete_options) {
			delete_option('devnet_fsl');
			delete_option('devnet_fsl_general');
			delete_option('devnet_fsl_bar');
			delete_option('devnet_fsl_gift_bar');
			delete_option('devnet_fsl_notice_bar');
			delete_option('devnet_fsl_label');
			delete_option('devnet_fsl_multilingual_check');
		}
	}
}
