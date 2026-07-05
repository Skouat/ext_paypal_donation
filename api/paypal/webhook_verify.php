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

use phpbb\cache\driver\driver_interface as cache_interface;
use phpbb\config\config;

/**
 * Verifies the authenticity of an incoming PayPal webhook notification.
 *
 * The PayPal-PHP-Server-SDK (2.3.0) does NOT provide webhook signature
 * verification, so it is implemented here using offline cryptographic
 * verification (RSA-SHA256), which is faster and has no extra API dependency.
 */
class webhook_verify
{
	use curl_transport;

	/**
	 * The PAYPAL-AUTH-ALGO header is always "SHA256withRSA".
	 */
	private const PAYPAL_AUTH_ALGO = 'SHA256withRSA';
	/** @var cache_interface */
	protected $cache;
	/** @var config */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param cache_interface $cache  Cache driver (used to cache PayPal certificates)
	 * @param config          $config Config object
	 *
	 * @access public
	 */
	public function __construct(cache_interface $cache, config $config)
	{
		$this->cache = $cache;
		$this->config = $config;
	}

	/**
	 * Verify a webhook notification against the given webhook ID.
	 *
	 * @param string $raw_body   The raw (unparsed) request body
	 * @param array  $headers    Normalised PayPal headers (see webhook_listener::collect_headers())
	 * @param string $webhook_id The configured Webhook ID to verify against
	 *
	 * @return bool True if the signature is authentic.
	 * @access public
	 */
	public function is_valid(string $raw_body, array $headers, string $webhook_id): bool
	{
		if (empty($webhook_id))
		{
			return false;
		}

		foreach (['transmission_id', 'transmission_time', 'transmission_sig', 'cert_url', 'auth_algo'] as $key)
		{
			if (empty($headers[$key]))
			{
				return false;
			}
		}

		// PayPal always signs webhooks with SHA256withRSA; reject anything else.
		if ($headers['auth_algo'] !== self::PAYPAL_AUTH_ALGO)
		{
			return false;
		}

		// The certificate MUST come from a paypal.com domain over HTTPS.
		if (!$this->is_paypal_cert_url($headers['cert_url']))
		{
			return false;
		}

		$cert = $this->fetch_cert($headers['cert_url']);
		if ($cert === false)
		{
			return false;
		}

		$public_key = openssl_pkey_get_public($cert);
		if ($public_key === false)
		{
			return false;
		}

		$expected_data = implode('|', [
			$headers['transmission_id'],
			$headers['transmission_time'],
			$webhook_id,
			sprintf('%u', crc32($raw_body)),
		]);

		$verified = openssl_verify(
			$expected_data,
			base64_decode($headers['transmission_sig']),
			$public_key,
			OPENSSL_ALGO_SHA256
		);

		if (PHP_VERSION_ID < 80000 && is_resource($public_key))
		{
			openssl_free_key($public_key);
		}

		return $verified === 1;
	}

	/**
	 * Ensure the certificate URL points to a genuine PayPal HTTPS host.
	 *
	 * @param string $url
	 *
	 * @return bool
	 * @access private
	 */
	private function is_paypal_cert_url(string $url): bool
	{
		$parts = parse_url($url);

		if (empty($parts['scheme']) || empty($parts['host']) || $parts['scheme'] !== 'https')
		{
			return false;
		}

		$host = strtolower($parts['host']);

		return $host === 'paypal.com' || substr($host, -11) === '.paypal.com';
	}

	/**
	 * Fetch (and cache) the PayPal public certificate.
	 *
	 * @param string $url
	 *
	 * @return string|false The certificate contents, or false on failure.
	 * @access private
	 */
	private function fetch_cert(string $url)
	{
		$cache_key = '_ppde_paypal_cert_' . md5($url);

		$cached = $this->cache->get($cache_key);
		if ($cached !== false)
		{
			return $cached;
		}

		if (!function_exists('curl_init'))
		{
			return false;
		}

		$ch = $this->curl_init_secure($url);
		$cert = curl_exec($ch);
		curl_close($ch);

		if ($cert === false || $cert === '')
		{
			return false;
		}

		// PayPal certificates rotate rarely; cache for 24h.
		$this->cache->put($cache_key, $cert, 86400);

		return $cert;
	}
}
