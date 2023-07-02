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
	 * Retrieves the default currency data.
	 *
	 * @param int $id The ID of the currency (optional).
	 *
	 * @return array The default currency data as an array.
	 * @access public
	 */
	public function get_default_currency_data($id = 0): array
	{
		return $this->entity->get_data($this->operator->build_sql_data($id, true));
	}

	/**
	 * Formats the given value as currency based on the PHP intl extension, if available.
	 * Otherwise, a basic currency formatter is used.
	 *
	 * @param float  $value             The value to be formatted as currency.
	 * @param string $currency_iso_code The ISO code of the currency.
	 * @param string $currency_symbol   The symbol of the currency.
	 * @param bool   $on_left           Determines whether the currency symbol should be placed on the left (default:
	 *                                  true).
	 * @return string The formatted currency string.
	 * @access public
	 */
	public function format_currency($value, $currency_iso_code, $currency_symbol, $on_left = true): string
	{
		if ($this->locale->is_locale_configured())
		{
			return $this->locale->numfmt_format_currency($this->locale->numfmt_create(), $value, $currency_iso_code);
		}

		return $this->legacy_currency_format($value, $currency_symbol, $on_left);
	}

	/**
	 * Format a value as a legacy currency string
	 *
	 * @param float  $value           The value to format as currency
	 * @param string $currency_symbol The symbol to use as the currency symbol
	 * @param bool   $on_left         Optional. Determines whether the currency symbol should be placed on the left or
	 *                                right of the formatted value. Default is true (left side).
	 * @param string $dec_point       Optional. The string to use as the decimal separator. Default is '.'.
	 * @param string $thousands_sep   Optional. The string to use as the thousands separator. Default is an empty
	 *                                string.
	 * @return string The formatted value as a currency string
	 * @access public
	 */
	public function legacy_currency_format($value, $currency_symbol, $on_left = true, $dec_point = '.', $thousands_sep = ''): string
	{
		$formatted_value = number_format(round($value, 2), 2, $dec_point, $thousands_sep);

		return $on_left
			? $currency_symbol . $formatted_value
			: $formatted_value . $currency_symbol;
	}

	/**
	 * Builds a currency select menu.
	 *
	 * @param int $config_value The selected currency value from the configuration (default is 0).
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
			$this->assign_currency_to_template($currency_item, $config_value);
		}
		unset ($currency_items);
	}

	/**
	 * Assign currency information to the template.
	 *
	 * @param array $currency_item The currency item with the following keys:
	 *                             - currency_id: The ID of the currency (integer).
	 *                             - currency_iso_code: The ISO code of the currency (string).
	 *                             - currency_name: The name of the currency (string).
	 *                             - currency_symbol: The symbol of the currency (string).
	 * @param int   $config_value  The configuration value used to determine the default currency (integer).
	 *
	 * @return void
	 * @access private
	 */
	private function assign_currency_to_template(array $currency_item, int $config_value): void
	{
		$this->template->assign_block_vars('options', [
			'CURRENCY_ID'        => (int) $currency_item['currency_id'],
			'CURRENCY_ISO_CODE'  => $currency_item['currency_iso_code'],
			'CURRENCY_NAME'      => $currency_item['currency_name'],
			'CURRENCY_SYMBOL'    => $currency_item['currency_symbol'],
			'S_CURRENCY_DEFAULT' => $config_value === (int) $currency_item['currency_id'],
		]);
	}
}
