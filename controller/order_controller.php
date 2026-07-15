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
use PaypalServerSdkLib\Models\Builders\PaymentSourceBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletExperienceContextBuilder;
use PaypalServerSdkLib\Models\PaypalWalletContextShippingPreference;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Exceptions\ApiException;
use skouat\ppde\api\paypal\order_party_extractor;
use skouat\ppde\entity\transaction_data_builder;
use skouat\ppde\ppde_constants;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Handles the synchronous donation flow (PayPal Orders API v2).
 *
 * create()  : creates a PayPal order and returns its ID.
 * capture() : captures the funds once the donor approved the payment.
 *
 * The authoritative recording is done asynchronously by the webhook listener;
 * the capture endpoint only records as a fallback.
 */
class order_controller extends main_controller
{
	use order_party_extractor;
	use transaction_data_builder;

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
		if ($error = $this->guard())
		{
			return $error;
		}

		$amount = (float) $this->request->variable('amount', 0.0);

		if ($amount <= 0)
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_AMOUNT_INVALID')], JsonResponse::HTTP_BAD_REQUEST);
		}

		// get_default_currency_data() only returns ENABLED currencies, so an
		// invalid/disabled id yields an empty array.
		$currency_id = $this->request->variable('currency_id', (int) $this->config['ppde_default_currency']);
		$currency_data = $this->ppde_actions_currency->get_default_currency_data($currency_id);

		if (empty($currency_data))
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_INVALID_CURRENCY')], JsonResponse::HTTP_BAD_REQUEST);
		}

		$currency_code = $currency_data[0]['currency_iso_code'];
		$decimals      = $this->ppde_actions_currency->get_currency_fraction_digits($currency_code);

		// Label shown on the PayPal checkout page (max 127 chars).
		$description = $this->truncate_description(
			$this->language->lang('PPDE_DONATION_TITLE_HEAD', $this->config['sitename'])
		);

		// Bank statement label (maybe empty after sanitization).
		$soft_descriptor = $this->build_soft_descriptor($this->config['sitename']);

		$purchase_unit_builder = PurchaseUnitRequestBuilder::init(
			AmountWithBreakdownBuilder::init(
				$currency_code,
				number_format($amount, $decimals, '.', '')
			)->build()
		)
			->description($description)
			// custom_id format kept for core::extract_user_id(): 'uid_<user_id>_<time>'
			->customId($this->build_custom_id())
		;

		if ($soft_descriptor !== '')
		{
			$purchase_unit_builder->softDescriptor($soft_descriptor);
		}

		$purchase_unit = $purchase_unit_builder->build();

		// A donation has no shipping: hide the address block.
		$experience_context = PaypalWalletExperienceContextBuilder::init()
			->shippingPreference(PaypalWalletContextShippingPreference::NO_SHIPPING)
		;

		// In Live, brand the checkout with the board name; in Sandbox keep the
		// test account's default store name so testers spot the environment.
		if (!$this->use_sandbox())
		{
			$experience_context->brandName($this->config['sitename']);
		}

		$payment_source = PaymentSourceBuilder::init()
			->paypal(
				PaypalWalletBuilder::init()
					->experienceContext($experience_context->build())
					->build()
			)
			->build()
		;

		$order_request = OrderRequestBuilder::init(
			CheckoutPaymentIntent::CAPTURE,
			[$purchase_unit]
		)
			->paymentSource($payment_source)
			->build()
		;

		try
		{
			$orders = $this->client_factory->build($this->use_sandbox())->getOrdersController();
			$response = $orders->createOrder(['body' => $order_request]);
		}
		catch (ApiException $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_PAYPAL_API_ERROR', time(), [$e->getMessage()]);
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_PAYPAL_ERROR')], JsonResponse::HTTP_BAD_GATEWAY);
		}

		return new JsonResponse(['id' => $response->getResult()->getId()]);
	}

	/**
	 * Truncate a string to the 127-character limit allowed by PayPal for the
	 * purchase unit description.
	 *
	 * @param string $text
	 *
	 * @return string
	 * @access private
	 */
	private function truncate_description(string $text): string
	{
		return (utf8_strlen($text) > 127) ? utf8_substr($text, 0, 124) . '...' : $text;
	}

	/**
	 * Build a PayPal-compliant soft descriptor (bank statement label) from a
	 * free-form string such as the board name.
	 *
	 * PayPal only allows the characters [A-Za-z0-9 .*-] and displays at most
	 * 22 characters (it also prepends its own "PAYPAL *" prefix). Accented and
	 * other non-ASCII characters are transliterated to ASCII when possible, then
	 * any remaining unsupported character is dropped.
	 *
	 * @param string $text
	 *
	 * @return string Sanitized descriptor, possibly empty if nothing remains.
	 * @access private
	 */
	private function build_soft_descriptor(string $text): string
	{
		// Transliterate accented/Unicode chars to ASCII ("Forêt" -> "Foret").
		if (function_exists('iconv'))
		{
			$converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

			if ($converted !== false)
			{
				$text = $converted;
			}
		}

		// Keep only the characters allowed by PayPal.
		$text = preg_replace('/[^A-Za-z0-9 .*-]/', '', $text);
		$text = trim(preg_replace('/\s+/', ' ', $text));

		// PayPal truncates at 22 characters.
		return substr($text, 0, 22);
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
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_BAD_REQUEST')], JsonResponse::HTTP_BAD_REQUEST);
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'ppde_donate'))
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_BAD_REQUEST')], JsonResponse::HTTP_BAD_REQUEST);
		}

		if (empty($this->config['ppde_enable']) || !$this->ppde_actions_auth->can_use_ppde())
		{
			return new JsonResponse(['error' => $this->language->lang('NOT_AUTHORISED')], JsonResponse::HTTP_FORBIDDEN);
		}

		if (!$this->client_factory->is_configured($this->use_sandbox()))
		{
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_CREDENTIALS_MISSING')], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
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
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_MISSING_ORDER_ID')], JsonResponse::HTTP_BAD_REQUEST);
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
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_PAYPAL_ERROR')], JsonResponse::HTTP_BAD_GATEWAY);
		}

		$result = $response->getResult();
		$order_status = (string) $result->getStatus();

		if ($order_status === ppde_constants::PAYPAL_ORDER_COMPLETED)
		{
			try
			{
				$data = $this->build_donation_from_order($result, $this->use_sandbox());

				if ($data !== null && $data['payment_status'] === ppde_constants::STATUS_COMPLETED)
				{
					$this->donation_recorder->record_completed($data, $this->use_sandbox());
				}
			}
			catch (\Throwable $e)
			{
				$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_WEBHOOK_PROCESS_ERROR', time(), [$order_id, $e->getMessage()]);
			}
		}

		return new JsonResponse(['status' => $order_status]);
	}

	/**
	 * Build the PPDE transaction array from a captured Order SDK object.
	 *
	 * Payer and payee details are read directly from the capture response
	 * (requested with prefer=return=representation), so no extra API call is
	 * needed. Returns null if the capture cannot be extracted, in which case
	 * the webhook remains the only recorder (graceful degradation).
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
		$capture_status = $this->map_capture_status((string) ($this->safe_call($capture, 'getStatus') ?? ''));

		if ($capture === null || (string) ($this->safe_call($capture, 'getId') ?? '') === '')
		{
			return null;
		}

		$custom    = (string) ($this->safe_call($capture, 'getCustomId') ?? '');
		$amount    = $this->safe_call($capture, 'getAmount');
		$breakdown = $this->safe_call($capture, 'getSellerReceivableBreakdown');
		$currency  = (string) ($this->safe_call($amount, 'getCurrencyCode') ?? '');
		$gross     = (float) ($this->safe_call($amount, 'getValue') ?? 0.0);

		// Use the real PayPal capture date; fall back to now if unavailable
		$create_time  = (string) ($this->safe_call($capture, 'getCreateTime') ?? '');
		$timestamp    = strtotime($create_time);
		$payment_date = ($timestamp !== false) ? $timestamp : time();

		// Seller receivable breakdown: fee, net, settled amount and exchange rate
		$fee = $net = 0.0;
		$exchange_rate = '';
		$receivable_obj = null;

		if ($breakdown)
		{
			$fee_obj        = $this->safe_call($breakdown, 'getPaypalFee');
			$net_obj        = $this->safe_call($breakdown, 'getNetAmount');
			$fx_obj         = $this->safe_call($breakdown, 'getExchangeRate');
			$receivable_obj = $this->safe_call($breakdown, 'getReceivableAmount');

			$fee           = (float) ($this->safe_call($fee_obj, 'getValue') ?? 0.0);
			$net           = (float) ($this->safe_call($net_obj, 'getValue') ?? 0.0);
			$exchange_rate = (string) ($this->safe_call($fx_obj, 'getValue') ?? '');
		}

		// Payer details, from payment_source.paypal (modern) or payer (legacy)
		$payer = $this->extract_payer_fields($this->resolve_payer_source($order));

		// Payee details, from the first purchase unit
		$payee = $this->extract_payee_fields($order);

		// Localized donation title, kept for consistency with the legacy IPN flow
		$item_name = $this->language->lang('PPDE_DONATION_TITLE_HEAD', $this->config['sitename']);

		return $this->build_transaction_data([
			'business'          => $is_sandbox ? $this->config['ppde_sandbox_rest_client_id'] : $this->config['ppde_rest_client_id'],
			'confirmed'         => $capture_status === ppde_constants::STATUS_COMPLETED,
			'custom'            => $custom,
			'exchange_rate'     => $exchange_rate,
			'first_name'        => $payer['first_name'],
			'item_name'         => $item_name,
			'item_number'       => $custom,
			'last_name'         => $payer['last_name'],
			'mc_currency'       => $currency,
			'mc_gross'          => $gross,
			'mc_fee'            => $fee,
			'net_amount'        => $net,
			'payer_email'       => $payer['email'],
			'payer_id'          => $payer['payer_id'],
			'payment_date'      => $payment_date,
			'payment_status'    => $capture_status,
			'receiver_id'       => $payee['merchant_id'],
			'receiver_email'    => $payee['email'],
			'residence_country' => $payer['country'],
			'settle_amount'     => (float) ($this->safe_call($receivable_obj, 'getValue') ?? 0.0),
			'settle_currency'   => (string) ($this->safe_call($receivable_obj, 'getCurrencyCode') ?? ''),
			'test_ipn'          => $is_sandbox,
			'txn_id'            => (string) ($this->safe_call($capture, 'getId') ?? ''),
			'txn_type'          => ppde_constants::TXN_TYPE_REST_DONATION,
		]);
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
		$units = $this->safe_call($order, 'getPurchaseUnits');
		if (empty($units) || !is_array($units))
		{
			return null;
		}

		$payments = $this->safe_call($units[0], 'getPayments');
		$captures = $this->safe_call($payments, 'getCaptures');

		return (!empty($captures) && is_array($captures)) ? $captures[0] : null;
	}

	/**
	 * Map a PayPal capture status to the PPDE payment status.
	 * Unknown statuses fall back to pending (never credited).
	 *
	 * @param string $status
	 *
	 * @return string
	 * @access private
	 */
	private function map_capture_status(string $status): string
	{
		switch (strtoupper($status))
		{
			case 'COMPLETED':
				return ppde_constants::STATUS_COMPLETED;
			case 'DECLINED':
			case 'FAILED':
				return ppde_constants::STATUS_DENIED;
			default:
				return ppde_constants::STATUS_PENDING;
		}
	}
}
