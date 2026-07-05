<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v40x;

class v400_m5_widen_amounts extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v40x\v400_m4_txn_id_unique_index'];
	}

	public function update_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'ppde_txn_log' => [
					// PayPal exchange rate: up to 15 decimals ("0." + 15 = 17) overflowed VARCHAR(16).
					'exchange_rate' => ['VCHAR:32', ''],
					// DECIMAL:15 = decimal(15,2): headroom for large / zero-decimal amounts (JPY).
					'mc_fee'        => ['DECIMAL:15', 0],
					'mc_gross'      => ['DECIMAL:15', 0],
					'net_amount'    => ['DECIMAL:15', 0],
					'settle_amount' => ['DECIMAL:15', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'ppde_txn_log' => [
					'exchange_rate' => ['VCHAR:16', ''],
					'mc_fee'        => ['DECIMAL:8', 0],
					'mc_gross'      => ['DECIMAL:8', 0],
					'net_amount'    => ['DECIMAL:8', 0],
					'settle_amount' => ['DECIMAL:8', 0],
				],
			],
		];
	}
}
