<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\entity;

use phpbb\db\driver\driver_interface;
use phpbb\language\language;

abstract class main
{
	/** @type string */
	protected $u_action;
	/**
	 * Declare overridden properties
	 */
	protected $db;
	protected $data;
	protected $lang_key_prefix;
	protected $lang_key_suffix;
	protected $language;
	protected $table_name;
	protected $table_schema;

	/**
	 * Construct
	 *
	 * @param driver_interface $db              Database object
	 * @param language         $language        Language object
	 * @param string           $lang_key_prefix Prefix for the messages thrown by exceptions
	 * @param string           $lang_key_suffix Suffix for the messages thrown by exceptions
	 * @param string           $table_name      Table name
	 * @param array            $table_schema    Array with column names to overwrite and type of data
	 *
	 * @access public
	 */
	public function __construct(
		driver_interface $db,
		language $language,
		$lang_key_prefix = '',
		$lang_key_suffix = '',
		$table_name = '',
		$table_schema = []
	)
	{
		$this->db = $db;
		$this->language = $language;
		$this->lang_key_prefix = $lang_key_prefix;
		$this->lang_key_suffix = $lang_key_suffix;
		$this->table_name = $table_name;
		$this->table_schema = $table_schema;
	}

	/**
	 * Set data in the $entity object.
	 * Use call_user_func_array() to call $entity function
	 *
	 * @param array $data_ary
	 *
	 * @return void
	 * @access public
	 */
	public function set_entity_data($data_ary): void
	{
		foreach ($data_ary as $entity_function => $data)
		{
			// Calling the set_$entity_function on the entity and passing it $currency_data
			call_user_func_array([$this, 'set_' . $entity_function], [$data]);
		}
		unset($entity_function);
	}

	/**
	 * Parse data to the entity
	 *
	 * @param string $run_before_insert Name of the function to call before SQL INSERT
	 *
	 * @return string
	 * @access public
	 */
	public function add_edit_data($run_before_insert = ''): string
	{
		if ($this->get_id())
		{
			// Save the edited item entity to the database
			$this->save($this->check_required_field());
			return 'UPDATED';
		}

		// Insert the data to the database
		$this->insert($run_before_insert);

		// Get the newly inserted identifier
		$id = $this->get_id();

		// Reload the data to return a fresh entity
		$this->load($id);
		return 'ADDED';
	}

	/**
	 * Insert the item for the first time
	 *
	 * Will throw an exception if the item was already inserted (call save() instead)
	 *
	 * @param string $run_before_insert
	 *
	 * @return main $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function insert($run_before_insert = '')
	{
		if (!empty($this->data[$this->table_schema['item_id']['name']]))
		{
			// The item already exists
			$this->display_warning_message($this->lang_key_prefix . '_EXIST');
		}

		// Run some stuff before insert data in database
		$this->run_function_before_action($run_before_insert);

		// Make extra sure there is no item_id set
		unset($this->data[$this->table_schema['item_id']['name']]);

		// Insert the item data to the database
		$sql = 'INSERT INTO ' . $this->table_name . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the item_id using the id created by the SQL insert
		$this->data[$this->table_schema['item_id']['name']] = (int) $this->db->sql_nextid();

		return $this;
	}

	/**
	 * Display a user warning message
	 *
	 * @param string $lang_key
	 * @param string $args
	 *
	 * @return void
	 * @access protected
	 */
	protected function display_warning_message($lang_key, $args = ''): void
	{
		$message = call_user_func_array([$this->language, 'lang'], array_merge([strtoupper($lang_key), $args])) . $this->adm_back_link_exists();
		trigger_error($message, E_USER_WARNING);
	}

	/**
	 * Checks if the adm_back_link function is loaded
	 *
	 * @return string
	 * @access protected
	 */
	protected function adm_back_link_exists(): string
	{
		return (function_exists('adm_back_link')) ? adm_back_link($this->u_action) : '';
	}

	/**
	 * Run function before do some alter some data in the database
	 *
	 * @param string $function_name
	 *
	 * @return bool
	 * @access private
	 */
	private function run_function_before_action($function_name): bool
	{
		$func_result = true;
		if ($function_name)
		{
			$func_result = (bool) call_user_func([$this, $function_name]);
		}

		return $func_result;
	}

	/**
	 * Save the current settings to the database
	 *
	 * This must be called before closing or any changes will not be saved!
	 * If adding a item (saving for the first time), you must call insert() or an exception will be thrown
	 *
	 * @param bool $required_fields
	 *
	 * @return main $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function save($required_fields)
	{
		if ($required_fields)
		{
			// The item already exists
			$this->display_warning_message($this->lang_key_prefix . '_NO_' . $this->lang_key_suffix);
		}

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE ' . $this->db->sql_escape($this->table_schema['item_id']['name']) . ' = ' . $this->get_id();
		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return int Item identifier
	 * @access public
	 */
	public function get_id(): int
	{
		return (int) ($this->data[$this->table_schema['item_id']['name']] ?? 0);
	}

	/**
	 * Check the Identifier of the called data exists in the database
	 *
	 * @param string $sql SQL Query
	 *
	 * @return bool
	 * @access public
	 */
	public function data_exists($sql): bool
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
	 * @return main $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_id($id)
	{
		$this->data[$this->table_schema['item_id']['name']] = (int) $id;

		return $this;
	}

	/**
	 * SQL Query to return the ID of selected item
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data_exists(): string
	{
		return 'SELECT ' . $this->table_schema['item_id']['name'] . '
 			FROM ' . $this->table_name . '
			WHERE ' . $this->db->sql_escape($this->table_schema['item_id']['name']) . ' = ' . (int) $this->data[$this->table_schema['item_id']['name']];
	}

	/**
	 * Load the data from the database
	 *
	 * @param int $id
	 *
	 * @return main $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function load($id)
	{
		$sql = 'SELECT *
			FROM ' . $this->table_name . '
			WHERE ' . $this->db->sql_escape($this->table_schema['item_id']['name']) . ' = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			// An item does not exist
			$this->display_warning_message($this->lang_key_prefix . '_NO_' . $this->lang_key_suffix);
		}

		return $this;
	}

	/**
	 * Get Item name
	 *
	 * @return string Item name
	 * @access public
	 */
	public function get_name(): string
	{
		return (string) ($this->data[$this->table_schema['item_name']['name']] ?? '');
	}

	/**
	 * Set Item name
	 *
	 * @param string $name
	 *
	 * @return main $this object for chaining calls; load()->set()->save()
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
	 * @return void
	 * @access public
	 */
	public function set_page_url($u_action): void
	{
		$this->u_action = $u_action;
	}

	/**
	 * Check if required field is set
	 *
	 * @return bool
	 * @access public
	 */
	public function check_required_field(): bool
	{
		return false;
	}

	/**
	 * Delete data from the database
	 *
	 * @param int    $id
	 * @param string $action_before_delete Function to start before deleting data.
	 * @param string $sql_where
	 * @param bool   $all                  Set to true if you want delete all data from the table.
	 *
	 * @return bool
	 * @access public
	 */
	public function delete($id, $action_before_delete = '', $sql_where = '', $all = false): bool
	{
		$where_clause = '';

		if (!$all)
		{
			if (empty($sql_where) && $this->disallow_deletion($id))
			{
				// The item selected does not exists
				$this->display_warning_message($this->lang_key_prefix . '_NO_' . $this->lang_key_suffix);
			}

			$where_clause = !empty($sql_where) ? $sql_where : ' WHERE ' . $this->db->sql_escape($this->table_schema['item_id']['name']) . ' = ' . (int) $id;
		}

		$this->run_function_before_action($action_before_delete);

		// Delete data from the database
		$sql = 'DELETE FROM ' . $this->table_name . $where_clause;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	/**
	 * Returns if we can proceed to item deletion
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	private function disallow_deletion($id): bool
	{
		return empty($this->data[$this->table_schema['item_id']['name']]) || ((int) $this->data[$this->table_schema['item_id']['name']] !== $id);
	}

	/**
	 * Get data from the database
	 *
	 * @param string $sql
	 * @param array  $additional_table_schema
	 * @param int    $limit
	 * @param int    $limit_offset
	 * @param bool   $override
	 *
	 * @return array
	 * @access public
	 */
	public function get_data($sql, $additional_table_schema = [], $limit = 0, $limit_offset = 0, $override = false): array
	{
		$entities = [];
		$result = $this->limit_query($sql, $limit, $limit_offset);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Import each row into an entity
			$entities[] = $this->import($row, $additional_table_schema, $override);
		}
		$this->db->sql_freeresult($result);

		// Return all entities
		return $entities;
	}

	/**
	 * Use query limit if requested
	 *
	 * @param string $sql
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return mixed
	 * @access private
	 */
	private function limit_query($sql, $limit, $offset)
	{
		return empty($limit) ? $this->db->sql_query($sql) : $this->db->sql_query_limit($sql, $limit, $offset);
	}

	/**
	 * Import and validate data
	 *
	 * Used when the data is already loaded externally.
	 * Any existing data on this item is over-written.
	 * All data is validated and an exception is thrown if any data is invalid.
	 *
	 * @param array $data Data array, typically from the database
	 * @param array $additional_table_schema
	 * @param bool  $override
	 *
	 * @return array $this->data
	 * @access public
	 */
	public function import($data, $additional_table_schema = [], $override = false): array
	{
		// Clear out any saved data
		$this->data = [];

		// Adds additional field to the table schema
		$this->table_schema = !$override ? array_merge($this->table_schema, $additional_table_schema) : $additional_table_schema;

		// Go through the basic fields and set them to our data array
		foreach ($this->table_schema as $generic_field => $field)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$field['name']]))
			{
				$this->display_warning_message('EXCEPTION_INVALID_FIELD', $field['name']);
			}

			// settype passes values by reference
			$value = $data[$field['name']];

			// We're using settype to enforce data types
			settype($value, $field['type']);

			$this->data[$field['name']] = $value;
		}

		return $this->data;
	}
}
