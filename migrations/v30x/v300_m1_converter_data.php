<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v30x;

/**
 * This migration removes old data from 3.0
 * installations of PayPal Donation MOD.
 */
class v300_m1_converter_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v31x\v313'];
	}

	/**
	 * Run migration if donation_mod_version config exists
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		return !isset($this->config['donation_mod_version']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return [
			['if', [
				['module.exists', ['acp', false, 'DONATION_OVERVIEW']],
				['module.remove', ['acp', false, 'DONATION_OVERVIEW']],
			]],
			['if', [
				['module.exists', ['acp', false, 'DONATION_CONFIG']],
				['module.remove', ['acp', false, 'DONATION_CONFIG']],
			]],
			['if', [
				['module.exists', ['acp', false, 'DONATION_DP_CONFIG']],
				['module.remove', ['acp', false, 'DONATION_DP_CONFIG']],
			]],
			['if', [
				['module.exists', ['acp', false, 'DONATION_DC_CONFIG']],
				['module.remove', ['acp', false, 'DONATION_DC_CONFIG']],
			]],
			['if', [
				['module.exists', ['acp', false, 'ACP_DONATION_MOD']],
				['module.remove', ['acp', false, 'ACP_DONATION_MOD']],
			]],

			// Custom functions
			['custom', [[$this, 'rename_ppdm_configs']]],
			['custom', [[$this, 'remove_ppdm_configs']]],
			['custom', [[$this, 'rename_ppdm_permissions']]],
		];
	}

	/**
	 * Rename config data from PayPal Donation MOD
	 */
	public function rename_ppdm_configs()
	{
		$ppdm_config_names = [
			'donation_account_id'         => 'ppde_account_id',
			'donation_default_value'      => 'ppde_default_value',
			'donation_dropbox_enable'     => 'ppde_dropbox_enable',
			'donation_dropbox_value'      => 'ppde_dropbox_value',
			'donation_goal'               => 'ppde_goal',
			'donation_goal_enable'        => 'ppde_goal_enable',
			'donation_install_date'       => 'ppde_install_date',
			'donation_raised'             => 'ppde_raised',
			'donation_raised_enable'      => 'ppde_raised_enable',
			'donation_stats_index_enable' => 'ppde_stats_index_enable',
			'donation_used'               => 'ppde_used',
			'donation_used_enable'        => 'ppde_used_enable',
			'paypal_sandbox_address'      => 'ppde_sandbox_address',
		];

		foreach ($ppdm_config_names as $old_name => $new_name)
		{
			// Retrieve the current_value of the config
			$current_value = $this->config->offsetGet($old_name);
			// Rename the config
			$sql = 'UPDATE ' . $this->table_prefix . "config
					SET config_name = '" . $new_name . "'
					WHERE config_name = '" . $old_name . "'";
			$this->db->sql_query($sql);
			// Set the new config name to the property $this->config.
			// This is necessary to prevent duplicate entry during the data_update() process
			$this->config->offsetSet($new_name, $current_value);
		}
	}

	/**
	 * Remove config data from PayPal Donation MOD
	 */
	public function remove_ppdm_configs()
	{
		$ppdm_config_names = [
			'donation_currency_enable',
			'donation_default_currency',
			'donation_enable',
			'donation_mod_version',
			'paypal_sandbox_enable',
			'paypal_sandbox_founder_enable',
		];

		// Delete all the unwanted configs
		$sql = 'DELETE FROM ' . $this->table_prefix . 'config
			WHERE ' . $this->db->sql_in_set('config_name', $ppdm_config_names);
		$this->db->sql_query($sql);
	}

	/**
	 * Rename permission from PayPal Donation MOD
	 */
	public function rename_ppdm_permissions()
	{
		$ppdm_permissions_names = [
			'a_pdm_manage' => 'a_ppde_manage',
			'u_pdm_use'    => 'u_ppde_use',
		];

		// Update all the configs kept in PPDE
		foreach ($ppdm_permissions_names as $old_name => $new_name)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "acl_options
					SET auth_option = '" . $new_name . "'
					WHERE auth_option = '" . $old_name . "'";
			$this->db->sql_query($sql);
		}
	}
}
