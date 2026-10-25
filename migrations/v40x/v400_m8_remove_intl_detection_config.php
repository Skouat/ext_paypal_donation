<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v40x;

class v400_m8_remove_intl_detection_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v40x\v400_m7_webhook_sig_failed_config'];
	}

	public function effectively_installed()
	{
		return !isset($this->config['ppde_first_start'])
			&& !isset($this->config['ppde_intl_detected'])
			&& !isset($this->config['ppde_intl_version'])
			&& !isset($this->config['ppde_intl_version_valid']);
	}

	public function update_data()
	{
		return [
			['config.remove', ['ppde_first_start']],
			['config.remove', ['ppde_intl_detected']],
			['config.remove', ['ppde_intl_version']],
			['config.remove', ['ppde_intl_version_valid']],
		];
	}

	public function revert_data()
	{
		return [
			['config.update', ['ppde_first_start', true]],
			['config.update', ['ppde_intl_detected', false]],
			['config.update', ['ppde_intl_version', '']],
			['config.update', ['ppde_intl_version_valid', false]],
		];
	}
}
