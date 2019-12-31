<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\actions;

use phpbb\template\template;

class currency
{
	protected $entity;
	protected $operator;
	protected $template;

	/**
	 * currency constructor.
	 *
	 * @param \skouat\ppde\entity\currency    $entity   Currency entity object
	 * @param \skouat\ppde\operators\currency $operator Currency operator object
	 * @param template                        $template Template object
	 *
	 * @access public
	 */

	public function __construct(\skouat\ppde\entity\currency $entity, \skouat\ppde\operators\currency $operator, template $template)
	{
		$this->entity = $entity;
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
	public function get_currency_data($iso_code)
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
	public function get_default_currency_data($id = 0)
	{
		return $this->entity->get_data($this->operator->build_sql_data($id, true));
	}

	/**
	 * Put the currency on the left or on the right of the amount
	 *
	 * @param int|float $value
	 * @param string    $currency
	 * @param bool      $on_left
	 * @param string    $dec_point
	 * @param string    $thousands_sep
	 *
	 * @return string
	 * @access public
	 */
	public function currency_on_left($value, $currency, $on_left = true, $dec_point = '.', $thousands_sep = '')
	{
		return $on_left ? $currency . number_format(round($value, 2), 2, $dec_point, $thousands_sep) : number_format(round($value, 2), 2, $dec_point, $thousands_sep) . $currency;
	}

	/**
	 * Build pull down menu options of available currency
	 *
	 * @param int $config_value Currency identifier; default: 0
	 *
	 * @return void
	 * @access public
	 */
	public function build_currency_select_menu($config_value = 0)
	{
		// Grab the list of all enabled currencies; 0 is for all data
		$currency_items = $this->entity->get_data($this->operator->build_sql_data(0, true));

		// Process each rule menu item for pull-down
		foreach ($currency_items as $currency_item)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('options', [
				'CURRENCY_ID'        => (int) $currency_item['currency_id'],
				'CURRENCY_ISO_CODE'  => $currency_item['currency_iso_code'],
				'CURRENCY_NAME'      => $currency_item['currency_name'],
				'CURRENCY_SYMBOL'    => $currency_item['currency_symbol'],
				'S_CURRENCY_DEFAULT' => $config_value == $currency_item['currency_id'],
			]);
		}
		unset ($currency_items, $currency_item);
	}
}
