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

class v310_m1_schema extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v30x\v300_m1_converter_data'];
	}

	/**
	 * Add the table schema to the database:
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'ppde_currency' => [
					'COLUMNS'     => [
						'currency_id'       => ['UINT', null, 'auto_increment'],
						'currency_name'     => ['VCHAR:50', ''],
						'currency_iso_code' => ['VCHAR:10', ''],
						'currency_symbol'   => ['VCHAR:10', ''],
						'currency_on_left'  => ['BOOL', 1],
						'currency_enable'   => ['BOOL', 1],
						'currency_order'    => ['UINT', 0],
					],
					'PRIMARY_KEY' => ['currency_id'],
				],

				$this->table_prefix . 'ppde_donation_pages' => [
					'COLUMNS' => [
						'page_id'                      => ['UINT', null, 'auto_increment'],
						'page_title'                   => ['VCHAR:50', ''],
						'page_lang_id'                 => ['UINT', 0],
						'page_content'                 => ['TEXT', ''],
						'page_content_bbcode_bitfield' => ['VCHAR:255', ''],
						'page_content_bbcode_uid'      => ['VCHAR:8', ''],
						'page_content_bbcode_options'  => ['UINT:4', 7],
					],

					'PRIMARY_KEY' => ['page_id'],
				],

				$this->table_prefix . 'ppde_txn_log' => [
					'COLUMNS' => [
						'transaction_id'    => ['UINT', null, 'auto_increment'],
						// Receiver information
						'receiver_id'       => ['VCHAR:13', ''],
						'receiver_email'    => ['VCHAR:127', ''],
						'residence_country' => ['VCHAR:2', ''],
						'business'          => ['VCHAR:127', ''],
						// Transaction information
						'confirmed'         => ['BOOL', 0],
						'test_ipn'          => ['BOOL', 0],
						'txn_id'            => ['VCHAR:32', ''],
						'txn_type'          => ['VCHAR:32', ''],
						'parent_txn_id'     => ['VCHAR:19', ''],
						// Buyer information
						'payer_email'       => ['VCHAR:127', ''],
						'payer_id'          => ['VCHAR:13', ''],
						'payer_status'      => ['VCHAR:16', ''],
						'first_name'        => ['VCHAR:64', ''],
						'last_name'         => ['VCHAR:64', ''],
						'user_id'           => ['UINT', 0],
						// Payment information
						'custom'            => ['VCHAR:255', ''],
						'item_name'         => ['VCHAR:128', ''],
						'item_number'       => ['VCHAR:128', ''],
						'mc_currency'       => ['VCHAR:8', ''],
						'mc_fee'            => ['DECIMAL:8', 0],
						'mc_gross'          => ['DECIMAL:8', 0],
						'payment_date'      => ['TIMESTAMP', 0],
						'payment_status'    => ['VCHAR:18', ''],
						'payment_type'      => ['VCHAR:10', ''],
						'settle_amount'     => ['DECIMAL:8', 0],
						'settle_currency'   => ['VCHAR:8', ''],
						'net_amount'        => ['DECIMAL:8', 0],
						'exchange_rate'     => ['VCHAR:16', ''],
					],

					'PRIMARY_KEY' => ['transaction_id'],
					'KEYS'        => [
						'user_id' => ['INDEX', 'user_id'],
						'txn_id'  => ['INDEX', 'txn_id'],
					],
				],
			],
		];
	}

	/**
	 * Drop the PayPal Donation tables schema from the database
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'ppde_currency',
				$this->table_prefix . 'ppde_donation_pages',
				$this->table_prefix . 'ppde_txn_log',
			],
		];
	}
}
