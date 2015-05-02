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
	* Import and validate data for donation page
	*
	* Used when the data is already loaded externally.
	* Any existing data on this page is over-written.
	* All data is validated and an exception is thrown if any data is invalid.
	*
	* @param array $data Data array, typically from the database
	* @return page_interface $this->data object
	* @access public
	* @throws trigger_error()
	*/
	public function import($data);

	/**
	* Get list of all installed language packs
	*
	* @return array Array of page data entities
	* @access public
	*/
	public function get_languages();

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action);
}
