<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2021 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\includes;

use phpbb\template\template;
use phpbb\language\language;
use phpbb\user;
use skouat\ppde\actions\currency;

class transaction_template_helper
{
	protected $actions_currency;
	protected $language;
	protected $template;
	protected $user;

	/**
	 * Constructor
	 *
	 * @param template $template
	 * @param language $language
	 * @param user     $user
	 * @param currency $actions_currency
	 */
	public function __construct(template $template, language $language, user $user, currency $actions_currency)
	{
		$this->template = $template;
		$this->language = $language;
		$this->user = $user;
		$this->actions_currency = $actions_currency;
	}

	/**
	 * Assign hidden fields
	 *
	 * @param array $data
	 */
	public function assign_hidden_fields(array $data): void
	{
		$s_hidden_fields = build_hidden_fields([
			'id'                  => $data['transaction_id'],
			'donor_id'            => $data['user_id'],
			'txn_errors_approved' => $data['txn_errors_approved'],
		]);
		$this->template->assign_var('S_HIDDEN_FIELDS', $s_hidden_fields);
	}

	/**
	 * Assign currency data to template variables
	 *
	 * @param array $data Transaction data
	 */
	public function assign_currency_data(array $data): void
	{
		$this->actions_currency->set_currency_data_from_iso_code($data['mc_currency']);
		$this->actions_currency->set_currency_data_from_iso_code($data['settle_currency']);

		$this->template->assign_vars([
			'EXCHANGE_RATE'                   => '1 ' . $data['mc_currency'] . ' = ' . $data['exchange_rate'] . ' ' . $data['settle_currency'],
			'MC_GROSS'                        => $this->actions_currency->format_currency($data['mc_gross']),
			'MC_FEE'                          => $this->actions_currency->format_currency($data['mc_fee']),
			'MC_NET'                          => $this->actions_currency->format_currency($data['net_amount']),
			'SETTLE_AMOUNT'                   => $this->actions_currency->format_currency($data['settle_amount']),
			'L_PPDE_DT_SETTLE_AMOUNT'         => $this->language->lang('PPDE_DT_SETTLE_AMOUNT', $data['settle_currency']),
			'L_PPDE_DT_EXCHANGE_RATE_EXPLAIN' => $this->language->lang('PPDE_DT_EXCHANGE_RATE_EXPLAIN', $this->user->format_date($data['payment_date'])),
			'S_CONVERT'                       => !((int) $data['settle_amount'] === 0 && empty($data['exchange_rate'])),
		]);
	}

	/**
	 * Assign user data to template variables
	 *
	 * @param array $data Transaction data
	 */
	public function assign_user_data(array $data): void
	{
		$this->template->assign_vars([
			'BOARD_USERNAME' => get_username_string('full', $data['user_id'], $data['username'], $data['user_colour'], $this->language->lang('GUEST'), append_sid($this->phpbb_admin_path . 'index.' . $this->php_ext, 'i=users&amp;mode=overview')),
			'NAME'           => $data['first_name'] . ' ' . $data['last_name'],
			'PAYER_EMAIL'    => $data['payer_email'],
			'PAYER_ID'       => $data['payer_id'],
			'PAYER_STATUS'   => $data['payer_status'] ? $this->language->lang('PPDE_DT_VERIFIED') : $this->language->lang('PPDE_DT_UNVERIFIED'),
		]);
	}

	/**
	 * Assign transaction details to template variables
	 *
	 * @param array $data Transaction data
	 */
	public function assign_transaction_details(array $data): void
	{
		$this->template->assign_vars([
			'ITEM_NAME'      => $data['item_name'],
			'ITEM_NUMBER'    => $data['item_number'],
			'MEMO'           => $data['memo'],
			'RECEIVER_EMAIL' => $data['receiver_email'],
			'RECEIVER_ID'    => $data['receiver_id'],
			'TXN_ID'         => $data['txn_id'],
		]);
	}

	/**
	 * Assign payment details to template variables
	 *
	 * @param array $data Transaction data
	 */
	public function assign_payment_details(array $data): void
	{
		$this->template->assign_vars([
			'PAYMENT_DATE'   => $this->user->format_date($data['payment_date']),
			'PAYMENT_STATUS' => $this->language->lang(['PPDE_DT_PAYMENT_STATUS_VALUES', strtolower($data['payment_status'])]),
		]);
	}

	/**
	 * Assign error data to template variables
	 *
	 * @param array $data Transaction data
	 */
	public function assign_error_data(array $data): void
	{
		$this->template->assign_vars([
			'S_ERROR'          => !empty($data['txn_errors']),
			'S_ERROR_APPROVED' => !empty($data['txn_errors_approved']),
			'ERROR_MSG'        => (!empty($data['txn_errors'])) ? $data['txn_errors'] : '',
		]);
	}
}
