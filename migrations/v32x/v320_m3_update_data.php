<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v32x;

class v320_m3_update_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v32x\v320_m2_update_schema'];
	}

	public function update_data()
	{
		return [
			// IPN Settings
			['config.add', ['ppde_ipn_min_before_group', 0]],
			['config.add', ['ppde_tls_detected', false]],
			['config.remove', ['ppde_fsock_detected']],
			['config.update', ['ppde_first_start', true]],
		];
	}
}
