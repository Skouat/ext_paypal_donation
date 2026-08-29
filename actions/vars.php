<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\actions;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\user;

class vars
{
	protected $actions_auth;
	protected $actions_currency;
	protected $config;
	protected $dp_vars;
	protected $language;
	protected $user;

	/**
	 * vars constructor.
	 *
	 * @param \skouat\ppde\actions\auth     $actions_auth     PPDE actions auth object
	 * @param \skouat\ppde\actions\currency $actions_currency PPDE actions currency object
	 * @param config                        $config           Config object
	 * @param language                      $language         Language object
	 * @param user                          $user             User object
	 *
	 * @access public
	 */

	public function __construct(
		auth $actions_auth,
		currency $actions_currency,
		config $config,
		language $language,
		user $user
	)
	{
		$this->actions_auth = $actions_auth;
		$this->actions_currency = $actions_currency;
		$this->config = $config;
		$this->language = $language;
		$this->user = $user;
	}

	/**
	 * Get template vars
	 *
	 * @return array $this->dp_vars
	 * @access public
	 */
	public function get_vars(): array
	{
		$default_currency_data = $this->actions_currency->get_default_currency_data((int) $this->config['ppde_default_currency']);
		$currency = !empty($default_currency_data) ? $default_currency_data[0] : [];

		$this->dp_vars = [
			['var' => '{USER_ID}', 'value' => $this->user->data['user_id']],
			['var' => '{USERNAME}', 'value' => $this->user->data['username']],
			['var' => '{SITE_NAME}', 'value' => $this->config['sitename']],
			['var' => '{SITE_DESC}', 'value' => $this->config['site_desc']],
			['var' => '{BOARD_CONTACT}', 'value' => $this->config['board_contact']],
			['var' => '{BOARD_EMAIL}', 'value' => $this->config['board_email']],
			['var' => '{BOARD_SIG}', 'value' => $this->config['board_email_sig']],
			['var' => '{DONATION_GOAL}', 'value' => $this->format((float) $this->config['ppde_goal'], $currency)],
			['var' => '{DONATION_RAISED}', 'value' => $this->format((float) $this->config['ppde_raised'], $currency)],
			['var' => '{DONATION_USED}', 'value' => $this->format((float) $this->config['ppde_used'], $currency)],
		];

		if ($this->actions_auth->is_in_admin())
		{
			$this->add_predefined_lang_vars();
		}

		return $this->dp_vars;
	}

	/**
	 * Add language key for donation pages Predefined vars
	 *
	 * @return void
	 * @access private
	 */
	private function add_predefined_lang_vars(): void
	{
		//Add language entries for displaying the vars
		foreach ($this->dp_vars as $index => $value)
		{
			$this->dp_vars[$index]['name'] = $this->language->lang('PPDE_DP_' . substr(substr($value['var'], 0, -1), 1));
		}
	}

	/**
	 * Replace template vars in the message
	 *
	 * @param string $message
	 *
	 * @return string
	 * @access public
	 */
	public function replace_template_vars($message): string
	{
		$tpl_ary = [];
		foreach ($this->dp_vars as $index => $value)
		{
			$tpl_ary[$value['var']] = $this->dp_vars[$index]['value'];
		}

		return str_replace(array_keys($tpl_ary), array_values($tpl_ary), $message);
	}

	private function format(float $amount, array $currency): string
	{
		return $this->actions_currency->format_currency(
			$amount,
			$currency['currency_iso_code'] ?? '',
			$currency['currency_symbol'] ?? '',
			(bool) ($currency['currency_on_left'] ?? true)
		);
	}
}
