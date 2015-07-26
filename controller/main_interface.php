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
 * Interface for our main controller
 *
 * This describes all of the methods we'll use for the admin front-end of this extension
 */
interface main_interface
{
	public function handle();

	/**
	 * Get default currency symbol
	 *
	 * @param int $id
	 *
	 * @return array
	 * @access public
	 */
	public function get_default_currency_data($id = 0);

	/**
	 * Generate statistics percent for display
	 *
	 * @param string $type
	 * @param        $multiplicand
	 * @param        $dividend
	 *
	 * @access public
	 */
	public function generate_stats_percent($multiplicand, $dividend, $type = '');

	/**
	 * Retrieve the language key for donation goal
	 *
	 * @param string $currency_symbol Currency symbol
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_goal_langkey($currency_symbol);

	/**
	 * Retrieve the language key for donation raised
	 *
	 * @param string $currency_symbol Currency symbol
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_raised_langkey($currency_symbol);

	/**
	 * Retrieve the language key for donation used
	 *
	 * @param string $currency_symbol Currency symbol
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_used_langkey($currency_symbol);
}
