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
 * @property string u_action
 */
abstract class main implements main_interface
{
	/**
	 * Suffix for the language keys returned by exceptions
	 *
	 * @type string
	 */
	protected $message_suffix;

	/** @var string */
	protected $table_name;

	/**
	 * Table schema and data type in the table
	 *
	 * @type array
	 */
	protected $table_schema;

	/**
	 * Declare overridden properties
	 *
	 * @type mixed
	 */
	protected $db;
	protected $user;
	protected $data;

	/**
	 * Construct
	 *
	 * @param \phpbb\db\driver\driver_interface $db             Database object
	 * @param \phpbb\user                       $user           User object
	 * @param string                            $message_suffix Prefix for the messages thrown by exceptions
	 * @param string                            $table_name     Table name
	 * @param array                             $table_schema   Array with column names to overwrite and type of data
	 *
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $message_suffix = '', $table_name = '', $table_schema = array())
	{
		$this->db = $db;
		$this->user = $user;
		$this->message_suffix = $message_suffix;
		$this->table_name = $table_name;
		$this->table_schema = $table_schema;
	}

	/**
	 * Import and validate data
	 *
	 * Used when the data is already loaded externally.
	 * Any existing data on this page is over-written.
	 * All data is validated and an exception is thrown if any data is invalid.
	 *
	 * @param  array $data Data array, typically from the database
	 *
	 * @return main_interface $this->data object
	 * @throws \skouat\ppde\exception\invalid_argument
	 * @access public
	 */
	public function import($data)
	{
		// Clear out any saved data
		$this->data = array();

		// Go through the basic fields and set them to our data array
		foreach ($this->table_schema as $generic_field => $field)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$field['name']]))
			{
				throw new \skouat\ppde\exception\invalid_argument(array($field['name'], 'FIELD_MISSING'));
			}

			// settype passes values by reference
			$value = $data[$field['name']];

			// We're using settype to enforce data types
			settype($value, $field['type']);

			$this->data[$field['name']] = $value;
		}
		unset($field);

		return $this->data;
	}

	/**
	 * Display Error message
	 *
	 * @param string $lang_key
	 * @param string $args
	 *
	 * @return null
	 * @access protected
	 */
	protected function display_error_message($lang_key, $args = '')
	{
		$message = call_user_func_array(array($this->user, 'lang'), array_merge(array(strtoupper($lang_key), $args))) . adm_back_link($this->u_action);
		trigger_error($message, E_USER_WARNING);
	}

	/**
	 * Load the data from the database
	 *
	 * @param int $id
	 *
	 * @return main_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function load($id)
	{
		$sql = 'SELECT *
			FROM ' . $this->table_name . '
			WHERE ' . $this->table_schema['item_id']['name'] . ' = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			// A item does not exist
			$this->display_error_message('PPDE_NO_' . $this->message_suffix);
		}

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
		return (isset($this->data[$this->table_schema['item_id']['name']])) ? (int) $this->data[$this->table_schema['item_id']['name']] : 0;
	}

	/**
	 * Get Item name
	 *
	 * @return string Item name
	 * @access public
	 */
	public function get_name()
	{
		return (isset($this->data[$this->table_schema['item_name']['name']])) ? (string) $this->data[$this->table_schema['item_name']['name']] : '';
	}

	/**
	 * Set Item name
	 *
	 * @param string $name
	 *
	 * @return main_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_name($name)
	{
		// Set the item type on our data array
		$this->data[$this->table_schema['item_name']['name']] = (string) $name;

		return $this;
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 *
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
