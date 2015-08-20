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
	 * SQL Query to return the ID of selected donation page
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data_exists();

	/**
	 * Get language id
	 *
	 * @return int Lang identifier
	 * @access public
	 */
	public function get_lang_id();

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
	 * Get message for edit
	 *
	 * @return string
	 * @access public
	 */
	public function get_message_for_edit();

	/**
	 * Get template vars
	 *
	 * @return array $this->dp_vars
	 * @access public
	 */
	public function get_vars();

	/**
	 * Check if bbcode is enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_bbcode_enabled();

	/**
	 * Disable bbcode on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_bbcode();

	/**
	 * Disable magic url on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_magic_url();

	/**
	 * Disable smilies on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_smilies();

	/**
	 * Enable bbcode on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_bbcode();

	/**
	 * Enable magic url on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_magic_url();

	/**
	 * Enable smilies on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_smilies();

	/**
	 * Check if magic_url is enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_magic_url_enabled();

	/**
	 * Check if smilies are enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_smilies_enabled();

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
	 * Set Lang identifier
	 *
	 * @param int $lang
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_lang_id($lang);

	/**
	 * Set message
	 *
	 * @param string $message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_message($message);
}
