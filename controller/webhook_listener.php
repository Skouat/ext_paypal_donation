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
use phpbb\request\request;
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
	/** @var core */
	protected $ppde_actions;
	/** @var transactions */
	protected $ppde_entity_transaction;
	/** @var webhook_verify */
	protected $webhook_verify;
	/** @var client_factory */
	protected $client_factory;
	/** @var \skouat\ppde\controller\ipn_log */
	protected $ppde_ipn_log;
	/** @var request */
	protected $request;
	/** @var dispatcher_interface */
	protected $dispatcher;

	/**
	 * Constructor
	 *
	 * @param config               $config
	 * @param language             $language
	 * @param core                 $ppde_actions
	 * @param transactions         $ppde_entity_transaction
	 * @param webhook_verify       $webhook_verify
	 * @param client_factory       $client_factory
	 * @param ipn_log              $ppde_ipn_log
	 * @param request              $request
	 * @param dispatcher_interface $dispatcher
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		core $ppde_actions,
		transactions $ppde_entity_transaction,
		webhook_verify $webhook_verify,
		client_factory $client_factory,
		ipn_log $ppde_ipn_log,
		request $request,
		dispatcher_interface $dispatcher
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->ppde_actions = $ppde_actions;
		$this->ppde_entity_transaction = $ppde_entity_transaction;
		$this->webhook_verify = $webhook_verify;
		$this->client_factory = $client_factory;
		$this->ppde_ipn_log = $ppde_ipn_log;
		$this->request = $request;
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
		$this->ppde_ipn_log->set_use_log_error((bool) $this->config['ppde_ipn_logging']);

		$raw_body = $this->get_raw_input();
		$headers = $this->collect_headers();

		// Determine the environment by verifying against each configured webhook ID.
		$environment = $this->resolve_environment($raw_body, $headers);
		if ($environment === null)
		{
			$this->ppde_ipn_log->log_error($this->language->lang('PPDE_WEBHOOK_INVALID_SIGNATURE'), $this->ppde_ipn_log->is_use_log_error());
			return new Response('', 403);
		}

		$event = json_decode($raw_body, true);
		if (!is_array($event) || empty($event['event_type']) || empty($event['resource']))
		{
			return new Response('', 400);
		}

		$this->process_event($event, $environment === 'sandbox');

		// Acknowledge receipt so PayPal stops re-sending the event.
		return new Response('', 200);
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

			// Other events (REFUNDED, REVERSED, DENIED…) are acknowledged for now.
			// Their handling can be implemented in a later iteration.
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

		// Idempotency: ignore re-delivered events to avoid double-counting stats.
		if ($txn_id === '' || $this->already_processed($txn_id))
		{
			return;
		}

		$data = $this->map_capture($resource, $is_sandbox);

		// Preserve backward-compatible event for third-party extensions.
		$transaction_data = $data;
		$vars = ['transaction_data'];
		extract($this->dispatcher->trigger_event('skouat.ppde.do_actions_completed_before', compact($vars)));
		$data = $transaction_data;

		$this->ppde_actions->log_to_db($data);
		$this->do_actions($is_sandbox);
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
			'business'          => $is_sandbox ? (string) $this->config['ppde_sandbox_rest_client_id'] : (string) $this->config['ppde_rest_client_id'],
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

			$payer = $result->getPayer();
			if (!$payer)
			{
				return $empty;
			}

			$name = $payer->getName();
			$address = $payer->getAddress();

			return [
				'first_name'   => $name ? (string) $name->getGivenName() : '',
				'last_name'    => $name ? (string) $name->getSurname() : '',
				'email'        => (string) $payer->getEmailAddress(),
				'payer_id'     => (string) $payer->getPayerId(),
				'payer_status' => '',
				'country'      => $address ? (string) $address->getCountryCode() : '',
			];
		}
		catch (\Throwable $e)
		{
			return $empty;
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
}
