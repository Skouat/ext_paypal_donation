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
	*	ppde_item:
	*		item_id
	*		item_type
	*		item_name
	*		item_iso_code
	*		item_symbol
	*		item_text
	*		item_enable
	*		left_id
	*		right_id
	*		item_text_bbcode_bitfield
	*		item_text_bbcode_uid
	*		item_text_bbcode_options
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'ppde_item' => array(
					'COLUMNS' => array(
						'item_id'					=> array('UINT', null, 'auto_increment'),
						'item_type'					=> array('VCHAR:16', ''),
						'item_name'					=> array('VCHAR:50', ''),
						'item_iso_code'				=> array('VCHAR:10', ''),
						'item_symbol'				=> array('VCHAR:10', ''),
						'item_text'					=> array('TEXT', ''),
						'item_enable'				=> array('BOOL', 1),
						'left_id'					=> array('UINT', 0),
						'right_id'					=> array('UINT', 0),
						'item_text_bbcode_bitfield'	=> array('VCHAR:255', ''),
						'item_text_bbcode_uid'		=> array('VCHAR:8', ''),
						'item_text_bbcode_options'	=> array('UINT:4', 7),
					),

					'PRIMARY_KEY'	=> array('item_id'),
				),
			),
		);
	}
}
