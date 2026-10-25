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

class v400_m7_webhook_sig_failed_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v40x\v400_m6_unicode_columns'];
	}

	public function effectively_installed()
	{
		return isset($this->config['ppde_webhook_sig_failed_last']);
	}

	public function update_data()
	{
		return [
			// Dynamic: rewritten at runtime to throttle logging, must not purge the config cache on each update.
			['config.add', ['ppde_webhook_sig_failed_last', 0, true]],
		];
	}
}
