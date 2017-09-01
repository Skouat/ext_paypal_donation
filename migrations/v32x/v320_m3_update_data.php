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
			array('config.add', array('ppde_ipn_min_before_group', 0)),
			array('config.add', array('ppde_tls_detected', false)),
			array('config.remove', array('ppde_fsock_detected')),
			array('config.update', array('ppde_first_start', true)),

			// Donors module
			array('module.add', array(
				'acp',
				'PPDE_ACP_DONATION',
				array(
					'module_basename' => '\skouat\ppde\acp\ppde_module',
					'module_langname' => 'PPDE_ACP_DONORS',
					'module_mode'     => 'donors',
					'module_auth'     => 'ext_skouat/ppde && acl_a_ppde_manage',
					'after'           => 'PPDE_ACP_CURRENCY',
				),
			)),
		);
	}
}
