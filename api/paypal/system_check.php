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
use skouat\ppde\controller\extension_manager;

/**
 * Detects server prerequisites for the extension (cURL availability/version).
 *
 * Replaces the cURL-detection part of the former ipn_paypal controller.
 * The IPN/WPS postback, TLS 1.2 check and remote-URI handling are gone:
 * the REST API manages its own OAuth and base URLs (via client_factory),
 * and the webhook uses OpenSSL for signature verification.
 */
class system_check
{
	/** @var config */
	protected $config;

	/** @var extension_manager */
	protected $ppde_ext_manager;

	/** @var string */
	private $response = '';

	/** @var string */
	private $response_status = '';

	/**
	 * Constructor
	 *
	 * @param config            $config
	 * @param extension_manager $ppde_ext_manager
	 *
	 * @access public
	 */
	public function __construct(config $config, extension_manager $ppde_ext_manager)
	{
		$this->config = $config;
		$this->ppde_ext_manager = $ppde_ext_manager;
	}

	/**
	 * Detect cURL availability and store it in config.
	 *
	 * @return void
	 * @access public
	 */
	public function set_remote_detected(): void
	{
		$ext_meta = $this->ppde_ext_manager->get_ext_meta();

		$this->config->set('ppde_curl_detected', $this->check_curl($ext_meta['extra']['version-check']['host']));
	}

	/**
	 * Check whether cURL can reach a host.
	 *
	 * @param string $host
	 *
	 * @return bool
	 * @access public
	 */
	public function check_curl($host): bool
	{
		if (function_exists('curl_init') && function_exists('curl_exec'))
		{
			$ch = curl_init($host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$this->response = curl_exec($ch);
			$this->response_status = (string) curl_getinfo($ch, CURLINFO_HTTP_CODE);

			curl_close($ch);

			return $this->response !== false || $this->response_status !== '0';
		}

		return false;
	}

	/**
	 * Store cURL version information in config.
	 *
	 * @return void
	 * @access public
	 */
	public function set_curl_info(): void
	{
		if ($curl_info = $this->check_curl_info())
		{
			$this->config->set('ppde_curl_version', $curl_info['version']);
			$this->config->set('ppde_curl_ssl_version', $curl_info['ssl_version']);
		}
	}

	/**
	 * Get cURL version array if available.
	 *
	 * @return array|bool
	 * @access public
	 */
	public function check_curl_info()
	{
		if (function_exists('curl_version'))
		{
			return curl_version();
		}

		return false;
	}
}
