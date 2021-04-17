<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\exception;

class transaction_exception extends \Exception
{
	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @return array
	 */
	public function get_errors(): array
	{
		return $this->errors;
	}

	/**
	 * @param array $errors
	 * @return transaction_exception
	 */
	public function set_errors($errors)
	{
		$this->errors = $errors;
		return $this;
	}
}
