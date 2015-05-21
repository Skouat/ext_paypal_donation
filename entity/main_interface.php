<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\entity;

interface main_interface
{
	/**
	 * Load the data from the database
	 *
	 * @param int $id
	 *
	 * @return main_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function load($id);

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 *
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action);
}
