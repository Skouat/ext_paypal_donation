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

/**
 * Interface for a donation page
 *
 * This describes all of the methods we'll have for a single donation page
 */
interface currency_interface
{
	/**
	 * Check if required field are set
	 *
	 * @return bool
	 * @access public
	 */
	public function check_required_field();

	/**
	 * Get Currency status
	 *
	 * @return boolean
	 * @access public
	 */
	public function get_currency_enable();

	/**
	 * Get the order number of the currency
	 *
	 * @return int Order identifier
	 * @access public
	 */
	public function get_currency_order();

	/**
	 * Get Currency status
	 *
	 * @return boolean
	 * @access public
	 */
	public function get_currency_position();

	/**
	 * Get Currency ISO code name
	 *
	 * @return int Lang identifier
	 * @access public
	 */
	public function get_iso_code();

	/**
	 * Get Currency Symbol
	 *
	 * @return string Currency symbol
	 * @access public
	 */
	public function get_symbol();

	/**
	 * Set Currency status
	 *
	 * @param bool $enable
	 *
	 * @return bool
	 * @access public
	 */
	public function set_currency_enable($enable);

	/**
	 * Set Currency status
	 *
	 * @param bool $on_left
	 *
	 * @return bool
	 * @access public
	 */
	public function set_currency_position($on_left);

	/**
	 * Set Currency ISO code name
	 *
	 * @param string $iso_code
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_iso_code($iso_code);

	/**
	 * Set Currency symbol
	 *
	 * @param string $symbol
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_symbol($symbol);
}
