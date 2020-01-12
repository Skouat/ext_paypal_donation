<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\acp;

class ppde_info
{
	function module()
	{
		return [
			'filename' => '\skouat\ppde\acp\ppde_module',
			'title'    => 'PPDE_ACP_DONATION',
			'modes'    => [
				'overview'        => [
					'title' => 'PPDE_ACP_OVERVIEW',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => ['PPDE_ACP_DONATION']],
				'settings'        => [
					'title' => 'PPDE_ACP_SETTINGS',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => ['PPDE_ACP_DONATION']],
				'paypal_features' => [
					'title' => 'PPDE_ACP_PAYPAL_FEATURES',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => ['PPDE_ACP_DONATION']],
				'donation_pages'  => [
					'title' => 'PPDE_ACP_DONATION_PAGES',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => ['PPDE_ACP_DONATION']],
				'currency'        => [
					'title' => 'PPDE_ACP_CURRENCY',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => ['PPDE_ACP_DONATION']],
				'transactions'    => [
					'title' => 'PPDE_ACP_TRANSACTIONS',
					'auth'  => 'ext_skouat/ppde && acl_a_ppde_manage',
					'cat'   => ['PPDE_ACP_DONATION']],
			],
		];
	}
}
