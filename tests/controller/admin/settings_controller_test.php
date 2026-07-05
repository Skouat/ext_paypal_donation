<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\controller\admin;

class settings_controller_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\controller\admin\settings_controller */
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();

		$config = new \phpbb\config\config([]);
		$language = $this->createMock(\phpbb\language\language::class);
		$log = $this->createMock(\phpbb\log\log::class);
		$auth = $this->createMock(\skouat\ppde\actions\auth::class);
		$currency = $this->createMock(\skouat\ppde\actions\currency::class);
		$locale = $this->createMock(\skouat\ppde\actions\locale_icu::class);
		$request = $this->createMock(\phpbb\request\request::class);
		$template = $this->createMock(\phpbb\template\template::class);
		$user = $this->createMock(\phpbb\user::class);

		$this->controller = new \skouat\ppde\controller\admin\settings_controller(
			$config,
			$language,
			$log,
			$auth,
			$currency,
			$locale,
			$request,
			$template,
			$user
		);
	}

	public function rebuild_items_data()
	{
		return [
			'duplicates and sorting' => ['10,5,20,5,10', 15, '5,10,15,20'],
			'non-numeric ignored'    => ['10,abc,20',     0, '10,20'],
			'only zeroes'            => ['0,0,0',         0, ''],
		];
	}

	/** @dataProvider rebuild_items_data */
	public function test_rebuild_items_list($input, $added, $expected)
	{
		$method = new \ReflectionMethod($this->controller, 'rebuild_items_list');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($this->controller, $input, $added));
	}

	public function test_build_stat_position_select_menu()
	{
		$template = $this->createMock(\phpbb\template\template::class);

		$property = new \ReflectionProperty($this->controller, 'template');
		$property->setAccessible(true);
		$property->setValue($this->controller, $template);

		$calls = [];
		$template->expects($this->exactly(3))
			->method('assign_block_vars')
			->willReturnCallback(static function ($block, $vars) use (&$calls) {
				$calls[] = ['block' => $block, 'vars' => $vars];
			})
		;

		$this->controller->build_stat_position_select_menu('bottom');

		$this->assertSame('positions_options', $calls[0]['block']);
		$this->assertSame('top', $calls[0]['vars']['POSITION_NAME']);
		$this->assertFalse($calls[0]['vars']['S_DEFAULT']);

		$this->assertSame('positions_options', $calls[1]['block']);
		$this->assertSame('bottom', $calls[1]['vars']['POSITION_NAME']);
		$this->assertTrue($calls[1]['vars']['S_DEFAULT']);
	}
}
