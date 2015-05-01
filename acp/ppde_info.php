<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\acp;

class ppde_info
{
	function module()
	{
		return array(
			'filename'	=> '\skouat\ppde\acp\ppde_module',
			'title'		=> 'ACP_DONATION_MOD',
			'modes'		=> array(
				'overview'	=> array(
					'title'	=> 'PPDE_OVERVIEW',
					'auth'	=> 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
				'settings' => array(
					'title'	=> 'PPDE_SETTINGS',
					'auth'	=> 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
				'donation_pages'	=> array(
					'title'	=> 'PPDE_DP_CONFIG',
					'auth'	=> 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
				'currency'			=> array(
					'title'	=> 'PPDE_DC_CONFIG',
					'auth'	=> 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
			),
		);
	}
}
