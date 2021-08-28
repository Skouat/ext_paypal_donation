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
class transactions extends main
{
	/**
	 * Data for this entity
	 *
	 * @var array
	 *    business
	 *    confirmed
	 *    custom
	 *    exchange_rate
	 *    first_name
	 *    item_name
	 *    item_number
	 *    last_name
	 *    mc_currency
	 *    mc_fee
	 *    mc_gross
	 *    memo
	 *    net_amount
	 *    parent_txn_id
	 *    payer_email
	 *    payer_id
	 *    payer_status
	 *    payment_date
	 *    payment_status
	 *    payment_type
	 *    receiver_email
	 *    receiver_id
	 *    residence_country
	 *    settle_amount
	 *    settle_currency
	 *    transaction_id
	 *    test_ipn
	 *    txn_errors
	 *    txn_id
	 *    txn_type
	 *    user_id
	 * @access protected
	 */
	protected $data;
	protected $extra_data;
	protected $transactions_log_table;

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
		$this->transactions_log_table = $table_name;
		parent::__construct(
			$db,
			$language,
			'PPDE_DT',
			'TRANSACTION',
			$table_name,
			[
				'item_id'                  => ['name' => 'transaction_id', 'type' => 'integer'],
				'item_business'            => ['name' => 'business', 'type' => 'string'],
				'item_confirmed'           => ['name' => 'confirmed', 'type' => 'boolean'],
				'item_custom'              => ['name' => 'custom', 'type' => 'string'],
				'item_exchange_rate'       => ['name' => 'exchange_rate', 'type' => 'string'],
				'item_first_name'          => ['name' => 'first_name', 'type' => 'string'],
				'item_item_name'           => ['name' => 'item_name', 'type' => 'string'],
				'item_item_number'         => ['name' => 'item_number', 'type' => 'string'],
				'item_last_name'           => ['name' => 'last_name', 'type' => 'string'],
				'item_mc_currency'         => ['name' => 'mc_currency', 'type' => 'string'],
				'item_mc_fee'              => ['name' => 'mc_fee', 'type' => 'float'],
				'item_mc_gross'            => ['name' => 'mc_gross', 'type' => 'float'],
				'item_memo'                => ['name' => 'memo', 'type' => 'string'],
				'item_net_amount'          => ['name' => 'net_amount', 'type' => 'float'],
				'item_parent_txn_id'       => ['name' => 'parent_txn_id', 'type' => 'string'],
				'item_payer_email'         => ['name' => 'payer_email', 'type' => 'string'],
				'item_payer_id'            => ['name' => 'payer_id', 'type' => 'string'],
				'item_payer_status'        => ['name' => 'payer_status', 'type' => 'string'],
				'item_payment_date'        => ['name' => 'payment_date', 'type' => 'integer'],
				'item_payment_status'      => ['name' => 'payment_status', 'type' => 'string'],
				'item_payment_type'        => ['name' => 'payment_type', 'type' => 'string'],
				'item_receiver_email'      => ['name' => 'receiver_email', 'type' => 'string'],
				'item_receiver_id'         => ['name' => 'receiver_id', 'type' => 'string'],
				'item_residence_country'   => ['name' => 'residence_country', 'type' => 'string'],
				'item_settle_amount'       => ['name' => 'settle_amount', 'type' => 'float'],
				'item_settle_currency'     => ['name' => 'settle_currency', 'type' => 'string'],
				'item_test_ipn'            => ['name' => 'test_ipn', 'type' => 'boolean'],
				'item_txn_errors'          => ['name' => 'txn_errors', 'type' => 'string'],
				'item_txn_errors_approved' => ['name' => 'txn_errors_approved', 'type' => 'string'],
				'item_txn_id'              => ['name' => 'txn_id', 'type' => 'string'],
				'item_txn_type'            => ['name' => 'txn_type', 'type' => 'string'],
				'item_user_id'             => ['name' => 'user_id', 'type' => 'integer'],
			]
		);
	}

	/**
	 * Checks if the txn_id exists for this transaction
	 *
	 * @return int $this->data['transaction_id'] Transaction identifier; 0 if the transaction doesn't exist
	 * @access public
	 */
	public function transaction_exists(): int
	{
		$sql = 'SELECT transaction_id
			FROM ' . $this->transactions_log_table . "
			WHERE txn_id = '" . $this->db->sql_escape($this->data['txn_id']) . "'";
		$result = $this->db->sql_query($sql);
		$field = (int) $this->db->sql_fetchfield('transaction_id');
		$this->db->sql_freeresult($result);

		return $field;
	}

	/**
	 * Get PayPal transaction id
	 *
	 * @return string
	 * @access public
	 */
	public function get_txn_id(): string
	{
		return (string) ($this->data['txn_id'] ?? '');
	}

	/**
	 * Get PayPal receiver ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_receiver_id(): string
	{
		return (string) ($this->data['receiver_id'] ?? '');
	}

	/**
	 * Get PayPal receiver e-mail
	 *
	 * @return string
	 * @access public
	 */
	public function get_receiver_email(): string
	{
		return (string) ($this->data['receiver_email'] ?? '');
	}

	/**
	 * Get PayPal receiver ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_residence_country(): string
	{
		return (string) ($this->data['residence_country'] ?? '');
	}

	/**
	 * Get PayPal business (same as receiver ID or receiver_email)
	 *
	 * @return string
	 * @access public
	 */
	public function get_business(): string
	{
		return (string) ($this->data['business'] ?? '');
	}

	/**
	 * Get PayPal transaction status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_confirmed(): bool
	{
		return (bool) ($this->data['confirmed'] ?? false);
	}

	/**
	 * Get Test IPN status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_test_ipn(): bool
	{
		return (bool) ($this->data['test_ipn'] ?? false);
	}

	/**
	 * Get PayPal transaction errors
	 *
	 * @return string
	 * @access public
	 */
	public function get_txn_errors(): string
	{
		return (string) ($this->data['txn_errors'] ?? '');
	}

	/**
	 * Get PayPal transaction errors approval status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_txn_errors_approved(): bool
	{
		return (bool) ($this->data['txn_errors_approved'] ?? '');
	}

	/**
	 * Get PayPal transaction type
	 *
	 * @return string
	 * @access public
	 */
	public function get_txn_type(): string
	{
		return (string) ($this->data['txn_type'] ?? '');
	}

	/**
	 * Get PayPal parent transaction ID (in case of refund)
	 *
	 * @return string
	 * @access public
	 */
	public function get_parent_txn_id(): string
	{
		return (string) ($this->data['parent_txn_id'] ?? '');
	}

	/**
	 * Get PayPal payer e-mail
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_email(): string
	{
		return (string) ($this->data['payer_email'] ?? '');
	}

	/**
	 * Get PayPal payer account ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_id(): string
	{
		return (string) ($this->data['payer_id'] ?? '');
	}

	/**
	 * Get PayPal payer Status (such as unverified/verified)
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_status(): string
	{
		return (string) ($this->data['payer_status'] ?? '');
	}

	/**
	 * Get PayPal payer first name
	 *
	 * @return string
	 * @access public
	 */
	public function get_first_name(): string
	{
		return (string) ($this->data['first_name'] ?? '');
	}

	/**
	 * Get PayPal payer last name
	 *
	 * @return string
	 * @access public
	 */
	public function get_last_name(): string
	{
		return (string) ($this->data['last_name'] ?? '');
	}

	/**
	 * Get member user_id
	 *
	 * @return int
	 * @access public
	 */
	public function get_user_id(): int
	{
		return (int) ($this->data['user_id'] ?? 0);
	}

	/**
	 * Get member username
	 *
	 * @return string
	 * @access public
	 */
	public function get_username(): string
	{
		return (string) ($this->extra_data['username'] ?? '');
	}

	/**
	 * Get PayPal payer last name
	 *
	 * @return string
	 * @access public
	 */
	public function get_custom(): string
	{
		return (string) ($this->data['custom'] ?? '');
	}

	/**
	 * Get PayPal item name
	 *
	 * @return string
	 * @access public
	 */
	public function get_item_name(): string
	{
		return (string) ($this->data['item_name'] ?? '');
	}

	/**
	 * Get PayPal item number (contains user_id)
	 *
	 * @return string
	 * @access public
	 */
	public function get_item_number(): string
	{
		return (string) ($this->data['item_number'] ?? '');
	}

	/**
	 * Get PayPal currency name (eg: USD, EUR, etc.)
	 *
	 * @return string
	 * @access public
	 */
	public function get_mc_currency(): string
	{
		return (string) ($this->data['mc_currency'] ?? '');
	}

	/**
	 * Get PayPal fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_mc_fee(): float
	{
		return (float) ($this->data['mc_fee'] ?? 0);
	}

	/**
	 * Get PayPal amount
	 * This is the amount of donation received before fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_mc_gross(): float
	{
		return (float) ($this->data['mc_gross'] ?? 0);
	}

	/**
	 * Get Net amount
	 * This is the amount of donation received after fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_net_amount(): float
	{
		return (float) ($this->data['net_amount'] ?? 0);
	}

	/**
	 * Get PayPal payment date
	 *
	 * @return int
	 * @access public
	 */
	public function get_payment_date(): int
	{
		return (int) ($this->data['payment_date'] ?? 0);
	}

	/**
	 * Get PayPal payment status
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_status(): string
	{
		return (string) ($this->data['payment_status'] ?? '');
	}

	/**
	 * Get PayPal payment type
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_type(): string
	{
		return (string) ($this->data['payment_type'] ?? '');
	}

	/**
	 * Get PayPal memo
	 *
	 * @return string
	 * @access public
	 */
	public function get_memo(): string
	{
		return (string) ($this->data['memo'] ?? '');
	}

	/**
	 * Get PayPal settle amount
	 * This is in case or the currency of the Payer is not in the same currency of the Receiver
	 *
	 * @return float
	 * @access public
	 */
	public function get_settle_amount(): float
	{
		return (float) ($this->data['settle_amount'] ?? 0);
	}

	/**
	 * Get PayPal settle currency
	 *
	 * @return string
	 * @access public
	 */
	public function get_settle_currency(): string
	{
		return (string) ($this->data['settle_currency'] ?? '');
	}

	/**
	 * Get PayPal exchange rate
	 * This is when the donation don’t use the same currency defined by the receiver
	 *
	 * @return string
	 * @access public
	 */
	public function get_exchange_rate(): string
	{
		return (string) ($this->data['exchange_rate'] ?? '');
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
	 * Set PayPal transaction errors
	 *
	 * @param string $txn_errors
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_txn_errors($txn_errors)
	{
		$this->data['txn_errors'] = (string) $txn_errors;

		return $this;
	}

	/**
	 * Set PayPal transaction errors approval status
	 *
	 * @param bool $txn_errors_approved
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_txn_errors_approved($txn_errors_approved)
	{
		$this->data['txn_errors_approved'] = (bool) $txn_errors_approved;

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
	 * @param int $user_id
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
	 * @param int $payment_date
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
	 * Set PayPal memo
	 *
	 * @param string $memo
	 *
	 * @return transactions $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_memo($memo)
	{
		$this->data['memo'] = (string) $memo;

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
