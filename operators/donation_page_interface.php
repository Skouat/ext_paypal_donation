<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\operators;

/**
* Interface for our pages operator
*
* This describes all of the methods we'll have for working with a set of pages
*/
interface donation_page_interface
{
	/**
	* Get data from item_data table
	*
	* @param string $item_type - Can only be "donation_page" or "currency"
	* @param int    $lang_id
	* @return array Array of page data entities
	* @access public
	*/
	public function get_item_data($item_type, $lang_id = 0);

	/**
	* Get list language packs
	*
	* @param int    $lang_id
	* @return array Array of page data entities
	* @access public
	*/
	public function get_languages($lang_id = 0);

	/**
	* Add a Item
	*
	* @param object $entity Item entity with new data to insert
	* @return page_interface Added page entity
	* @access public
	*/
	public function add_item_data($entity);
}
