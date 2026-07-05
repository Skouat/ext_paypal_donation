<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\api\paypal;

trait order_party_extractor
{
	/**
	 * Extract payer fields from a PayPal source object
	 * (modern payment_source.paypal or legacy payer).
	 */
	protected function extract_payer_fields($source): array
	{
		$empty = ['first_name' => '', 'last_name' => '', 'email' => '', 'payer_id' => '', 'country' => ''];

		if (!$source)
		{
			return $empty;
		}

		$name    = $this->safe_call($source, 'getName');
		$address = $this->safe_call($source, 'getAddress');

		// Modern accounts expose getAccountId(); legacy payer uses getPayerId().
		$payer_id = $this->safe_call($source, 'getAccountId') ?? $this->safe_call($source, 'getPayerId') ?? '';

		return [
			'first_name' => (string) ($this->safe_call($name, 'getGivenName') ?? ''),
			'last_name'  => (string) ($this->safe_call($name, 'getSurname') ?? ''),
			'email'      => (string) ($this->safe_call($source, 'getEmailAddress') ?? ''),
			'payer_id'   => (string) $payer_id,
			'country'    => (string) ($this->safe_call($address, 'getCountryCode') ?? ''),
		];
	}

	/**
	 * Pick the best payer source from an Order (paypal source first, legacy payer fallback).
	 */
	protected function resolve_payer_source($order)
	{
		$payment_source = $this->safe_call($order, 'getPaymentSource');
		$paypal_src     = $this->safe_call($payment_source, 'getPaypal');

		return $paypal_src ?: $this->safe_call($order, 'getPayer');
	}

	/**
	 * Extract payee (merchant) fields from an Order's first purchase unit.
	 *
	 * @param object $order An Order object exposing getPurchaseUnits().
	 *
	 * @return array
	 * @access protected
	 */
	protected function extract_payee_fields($order): array
	{
		$units      = $this->safe_call($order, 'getPurchaseUnits');
		$first_unit = (!empty($units) && is_array($units)) ? $units[0] : null;
		$payee      = $this->safe_call($first_unit, 'getPayee');

		return [
			'email'       => (string) ($this->safe_call($payee, 'getEmailAddress') ?? ''),
			'merchant_id' => (string) ($this->safe_call($payee, 'getMerchantId') ?? ''),
		];
	}

	/**
	 * Safely call a getter on a possibly-null SDK object.
	 *
	 * @param object|null $object
	 * @param string      $method
	 * @param mixed       $default
	 *
	 * @return mixed
	 * @access protected
	 */
	protected function safe_call($object, string $method, $default = null)
	{
		return ($object !== null && method_exists($object, $method)) ? $object->$method() : $default;
	}
}
