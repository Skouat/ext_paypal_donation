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
		if ($this->config['ppde_goal_enable'] || $this->config['ppde_raised_enable'] || $this->config['ppde_used_enable'])
		{
			// Get data from the database
			$default_currency_data = $this->ppde_actions_currency->get_default_currency_data((int) $this->config['ppde_default_currency']);

			$this->template->assign_vars([
				'PPDE_GOAL_ENABLE'   => $this->config['ppde_goal_enable'],
				'PPDE_RAISED_ENABLE' => $this->config['ppde_raised_enable'],
				'PPDE_USED_ENABLE'   => $this->config['ppde_used_enable'],

				'L_PPDE_GOAL'   => $this->get_ppde_goal_langkey($default_currency_data[0]['currency_iso_code'], $default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
				'L_PPDE_RAISED' => $this->get_ppde_raised_langkey($default_currency_data[0]['currency_iso_code'], $default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
				'L_PPDE_USED'   => $this->get_ppde_used_langkey($default_currency_data[0]['currency_iso_code'], $default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),

				'S_PPDE_STATS_TEXT_ONLY' => $this->config['ppde_stats_text_only'],
			]);

			// Generate statistics percent for display
			$this->generate_stats_percent();
		}
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
	public function get_ppde_goal_langkey($currency_iso_code, $currency_symbol, $on_left = true): string
	{
		if ((int) $this->config['ppde_goal'] <= 0)
		{
			return $this->language->lang('PPDE_DONATE_NO_GOAL');
		}

		if ((int) $this->config['ppde_goal'] < (int) $this->config['ppde_raised'])
		{
			return $this->language->lang('PPDE_DONATE_GOAL_REACHED');
		}

		return $this->language->lang('PPDE_DONATE_GOAL_RAISE', $this->ppde_actions_currency->format_currency((float) $this->config['ppde_goal'], $currency_iso_code, $currency_symbol, $on_left));
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
	public function get_ppde_raised_langkey($currency_iso_code, $currency_symbol, $on_left = true): string
	{
		if ((int) $this->config['ppde_raised'] <= 0)
		{
			return $this->language->lang('PPDE_DONATE_NOT_RECEIVED');
		}

		return $this->language->lang('PPDE_DONATE_RECEIVED', $this->ppde_actions_currency->format_currency((float) $this->config['ppde_raised'], $currency_iso_code, $currency_symbol, $on_left));
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
	public function get_ppde_used_langkey($currency_iso_code, $currency_symbol, $on_left = true): string
	{
		if ((int) $this->config['ppde_used'] <= 0)
		{
			return $this->language->lang('PPDE_DONATE_NOT_USED');
		}

		if ((int) $this->config['ppde_used'] < (int) $this->config['ppde_raised'])
		{
			return $this->language->lang(
				'PPDE_DONATE_USED',
				$this->ppde_actions_currency->format_currency((float) $this->config['ppde_used'], $currency_iso_code, $currency_symbol, $on_left),
				$this->ppde_actions_currency->format_currency((float) $this->config['ppde_raised'], $currency_iso_code, $currency_symbol, $on_left)
			);
		}

		return $this->language->lang('PPDE_DONATE_USED_EXCEEDED', $this->ppde_actions_currency->format_currency((float) $this->config['ppde_used'], $currency_iso_code, $currency_symbol, $on_left));
	}

	/**
	 * Generate statistics percent for display
	 *
	 * @return void
	 * @access private
	 */
	private function generate_stats_percent(): void
	{
		if ($this->is_ppde_goal_stats())
		{
			$percent = $this->percent_value((float) $this->config['ppde_raised'], (float) $this->config['ppde_goal']);
			$this->assign_vars_stats_percent('GOAL_NUMBER', $percent);
		}

		if ($this->is_ppde_used_stats())
		{
			$percent = $this->percent_value((float) $this->config['ppde_used'], (float) $this->config['ppde_raised']);
			$this->assign_vars_stats_percent('USED_NUMBER', $percent, true);
		}
	}

	/**
	 * Checks if stats can be displayed
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_goal_stats(): bool
	{
		return $this->config['ppde_goal_enable'] && (int) $this->config['ppde_goal'] > 0;
	}

	/**
	 * Checks if stats can be displayed
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_used_stats(): bool
	{
		return $this->config['ppde_used_enable'] && (int) $this->config['ppde_raised'] > 0 && (int) $this->config['ppde_used'] > 0;
	}

	/**
	 * Returns percent value
	 *
	 * @param float $multiplicand
	 * @param float $dividend
	 *
	 * @return float
	 * @access private
	 */
	private function percent_value($multiplicand, $dividend)
	{
		return ($multiplicand * 100) / $dividend;
	}

	/**
	 * Assign statistics percent vars to template
	 *
	 * @param string $varname
	 * @param float  $percent
	 * @param bool   $reverse_css
	 *
	 * @return void
	 * @access private
	 */
	private function assign_vars_stats_percent($varname, $percent, $reverse_css = false): void
	{
		// Force $varname to be in upper case
		$varname = strtoupper($varname);

		$this->template->assign_vars([
			'PPDE_' . $varname     => ($percent < 100) ? round($percent, 2) : round($percent),
			'PPDE_CSS_' . $varname => $this->ppde_css_classname($percent, $reverse_css),
			'S_' . $varname        => true,
		]);
	}

	/**
	 * Returns the CSS class name based on the percent of stats
	 *
	 * @param float $value
	 * @param bool  $reverse
	 *
	 * @return string
	 * @access private
	 */
	private function ppde_css_classname($value, $reverse = false): string
	{
		$css_reverse = '';
		// Array of CSS class name
		$css_data_ary = [
			10  => 'ten',
			20  => 'twenty',
			30  => 'thirty',
			40  => 'forty',
			50  => 'fifty',
			60  => 'sixty',
			70  => 'seventy',
			80  => 'eighty',
			90  => 'ninety',
			100 => 'hundred',
		];

		// Determine the index based on the value rounded up to the next highest
		$index = ceil($value / 10) * 10;

		// Reverse the CSS color
		if ($reverse && $value < 100)
		{
			// Determine the index based on the value rounded to the next lowest integer.
			$index = floor($value / 10) * 10;

			$value = 100 - $value;
			$css_reverse = '-reverse';
		}

		if (isset($css_data_ary[$index]) && $value < 100)
		{
			return $css_data_ary[$index] . $css_reverse;
		}

		return $reverse ? 'red' : 'green';
	}
}
