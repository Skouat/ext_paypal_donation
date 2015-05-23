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
					'modes'           => array('overview', 'settings', 'donation_pages', 'currency'),
				)
			)),

			array('custom', array(array(&$this, 'add_ppde_donation_pages_data'))),
			array('custom', array(array(&$this, 'add_ppde_currency_data'))),

			array('config.add', array('ppde_version', '1.0.0-dev')),
		);
	}

	/**
	 * Add initial data to the database
	 *
	 * @return null
	 * @access public
	 */
	public function add_ppde_donation_pages_data()
	{
		// Define data
		$dp_data = array(
			array(
				'page_title'   => 'donation_body',
				'page_lang_id' => 1,
				'page_content' => '',
			),
			array(
				'page_title'   => 'donation_success',
				'page_lang_id' => 1,
				'page_content' => '',
			),
			array(
				'page_title'   => 'donation_cancel',
				'page_lang_id' => 1,
				'page_content' => '',
			),
		);

		// Insert data
		$this->db->sql_multi_insert($this->table_prefix . 'ppde_donation_pages', $dp_data);
	}

	/**
	 * Add initial currency data to the database
	 *
	 * @return null
	 * @access public
	 */
	public function add_ppde_currency_data()
	{
		// Define data
		$currency_data = array(
			array(
				'currency_name'     => 'U.S. Dollar',
				'currency_iso_code' => 'USD',
				'currency_symbol'   => '&#36;', // symbol dollar
				'currency_enable'   => true,
				'currency_order'    => 1,
			),
			array(
				'currency_name'     => 'Euro',
				'currency_iso_code' => 'EUR',
				'currency_symbol'   => '&#8364;', // symbol euro
				'currency_enable'   => true,
				'currency_order'    => 2,
			),
			array(
				'currency_name'     => 'Australian Dollar',
				'currency_iso_code' => 'AUD',
				'currency_symbol'   => '&#36;', // symbol $
				'currency_enable'   => true,
				'currency_order'    => 3,
			),
			array(
				'currency_name'     => 'Canadian Dollar',
				'currency_iso_code' => 'CAD',
				'currency_symbol'   => '&#36;', // symbol $
				'currency_enable'   => true,
				'currency_order'    => 4,
			),
			array(
				'currency_name'     => 'Hong Kong Dollar',
				'currency_iso_code' => 'HKD',
				'currency_symbol'   => '&#36;', // symbol $
				'currency_enable'   => true,
				'currency_order'    => 5,
			),
			array(
				'currency_name'     => 'Pound Sterling',
				'currency_iso_code' => 'GBP',
				'currency_symbol'   => '&#163;', // symbol livre sterling
				'currency_enable'   => true,
				'currency_order'    => 6,
			),
			array(
				'currency_name'     => 'Yen',
				'currency_iso_code' => 'JPY',
				'currency_symbol'   => '&#165;', // symbol yen
				'currency_enable'   => true,
				'currency_order'    => 7,
			),
		);

		// Insert data
		$this->db->sql_multi_insert($this->table_prefix . 'ppde_currency', $currency_data);
	}
}
