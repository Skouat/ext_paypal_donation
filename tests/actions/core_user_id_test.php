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

class core_user_id_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\actions\core */
	protected $core;

	protected function setUp(): void
	{
		parent::setUp();

		if (!defined('ANONYMOUS'))
		{
			define('ANONYMOUS', 1);
		}

		$config       = $this->createMock(\phpbb\config\config::class);
		$language     = $this->createMock(\phpbb\language\language::class);
		$notification = $this->createMock(\skouat\ppde\notification\core::class);
		$path_helper  = $this->createMock(\phpbb\path_helper::class);
		$entity       = $this->createMock(\skouat\ppde\entity\transactions::class);
		$operator     = $this->createMock(\skouat\ppde\operators\transactions::class);
		$dispatcher   = $this->createMock(\phpbb\event\dispatcher_interface::class);
		$user         = $this->createMock(\phpbb\user::class);

		$this->core = new \skouat\ppde\actions\core(
			$config, $language, $notification, $path_helper,
			$entity, $operator, $dispatcher, $user, 'php'
		);
	}

	public function custom_data()
	{
		return [
			'standard custom'   => ['uid_42_1700000000', '42'],
			'trailing sep only' => ['uid_42_',           '42'],
			'empty custom'      => ['',                   ANONYMOUS],
			'non-numeric id'    => ['uid_abc_99',         ANONYMOUS],
		];
	}

	/** @dataProvider custom_data */
	public function test_extract_and_validate_user_id($custom, $expected)
	{
		$this->core->set_transaction_data(['custom' => $custom]);

		$this->invoke('extract_user_id');
		$this->invoke('validate_user_id');

		$data = $this->read_transaction_data();

		// Loose comparison: extract yields strings, validate may set the int ANONYMOUS.
		$this->assertEquals($expected, $data['user_id']);
	}

	private function invoke($method)
	{
		$m = new \ReflectionMethod($this->core, $method);
		$m->setAccessible(true);
		$m->invoke($this->core);
	}

	private function read_transaction_data(): array
	{
		$p = new \ReflectionProperty($this->core, 'transaction_data');
		$p->setAccessible(true);
		return $p->getValue($this->core);
	}
}
