<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\exception;

class transaction_exception extends \Exception
{
	/**
	 * @var array
	 */
	private $errors;

	/**
	 * transaction_exception constructor.
	 *
	 * @param array           $errors   An array of error messages
	 * @param int             $code     The Exception code
	 * @param \Throwable|null $previous The previous throwable used for the exception chaining
	 */
	public function __construct(array $errors = [], $code = 0, \Throwable $previous = null)
	{
		parent::__construct(implode("\n", $errors), $code, $previous);
		$this->errors = $errors;
	}

	/**
	 * @return array
	 */
	public function get_errors(): array
	{
		return $this->errors;
	}
}
