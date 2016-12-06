<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v31x;

class v310_m4_data_update extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v31x\v310_m2_data');
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'PPDE_ACP_DONATION',
				array(
					'module_basename' => '\skouat\ppde\acp\ppde_module',
					'module_langname' => 'PPDE_ACP_PAYPAL_FEATURES',
					'module_mode'     => 'paypal_features',
					'module_auth'     => 'ext_skouat/ppde && acl_a_ppde_manage',
					'after'           => 'PPDE_ACP_CURRENCY',
				)
			)),
		);
	}
}
