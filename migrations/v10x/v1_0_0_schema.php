<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\migrations\v10x;

class v1_0_0_schema extends \phpbb\db\migration\migration
{
	/**
	* Add the table schema to the database:
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'ppde_currency' => array(
					'COLUMNS' => array(
						'currency_id'		=> array('UINT', null, 'auto_increment'),
						'currency_name'		=> array('VCHAR:50', ''),
						'currency_iso_code'	=> array('VCHAR:10', ''),
						'currency_symbol'	=> array('VCHAR:10', ''),
						'currency_enable'	=> array('BOOL', 1),
						'currency_left_id'	=> array('UINT', 0),
						'currency_right_id'	=> array('UINT', 0),
					),
					'PRIMARY_KEY' => array('currency_id'),
				),

				$this->table_prefix . 'ppde_donation_pages' => array(
					'COLUMNS' => array(
						'page_id'						=> array('UINT', null, 'auto_increment'),
						'page_title'					=> array('VCHAR:50', ''),
						'page_lang_id'					=> array('UINT', 0),
						'page_content'					=> array('TEXT', ''),
						'page_content_bbcode_bitfield'	=> array('VCHAR:255', ''),
						'page_content_bbcode_uid'		=> array('VCHAR:8', ''),
						'page_content_bbcode_options'	=> array('UINT:4', 7),
					),

					'PRIMARY_KEY' => array('page_id'),
				),
			),
		);
	}

	/**
	 * Drop the PayPal Donation tables schema from the database
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'ppde_currency',
				$this->table_prefix . 'ppde_donation_pages',
			),
		);
	}
}
