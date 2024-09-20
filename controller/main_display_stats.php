<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\template\template;
use skouat\ppde\actions\currency;

class main_display_stats
{
	protected $config;
	protected $language;
	protected $ppde_actions_currency;
	protected $template;

	/**
	 * Constructor
	 *
	 * @param config   $config                Config object
	 * @param language $language              Language user object
	 * @param currency $ppde_actions_currency Currency actions object
	 * @param template $template              Template object
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		currency $ppde_actions_currency,
		template $template
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->ppde_actions_currency = $ppde_actions_currency;
		$this->template = $template;
	}

	/**
	 * Assign statistics vars to the template
	 *
	 * @return void
	 * @access public
	 */
	public function display_stats(): void
	{
		if ($this->is_stats_enabled())
		{
			// Get data for the default currency
			$default_currency_data = $this->ppde_actions_currency->get_default_currency_data((int) $this->config['ppde_default_currency']);

			$this->assign_template_enable_vars();
			$this->assign_template_lang_vars($default_currency_data);
			$this->assign_template_stats_text_only_var();

			// Generate statistics percent for display
			$this->generate_stats_percentage();
		}
	}

	private function is_stats_enabled(): bool
	{
		return $this->config['ppde_goal_enable'] || $this->config['ppde_raised_enable'] || $this->config['ppde_used_enable'];
	}

	private function assign_template_enable_vars(): void
	{
		$this->template->assign_vars([
			'PPDE_GOAL_ENABLE'   => $this->config['ppde_goal_enable'],
			'PPDE_RAISED_ENABLE' => $this->config['ppde_raised_enable'],
			'PPDE_USED_ENABLE'   => $this->config['ppde_used_enable'],
		]);
	}

	private function assign_template_stats_text_only_var(): void
	{
		$this->template->assign_var('S_PPDE_STATS_TEXT_ONLY', $this->config['ppde_stats_text_only']);
	}

	private function assign_template_lang_vars(array $default_currency_data): void
	{
		$iso_code = $default_currency_data[0]['currency_iso_code'];
		$symbol = $default_currency_data[0]['currency_symbol'];
		$currency_on_left = (bool) $default_currency_data[0]['currency_on_left'];

		$this->template->assign_vars([
			'L_PPDE_GOAL'   => $this->get_ppde_goal_langkey($iso_code, $symbol, $currency_on_left),
			'L_PPDE_RAISED' => $this->get_ppde_raised_langkey($iso_code, $symbol, $currency_on_left),
			'L_PPDE_USED'   => $this->get_ppde_used_langkey($iso_code, $symbol, $currency_on_left),
		]);
	}

	/**
	 * Retrieve the language key for donation goal
	 *
	 * @param string $currency_iso_code
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_goal_langkey(string $currency_iso_code, string $currency_symbol, bool $on_left = true): string
	{
		$goal = (int) $this->config['ppde_goal'];
		$raised = (int) $this->config['ppde_raised'];

		if ($goal <= 0)
		{
			return $this->language->lang('PPDE_DONATE_NO_GOAL');
		}

		if ($goal < $raised)
		{
			return $this->language->lang('PPDE_DONATE_GOAL_REACHED');
		}

		$formatted_goal = $this->ppde_actions_currency->format_currency((float) $goal, $currency_iso_code, $currency_symbol, $on_left);
		return $this->language->lang('PPDE_DONATE_GOAL_RAISE', $formatted_goal);
	}

	/**
	 * Retrieve the language key for donation raised
	 *
	 * @param string $currency_iso_code
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_raised_langkey(string $currency_iso_code, string $currency_symbol, bool $on_left = true): string
	{
		$raised = (int) $this->config['ppde_raised'];

		if ($raised <= 0)
		{
			return $this->language->lang('PPDE_DONATE_NOT_RECEIVED');
		}

		$formatted_raised = $this->ppde_actions_currency->format_currency((float) $raised, $currency_iso_code, $currency_symbol, $on_left);
		return $this->language->lang('PPDE_DONATE_RECEIVED', $formatted_raised);
	}

	/**
	 * Retrieve the language key for donation used
	 *
	 * @param string $currency_iso_code
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_used_langkey(string $currency_iso_code, string $currency_symbol, bool $on_left = true): string
	{
		$used = (int) $this->config['ppde_used'];
		$raised = (int) $this->config['ppde_raised'];

		if ($used <= 0)
		{
			return $this->language->lang('PPDE_DONATE_NOT_USED');
		}

		$formatted_used = $this->ppde_actions_currency->format_currency((float) $used, $currency_iso_code, $currency_symbol, $on_left);

		if ($used < $raised)
		{
			$formatted_raised = $this->ppde_actions_currency->format_currency((float) $raised, $currency_iso_code, $currency_symbol, $on_left);
			return $this->language->lang('PPDE_DONATE_USED', $formatted_used, $formatted_raised);
		}

		return $this->language->lang('PPDE_DONATE_USED_EXCEEDED', $formatted_used);
	}

	/**
	 * Generate statistics percentage for display
	 *
	 * @return void
	 * @access private
	 */
	private function generate_stats_percentage(): void
	{
		$stat_conditions = [
			'GOAL_NUMBER' => ['condition' => $this->is_ppde_goal_stats(), 'numerator' => 'ppde_raised', 'denominator' => 'ppde_goal'],
			'USED_NUMBER' => ['condition' => $this->is_ppde_used_stats(), 'numerator' => 'ppde_used', 'denominator' => 'ppde_raised'],
		];

		foreach ($stat_conditions as $stat_name => $details)
		{
			if ($details['condition'])
			{
				$percentage = $this->percentage_value(
					(float) $this->config[$details['numerator']],
					(float) $this->config[$details['denominator']]
				);
				$this->assign_vars_stats_percentage($stat_name, $percentage);
			}
		}
	}

	/**
	 * Verifies if stats can be shown
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_goal_stats(): bool
	{
		return $this->config['ppde_goal_enable'] && (int) $this->config['ppde_goal'] > 0;
	}

	/**
	 * Verifies if statistics can be shown
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_used_stats(): bool
	{
		return $this->config['ppde_used_enable'] && (int) $this->config['ppde_raised'] > 0 && (int) $this->config['ppde_used'] > 0;
	}

	/**
	 * Gives back the percentage value
	 *
	 * @param float $multiplicand
	 * @param float $divisor
	 *
	 * @return float
	 * @access private
	 */
	private function percentage_value(float $multiplicand, float $divisor): float
	{
		if ($divisor == 0)
		{
			return 0.0;
		}
		return round(($multiplicand * 100) / $divisor, 2);
	}

	/**
	 * Assigns statistics proportion vars to the template
	 *
	 * @param string $var_name
	 * @param float  $percentage
	 * @return void
	 * @access private
	 */
	private function assign_vars_stats_percentage(string $var_name, float $percentage): void
	{
		// Enforcing $var_name to uppercase
		$var_name = strtoupper($var_name);

		$this->template->assign_vars([
			'PPDE_' . $var_name          => ($percentage < 100) ? $percentage : round($percentage),
			'PPDE_' . $var_name . '_CSS' => max(0, min(100, $percentage)),
			'S_' . $var_name             => true,
		]);
	}
}
