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

/**
 * Makes txn_id UNIQUE so a PayPal transaction is recorded only once, even if
 * the webhook and the capture endpoint race to insert it.
 */
class v400_m4_txn_id_unique_index extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v40x\v400_m3_clean_duplicate_txn'];
	}

	public function effectively_installed()
	{
		return $this->db_tools->sql_unique_index_exists($this->table_prefix . 'ppde_txn_log', 'txn_uniq');
	}

	public function update_schema()
	{
		return [
			'drop_keys'        => [
				$this->table_prefix . 'ppde_txn_log' => ['txn_id'],
			],
			'add_unique_index' => [
				$this->table_prefix . 'ppde_txn_log' => [
					'txn_uniq' => ['txn_id'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'ppde_txn_log' => ['txn_uniq'],
			],
			'add_index' => [
				$this->table_prefix . 'ppde_txn_log' => [
					'txn_id' => ['txn_id'],
				],
			],
		];
	}
}
