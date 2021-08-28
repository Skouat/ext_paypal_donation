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

/**
 * @property driver_interface db                 phpBB Database object
 * @property language         language           phpBB Language object
 * @property string           lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property string           lang_key_suffix    Suffix for the messages thrown by exceptions
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
	 * @param driver_interface $db         Database object
	 * @param language         $language   Language object
	 * @param string           $table_name Name of the table used to store data
	 *
	 * @access public
	 */
	public function __construct(driver_interface $db, language $language, $table_name)
	{
		$this->currency_table = $table_name;
		parent::__construct(
			$db,
			$language,
			'PPDE_DC',
			'CURRENCY',
			$table_name,
			[
				'item_id'       => ['name' => 'currency_id', 'type' => 'integer'],
				'item_name'     => ['name' => 'currency_name', 'type' => 'string'],
				'item_iso_code' => ['name' => 'currency_iso_code', 'type' => 'string'],
				'item_symbol'   => ['name' => 'currency_symbol', 'type' => 'string'],
				'item_on_left'  => ['name' => 'currency_on_left', 'type' => 'boolean'],
				'item_enable'   => ['name' => 'currency_enable', 'type' => 'boolean'],
				'item_order'    => ['name' => 'currency_order', 'type' => 'integer'],
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_sql_data_exists($iso_code = ''): string
	{
		return 'SELECT currency_id
			FROM ' . $this->currency_table . "
			WHERE currency_iso_code = '" . $this->db->sql_escape($iso_code ?: $this->data['currency_iso_code']) . "'";
	}

	/**
	 * Get the order number of the currency
	 *
	 * @return int Order identifier
	 * @access public
	 */
	public function get_currency_order(): int
	{
		return (int) ($this->data['currency_order'] ?? 0);
	}

	/**
	 * Get Currency status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_currency_position(): bool
	{
		return (bool) ($this->data['currency_on_left'] ?? false);
	}

	/**
	 * Get Currency ISO code
	 *
	 * @return string ISO code name
	 * @access public
	 */
	public function get_iso_code(): string
	{
		return (string) ($this->data['currency_iso_code'] ?? '');
	}

	/**
	 * Get Currency Symbol
	 *
	 * @return string Currency symbol
	 * @access public
	 */
	public function get_symbol(): string
	{
		return (isset($this->data['currency_symbol'])) ? html_entity_decode($this->data['currency_symbol'], ENT_COMPAT | ENT_HTML5, 'UTF-8') : '';
	}

	/**
	 * Set Currency status
	 *
	 * @param bool $on_left
	 *
	 * @return currency $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_currency_position($on_left): currency
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
	 * @return currency $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_currency_enable($enable): currency
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
	public function set_iso_code($iso_code): currency
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
	public function set_symbol($symbol): currency
	{
		// Set the lang_id on our data array
		$this->data['currency_symbol'] = htmlentities($symbol, ENT_COMPAT | ENT_HTML5, 'UTF-8');

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_required_field(): bool
	{
		return empty($this->data['currency_name']) || empty($this->data['currency_iso_code']) || empty($this->data['currency_symbol']);
	}

	/**
	 * Set Currency order number
	 *
	 * @return currency $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_order()
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
	private function get_max_order(): int
	{
		$sql = 'SELECT MAX(currency_order) AS max_order
			FROM ' . $this->currency_table;
		$result = $this->db->sql_query($sql);
		$field = $this->db->sql_fetchfield('max_order');
		$this->db->sql_freeresult($result);

		return $field;
	}

	/**
	 * Returns error if the currency is enabled
	 *
	 * @return void
	 * @access protected
	 */
	protected function check_currency_enable(): void
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
	public function get_currency_enable(): bool
	{
		return (bool) ($this->data['currency_enable'] ?? false);
	}
}
