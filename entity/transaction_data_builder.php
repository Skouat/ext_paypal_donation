<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\entity;

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
		$defaults = array_map(static function ($pair) {
			return $pair[0];
		}, $this->transaction_data_template());

		return array_merge($defaults, $overrides);
	}

	/**
	 * Maps every persisted transaction field to its [default, type] pair.
	 *
	 * Single source of truth shared by build_transaction_data() (defaults) and
	 * the operator's build_data_ary() (whitelist + casting).
	 *
	 * @return array<string, array{0: mixed, 1: string}>
	 * @access protected
	 */
	protected function transaction_data_template(): array
	{
		return [
			'business'          => ['', 'string'],
			'confirmed'         => [false, 'boolean'],
			'custom'            => ['', 'string'],
			'exchange_rate'     => ['', 'string'],
			'first_name'        => ['', 'string'],
			'item_name'         => ['', 'string'],
			'item_number'       => ['', 'string'],
			'last_name'         => ['', 'string'],
			'mc_currency'       => ['', 'string'],
			'mc_gross'          => [0.0, 'float'],
			'mc_fee'            => [0.0, 'float'],
			'net_amount'        => [0.0, 'float'],
			'parent_txn_id'     => ['', 'string'],
			'payer_email'       => ['', 'string'],
			'payer_id'          => ['', 'string'],
			'payer_status'      => ['', 'string'],
			'payment_date'      => [0, 'integer'],
			'payment_status'    => ['', 'string'],
			'payment_type'      => ['', 'string'],
			'memo'              => ['', 'string'],
			'receiver_id'       => ['', 'string'],
			'receiver_email'    => ['', 'string'],
			'residence_country' => ['', 'string'],
			'settle_amount'     => [0.0, 'float'],
			'settle_currency'   => ['', 'string'],
			'test_ipn'          => [false, 'boolean'],
			'txn_errors'        => ['', 'string'],
			'txn_id'            => ['', 'string'],
			'txn_type'          => ['', 'string'],
			'user_id'           => [ANONYMOUS, 'integer'],
		];
	}
}
