<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v32x;

class v320_m3_update_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v32x\v320_m2_update_schema');
	}

	public function update_data()
	{
		return array(
			// IPN Settings
			array('config.add', array('ppde_https_detected', 0)),
			array('config.add', array('ppde_ipn_min_before_group', 0)),
			array('config.add', array('ppde_tls_detected', 0)),
			array('config.remove', array('ppde_fsock_detected', 0)),
		);
	}
}
