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

class v320_m2_update_schema extends \phpbb\db\migration\migration
{
	/**
	 * @inheritDoc
	 */
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v32x\v320_m1_reparse'];
	}

	/**
	 * @inheritDoc
	 */
	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_ppde_donated_amount' => ['DECIMAL:8', 0],
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
				$this->table_prefix . 'users' => ['user_ppde_donated_amount'],
			],
		];
	}
}
