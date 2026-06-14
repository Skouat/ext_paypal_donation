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

use phpbb\config\config;
use phpbb\language\language;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;

/**
 * Builds a fully configured and authenticated PayPal REST API client.
 *
 * This service replaces the low-level cURL/OAuth handling previously done in
 * the IPN PayPal controller. It centralizes:
 *  - OAuth2 Client Credentials authentication (token handling is automatic),
 *  - Sandbox/Live environment switching,
 *  - SDK configuration (timeout, retries).
 */
class client_factory
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/**
	 * Per-request cache of built clients, keyed by environment ('live'|'sandbox').
	 * Avoids rebuilding the client (and re-reading config) on every call.
	 *
	 * @var PaypalServerSdkClient[]
	 */
	private $clients = [];

	/**
	 * Constructor
	 *
	 * @param config   $config   Config object
	 * @param language $language Language object
	 *
	 * @access public
	 */
	public function __construct(config $config, language $language)
	{
		$this->config = $config;
		$this->language = $language;
	}

	/**
	 * Build (or return the cached) PayPal REST client for the requested environment.
	 *
	 * @param bool $sandbox True to target the PayPal Sandbox, false for Live.
	 *
	 * @return PaypalServerSdkClient
	 * @access public
	 */
	public function build(bool $sandbox): PaypalServerSdkClient
	{
		$key = $this->get_env_key($sandbox);

		// Return the already built client for this environment if available
		if (isset($this->clients[$key]))
		{
			return $this->clients[$key];
		}

		[$client_id, $client_secret] = $this->get_credentials($sandbox);

		// Fail fast with a clear message if credentials are missing
		if (empty($client_id) || empty($client_secret))
		{
			trigger_error($this->language->lang('PPDE_REST_CREDENTIALS_MISSING'), E_USER_WARNING);
		}

		$client = PaypalServerSdkClientBuilder::init()
			->clientCredentialsAuthCredentials(
				ClientCredentialsAuthCredentialsBuilder::init($client_id, $client_secret)
			)
			->environment($sandbox ? Environment::SANDBOX : Environment::PRODUCTION)
			->timeout(30)
			->enableRetries(true)
			->numberOfRetries(2)
			->build()
		;

		return $this->clients[$key] = $client;
	}

	/**
	 * Returns the cache key matching the environment.
	 *
	 * @param bool $sandbox
	 *
	 * @return string
	 * @access private
	 */
	private function get_env_key(bool $sandbox): string
	{
		return $sandbox ? 'sandbox' : 'live';
	}

	/**
	 * Returns the appropriate Client ID and Secret for the requested environment.
	 *
	 * @param bool $sandbox
	 *
	 * @return array{0: string, 1: string} [client_id, client_secret]
	 * @access private
	 */
	private function get_credentials(bool $sandbox): array
	{
		if ($sandbox)
		{
			return [
				(string) $this->config['ppde_sandbox_rest_client_id'],
				(string) $this->config['ppde_sandbox_rest_secret'],
			];
		}

		return [
			(string) $this->config['ppde_rest_client_id'],
			(string) $this->config['ppde_rest_secret'],
		];
	}

	/**
	 * Check whether the REST credentials are configured for the given environment.
	 * Use this instead of the old main_controller::is_ipn_requirement_satisfied().
	 *
	 * @param bool $sandbox True to check Sandbox credentials, false for Live.
	 *
	 * @return bool
	 * @access public
	 */
	public function is_configured(bool $sandbox): bool
	{
		[$client_id, $client_secret] = $this->get_credentials($sandbox);

		return !empty($client_id) && !empty($client_secret);
	}
}
