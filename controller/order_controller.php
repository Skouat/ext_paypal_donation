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
use skouat\ppde\api\paypal\transaction_data_builder;
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
		$decimals      = $this->ppde_actions_currency->get_currency_fraction_digits($currency_code);

		// Clear, human-readable label shown on the PayPal checkout page (max 127 chars)
		$description = $this->truncate_description(
			$this->language->lang('PPDE_DONATION_TITLE_HEAD', $this->config['sitename'])
		);

		// PayPal-compliant bank statement label (may be empty after sanitization)
		$soft_descriptor = $this->build_soft_descriptor($this->config['sitename']);

		$purchase_unit_builder = PurchaseUnitRequestBuilder::init(
			AmountWithBreakdownBuilder::init(
				$currency_code,
				number_format($amount, $decimals, '.', '')
			)->build()
		)
			->description($description)
			// Keeps compatibility with core::extract_user_id():
			// custom_id format = 'uid_<user_id>_<time>'
			->customId($this->build_custom_id())
		;

		// Statement descriptor is optional: only set it when sanitization yields
		// a non-empty, PayPal-compliant value.
		if ($soft_descriptor !== '')
		{
			$purchase_unit_builder->softDescriptor($soft_descriptor);
		}

		$purchase_unit = $purchase_unit_builder->build();

		// A donation has no shipping: redact the address block on the PayPal
		// checkout page.
		$experience_context = PaypalWalletExperienceContextBuilder::init()
			->shippingPreference(PaypalWalletContextShippingPreference::NO_SHIPPING)
		;

		// In Live mode, override the brand shown on the PayPal checkout page with
		// the board name. In Sandbox, keep the test account's default store name
		// so testers can clearly identify the sandbox environment.
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
			return new JsonResponse(['error' => $this->language->lang('PPDE_REST_PAYPAL_ERROR')], 502);
		}

		// Return the order ID to the PayPal JS SDK
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
		// Transliterate accented/Unicode characters to their ASCII equivalent
		// (e.g. "Forêt" -> "Foret"). Suppress warnings on unconvertible input.
		if (function_exists('iconv'))
		{
			$converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

			if ($converted !== false)
			{
				$text = $converted;
			}
		}

		// Keep only the characters allowed by PayPal: letters, digits, space, dot, asterisk and dash.
		$text = preg_replace('/[^A-Za-z0-9 .*-]/', '', $text);

		// Collapse repeated spaces and trim.
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

		if ($capture === null || !method_exists($capture, 'getId') || (string) $capture->getId() === '')
		{
			return null;
		}

		$custom    = method_exists($capture, 'getCustomId') ? (string) $capture->getCustomId() : '';
		$amount    = method_exists($capture, 'getAmount') ? $capture->getAmount() : null;
		$breakdown = method_exists($capture, 'getSellerReceivableBreakdown') ? $capture->getSellerReceivableBreakdown() : null;
		$currency  = ($amount && method_exists($amount, 'getCurrencyCode')) ? (string) $amount->getCurrencyCode() : '';
		$gross     = ($amount && method_exists($amount, 'getValue')) ? (float) $amount->getValue() : 0.0;

		// Use the real PayPal capture date; fall back to now if unavailable
		$create_time  = method_exists($capture, 'getCreateTime') ? (string) $capture->getCreateTime() : '';
		$payment_date = ($create_time !== '') ? (int) strtotime($create_time) : time();

		// Seller receivable breakdown: fee, net, settled amount and exchange rate
		$fee = $net = 0.0;
		$exchange_rate = '';
		$receivable_obj = null;

		if ($breakdown)
		{
			$fee_obj        = method_exists($breakdown, 'getPaypalFee') ? $breakdown->getPaypalFee() : null;
			$net_obj        = method_exists($breakdown, 'getNetAmount') ? $breakdown->getNetAmount() : null;
			$fx_obj         = method_exists($breakdown, 'getExchangeRate') ? $breakdown->getExchangeRate() : null;
			$receivable_obj = method_exists($breakdown, 'getReceivableAmount') ? $breakdown->getReceivableAmount() : null;

			$fee           = ($fee_obj && method_exists($fee_obj, 'getValue')) ? (float) $fee_obj->getValue() : 0.0;
			$net           = ($net_obj && method_exists($net_obj, 'getValue')) ? (float) $net_obj->getValue() : 0.0;
			$exchange_rate = ($fx_obj && method_exists($fx_obj, 'getValue')) ? (string) $fx_obj->getValue() : '';
		}

		// Payer details, from payment_source.paypal (modern) or payer (legacy)
		$payer = $this->extract_payer_fields($this->resolve_payer_source($order));

		// Payee details, from the first purchase unit
		$payee = $this->extract_payee_fields($order);

		// Localized donation title, kept for consistency with the legacy IPN flow
		$item_name = $this->language->lang('PPDE_DONATION_TITLE_HEAD', $this->config['sitename']);

		return $this->build_transaction_data([
			'business'          => $is_sandbox ? $this->config['ppde_sandbox_rest_client_id'] : $this->config['ppde_rest_client_id'],
			'confirmed'         => true,
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
			'payment_status'    => 'Completed',
			'receiver_id'       => $payee['merchant_id'],
			'receiver_email'    => $payee['email'],
			'residence_country' => $payer['country'],
			'settle_amount'     => ($receivable_obj && method_exists($receivable_obj, 'getValue')) ? (float) $receivable_obj->getValue() : 0.0,
			'settle_currency'   => ($receivable_obj && method_exists($receivable_obj, 'getCurrencyCode')) ? (string) $receivable_obj->getCurrencyCode() : '',
			'test_ipn'          => $is_sandbox,
			'txn_id'            => (string) $capture->getId(),
			'txn_type'          => 'ppde_rest_donation',
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
