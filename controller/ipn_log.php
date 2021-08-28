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
use phpbb\filesystem\filesystem_interface;
use phpbb\path_helper;

class ipn_log
{
	protected $config;
	protected $filesystem;
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
	private $report_data = ['remote_uri'             => '',
							'remote_type'            => '',
							'remote_report_response' => '',
							'remote_response_status' => '',
							'remote_data'            => [],
	];

	/**
	 * Constructor
	 *
	 * @param config               $config               Config object
	 * @param filesystem_interface $filesystem           phpBB's filesystem service
	 * @param path_helper          $path_helper          Path helper object
	 * @param main_controller      $ppde_controller_main Main controller
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		filesystem_interface $filesystem,
		path_helper $path_helper,
		main_controller $ppde_controller_main
	)
	{
		$this->config = $config;
		$this->filesystem = $filesystem;
		$this->path_helper = $path_helper;
		$this->ppde_controller_main = $ppde_controller_main;

		$this->log_path_filename = $this->path_helper->get_phpbb_root_path() . 'store/ext/ppde/ppde_tx_' . time() . '_' . unique_id() . '.log';
	}

	/**
	 * @return bool
	 */
	public function is_use_log_error(): bool
	{
		return $this->use_log_error;
	}

	/**
	 * Set the property
	 *
	 * @param bool $use_log_error
	 */
	public function set_use_log_error($use_log_error): void
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
	public function set_report_data($remote_uri, $remote_type, $remote_report_response, $remote_response_status, $remote_data): void
	{
		$this->report_data = [
			'remote_uri'             => (string) $remote_uri,
			'remote_type'            => (string) $remote_type,
			'remote_report_response' => (string) $remote_report_response,
			'remote_response_status' => (string) $remote_response_status,
			'remote_data'            => (array) $remote_data,
		];
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
	public function log_error($message, $log_in_file = false, $exit = false, $error_type = E_USER_NOTICE, $args = []): void
	{
		$error_timestamp = date('d-M-Y H:i:s Z');

		$backtrace = '';
		if (!empty($this->config['ppde_sandbox_enable']) && $this->ppde_controller_main->use_ipn())
		{
			$backtrace = get_backtrace();
		}

		if (count($args))
		{
			$message .= '<br>[args]<br>';
			foreach ($args as $key => $value)
			{
				$value = urlencode($value);
				$message .= $key . ' = ' . $value . ';<br>';
			}
		}

		if ($log_in_file)
		{
			$message_in_file = str_replace('<br>', "\n", $message);
			$backtrace = html_entity_decode(strip_tags(str_replace(['<br />', '<br>', "\n\n"], "\n", $backtrace)));
			$this->set_output_handler(new \skouat\ppde\output_handler\log_wrapper_output_handler($this->filesystem, $this->log_path_filename));
			$this->output_handler->write(sprintf('[%s] %s %s', $error_timestamp, $message_in_file, $backtrace));
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
	public function set_output_handler(\skouat\ppde\output_handler\log_wrapper_output_handler $handler): void
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
	public function get_text_report(): string
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
	private function text_report_insert_line(&$r = ''): void
	{
		$r .= str_repeat('-', 80);
	}

	/**
	 * Insert remote data args into the text report
	 *
	 * @param string $r
	 *
	 * @return void
	 * @access private
	 */
	private function text_report_insert_args(&$r = ''): void
	{
		foreach ($this->report_data['remote_data'] as $key => $value)
		{
			$r .= str_pad($key, 25) . $value . "\n";
		}
	}
}
