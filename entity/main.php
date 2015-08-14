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

abstract class main
{
	protected $lang_key_prefix;
	protected $lang_key_suffix;
	protected $table_name;
	protected $table_schema;
	protected $u_action;
	/**
	 * Declare overridden properties
	 */
	protected $db;
	protected $user;
	protected $data;

	/**
	 * Construct
	 *
	 * @param \phpbb\db\driver\driver_interface $db              Database object
	 * @param \phpbb\user                       $user            User object
	 * @param string                            $lang_key_prefix Prefix for the messages thrown by exceptions
	 * @param string                            $lang_key_suffix Suffix for the messages thrown by exceptions
	 * @param string                            $table_name      Table name
	 * @param array                             $table_schema    Array with column names to overwrite and type of data
	 *
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $lang_key_suffix = '', $lang_key_prefix = '', $table_name = '', $table_schema = array())
	{
		$this->db = $db;
		$this->user = $user;
		$this->lang_key_suffix = $lang_key_suffix;
		$this->lang_key_prefix = $lang_key_prefix;
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
	 * @return object $this->data object
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
	 * Insert the item for the first time
	 *
	 * Will throw an exception if the item was already inserted (call save() instead)
	 *
	 * @param string $run_before_insert
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function insert($run_before_insert = '')
	{
		if (!empty($this->data[$this->table_schema['item_id']['name']]))
		{
			// The page already exists
			$this->display_error_message($this->lang_key_prefix . '_EXIST');
		}

		// Run some stuff before insert data in database
		if ($run_before_insert)
		{
			$this->function_before_insert($run_before_insert);
		}

		// Make extra sure there is no page_id set
		unset($this->data[$this->table_schema['item_id']['name']]);

		// Insert the page data to the database
		$sql = 'INSERT INTO ' . $this->table_name . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the page_id using the id created by the SQL insert
		$this->data[$this->table_schema['item_id']['name']] = (int) $this->db->sql_nextid();

		return $this;
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
	 * @param $function_name
	 *
	 * @return mixed
	 */
	private function function_before_insert($function_name)
	{
		return call_user_func(array($this, $function_name));
	}

	/**
	 * Save the current settings to the database
	 *
	 * This must be called before closing or any changes will not be saved!
	 * If adding a page (saving for the first time), you must call insert() or an exception will be thrown
	 *
	 * @param bool $required_fields
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function save($required_fields)
	{
		if ($required_fields)
		{
			// The page already exists
			$this->display_error_message($this->lang_key_prefix . '_NO_' . $this->lang_key_suffix);
		}

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE ' . $this->table_schema['item_id']['name'] . ' = ' . $this->get_id();
		$this->db->sql_query($sql);

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
	 * Check the Identifier of the called data exists in the database
	 *
	 * @param string $sql SQL Query
	 *
	 * @return bool
	 * @access public
	 */
	public function data_exists($sql)
	{
		$this->db->sql_query($sql);
		$this->set_id($this->db->sql_fetchfield($this->table_schema['item_id']['name']));

		return (bool) $this->data[$this->table_schema['item_id']['name']];
	}

	/**
	 * Set item Identifier
	 *
	 * @param int $id
	 *
	 * @return object $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_id($id)
	{
			$this->data[$this->table_schema['item_id']['name']] = (int) $id;

		return $this;
	}

	/**
	 * SQL Query to return the ID of selected currency
	 *
	 * @return string
	 * @access public
	 */
	public function get_sql_data_exists()
	{
		return 'SELECT ' . $this->table_schema['item_id']['name'] . '
 			FROM ' . $this->table_name . '
			WHERE ' . $this->table_schema['item_id']['name'] . ' = ' . $this->data[$this->table_name['item_id']['name']];
	}

	/**
	 * Load the data from the database
	 *
	 * @param int $id
	 *
	 * @return object $this object for chaining calls; load()->set()->save()
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
			$this->display_error_message($this->lang_key_prefix . '_NO_' . $this->lang_key_suffix);
		}

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
		return (isset($this->data[$this->table_schema['item_name']['name']])) ? (string) $this->data[$this->table_schema['item_name']['name']] : '';
	}

	/**
	 * Set Item name
	 *
	 * @param string $name
	 *
	 * @return object $this object for chaining calls; load()->set()->save()
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

	/**
	 * Check if required field are set
	 *
	 * @return bool
	 * @access public
	 */
	public function check_required_field()
	{
		return false;
	}
}
