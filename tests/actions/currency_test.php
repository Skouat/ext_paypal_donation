<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\actions;

class currency_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\actions\currency */
	protected $currency;
	protected $entity;
	protected $locale;
	protected $operator;
	protected $template;

	protected function setUp(): void
	{
		parent::setUp();

		$this->entity = $this->getMockBuilder('\skouat\ppde\entity\currency')
			->disableOriginalConstructor()->getMock();
		$this->locale = $this->getMockBuilder('\skouat\ppde\actions\locale_icu')
			->disableOriginalConstructor()->getMock();
		$this->operator = $this->getMockBuilder('\skouat\ppde\operators\currency')
			->disableOriginalConstructor()->getMock();
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()->getMock();

		$this->currency = new \skouat\ppde\actions\currency(
			$this->entity,
			$this->locale,
			$this->operator,
			$this->template
		);
	}

	public function currency_on_left_data()
	{
		return [
			'dollar on left'         => [20,     '$', true,  '$20.00'],
			'euro on right'          => [15,     '€', false, '15.00€'],
			'rounded to 2 decimals'  => [9.999,  '$', true,  '$10.00'],
			'zero value'             => [0,      '$', true,  '$0.00'],
			'no thousands separator' => [1234.5, '$', false, '1234.50$'],
		];
	}

	/** @dataProvider currency_on_left_data */
	public function test_currency_on_left($value, $symbol, $on_left, $expected)
	{
		$this->assertSame($expected, $this->currency->currency_on_left($value, $symbol, $on_left));
	}

	public function test_get_currency_data_empty_iso()
	{
		$expected = [['currency_iso_code' => '', 'currency_symbol' => '', 'currency_on_left' => false]];

		$this->assertSame($expected, $this->currency->get_currency_data(''));
	}

	public function test_get_currency_data_found()
	{
		$db_data = [['currency_iso_code' => 'USD', 'currency_symbol' => '$', 'currency_on_left' => true]];

		$this->entity->method('get_id')->willReturn(1);
		$this->entity->method('get_data')->willReturn($db_data);

		$this->assertSame($db_data, $this->currency->get_currency_data('USD'));
	}

	public function test_get_currency_data_not_found()
	{
		$this->entity->method('get_id')->willReturn(0);

		$expected = [['currency_iso_code' => 'XYZ', 'currency_symbol' => 'XYZ', 'currency_on_left' => false]];

		$this->assertSame($expected, $this->currency->get_currency_data('XYZ'));
	}

	public function test_get_currency_data_found_but_empty()
	{
		$this->entity->method('get_id')->willReturn(1);
		$this->entity->method('get_data')->willReturn([]);

		$expected = [['currency_iso_code' => 'EUR', 'currency_symbol' => 'EUR', 'currency_on_left' => false]];

		$this->assertSame($expected, $this->currency->get_currency_data('EUR'));
	}

	public function test_format_currency_without_locale()
	{
		$this->locale->method('is_locale_configured')->willReturn(false);

		$this->assertSame('$20.00', $this->currency->format_currency(20, 'USD', '$', true));
	}

	public function test_format_currency_with_locale()
	{
		$this->locale->method('is_locale_configured')->willReturn(true);
		$this->locale->method('numfmt_create')->willReturn(null);
		$this->locale->method('numfmt_format_currency')->willReturn('US$20.00');

		$this->assertSame('US$20.00', $this->currency->format_currency(20, 'USD', '$', true));
	}

	public function test_build_currency_select_menu()
	{
		$items = [
			['currency_id' => 1, 'currency_iso_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$'],
			['currency_id' => 2, 'currency_iso_code' => 'EUR', 'currency_name' => 'Euro',   'currency_symbol' => '€'],
		];

		$this->entity->method('get_data')->willReturn($items);

		$calls = [];
		$this->template->expects($this->exactly(2))
			->method('assign_block_vars')
			->willReturnCallback(static function ($block, $vars) use (&$calls) {
				$calls[] = ['block' => $block, 'vars' => $vars];
			});

		$this->currency->build_currency_select_menu(1);

		$this->assertSame('options', $calls[0]['block']);
		$this->assertSame([
			'CURRENCY_ID'        => 1,
			'CURRENCY_ISO_CODE'  => 'USD',
			'CURRENCY_NAME'      => 'Dollar',
			'CURRENCY_SYMBOL'    => '$',
			'S_CURRENCY_DEFAULT' => true,
		], $calls[0]['vars']);

		$this->assertSame('options', $calls[1]['block']);
		$this->assertSame([
			'CURRENCY_ID'        => 2,
			'CURRENCY_ISO_CODE'  => 'EUR',
			'CURRENCY_NAME'      => 'Euro',
			'CURRENCY_SYMBOL'    => '€',
			'S_CURRENCY_DEFAULT' => false,
		], $calls[1]['vars']);
	}
}
