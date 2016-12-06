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
			'filename' => '\skouat\ppde\acp\ppde_module',
			'title'    => 'PPDE_ACP_DONATION',
			'modes'    => array(
				'overview'        => array(
					'title' => 'PPDE_ACP_OVERVIEW',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => array('PPDE_ACP_DONATION')),
				'settings'        => array(
					'title' => 'PPDE_ACP_SETTINGS',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => array('PPDE_ACP_DONATION')),
				'paypal_features' => array(
					'title' => 'PPDE_ACP_PAYPAL_FEATURES',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => array('PPDE_ACP_DONATION')),
				'donation_pages'  => array(
					'title' => 'PPDE_ACP_DONATION_PAGES',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => array('PPDE_ACP_DONATION')),
				'currency'        => array(
					'title' => 'PPDE_ACP_CURRENCY',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => array('PPDE_ACP_DONATION')),
				'transactions'    => array(
					'title' => 'PPDE_ACP_TRANSACTIONS',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => array('PPDE_ACP_DONATION')),
			),
		);
	}
}
