<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
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
 * It centralizes:
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

	/**
	 * Returns the public Client ID for the requested environment.
	 * Safe to expose in templates (unlike the secret).
	 *
	 * @param bool $sandbox
	 *
	 * @return string
	 * @access public
	 */
	public function get_client_id(bool $sandbox): string
	{
		[$client_id] = $this->get_credentials($sandbox);

		return $client_id;
	}

	/**
	 * Test the REST API credentials by requesting an OAuth2 access token.
	 *
	 * This performs a direct, side-effect-free call to PayPal's token endpoint
	 * using the stored credentials for the given environment.
	 *
	 * @param bool $sandbox True to test the Sandbox credentials, false for Live.
	 *
	 * @return array{success: bool, reason: string, http_code: int, detail: string}
	 * @access public
	 */
	public function test_connection(bool $sandbox): array
	{
		[$client_id, $client_secret] = $this->get_credentials($sandbox);

		if (empty($client_id) || empty($client_secret))
		{
			return ['success' => false, 'reason' => 'missing', 'http_code' => 0, 'detail' => ''];
		}

		if (!function_exists('curl_init'))
		{
			return ['success' => false, 'reason' => 'curl', 'http_code' => 0, 'detail' => 'cURL not available'];
		}

		$base = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

		$ch = curl_init($base . '/v1/oauth2/token');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
		]);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_exec($ch);

		$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);

		if ($curl_errno)
		{
			return ['success' => false, 'reason' => 'curl', 'http_code' => 0, 'detail' => $curl_error];
		}

		if ($http_code === 200)
		{
			return ['success' => true, 'reason' => '', 'http_code' => 200, 'detail' => ''];
		}

		if ($http_code === 401)
		{
			return ['success' => false, 'reason' => 'auth', 'http_code' => 401, 'detail' => ''];
		}

		return ['success' => false, 'reason' => 'http', 'http_code' => $http_code, 'detail' => ''];
	}
}
