<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Skouat
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
					'title'	=> 'DONATION_OVERVIEW',
					'auth'	=> 'acl_a_pdm_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
				'configuration' => array(
					'title'	=> 'DONATION_CONFIG',
					'auth'	=> 'acl_a_pdm_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
				'donation_pages'	=> array(
					'title'	=> 'DONATION_DP_CONFIG',
					'auth'	=> 'acl_a_pdm_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
				'currency'			=> array(
					'title'	=> 'DONATION_DC_CONFIG',
					'auth'	=> 'acl_a_pdm_manage',
					'cat'	=> array('ACP_DONATION_MOD')),
			),
		);
	}
}
