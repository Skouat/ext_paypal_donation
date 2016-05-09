<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v30x;

/**
 * This migration removes old schema from 3.0
 * installations of PayPal Donation MOD.
 */
class v300_m2_converter_schema extends \phpbb\db\migration\migration
{
	/**
	 * Run migration if donation_mod_version config exists
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'donation_item');
	}

	public static function depends_on()
	{
		return array('\skouat\ppde\migrations\v30x\v300_m1_converter_data');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'donation_item',
			),
		);
	}
}
