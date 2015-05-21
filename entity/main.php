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
class main implements main_interface
{
	/** @var string */
	protected $table_name;

	/**
	 * Suffix for the language keys returned by exceptions
	 *
	 * @type string
	 */
	protected $message_suffix;

	/**
	 * Column names in the table
	 *
	 * @type string
	 */
	protected $column_item_id = 'item_id';


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
	 * @param string                            $table_name     Table name
	 * @param string                            $message_suffix Prefix for the messages thrown by exceptions
	 * @param array                             $columns        Array with column names to overwrite
	 *
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $table_name, $message_suffix = '', $columns = array())
	{
		$this->db = $db;
		$this->user = $user;
		$this->table_name = $table_name;
		$this->message_suffix = $message_suffix;
		if (!empty($columns))
		{
			foreach ($columns as $column => $name)
			{
				$column_name = 'column_' . $column;
				$this->$column_name = $name;
			}
		}
	}

	/**
	 * Display Error message
	 *
	 * @param string $lang_key
	 *
	 * @return null
	 * @access protected
	 */
	protected function display_error_message($lang_key)
	{
		$message = call_user_func_array(array($this->user, 'lang'), array_merge(array(strtoupper($lang_key)))) . adm_back_link($this->u_action);
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
			WHERE ' . $this->column_item_id . ' = ' . (int) $id;
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
