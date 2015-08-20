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
	 * Check all items order and fix them if necessary
	 *
	 * @return null
	 * @access public
	 */
	public function fix_currency_order();

	/**
	 * SQL Query to return currency data table
	 *
	 * @param int  $currency_id  Identifier of currency; Set to 0 to get all currencies
	 * @param bool $only_enabled Status of currency (Default: false)
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data($currency_id = 0, $only_enabled = false);

	/**
	 * Checks if the currency is the last enabled.
	 *
	 * @param string $action
	 *
	 * @return bool
	 * @access public
	 */
	public function last_currency_enabled($action = '');

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
}
