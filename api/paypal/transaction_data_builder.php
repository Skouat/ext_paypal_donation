<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */


namespace skouat\ppde\api\paypal;

trait transaction_data_builder
{
	/**
	 * Assemble a complete PPDE transaction data array from a set of overrides,
	 * filling every column with a safe default so no key is ever missing when
	 * the array reaches the operator's build_data_ary().
	 *
	 * @param array $overrides Only the fields the caller actually knows about.
	 *
	 * @return array
	 * @access protected
	 */
	protected function build_transaction_data(array $overrides): array
	{
		return array_merge([
			'business'          => '',
			'confirmed'         => false,
			'custom'            => '',
			'exchange_rate'     => '',
			'first_name'        => '',
			'item_name'         => '',
			'item_number'       => '',
			'last_name'         => '',
			'mc_currency'       => '',
			'mc_gross'          => 0.0,
			'mc_fee'            => 0.0,
			'net_amount'        => 0.0,
			'parent_txn_id'     => '',
			'payer_email'       => '',
			'payer_id'          => '',
			'payer_status'      => '',
			'payment_date'      => 0,
			'payment_status'    => '',
			'payment_type'      => '',
			'memo'              => '',
			'receiver_id'       => '',
			'receiver_email'    => '',
			'residence_country' => '',
			'settle_amount'     => 0.0,
			'settle_currency'   => '',
			'test_ipn'          => false,
			'txn_errors'        => '',
			'txn_id'            => '',
			'txn_type'          => '',
			'user_id'           => ANONYMOUS,
		], $overrides);
	}
}
