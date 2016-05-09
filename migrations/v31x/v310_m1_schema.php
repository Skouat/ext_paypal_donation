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

class v310_m1_schema extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v30x\v300_m1_converter_data');
	}

	/**
	 * Add the table schema to the database:
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'ppde_currency'       => array(
					'COLUMNS'     => array(
						'currency_id'       => array('UINT', null, 'auto_increment'),
						'currency_name'     => array('VCHAR:50', ''),
						'currency_iso_code' => array('VCHAR:10', ''),
						'currency_symbol'   => array('VCHAR:10', ''),
						'currency_on_left'  => array('BOOL', 1),
						'currency_enable'   => array('BOOL', 1),
						'currency_order'    => array('UINT', 0),
					),
					'PRIMARY_KEY' => array('currency_id'),
				),

				$this->table_prefix . 'ppde_donation_pages' => array(
					'COLUMNS'     => array(
						'page_id'                      => array('UINT', null, 'auto_increment'),
						'page_title'                   => array('VCHAR:50', ''),
						'page_lang_id'                 => array('UINT', 0),
						'page_content'                 => array('TEXT', ''),
						'page_content_bbcode_bitfield' => array('VCHAR:255', ''),
						'page_content_bbcode_uid'      => array('VCHAR:8', ''),
						'page_content_bbcode_options'  => array('UINT:4', 7),
					),

					'PRIMARY_KEY' => array('page_id'),
				),

				$this->table_prefix . 'ppde_txn_log'        => array(
					'COLUMNS'     => array(
						'transaction_id'    => array('UINT', null, 'auto_increment'),
						// Receiver information
						'receiver_id'       => array('VCHAR:13', ''),
						'receiver_email'    => array('VCHAR:127', ''),
						'residence_country' => array('VCHAR:2', ''),
						'business'          => array('VCHAR:127', ''),
						// Transaction information
						'confirmed'         => array('BOOL', 0),
						'test_ipn'          => array('BOOL', 0),
						'txn_id'            => array('VCHAR:32', ''),
						'txn_type'          => array('VCHAR:32', ''),
						'parent_txn_id'     => array('VCHAR:19', ''),
						// Buyer information
						'payer_email'       => array('VCHAR:127', ''),
						'payer_id'          => array('VCHAR:13', ''),
						'payer_status'      => array('VCHAR:16', ''),
						'first_name'        => array('VCHAR:64', ''),
						'last_name'         => array('VCHAR:64', ''),
						'user_id'           => array('UINT', 0),
						// Payment information
						'custom'            => array('VCHAR:255', ''),
						'item_name'         => array('VCHAR:128', ''),
						'item_number'       => array('VCHAR:128', ''),
						'mc_currency'       => array('VCHAR:8', ''),
						'mc_fee'            => array('DECIMAL:8', 0),
						'mc_gross'          => array('DECIMAL:8', 0),
						'payment_date'      => array('TIMESTAMP', 0),
						'payment_status'    => array('VCHAR:18', ''),
						'payment_type'      => array('VCHAR:10', ''),
						'settle_amount'     => array('DECIMAL:8', 0),
						'settle_currency'   => array('VCHAR:8', ''),
						'net_amount'        => array('DECIMAL:8', 0),
						'exchange_rate'     => array('VCHAR:16', ''),
					),

					'PRIMARY_KEY' => array('transaction_id'),
					'KEYS'        => array(
						'user_id' => array('INDEX', 'user_id'),
						'txn_id'  => array('INDEX', 'txn_id'),
					),
				),
			),
		);
	}

	/**
	 * Drop the PayPal Donation tables schema from the database
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'ppde_currency',
				$this->table_prefix . 'ppde_donation_pages',
				$this->table_prefix . 'ppde_txn_log',
			),
		);
	}
}
