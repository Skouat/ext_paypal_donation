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
 * Interface for our admin settings controller
 *
 * This describes all of the methods we'll use for the admin front-end of this extension
 */
interface admin_settings_interface
{
	/**
	 * Display the general settings a user can configure for this extension
	 *
	 * @return null
	 * @access public
	 */
	public function display_settings();
}
