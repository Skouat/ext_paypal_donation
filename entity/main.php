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
* Entity for a donation page
*/
class main implements main_interface
{
	/**
	* Data for this entity
	*
	* @var array
	*	item_id
	*	item_type
	*	item_name
	*	item_iso_code
	*	item_symbol
	*	item_text
	*	item_text_bbcode_uid
	*	item_text_bbcode_bitfield
	*	item_text_bbcode_options
	*	item_left_id
	*	item_right_id
	*	item_enable
	* @access protected
	*/
	protected $data;

	protected $db;
	protected $user;
	protected $item_data_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                 Database object
	* @param \phpbb\user                          $user               User object
	* @param string                               $item_data_table    Name of the table used to store data
	* @return \skouat\ppde\entity\main
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $item_data_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->item_data_table = $item_data_table;
	}

	/**
	* Load the data from the database for this rule
	*
	* @param int $id Item identifier
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function load($id)
	{
		$sql = 'SELECT *
			FROM ' . $this->item_data_table . '
			WHERE item_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			// A item does not exist
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array('PPDE_NO_ITEM'))) . adm_back_link($this->u_action);
			trigger_error($message, E_USER_WARNING);
		}

		return $this;
	}

	/**
	* Import and validate data for donation page
	*
	* Used when the data is already loaded externally.
	* Any existing data on this page is over-written.
	* All data is validated and an exception is thrown if any data is invalid.
	*
	* @param array $data Data array, typically from the database
	* @return page_interface $this->data object
	* @access public
	*/
	public function import($data)
	{
		// Clear out any saved data
		$this->data = array();

		// All of our fields
		$fields = array(
			// column		=> data type (see settype())
			'item_id'		=> 'integer',
			'item_type'		=> 'string',
			'item_name'		=> 'string',
			'item_iso_code'	=> 'integer',
			'item_symbol'	=> 'string',
			'item_left_id'	=> 'integer',
			'item_right_id'	=> 'integer',
			'item_enable'	=> 'boolean',

			'item_text'						=> 'string',
			'item_text_bbcode_uid'			=> 'string',
			'item_text_bbcode_bitfield'		=> 'string',
			'item_text_bbcode_options'		=> 'integer',
		);

		// Go through the basic fields and set them to our data array
		foreach ($fields as $field => $type)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$field]))
			{
				$message = call_user_func_array(array($this->user, 'lang'), array_merge(array('PPDE_FIELD_MISSING', $field))) . adm_back_link($this->u_action);
				trigger_error($message, E_USER_WARNING);
			}

			// settype passes values by reference
			$value = $data[$field];

			// We're using settype to enforce data types
			settype($value, $type);

			$this->data[$field] = $value;
		}

		return $this->data;
	}

	/**
	* Insert the item for the first time
	*
	* Will throw an exception if the item was already inserted (call save() instead)
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function insert()
	{
		if (!empty($this->data['item_id']))
		{
			// The item already exists
			$message = call_user_func_array(array($this->user, 'lang'), array_merge(array('PPDE_ITEM_EXIST'))) . adm_back_link($this->u_action);
			trigger_error($message, E_USER_WARNING);
		}

		// Resets values required for the nested set system
		$this->data['item_left_id'] = 0;
		$this->data['item_right_id'] = 0;

		// Make extra sure there is no item_id set
		unset($this->data['item_id']);

		// Insert the rule data to the database
		$sql = 'INSERT INTO ' . $this->item_data_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the rule_id using the id created by the SQL insert
		$this->data['item_id'] = (int) $this->db->sql_nextid();

		return $this;
	}

	/**
	* Get id
	*
	* @return int Item identifier
	* @access public
	*/
	public function get_id()
	{
		return (isset($this->data['item_id'])) ? (int) $this->data['item_id'] : 0;
	}

	/**
	* Get language id
	*
	* @return int Lang identifier
	* @access public
	*/
	public function get_lang_id()
	{
		return (isset($this->data['item_iso_code'])) ? (int) $this->data['item_iso_code'] : 0;
	}

	/**

	* Set Lang identifier
	*
	* @param int $lang
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_lang_id($lang)
	{
		// Set the lang_id on our data array
		$this->data['item_iso_code'] = (int) $lang;

		return $this;
	}

	/**
	* Get ISO code
	*
	* @return string Item ISO Code
	* @access public
	*/
	public function get_iso_code()
	{
		return (isset($this->data['item_iso_code'])) ? (int) $this->data['item_iso_code'] : '';
	}

	/**
	* Get Item type
	*
	* @return string Item type
	* @access public
	*/
	public function get_type()
	{
		return (isset($this->data['item_name'])) ? (string) $this->data['item_name'] : '';
	}

	/**
	* Set Item type
	*
	* @param string $type
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_type($type)
	{
		// Set the item type on our data array
		$this->data['item_type'] = (string) $type;

		return $this;
	}

	/**
	* Get Item name
	*
	* @return string Item name
	* @access public
	*/
	public function get_name()
	{
		return (isset($this->data['item_name'])) ? (string) $this->data['item_name'] : '';
	}

	/**
	* Set name
	*
	* @param string $name
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_name($name)
	{
		// Set the item_name on our data array
		$this->data['item_name'] = (string) $name;

		return $this;
	}

	/**
	* Get message for edit
	*
	* @return string
	* @access public
	*/
	public function get_message_for_edit()
	{
		// Use defaults if these haven't been set yet
		$message = (isset($this->data['item_text'])) ? $this->data['item_text'] : '';
		$uid = (isset($this->data['item_text_bbcode_uid'])) ? $this->data['item_text_bbcode_uid'] : '';
		$options = (isset($this->data['item_text_bbcode_options'])) ? (int) $this->data['item_text_bbcode_options'] : 0;

		// Generate for edit
		$message_data = generate_text_for_edit($message, $uid, $options);

		return $message_data['text'];
	}

	/**
	* Get message for display
	*
	* @param bool $censor_text True to censor the text (Default: true)
	* @return string
	* @access public
	*/
	public function get_message_for_display($censor_text = true)
	{
		// If these haven't been set yet; use defaults
		$message = (isset($this->data['item_text'])) ? $this->data['item_text'] : '';
		$uid = (isset($this->data['item_text_bbcode_uid'])) ? $this->data['item_text_bbcode_uid'] : '';
		$bitfield = (isset($this->data['item_text_bbcode_bitfield'])) ? $this->data['item_text_bbcode_bitfield'] : '';
		$options = (isset($this->data['item_text_bbcode_options'])) ? (int) $this->data['item_text_bbcode_options'] : 0;

		// Generate for display
		return generate_text_for_display($message, $uid, $bitfield, $options, $censor_text);
	}

	/**
	* Set message
	*
	* @param string $message
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function set_message($message)
	{
		// Prepare the text for storage
		$uid = $bitfield = $flags = '';
		generate_text_for_storage($message, $uid, $bitfield, $flags, $this->message_bbcode_enabled(), $this->message_magic_url_enabled(), $this->message_smilies_enabled());

		// Set the message to our data array
		$this->data['item_text'] = $message;
		$this->data['item_text_bbcode_uid'] = $uid;
		$this->data['item_text_bbcode_bitfield'] = $bitfield;
		// Flags are already set

		return $this;
	}

	/**
	* Check if bbcode is enabled on the message
	*
	* @return bool
	* @access public
	*/
	public function message_bbcode_enabled()
	{
		return ($this->data['item_text_bbcode_options'] & OPTION_FLAG_BBCODE);
	}

	/**
	* Enable bbcode on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_enable_bbcode()
	{
		$this->set_message_option(OPTION_FLAG_BBCODE);

		return $this;
	}

	/**
	* Disable bbcode on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_disable_bbcode()
	{
		$this->set_message_option(OPTION_FLAG_BBCODE, true);

		return $this;
	}

	/**
	* Check if magic_url is enabled on the message
	*
	* @return bool
	* @access public
	*/
	public function message_magic_url_enabled()
	{
		return ($this->data['item_text_bbcode_options'] & OPTION_FLAG_LINKS);
	}

	/**
	* Enable magic url on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_enable_magic_url()
	{
		$this->set_message_option(OPTION_FLAG_LINKS);

		return $this;
	}

	/**
	* Disable magic url on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_disable_magic_url()
	{
		$this->set_message_option(OPTION_FLAG_LINKS, true);

		return $this;
	}

	/**
	* Check if smilies are enabled on the message
	*
	* @return bool
	* @access public
	*/
	public function message_smilies_enabled()
	{
		return ($this->data['item_text_bbcode_options'] & OPTION_FLAG_SMILIES);
	}

	/**
	* Enable smilies on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_enable_smilies()
	{
		$this->set_message_option(OPTION_FLAG_SMILIES);

		return $this;
	}

	/**
	* Disable smilies on the message
	*
	* @return main_interface $this object for chaining calls; load()->set()->save()
	* @access public
	*/
	public function message_disable_smilies()
	{
		$this->set_message_option(OPTION_FLAG_SMILIES, true);

		return $this;
	}

	/**
	* Set option helper
	*
	* @param int $option_value Value of the option
	* @param bool $negate Negate (unset) option (Default: False)
	* @param bool $reparse_message Reparse the message after setting option (Default: True)
	* @return null
	* @access protected
	*/
	protected function set_message_option($option_value, $negate = false, $reparse_message = true)
	{
		// Set item_text_bbcode_options to 0 if it does not yet exist
		$this->data['item_text_bbcode_options'] = (isset($this->data['item_text_bbcode_options'])) ? $this->data['item_text_bbcode_options'] : 0;

		// If we're setting the option and the option is not already set
		if (!$negate && !($this->data['item_text_bbcode_options'] & $option_value))
		{
			// Add the option to the options
			$this->data['item_text_bbcode_options'] += $option_value;
		}

		// If we're unsetting the option and the option is already set
		if ($negate && $this->data['item_text_bbcode_options'] & $option_value)
		{
			// Subtract the option from the options
			$this->data['item_text_bbcode_options'] -= $option_value;
		}

		// Reparse the message
		if ($reparse_message && !empty($this->data['item_text']))
		{
			$message = $this->data['item_text'];

			decode_message($message, $this->data['item_text_bbcode_uid']);

			$this->set_message($message);
		}
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
