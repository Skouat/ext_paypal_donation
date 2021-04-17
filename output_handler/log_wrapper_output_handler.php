<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\output_handler;

use phpbb\filesystem\filesystem_interface;

class log_wrapper_output_handler
{
	/**
	 * @var filesystem_interface
	 */
	protected $filesystem;
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
	 * @param filesystem_interface $filesystem phpBB's filesystem service
	 * @param string               $log_file   File to log to
	 */
	public function __construct(filesystem_interface $filesystem, $log_file)
	{
		$this->filesystem = $filesystem;
		$this->file_open($log_file);
	}

	/**
	 * Open file for logging
	 *
	 * @param string $file File to open
	 */
	protected function file_open($file): void
	{
		//check if the extension directory exists in the store/ folder
		$this->ext_folder_exists(dirname($file));

		if ($this->log_path_result && $this->filesystem->is_writable(dirname($file)))
		{
			$this->file_handle = fopen($file, 'ab');
		}
		else
		{
			throw new \RuntimeException('Unable to write to transaction log file');
		}
	}

	protected function ext_folder_exists($dir): void
	{
		// Try to create the directory if it does not exist
		if (!file_exists($dir))
		{
			$mkdir_result = @mkdir($dir, 0777, true);
			$chmod_result = $this->filesystem->phpbb_chmod($dir, CHMOD_READ | CHMOD_WRITE);
			$this->log_path_result = $mkdir_result && $chmod_result;
		}
		else
		{
			$this->log_path_result = true;
		}
	}

	public function write($message)
	{
		if ($this->file_handle !== false)
		{
			fwrite($this->file_handle, $message);
			fflush($this->file_handle);
		}
	}
}
