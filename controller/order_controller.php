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
 * This controller replaces the old Website Payments Standard form
 * (cmd=_donations) previously built in main_donate::paypal_hidden_fields().
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
			return new JsonResponse(['error' => $this->language->lang('PPDE_MT_MC_GROSS_TOO_LOW')], 400);
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
			return new JsonResponse(['error' => 'BAD_REQUEST'], 400);
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

		// The donation is recorded in DB by the webhook listener, not here.
		// We only return the capture status so the JS can redirect the donor
		// to the success or cancel page.
		return new JsonResponse([
			'status' => $response->getResult()->getStatus(),
		]);
	}
}
