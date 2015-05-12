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
* Interface for our currency operator
*
* This describes all of the methods we'll have for working with a set of pages
*/
interface currency_interface
{
	/**
	* Get data from currency table
	*
	* @param int    $currency_id
	* @return array Array of currency data entities
	* @access public
	*/
	public function get_currency_data($currency_id = 0);
}
