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

class v310_m3_currency_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v31x\v310_m2_data'];
	}

	public function update_data()
	{
		return [
			// Run custom actions
			['custom', [[&$this, 'add_ppde_currency_data']]],
		];
	}

	/**
	 * Add initial currency data to the database
	 *
	 * @return void
	 * @access public
	 */
	public function add_ppde_currency_data()
	{
		// Define data
		$currency_data = [
			[
				'currency_name'     => 'U.S. Dollar',
				'currency_iso_code' => 'USD',
				'currency_symbol'   => '&dollar;',
				'currency_enable'   => true,
				'currency_on_left'  => true,
				'currency_order'    => 1,
			],
			[
				'currency_name'     => 'Euro',
				'currency_iso_code' => 'EUR',
				'currency_symbol'   => '&euro;',
				'currency_enable'   => true,
				'currency_on_left'  => false,
				'currency_order'    => 2,
			],
			[
				'currency_name'     => 'Australian Dollar',
				'currency_iso_code' => 'AUD',
				'currency_symbol'   => '&dollar;',
				'currency_enable'   => true,
				'currency_on_left'  => true,
				'currency_order'    => 3,
			],
			[
				'currency_name'     => 'Canadian Dollar',
				'currency_iso_code' => 'CAD',
				'currency_symbol'   => '&dollar;',
				'currency_enable'   => true,
				'currency_on_left'  => true,
				'currency_order'    => 4,
			],
			[
				'currency_name'     => 'Hong Kong Dollar',
				'currency_iso_code' => 'HKD',
				'currency_symbol'   => '&dollar;',
				'currency_enable'   => true,
				'currency_on_left'  => true,
				'currency_order'    => 5,
			],
			[
				'currency_name'     => 'Pound Sterling',
				'currency_iso_code' => 'GBP',
				'currency_symbol'   => '&pound;',
				'currency_enable'   => true,
				'currency_on_left'  => true,
				'currency_order'    => 6,
			],
			[
				'currency_name'     => 'Yen',
				'currency_iso_code' => 'JPY',
				'currency_symbol'   => '&yen;',
				'currency_enable'   => true,
				'currency_on_left'  => false,
				'currency_order'    => 7,
			],
		];

		// Insert data
		$this->db->sql_multi_insert($this->table_prefix . 'ppde_currency', $currency_data);
	}
}
