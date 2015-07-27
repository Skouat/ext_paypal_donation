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
 * Entity for a currency
 */
class currency extends main implements currency_interface
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

	protected $db;
	protected $user;
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
			'CURRENCY',
			$table_name,
			array(
				'item_id'       => array(
					'name' => 'currency_id',
					'type' => 'integer'),
				'item_name'     => array(
					'name' => 'currency_name',
					'type' => 'string'),
				'item_iso_code' => array(
					'name' => 'currency_iso_code',
					'type' => 'string'),
				'item_symbol'   => array(
					'name' => 'currency_symbol',
					'type' => 'string'),
				'item_enable'   => array(
					'name' => 'currency_enable',
					'type' => 'boolean'),
				'item_order'    => array(
					'name' => 'currency_order',
					'type' => 'integer'),
			)
		);
	}

	/**
	 * Check the currency_id exist from the database for this currency
	 *
	 * @return int $this->data['currency_id'] Currency identifier; 0 if the currency doesn't exist
	 * @access public
	 */
	public function currency_exists()
	{
		$sql = 'SELECT currency_id
			FROM ' . $this->currency_table . "
			WHERE currency_iso_code = '" . $this->db->sql_escape($this->data['currency_iso_code']) . "'
			AND currency_symbol = '" . $this->db->sql_escape($this->data['currency_symbol']) . "'";
		$this->db->sql_query($sql);

		return $this->db->sql_fetchfield('currency_id');
	}

	/**
	 * Insert the item for the first time
	 *
	 * Will throw an exception if the item was already inserted (call save() instead)
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function insert()
	{
		if (!empty($this->data['currency_id']))
		{
			// The page already exists
			$this->display_error_message('PPDE_CURRENCY_EXIST');
		}

		// Make extra sure there is no currency_id set
		unset($this->data['currency_id']);

		// Set the Order value before insert new data
		$this->set_order();

		// Insert data to the database
		$sql = 'INSERT INTO ' . $this->currency_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the currency_id using the id created by the SQL insert
		$this->data['currency_id'] = (int) $this->db->sql_nextid();

		return $this;
	}

	/**
	 * Set Currency order number
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access private
	 */
	private function set_order()
	{
		$this->data['currency_order'] = (int) $this->get_max_order() + 1;

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
	 * Save the current settings to the database
	 *
	 * This must be called before closing or any changes will not be saved!
	 * If adding a page (saving for the first time), you must call insert() or an exception will be thrown
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function save()
	{
		if (empty($this->data['currency_name']) || empty($this->data['currency_iso_code']) || empty($this->data['currency_symbol']))
		{
			// The currency field missing
			$this->display_error_message('PPDE_NO_CURRENCY');
		}

		$sql = 'UPDATE ' . $this->currency_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE currency_id = ' . $this->get_id();
		$this->db->sql_query($sql);

		return $this;
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
	 * Set Currency symbol
	 *
	 * @param string $symbol
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_symbol($symbol)
	{
		// Set the lang_id on our data array
		$this->data['currency_symbol'] = (string) $symbol;

		return $this;
	}

	/**
	 * Get Currency Symbol
	 *
	 * @return string Currency symbol
	 * @access public
	 */
	public function get_symbol()
	{
		return (isset($this->data['currency_symbol'])) ? (string) $this->data['currency_symbol'] : '';
	}

	/**
	 * Set Currency ISO code name
	 *
	 * @param string $iso_code
	 *
	 * @return currency_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_iso_code($iso_code)
	{
		// Set the lang_id on our data array
		$this->data['currency_iso_code'] = (string) $iso_code;

		return $this;
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
	 * Get the order number of the currency
	 *
	 * @return int Order identifier
	 * @access public
	 */
	public function get_currency_order()
	{
		return (isset($this->data['currency_order'])) ? (int) $this->data['currency_order'] : 0;
	}
}
