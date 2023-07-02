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
			$this->generate_stats_proportion();
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
	 * Generate statistics proportion for display
	 *
	 * @return void
	 * @access private
	 */
	private function generate_stats_proportion(): void
	{
		$stat_conditions = [
			'GOAL_NUMBER' => ['condition' => $this->is_ppde_goal_stats(), 'nums' => ['ppde_raised', 'ppde_goal']],
			'USED_NUMBER' => ['condition' => $this->is_ppde_used_stats(), 'nums' => ['ppde_used', 'ppde_raised']],
		];

		foreach ($stat_conditions as $stat_name => $details)
		{
			if ($details['condition'])
			{
				$data = $details['nums'];
				$percentage = $this->percentage_value((float) $this->config[$data[0]], (float) $this->config[$data[1]]);
				$this->assign_vars_stats_proportion($stat_name, $percentage, $stat_name === 'USED_NUMBER');
			}
		}
	}

	/**
	 * Verifies if stats can be shown
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_goal_stats()
	{
		return $this->config['ppde_goal_enable'] && (int) $this->config['ppde_goal'] > 0;
	}

	/**
	 * Verifies if statistics can be shown
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_used_stats()
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
	private function percentage_value($multiplicand, $divisor)
	{
		return ($multiplicand * 100) / $divisor;
	}

	/**
	 * Assigns statistics proportion vars to the template
	 *
	 * @param string $var_name
	 * @param float  $proportion
	 * @param bool   $reverse_css
	 *
	 * @return void
	 * @access private
	 */
	private function assign_vars_stats_proportion($var_name, $proportion, $reverse_css = false)
	{
		// Enforcing $var_name to uppercase
		$var_name = strtoupper($var_name);

		$this->template->assign_vars([
			'PPDE_' . $var_name     => ($proportion < 100) ? round($proportion, 2) : round($proportion),
			'PPDE_CSS_' . $var_name => $this->ppde_css_classname($proportion, $reverse_css),
			'S_' . $var_name        => true,
		]);
	}

	/**
	 * Gives back the CSS class name based on the proportion of the stats
	 *
	 * @param float $value
	 * @param bool  $reverse
	 *
	 * @return string
	 * @access private
	 */
	private function ppde_css_classname($value, $reverse = false)
	{
		$css_reverse = '';
		$css_data_array = [
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

		$index = $this->calculate_index($value, $reverse);

		if ($reverse && $value < 100)
		{
			$value = 100 - $value;
			$css_reverse = '-reverse';
		}

		if (isset($css_data_array[$index]) && $value < 100)
		{
			return $css_data_array[$index] . $css_reverse;
		}

		return $reverse ? 'red' : 'green';
	}

	/**
	 * Calculate the index based on the value
	 *
	 * @param float $value
	 * @param bool  $reverse
	 *
	 * @return int
	 */
	private function calculate_index($value, $reverse)
	{
		if ($reverse && $value < 100)
		{
			return floor($value / 10) * 10;
		}

		return ceil($value / 10) * 10;
	}
}
