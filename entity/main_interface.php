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
interface main_interface
{
	/**
	* Load the data from the database for this rule
	*
	* @param int $id Item identifier
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function load($id);

	/**
	* Import and validate data for donation page
	*
	* Used when the data is already loaded externally.
	* Any existing data on this page is over-written.
	* All data is validated and an exception is thrown if any data is invalid.
	*
	* @param array $data Data array, typically from the database
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function import($data);

	/**
	* Insert the item for the first time
	*
	* Will throw an exception if the item was already inserted (call save() instead)
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function insert();

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
	* Set Lang identifier
	*
	* @param int $lang
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_lang_id($lang);

	/**
	* Get ISO code
	*
	* @return string Item ISO Code
	* @access public
	*/
	public function get_iso_code();

	/**
	* Get Item type
	*
	* @return string Item type
	* @access public
	*/
	public function get_type();

	/**
	* Set Item type
	*
	* @param string $type
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_type($type);

	/**
	* Get Item name
	*
	* @return string Item name
	* @access public
	*/
	public function get_name();

	/**
	* Set name
	*
	* @param string $name
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_name($name);

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
	* @return string
	* @access public
	*/
	public function get_message_for_display($censor_text = true);

	/**
	* Set message
	*
	* @param string $message
	* @return main_interface $this object for chaining calls; load()->set()->save()
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
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_enable_bbcode();

	/**
	* Disable bbcode on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
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
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_enable_magic_url();

	/**
	* Disable magic url on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
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
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_enable_smilies();

	/**
	* Disable smilies on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_disable_smilies();

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action);
}
