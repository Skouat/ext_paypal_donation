<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v31x;

class v310_m2_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v31x\v310_m1_schema'];
	}

	public function update_data()
	{
		return [
			// Global Settings
			['config.add', ['ppde_enable', false]],
			['config.add', ['ppde_header_link', false]],
			['config.add', ['ppde_account_id', '']],
			['config.add', ['ppde_default_currency', 1]],
			['config.add', ['ppde_default_value', 0]],
			['config.add', ['ppde_dropbox_enable', false]],
			['config.add', ['ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100']],

			// IPN Settings
			['config.add', ['ppde_ipn_enable', false]],
			['config.add', ['ppde_ipn_autogroup_enable', false]],
			['config.add', ['ppde_ipn_donorlist_enable', false]],
			['config.add', ['ppde_ipn_group_id', 2]],
			['config.add', ['ppde_ipn_group_as_default', false]],
			['config.add', ['ppde_ipn_balance', 0]],
			['config.add', ['ppde_ipn_logging', false]],
			['config.add', ['ppde_ipn_notification_enable', false]],
			['config.add', ['ppde_curl_detected', false]],
			['config.add', ['ppde_curl_version', '']],
			['config.add', ['ppde_curl_ssl_version', '']],
			['config.add', ['ppde_fsock_detected', false]],

			// Sandbox Settings
			['config.add', ['ppde_sandbox_enable', false]],
			['config.add', ['ppde_sandbox_founder_enable', true]],
			['config.add', ['ppde_sandbox_address', '']],

			// Statistics Settings
			['config.add', ['ppde_stats_index_enable', false]],
			['config.add', ['ppde_goal', 0]],
			['config.add', ['ppde_goal_enable', false]],
			['config.add', ['ppde_raised', 0]],
			['config.add', ['ppde_raised_ipn', 0]],
			['config.add', ['ppde_raised_enable', false]],
			['config.add', ['ppde_used', 0]],
			['config.add', ['ppde_used_enable', false]],

			// Overview Settings
			['config.add', ['ppde_anonymous_donors_count', 0]],
			['config.add', ['ppde_anonymous_donors_count_ipn', 0]],
			['config.add', ['ppde_known_donors_count', 0]],
			['config.add', ['ppde_known_donors_count_ipn', 0]],
			['config.add', ['ppde_transactions_count', 0]],
			['config.add', ['ppde_transactions_count_ipn', 0]],

			//Misc Settings
			['config.add', ['ppde_install_date', time()]],
			['config.add', ['ppde_first_start', true]],

			// Add new permissions
			['permission.add', ['a_ppde_manage', true]],
			['permission.add', ['u_ppde_use', true]],
			['permission.add', ['u_ppde_view_donorlist', true]],

			// Assign permissions to roles
			['permission.permission_set', ['ROLE_ADMIN_FULL', ['a_ppde_manage']]],
			['permission.permission_set', ['ROLE_USER_FULL', ['u_ppde_use']]],
			['permission.permission_set', ['ROLE_USER_FULL', ['u_ppde_view_donorlist']]],

			// Add new module
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'PPDE_ACP_DONATION',
				[
					'module_enabled'  => 1,
					'module_display'  => 1,
					'module_langname' => 'PPDE_ACP_DONATION',
					'module_auth'     => 'ext_skouat/ppde && acl_a_ppde_manage',
				],
			]],

			['module.add', [
				'acp',
				'PPDE_ACP_DONATION',
				[
					'module_basename' => '\skouat\ppde\acp\ppde_module',
					'modes'           => ['overview', 'settings', 'donation_pages', 'currency', 'transactions'],
				],
			]],
		];
	}
}
