<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v32x;

class v320_m2_update_schema extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * @inheritDoc
	 */
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v31x\v320_m1_reparse');
	}

	/**
	 * @inheritDoc
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_ppde_donated_amount' => array('DECIMAL:8', 0),
				),
			),
		);
	}
}
