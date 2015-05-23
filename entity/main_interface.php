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
	 * Import and validate data
	 *
	 * Used when the data is already loaded externally.
	 * Any existing data on this page is over-written.
	 * All data is validated and an exception is thrown if any data is invalid.
	 *
	 * @param  array $data Data array, typically from the database
	 *
	 * @return main_interface $this->data object
	 * @access public
	 */
	public function import($data);

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
	 * Get id
	 *
	 * @return int Item identifier
	 * @access public
	 */
	public function get_id();

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
