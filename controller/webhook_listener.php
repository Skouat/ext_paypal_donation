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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\user;
use skouat\ppde\actions\core;
use skouat\ppde\actions\donation_recorder;
use skouat\ppde\api\paypal\client_factory;
use skouat\ppde\api\paypal\order_party_extractor;
use skouat\ppde\api\paypal\transaction_data_builder;
use skouat\ppde\api\paypal\webhook_verify;
use skouat\ppde\entity\transactions as transactions_entity;
use skouat\ppde\exception\transaction_exception;
use skouat\ppde\operators\transactions as transactions_operator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receives and processes PayPal webhook notifications.
 *
 * The donation is recorded in the database asynchronously,
 * once the webhook signature has been cryptographically verified.
 */
class webhook_listener
{
	use order_party_extractor;
	use transaction_data_builder;

	/** @var config */
	protected $config;
	/** @var language */
	protected $language;
	/** @var log */
	protected $log;
	/** @var core */
	protected $ppde_actions;
	/** @var transactions_entity */
	protected $ppde_entity_transaction;
	/** @var transactions_operator */
	protected $ppde_operator_transaction;
	/** @var webhook_verify */
	protected $webhook_verify;
	/** @var client_factory */
	protected $client_factory;
	/** @var request */
	protected $request;
	/** @var user */
	protected $user;
	/** @var donation_recorder */
	protected $donation_recorder;

	/**
	 * Constructor
	 *
	 * @param config                $config
	 * @param language              $language
	 * @param log                   $log
	 * @param core                  $ppde_actions
	 * @param transactions_entity   $ppde_entity_transaction
	 * @param transactions_operator $ppde_operator_transaction
	 * @param webhook_verify        $webhook_verify
	 * @param client_factory        $client_factory
	 * @param request               $request
	 * @param user                  $user
	 * @param donation_recorder     $donation_recorder
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		log $log,
		core $ppde_actions,
		transactions_entity $ppde_entity_transaction,
		transactions_operator $ppde_operator_transaction,
		webhook_verify $webhook_verify,
		client_factory $client_factory,
		request $request,
		user $user,
		donation_recorder $donation_recorder
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions = $ppde_actions;
		$this->ppde_entity_transaction = $ppde_entity_transaction;
		$this->ppde_operator_transaction = $ppde_operator_transaction;
		$this->webhook_verify = $webhook_verify;
		$this->client_factory = $client_factory;
		$this->request = $request;
		$this->user = $user;
		$this->donation_recorder = $donation_recorder;
	}

	/**
	 * Handle the incoming webhook.
	 *
	 * @return Response
	 * @access public
	 */
	public function handle(): Response
	{
		$this->language->add_lang('donate', 'skouat/ppde');

		$raw_body = $this->get_raw_input();
		$headers = $this->collect_headers();

		$event = json_decode($raw_body, true);
		$event_type = (is_array($event) && !empty($event['event_type'])) ? $event['event_type'] : '';

		$environment = $this->resolve_environment($raw_body, $headers);
		if ($environment === null)
		{
			$this->log->add('critical', ANONYMOUS, $this->user->ip, 'LOG_PPDE_WEBHOOK_SIG_FAILED', time(), [$event_type]);
			return new Response('', 403);
		}

		if (!is_array($event) || $event_type === '' || empty($event['resource']))
		{
			return new Response('', 400);
		}

		$processed = $this->process_event($event, $environment === 'sandbox');

		return new Response('', $processed ? 200 : 500);
	}

	/**
	 * Read the raw request body.
	 * Extracted into its own method to ease unit testing.
	 *
	 * @return string
	 * @access protected
	 */
	protected function get_raw_input(): string
	{
		return (string) file_get_contents('php://input');
	}

	/**
	 * Collect and normalize the PayPal signature headers.
	 *
	 * @return array
	 * @access private
	 */
	private function collect_headers(): array
	{
		return [
			'transmission_id'   => (string) $this->request->header('Paypal-Transmission-Id'),
			'transmission_time' => (string) $this->request->header('Paypal-Transmission-Time'),
			'transmission_sig'  => (string) $this->request->header('Paypal-Transmission-Sig'),
			'cert_url'          => (string) $this->request->header('Paypal-Cert-Url'),
			'auth_algo'         => (string) $this->request->header('Paypal-Auth-Algo'),
		];
	}

	/**
	 * Resolve which environment a verified webhook belongs to.
	 *
	 * @param string $raw_body
	 * @param array  $headers
	 *
	 * @return string|null 'live', 'sandbox', or null if the signature is invalid.
	 * @access private
	 */
	private function resolve_environment(string $raw_body, array $headers): ?string
	{
		if ($this->webhook_verify->is_valid($raw_body, $headers, $this->config['ppde_webhook_id']))
		{
			return 'live';
		}

		if ($this->webhook_verify->is_valid($raw_body, $headers, $this->config['ppde_sandbox_webhook_id']))
		{
			return 'sandbox';
		}

		return null;
	}

	/**
	 * Dispatch the event to the appropriate handler.
	 *
	 * @param array $event
	 * @param bool  $is_sandbox
	 *
	 * @return bool
	 * @access private
	 */
	private function process_event(array $event, bool $is_sandbox): bool
	{
		switch ($event['event_type'])
		{
			case 'PAYMENT.CAPTURE.COMPLETED':
				return $this->handle_capture($event['resource'], 'Completed', $is_sandbox);

			case 'PAYMENT.CAPTURE.PENDING':
				return $this->handle_capture($event['resource'], 'Pending', $is_sandbox);

			case 'PAYMENT.CAPTURE.REFUNDED':
				return $this->handle_capture_refunded($event['resource'], 'Refunded', $is_sandbox);

			case 'PAYMENT.CAPTURE.REVERSED':
				return $this->handle_capture_refunded($event['resource'], 'Reversed', $is_sandbox);

			case 'PAYMENT.CAPTURE.DENIED':
				return $this->handle_capture_denied($event['resource'], $is_sandbox);

			// Any other event type is acknowledged (HTTP 200) but not processed.
			default:
				return true;
		}
	}

	/**
	 * Handle a capture event: record the donation (insert or update) and,
	 * when completed, run the post-actions. A capture first received as
	 * "Pending" is later upgraded to "Completed".
	 *
	 * @param array  $resource
	 * @param string $payment_status 'Pending' or 'Completed'
	 * @param bool   $is_sandbox
	 *
	 * @return bool
	 * @access private
	 */
	private function handle_capture(array $resource, string $payment_status, bool $is_sandbox): bool
	{
		$txn_id = $resource['id'] ?? '';

		if ($txn_id === '' || $this->already_completed($txn_id))
		{
			return true;
		}

		try
		{
			$data = $this->map_capture($resource, $payment_status, $is_sandbox);

			if ($payment_status === 'Completed')
			{
				$this->donation_recorder->record_completed($data, $is_sandbox);
			}
			else
			{
				$this->ppde_actions->log_to_db($data);
			}
		}
		catch (transaction_exception $e)
		{
			return true;
		}
		catch (\Throwable $e)
		{
			$this->log->add('critical', ANONYMOUS, $this->user->ip, 'LOG_PPDE_WEBHOOK_PROCESS_ERROR', time(), [$txn_id, $e->getMessage()]);
			return false;
		}

		return true;
	}

	/**
	 * Check whether a transaction has already been logged (idempotency guard).
	 *
	 * @param string $txn_id
	 *
	 * @return bool
	 * @access private
	 */
	private function already_processed(string $txn_id): bool
	{
		$this->ppde_entity_transaction->set_txn_id($txn_id);

		return (bool) $this->ppde_entity_transaction->transaction_exists();
	}

	/**
	 * Check whether a capture has already been fully processed (Completed).
	 * Unlike already_processed(), this lets a pending row be upgraded later.
	 *
	 * @param string $txn_id
	 *
	 * @return bool
	 * @access private
	 */
	private function already_completed(string $txn_id): bool
	{
		return $this->ppde_operator_transaction->is_txn_completed($txn_id);
	}

	/**
	 * Map a capture resource (completed, pending or denied) to the PPDE transaction array.
	 *
	 * @param array  $resource
	 * @param string $payment_status 'Completed', 'Pending' or 'Denied'
	 * @param bool   $is_sandbox
	 *
	 * @return array
	 * @access private
	 */
	private function map_capture(array $resource, string $payment_status, bool $is_sandbox): array
	{
		$breakdown  = $resource['seller_receivable_breakdown'] ?? [];
		$custom     = $resource['custom_id'] ?? '';
		$order_id   = $resource['supplementary_data']['related_ids']['order_id'] ?? '';
		$receivable = $breakdown['receivable_amount'] ?? [];

		$parties = $this->fetch_parties($order_id, $is_sandbox);
		$payer   = $parties['payer'];
		$payee   = $parties['payee'];

		// Localized donation title, kept for consistency with the legacy IPN flow
		$item_name = $this->language->lang('PPDE_DONATION_TITLE_HEAD', $this->config['sitename']);

		return $this->build_transaction_data([
			'business'          => $is_sandbox ? $this->config['ppde_sandbox_rest_client_id'] : $this->config['ppde_rest_client_id'],
			'confirmed'         => $payment_status === 'Completed',
			'custom'            => $custom,
			'exchange_rate'     => $breakdown['exchange_rate']['value'] ?? '',
			'first_name'        => $payer['first_name'],
			'item_name'         => $item_name,
			'item_number'       => $custom,
			'last_name'         => $payer['last_name'],
			'mc_currency'       => $resource['amount']['currency_code'] ?? '',
			'mc_gross'          => (float) ($resource['amount']['value'] ?? 0),
			'mc_fee'            => (float) ($breakdown['paypal_fee']['value'] ?? 0),
			'net_amount'        => (float) ($breakdown['net_amount']['value'] ?? 0),
			'payer_email'       => $payer['email'],
			'payer_id'          => $payer['payer_id'],
			'payment_date'      => (int) strtotime($resource['create_time'] ?? 'now') ?: time(),
			'payment_status'    => $payment_status,
			'receiver_id'       => $payee['merchant_id'],
			'receiver_email'    => $payee['email'],
			'residence_country' => $payer['country'],
			'settle_amount'     => (float) ($receivable['value'] ?? 0),
			'settle_currency'   => (string) ($receivable['currency_code'] ?? ''),
			'test_ipn'          => $is_sandbox,
			'txn_id'            => $resource['id'] ?? '',
			'txn_type'          => 'ppde_rest_donation',
		]);
	}

	/**
	 * Fetch payer and payee details by retrieving the related order.
	 * Gracefully returns empty values on any failure.
	 *
	 * @param string $order_id
	 * @param bool   $is_sandbox
	 *
	 * @return array
	 * @access private
	 */
	private function fetch_parties(string $order_id, bool $is_sandbox): array
	{
		$empty = [
			'payer' => ['first_name' => '', 'last_name' => '', 'email' => '', 'payer_id' => '', 'country' => ''],
			'payee' => ['email' => '', 'merchant_id' => ''],
		];

		if ($order_id === '')
		{
			return $empty;
		}

		try
		{
			$orders = $this->client_factory->build($is_sandbox)->getOrdersController();
			$result = $orders->getOrder(['id' => $order_id])->getResult();

			// Collect candidate payer sources, modern first, legacy as fallback,
			// preferring whichever provides an email (needed for guest matching).
			$sources = [];

			$payment_source = method_exists($result, 'getPaymentSource') ? $result->getPaymentSource() : null;
			$paypal_source = ($payment_source && method_exists($payment_source, 'getPaypal')) ? $payment_source->getPaypal() : null;
			if ($paypal_source)
			{
				$sources[] = $paypal_source;
			}

			$legacy_payer = method_exists($result, 'getPayer') ? $result->getPayer() : null;
			if ($legacy_payer)
			{
				$sources[] = $legacy_payer;
			}

			$payer = $empty['payer'];
			foreach ($sources as $source)
			{
				$data = $this->extract_payer_fields($source);

				if ($data['email'] !== '')
				{
					$payer = $data;
					break;
				}

				if ($payer === $empty['payer'])
				{
					$payer = $data;
				}
			}

			return [
				'payer' => $payer,
				'payee' => $this->extract_payee_fields($result),
			];
		}
		catch (\Throwable $e)
		{
			return $empty;
		}
	}

	/**
	 * Handle a refunded or reversed capture: record it as a negative
	 * transaction and adjust the running totals accordingly.
	 *
	 * @param array  $resource       The webhook "resource" (a refund object)
	 * @param string $payment_status 'Refunded' or 'Reversed'
	 * @param bool   $is_sandbox
	 *
	 * @return bool
	 * @access private
	 */
	private function handle_capture_refunded(array $resource, string $payment_status, bool $is_sandbox): bool
	{
		$refund_id = $resource['id'] ?? '';

		if ($refund_id === '' || $this->already_processed($refund_id))
		{
			return true;
		}

		try
		{
			$parent_txn_id = $this->extract_parent_capture_id($resource);
			$custom = $this->resolve_refund_custom($resource, $parent_txn_id);

			$data = $this->map_refund($resource, $parent_txn_id, $custom, $payment_status, $is_sandbox);

			$this->ppde_actions->log_to_db($data);
			$this->do_refund_actions($is_sandbox);

			$this->log->add('admin', ANONYMOUS, $this->user->ip, 'LOG_PPDE_REFUND_PROCESSED', time(), [$refund_id, $parent_txn_id]);
		}
		catch (transaction_exception $e)
		{
			return true;
		}
		catch (\Throwable $e)
		{
			$this->log->add('critical', ANONYMOUS, $this->user->ip, 'LOG_PPDE_WEBHOOK_PROCESS_ERROR', time(), [$refund_id, $e->getMessage()]);
			return false;
		}

		return true;
	}

	/**
	 * Extract the parent capture ID from a refund resource (link rel="up").
	 *
	 * @param array $resource
	 *
	 * @return string
	 * @access private
	 */
	private function extract_parent_capture_id(array $resource): string
	{
		if (empty($resource['links']) || !is_array($resource['links']))
		{
			return '';
		}

		foreach ($resource['links'] as $link)
		{
			if (($link['rel'] ?? '') === 'up' && !empty($link['href']))
			{
				$path = parse_url($link['href'], PHP_URL_PATH) ?: $link['href'];
				$segments = explode('/', rtrim($path, '/'));

				return (string) end($segments);
			}
		}

		return '';
	}

	/**
	 * Resolve the "custom" value to attach to the refund transaction.
	 * Prefers the parent capture's custom (most reliable for the board user_id),
	 * then falls back to the custom_id copied onto the refund resource.
	 *
	 * @param array  $resource
	 * @param string $parent_txn_id
	 *
	 * @return string
	 * @access private
	 */
	private function resolve_refund_custom(array $resource, string $parent_txn_id): string
	{
		if ($parent_txn_id !== '')
		{
			$custom = $this->ppde_operator_transaction->get_custom_by_txn_id($parent_txn_id);

			if ($custom !== '')
			{
				return $custom;
			}
		}

		return (string) ($resource['custom_id'] ?? '');
	}

	/**
	 * Map a refund/reversal resource to the PPDE transaction array.
	 * Amounts are negated so the existing actions decrement the totals.
	 *
	 * @param array  $resource
	 * @param string $parent_txn_id
	 * @param string $custom
	 * @param string $payment_status 'Refunded' or 'Reversed'
	 * @param bool   $is_sandbox
	 *
	 * @return array
	 * @access private
	 */
	private function map_refund(array $resource, string $parent_txn_id, string $custom, string $payment_status, bool $is_sandbox): array
	{
		// NB: refunds use "seller_payable_breakdown" (not "receivable").
		$breakdown = $resource['seller_payable_breakdown'] ?? [];

		$gross = (float) ($resource['amount']['value'] ?? ($breakdown['gross_amount']['value'] ?? 0));
		$fee = (float) ($breakdown['paypal_fee']['value'] ?? 0);
		$net = (float) ($breakdown['net_amount']['value'] ?? ($gross - $fee));
		$currency = $resource['amount']['currency_code'] ?? ($breakdown['gross_amount']['currency_code'] ?? '');

		return $this->build_transaction_data([
			'business'       => $is_sandbox ? $this->config['ppde_sandbox_rest_client_id'] : $this->config['ppde_rest_client_id'],
			'confirmed'      => true,
			'custom'         => $custom,
			'exchange_rate'  => $breakdown['exchange_rate']['value'] ?? '',
			'item_number'    => $custom,
			'mc_currency'    => $currency,
			'mc_gross'       => -1 * $gross,
			'mc_fee'         => -1 * $fee,
			'net_amount'     => -1 * $net,
			'parent_txn_id'  => $parent_txn_id,
			'payment_date'   => (int) strtotime($resource['create_time'] ?? 'now') ?: time(),
			'payment_status' => $payment_status,
			'test_ipn'       => $is_sandbox,
			'txn_id'         => $resource['id'] ?? '',
			'txn_type'       => 'ppde_rest_refund',
		]);
	}

	/**
	 * Adjust running totals after a refund/reversal has been logged.
	 *
	 * @param bool $is_sandbox
	 *
	 * @return void
	 * @access private
	 */
	private function do_refund_actions(bool $is_sandbox): void
	{
		$this->ppde_actions->set_ipn_test_properties($is_sandbox);
		$this->ppde_actions->is_donor_is_member();

		$this->ppde_actions->update_overview_stats();
		$this->ppde_actions->update_raised_amount();

		if (!$is_sandbox && $this->ppde_actions->get_donor_is_member())
		{
			// A refund can only decrease the total: never add to the group,
			// only remove the donor if they dropped below the configured minimum.
			$this->ppde_actions->update_donor_stats();
			$this->ppde_actions->donors_group_user_remove();
		}
	}

	/**
	 * Handle a denied capture: record it for traceability without running any
	 * post-action, since the funds were never received.
	 *
	 * A previously pending row is upgraded to "Denied"; a brand-new denial is
	 * inserted. A capture that already completed is never downgraded here
	 * (a settled capture is reverted through REVERSED instead).
	 *
	 * @param array $resource
	 * @param bool  $is_sandbox
	 *
	 * @return bool
	 * @access private
	 */
	private function handle_capture_denied(array $resource, bool $is_sandbox): bool
	{
		$txn_id = $resource['id'] ?? '';

		if ($txn_id === '')
		{
			return true;
		}

		$current_status = $this->ppde_operator_transaction->get_payment_status_by_txn_id($txn_id);

		if ($current_status === 'Denied' || $current_status === 'Completed')
		{
			return true;
		}

		try
		{
			$data = $this->map_capture($resource, 'Denied', $is_sandbox);
			$this->ppde_actions->log_to_db($data);

			$this->log->add('admin', ANONYMOUS, $this->user->ip, 'LOG_PPDE_CAPTURE_DENIED', time(), [$txn_id]);
		}
		catch (transaction_exception $e)
		{
			return true;
		}
		catch (\Throwable $e)
		{
			$this->log->add('critical', ANONYMOUS, $this->user->ip, 'LOG_PPDE_WEBHOOK_PROCESS_ERROR', time(), [$txn_id, $e->getMessage()]);
			return false;
		}

		return true;
	}
}
