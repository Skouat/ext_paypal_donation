<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\migrations\v10x;

class v1_0_0_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v10x\v1_0_0_schema');
	}

	public function update_data()
	{
		return array(
			// Global Settings
			array('config.add', array('ppde_enable', false)),
			array('config.add', array('ppde_account_id', '')),
			array('config.add', array('ppde_default_currency', 1)),
			array('config.add', array('ppde_default_value', 0)),
			array('config.add', array('ppde_dropbox_enable', false)),
			array('config.add', array('ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100')),

			// Sandbox Settings
			array('config.add', array('ppde_sandbox_enable', false)),
			array('config.add', array('ppde_sandbox_founder_enable', true)),
			array('config.add', array('ppde_sandbox_address', '')),

			// Statistics Settings
			array('config.add', array('ppde_stats_index_enable', false)),
			array('config.add', array('ppde_goal', 0)),
			array('config.add', array('ppde_goal_enable', false)),
			array('config.add', array('ppde_raised', 0)),
			array('config.add', array('ppde_raised_enable', false)),
			array('config.add', array('ppde_used', 0)),
			array('config.add', array('ppde_used_enable', false)),

			//Misc Settings
			array('config.add', array('ppde_install_date', time())),

			array('permission.add', array('u_ppde_use', true)),
			array('permission.add', array('a_ppde_manage', true)),

			array('permission.permission_set',
				array('ROLE_USER_FULL',
					array('u_ppde_use')
				)
			),

			array('permission.permission_set',
				array('ROLE_ADMIN_FULL',
					array('a_ppde_manage')
				)
			),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_DONATION_MOD',
				array(
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'module_langname'	=> 'PPDE_ACP_DONATION_MOD',
					'module_auth'		=> 'ext_skouat/ppde && acl_a_ppde_manage',
				)
			)),

			array('module.add', array(
				'acp',
				'ACP_DONATION_MOD',
				array(
					'module_basename'	=> '\skouat\ppde\acp\ppde_module',
					'modes'				=> array('overview', 'settings', 'donation_pages', 'currency'),
				)
			)),

			array('custom', array(array(&$this, 'add_ppde_item_data'))),

			array('config.add', array('ppde_version', '1.0.0-dev')),
		);
	}

	/**
	* Add initial data to the database
	*
	* @return null
	* @access public
	*/
	public function add_ppde_item_data()
	{
		// Define data
		$item_data = array(
			array(
				'item_type'			=> 'donation_pages',
				'item_name'			=> 'donation_body',
				'item_iso_code'		=> 1,
				'item_symbol'		=> '',
				'item_text'			=> '',
				'item_enable'		=> true,
				'item_left_id'		=> 0,
				'item_right_id'		=> 0,
			),
			array(
				'item_type'			=> 'donation_pages',
				'item_name'			=> 'donation_success',
				'item_iso_code'		=> 1,
				'item_symbol'		=> '',
				'item_text'			=> '',
				'item_enable'		=> true,
				'item_left_id'		=> 0,
				'item_right_id'		=> 0,
			),
			array(
				'item_type'			=> 'donation_pages',
				'item_name'			=> 'donation_cancel',
				'item_iso_code'		=> 1,
				'item_symbol'		=> '',
				'item_text'			=> '',
				'item_enable'		=> true,
				'item_left_id'		=> 0,
				'item_right_id'		=> 0,
			),
		);

		// Insert data
		$this->db->sql_multi_insert($this->table_prefix . 'ppde_item', $item_data);
	}
}
