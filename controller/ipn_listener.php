<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Special Thanks to the following individuals for their inspiration:
 *    David Lewis (Highway of Life) http://startrekguide.com
 *    Micah Carrick (email@micahcarrick.com) http://www.micahcarrick.com
 */

namespace skouat\ppde\controller;

define('ASCII_RANGE', '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

class ipn_listener
{
	protected $config;
	protected $ppde_controller_main;
	protected $request;
	protected $user;
	protected $root_path;
	/**
	 * Args from PayPal notify return URL
	 *
	 * @var string
	 */
	private $args_return_uri = array();
	/**
	 * @var array
	 */
	private $curl_fsock = array();
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
	 * The amount of time, in seconds, to wait for the PayPal server to respond
	 * before timing out. Default 30 seconds.
	 *
	 * @var int
	 */
	private $timeout = 30;
	/**
	 * Data from PayPal transaction
	 *
	 * @var array
	 */
	private $transaction_data = array();
	/**
	 * PayPal URL
	 * Could be Sandbox URL ou normal PayPal URL.
	 *
	 * @var string
	 */
	private $u_paypal = '';
	/**
	 * If true, the error are logged into /store/transaction.log.
	 * If false, error aren't logged. Default false.
	 *
	 * @var boolean
	 */
	private $use_log_error = false;
	/**
	 * Transaction status
	 *
	 * @var boolean
	 */
	private $verified = false;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                    $config               Config object
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main Main controller object
	 * @param \phpbb\request\request                  $request              Request object
	 * @param \phpbb\user                             $user                 User object
	 * @param string                                  $root_path            phpBB root path
	 *
	 * @return \skouat\ppde\controller\ipn_listener
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \skouat\ppde\controller\main_controller $ppde_controller_main, \phpbb\request\request $request, \phpbb\user $user, $root_path)
	{
		$this->config = $config;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->request = $request;
		$this->user = $user;
		$this->root_path = $root_path;
	}

	public function handle()
	{
		// Set IPN logging
		$this->use_log_error = (bool) $this->config['ppde_ipn_logging'];

		// Set the property 'curl_fsock' to determine which remote connection to use to contact PayPal
		$this->set_curl_fsock();

		if ($this->get_curl_fsock() == 'none')
		{
			$this->log_error($this->user->lang['NO_CONNECTION_DETECTED'], true, true, E_USER_WARNING);
		}

		// Check the transaction returned by PayPal
		$this->validate_transaction();
	}

	/**
	 * Set property 'use_curl' to determine if we use cURL or fsockopen().
	 * If none is available we set the property 'no_curl_fsock' to true.
	 *
	 * @return null
	 * @access private
	 */
	private function set_curl_fsock()
	{
		if ($this->config['ppde_curl_detected'])
		{
			$this->curl_fsock = array(
				'curl'  => true,
				'fsock' => false,
				'none'  => false,
			);
		}
		else if ($this->config['ppde_fsock_detected'])
		{
			$this->curl_fsock = array(
				'curl'  => false,
				'fsock' => true,
				'none'  => false,
			);
		}
		else
		{
			$this->curl_fsock = array(
				'curl'  => false,
				'fsock' => false,
				'none'  => true,
			);
		}
	}

	/**
	 * Get the service that will be used to contact PayPal: cURL or fsockopen()
	 * Return the name of the key that is set to true.
	 *
	 * @return string
	 * @access private
	 */
	private function get_curl_fsock()
	{
		return array_search(true, $this->curl_fsock);
	}


	/**
	 * Log error messages
	 *
	 * @param string $message
	 * @param bool   $log_in_file
	 * @param bool   $exit
	 * @param int    $error_type
	 * @param array  $args
	 *
	 * @return null
	 * @access private
	 */
	private function log_error($message, $log_in_file = false, $exit = false, $error_type = E_USER_NOTICE, $args = array())
	{
		$error_timestamp = date('d-M-Y H:i:s Z');

		$backtrace = '';
		if ($this->ppde_controller_main->use_sandbox())
		{
			$backtrace = get_backtrace();
			$backtrace = html_entity_decode(strip_tags(str_replace(array('<br />', "\n\n"), "\n", $backtrace)));
		}

		$message = str_replace('<br />', ';', $message);

		if (sizeof($args))
		{
			$message .= "\n[args]\n";
			foreach ($args as $key => $value)
			{
				$value = urlencode($value);
				$message .= $key . ' = ' . $value . ";\n";
			}
			unset($value);
		}

		if ($log_in_file)
		{
			error_log(sprintf('[%s] %s %s', $error_timestamp, $message, $backtrace), 3, $this->root_path . 'store/ppde_transaction.log');
		}

		if ($exit)
		{
			trigger_error($message, $error_type);
		}
	}


	/**
	 * Post Data back to PayPal to validate the authenticity of the transaction.
	 */
	private function validate_transaction()
	{
		// Request and populate $this->transaction_data
		$this->get_post_data($this->transaction_vars_list());

		if ($this->validate_post_data() == false)
		{
			// The minimum required checks are not met
			// So we force to log collected data in /store/ppde_transaction.log
			$this->log_error($this->user->lang['INVALID_RESPONSE_STATUS'], true, true, E_USER_NOTICE, array($this->transaction_data));
		}

		$decode_ary = array('receiver_email', 'payer_email', 'payment_date', 'business');
		foreach ($decode_ary as $key)
		{
			$this->transaction_data[$key] = urldecode($this->transaction_data[$key]);
		}

		$this->set_args_return_uri();

		// Get PayPal or Sandbox URL
		$this->u_paypal = $this->ppde_controller_main->get_paypal_url((bool) $this->transaction_data['test_ipn']);

		$this->initiate_paypal_connection();

		if (strpos($this->response_status, '200') === false)
		{
			$this->log_error($this->user->lang['INVALID_RESPONSE_STATUS'], $this->use_log_error, true, E_USER_NOTICE, array($this->response_status));
		}

		// the item number contains the user_id and the payment time in timestamp format
		$this->extract_item_number_data();

		$this->check_response();
	}

	/**
	 * Get data from $_POST
	 *
	 * @param array $data_ary
	 *
	 * @return array
	 */
	private function get_post_data($data_ary = array())
	{
		$post_data = array();

		if (sizeof($data_ary))
		{
			foreach ($data_ary as $key => $default)
			{
				if (is_array($default))
				{
					$this->transaction_data[$key] = $this->request->variable($key, (string) $default[0], (bool) $default[1]);
				}
				else
				{
					$this->transaction_data[$key] = $this->request->variable($key, $default);
				}
			}
		}
		else
		{
			foreach ($this->request->variable_names(\phpbb\request\request_interface::POST) as $key)
			{
				$post_data[$key] = $this->request->variable($key, '', true);
			}
		}

		return $post_data;
	}

	/**
	 * Setup the data list with default values.
	 *
	 * @return array
	 * @access private
	 */
	private function transaction_vars_list()
	{
		return array(
			'receiver_id'       => '', // Secure Merchant Account ID
			'receiver_email'    => '', // Merchant e-mail address
			'residence_country' => '', // Merchant country code
			'business'          => '', // Primary merchant e-mail address

			'confirmed'         => false, // used to check if the payment is confirmed
			'test_ipn'          => false, // used when transaction come from Sandbox platform
			'txn_id'            => '', // Transaction ID
			'txn_type'          => '', // Transaction type - Should be: 'send_money'
			'parent_txn_id'     => '', // Transaction ID

			'payer_email'       => '', // PayPal sender email address
			'payer_id'          => '', // PayPal sender ID
			'payer_status'      => 'unverified', // PayPal sender status (verified, unverified?)
			'first_name'        => array('', true), // First name of sender
			'last_name'         => array('', true), // Last name of sender

			'item_name'         => array('', true), // Equal to: $this->config['sitename']
			'item_number'       => '', // Equal to: 'uid_' . $this->user->data['user_id'] . '_' . time()
			'mc_currency'       => '', // Currency
			'mc_gross'          => 0, // Amt received (before fees)
			'mc_fee'            => 0, // Amt of fees
			'payment_date'      => '', // Payment Date/Time EX: '19:08:04 Oct 03, 2007 PDT'
			'payment_status'    => '', // eg: 'Completed'
			'payment_type'      => '', // Payment type
			'settle_amount'     => 0, // Amt received after currency conversion (before fees)
			'settle_currency'   => '', // Currency of 'settle_amount'
			'exchange_rate'     => '', // Exchange rate used if a currency conversion occurred
		);
	}

	/**
	 * Check if some settings are valid.
	 *
	 * @return bool
	 * @access private
	 */
	private function validate_post_data()
	{
		$check = array();
		$check[] = $this->is_valid_txn_id();
		$check[] = $this->check_account_id();

		return (bool) array_product($check);
	}

	/**
	 * Check if txn_id is not_empty
	 * Return false if txn_id is not empty
	 *
	 * @return bool
	 * @access private
	 */
	private function is_valid_txn_id()
	{
		if (empty($this->transaction_data['txn_id']))
		{
			$this->log_error($this->user->lang['INVALID_TRANSACTION_RECORD'], $this->use_log_error, true, E_USER_NOTICE, $this->transaction_data);
		}
		else
		{
			return $this->only_ascii($this->transaction_data['txn_id']);
		}

		return false;
	}

	/**
	 * Check if txn_id contains only ASCII chars.
	 * Return false if it contains non ASCII chars.
	 *
	 * @param $value
	 *
	 * @return bool
	 * @access private
	 */
	private function only_ascii($value)
	{
		// we ensure that the txn_id (transaction ID) contains only ASCII chars...
		$pos = strspn($value, ASCII_RANGE);
		$len = strlen($value);

		if ($pos != $len)
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if account_id
	 *
	 * @return bool
	 * @access private
	 */
	private function check_account_id()
	{
		$account_value = $this->ppde_controller_main->use_sandbox() ? $this->config['ppde_account_id'] : $this->config['ppde_sandbox_address'];

		if ($this->only_ascii($account_value))
		{
			return $account_value == $this->transaction_data['receiver_id'];
		}
		else
		{
			return $account_value == $this->transaction_data['receiver_email'];
		}
	}

	/**
	 * Get all args for construct the return URI
	 *
	 * @return null
	 * @access private
	 */
	private function set_args_return_uri()
	{
		$values = array();
		// Add the cmd=_notify-validate for PayPal
		$this->args_return_uri = 'cmd=_notify-validate';

		// Grab the post data form and set in an array to be used in the URI to PayPal
		foreach ($this->get_post_data() as $key => $value)
		{
			$encoded = urlencode(stripslashes($value));
			$values[] = $key . '=' . $encoded;

			$this->transaction_data[$key] = $value;
		}

		// implode the array into a string URI
		$this->args_return_uri .= '&' . implode('&', $values);
	}

	/**
	 * Select the appropriate method to communicate with PayPal
	 * In first, we use cURL. If it is not available we try with fsockopen()
	 *
	 * @return null
	 * @access private
	 */
	private function initiate_paypal_connection()
	{
		if ($this->curl_fsock['curl'])
		{
			$this->curl_post($this->args_return_uri);
		}
		else if ($this->curl_fsock['fsock'])
		{
			$this->fsock_post($this->args_return_uri);
		}
		else
		{
			$this->log_error($this->user->lang['NO_CONNECTION_DETECTED'], $this->use_log_error, true, E_USER_ERROR, $this->transaction_data);
		}
	}

	/**
	 * Post Back Using cURL
	 *
	 * Sends the post back to PayPal using the cURL library. Called by
	 * the validate_transaction() method if the use_curl property is true. Throws an
	 * exception if the post fails. Populates the response, response_status,
	 * and post_uri properties on success.
	 *
	 * @param  string $encoded_data The post data as a URL encoded string
	 *
	 * @return null
	 * @access private
	 */
	private function curl_post($encoded_data)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->u_paypal);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		if ($this->use_log_error)
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		}

		$this->response = curl_exec($ch);
		if (curl_errno($ch) != 0)
		{
			// cURL error
			$this->log_error($this->user->lang['CURL_ERROR'] . curl_errno($ch) . ' (' . curl_error($ch) . ')', $this->use_log_error);
			curl_close($ch);
		}
		else
		{
			$this->response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
			curl_close($ch);
		}

		// Split response headers and payload, a better way for strcmp
		$tokens = explode("\r\n\r\n", trim($this->response));
		$this->response = trim(end($tokens));
	}

	/**
	 * Post Back Using fsockopen()
	 *
	 * Sends the post back to PayPal using the fsockopen() function. Called by
	 * the validate_transaction() method if the use_curl property is false.
	 * Throws an exception if the post fails. Populates the response,
	 * response_status, properties on success.
	 *
	 * @param  string $encoded_data The post data as a URL encoded string
	 *
	 * @return null
	 * @access private
	 */
	private function fsock_post($encoded_data)
	{
		$errstr = '';
		$errno = 0;

		$parse_url = parse_url($this->u_paypal);

		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= 'Host: ' . $parse_url['host'] . "\r\n";
		$header .= 'Content-Length: ' . strlen($encoded_data) . "\r\n";
		$header .= "Connection: Close\r\n\r\n";

		$fp = fsockopen('ssl://' . $parse_url['host'], 443, $errno, $errstr, $this->timeout);

		if (!$fp)
		{
			$this->log_error($this->user->lang['FSOCK_ERROR'] . $errno . ' (' . $errstr . ')', $this->use_log_error);
		}
		else
		{
			// Send the data to PayPal
			fputs($fp, $header . $encoded_data);

			// Loop through the response
			while (!feof($fp))
			{
				if (empty($this->response))
				{
					// extract HTTP status from first line
					$this->response = $status = fgets($fp, 1024);
					$this->response_status = trim(substr($status, 9, 4));
				}
				else
				{
					$this->response .= fgets($fp, 1024);
				}
			}

			fclose($fp);
		}
	}

	/**
	 * Retrieve user_id and payment_time from item_number args
	 *
	 * @return null
	 * @access private
	 */
	private function extract_item_number_data()
	{
		list($this->transaction_data['user_id'], $this->transaction_data['payment_time']) = explode('_', substr($this->transaction_data['item_number'], 4));
	}

	/**
	 * Check response returned by PayPal et log errors if there is no valid response
	 * Set true if response is 'VERIFIED'. In other case set to false and log errors
	 *
	 * @return null
	 * @access private
	 */
	private function check_response()
	{
		if ($this->txn_is_verified())
		{
			$this->log_error("DEBUG VERIFIED:\n" . $this->get_text_report(), $this->use_log_error);
			$this->verified = $this->transaction_data['confirmed'] = true;
		}
		else if ($this->txn_is_invalid())
		{
			$this->verified = $this->transaction_data['confirmed'] = false;
			$this->log_error("DEBUG INVALID:\n" . $this->get_text_report(), $this->use_log_error);
		}
		else
		{
			$this->verified = $this->transaction_data['confirmed'] = false;
			$this->log_error("DEBUG OTHER:\n" . $this->get_text_report(), $this->use_log_error);
			$this->log_error($this->user->lang['UNEXPECTED_RESPONSE'], $this->use_log_error, true);
		}
	}

	/**
	 * Check if transaction is VERIFIED for both method: cURL or fsockopen()
	 *
	 * @return bool
	 * @access private
	 */
	private function txn_is_verified()
	{
		return $this->is_curl_strcmp('VERIFIED') || $this->is_fsock_strpos('VERIFIED');
	}

	/**
	 * If cURL is available we use strcmp() to get the Pay
	 *
	 * @param $arg
	 *
	 * @return bool
	 */
	private function is_curl_strcmp($arg)
	{
		return $this->curl_fsock['curl'] && strcmp($this->response, $arg) === 0;
	}

	/**
	 * If fsockopen is available we use strpos()
	 *
	 * @param $arg
	 *
	 * @return bool
	 */
	private function is_fsock_strpos($arg)
	{
		return $this->curl_fsock['fsock'] && strpos($this->response, $arg) !== false;
	}

	/**
	 * Check if transaction is INVALID for both method: cURL or fsockopen()
	 *
	 * @return bool
	 * @access private
	 */
	private function txn_is_invalid()
	{
		return $this->is_curl_strcmp('INVALID') || $this->is_fsock_strpos('INVALID');
	}

	/**
	 * Get Text Report
	 *
	 * Returns a report of the IPN transaction in plain text format. This is
	 * useful in emails to order processors and system administrators. Override
	 * this method in your own class to customize the report.
	 *
	 * @return string
	 * @access private
	 */
	private function get_text_report()
	{
		$r = '';

		// Date and POST url
		$this->text_report_insert_line($r);
		$r .= "\n[" . date('m/d/Y g:i A') . '] - ' . $this->u_paypal . ' ( ' . $this->get_curl_fsock() . " )\n";

		// HTTP Response
		$this->text_report_insert_line($r);
		$r .= "\n" . $this->get_response() . "\n";
		$r .= "\n" . $this->get_response_status() . "\n";
		$this->text_report_insert_line($r);

		// POST vars
		$r .= "\n";
		$this->text_report_insert_args($r);
		$r .= "\n\n";

		return $r;
	}

	/**
	 * Insert hyphens line in the text report
	 *
	 * @param string $r
	 *
	 * @return null
	 * @access private
	 */
	private function text_report_insert_line(&$r = '')
	{
		for ($i = 0; $i < 80; $i++)
		{
			$r .= '-';
		}
	}

	/**
	 * Get Response
	 *
	 * Returns the entire response from PayPal as a string including all the
	 * HTTP headers.
	 *
	 * @return string
	 * @access private
	 */
	private function get_response()
	{
		return $this->response;
	}

	/**
	 * Get Response Status
	 *
	 * Return the HTTP Code from PayPal
	 *
	 * @return string
	 * @access private
	 */
	private function get_response_status()
	{
		return $this->response_status;
	}

	/**
	 * Insert $this->transaction_data args int the text report
	 *
	 * @param string $r
	 *
	 * @return null
	 * @access private
	 */
	private function text_report_insert_args(&$r = '')
	{
		foreach ($this->transaction_data as $key => $value)
		{
			$r .= str_pad($key, 25) . $value . "\n";
		}
	}
}
