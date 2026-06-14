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
	public function is_valid($raw_body, array $headers, $webhook_id): bool
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

		// Security: the certificate MUST come from a paypal.com domain over HTTPS.
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

		// Signed string = transmissionId|transmissionTime|webhookId|crc32(rawBody)
		$expected_data = implode('|', [
			$headers['transmission_id'],
			$headers['transmission_time'],
			$webhook_id,
			crc32($raw_body),
		]);

		$verified = openssl_verify(
			$expected_data,
			base64_decode($headers['transmission_sig']),
			$public_key,
			$this->map_algo($headers['auth_algo'])
		);

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

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
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

	/**
	 * Map the PayPal auth algorithm header to an OpenSSL algorithm constant.
	 *
	 * @param string $auth_algo e.g. "SHA256withRSA"
	 *
	 * @return int
	 * @access private
	 */
	private function map_algo(string $auth_algo): int
	{
		// PayPal currently always signs with SHA256withRSA.
		return OPENSSL_ALGO_SHA256;
	}
}
