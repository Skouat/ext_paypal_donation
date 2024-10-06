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

use phpbb\template\template;
use skouat\ppde\entity\currency as currency_entity;
use skouat\ppde\operators\currency as currency_operator;

class currency
{
	protected $entity;
	protected $locale;
	protected $operator;
	protected $template;
	protected $default_currency_data;

	/**
	 * currency constructor.
	 *
	 * @param currency_entity   $entity   Currency entity object
	 * @param locale_icu        $locale   PPDE Locale object
	 * @param currency_operator $operator Currency operator object
	 * @param template          $template Template object
	 */
	public function __construct(
		currency_entity $entity,
		locale_icu $locale,
		currency_operator $operator,
		template $template
	)
	{
		$this->entity = $entity;
		$this->locale = $locale;
		$this->operator = $operator;
		$this->template = $template;
		$this->default_currency_data = [];
	}

	/**
	 * Get currency data based on currency ISO code
	 *
	 * @param string $iso_code The ISO code of the currency
	 * @return array Currency data
	 */
	public function set_currency_data_from_iso_code(string $iso_code): void
	{
		$this->entity->data_exists($this->entity->build_sql_data_exists($iso_code));
		$this->set_default_currency_data($this->entity->get_id());
	}

	/**
	 * Sets the default currency data.
	 *
	 * @param int $id The ID of the currency (optional).
	 */
	public function set_default_currency_data(int $id): void
	{
		$this->default_currency_data = $this->entity->get_data($this->operator->build_sql_data($id, true))[0];
	}

	/**
	 * Gets the default currency data.
	 *
	 * @return array The default currency data as an array.
	 */
	public function get_default_currency_data(): array
	{
		return ($this->default_currency_data ?? []);
	}

	/**
	 * Formats the given value as currency based on the PHP intl extension, if available.
	 * Otherwise, a basic currency formatter is used.
	 *
	 * @param float $amount The amount to be formatted as currency.
	 * @return string The formatted currency string.
	 */
	public function format_currency(float $amount): string
	{
		if ($this->locale->is_locale_configured())
		{
			return $this->locale->numfmt_format_currency($this->locale->numfmt_create(), $amount, $this->default_currency_data['currency_iso_code']);
		}

		return $this->format_currency_without_intl($amount, $this->default_currency_data['currency_symbol'], $this->default_currency_data['currency_on_left']);
	}

	/**
	 * Format a value as a currency string without using the intl extension
	 *
	 * @param float  $value           The value to format as currency
	 * @param string $currency_symbol The symbol to use as the currency symbol
	 * @param bool   $on_left         Optional. Determines whether the currency symbol should be placed on the left or
	 *                                right of the formatted value. Default is true (left side).
	 * @param string $dec_point       Optional. The string to use as the decimal separator. Default is '.'.
	 * @param string $thousands_sep   Optional. The string to use as the thousands separator. Default is an empty
	 *                                string.
	 * @return string The formatted value as a currency string
	 */
	public function format_currency_without_intl(float $value, string $currency_symbol, bool $on_left = true, string $dec_point = '.', string $thousands_sep = ''): string
	{
		$formatted_value = number_format(round($value, 2), 2, $dec_point, $thousands_sep);
		return $on_left ? $currency_symbol . $formatted_value : $formatted_value . $currency_symbol;
	}

	/**
	 * Builds a currency select menu.
	 *
	 * @param int $config_value The selected currency value from the configuration (default is 0).
	 */
	public function build_currency_select_menu(int $config_value = 0): void
	{
		// Grab the list of all enabled currencies; 0 is for all data
		$currency_items = $this->entity->get_data($this->operator->build_sql_data(0, true));

		// Process each menu item for pull-down
		foreach ($currency_items as $currency_item)
		{
			$this->assign_currency_to_template($currency_item, $config_value);
		}
	}

	/**
	 * Build pull down menu options of available currency value
	 *
	 * @param string $dropbox_value Comma-separated list of currency values
	 * @param int    $default_value Default selected value
	 * @return string HTML options for select menu
	 */
	public function build_currency_value_select_menu(string $dropbox_value, int $default_value): string
	{
		$values = array_filter(array_map('intval', explode(',', $dropbox_value)));
		$options = '';

		foreach ($values as $value)
		{
			$selected = ($value == $default_value) ? ' selected' : '';
			$options .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
		}

		return $options;
	}

	/**
	 * Assign currency information to the template.
	 *
	 * @param array $currency_item The currency item with the following keys:
	 *                             - currency_id (int): The ID of the currency.
	 *                             - currency_iso_code (string): The ISO code of the currency.
	 *                             - currency_name (string): The name of the currency.
	 *                             - currency_symbol (string): The symbol of the currency.
	 * @param int   $config_value  The configuration value used to determine the default currency (integer).
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
