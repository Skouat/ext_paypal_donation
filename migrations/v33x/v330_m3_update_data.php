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
		return ['\skouat\ppde\migrations\v32x\v330_m2_update_data'];
	}

	public function update_data()
	{
		return [
			['config.update', ['ppde_tls_detected', false]],
			['config.update', ['ppde_first_start', true]],
		];
	}
}
