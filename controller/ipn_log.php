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
use phpbb\filesystem\filesystem_interface;
use phpbb\path_helper;

class ipn_log
{
	protected $config;
	protected $filesystem;
	protected $path_helper;
	protected $ppde_controller_main;

	private $log_path_filename;

	/**
	 * The output handler. A null handler is configured by default.
	 *
	 * @var \skouat\ppde\output_handler\log_wrapper_output_handler
	 */
	private $output_handler;

	/**
	 * If true, errors are logged into /store/ppde_transactions.log.
	 * If false, errors aren't logged. Default is false.
	 *
	 * @var boolean
	 */
	private $use_log_error = false;
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

		$this->log_path_filename = $this->generate_log_filename();
	}

	/**
	 * Generate a unique log filename
	 *
	 * @return string The generated log filename
	 */
	private function generate_log_filename(): string
	{
		$date = new \DateTime();
		return $this->path_helper->get_phpbb_root_path() . 'store/ext/ppde/ppde_tx_' . $date->format('Y-m-d_His_') . unique_id() . '.log';
	}

	/**
	 * Check if error logging is enabled
	 *
	 * @return bool True if error logging is enabled, false otherwise
	 */
	public function is_use_log_error(): bool
	{
		return $this->use_log_error;
	}

	/**
	 * Set whether to use error logging
	 *
	 * @param bool $use_log_error True to enable error logging, false to disable
	 */
	public function set_use_log_error(bool $use_log_error): void
	{
		$this->use_log_error = $use_log_error;
	}

	/**
	 * Set the report data for logging
	 *
	 * @param string $remote_uri             The remote URI
	 * @param string $remote_type            The remote type
	 * @param string $remote_report_response The remote report response
	 * @param string $remote_response_status The remote response status
	 * @param array  $remote_data            The remote data
	 */
	public function set_report_data(string $remote_uri, string $remote_type, string $remote_report_response, string $remote_response_status, array $remote_data): void
	{
		$this->report_data = [
			'remote_uri'             => $remote_uri,
			'remote_type'            => $remote_type,
			'remote_report_response' => $remote_report_response,
			'remote_response_status' => $remote_response_status,
			'remote_data'            => $remote_data,
		];
	}

	/**
	 * Log error messages
	 *
	 * @param string $message     The error message to log
	 * @param bool   $log_in_file Whether to log the error in a file
	 * @param bool   $exit        Whether to exit after logging the error
	 * @param int    $error_type  The type of error (E_USER_NOTICE, E_USER_WARNING, etc.)
	 * @param array  $args        Additional arguments to include in the log
	 */
	public function log_error(string $message, bool $log_in_file = false, bool $exit = false, int $error_type = E_USER_NOTICE, array $args = []): void
	{
		$error_timestamp = date('d-M-Y H:i:s Z');

		$backtrace = '';
		if (!empty($this->config['ppde_sandbox_enable']) && $this->ppde_controller_main->use_ipn())
		{
			$backtrace = get_backtrace();
		}

		$message = $this->prepare_message($message, $args);

		if ($log_in_file)
		{
			$this->write_to_file($error_timestamp, $message, $backtrace);
		}

		if ($exit)
		{
			trigger_error($message, $error_type);
		}
	}

	/**
	 * Prepare the error message
	 *
	 * @param string $message The base error message
	 * @param array  $args    Additional arguments to include in the message
	 * @return string The prepared error message
	 */
	private function prepare_message(string $message, array $args): string
	{
		if (!empty($args))
		{
			$message .= '<br>[args]<br>';
			foreach ($args as $key => $value)
			{
				$value = urlencode($value);
				$message .= $key . ' = ' . $value . ';<br>';
			}
		}
		return $message;
	}

	/**
	 * Write error message to file
	 *
	 * @param string $timestamp The timestamp of the error
	 * @param string $message   The error message
	 * @param string $backtrace The backtrace of the error
	 */
	private function write_to_file(string $timestamp, string $message, string $backtrace): void
	{
		$message_in_file = str_replace('<br>', "\n", $message);
		$backtrace = html_entity_decode(strip_tags(str_replace(['<br />', '<br>', "\n\n"], "\n", $backtrace)));

		try
		{
			$this->set_output_handler(new \skouat\ppde\output_handler\log_wrapper_output_handler($this->filesystem, $this->log_path_filename));
			$this->output_handler->write(sprintf('[%s] %s %s', $timestamp, $message_in_file, $backtrace));
		}
		catch (\Exception $e)
		{
			// If we can't write to our log file, fall back to error_log
			$this->log_error('PPDE Log Error: ' . $e->getMessage() . '<br>Original message: ' . $message_in_file);
		}
	}

	/**
	 * Set the output handler
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
	 * @return string The generated text report
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
	 * @param string $r The report string to append to
	 */
	private function text_report_insert_line(string &$r = ''): void
	{
		$r .= str_repeat('-', 80);
	}

	/**
	 * Insert remote data args into the text report
	 *
	 * @param string $r The report string to append to
	 */
	private function text_report_insert_args(string &$r = ''): void
	{
		foreach ($this->report_data['remote_data'] as $key => $value)
		{
			$r .= str_pad($key, 25) . $value . "\n";
		}
	}
}
