<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\controller;

/**
* Interface for our admin donation_pages_controller
*
* This describes all of the methods we'll use for the admin front-end of this extension
*/
interface admin_currency_interface
{
	/**
	* Display the pages
	*
	* @return null
	* @access public
	*/
	public function display_currency();

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action);
}
