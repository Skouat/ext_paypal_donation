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
 * Interface for a transaction log
 *
 * This describes all of the methods we'll have for a single transaction
 */
interface transactions_interface
{
	/**
	 * Check the txn_id exist from the database for this transaction
	 *
	 * @return int $this->data['txn_id'] Transaction identifier; 0 if the transaction doesn't exist
	 * @access public
	 */
	public function transaction_exists();

	/**
	 * Get PayPal transaction id
	 *
	 * @return string
	 * @access public
	 */
	public function get_txn_id();

	/**
	 * Get PayPal receiver ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_receiver_id();

	/**
	 * Get PayPal receiver e-mail
	 *
	 * @return string
	 * @access public
	 */
	public function get_receiver_email();

	/**
	 * Get PayPal receiver ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_residence_country();

	/**
	 * Get PayPal business (same as receiver ID or receiver_email)
	 *
	 * @return string
	 * @access public
	 */
	public function get_business();

	/**
	 * Get PayPal transaction status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_confirmed();

	/**
	 * Get Test IPN status
	 *
	 * @return bool
	 * @access public
	 */
	public function get_test_ipn();

	/**
	 * Get PayPal transaction type
	 *
	 * @return string
	 * @access public
	 */
	public function get_txn_type();

	/**
	 * Get PayPal parent transaction ID (in case of refund)
	 *
	 * @return string
	 * @access public
	 */
	public function get_parent_txn_id();

	/**
	 * Get PayPal payer e-mail
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_email();

	/**
	 * Get PayPal payer account ID
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_id();

	/**
	 * Get PayPal payer Status (such as unverified/verified)
	 *
	 * @return string
	 * @access public
	 */
	public function get_payer_status();

	/**
	 * Get PayPal payer first name
	 *
	 * @return string
	 * @access public
	 */
	public function get_first_name();

	/**
	 * Get PayPal payer last name
	 *
	 * @return string
	 * @access public
	 */
	public function get_last_name();

	/**
	 * Get member user_id
	 *
	 * @return integer
	 * @access public
	 */
	public function get_user_id();

	/**
	 * Get PayPal payer last name
	 *
	 * @return string
	 * @access public
	 */
	public function get_custom();

	/**
	 * Get PayPal item name
	 *
	 * @return string
	 * @access public
	 */
	public function get_item_name();

	/**
	 * Get PayPal item number (contains user_id and payment_time)
	 *
	 * @return string
	 * @access public
	 */
	public function get_item_number();

	/**
	 * Get PayPal currency name (eg: USD, EUR, etc.)
	 *
	 * @return string
	 * @access public
	 */
	public function get_mc_currency();

	/**
	 * Get PayPal fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_mc_fee();

	/**
	 * Get PayPal amount
	 * This is the amount of donation received before fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_mc_gross();

	/**
	 * Get Net amount
	 * This is the amount of donation received after fees
	 *
	 * @return float
	 * @access public
	 */
	public function get_net_amount();

	/**
	 * Get PayPal payment date
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_date();

	/**
	 * Get payment time
	 * Value retrieved from the item_number
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_time();

	/**
	 * Get PayPal payment status
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_status();

	/**
	 * Get PayPal payment type
	 *
	 * @return string
	 * @access public
	 */
	public function get_payment_type();

	/**
	 * Get PayPal settle amount
	 * This is in case or the currency of the Payer is not in the same currency of the Receiver
	 *
	 * @return float
	 * @access public
	 */
	public function get_settle_amount();

	/**
	 * Get PayPal settle currency
	 *
	 * @return string
	 * @access public
	 */
	public function get_settle_currency();

	/**
	 * Get PayPal exchange rate
	 * This is when the donation don’t use the same currency defined by the receiver
	 *
	 * @return string
	 * @access public
	 */
	public function get_exchange_rate();

	/**
	 * Set PayPal transaction id
	 *
	 * @param string $txn_id
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_txn_id($txn_id);

	/**
	 * Set PayPal receiver ID
	 *
	 * @param string $receiver_id
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_receiver_id($receiver_id);

	/**
	 * Set PayPal receiver e-mail
	 *
	 * @param string $receiver_email
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_receiver_email($receiver_email);

	/**
	 * Set PayPal receiver ID
	 *
	 * @param string $residence_country
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_residence_country($residence_country);

	/**
	 * Set PayPal business (same as receiver ID or receiver_email)
	 *
	 * @param string $business
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_business($business);

	/**
	 * Set PayPal transaction status
	 *
	 * @param bool $confirmed
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_confirmed($confirmed);

	/**
	 * Set Test IPN status
	 *
	 * @param bool $test_ipn
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_test_ipn($test_ipn);

	/**
	 * Set PayPal transaction type
	 *
	 * @param string $txn_type
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_txn_type($txn_type);

	/**
	 * Set PayPal parent transaction ID (in case of refund)
	 *
	 * @param string $parent_txn_id
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_parent_txn_id($parent_txn_id);

	/**
	 * Set PayPal payer e-mail
	 *
	 * @param string $payer_email
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payer_email($payer_email);

	/**
	 * Set PayPal payer account ID
	 *
	 * @param string $payer_id
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payer_id($payer_id);

	/**
	 * Set PayPal payer Status (such as unverified/verified)
	 *
	 * @param string $payer_status
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payer_status($payer_status);

	/**
	 * Set PayPal payer first name
	 *
	 * @param string $first_name
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_first_name($first_name);

	/**
	 * Set PayPal payer last name
	 *
	 * @param string $last_name
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_last_name($last_name);

	/**
	 * Set member user_id
	 *
	 * @param integer $user_id
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_user_id($user_id);

	/**
	 * Set PayPal payer last name
	 *
	 * @param string $custom
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_custom($custom);

	/**
	 * Set PayPal item name
	 *
	 * @param string $item_name
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_item_name($item_name);

	/**
	 * Set PayPal item number (contains user_id and payment_time)
	 *
	 * @param string $item_number
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_item_number($item_number);

	/**
	 * Set PayPal currency name (eg: USD, EUR, etc.)
	 *
	 * @param string $mc_currency
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_mc_currency($mc_currency);

	/**
	 * Set PayPal fees
	 *
	 * @param float $mc_fee
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_mc_fee($mc_fee);

	/**
	 * Set PayPal amount
	 * This is the amount of donation received before fees
	 *
	 * @param float $mc_gross
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_mc_gross($mc_gross);

	/**
	 * Set Net amount
	 * This is the amount of donation received after fees
	 *
	 * @param float $net_amount
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_net_amount($net_amount);

	/**
	 * Set PayPal payment date
	 *
	 * @param string $payment_date
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payment_date($payment_date);

	/**
	 * Set payment time
	 * Value retrieved from the item_number
	 *
	 * @param string $payment_time
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payment_time($payment_time);

	/**
	 * Set PayPal payment status
	 *
	 * @param string $payment_status
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payment_status($payment_status);

	/**
	 * Get PayPal payment type
	 *
	 * @param string $payment_type
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_payment_type($payment_type);

	/**
	 * Set PayPal settle amount
	 * This is in case or the currency of the Payer is not in the same currency of the Receiver
	 *
	 * @param float $settle_amount
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_settle_amount($settle_amount);

	/**
	 * Set PayPal settle currency
	 *
	 * @param string $settle_currency
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_settle_currency($settle_currency);

	/**
	 * Set PayPal exchange rate
	 * This is when the donation don’t use the same currency defined by the receiver
	 *
	 * @param string $exchange_rate
	 *
	 * @return transactions_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_exchange_rate($exchange_rate);
}
