<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\includes;

use phpbb\language\language;
use phpbb\request\request;
use phpbb\user;
use phpbb\user_loader;
use skouat\ppde\exception\transaction_exception;

class transaction_validator
{
	/** @var language */
	protected $language;

	/** @var user */
	protected $user;

	/** @var user_loader */
	protected $user_loader;

	/** @var request */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param language    $language
	 * @param request     $request
	 * @param user        $user
	 * @param user_loader $user_loader
	 */
	public function __construct(language $language, request $request, user $user, user_loader $user_loader)
	{
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;
		$this->user_loader = $user_loader;
	}

	/**
	 * Validate and return the user ID
	 *
	 * @param string $username
	 * @param int    $donor_id
	 * @return int
	 * @throws transaction_exception
	 */
	public function validate_user_id(string $username, int $donor_id = 0): int
	{
		if (empty($username) && ($donor_id === ANONYMOUS || $this->request->is_set('u')))
		{
			return ANONYMOUS;
		}

		$user_id = ($username !== '') ? $this->user_loader->load_user_by_username($username) : $donor_id;

		if ($user_id <= ANONYMOUS)
		{
			throw new transaction_exception([$this->language->lang('PPDE_MT_DONOR_NOT_FOUND')]);
		}

		return $user_id;
	}

	/**
	 * Validate payment date and time
	 *
	 * @param array $transaction_data
	 * @return int
	 * @throws transaction_exception
	 */
	public function validate_payment_date_time(array $transaction_data): int
	{
		$payment_date = implode('-', [
			$transaction_data['MT_PAYMENT_DATE_YEAR'],
			$transaction_data['MT_PAYMENT_DATE_MONTH'],
			$transaction_data['MT_PAYMENT_DATE_DAY'],
		]);

		$payment_time = $transaction_data['MT_PAYMENT_TIME'];
		$date_time_string = $payment_date . ' ' . $payment_time;

		$payment_date_time = $this->parse_date_time($date_time_string);

		if ($payment_date_time === false)
		{
			throw new transaction_exception([$this->language->lang('PPDE_MT_PAYMENT_DATE_ERROR', $date_time_string)]);
		}

		if ($payment_date_time > time())
		{
			throw new transaction_exception([$this->language->lang('PPDE_MT_PAYMENT_DATE_FUTURE', $this->user->format_date($payment_date_time))]);
		}

		$this->validate_time($payment_time);

		return $payment_date_time;
	}

	/**
	 * Parse date and time string
	 *
	 * @param string $date_time_string
	 * @return int|false
	 */
	private function parse_date_time($date_time_string)
	{
		$formats = ['Y-m-d H:i:s', 'Y-m-d G:i', 'Y-m-d h:i:s a', 'Y-m-d g:i A'];

		foreach ($formats as $format)
		{
			$parsed = \DateTime::createFromFormat($format, $date_time_string);
			if ($parsed !== false)
			{
				return $parsed->getTimestamp();
			}
		}

		return false;
	}

	/**
	 * Validate time format
	 *
	 * @param string $payment_time
	 * @throws transaction_exception
	 */
	private function validate_time(string $payment_time): void
	{
		$time_parts = explode(':', $payment_time);
		if (count($time_parts) < 2 || count($time_parts) > 3)
		{
			throw new transaction_exception([$this->language->lang('PPDE_MT_PAYMENT_TIME_ERROR', $payment_time)]);
		}

		$hours = (int) $time_parts[0];
		$minutes = (int) $time_parts[1];
		$seconds = isset($time_parts[2]) ? (int) $time_parts[2] : 0;

		if ($hours >= 24 || $minutes >= 60 || $seconds >= 60)
		{
			throw new transaction_exception([$this->language->lang('PPDE_MT_PAYMENT_TIME_ERROR', $payment_time)]);
		}
	}

	/**
	 * Validate transaction amounts
	 *
	 * @param array $transaction_data
	 * @throws transaction_exception
	 */
	public function validate_transaction_amounts(array $transaction_data): void
	{
		$errors = [];

		if ($transaction_data['MT_MC_GROSS'] <= 0)
		{
			$errors[] = $this->language->lang('PPDE_MT_MC_GROSS_TOO_LOW');
		}

		if ($transaction_data['MT_MC_FEE'] < 0)
		{
			$errors[] = $this->language->lang('PPDE_MT_MC_FEE_NEGATIVE');
		}

		if ($transaction_data['MT_MC_FEE'] >= $transaction_data['MT_MC_GROSS'])
		{
			$errors[] = $this->language->lang('PPDE_MT_MC_FEE_TOO_HIGH');
		}

		if (!empty($errors))
		{
			throw new transaction_exception($errors);
		}
	}
}
