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

class ipn_remote
{
	protected $config;
	protected $language;
	protected $ppde_ipn_log;
	/**
	 * @var array
	 */
	private $curl_fsock = array('curl' => false,
								'none' => true);
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
	 * @param \phpbb\config\config            $config       Config object
	 * @param \phpbb\language\language        $language     Language user object
	 * @param \skouat\ppde\controller\ipn_log $ppde_ipn_log IPN log
	 *
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \skouat\ppde\controller\ipn_log $ppde_ipn_log)
	{
		$this->config = $config;
		$this->language = $language;
		$this->ppde_ipn_log = $ppde_ipn_log;
	}

	/**
	 * Select the appropriate method to communicate with PayPal
	 * We use cURL. If it is not available we log an error
	 *
	 * @param string $args_return_uri
	 * @param array  $data
	 *
	 * @return void
	 * @access public
	 */
	public function initiate_paypal_connection($args_return_uri, $data)
	{
		if ($this->curl_fsock['curl'])
		{
			$this->curl_post($args_return_uri);
			$this->response;
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
	 * Throws an exception if the post fails. Populates the response, response_status,
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
	 * Set property 'curl_fsock' to use cURL.
	 * If cURL is not available we use default value of the property 'curl_fsock'.
	 *
	 * @return bool
	 * @access public
	 */
	public function is_remote_detected()
	{
		if ($this->config['ppde_curl_detected'])
		{
			$this->curl_fsock = array(
				'curl' => (bool) true,
				'none' => (bool) false,
			);
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
	public function set_u_paypal($u_paypal)
	{
		$this->u_paypal = (string) $u_paypal;
	}

	/**
	 * Get the service that will be used to contact PayPal
	 * Returns the name of the key that is set to true.
	 *
	 * @return string
	 * @access public
	 */
	public function get_remote_used()
	{
		return array_search(true, $this->curl_fsock);
	}

	/**
	 * Full PayPal response for include in text report
	 *
	 * @return string
	 * @access public
	 */
	public function get_report_response()
	{
		return $this->report_response;
	}

	/**
	 * PayPal response status
	 *
	 * @return string
	 * @access public
	 */
	public function get_response_status()
	{
		return $this->response_status;
	}

	/**
	 * Check if the response status is equal to "200".
	 *
	 * @return bool
	 * @access public
	 */
	public function check_response_status()
	{
		return strpos($this->response_status, '200') === false;
	}

	/**
	 * If cURL is available we use strcmp() to get the Pay
	 *
	 * @param string $arg
	 *
	 * @return bool
	 * @access public
	 */
	public function is_curl_strcmp($arg)
	{
		return $this->curl_fsock['curl'] && (strcmp($this->response, $arg) === 0);
	}
}
