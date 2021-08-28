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

use phpbb\template\template;

class currency
{
	protected $entity;
	protected $locale;
	protected $operator;
	protected $template;

	/**
	 * currency constructor.
	 *
	 * @param \skouat\ppde\entity\currency    $entity   Currency entity object
	 * @param \skouat\ppde\actions\locale_icu $locale   PPDE Locale object
	 * @param \skouat\ppde\operators\currency $operator Currency operator object
	 * @param template                        $template Template object
	 *
	 * @access public
	 */

	public function __construct(
		\skouat\ppde\entity\currency $entity,
		locale_icu $locale,
		\skouat\ppde\operators\currency $operator,
		template $template
	)
	{
		$this->entity = $entity;
		$this->locale = $locale;
		$this->operator = $operator;
		$this->template = $template;
	}

	/**
	 * Get currency data based on currency ISO code
	 *
	 * @param string $iso_code
	 *
	 * @return array
	 * @access public
	 */
	public function get_currency_data($iso_code): array
	{
		$this->entity->data_exists($this->entity->build_sql_data_exists($iso_code));

		return $this->get_default_currency_data($this->entity->get_id());
	}

	/**
	 * Get default currency symbol
	 *
	 * @param int $id Currency identifier; default: 0
	 *
	 * @return array
	 * @access public
	 */
	public function get_default_currency_data($id = 0): array
	{
		return $this->entity->get_data($this->operator->build_sql_data($id, true));
	}

	/**
	 * Format currency value, based on the PHP intl extension.
	 * If this PHP Extension is not available, we switch on a basic currency formatter.
	 *
	 * @param float  $value
	 * @param string $currency_iso_code
	 * @param string $currency_symbol
	 * @param bool   $on_left
	 *
	 * @return string
	 * @access public
	 */
	public function format_currency($value, $currency_iso_code, $currency_symbol, $on_left = true): string
	{
		if ($this->locale->is_locale_configured())
		{
			return $this->locale->numfmt_format_currency($this->locale->numfmt_create(), $value, $currency_iso_code);
		}

		return $this->currency_on_left($value, $currency_symbol, $on_left);
	}

	/**
	 * Put the currency on the left or on the right of the amount
	 *
	 * @param float  $value
	 * @param string $currency_symbol
	 * @param bool   $on_left
	 * @param string $dec_point
	 * @param string $thousands_sep
	 *
	 * @return string
	 * @access public
	 */
	public function currency_on_left($value, $currency_symbol, $on_left = true, $dec_point = '.', $thousands_sep = ''): string
	{
		if ($on_left)
		{
			return $currency_symbol . number_format(round($value, 2), 2, $dec_point, $thousands_sep);
		}

		return number_format(round($value, 2), 2, $dec_point, $thousands_sep) . $currency_symbol;
	}

	/**
	 * Build pull down menu options of available currency
	 *
	 * @param int $config_value Currency identifier; default: 0
	 *
	 * @return void
	 * @access public
	 */
	public function build_currency_select_menu($config_value = 0): void
	{
		// Grab the list of all enabled currencies; 0 is for all data
		$currency_items = $this->entity->get_data($this->operator->build_sql_data(0, true));

		// Process each menu item for pull-down
		foreach ($currency_items as $currency_item)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('options', [
				'CURRENCY_ID'        => (int) $currency_item['currency_id'],
				'CURRENCY_ISO_CODE'  => $currency_item['currency_iso_code'],
				'CURRENCY_NAME'      => $currency_item['currency_name'],
				'CURRENCY_SYMBOL'    => $currency_item['currency_symbol'],
				'S_CURRENCY_DEFAULT' => (int) $config_value === (int) $currency_item['currency_id'],
			]);
		}
		unset ($currency_items);
	}
}
