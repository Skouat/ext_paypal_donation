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
	 * Build pull down menu options of available currency
	 *
	 * @param int $config_value Currency identifier; default: 0
	 *
	 * @return null
	 * @access public
	 */
	public function build_currency_select_menu($config_value = 0);

	/**
	 * Get auth acl for 'u_ppde_use'
	 *
	 * @return bool
	 */
	public function can_use_ppde();

	/**
	 * Check if cURL is available
	 *
	 * @return bool
	 * @access public
	 */
	public function check_curl();

	/**
	 * Check if fsockopen is available
	 *
	 * @return bool
	 * @access public
	 */
	public function check_fsockopen();

	/**
	 * Assign statistics vars to the template
	 *
	 * @return null
	 * @access public
	 */
	public function display_stats();

	/**
	 * Get default currency data
	 *
	 * @param int $id
	 *
	 * @return array
	 * @access public
	 */
	public function get_default_currency_data($id = 0);

	/**
	 * Get PayPal URL
	 * Used in form and in IPN process
	 *
	 * @param bool $is_test_ipn
	 *
	 * @return string
	 * @access public
	 */
	public function get_paypal_url($is_test_ipn = false);

	/**
	 * Retrieve the language key for donation goal
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_goal_langkey($currency_symbol, $on_left = true);

	/**
	 * Retrieve the language key for donation raised
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_raised_langkey($currency_symbol, $on_left = true);

	/**
	 * Retrieve the language key for donation used
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_used_langkey($currency_symbol, $on_left = true);

	/**
	 * Load metadata for this extension
	 *
	 * @return null
	 * @access public
	 */
	public function load_metadata();
	/**
	 * Check if Sandbox is enable
	 *
	 * @return bool
	 * @access public
	 */
	public function use_sandbox();
}
