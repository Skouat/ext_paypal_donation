<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v31x;

class v310_m2_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v31x\v310_m1_schema');
	}

	public function update_data()
	{
		return array(
			// Global Settings
			array('config.add', array('ppde_enable', false)),
			array('config.add', array('ppde_header_link', false)),
			array('config.add', array('ppde_account_id', '')),
			array('config.add', array('ppde_default_currency', 1)),
			array('config.add', array('ppde_default_value', 0)),
			array('config.add', array('ppde_dropbox_enable', false)),
			array('config.add', array('ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100')),

			// IPN Settings
			array('config.add', array('ppde_ipn_enable', false)),
			array('config.add', array('ppde_ipn_autogroup_enable', false)),
			array('config.add', array('ppde_ipn_donorlist_enable', false)),
			array('config.add', array('ppde_ipn_group_id', 2)),
			array('config.add', array('ppde_ipn_group_as_default', false)),
			array('config.add', array('ppde_ipn_balance', 0)),
			array('config.add', array('ppde_ipn_logging', false)),
			array('config.add', array('ppde_ipn_notification_enable', false)),
			array('config.add', array('ppde_curl_detected', false)),
			array('config.add', array('ppde_curl_version', '')),
			array('config.add', array('ppde_curl_ssl_version', '')),
			array('config.add', array('ppde_fsock_detected', false)),

			// Sandbox Settings
			array('config.add', array('ppde_sandbox_enable', false)),
			array('config.add', array('ppde_sandbox_founder_enable', true)),
			array('config.add', array('ppde_sandbox_address', '')),

			// Statistics Settings
			array('config.add', array('ppde_stats_index_enable', false)),
			array('config.add', array('ppde_goal', 0)),
			array('config.add', array('ppde_goal_enable', false)),
			array('config.add', array('ppde_raised', 0)),
			array('config.add', array('ppde_raised_ipn', 0)),
			array('config.add', array('ppde_raised_enable', false)),
			array('config.add', array('ppde_used', 0)),
			array('config.add', array('ppde_used_enable', false)),

			// Overview Settings
			array('config.add', array('ppde_anonymous_donors_count', 0)),
			array('config.add', array('ppde_anonymous_donors_count_ipn', 0)),
			array('config.add', array('ppde_known_donors_count', 0)),
			array('config.add', array('ppde_known_donors_count_ipn', 0)),
			array('config.add', array('ppde_transactions_count', 0)),
			array('config.add', array('ppde_transactions_count_ipn', 0)),

			//Misc Settings
			array('config.add', array('ppde_install_date', time())),
			array('config.add', array('ppde_first_start', true)),

			// add new permissions
			array('permission.add', array('a_ppde_manage', true)),
			array('permission.add', array('u_ppde_use', true)),
			array('permission.add', array('u_ppde_view_donorlist', true)),

			//assign permissions to roles
			array('permission.permission_set', array('ROLE_ADMIN_FULL', array('a_ppde_manage'))),
			array('permission.permission_set', array('ROLE_USER_FULL', array('u_ppde_use'))),
			array('permission.permission_set', array('ROLE_USER_FULL', array('u_ppde_view_donorlist'))),

			// add new module
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'PPDE_ACP_DONATION',
				array(
					'module_enabled'  => 1,
					'module_display'  => 1,
					'module_langname' => 'PPDE_ACP_DONATION',
					'module_auth'     => 'ext_skouat/ppde && acl_a_ppde_manage',
				)
			)),

			array('module.add', array(
				'acp',
				'PPDE_ACP_DONATION',
				array(
					'module_basename' => '\skouat\ppde\acp\ppde_module',
					'modes'           => array('overview', 'settings', 'donation_pages', 'currency', 'transactions'),
				)
			)),
		);
	}
}
