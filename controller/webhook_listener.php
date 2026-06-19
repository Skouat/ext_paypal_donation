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
use phpbb\event\dispatcher_interface;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\user;
use skouat\ppde\actions\core;
use skouat\ppde\api\paypal\client_factory;
use skouat\ppde\api\paypal\webhook_verify;
use skouat\ppde\entity\transactions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receives and processes PayPal webhook notifications.
 *
 * This controller replaces the legacy IPN listener (ipn_listener.php).
 * The donation is recorded in the database here, asynchronously, after the
 * webhook signature has been cryptographically verified.
 */
class webhook_listener
{
	/** @var config */
	protected $config;
	/** @var language */
	protected $language;
	/** @var log */
	protected $log;
	/** @var core */
	protected $ppde_actions;
	/** @var transactions */
	protected $ppde_entity_transaction;
	/** @var webhook_verify */
	protected $webhook_verify;
	/** @var client_factory */
	protected $client_factory;
	/** @var request */
	protected $request;
	/** @var user */
	protected $user;
	/** @var dispatcher_interface */
	protected $dispatcher;

	/**
	 * Constructor
	 *
	 * @param config               $config
	 * @param language             $language
	 * @param \phpbb\log\log       $log
	 * @param core                 $ppde_actions
	 * @param transactions         $ppde_entity_transaction
	 * @param webhook_verify       $webhook_verify
	 * @param client_factory       $client_factory
	 * @param request              $request
	 * @param \phpbb\user          $user
	 * @param dispatcher_interface $dispatcher
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		log $log,
		core $ppde_actions,
		transactions $ppde_entity_transaction,
		webhook_verify $webhook_verify,
		client_factory $client_factory,
		request $request,
		user $user,
		dispatcher_interface $dispatcher
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions = $ppde_actions;
		$this->ppde_entity_transaction = $ppde_entity_transaction;
		$this->webhook_verify = $webhook_verify;
		$this->client_factory = $client_factory;
		$this->request = $request;
		$this->user = $user;
		$this->dispatcher = $dispatcher;
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

		// Determine the environment by verifying against each configured webhook ID.
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

		$this->process_event($event, $environment === 'sandbox');

		// Acknowledge receipt so PayPal stops re-sending the event.
		return new Response('', 200);
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
	 * @return void
	 * @access private
	 */
	private function process_event(array $event, bool $is_sandbox): void
	{
		switch ($event['event_type'])
		{
			case 'PAYMENT.CAPTURE.COMPLETED':
				$this->handle_capture_completed($event['resource'], $is_sandbox);
			break;

			case 'PAYMENT.CAPTURE.REFUNDED':
				$this->handle_capture_refunded($event['resource'], 'Refunded', $is_sandbox);
			break;

			case 'PAYMENT.CAPTURE.REVERSED':
				$this->handle_capture_refunded($event['resource'], 'Reversed', $is_sandbox);
			break;

			// Other events (DENIED, PENDING…) are acknowledged for now.
			default:
			break;
		}
	}

	/**
	 * Handle a completed capture: record the donation and run post-actions.
	 *
	 * @param array $resource
	 * @param bool  $is_sandbox
	 *
	 * @return void
	 * @access private
	 */
	private function handle_capture_completed(array $resource, bool $is_sandbox): void
	{
		$txn_id = $resource['id'] ?? '';

		if ($txn_id === '' || $this->already_processed($txn_id))
		{
			return;
		}

		try
		{
			$data = $this->map_capture($resource, $is_sandbox);

			$transaction_data = $data;
			$vars = ['transaction_data'];
			extract($this->dispatcher->trigger_event('skouat.ppde.do_actions_completed_before', compact($vars)));
			$data = $transaction_data;

			$this->ppde_actions->log_to_db($data);
			$this->do_actions($is_sandbox);
		}
		catch (\Throwable $e)
		{
			$this->log->add('critical', ANONYMOUS, $this->user->ip, 'LOG_PPDE_WEBHOOK_PROCESS_ERROR', time(), [$txn_id, $e->getMessage()]);
		}
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
	 * Map a PAYMENT.CAPTURE.COMPLETED resource to the PPDE transaction array.
	 *
	 * @param array $resource
	 * @param bool  $is_sandbox
	 *
	 * @return array
	 * @access private
	 */
	private function map_capture(array $resource, bool $is_sandbox): array
	{
		$breakdown = $resource['seller_receivable_breakdown'] ?? [];
		$custom    = $resource['custom_id'] ?? '';
		$order_id  = $resource['supplementary_data']['related_ids']['order_id'] ?? '';

		$payer = $this->fetch_payer($order_id, $is_sandbox);

		return [
			'business'          => $is_sandbox ? $this->config['ppde_sandbox_rest_client_id'] : $this->config['ppde_rest_client_id'],
			'confirmed'         => true,
			'custom'            => $custom,
			'exchange_rate'     => $breakdown['exchange_rate']['value'] ?? '',
			'first_name'        => $payer['first_name'],
			'item_name'         => '',
			'item_number'       => $custom,
			'last_name'         => $payer['last_name'],
			'mc_currency'       => $resource['amount']['currency_code'] ?? '',
			'mc_gross'          => (float) ($resource['amount']['value'] ?? 0),
			'mc_fee'            => (float) ($breakdown['paypal_fee']['value'] ?? 0),
			'net_amount'        => (float) ($breakdown['net_amount']['value'] ?? 0),
			'parent_txn_id'     => '',
			'payer_email'       => $payer['email'],
			'payer_id'          => $payer['payer_id'],
			'payer_status'      => $payer['payer_status'],
			'payment_date'      => (int) strtotime($resource['create_time'] ?? 'now'),
			'payment_status'    => 'Completed',
			'payment_type'      => '',
			'memo'              => '',
			'receiver_id'       => '',
			'receiver_email'    => '',
			'residence_country' => $payer['country'],
			'settle_amount'     => 0.0,
			'settle_currency'   => '',
			'test_ipn'          => $is_sandbox,
			'txn_errors'        => '',
			'txn_id'            => $resource['id'] ?? '',
			'txn_type'          => 'ppde_rest_donation',
			'user_id'           => ANONYMOUS, // Overridden by core::extract_user_id() from custom
		];
	}

	/**
	 * Fetch payer details by retrieving the related order.
	 * Gracefully returns empty values on any failure.
	 *
	 * @param string $order_id
	 * @param bool   $is_sandbox
	 *
	 * @return array
	 * @access private
	 */
	private function fetch_payer(string $order_id, bool $is_sandbox): array
	{
		$empty = ['first_name' => '', 'last_name' => '', 'email' => '', 'payer_id' => '', 'payer_status' => '', 'country' => ''];

		if ($order_id === '')
		{
			return $empty;
		}

		try
		{
			$orders = $this->client_factory->build($is_sandbox)->getOrdersController();
			$result = $orders->getOrder(['id' => $order_id])->getResult();

			// Collect the candidate sources, modern first, legacy as fallback.
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

			// Prefer the source that provides an email (needed for guest donor
			// matching); otherwise keep the first non-empty result as fallback.
			$best = $empty;
			foreach ($sources as $source)
			{
				$data = $this->extract_payer($source);

				if ($data['email'] !== '')
				{
					return $data;
				}

				if ($best === $empty)
				{
					$best = $data;
				}
			}

			return $best;
		}
		catch (\Throwable $e)
		{
			return $empty;
		}
	}

	/**
	 * Extract payer fields from a PayPal source object.
	 *
	 * Works with both the modern "payment_source.paypal" object and the legacy
	 * "payer" object, which share the same getters except for the account
	 * identifier (getAccountId() vs getPayerId()).
	 *
	 * @param object $source A PaypalWalletResponse or a (deprecated) Payer object.
	 *
	 * @return array
	 * @access private
	 */
	private function extract_payer($source): array
	{
		$name = method_exists($source, 'getName') ? $source->getName() : null;
		$address = method_exists($source, 'getAddress') ? $source->getAddress() : null;

		// payment_source.paypal exposes getAccountId(); the legacy payer uses getPayerId().
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
			'first_name'   => ($name && method_exists($name, 'getGivenName')) ? (string) $name->getGivenName() : '',
			'last_name'    => ($name && method_exists($name, 'getSurname')) ? (string) $name->getSurname() : '',
			'email'        => method_exists($source, 'getEmailAddress') ? (string) $source->getEmailAddress() : '',
			'payer_id'     => $payer_id,
			'payer_status' => '',
			'country'      => ($address && method_exists($address, 'getCountryCode')) ? (string) $address->getCountryCode() : '',
		];
	}

	/**
	 * Run statistics, auto-group and notification actions after logging.
	 *
	 * @param bool $is_sandbox
	 *
	 * @return void
	 * @access private
	 */
	private function do_actions(bool $is_sandbox): void
	{
		$this->ppde_actions->set_ipn_test_properties($is_sandbox);
		$this->ppde_actions->is_donor_is_member();

		$this->ppde_actions->update_overview_stats();
		$this->ppde_actions->update_raised_amount();

		if (!$is_sandbox)
		{
			$this->ppde_actions->notification->notify_admin_donation_received();

			if ($this->ppde_actions->get_donor_is_member())
			{
				$this->ppde_actions->update_donor_stats();
				$this->ppde_actions->donors_group_user_add();
				$this->ppde_actions->notification->notify_donor_donation_received();
			}
		}
	}

	/**
	 * Handle a refunded or reversed capture: record the negative transaction
	 * and adjust the running totals accordingly.
	 *
	 * @param array  $resource       The webhook "resource" (a refund object)
	 * @param string $payment_status 'Refunded' or 'Reversed'
	 * @param bool   $is_sandbox
	 *
	 * @return void
	 * @access private
	 */
	private function handle_capture_refunded(array $resource, string $payment_status, bool $is_sandbox): void
	{
		$refund_id = $resource['id'] ?? '';

		// Idempotency guard: the refund is stored as its own transaction row.
		if ($refund_id === '' || $this->already_processed($refund_id))
		{
			return;
		}

		try
		{
			// Resolve the parent capture and reuse its "custom" so the donor is
			// matched by core::extract_user_id(). If the parent capture is unknown
			// (e.g. donation made outside this board), the refund is still recorded.
			$parent_txn_id = $this->extract_parent_capture_id($resource);
			$custom = $this->resolve_refund_custom($resource, $parent_txn_id);

			$data = $this->map_refund($resource, $parent_txn_id, $custom, $payment_status, $is_sandbox);

			$this->ppde_actions->log_to_db($data);
			$this->do_refund_actions($is_sandbox);

			$this->log->add('admin', ANONYMOUS, $this->user->ip, 'LOG_PPDE_REFUND_PROCESSED', time(), [$refund_id, $parent_txn_id]);
		}
		catch (\Throwable $e)
		{
			$this->log->add('critical', ANONYMOUS, $this->user->ip, 'LOG_PPDE_WEBHOOK_PROCESS_ERROR', time(), [$refund_id, $e->getMessage()]);
		}
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
			$this->ppde_entity_transaction->set_txn_id($parent_txn_id);
			$parent_id = $this->ppde_entity_transaction->transaction_exists();

			if ($parent_id)
			{
				$this->ppde_entity_transaction->load($parent_id);
				$custom = $this->ppde_entity_transaction->get_custom();

				if ($custom !== '')
				{
					return $custom;
				}
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

		return [
			'business'          => $is_sandbox ? $this->config['ppde_sandbox_rest_client_id'] : $this->config['ppde_rest_client_id'],
			'confirmed'         => true,
			'custom'            => $custom,
			'exchange_rate'     => $breakdown['exchange_rate']['value'] ?? '',
			'first_name'        => '',
			'item_name'         => '',
			'item_number'       => $custom,
			'last_name'         => '',
			'mc_currency'       => $currency,
			'mc_gross'          => -1 * $gross,
			'mc_fee'            => -1 * $fee,
			'net_amount'        => -1 * $net,
			'parent_txn_id'     => $parent_txn_id,
			'payer_email'       => '',
			'payer_id'          => '',
			'payer_status'      => '',
			'payment_date'      => (int) strtotime($resource['create_time'] ?? 'now'),
			'payment_status'    => $payment_status,
			'payment_type'      => '',
			'memo'              => '',
			'receiver_id'       => '',
			'receiver_email'    => '',
			'residence_country' => '',
			'settle_amount'     => 0.0,
			'settle_currency'   => '',
			'test_ipn'          => $is_sandbox,
			'txn_errors'        => '',
			'txn_id'            => $resource['id'] ?? '',
			'txn_type'          => 'ppde_rest_refund',
			'user_id'           => ANONYMOUS, // Overridden by core::extract_user_id() from custom
		];
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
			$this->ppde_actions->update_donor_stats();
			$this->ppde_actions->donors_group_user_remove();
		}
	}
}
