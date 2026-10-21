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

class v400_m6_unicode_columns extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v40x\v400_m5_widen_amounts'];
	}

	public function update_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'ppde_donation_pages' => [
					// TEXT is capped at 8000 characters on PostgreSQL and MSSQL.
					'page_content' => ['MTEXT_UNI', ''],
				],
				$this->table_prefix . 'ppde_txn_log' => [
					// Non-unicode types inherit legacy MSSQL collations and byte-based Oracle limits.
					'first_name' => ['VCHAR_UNI:64', ''],
					'last_name'  => ['VCHAR_UNI:64', ''],
					'memo'       => ['VCHAR_UNI:255', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'ppde_donation_pages' => [
					// Reverting to TEXT may truncate content longer than 8000 characters on PostgreSQL and MSSQL.
					'page_content' => ['TEXT', ''],
				],
				$this->table_prefix . 'ppde_txn_log' => [
					'first_name' => ['VCHAR:64', ''],
					'last_name'  => ['VCHAR:64', ''],
					'memo'       => ['VCHAR:255', ''],
				],
			],
		];
	}
}
