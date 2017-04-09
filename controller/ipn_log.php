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

class ipn_log
{
	protected $config;
	protected $path_helper;
	protected $ppde_controller_main;

	/**
	 * @var string
	 */
	private $log_path_filename;
	/**
	 * The output handler. A null handler is configured by default.
	 *
	 * @var \skouat\ppde\output_handler\log_wrapper_output_handler
	 */
	private $output_handler;
	/**
	 * If true, the error are logged into /store/ppde_transactions.log.
	 * If false, error aren't logged. Default false.
	 *
	 * @var boolean
	 */
	private $use_log_error = false;

	/** @var array */
	private $report_data = array('remote_uri'             => '',
								 'remote_type'            => '',
								 'remote_report_response' => '',
								 'remote_response_status' => '',
								 'remote_data'            => array());

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                    $config               Config object
	 * @param \phpbb\path_helper                      $path_helper          Path helper object
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main Main controller
	 *
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\path_helper $path_helper, \skouat\ppde\controller\main_controller $ppde_controller_main)
	{
		$this->config = $config;
		$this->path_helper = $path_helper;
		$this->ppde_controller_main = $ppde_controller_main;

		$this->log_path_filename = $this->path_helper->get_phpbb_root_path() . 'store/ext/ppde/ppde_tx_' . time() . '_' . unique_id() . '.log';
	}

	/**
	 * @return boolean
	 */
	public function is_use_log_error()
	{
		return $this->use_log_error;
	}

	/**
	 * Set the property
	 *
	 * @param boolean $use_log_error
	 */
	public function set_use_log_error($use_log_error)
	{
		$this->use_log_error = (bool) $use_log_error;
	}

	/**
	 * Set the property
	 *
	 * @param string $remote_uri
	 * @param string $remote_type
	 * @param string $remote_report_response
	 * @param string $remote_response_status
	 * @param array  $remote_data
	 *
	 * @return void
	 * @access public
	 */
	public function set_report_data($remote_uri, $remote_type, $remote_report_response, $remote_response_status, $remote_data)
	{
		$this->report_data = array(
			'remote_uri'             => (string) $remote_uri,
			'remote_type'            => (string) $remote_type,
			'remote_report_response' => (string) $remote_report_response,
			'remote_response_status' => (string) $remote_response_status,
			'remote_data'            => (array) $remote_data,
		);
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
	 * @access public
	 */
	public function log_error($message, $log_in_file = false, $exit = false, $error_type = E_USER_NOTICE, $args = array())
	{
		$error_timestamp = date('d-M-Y H:i:s Z');

		$backtrace = '';
		if ($this->ppde_controller_main->use_ipn() && !empty($this->config['ppde_sandbox_enable']))
		{
			$backtrace = get_backtrace();
			$backtrace = html_entity_decode(strip_tags(str_replace(array('<br>', "\n\n"), "\n", $backtrace)));
		}

		$message = str_replace('<br>', ';', $message);

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
	 * Get Text Report
	 *
	 * Returns a report of the IPN transaction in plain text format. This is
	 * useful in emails to order processors and system administrators. Override
	 * this method in your own class to customize the report.
	 *
	 * @return string
	 * @access public
	 */
	public function get_text_report()
	{
		$r = '';

		// Date and POST url
		$this->text_report_insert_line($r);
		$r .= "\n[" . date('m/d/Y g:i A') . '] - ' . $this->report_data['remote_uri'] . ' ( ' . $this->report_data['remote_type'] . " )\n";

		// HTTP Response
		$this->text_report_insert_line($r);
		$r .= "\n" . $this->report_data['remote_report_response'] . "\n";
		$r .= "\n" . $this->report_data['remote_response_status'] . "\n";
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
	 * Insert $this->transaction_data args int the text report
	 *
	 * @param string $r
	 *
	 * @return void
	 * @access private
	 */
	private function text_report_insert_args(&$r = '')
	{
		foreach ($this->report_data['remote_data'] as $key => $value)
		{
			$r .= str_pad($key, 25) . $value . "\n";
		}
	}
}
