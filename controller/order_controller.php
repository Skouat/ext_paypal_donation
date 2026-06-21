<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Handles the synchronous donation payment flow using the PayPal Orders API v2.
 *
 * Two endpoints are exposed and called by the PayPal JS SDK in the donor browser:
 *  - create()  : creates a PayPal order and returns its ID.
 *  - capture() : captures the funds once the donor approved the payment.
 *
 * The authoritative recording of the donation in the database is performed
 * asynchronously by the webhook listener, NOT here.
 */
class order_controller extends main_controller
{
	/** @var \skouat\ppde\api\paypal\client_factory */
	protected $client_factory;
	/** @var \phpbb\log\log */
	protected $log;
	/** @var \skouat\ppde\actions\donation_recorder */
	protected $donation_recorder;

	/**
	 * Inject the PayPal client factory.
	 *
	 * @param \skouat\ppde\api\paypal\client_factory $client_factory
	 *
	 * @return void
	 * @access public
	 */
	public function set_client_factory(\skouat\ppde\api\paypal\client_factory $client_factory): void
	{
		$this->client_factory = $client_factory;
	}

	public function set_log(\phpbb\log\log $log): void
	{
		$this->log = $log;
	}

	public function set_donation_recorder(\skouat\ppde\actions\donation_recorder $donation_recorder): void
	{
		$this->donation_recorder = $donation_recorder;
	}

	/**
	 * Create a PayPal order.
	 * Route: skouat_ppde_create_order
	 *
	 * @return JsonResponse
	 * @access public
	 */
	public function create(): JsonResponse
	{
		// Access guards
		if ($error = $this->guard())
		{
			return $error;
		}

		// Collect and validate the donation amount
		$amount = (float) $this->request->variable('amount', 0.0);

		if ($amount <= 0)
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_AMOUNT_INVALID')], 400);
		}

		// Resolve the currency ISO code from the selected currency id.
		// get_default_currency_data() only returns ENABLED currencies,
		// so an invalid/disabled id yields an empty array.
		$currency_id = $this->request->variable('currency_id', (int) $this->config['ppde_default_currency']);
		$currency_data = $this->ppde_actions_currency->get_default_currency_data($currency_id);

		if (empty($currency_data))
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_INVALID_CURRENCY')], 400);
		}

		$currency_code = $currency_data[0]['currency_iso_code'];

		// Build the order request body
		$order_request = OrderRequestBuilder::init(
			CheckoutPaymentIntent::CAPTURE,
			[
				PurchaseUnitRequestBuilder::init(
					AmountWithBreakdownBuilder::init(
						$currency_code,
						number_format($amount, 2, '.', '')
					)->build()
				)
					// Keeps compatibility with core::extract_user_id():
					// custom_id format = 'uid_<user_id>_<time>'
					->customId($this->build_custom_id())
					->build(),
			]
		)->build();

		try
		{
			$orders = $this->client_factory->build($this->use_sandbox())->getOrdersController();
			$response = $orders->createOrder(['body' => $order_request]);
		}
		catch (ApiException $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_PAYPAL_API_ERROR', time(), [$e->getMessage()]);
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_PAYPAL_ERROR')], 502);
		}

		// Return the order ID to the PayPal JS SDK
		return new JsonResponse(['id' => $response->getResult()->getId()]);
	}

	/**
	 * Common access checks for both endpoints.
	 *
	 * @return JsonResponse|null A JsonResponse on failure, null if access is granted.
	 * @access private
	 */
	private function guard(): ?JsonResponse
	{
		if (!$this->request->is_ajax())
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_BAD_REQUEST')], 400);
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'ppde_donate'))
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_BAD_REQUEST')], 400);
		}

		if (empty($this->config['ppde_enable']) || !$this->ppde_actions_auth->can_use_ppde())
		{
			return new JsonResponse(['error' => $this->language->lang('NOT_AUTHORISED')], 403);
		}

		if (!$this->client_factory->is_configured($this->use_sandbox()))
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_CREDENTIALS_MISSING')], 503);
		}

		return null;
	}

	/**
	 * Build the custom identifier embedded in the order.
	 * Format kept identical to the legacy IPN flow so that
	 * core::extract_user_id() keeps working unchanged.
	 *
	 * @return string
	 * @access private
	 */
	private function build_custom_id(): string
	{
		return 'uid_' . $this->user->data['user_id'] . '_' . time();
	}

	/**
	 * Capture an approved PayPal order.
	 * Route: skouat_ppde_capture_order
	 *
	 * @return JsonResponse
	 * @access public
	 */
	public function capture(): JsonResponse
	{
		// Access guards
		if ($error = $this->guard())
		{
			return $error;
		}

		$order_id = $this->request->variable('order_id', '');

		if ($order_id === '')
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_MISSING_ORDER_ID')], 400);
		}

		try
		{
			$orders = $this->client_factory->build($this->use_sandbox())->getOrdersController();
			$response = $orders->captureOrder([
				'id'     => $order_id,
				'prefer' => 'return=representation',
			]);
		}
		catch (ApiException $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_PAYPAL_API_ERROR', time(), [$e->getMessage()]);
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_PAYPAL_ERROR')], 502);
		}

		$result = $response->getResult();
		$status = (string) $result->getStatus();

		// Safety net: record the donation now (idempotently) in case the PayPal
		// webhook never reaches the board (misconfiguration, WAF, anti-bot…).
		// The recorder's idempotency guard prevents any double processing when
		// the webhook later arrives. Any failure here is swallowed so the donor's
		// redirect is never broken — the webhook remains the authoritative path.
		if ($status === 'COMPLETED')
		{
			try
			{
				$data = $this->build_donation_from_order($result, $this->use_sandbox());

				if ($data !== null)
				{
					$this->donation_recorder->record_completed($data, $this->use_sandbox());
				}
			}
			catch (\Throwable $e)
			{
				$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_WEBHOOK_PROCESS_ERROR', time(), [$order_id, $e->getMessage()]);
			}
		}

		return new JsonResponse(['status' => $status]);
	}

	/**
	 * Build the PPDE transaction array from a captured Order SDK object.
	 * Returns null if the capture cannot be extracted (the webhook then remains
	 * the only recorder — graceful degradation).
	 *
	 * @param object $order      The Order result from captureOrder()
	 * @param bool   $is_sandbox
	 *
	 * @return array|null
	 * @access private
	 */
	private function build_donation_from_order($order, bool $is_sandbox): ?array
	{
		$capture = $this->extract_capture($order);

		if ($capture === null || !method_exists($capture, 'getId') || (string) $capture->getId() === '')
		{
			return null;
		}

		$custom         = method_exists($capture, 'getCustomId') ? (string) $capture->getCustomId() : '';
		$amount         = method_exists($capture, 'getAmount') ? $capture->getAmount() : null;
		$breakdown      = method_exists($capture, 'getSellerReceivableBreakdown') ? $capture->getSellerReceivableBreakdown() : null;
		$receivable_obj = method_exists($breakdown, 'getReceivableAmount') ? $breakdown->getReceivableAmount() : null;
		$currency       = ($amount && method_exists($amount, 'getCurrencyCode')) ? (string) $amount->getCurrencyCode() : '';
		$gross          = ($amount && method_exists($amount, 'getValue')) ? (float) $amount->getValue() : 0.0;

		$fee = $net = 0.0;
		$exchange_rate = '';
		if ($breakdown)
		{
			$fee_obj = method_exists($breakdown, 'getPaypalFee') ? $breakdown->getPaypalFee() : null;
			$net_obj = method_exists($breakdown, 'getNetAmount') ? $breakdown->getNetAmount() : null;
			$fx_obj  = method_exists($breakdown, 'getExchangeRate') ? $breakdown->getExchangeRate() : null;

			$fee = ($fee_obj && method_exists($fee_obj, 'getValue')) ? (float) $fee_obj->getValue() : 0.0;
			$net = ($net_obj && method_exists($net_obj, 'getValue')) ? (float) $net_obj->getValue() : 0.0;
			$exchange_rate = ($fx_obj && method_exists($fx_obj, 'getValue')) ? (string) $fx_obj->getValue() : '';
		}

		return [
			'business'          => $is_sandbox ? $this->config['ppde_sandbox_rest_client_id'] : $this->config['ppde_rest_client_id'],
			'confirmed'         => true,
			'custom'            => $custom,
			'exchange_rate'     => $exchange_rate,
			'first_name'        => '',
			'item_name'         => '',
			'item_number'       => $custom,
			'last_name'         => '',
			'mc_currency'       => $currency,
			'mc_gross'          => $gross,
			'mc_fee'            => $fee,
			'net_amount'        => $net,
			'parent_txn_id'     => '',
			'payer_email'       => '',
			'payer_id'          => '',
			'payer_status'      => '',
			'payment_date'      => time(),
			'payment_status'    => 'Completed',
			'payment_type'      => '',
			'memo'              => '',
			'receiver_id'       => '',
			'receiver_email'    => '',
			'residence_country' => '',
			'settle_amount'     => ($receivable_obj && method_exists($receivable_obj, 'getValue')) ? (float) $receivable_obj->getValue() : 0.0,
			'settle_currency'   => ($receivable_obj && method_exists($receivable_obj, 'getCurrencyCode')) ? (string) $receivable_obj->getCurrencyCode() : '',
			'test_ipn'          => $is_sandbox,
			'txn_errors'        => '',
			'txn_id'            => (string) $capture->getId(),
			'txn_type'          => 'ppde_rest_donation',
			'user_id'           => ANONYMOUS, // Overridden by core::extract_user_id() from custom
		];
	}

	/**
	 * Extract the first capture object from a captured Order, defensively.
	 *
	 * @param object $order
	 *
	 * @return object|null
	 * @access private
	 */
	private function extract_capture($order)
	{
		$units = method_exists($order, 'getPurchaseUnits') ? $order->getPurchaseUnits() : null;
		if (empty($units) || !is_array($units))
		{
			return null;
		}

		$payments = method_exists($units[0], 'getPayments') ? $units[0]->getPayments() : null;
		$captures = ($payments && method_exists($payments, 'getCaptures')) ? $payments->getCaptures() : null;

		return (!empty($captures) && is_array($captures)) ? $captures[0] : null;
	}
}
