<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Special Thanks to the following individuals for their inspiration:
 *    David Lewis (Highway of Life) http://startrekguide.com
 *    Micah Carrick (email@micahcarrick.com) http://www.micahcarrick.com
 */

namespace skouat\ppde\controller;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\request\request;

class ipn_paypal
{
	/**
	 * Args from PayPal notify return URL
	 *
	 * @var string
	 */
	private $args_return_uri = [];
	/** Production and Sandbox Postback URL
	 *
	 * @var array
	 */
	private static $remote_uri = [
		['hostname' => 'www.paypal.com', 'uri' => 'https://www.paypal.com/cgi-bin/webscr', 'type' => 'live'],
		['hostname' => 'www.sandbox.paypal.com', 'uri' => 'https://www.sandbox.paypal.com/cgi-bin/webscr', 'type' => 'sandbox'],
		['hostname' => 'ipnpb.paypal.com', 'uri' => 'https://ipnpb.paypal.com/cgi-bin/webscr', 'type' => 'live'],
		['hostname' => 'ipnpb.sandbox.paypal.com', 'uri' => 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr', 'type' => 'sandbox'],
	];

	protected $config;
	protected $language;
	protected $ppde_ext_manager;
	protected $ppde_ipn_log;
	protected $request;
	/**
	 * @var array
	 */
	private $curl_fsock = ['curl' => false, 'none' => true];
	/**
	 * @var array
	 */
	private $postback_args = [];
	/**
	 * Full PayPal response for include in text report
	 *
	 * @var string
	 */
	private $report_response = '';
	/**
	 * PayPal response (VERIFIED or INVALID)
	 *
	 * @var string
	 */
	private $response = '';
	/**
	 * PayPal response status (code 200 or other)
	 *
	 * @var string
	 */
	private $response_status = '';
	/**
	 * PayPal URL
	 * Could be Sandbox URL ou normal PayPal URL.
	 *
	 * @var string
	 */
	private $u_paypal = '';

	/**
	 * Constructor
	 *
	 * @param config            $config           Config object
	 * @param language          $language         Language user object
	 * @param extension_manager $ppde_ext_manager Extension manager object
	 * @param ipn_log           $ppde_ipn_log     IPN log
	 * @param request           $request          Request object
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		extension_manager $ppde_ext_manager,
		ipn_log $ppde_ipn_log,
		request $request
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->ppde_ext_manager = $ppde_ext_manager;
		$this->ppde_ipn_log = $ppde_ipn_log;
		$this->request = $request;
	}

	/**
	 * @return array
	 */
	public static function get_remote_uri(): array
	{
		return self::$remote_uri;
	}

	/**
	 * Initiate communication with PayPal.
	 * We use cURL. If it is not available we log an error.
	 *
	 * @param array $data
	 *
	 * @return void
	 * @access public
	 */
	public function initiate_paypal_connection($data): void
	{
		if ($this->curl_fsock['curl'])
		{
			$this->curl_post($this->args_return_uri);
		}
		else
		{
			$this->ppde_ipn_log->log_error($this->language->lang('NO_CONNECTION_DETECTED'), $this->ppde_ipn_log->is_use_log_error(), true, E_USER_ERROR, $data);
		}
	}

	/**
	 * Post Back Using cURL
	 *
	 * Sends the post back to PayPal using the cURL library. Called by
	 * the validate_transaction() method if the curl_fsock['curl'] property is true.
	 * Throws an exception if the post fails. Populates the response and response_status properties on success.
	 *
	 * @param string $encoded_data The post data as a URL encoded string
	 *
	 * @return void
	 * @access private
	 */
	private function curl_post($encoded_data): void
	{
		$ch = curl_init($this->u_paypal);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_data);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'User-Agent: PHP-IPN-Verification-Script',
			'Connection: Close',
		]);

		if ($this->ppde_ipn_log->is_use_log_error())
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		}

		$this->report_response = $this->response = curl_exec($ch);
		if (curl_errno($ch) != 0)
		{
			// cURL error
			$this->ppde_ipn_log->log_error($this->language->lang('CURL_ERROR', curl_errno($ch) . ' (' . curl_error($ch) . ')'), $this->ppde_ipn_log->is_use_log_error());
		}
		else
		{
			$info = curl_getinfo($ch);
			$this->response_status = $info['http_code'];
		}
		curl_close($ch);

		// Split response headers and payload, a better way for strcmp
		$tokens = explode("\r\n\r\n", trim($this->response));
		$this->response = trim(end($tokens));
	}

	/**
	 * Set property 'curl_fsock' to use cURL based on config settings.
	 * If cURL is not available we use default value of the property 'curl_fsock'.
	 *
	 * @return bool
	 * @access public
	 */
	public function is_remote_detected(): bool
	{
		if ($this->config['ppde_curl_detected'])
		{
			$this->curl_fsock = ['curl' => true, 'none' => false];
		}

		return array_search(true, $this->curl_fsock);
	}

	/**
	 * Set the property '$u_paypal'
	 *
	 * @param string $u_paypal
	 *
	 * @return void
	 * @access public
	 */
	public function set_u_paypal($u_paypal): void
	{
		$this->u_paypal = (string) $u_paypal;
	}

	/**
	 * Get the property '$u_paypal'
	 *
	 * @return string
	 * @access public
	 */
	public function get_u_paypal(): string
	{
		return $this->u_paypal;
	}

	/**
	 * Get the service that will be used to contact PayPal
	 * Returns the name of the key that is set to true.
	 *
	 * @return string
	 * @access public
	 */
	public function get_remote_used(): string
	{
		return array_search(true, $this->curl_fsock);
	}

	/**
	 * Full PayPal response for include in text report
	 *
	 * @return string
	 * @access public
	 */
	public function get_report_response(): string
	{
		return $this->report_response;
	}

	/**
	 * PayPal response status
	 *
	 * @return string
	 * @access public
	 */
	public function get_response_status(): string
	{
		return $this->response_status;
	}

	/**
	 * Check if the response status is equal to "200".
	 *
	 * @return bool
	 * @access public
	 */
	public function check_response_status(): bool
	{
		return $this->response_status != 200;
	}

	/**
	 * If cURL is available we use strcmp() to get the Pay
	 *
	 * @param string $arg
	 *
	 * @return bool
	 * @access public
	 */
	public function is_curl_strcmp($arg): bool
	{
		return $this->curl_fsock['curl'] && (strcmp($this->response, $arg) === 0);
	}

	/**
	 * Check if website use TLS 1.2
	 *
	 * @return void
	 * @access public
	 */
	public function check_tls(): void
	{
		$ext_meta = $this->ppde_ext_manager->get_ext_meta();

		// Reset settings to false
		$this->config->set('ppde_tls_detected', false);
		$this->response = '';

		$this->check_curl($ext_meta['extra']['security-check']['tls']['tls-host']);

		// Analyse response
		$json = json_decode($this->response);

		if ($json !== null && in_array($json->tls_version, $ext_meta['extra']['security-check']['tls']['tls-version']))
		{
			$this->config->set('ppde_tls_detected', true);
		}
	}

	/**
	 * Set config value for cURL
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
	 * Check if cURL is available
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
	 * Set config value for cURL version
	 *
	 * @return void
	 * @access public
	 */
	public function set_curl_info(): void
	{
		// Get cURL version informations
		if ($curl_info = $this->check_curl_info())
		{
			$this->config->set('ppde_curl_version', $curl_info['version']);
			$this->config->set('ppde_curl_ssl_version', $curl_info['ssl_version']);
		}
	}

	/**
	 * Get cURL version if available
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

	/**
	 * Get all args and build the return URI
	 *
	 * @return void
	 * @access public
	 */
	public function set_args_return_uri(): void
	{
		$values = [];
		// Add the cmd=_notify-validate for PayPal
		$this->args_return_uri = 'cmd=_notify-validate';

		// Grab the post data form and set in an array to be used in the URI to PayPal
		foreach ($this->get_postback_args() as $key => $value)
		{
			$values[] = $key . '=' . urlencode($value);
		}

		// Implode the array into a string URI
		$this->args_return_uri .= '&' . implode('&', $values);
	}

	/**
	 * Get $_POST content as is. This is used to Postback args to PayPal or for tracking errors.
	 * Based on official PayPal IPN class (https://github.com/paypal/ipn-code-samples/blob/master/php/PaypalIPN.php)
	 *
	 * @return void
	 * @access public
	 */
	public function set_postback_args(): void
	{
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);

		foreach ($raw_post_array as $keyval)
		{
			$keyval = explode('=', $keyval);
			if (count($keyval) === 2)
			{
				// Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
				if ($keyval[0] === 'payment_date')
				{
					if (substr_count($keyval[1], '+') === 1)
					{
						$keyval[1] = str_replace('+', '%2B', $keyval[1]);
					}
				}
				$this->postback_args[$keyval[0]] = urldecode($keyval[1]);
			}
		}
	}

	/**
	 * @return array
	 */
	public function get_postback_args(): array
	{
		return $this->postback_args;
	}
}
