<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v33x;

class v330_m1_update_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v32x\v320_m6_update_data'];
	}

	public function update_data()
	{
		return [
			// PHP intl Settings
			['config.add', ['ppde_default_locale', '']],
			['config.add', ['ppde_intl_detected', false]],
			['config.add', ['ppde_intl_version', '']],
			['config.add', ['ppde_intl_version_valid', false]],
			['config.add', ['ppde_stats_text_only', false]],
			['config.update', ['ppde_first_start', true]],
		];
	}
}
