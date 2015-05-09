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
* Interface for our admin controller
*
* This describes all of the methods we'll use for the admin front-end of this extension
*/
interface admin_interface
{
	/**
	* Display the general settings a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_overview($id, $mode, $action);

	/**
	* Display the general settings a user can configure for this extension
	*
	* @return null
	* @access public
	*/

	public function display_settings();

	/**
	* Add a donation page
	*
	* @return null
	* @access public
	*/
	public function add_donation_page();

	/**
	* Edit a donation page
	*
	* @param int $page_id Donation page identifier to edit
	* @return null
	* @access public
	*/
	public function edit_donation_page($page_id);

	/**
	* Delete a donation page
	*
	* @param int $page_id The donation page identifier to delete
	* @return null
	* @access public
	*/
	public function delete_donation_page($page_id);

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action);
}
