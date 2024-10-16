<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

use phpbb\config\config;

class esi_controller
{
	protected $config;
	protected $ppde_ext_manager;
	private $response = '';
	private $response_status = '';

	/**
	 * Constructor.
	 *
	 * @param config            $config           Configuration object
	 * @param extension_manager $ppde_ext_manager Extension manager object
	 */
	public function __construct(
		config $config,
		extension_manager $ppde_ext_manager
	)
	{
		$this->config = $config;
		$this->ppde_ext_manager = $ppde_ext_manager;
	}

	/**
	 * Check TLS configuration.
	 *
	 * This method checks the TLS configuration by making a cURL request to a
	 * specified TLS host. It decodes the JSON response and compares the TLS version
	 * with the allowed versions defined in the extension metadata. If a match is found,
	 * it updates the 'ppde_tls_detected' configuration value with the detected TLS version.
	 *
	 * @return void
	 */
	public function check_tls(): void
	{
		// Reset settings to false
		$this->config->set('ppde_tls_detected', false);
		$this->response = '';

		$ext_meta = $this->ppde_ext_manager->get_ext_meta();
		$this->check_curl($ext_meta['extra']['security-check']['tls']['tls-host']);

		// Analyse response
		$json = json_decode($this->response, false);

		if ($json !== null && in_array($json->tls_version, $ext_meta['extra']['security-check']['tls']['tls-version'], true))
		{
			$this->config->set('ppde_tls_detected', $json->tls_version);
		}
	}

	/**
	 * Check if cURL is available and make a request to the specified host.
	 *
	 * @param string $host The host to send the request to.
	 *
	 * @return bool True if the request is successful, false otherwise.
	 */
	public function check_curl(string $host): bool
	{
		if ($this->is_curl_loaded())
		{
			return $this->execute_curl_request($host);
		}

		return false;
	}

	/**
	 * Check if the cURL extension is loaded and the curl_init function is available.
	 *
	 * @return bool Returns true if cURL is available, false otherwise.
	 */
	private function is_curl_loaded(): bool
	{
		return extension_loaded('curl') && function_exists('curl_init');
	}

	/**
	 * Execute a cURL request to the specified host and store the response and status.
	 *
	 * @param string $host The host to send the cURL request to.
	 *
	 * @return bool Returns true if the cURL request is successful, false otherwise.
	 */
	private function execute_curl_request(string $host): bool
	{
		$ch = curl_init($host);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$this->response = curl_exec($ch);
		$this->response_status = (string) curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return $this->response !== false || $this->response_status !== '0';
	}

	/**
	 * Determine if the remote server is reachable using cURL.
	 *
	 * This method retrieves the host from the extension metadata and uses the `check_curl()`
	 * method to determine if a connection can be established. It then updates the
	 * 'ppde_curl_detected' configuration value with the check result.
	 *
	 * @return void
	 */
	public function set_remote_detected(): void
	{
		$ext_meta = $this->ppde_ext_manager->get_ext_meta();
		$this->config->set('ppde_curl_detected', $this->check_curl($ext_meta['extra']['version-check']['host']));
	}

	/**
	 * Retrieve and store cURL version information in the configuration.
	 *
	 * This method attempts to retrieve cURL version information using `curl_version()`.
	 * If successful, it updates the 'ppde_curl_version' and 'ppde_curl_ssl_version'
	 * configuration values.
	 *
	 * @return void
	 */
	public function set_curl_info(): void
	{
		// Get cURL version information
		if ($curl_info = $this->check_curl_info())
		{
			$this->config->set('ppde_curl_version', $curl_info['version']);
			$this->config->set('ppde_curl_ssl_version', $curl_info['ssl_version']);
		}
	}

	/**
	 * Check if the `curl_version()` function is available and retrieve cURL version information.
	 *
	 * @return array|bool Returns an array containing cURL version information if available, false otherwise.
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
