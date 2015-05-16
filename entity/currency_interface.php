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
	 * Load the data from the database for this currency
	 *
	 * @param int $id Item identifier
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function load($id);

	/**
	 * Check the currency_id exist from the database for this currency
	 *
	 * @return int $this->currency_data['currency_id'] Currency identifier; 0 if the currency doesn't exist
	 * @access public
	 */
	public function currency_exists();

	/**
	 * Import and validate data for donation page
	 *
	 * Used when the data is already loaded externally.
	 * Any existing data on this page is over-written.
	 * All data is validated and an exception is thrown if any data is invalid.
	 *
	 * @param array $data Data array, typically from the database
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function import($data);

	/**
	 * Save the current settings to the database
	 *
	 * This must be called before closing or any changes will not be saved!
	 * If adding a page (saving for the first time), you must call insert() or an exception will be thrown
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function save();

	/**
	 * Get id
	 *
	 * @return int Currency identifier
	 * @access public
	 */
	public function get_id();

	/**
	 * Get Currency status
	 *
	 * @return boolean
	 * @access public
	 */
	public function get_currency_enable();

	/**
	 * Set Currency status
	 *
	 * @param bool $enable
	 * @return bool
	 * @access public
	 */
	public function set_currency_enable($enable);

	/**
	 * Get Currency ISO code name
	 *
	 * @return int Lang identifier
	 * @access public
	 */
	public function get_iso_code();

	/**
	 * Set Currency ISO code name
	 *
	 * @param string $iso_code
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_iso_code($iso_code);

	/**
	 * Get Currency name
	 *
	 * @return string Currency name
	 * @access public
	 */
	public function get_name();

	/**
	 * Set Currency name
	 *
	 * @param string $name
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_name($name);

	/**
	 * Get Currency Symbol
	 *
	 * @return string Currency symbol
	 * @access public
	 */
	public function get_symbol();

	/**
	 * Set Currency symbol
	 *
	 * @param string $symbol
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_symbol($symbol);

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action);
}
