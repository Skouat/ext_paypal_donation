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

class core_actions
{
	public $notification;
	protected $config;
	protected $transaction_data;

	/**
	 * Constructor
	 *
	 * @param config                         $config       Config object
	 * @param \skouat\ppde\notification\core $notification PPDE Notification object
	 *
	 * @access public
	 */
	public function __construct(config $config, \skouat\ppde\notification\core $notification)
	{
		$this->config = $config;
		$this->notification = $notification;
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
	 * Updates the amount of donation raised
	 *
	 * @param string $ipn_suffix Should be '_ipn' or an empty string.
	 *
	 * @return void
	 * @access public
	 */
	public function update_raised_amount($ipn_suffix = '')
	{
		$this->config->set('ppde_raised' . $ipn_suffix, (float) $this->config['ppde_raised' . $ipn_suffix] + (float) $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']), true);
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
}
