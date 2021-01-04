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

class v330_m3_update_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v33x\v330_m2_update_data'];
	}

	public function update_data()
	{
		return [
			['config.add', ['ppde_time_expiration', 0]],
			['module.add', [
				'acp',
				'PPDE_ACP_DONATION',
				[
					'module_basename' => '\skouat\ppde\acp\ppde_module',
					'modes'           => ['donors'],
				],
			]],
		];
	}
}
