<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

use phpbb\config\config;
use phpbb\language\language;
use skouat\ppde\operators\transactions;

class core_actions
{
	public $notification;
	protected $config;
	protected $is_ipn_test;
	protected $language;
	protected $ppde_operator_transaction;
	protected $suffix_ipn;
	protected $transaction_data;

	/**
	 * Constructor
	 *
	 * @param config                         $config                    Config object
	 * @param language                       $language                  Language user object
	 * @param \skouat\ppde\notification\core $notification              PPDE Notification object
	 * @param transactions                   $ppde_operator_transaction Operator object
	 *
	 * @access public
	 */
	public function __construct(config $config, language $language, \skouat\ppde\notification\core $notification, transactions $ppde_operator_transaction)
	{
		$this->config = $config;
		$this->language = $language;
		$this->notification = $notification;
		$this->ppde_operator_transaction = $ppde_operator_transaction;
	}

	/**
	 * Set Transaction Data array
	 *
	 * @param array $transaction_data Array of the donation transaction.
	 *
	 * @return void
	 * @access public
	 */
	public function set_transaction_data($transaction_data)
	{
		$this->transaction_data = $transaction_data;
	}

	/**
	 * Sets properties related to ipn tests
	 *
	 * @param bool $ipn_test
	 *
	 * @return void
	 * @access public
	 */
	public function set_ipn_test_properties($ipn_test)
	{
		$this->set_ipn_test($ipn_test);
		$this->set_suffix_ipn($this->is_ipn_test);
	}

	/**
	 * Sets the property $this->is_ipn_test
	 *
	 * @param bool $ipn_test
	 *
	 * @return void
	 * @access private
	 */
	private function set_ipn_test($ipn_test)
	{
		$this->is_ipn_test = $ipn_test ? (bool) $ipn_test : false;
	}

	/**
	 * Sets the property $this->suffix_ipn
	 *
	 * @param bool $is_ipn_test
	 *
	 * @return void
	 * @access private
	 */
	private function set_suffix_ipn($is_ipn_test)
	{
		$this->suffix_ipn = $is_ipn_test ? '_ipn' : '';
	}

	/**
	 * @return string
	 */
	public function get_suffix_ipn()
	{
		return ($this->get_ipn_test()) ? $this->suffix_ipn : '';
	}

	/**
	 * @return boolean
	 */
	public function get_ipn_test()
	{
		return ($this->is_ipn_test) ? (bool) $this->is_ipn_test : false;
	}

	/**
	 * Updates the amount of donation raised
	 *
	 * @return void
	 * @access public
	 */
	public function update_raised_amount()
	{
		$this->config->set('ppde_raised' . $this->suffix_ipn, (float) $this->config['ppde_raised' . $this->suffix_ipn] + (float) $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']), true);
	}

	/**
	 * Returns the net amount of a donation
	 *
	 * @param float  $amount
	 * @param float  $fee
	 * @param string $dec_point
	 * @param string $thousands_sep
	 *
	 * @return string
	 * @access public
	 */
	public function net_amount($amount, $fee, $dec_point = '.', $thousands_sep = '')
	{
		return number_format((float) $amount - (float) $fee, 2, $dec_point, $thousands_sep);
	}

	/**
	 * Updates the Overview module statistics
	 *
	 * @return void
	 * @access public
	 */
	public function update_overview_stats()
	{
		$this->config->set('ppde_anonymous_donors_count' . $this->suffix_ipn, $this->get_count_result('ppde_anonymous_donors_count' . $this->suffix_ipn));
		$this->config->set('ppde_known_donors_count' . $this->suffix_ipn, $this->get_count_result('ppde_known_donors_count' . $this->suffix_ipn), true);
		$this->config->set('ppde_transactions_count' . $this->suffix_ipn, $this->get_count_result('ppde_transactions_count' . $this->suffix_ipn), true);
	}

	/**
	 * Returns count result for updating stats
	 *
	 * @param string $config_name
	 *
	 * @return int
	 * @access private
	 */
	private function get_count_result($config_name)
	{
		if (!$this->config->offsetExists($config_name))
		{
			trigger_error($this->language->lang('EXCEPTION_INVALID_CONFIG_NAME', $config_name), E_USER_WARNING);
		}

		return $this->ppde_operator_transaction->sql_query_count_result($config_name, $this->is_ipn_test);
	}
}
