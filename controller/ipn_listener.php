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

use Symfony\Component\DependencyInjection\ContainerInterface;

class ipn_listener
{
	const ASCII_RANGE = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	/**
	 * Services properties declaration
	 */
	protected $config;
	protected $container;
	protected $dispatcher;
	protected $notification;
	protected $path_helper;
	protected $php_ext;
	protected $ppde_controller_main;
	protected $ppde_controller_transactions_admin;
	protected $request;
	protected $user;

	/**
	 * Args from PayPal notify return URL
	 *
	 * @var string
	 */
	private $args_return_uri = array();
	/**
	 * @var array
	 */
	private $curl_fsock = array('curl'  => false,
								'fsock' => false,
								'none'  => true);
	/**
	 * Main currency data
	 *
	 * @var array
	 */
	private $currency_mc_data;
	/**
	 * Settle currency data
	 *
	 * @var array
	 */
	private $currency_settle_data;
	/**
	 * @var string
	 */
	private $log_path_filename;
	/**
	 * The output handler. A null handler is configured by default.
	 *
	 * @var \skouat\ppde\output_handler\log_wrapper_output_handler
	 */
	public $output_handler;
	/**
	 * @var array|boolean
	 */
	private $payer_data;
	/**
	 * PayPal response (VERIFIED or INVALID)
	 *
	 * @var string
	 */
	private $response = '';
	/**
	 * Full PayPal response for include in text report
	 *
	 * @var string
	 */
	private $report_response = '';
	/**
	 * PayPal response status (code 200 or other)
	 *
	 * @var string
	 */
	private $response_status = '';
	/**
	 * phpBB root path
	 *
	 * @var string
	 */
	private $root_path;
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
	 * If true, the error are logged into /store/ppde_transactions.log.
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
	 * @param \phpbb\config\config                                  $config                             Config object
	 * @param ContainerInterface                                    $container                          Service container interface
	 * @param \phpbb\notification\manager                           $notification                       Notification object
	 * @param \phpbb\path_helper                                    $path_helper                        Path helper object
	 * @param \skouat\ppde\controller\main_controller               $ppde_controller_main               Main controller object
	 * @param \skouat\ppde\controller\admin_transactions_controller $ppde_controller_transactions_admin Admin transactions controller object
	 * @param \phpbb\request\request                                $request                            Request object
	 * @param \phpbb\user                                           $user                               User object
	 * @param \phpbb\event\dispatcher_interface                     $dispatcher                         Dispatcher object
	 * @param string                                                $php_ext                            phpEx
	 *
	 * @return \skouat\ppde\controller\ipn_listener
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, ContainerInterface $container, \phpbb\notification\manager $notification, \phpbb\path_helper $path_helper, \skouat\ppde\controller\main_controller $ppde_controller_main, \skouat\ppde\controller\admin_transactions_controller $ppde_controller_transactions_admin, \phpbb\request\request $request, \phpbb\user $user, \phpbb\event\dispatcher_interface $dispatcher, $php_ext)
	{
		$this->config = $config;
		$this->container = $container;
		$this->notification = $notification;
		$this->path_helper = $path_helper;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->ppde_controller_transactions_admin = $ppde_controller_transactions_admin;
		$this->request = $request;
		$this->user = $user;
		$this->dispatcher = $dispatcher;
		$this->php_ext = $php_ext;

		$this->root_path = $this->path_helper->get_phpbb_root_path();
	}

	public function handle()
	{
		// Set IPN logging
		$this->use_log_error = (bool) $this->config['ppde_ipn_logging'];
		$this->log_path_filename = $this->path_helper->get_phpbb_root_path() . 'store/ext/ppde/ppde_tx_' . time() . '.log';

		// Set the property 'curl_fsock' to determine which remote connection to use to contact PayPal
		$this->is_curl_fsock_detected();

		// if no connection detected, disable IPN, log error and exit code execution
		if ($this->get_curl_fsock() == 'none')
		{
			$this->config->set('ppde_ipn_enable', false);
			$this->log_error($this->user->lang['NO_CONNECTION_DETECTED'], true, true, E_USER_WARNING);
		}

		// Check the transaction returned by PayPal
		$this->validate_transaction();

		$this->log_to_db();

		$this->do_actions();

		// We stop the execution of the code because nothing need to be returned to phpBB.
		// And PayPal need it to terminate properly the IPN process.
		garbage_collection();
		exit_handler();
	}

	/**
	 * Set property 'use_curl' to determine if we use cURL or fsockopen().
	 * If both are not available we use default value of the property 'use_curl'.
	 *
	 * @return void
	 * @access private
	 */
	private function is_curl_fsock_detected()
	{
		// First, we declare fsockopen() as detected if true
		$this->check_curl_fsock_detected('ppde_fsock_detected', false, true, false);

		// Finally to set as default method to use, cURL is the last method initiated.
		$this->check_curl_fsock_detected('ppde_curl_detected', true, false, false);
	}

	/**
	 * @param string $config_name
	 * @param bool   $curl
	 * @param bool   $fsock
	 * @param bool   $none
	 *
	 * @return void
	 * @access private
	 */
	private function check_curl_fsock_detected($config_name, $curl, $fsock, $none)
	{
		if ($this->config[$config_name])
		{
			$this->set_curl_fsock((bool) $curl, (bool) $fsock, (bool) $none);
		}
	}

	/**
	 * Set the property 'curl_fsock'
	 *
	 * @param bool $curl
	 * @param bool $fsock
	 * @param bool $none
	 *
	 * @return void
	 * @access private
	 */
	private function set_curl_fsock($curl = false, $fsock = false, $none = true)
	{
		$this->curl_fsock = array(
			'curl'  => (bool) $curl,
			'fsock' => (bool) $fsock,
			'none'  => (bool) $none,
		);
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
	 * @return void
	 * @access private
	 */
	private function log_error($message, $log_in_file = false, $exit = false, $error_type = E_USER_NOTICE, $args = array())
	{
		$error_timestamp = date('d-M-Y H:i:s Z');

		$backtrace = '';
		if ($this->ipn_use_sandbox())
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
			$this->set_output_handler(new \skouat\ppde\output_handler\log_wrapper_output_handler($this->log_path_filename));

			$this->output_handler->write(sprintf('[%s] %s %s', $error_timestamp, $message, $backtrace));
		}

		if ($exit)
		{
			trigger_error($message, $error_type);
		}
	}

	/**
	 * Set the output handler.
	 *
	 * @param \skouat\ppde\output_handler\log_wrapper_output_handler $handler The output handler
	 */
	public function set_output_handler(\skouat\ppde\output_handler\log_wrapper_output_handler $handler)
	{
		$this->output_handler = $handler;
	}

	/**
	 * Post Data back to PayPal to validate the authenticity of the transaction.
	 *
	 * @return bool
	 * @access private
	 */
	private function validate_transaction()
	{
		// Request and populate $this->transaction_data
		$this->get_post_data($this->transaction_vars_list());

		if ($this->validate_post_data() === false)
		{
			// The minimum required checks are not met
			// So we force to log collected data in /store/ppde_transactions.log
			$this->log_error($this->user->lang['INVALID_RESPONSE_STATUS'], true, true, E_USER_NOTICE, $this->transaction_data);
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

		return $this->check_response();
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
	 * @return array<string,string|false|array<string|boolean>|double>
	 * @access private
	 */
	private function transaction_vars_list()
	{
		return array(
			'business'          => '',              // Primary merchant e-mail address
			'confirmed'         => false,           // used to check if the payment is confirmed
			'exchange_rate'     => '',              // Exchange rate used if a currency conversion occurred
			'first_name'        => array('', true), // First name of sender
			'item_name'         => array('', true), // Equal to: $this->config['sitename']
			'item_number'       => '',              // Equal to: 'uid_' . $this->user->data['user_id'] . '_' . time()
			'last_name'         => array('', true), // Last name of sender
			'mc_currency'       => '',              // Currency
			'mc_gross'          => 0.00,            // Amt received (before fees)
			'mc_fee'            => 0.00,            // Amt of fees
			'parent_txn_id'     => '',              // Transaction ID
			'payer_email'       => '',              // PayPal sender email address
			'payer_id'          => '',              // PayPal sender ID
			'payer_status'      => 'unverified',    // PayPal sender status (verified, unverified?)
			'payment_date'      => '',              // Payment Date/Time EX: '19:08:04 Oct 03, 2007 PDT'
			'payment_status'    => '',              // eg: 'Completed'
			'payment_type'      => '',              // Payment type
			'receiver_id'       => '',              // Secure Merchant Account ID
			'receiver_email'    => '',              // Merchant e-mail address
			'residence_country' => '',              // Merchant country code
			'settle_amount'     => 0.00,            // Amt received after currency conversion (before fees)
			'settle_currency'   => '',              // Currency of 'settle_amount'
			'test_ipn'          => false,           // used when transaction come from Sandbox platform
			'txn_id'            => '',              // Transaction ID
			'txn_type'          => '',              // Transaction type - Should be: 'send_money'
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
		$pos = strspn($value, self::ASCII_RANGE);
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
		$account_value = $this->ipn_use_sandbox() ? $this->config['ppde_sandbox_address'] : $this->config['ppde_account_id'];

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
	 * @return void
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
			$encoded = urlencode($value);
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
	 * @return void
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
	 * @return void
	 * @access private
	 */
	private function curl_post($encoded_data)
	{
		$ch = curl_init($this->u_paypal);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		if ($this->use_log_error)
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		}

		$this->report_response = $this->response = curl_exec($ch);
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
	 * @return void
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

			$this->report_response = $this->response;

			fclose($fp);
		}
	}

	/**
	 * Check response returned by PayPal et log errors if there is no valid response
	 * Set true if response is 'VERIFIED'. In other case set to false and log errors
	 *
	 * @return bool $this->verified
	 * @access private
	 */
	private function check_response()
	{
		if ($this->txn_is_verified())
		{
			$this->verified = $this->transaction_data['confirmed'] = true;
			$this->log_error("DEBUG VERIFIED:\n" . $this->get_text_report(), $this->use_log_error);
		}
		else if ($this->txn_is_invalid())
		{
			$this->verified = $this->transaction_data['confirmed'] = false;
			$this->log_error("DEBUG INVALID:\n" . $this->get_text_report(), $this->use_log_error, true);
		}
		else
		{
			$this->verified = $this->transaction_data['confirmed'] = false;
			$this->log_error("DEBUG OTHER:\n" . $this->get_text_report(), $this->use_log_error);
			$this->log_error($this->user->lang['UNEXPECTED_RESPONSE'], $this->use_log_error, true);
		}

		return $this->verified;
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
	 * @param string $arg
	 *
	 * @return bool
	 * @access private
	 */
	private function is_curl_strcmp($arg)
	{
		return $this->curl_fsock['curl'] && (strcmp($this->response, $arg) === 0);
	}

	/**
	 * If fsockopen is available we use strpos()
	 *
	 * @param string $arg
	 *
	 * @return bool
	 * @access private
	 */
	private function is_fsock_strpos($arg)
	{
		return $this->curl_fsock['fsock'] && strpos($this->response, $arg) !== false;
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
		$r .= "\n" . $this->get_report_response() . "\n";
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
	 * @return void
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
	private function get_report_response()
	{
		return $this->report_response;
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
	 * @return void
	 * @access private
	 */
	private function text_report_insert_args(&$r = '')
	{
		foreach ($this->transaction_data as $key => $value)
		{
			$r .= str_pad($key, 25) . $value . "\n";
		}
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
	 * Log the transaction to the database
	 *
	 * @access private
	 */
	private function log_to_db()
	{
		// Initiate a transaction log entity
		/** @type \skouat\ppde\entity\transactions $entity */
		$entity = $this->container->get('skouat.ppde.entity.transactions');

		// the item number contains the user_id
		$this->extract_item_number_data();

		// set username in extra_data property in $entity
		$user_ary = $this->ppde_controller_transactions_admin->ppde_operator->query_donor_user_data('user', $this->transaction_data['user_id']);
		$entity->set_username($user_ary['username']);

		// list the data to be thrown into the database
		$data = $this->build_data_ary();

		$this->ppde_controller_transactions_admin->set_entity_data($entity, $data);

		$this->submit_data($entity);
	}

	/**
	 * Retrieve user_id from item_number args
	 *
	 * @return void
	 * @access private
	 */
	private function extract_item_number_data()
	{
		list($this->transaction_data['user_id']) = explode('_', substr($this->transaction_data['item_number'], 4), -1);
	}

	/**
	 * Prepare data array() before send it to $entity
	 *
	 * @return array
	 */
	private function build_data_ary()
	{
		return array(
			'business'          => $this->transaction_data['business'],
			'confirmed'         => (bool) $this->transaction_data['confirmed'],
			'exchange_rate'     => $this->transaction_data['exchange_rate'],
			'first_name'        => $this->transaction_data['first_name'],
			'item_name'         => $this->transaction_data['item_name'],
			'item_number'       => $this->transaction_data['item_number'],
			'last_name'         => $this->transaction_data['last_name'],
			'mc_currency'       => $this->transaction_data['mc_currency'],
			'mc_gross'          => floatval($this->transaction_data['mc_gross']),
			'mc_fee'            => floatval($this->transaction_data['mc_fee']),
			'net_amount'        => $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']),
			'parent_txn_id'     => $this->transaction_data['parent_txn_id'],
			'payer_email'       => $this->transaction_data['payer_email'],
			'payer_id'          => $this->transaction_data['payer_id'],
			'payer_status'      => $this->transaction_data['payer_status'],
			'payment_date'      => strtotime($this->transaction_data['payment_date']),
			'payment_status'    => $this->transaction_data['payment_status'],
			'payment_type'      => $this->transaction_data['payment_type'],
			'receiver_id'       => $this->transaction_data['receiver_id'],
			'receiver_email'    => $this->transaction_data['receiver_email'],
			'residence_country' => $this->transaction_data['residence_country'],
			'settle_amount'     => floatval($this->transaction_data['settle_amount']),
			'settle_currency'   => $this->transaction_data['settle_currency'],
			'test_ipn'          => $this->transaction_data['test_ipn'],
			'txn_id'            => $this->transaction_data['txn_id'],
			'txn_type'          => $this->transaction_data['txn_type'],
			'user_id'           => (int) $this->transaction_data['user_id'],
		);
	}

	/**
	 * Returns the net amount of a PayPal Transaction
	 *
	 * @param float $amount
	 * @param float $fee
	 *
	 * @return string
	 */
	private function net_amount($amount, $fee)
	{
		return number_format((float) $amount - (float) $fee, 2);
	}

	/**
	 *  Submit data to the database
	 *
	 * @param \skouat\ppde\entity\transactions $entity The transactions log entity object
	 *
	 * @return void
	 * @access private
	 */
	private function submit_data(\skouat\ppde\entity\transactions $entity)
	{
		if ($this->verified)
		{
			// load the ID of the transaction in the entity
			$entity->set_id($entity->transaction_exists());
			// Add or edit transaction data
			$this->ppde_controller_transactions_admin->add_edit_data($entity);
		}
	}

	/**
	 * Checks if payment_status is completed
	 *
	 * @return bool
	 * @access private
	 */
	private function payment_status_is_completed()
	{
		return $this->transaction_data['payment_status'] === 'Completed';
	}

	/**
	 * Do actions if the transaction is verified
	 *
	 * @return void
	 * @access private
	 */
	private function do_actions()
	{
		// If the transaction is not verified do nothing
		if (!$this->verified)
		{
			return;
		}

		if ($this->payment_status_is_completed())
		{
			$transaction_data = $this->transaction_data;

			/**
			 * Event that is triggered when a transaction has been successfully completed
			 *
			 * @event skouat.ppde.do_actions_completed_before
			 * @var array   transaction_data    Array containing transaction data
			 * @since 1.0.3
			 */
			$vars = array(
				'transaction_data',
			);
			extract($this->dispatcher->trigger_event('skouat.ppde.do_actions_completed_before', compact($vars)));

			$this->transaction_data = $transaction_data;
			unset($transaction_data);

			$this->ppde_controller_transactions_admin->set_ipn_test_properties((bool) $this->transaction_data['test_ipn']);
			$this->ppde_controller_transactions_admin->update_stats((bool) $this->transaction_data['test_ipn']);
			$this->update_raised_amount();

			// If the transaction is not a IPN test do additional actions
			if (!$this->transaction_data['test_ipn'])
			{
				$this->donors_group_user_add();
				$this->notify_donation_received();
			}
		}
	}

	/**
	 * Add donor to the donors group
	 *
	 * @return void
	 * @access private
	 */
	private function donors_group_user_add()
	{
		// we add the user to the donors group
		$can_use_autogroup = $this->can_use_autogroup();
		$group_id = (int) $this->config['ppde_ipn_group_id'];
		$payer_id = (int) $this->payer_data['user_id'];
		$payer_username = $this->payer_data['username'];
		$default_group = $this->config['ppde_ipn_group_as_default'];

		/**
		 * Event to modify data before a user is added to the donors group
		 *
		 * @event skouat.ppde.donors_group_user_add_before
		 * @var bool    can_use_autogroup   Whether or not to add the user to the group
		 * @var int     group_id            The ID of the group to which the user will be added
		 * @var int     payer_id            The ID of the user who will we added to the group
		 * @var string  payer_username      The user name
		 * @var bool    default_group       Whether or not the group should be made default for the user
		 * @since 1.0.3
		 */
		$vars = array(
			'can_use_autogroup',
			'group_id',
			'payer_id',
			'payer_username',
			'default_group',
		);
		extract($this->dispatcher->trigger_event('skouat.ppde.donors_group_user_add_before', compact($vars)));

		if ($can_use_autogroup)
		{
			if (!function_exists('group_user_add'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}

			// add the user to the donors group and set as default.
			group_user_add($group_id, array($payer_id), array($payer_username), get_group_name($group_id), $default_group);
		}
	}

	/**
	 * Checks if all required settings are meet for adding the donor to the group of donors
	 *
	 * @return bool
	 * @access private
	 */
	private function can_use_autogroup()
	{
		return $this->autogroup_is_enabled() && $this->donor_is_member() && $this->payment_status_is_completed();
	}

	/**
	 * Checks if Autogroup could be used
	 *
	 * @return bool
	 * @access private
	 */
	private function autogroup_is_enabled()
	{
		return $this->verified && $this->config['ppde_ipn_enable'] && $this->config['ppde_ipn_autogroup_enable'];
	}

	/**
	 * Returns if donor is member
	 *
	 * @return bool
	 * @access private
	 */
	private function donor_is_member()
	{
		return $this->is_donor_is_member() && !empty($this->payer_data);
	}

	/**
	 * Checks if the donor is a member
	 *
	 * @return bool
	 * @access private
	 */

	private function is_donor_is_member()
	{
		$anonymous_user = false;

		// if the user_id is not anonymous, get the user information (user id, username)
		if ($this->transaction_data['user_id'] != ANONYMOUS)
		{
			$this->payer_data = $this->ppde_controller_transactions_admin->ppde_operator->query_donor_user_data('user', $this->transaction_data['user_id']);

			if (empty($this->payer_data))
			{
				// no results, therefore the user is anonymous...
				$anonymous_user = true;
			}
		}
		else
		{
			// the user is anonymous by default
			$anonymous_user = true;
		}

		if ($anonymous_user)
		{
			// if the user is anonymous, check their PayPal email address with all known email hashes
			// to determine if the user exists in the database with that email
			$this->payer_data = $this->ppde_controller_transactions_admin->ppde_operator->query_donor_user_data('email', $this->transaction_data['payer_email']);

			if (empty($this->payer_data))
			{
				// no results, therefore the user is really a guest
				return false;
			}
		}

		return true;
	}

	/**
	 * Updates the amount of donation raised
	 *
	 * @return void
	 * @access private
	 */
	private function update_raised_amount()
	{
		$ipn_suffix = $this->ppde_controller_transactions_admin->get_suffix_ipn();
		$this->config->set('ppde_raised' . $ipn_suffix, (float) $this->config['ppde_raised' . $ipn_suffix] + (float) $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']), true);
	}

	/**
	 * Notify donors and admin when the donation is received
	 *
	 * @return void
	 * @access private
	 */
	private function notify_donation_received()
	{
		// Initiate a transaction entity
		/** @type \skouat\ppde\entity\transactions $entity */
		$entity = $this->container->get('skouat.ppde.entity.transactions');

		// Initiate a currency entity
		/** @type \skouat\ppde\entity\currency $currency_entity */
		$currency_entity = $this->container->get('skouat.ppde.entity.currency');

		// Set currency data properties
		$this->currency_settle_data = $this->get_currency_data($currency_entity, $entity->get_settle_currency());
		$this->currency_mc_data = $this->get_currency_data($currency_entity, $entity->get_mc_currency());

		$notification_data = array(
			'net_amount'     => $this->ppde_controller_main->currency_on_left(number_format($entity->get_net_amount(), 2), $this->currency_mc_data[0]['currency_symbol'], (bool) $this->currency_mc_data[0]['currency_on_left']),
			'mc_gross'       => $this->ppde_controller_main->currency_on_left(number_format($this->transaction_data['mc_gross'], 2), $this->currency_mc_data[0]['currency_symbol'], (bool) $this->currency_mc_data[0]['currency_on_left']),
			'payer_email'    => $this->transaction_data['payer_email'],
			'payer_username' => $entity->get_username(),
			'settle_amount'  => $this->transaction_data['settle_amount'] ? $this->ppde_controller_main->currency_on_left(number_format($this->transaction_data['settle_amount'], 2), $this->currency_settle_data[0]['currency_symbol'], (bool) $this->currency_settle_data[0]['currency_on_left']) : '',
			'transaction_id' => $entity->get_id(),
			'txn_id'         => $this->transaction_data['txn_id'],
			'user_from'      => $entity->get_user_id(),
		);

		// Send admin notification
		$this->notification->add_notifications('skouat.ppde.notification.type.admin_donation_received', $notification_data);
		// Send donor notification
		$this->notification->add_notifications('skouat.ppde.notification.type.donor_donation_received', $notification_data);
	}

	/**
	 * Get currency data based on currency ISO code
	 *
	 * @param \skouat\ppde\entity\currency $entity The currency entity object
	 * @param string                       $iso_code
	 *
	 * @return array
	 * @access private
	 */
	private function get_currency_data(\skouat\ppde\entity\currency $entity, $iso_code)
	{
		// Retrieve the currency ID for settle
		$entity->data_exists($entity->build_sql_data_exists($iso_code));

		return $this->ppde_controller_main->get_default_currency_data($entity->get_id());
	}

	/**
	 * Check if Sandbox is enabled based on config value
	 *
	 * @return bool
	 * @access private
	 */
	private function ipn_use_sandbox()
	{
		return $this->ppde_controller_main->use_ipn() && !empty($this->config['ppde_sandbox_enable']);
	}
}
