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
	 * @param int  $currency_id Identifier of currency; Set to 0 to get all currencies (Default: 0)
	 * @param bool $only_enabled Currency states (Default: false)
	 *
	 * @return array Array of currency data entities
	 * @access public
	 */
	public function get_currency_data($currency_id = 0, $only_enabled = false);

	/**
	 * Add a currency
	 *
	 * @param object $entity Currency entity with new data to insert
	 *
	 * @return currency_interface Added currency entity
	 * @access public
	 */
	public function add_currency_data($entity);

	/**
	 * Delete a currency
	 *
	 * @param int $currency_id The currency identifier to delete
	 *
	 * @return bool True if row was deleted, false otherwise
	 * @access public
	 */
	public function delete_currency_data($currency_id);

	/**
	 * Move a currency up/down
	 *
	 * @param int $switch_order_id The next value of the order
	 * @param int $current_order   The current order identifier
	 * @param int $id              The currency identifier to move
	 *
	 * @return bool
	 * @access public
	 */
	public function move($switch_order_id, $current_order, $id);

	/**
	 * Check all items order and fix them if necessary
	 *
	 * @return null
	 * @access public
	 */
	public function fix_currency_order();
}
