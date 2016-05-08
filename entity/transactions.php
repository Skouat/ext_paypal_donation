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
class transactions extends main
{
	/**
	 * Data for this entity
	 *
	 * @var array
	 *    transaction_id
	 *    txn_id
	 *    txn_type
	 *    confirmed
	 *    user_id
	 *    item_name
	 *    item_number
	 *    business
	 *    receiver_id
	 *    receiver_email
	 *    payment_status
	 *    mc_gross
	 *    mc_fee
	 *    mc_currency
	 *    settle_amount
	 *    settle_currency
	 *    net_amount
	 *    exchange_rate
	 *    payment_type
	 *    payment_date
	 *    payer_id
	 *    payer_email
	 *    payer_status
	 *    first_name
	 *    last_name
	 * @access protected
	 */
	protected $data;
	protected $extra_data;
	protected $transactions_log_table;

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
		$this->transactions_log_table = $table_name;
		parent::__construct(
			$db,
			$user,
			'PPDE_DT',
			'TRANSACTION',
			$table_name,
			array(
				'item_id'                => array('name' => 'transaction_id', 'type' => 'integer'),
				'item_receiver_id'       => array('name' => 'receiver_id', 'type' => 'string'),
				'item_receiver_email'    => array('name' => 'receiver_email', 'type' => 'string'),
				'item_residence_country' => array('name' => 'residence_country', 'type' => 'string'),
				'item_business'          => array('name' => 'business', 'type' => 'string'),
				'item_confirmed'         => array('name' => 'confirmed', 'type' => 'boolean'),
				'item_test_ipn'          => array('name' => 'test_ipn', 'type' => 'boolean'),
				'item_txn_id'            => array('name' => 'txn_id', 'type' => 'string'),
				'item_txn_type'          => array('name' => 'txn_type', 'type' => 'string'),
				'item_parent_txn_id'     => array('name' => 'parent_txn_id', 'type' => 'string'),
				'item_payer_email'       => array('name' => 'payer_email', 'type' => 'string'),
				'item_payer_id'          => array('name' => 'payer_id', 'type' => 'string'),
				'item_payer_status'      => array('name' => 'payer_status', 'type' => 'string'),
				'item_first_name'        => array('name' => 'first_name', 'type' => 'string'),
				'item_last_name'         => array('name' => 'last_name', 'type' => 'string'),
				'item_user_id'           => array('name' => 'user_id', 'type' => 'integer'),
				'item_custom'            => array('name' => 'custom', 'type' => 'string'),
				'item_item_name'         => array('name' => 'item_name', 'type' => 'string'),
				'item_item_number'       => array('name' => 'item_number', 'type' => 'string'),
				'item_mc_currency'       => array('name' => 'mc_currency', 'type' => 'string'),
				'item_mc_fee'            => array('name' => 'mc_fee', 'type' => 'float'),
				'item_mc_gross'          => array('name' => 'mc_gross', 'type' => 'float'),
				'item_net_amount'        => array('name' => 'net_amount', 'type' => 'float'),
				'item_payment_date'      => array('name' => 'payment_date', 'type' => 'integer'),
				'item_payment_status'    => array('name' => 'payment_status', 'type' => 'string'),
				'item_payment_type'      => array('name' => 'payment_type', 'type' => 'string'),
				'item_settle_amount'     => array('name' => 'settle_amount', 'type' => 'float'),
				'item_settle_currency'   => array('name' => 'settle_currency', 'type' => 'string'),
				'item_exchange_rate'     => array('name' => 'exchange_rate', 'type' => 'string'),
			)
		);
	}

	/**
	 * Checks if the txn_id exists for this transaction
	 *
	 * @return int $this->data['transaction_id'] Transaction identifier; 0 if the transaction doesn't exist
	 * @access public
	 */
	public function transaction_exists()
	{
		$sql = 'SELECT transaction_id
			FROM ' . $this->transactions_log_table . "
			WHERE txn_id = '" . $this->db->sql_escape($this->data['txn_id']) . "'";
		$this->db->sql_query($sql);

		return $this->db->sql_fetchfield('transaction_id');
	}

	/**
	 * Get PayPal transaction id
	 *
	 * @return string
	 * @access public
	 */
	public function get_txn_id()
	{
		return (isset($this->data['txn_id'])) ? (string) $this->data['txn_id'] : '';
	}

	/**
	 * Get PayPal receiver ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_receiver_id()
	{
		return (isset($this->data['receiver_id'])) ? (string) $this->data['receiver_id'] : '';
	}

	/**
	 * Get PayPal receiver e-mail
	 *
	 * @return string
	 * @access public
	 */
	public function get_receiver_email()
	{
		return (isset($this->data['receiver_email'])) ? (string) $this->data['receiver_email'] : '';
	}

	/**
	 * Get PayPal receiver ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_residence_country()
	{
		return (isset($this->data['residence_country'])) ? (string) $this->data['residence_country'] : '';
	}

	/**
	 * Get PayPal business (same as receiver ID or receiver_email)
	 *
	 * @return string
	 * @access public
	 */
	public function get_business()
	{
		return (isset($this->data['business'])) ? (string) $this->data['business'] : '';
	}

	/**
	 * Get PayPal transaction status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_confirmed()
	{
		return (isset($this->data['confirmed'])) ? (bool) $this->data['confirmed'] : false;
	}

	/**
	 * Get Test IPN status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_test_ipn()
	{
		return (isset($this->data['test_ipn'])) ? (bool) $this->data['test_ipn'] : false;
	}

	/**
	 * Get PayPal transaction type
	 *
	 * @return string
	 * @access public
	 */
	public function get_txn_type()
	{
		return (isset($this->data['txn_type'])) ? (string) $this->data['txn_type'] : '';
	}

	/**
	 * Get PayPal parent transaction ID (in case of refund)
	 *
	 * @return string
	 * @access public
	 */
	public function get_parent_txn_id()
	{
		return (isset($this->data['parent_txn_id'])) ? (string) $this->data['parent_txn_id'] : '';
	}

	/**
	 * Get PayPal payer e-mail
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_email()
	{
		return (isset($this->data['payer_email'])) ? (string) $this->data['payer_email'] : '';
	}

	/**
	 * Get PayPal payer account ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_id()
	{
		return (isset($this->data['payer_id'])) ? (string) $this->data['payer_id'] : '';
	}

	/**
	 * Get PayPal payer Status (such as unverified/verified)
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_status()
	{
		return (isset($this->data['payer_status'])) ? (string) $this->data['payer_status'] : '';
	}

	/**
	 * Get PayPal payer first name
	 *
	 * @return string
	 * @access public
	 */
	public function get_first_name()
	{
		return (isset($this->data['first_name'])) ? (string) $this->data['first_name'] : '';
	}

	/**
	 * Get PayPal payer last name
	 *
	 * @return string
	 * @access public
	 */
	public function get_last_name()
	{
		return (isset($this->data['last_name'])) ? (string) $this->data['last_name'] : '';
	}

	/**
	 * Get member user_id
	 *
	 * @return integer
	 * @access public
	 */
	public function get_user_id()
	{
		return (isset($this->data['user_id'])) ? (integer) $this->data['user_id'] : 0;
	}

	/**
	 * Get member username
	 *
	 * @return string
	 * @access public
	 */
	public function get_username()
	{
		return (isset($this->extra_data['username'])) ? (string) $this->extra_data['username'] : '';
	}

	/**
	 * Get PayPal payer last name
	 *
	 * @return string
	 * @access public
	 */
	public function get_custom()
	{
		return (isset($this->data['custom'])) ? (string) $this->data['custom'] : '';
	}

	/**
	 * Get PayPal item name
	 *
	 * @return string
	 * @access public
	 */
	public function get_item_name()
	{
		return (isset($this->data['item_name'])) ? (string) $this->data['item_name'] : '';
	}

	/**
	 * Get PayPal item number (contains user_id)
	 *
	 * @return string
	 * @access public
	 */
	public function get_item_number()
	{
		return (isset($this->data['item_number'])) ? (string) $this->data['item_number'] : '';
	}

	/**
	 * Get PayPal currency name (eg: USD, EUR, etc.)
	 *
	 * @return string
	 * @access public
	 */
	public function get_mc_currency()
	{
		return (isset($this->data['mc_currency'])) ? (string) $this->data['mc_currency'] : '';
	}

	/**
	 * Get PayPal fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_mc_fee()
	{
		return (isset($this->data['mc_fee'])) ? (float) $this->data['mc_fee'] : 0;
	}

	/**
	 * Get PayPal amount
	 * This is the amount of donation received before fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_mc_gross()
	{
		return (isset($this->data['mc_gross'])) ? (float) $this->data['mc_gross'] : 0;
	}

	/**
	 * Get Net amount
	 * This is the amount of donation received after fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_net_amount()
	{
		return (isset($this->data['net_amount'])) ? (float) $this->data['net_amount'] : 0;
	}

	/**
	 * Get PayPal payment date
	 *
	 * @return integer
	 * @access public
	 */
	public function get_payment_date()
	{
		return (isset($this->data['payment_date'])) ? (int) $this->data['payment_date'] : '';
	}

	/**
	 * Get PayPal payment status
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_status()
	{
		return (isset($this->data['payment_status'])) ? (string) $this->data['payment_status'] : '';
	}

	/**
	 * Get PayPal payment type
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_type()
	{
		return (isset($this->data['payment_type'])) ? (string) $this->data['payment_type'] : '';
	}

	/**
	 * Get PayPal settle amount
	 * This is in case or the currency of the Payer is not in the same currency of the Receiver
	 *
	 * @return float
	 * @access public
	 */
	public function get_settle_amount()
	{
		return (isset($this->data['settle_amount'])) ? (float) $this->data['settle_amount'] : 0;
	}

	/**
	 * Get PayPal settle currency
	 *
	 * @return string
	 * @access public
	 */
	public function get_settle_currency()
	{
		return (isset($this->data['settle_currency'])) ? (string) $this->data['settle_currency'] : '';
	}

	/**
	 * Get PayPal exchange rate
	 * This is when the donation don’t use the same currency defined by the receiver
	 *
	 * @return string
	 * @access public
	 */
	public function get_exchange_rate()
	{
		return (isset($this->data['exchange_rate'])) ? (string) $this->data['exchange_rate'] : '';
	}

	/**
	 * Set PayPal transaction id
	 *
	 * @param string $txn_id
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_txn_id($txn_id)
	{
		$this->data['txn_id'] = (string) $txn_id;

		return $this;
	}

	/**
	 * Set PayPal receiver ID
	 *
	 * @param string $receiver_id
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_receiver_id($receiver_id)
	{
		$this->data['receiver_id'] = (string) $receiver_id;

		return $this;
	}

	/**
	 * Set PayPal receiver e-mail
	 *
	 * @param string $receiver_email
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_receiver_email($receiver_email)
	{
		$this->data['receiver_email'] = (string) $receiver_email;

		return $this;
	}

	/**
	 * Set PayPal receiver ID
	 *
	 * @param string $residence_country
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_residence_country($residence_country)
	{
		$this->data['residence_country'] = (string) $residence_country;

		return $this;
	}

	/**
	 * Set PayPal business (same as receiver ID or receiver_email)
	 *
	 * @param string $business
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_business($business)
	{
		$this->data['business'] = (string) $business;

		return $this;
	}

	/**
	 * Set PayPal transaction status
	 *
	 * @param bool $confirmed
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_confirmed($confirmed)
	{
		$this->data['confirmed'] = (bool) $confirmed;

		return $this;
	}

	/**
	 * Set Test IPN status
	 *
	 * @param bool $test_ipn
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_test_ipn($test_ipn)
	{
		$this->data['test_ipn'] = (bool) $test_ipn;

		return $this;
	}

	/**
	 * Set PayPal transaction type
	 *
	 * @param string $txn_type
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_txn_type($txn_type)
	{
		$this->data['txn_type'] = (string) $txn_type;

		return $this;
	}

	/**
	 * Set PayPal parent transaction ID (in case of refund)
	 *
	 * @param string $parent_txn_id
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_parent_txn_id($parent_txn_id)
	{
		$this->data['parent_txn_id'] = (string) $parent_txn_id;

		return $this;
	}

	/**
	 * Set PayPal payer e-mail
	 *
	 * @param string $payer_email
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payer_email($payer_email)
	{
		$this->data['payer_email'] = (string) $payer_email;

		return $this;
	}

	/**
	 * Set PayPal payer account ID
	 *
	 * @param string $payer_id
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payer_id($payer_id)
	{
		$this->data['payer_id'] = (string) $payer_id;

		return $this;
	}

	/**
	 * Set PayPal payer Status (such as unverified/verified)
	 *
	 * @param string $payer_status
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payer_status($payer_status)
	{
		$this->data['payer_status'] = (string) $payer_status;

		return $this;
	}

	/**
	 * Set PayPal payer first name
	 *
	 * @param string $first_name
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_first_name($first_name)
	{
		$this->data['first_name'] = (string) $first_name;

		return $this;
	}

	/**
	 * Set PayPal payer last name
	 *
	 * @param string $last_name
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_last_name($last_name)
	{
		$this->data['last_name'] = (string) $last_name;

		return $this;
	}

	/**
	 * Set member user_id
	 *
	 * @param integer $user_id
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_user_id($user_id)
	{
		$this->data['user_id'] = (integer) $user_id;

		return $this;
	}

	/**
	 * Set member username
	 *
	 * @param string $username
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_username($username)
	{
		$this->extra_data['username'] = (string) $username;

		return $this;
	}

	/**
	 * Set PayPal payer last name
	 *
	 * @param string $custom
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_custom($custom)
	{
		$this->data['custom'] = (string) $custom;

		return $this;
	}

	/**
	 * Set PayPal item name
	 *
	 * @param string $item_name
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_item_name($item_name)
	{
		$this->data['item_name'] = (string) $item_name;

		return $this;
	}

	/**
	 * Set PayPal item number (contains user_id)
	 *
	 * @param string $item_number
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_item_number($item_number)
	{
		$this->data['item_number'] = (string) $item_number;

		return $this;
	}

	/**
	 * Set PayPal currency name (eg: USD, EUR, etc.)
	 *
	 * @param string $mc_currency
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_mc_currency($mc_currency)
	{
		$this->data['mc_currency'] = (string) $mc_currency;

		return $this;
	}

	/**
	 * Set PayPal fees
	 *
	 * @param float $mc_fee
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_mc_fee($mc_fee)
	{
		$this->data['mc_fee'] = (float) $mc_fee;

		return $this;
	}

	/**
	 * Set PayPal amount
	 * This is the amount of donation received before fees
	 *
	 * @param float $mc_gross
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_mc_gross($mc_gross)
	{
		$this->data['mc_gross'] = (float) $mc_gross;

		return $this;
	}

	/**
	 * Set Net amount
	 * This is the amount of donation received after fees
	 *
	 * @param float $net_amount
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_net_amount($net_amount)
	{
		$this->data['net_amount'] = (float) $net_amount;

		return $this;
	}

	/**
	 * Set PayPal payment date
	 *
	 * @param integer $payment_date
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payment_date($payment_date)
	{
		$this->data['payment_date'] = (int) $payment_date;

		return $this;
	}

	/**
	 * Set PayPal payment status
	 *
	 * @param string $payment_status
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payment_status($payment_status)
	{
		$this->data['payment_status'] = (string) $payment_status;

		return $this;
	}

	/**
	 * Set PayPal payment type
	 *
	 * @param string $payment_type
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payment_type($payment_type)
	{
		$this->data['payment_type'] = (string) $payment_type;

		return $this;
	}

	/**
	 * Set PayPal settle amount
	 * This is in case or the currency of the Payer is not in the same currency of the Receiver
	 *
	 * @param float $settle_amount
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_settle_amount($settle_amount)
	{
		$this->data['settle_amount'] = (float) $settle_amount;

		return $this;
	}

	/**
	 * Set PayPal settle currency
	 *
	 * @param string $settle_currency
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_settle_currency($settle_currency)
	{
		$this->data['settle_currency'] = (string) $settle_currency;

		return $this;
	}

	/**
	 * Set PayPal exchange rate
	 * This is when the donation don’t use the same currency defined by the receiver
	 *
	 * @param string $exchange_rate
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_exchange_rate($exchange_rate)
	{
		$this->data['exchange_rate'] = (string) $exchange_rate;

		return $this;
	}
}
