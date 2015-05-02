<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\operators;

/**
* Operator for a set of pages
*/
class donation_page implements donation_page_interface
{
	protected $data;

	protected $db;
	protected $user;
	protected $ppde_item_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                 Database connection
	* @param \phpbb\user                          $user               User object
	* @param string                               $ppde_item_table    Table name
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $ppde_item_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->ppde_item_table = $ppde_item_table;
	}

	/**
	* Get data from item_data table
	*
	* @param string $item_type - Can only be "donation_page" or "currency"
	* @param int    $lang_id
	* @return array Array of page data entities
	* @access public
	*/
	public function get_item_data($item_type, $lang_id = 0)
	{
		$entities = array();

		// Load all page data from the database
		// Build sql query with alias field
		$sql = 'SELECT item_id, item_name AS donation_title, item_iso_code AS lang_iso
				FROM ' . $this->ppde_item_table . "
				WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_iso_code = " . (int) ($lang_id);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Import each donatino page row into an entity
			$entities[] = $this->import($row);
		}
		$this->db->sql_freeresult($result);

		// Return all page entities
		return $entities;
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
	* @throws trigger_error()
	*/
	public function import($data)
	{
		// Clear out any saved data
		$this->data = array();

		// All of our fields
		$fields = array(
			// column			=> data type (see settype())
			'item_id'			=> 'integer',
			'donation_title'	=> 'string',
			'lang_iso'			=> 'integer',
		);

		// Go through the basic fields and set them to our data array
		foreach ($fields as $field => $type)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$field]))
			{
				trigger_error($this->user->lang('PPDE_FIELD_MISSING') . adm_back_link($this->u_action), E_USER_WARNING);
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
	* Get list of all installed language packs
	*
	* @return array Array of page data entities
	* @access public
	*/
	public function get_languages()
	{
		$langs = array();

		$sql = 'SELECT *
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$langs[$row['lang_iso']] = array(
				'name'	=> $row['lang_local_name'],
				'id'	=> (int) $row['lang_id'],
			);
		}
		$this->db->sql_freeresult($result);

		// Return all available languages
		return $langs;
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
