<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v32x;

class v320_m4_update_schema extends \phpbb\db\migration\migration
{
	/**
	 * @inheritDoc
	 */
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v32x\v320_m2_update_schema'];
	}

	/**
	 * @inheritDoc
	 */
	public function update_schema()
	{
		return [
			'add_columns'    => [
				$this->table_prefix . 'ppde_txn_log' => [
					'memo'       => ['VCHAR:255', ''],
					'txn_errors' => ['TEXT_UNI', ''],
				],
			],
			'change_columns' => [
				$this->table_prefix . 'ppde_txn_log' => [
					'item_name'   => ['VCHAR:127', ''],
					'item_number' => ['VCHAR:127', ''],
				],
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function revert_schema()
	{
		return [
			'drop_columns'   => [
				$this->table_prefix . 'ppde_txn_log' => [
					'memo',
					'txn_errors',
				],
			],
			'change_columns' => [
				$this->table_prefix . 'ppde_txn_log' => [
					'item_name'   => ['VCHAR:128', ''],
					'item_number' => ['VCHAR:128', ''],
				],
			],
		];
	}
}
