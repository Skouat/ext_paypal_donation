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
 * Interface for our admin currency controller
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
	 * Add a currency
	 *
	 * @return null
	 * @access public
	 */
	public function add_currency();

	/**
	 * Edit a currency
	 *
	 * @param int $currency_id Currency identifier to edit
	 *
	 * @return null
	 * @access public
	 */
	public function edit_currency($currency_id);

	/**
	 * Move a currency up/down
	 *
	 * @param int    $currency_id The currency identifier to move
	 * @param string $direction   The direction (up|down)
	 *
	 * @return null
	 * @access   public
	 */
	public function move_currency($currency_id, $direction);

	/**
	 * Enable/disable a currency
	 *
	 * @param int    $currency_id
	 * @param string $action
	 *
	 * @return null
	 * @access public
	 */
	public function enable_currency($currency_id, $action);

	/**
	 * Delete a currency
	 *
	 * @param int $currency_id
	 *
	 * @return null
	 * @access public
	 */
	public function delete_currency($currency_id);
}
