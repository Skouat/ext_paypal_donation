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

class v320_m7_update_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v32x\v320_m6_update_data');
	}

	public function update_data()
	{
		return array(
			// INTL Settings
			array('config.add', array('ppde_default_locale', '')),
			array('config.add', array('ppde_intl_detected', false)),
			array('config.add', array('ppde_intl_version', '')),
			array('config.add', array('ppde_intl_version_valid', false)),
			array('config.update', array('ppde_first_start', true)),
		);
	}
}
