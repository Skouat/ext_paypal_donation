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
	protected $actions_core;
	protected $actions_currency;
	protected $config;
	protected $dp_vars;
	protected $language;
	protected $user;

	/**
	 * currency constructor.
	 *
	 * @param \skouat\ppde\actions\core     $actions_core     PPDE actions core object
	 * @param \skouat\ppde\actions\currency $actions_currency PPDE actions currency object
	 * @param config                        $config           Config object
	 * @param language                      $language         Language object
	 * @param user                          $user             User object
	 *
	 * @access public
	 */

	public function __construct(
		core $actions_core,
		currency $actions_currency,
		config $config,
		language $language,
		user $user
	)
	{
		$this->actions_core = $actions_core;
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
		$this->dp_vars = [
			0 => ['var' => '{USER_ID}', 'value' => $this->user->data['user_id']],
			1 => ['var' => '{USERNAME}', 'value' => $this->user->data['username']],
			2 => ['var' => '{SITE_NAME}', 'value' => $this->config['sitename']],
			3 => ['var' => '{SITE_DESC}', 'value' => $this->config['site_desc']],
			4 => ['var' => '{BOARD_CONTACT}', 'value' => $this->config['board_contact']],
			5 => ['var' => '{BOARD_EMAIL}', 'value' => $this->config['board_email']],
			6 => ['var' => '{BOARD_SIG}', 'value' => $this->config['board_email_sig']],
			7 => ['var' => '{DONATION_GOAL}', 'value' => $this->actions_currency->format_currency(
				(float) $this->config['ppde_goal'],
				$default_currency_data[0]['currency_iso_code'],
				$default_currency_data[0]['currency_symbol'],
				(bool) $default_currency_data[0]['currency_on_left'])],
			8 => ['var' => '{DONATION_RAISED}', 'value' => $this->actions_currency->format_currency(
				(float) $this->config['ppde_raised'],
				$default_currency_data[0]['currency_iso_code'],
				$default_currency_data[0]['currency_symbol'],
				(bool) $default_currency_data[0]['currency_on_left'])],
		];

		if ($this->actions_core->is_in_admin())
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
}
