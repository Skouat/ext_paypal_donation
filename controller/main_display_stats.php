<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

class main_display_stats
{
	protected $config;
	protected $language;
	protected $ppde_controller_main;
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                    $config               Config object
	 * @param \phpbb\language\language                $language             Language user object
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main PPDE main controller object
	 * @param \phpbb\template\template                $template             Template object
	 *
	 * @return \skouat\ppde\controller\main_display_stats
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \skouat\ppde\controller\main_controller $ppde_controller_main, \phpbb\template\template $template)
	{
		$this->config = $config;
		$this->language = $language;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->template = $template;
	}

	/**
	 * Assign statistics vars to the template
	 *
	 * @return null
	 * @access public
	 */
	public function display_stats()
	{
		if ($this->config['ppde_goal_enable'] || $this->config['ppde_raised_enable'] || $this->config['ppde_used_enable'])
		{
			// Get data from the database
			$default_currency_data = $this->ppde_controller_main->get_default_currency_data($this->config['ppde_default_currency']);

			$this->template->assign_vars(array(
				'PPDE_GOAL_ENABLE'   => $this->config['ppde_goal_enable'],
				'PPDE_RAISED_ENABLE' => $this->config['ppde_raised_enable'],
				'PPDE_USED_ENABLE'   => $this->config['ppde_used_enable'],

				'L_PPDE_GOAL'   => $this->get_ppde_goal_langkey($default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
				'L_PPDE_RAISED' => $this->get_ppde_raised_langkey($default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
				'L_PPDE_USED'   => $this->get_ppde_used_langkey($default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
			));

			// Generate statistics percent for display
			$this->generate_stats_percent();
		}
	}

	/**
	 * Retrieve the language key for donation goal
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_goal_langkey($currency_symbol, $on_left = true)
	{
		if ((int) $this->config['ppde_goal'] <= 0)
		{
			$l_ppde_goal = $this->language->lang('PPDE_DONATE_NO_GOAL');
		}
		else if ((int) $this->config['ppde_goal'] < (int) $this->config['ppde_raised'])
		{
			$l_ppde_goal = $this->language->lang('PPDE_DONATE_GOAL_REACHED');
		}
		else
		{
			$l_ppde_goal = $this->language->lang('PPDE_DONATE_GOAL_RAISE', $this->ppde_controller_main->currency_on_left((int) $this->config['ppde_goal'], $currency_symbol, $on_left));
		}

		return $l_ppde_goal;
	}

	/**
	 * Retrieve the language key for donation raised
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_raised_langkey($currency_symbol, $on_left = true)
	{
		if ((int) $this->config['ppde_raised'] <= 0)
		{
			$l_ppde_raised = $this->language->lang('PPDE_DONATE_NOT_RECEIVED');
		}
		else
		{
			$l_ppde_raised = $this->language->lang('PPDE_DONATE_RECEIVED', $this->ppde_controller_main->currency_on_left((int) $this->config['ppde_raised'], $currency_symbol, $on_left));
		}

		return $l_ppde_raised;
	}

	/**
	 * Retrieve the language key for donation used
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_used_langkey($currency_symbol, $on_left = true)
	{
		if ((int) $this->config['ppde_used'] <= 0)
		{
			$l_ppde_used = $this->language->lang('PPDE_DONATE_NOT_USED');
		}
		else if ((int) $this->config['ppde_used'] < (int) $this->config['ppde_raised'])
		{
			$l_ppde_used = $this->language->lang('PPDE_DONATE_USED', $this->ppde_controller_main->currency_on_left((int) $this->config['ppde_used'], $currency_symbol, $on_left), $this->ppde_controller_main->currency_on_left((int) $this->config['ppde_raised'], $currency_symbol, $on_left));
		}
		else
		{
			$l_ppde_used = $this->language->lang('PPDE_DONATE_USED_EXCEEDED', $this->ppde_controller_main->currency_on_left((int) $this->config['ppde_used'], $currency_symbol, $on_left));
		}

		return $l_ppde_used;
	}

	/**
	 * Generate statistics percent for display
	 *
	 * @return null
	 * @access private
	 */
	private function generate_stats_percent()
	{
		if ($this->is_ppde_goal_stats())
		{
			$percent = $this->percent_value((int) $this->config['ppde_raised'], (int) $this->config['ppde_goal']);
			$this->assign_vars_stats_percent('GOAL_NUMBER', $percent);
		}

		if ($this->is_ppde_used_stats())
		{
			$percent = $this->percent_value((int) $this->config['ppde_used'], (int) $this->config['ppde_raised']);
			$this->assign_vars_stats_percent('USED_NUMBER', $percent, true);
		}
	}

	/**
	 * Checks if stats can be displayed
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_goal_stats()
	{
		return $this->config['ppde_goal_enable'] && (int) $this->config['ppde_goal'] > 0;
	}

	/**
	 * Checks if stats can be displayed
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_used_stats()
	{
		return $this->config['ppde_used_enable'] && (int) $this->config['ppde_raised'] > 0 && (int) $this->config['ppde_used'] > 0;
	}

	/**
	 * Returns percent value
	 *
	 * @param integer $multiplicand
	 * @param integer $dividend
	 *
	 * @return float
	 * @access private
	 */
	private function percent_value($multiplicand, $dividend)
	{
		return round(($multiplicand * 100) / $dividend, 2);
	}

	/**
	 * Assign statistics percent vars to template
	 *
	 * @param string $type
	 * @param float  $percent
	 * @param bool   $reverse_css
	 *
	 * @return null
	 * @access private
	 */
	private function assign_vars_stats_percent($type, $percent, $reverse_css = false)
	{
		$this->template->assign_vars(array(
			'PPDE_' . $type     => $percent,
			'PPDE_CSS_' . $type => $this->ppde_css_classname($percent, $reverse_css),
			'S_' . $type        => !empty($type) ? true : false,
		));
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
	private function ppde_css_classname($value, $reverse = false)
	{
		$css_reverse = '';

		if ($reverse && $value < 100)
		{
			$value = 100 - $value;
			$css_reverse = '-reverse';
		}

		switch ($value)
		{
			case ($value <= 10):
				return 'ten' . $css_reverse;
			case ($value <= 20):
				return 'twenty' . $css_reverse;
			case ($value <= 30):
				return 'thirty' . $css_reverse;
			case ($value <= 40):
				return 'forty' . $css_reverse;
			case ($value <= 50):
				return 'fifty' . $css_reverse;
			case ($value <= 60):
				return 'sixty' . $css_reverse;
			case ($value <= 70):
				return 'seventy' . $css_reverse;
			case ($value <= 80):
				return 'eighty' . $css_reverse;
			case ($value <= 90):
				return 'ninety' . $css_reverse;
			case ($value < 100):
				return 'hundred' . $css_reverse;
			default:
				return $reverse ? 'red' : 'green';
		}
	}
}
