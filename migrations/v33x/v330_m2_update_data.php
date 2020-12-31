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

class v330_m2_update_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v33x\v330_m1_update_data'];
	}

	public function update_data()
	{
		return [
			['config.add', ['ppde_stats_position', 'bottom']],
			['config.add', ['ppde_allow_guest', false]],
			['config.add', ['ppde_ipn_dl_allow_guest', false]],
		];
	}
}
