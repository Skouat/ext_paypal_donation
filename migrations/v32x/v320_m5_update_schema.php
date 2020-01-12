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

class v320_m5_update_schema extends \phpbb\db\migration\migration
{
	/**
	 * @inheritDoc
	 */
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v32x\v320_m4_update_schema'];
	}

	/**
	 * @inheritDoc
	 */
	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'ppde_txn_log' => [
					'txn_errors_approved' => ['BOOL', 0],
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
			'drop_columns' => [
				$this->table_prefix . 'ppde_txn_log' => ['txn_errors_approved'],
			],
		];
	}
}
