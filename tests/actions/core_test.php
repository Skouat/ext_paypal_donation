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

class core_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\actions\core */
	protected $core;

	protected function setUp(): void
	{
		parent::setUp();

		$config       = $this->getMockBuilder('\phpbb\config\config')->disableOriginalConstructor()->getMock();
		$language     = $this->getMockBuilder('\phpbb\language\language')->disableOriginalConstructor()->getMock();
		$notification = $this->getMockBuilder('\skouat\ppde\notification\core')->disableOriginalConstructor()->getMock();
		$path_helper  = $this->getMockBuilder('\phpbb\path_helper')->disableOriginalConstructor()->getMock();
		$entity       = $this->getMockBuilder('\skouat\ppde\entity\transactions')->disableOriginalConstructor()->getMock();
		$operator     = $this->getMockBuilder('\skouat\ppde\operators\transactions')->disableOriginalConstructor()->getMock();
		$dispatcher   = $this->getMockBuilder('\phpbb\event\dispatcher_interface')->disableOriginalConstructor()->getMock();
		$user         = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();

		$this->core = new \skouat\ppde\actions\core(
			$config, $language, $notification, $path_helper,
			$entity, $operator, $dispatcher, $user, 'php'
		);
	}

	public function net_amount_data()
	{
		return [
			'simple subtraction' => [100,   3,    '97.00'],
			'with decimals'      => [50.50, 0.50, '50.00'],
			'no fee'             => [20,    0,    '20.00'],
			'rounds to 2 dec.'   => [9.999, 0,    '10.00'],
		];
	}

	/** @dataProvider net_amount_data */
	public function test_net_amount($amount, $fee, $expected)
	{
		$this->assertSame($expected, $this->core->net_amount($amount, $fee));
	}
}
