<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
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
	/** @var array Production and Sandbox Postback URL */
	private static $remote_uri = [
		['hostname' => 'www.paypal.com', 'uri' => 'https://www.paypal.com/cgi-bin/webscr', 'type' => 'live'],
		['hostname' => 'www.sandbox.paypal.com', 'uri' => 'https://www.sandbox.paypal.com/cgi-bin/webscr', 'type' => 'sandbox'],
		['hostname' => 'ipnpb.paypal.com', 'uri' => 'https://ipnpb.paypal.com/cgi-bin/webscr', 'type' => 'live'],
		['hostname' => 'ipnpb.sandbox.paypal.com', 'uri' => 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr', 'type' => 'sandbox'],
	];

	protected $config;
	protected $language;
	protected $ppde_ipn_log;
	protected $request;
	/** @var string Args from PayPal notify return URL */
	private $args_return_uri;
	/** @var array */
	private $curl_fsock = ['curl' => false, 'none' => true];

	/** @var array */
	private $postback_args = [];

	/** @var string Full PayPal response for include in text report */
	private $report_response = '';

	/** @var string PayPal response (VERIFIED or INVALID) */
	private $response = '';

	/** @var string PayPal response status (code 200 or other) */
	private $response_status = '';

	/** @var string PayPal URL (Could be Sandbox URL or normal PayPal URL) */
	private $u_paypal = '';

	/**
	 * Constructor
	 *
	 * @param config            $config           Config object
	 * @param language          $language         Language user object
	 * @param ipn_log           $ppde_ipn_log     IPN log
	 * @param request           $request          Request object
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		ipn_log $ppde_ipn_log,
		request $request
	)
	{
		$this->config = $config;
		$this->language = $language;
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
	public function initiate_paypal_connection(array $data): void
	{
		if ($this->curl_fsock['curl'])
		{
			$this->curl_post($this->args_return_uri);
			return;
		}

		$this->log_paypal_connection_error($data);
	}

	/**
	 * Log PayPal connection error
	 *
	 * @param array $data
	 */
	private function log_paypal_connection_error(array $data): void
	{
		$this->ppde_ipn_log->log_error(
			$this->language->lang('NO_CONNECTION_DETECTED'),
			$this->ppde_ipn_log->is_use_log_error(),
			true,
			E_USER_ERROR,
			$data
		);
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
	private function curl_post(string $encoded_data): void
	{
		$ch = $this->init_curl_session($encoded_data);
		$this->valuate_response(curl_exec($ch), $ch);
		if ($this->ppde_ipn_log->is_use_log_error())
		{
			$this->parse_curl_response();
		}
		curl_close($ch);
	}

	/**
	 * Initializes a cURL session with the specified encoded data.
	 *
	 * @param string $encoded_data The encoded data to be sent in the cURL request.
	 *
	 * @return resource Returns a cURL session handle on success, false on failure.
	 * @access private
	 */
	private function init_curl_session(string $encoded_data)
	{
		$ch = curl_init($this->u_paypal);

		curl_setopt_array($ch, [
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS     => $encoded_data,
			CURLOPT_SSLVERSION     => 6,
			CURLOPT_SSL_VERIFYPEER => 1,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FORBID_REUSE   => true,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_HTTPHEADER     => [
				'User-Agent: PHP-IPN-Verification-Script',
				'Connection: Close',
			],
		]);

		if ($this->ppde_ipn_log->is_use_log_error())
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		}

		return $ch;
	}

	/**
	 * Updates the response status and logs any cURL errors.
	 *
	 * @param mixed    $response The response received from the API call.
	 * @param resource $ch       The cURL handle used to make the API call.
	 * @return void
	 */
	private function valuate_response($response, $ch): void
	{
		$this->report_response = $response;
		if (curl_errno($ch) != 0)
		{
			$this->log_curl_error($ch);
		}
		else
		{
			$this->response_status = curl_getinfo($ch)['http_code'];
		}
	}

	/**
	 * Log the error message from a cURL request.
	 *
	 * @param resource $ch The cURL handle.
	 * @return void
	 */
	private function log_curl_error($ch): void
	{
		$this->ppde_ipn_log->log_error(
			$this->language->lang('CURL_ERROR', curl_errno($ch) . ' (' . curl_error($ch) . ')'),
			$this->ppde_ipn_log->is_use_log_error()
		);
	}

	/**
	 * Parses the cURL response and separates the response headers from the payload.
	 *
	 * This method splits the response by the double line-break "\r\n\r\n". It then trims the response headers and
	 * stores the trimmed payload as the new response.
	 *
	 * @access private
	 * @return void
	 */
	private function parse_curl_response(): void
	{
		// Split response headers and payload, a better way for strcmp
		$tokens = explode("\r\n\r\n", trim($this->report_response));
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
	public function set_u_paypal(string $u_paypal): void
	{
		$this->u_paypal = $u_paypal;
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
	 * If cURL is available we use strcmp() to get the PayPal response
	 *
	 * @param string $arg
	 *
	 * @return bool
	 * @access public
	 */
	public function is_curl_strcmp(string $arg): bool
	{
		return $this->curl_fsock['curl'] && (strcmp($this->response, $arg) === 0);
	}

	/**
	 * Get all args and build the return URI
	 *
	 * @return void
	 * @access public
	 */
	public function set_args_return_uri(): void
	{
		// Add the cmd=_notify-validate for PayPal
		$this->args_return_uri = 'cmd=_notify-validate';

		// Grab the post data form and set in an array to be used in the uri to PayPal
		$postback_args = $this->get_postback_args();
		$query_strings = http_build_query($postback_args);

		// Append the uri with the query strings
		$this->args_return_uri .= '&' . $query_strings;
	}

	/**
	 * Sets the postback arguments for the current object.
	 *
	 * This is used to Postback args to PayPal or for tracking errors.
	 * Based on official PayPal IPN class.
	 * Ref. https://github.com/paypal/ipn-code-samples/blob/master/php/PaypalIPN.php#L67-L81
	 *
	 * @return void
	 * @access public
	 */
	public function set_postback_args(): void
	{
		$postback_args = $this->get_decoded_input_params();

		foreach ($postback_args as $key => $value)
		{
			if ($this->is_payment_date_and_has_plus($key, $value))
			{
				$postback_args[$key] = str_replace('+', '%2B', $value);
			}
		}
		$this->postback_args = $postback_args;
	}

	/**
	 * Retrieves the decoded input parameters.
	 *
	 * @return array The input parameters after being decoded.
	 * @access private
	 */
	private function get_decoded_input_params(): array
	{
		parse_str(file_get_contents('php://input'), $params);
		return array_map('urldecode', $params);
	}

	/**
	 * Check if the given key is 'payment_date' and the value contains a '+'.
	 *
	 * @param string $key   The key to check.
	 * @param string $value The value to check.
	 *
	 * @return bool Returns true if the key is 'payment_date' and the value contains a '+', otherwise returns false.
	 * @access private
	 */
	private function is_payment_date_and_has_plus(string $key, string $value): bool
	{
		return $key === 'payment_date' && strpos($value, '+') !== false;
	}

	/**
	 * Retrieves the postback arguments.
	 *
	 * @return array The postback arguments.
	 * @access public
	 */
	public function get_postback_args(): array
	{
		return $this->postback_args;
	}
}
