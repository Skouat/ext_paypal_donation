<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
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
	 * Constructor
	 *
	 * @param \skouat\ppde\actions\core     $actions_core     PPDE actions core object
	 * @param \skouat\ppde\actions\currency $actions_currency PPDE actions currency object
	 * @param config                        $config           Config object
	 * @param language                      $language         Language object
	 * @param user                          $user             User object
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
	 * @return array Array of template variables
	 */
	public function get_vars(): array
	{
		$this->actions_currency->set_default_currency_data((int) $this->config['ppde_default_currency']);
		$this->dp_vars = $this->populate_template_vars();

		if ($this->actions_core->is_in_admin())
		{
			$this->add_predefined_lang_vars();
		}

		return $this->dp_vars;
	}

	/**
	 * Populate template vars
	 *
	 * @return array Array of template variables
	 */
	private function populate_template_vars(): array
	{
		return [
			['var' => '{USER_ID}',         'value' => $this->user->data['user_id']],
			['var' => '{USERNAME}',        'value' => $this->user->data['username']],
			['var' => '{SITE_NAME}',       'value' => $this->config['sitename']],
			['var' => '{SITE_DESC}',       'value' => $this->config['site_desc']],
			['var' => '{BOARD_CONTACT}',   'value' => $this->config['board_contact']],
			['var' => '{BOARD_EMAIL}',     'value' => $this->config['board_email']],
			['var' => '{BOARD_SIG}',       'value' => $this->config['board_email_sig']],
			['var' => '{DONATION_GOAL}',   'value' => $this->actions_currency->format_currency((float) $this->config['ppde_goal'])],
			['var' => '{DONATION_RAISED}', 'value' => $this->actions_currency->format_currency((float) $this->config['ppde_raised'])],
		];
	}

	/**
	 * Adds predefined language keys variables to the donation pages.
	 */
	private function add_predefined_lang_vars(): void
	{
		foreach ($this->dp_vars as &$value)
		{
			$value['name'] = $this->language->lang('PPDE_DP_' . trim($value['var'], '{}'));
		}
	}

	/**
	 * Replace template vars in the message
	 *
	 * @param string $message The message containing template variables
	 * @return string The message with template variables replaced
	 */
	public function replace_template_vars(string $message): string
	{
		$tpl_ary = array_column($this->dp_vars, 'value', 'var');
		return strtr($message, $tpl_ary);
	}
}
