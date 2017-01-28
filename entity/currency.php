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
 * @property \phpbb\db\driver\driver_interface db                 phpBB Database object
 * @property \phpbb\user                       user               phpBB User object
 * @property string                            lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property string                            lang_key_suffix    Suffix for the messages thrown by exceptions
 */
class currency extends main
{
	/**
	 * Data for this entity
	 *
	 * @var array
	 *    currency_id
	 *    currency_name
	 *    currency_iso_code
	 *    currency_symbol
	 *    currency_enable
	 *    currency_order
	 * @access protected
	 */
	protected $data;
	protected $currency_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db         Database object
	 * @param \phpbb\user                       $user       User object
	 * @param string                            $table_name Name of the table used to store data
	 *
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $table_name)
	{
		$this->db = $db;
		$this->user = $user;
		$this->currency_table = $table_name;
		parent::__construct(
			$db,
			$user,
			'PPDE_DC',
			'CURRENCY',
			$table_name,
			array(
				'item_id'       => array('name' => 'currency_id', 'type' => 'integer'),
				'item_name'     => array('name' => 'currency_name', 'type' => 'string'),
				'item_iso_code' => array('name' => 'currency_iso_code', 'type' => 'string'),
				'item_symbol'   => array('name' => 'currency_symbol', 'type' => 'string'),
				'item_on_left'  => array('name' => 'currency_on_left', 'type' => 'boolean'),
				'item_enable'   => array('name' => 'currency_enable', 'type' => 'boolean'),
				'item_order'    => array('name' => 'currency_order', 'type' => 'integer'),
			)
		);
	}

	/**
	 * SQL Query to return the ID of selected currency
	 *
	 * @param string $iso_code Currency ISO code name
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data_exists($iso_code = '')
	{
		return 'SELECT currency_id
			FROM ' . $this->currency_table . "
			WHERE currency_iso_code = '" . $this->db->sql_escape($iso_code ? $iso_code : $this->data['currency_iso_code']) . "'";
	}

	/**
	 * Get the order number of the currency
	 *
	 * @return int Order identifier
	 * @access public
	 */
	public function get_currency_order()
	{
		return (isset($this->data['currency_order'])) ? (int) $this->data['currency_order'] : 0;
	}

	/**
	 * Get Currency status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_currency_position()
	{
		return (isset($this->data['currency_on_left'])) ? (bool) $this->data['currency_on_left'] : false;
	}

	/**
	 * Get Currency ISO code
	 *
	 * @return string ISO code name
	 * @access public
	 */
	public function get_iso_code()
	{
		return (isset($this->data['currency_iso_code'])) ? (string) $this->data['currency_iso_code'] : '';
	}

	/**
	 * Get Currency Symbol
	 *
	 * @return string Currency symbol
	 * @access public
	 */
	public function get_symbol()
	{
		return (isset($this->data['currency_symbol'])) ? (string) html_entity_decode($this->data['currency_symbol'], ENT_COMPAT | ENT_HTML5, 'UTF-8') : '';
	}

	/**
	 * Set Currency status
	 *
	 * @param bool $on_left
	 *
	 * @return bool
	 * @access public
	 */
	public function set_currency_position($on_left)
	{
		// Set the item type on our data array
		$this->data['currency_on_left'] = (bool) $on_left;

		return $this;
	}

	/**
	 * Set Currency status
	 *
	 * @param bool $enable
	 *
	 * @return bool
	 * @access public
	 */
	public function set_currency_enable($enable)
	{
		// Set the item type on our data array
		$this->data['currency_enable'] = (bool) $enable;

		return $this;
	}

	/**
	 * Set Currency ISO code name
	 *
	 * @param string $iso_code
	 *
	 * @return currency $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_iso_code($iso_code)
	{
		// Set the lang_id on our data array
		$this->data['currency_iso_code'] = (string) $iso_code;

		return $this;
	}

	/**
	 * Set Currency symbol
	 *
	 * @param string $symbol
	 *
	 * @return currency $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_symbol($symbol)
	{
		// Set the lang_id on our data array
		$this->data['currency_symbol'] = (string) htmlentities($symbol, ENT_COMPAT | ENT_HTML5, 'UTF-8');

		return $this;
	}

	/**
	 * Check if required field are set
	 *
	 * @return bool
	 * @access public
	 */
	public function check_required_field()
	{
		return empty($this->data['currency_name']) || empty($this->data['currency_iso_code']) || empty($this->data['currency_symbol']);
	}

	/**
	 * Set Currency order number
	 *
	 * @return currency $this object for chaining calls; load()->set()->save()
	 * @access protected
	 */
	protected function set_order()
	{
		$order = (int) $this->get_max_order() + 1;

		/*
		* If the data is out of range we'll throw an exception. We use 16777215 as a
		* maximum because it matches the MySQL unsigned mediumint maximum value which
		* is the lowest amongst the DBMS supported by phpBB.
		*/
		if ($order < 0 || $order > 16777215)
		{
			$this->display_warning_message('EXCEPTION_OUT_OF_BOUNDS', 'currency_order');
		}

		$this->data['currency_order'] = $order;

		return $this;
	}

	/**
	 * Get max currency order value
	 *
	 * @return int Order identifier
	 * @access private
	 */
	private function get_max_order()
	{
		$sql = 'SELECT MAX(currency_order) AS max_order
			FROM ' . $this->currency_table;
		$this->db->sql_query($sql);

		return $this->db->sql_fetchfield('max_order');
	}

	/**
	 * Returns error if the currency is enabled
	 *
	 * @return void
	 * @access protected
	 */
	protected function check_currency_enable()
	{
		if ($this->get_currency_enable())
		{
			// Return an error if the currency is enabled
			$this->display_warning_message('PPDE_DISABLE_BEFORE_DELETION');
		}
	}

	/**
	 * Get Currency status
	 *
	 * @return boolean
	 * @access public
	 */
	public function get_currency_enable()
	{
		return (isset($this->data['currency_enable'])) ? (bool) $this->data['currency_enable'] : false;
	}
}
