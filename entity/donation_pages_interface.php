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
interface donation_pages_interface
{
	/**
	 * Load the data from the database for this donation page
	 *
	 * @param int $id Item identifier
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function load($id);

	/**
	 * Check the page_id exist from the database for this donation page
	 *
	 * @return int $this->dp_data['page_id'] Donation page identifier; 0 if the page doesn't exist
	 * @access public
	 */
	public function donation_page_exists();

	/**
	 * Import and validate data for donation page
	 *
	 * Used when the data is already loaded externally.
	 * Any existing data on this page is over-written.
	 * All data is validated and an exception is thrown if any data is invalid.
	 *
	 * @param array $data Data array, typically from the database
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function import($data);

	/**
	 * Insert the item for the first time
	 *
	 * Will throw an exception if the item was already inserted (call save() instead)
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function insert();

	/**
	 * Save the current settings to the database
	 *
	 * This must be called before closing or any changes will not be saved!
	 * If adding a page (saving for the first time), you must call insert() or an exception will be thrown
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function save();

	/**
	 * Get id
	 *
	 * @return int Item identifier
	 * @access public
	 */
	public function get_id();

	/**
	 * Get language id
	 *
	 * @return int Lang identifier
	 * @access public
	 */
	public function get_lang_id();

	/**
	 * Get Page title
	 *
	 * @return string Title type
	 * @access public
	 */
	public function get_title();

	/**
	 * Get template vars
	 *
	 * @return $this->dp_vars
	 * @access public
	 */
	public function get_vars();

	/**
	 * Set Lang identifier
	 *
	 * @param int $lang
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_lang_id($lang);

	/**
	 * Set Page title
	 *
	 * @param string $title
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_title($title);

	/**
	 * Get message for edit
	 *
	 * @return string
	 * @access public
	 */
	public function get_message_for_edit();

	/**
	 * Get message for display
	 *
	 * @param bool $censor_text True to censor the text (Default: true)
	 *
	 * @return string
	 * @access public
	 */
	public function get_message_for_display($censor_text = true);

	/**
	 * Replace template vars in the message
	 *
	 * @param string $message
	 *
	 * @return string
	 * @access public
	 */
	public function replace_template_vars($message);

	/**
	 * Set message
	 *
	 * @param string $message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_message($message);

	/**
	 * Check if bbcode is enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_bbcode_enabled();

	/**
	 * Enable bbcode on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_bbcode();

	/**
	 * Disable bbcode on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_bbcode();

	/**
	 * Check if magic_url is enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_magic_url_enabled();

	/**
	 * Enable magic url on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_magic_url();

	/**
	 * Disable magic url on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_magic_url();

	/**
	 * Check if smilies are enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_smilies_enabled();

	/**
	 * Enable smilies on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_smilies();

	/**
	 * Disable smilies on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_smilies();

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
