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

		$name    = method_exists($source, 'getName') ? $source->getName() : null;
		$address = method_exists($source, 'getAddress') ? $source->getAddress() : null;

		if (method_exists($source, 'getAccountId'))
		{
			$payer_id = (string) $source->getAccountId();
		}
		else if (method_exists($source, 'getPayerId'))
		{
			$payer_id = (string) $source->getPayerId();
		}
		else
		{
			$payer_id = '';
		}

		return [
			'first_name' => ($name && method_exists($name, 'getGivenName')) ? (string) $name->getGivenName() : '',
			'last_name'  => ($name && method_exists($name, 'getSurname')) ? (string) $name->getSurname() : '',
			'email'      => method_exists($source, 'getEmailAddress') ? (string) $source->getEmailAddress() : '',
			'payer_id'   => $payer_id,
			'country'    => ($address && method_exists($address, 'getCountryCode')) ? (string) $address->getCountryCode() : '',
		];
	}

	/**
	 * Pick the best payer source from an Order (paypal source first, legacy payer fallback).
	 */
	protected function resolve_payer_source($order)
	{
		$payment_source = method_exists($order, 'getPaymentSource') ? $order->getPaymentSource() : null;
		$paypal_src     = ($payment_source && method_exists($payment_source, 'getPaypal')) ? $payment_source->getPaypal() : null;

		if ($paypal_src)
		{
			return $paypal_src;
		}

		return method_exists($order, 'getPayer') ? $order->getPayer() : null;
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
		$units      = method_exists($order, 'getPurchaseUnits') ? $order->getPurchaseUnits() : null;
		$first_unit = (!empty($units) && is_array($units)) ? $units[0] : null;
		$payee      = ($first_unit && method_exists($first_unit, 'getPayee')) ? $first_unit->getPayee() : null;

		if (!$payee)
		{
			return ['email' => '', 'merchant_id' => ''];
		}

		return [
			'email'       => method_exists($payee, 'getEmailAddress') ? (string) $payee->getEmailAddress() : '',
			'merchant_id' => method_exists($payee, 'getMerchantId') ? (string) $payee->getMerchantId() : '',
		];
	}
}
