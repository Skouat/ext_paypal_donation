<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
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
		if ($iso_code === '')
		{
			return $this->build_fallback_currency_data($iso_code);
		}

		$this->entity->data_exists($this->entity->build_sql_data_exists($iso_code));

		$currency_id = $this->entity->get_id();

		if ($currency_id)
		{
			$data = $this->entity->get_data($this->operator->build_sql_data($currency_id));

			if (!empty($data))
			{
				return $data;
			}
		}

		return $this->build_fallback_currency_data($iso_code);
	}

	/**
	 * Build a minimal currency data structure from an ISO code, used when the
	 * matching currency no longer exists in the database.
	 *
	 * @param string $iso_code
	 *
	 * @return array
	 * @access private
	 */
	private function build_fallback_currency_data(string $iso_code): array
	{
		return [[
					'currency_iso_code' => $iso_code,
					'currency_symbol'   => $iso_code,
					'currency_on_left'  => false,
				]];
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
	 * Format currency value with the PHP intl extension.
	 *
	 * @param float  $value
	 * @param string $currency_iso_code
	 * @param string $currency_symbol   Unused; kept for caller compatibility
	 * @param bool   $on_left           Unused; kept for caller compatibility
	 *
	 * @return string
	 * @access public
	 */
	public function format_currency($value, $currency_iso_code, $currency_symbol, $on_left = true): string
	{
		return $this->locale->numfmt_format_currency($this->locale->numfmt_create(), $value, $currency_iso_code);
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

	/**
	 * Gets the number of fraction digits for a currency (ISO 4217).
	 *
	 * @param string $currency_iso_code
	 *
	 * @return int
	 * @access public
	 */
	public function get_currency_fraction_digits(string $currency_iso_code): int
	{
		return $this->locale->get_currency_fraction_digits($currency_iso_code);
	}
}
