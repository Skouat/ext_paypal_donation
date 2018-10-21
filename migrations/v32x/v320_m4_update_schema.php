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

class v320_m4_update_schema extends \phpbb\db\migration\migration
{
	/**
	 * @inheritDoc
	 */
	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v32x\v320_m2_update_schema');
	}

	/**
	 * @inheritDoc
	 */
	public function update_schema()
	{
		return array(
			'add_columns'    => array(
				$this->table_prefix . 'ppde_txn_log' => array(
					'memo' => array('TEXT_UNI', ''),
				),
			),
			'change_columns' => array(
				$this->table_prefix . 'ppde_txn_log' => array(
					'item_name'   => array('VCHAR:127', ''),
					'item_number' => array('VCHAR:127', ''),
				),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function revert_schema()
	{
		return array(
			'drop_columns'   => array(
				$this->table_prefix . 'ppde_txn_log' => array('memo'),
			),
			'change_columns' => array(
				$this->table_prefix . 'ppde_txn_log' => array(
					'item_name'   => array('VCHAR:128', ''),
					'item_number' => array('VCHAR:128', ''),
				),
			),
		);
	}
}
