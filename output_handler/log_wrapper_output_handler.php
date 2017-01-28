<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\output_handler;

class log_wrapper_output_handler
{
	/**
	 * Log file handle
	 *
	 * @var resource
	 */
	protected $file_handle = false;
	/**
	 * Status of the log path
	 *
	 * @var bool
	 */
	protected $log_path_result = false;

	/**
	 * Constructor
	 *
	 * @param string $log_file File to log to
	 */
	public function __construct($log_file)
	{
		$this->file_open($log_file);
	}

	/**
	 * Open file for logging
	 *
	 * @param string $file File to open
	 */
	protected function file_open($file)
	{
		//check if the extension directory exists in the store/ folder
		$this->ext_folder_exists(dirname($file));

		if (phpbb_is_writable(dirname($file)) && $this->log_path_result)
		{
			$this->file_handle = fopen($file, 'w');
		}
		else
		{
			throw new \RuntimeException('Unable to write to transaction log file');
		}
	}

	protected function ext_folder_exists($dir)
	{
		// Try to create the directory if it does not exist
		if (!file_exists($dir))
		{
			$mkdir_result = @mkdir($dir, 0777, true);
			$chmod_result = phpbb_chmod($dir, CHMOD_READ | CHMOD_WRITE);
			$this->log_path_result = ($mkdir_result && $chmod_result) ? true : false;
		}
		else
		{
			$this->log_path_result = true;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($message)
	{
		if ($this->file_handle !== false)
		{
			fwrite($this->file_handle, $message);
			fflush($this->file_handle);
		}
	}
}
