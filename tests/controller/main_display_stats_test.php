<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\controller;

class main_display_stats_test extends \phpbb_test_case
{
	protected $language;
	protected $currency;
	protected $template;

	protected function setUp(): void
	{
		parent::setUp();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();

		// NEW: tell the mock what to RETURN.
		// Here lang('SOME_KEY') will simply return 'SOME_KEY',
		// so the test can assert WHICH language key was chosen.
		$this->language->method('lang')->willReturnArgument(0);

		$this->currency = $this->getMockBuilder('\skouat\ppde\actions\currency')
			->disableOriginalConstructor()
			->getMock();

		// NEW: whatever arguments are passed, always return this fixed string.
		$this->currency->method('format_currency')->willReturn('1,000.00');

		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * Helper: build the object under test with a given config.
	 * We use the REAL config object (it just takes an array), which is
	 * simpler than mocking array access.
	 */
	private function get_stats(array $config_data)
	{
		$config = new \phpbb\config\config($config_data);

		return new \skouat\ppde\controller\main_display_stats(
			$config,
			$this->language,
			$this->currency,
			$this->template
		);
	}

	public function test_goal_langkey_no_goal()
	{
		// goal = 0  =>  "no goal defined" message
		$stats = $this->get_stats(['ppde_goal' => 0, 'ppde_raised' => 0]);
		$this->assertSame('PPDE_DONATE_NO_GOAL', $stats->get_ppde_goal_langkey('USD', '$', true));
	}

	public function test_goal_langkey_reached()
	{
		// goal (100) < raised (200)  =>  "goal reached" message
		$stats = $this->get_stats(['ppde_goal' => 100, 'ppde_raised' => 200]);
		$this->assertSame('PPDE_DONATE_GOAL_REACHED', $stats->get_ppde_goal_langkey('USD', '$', true));
	}

	public function test_goal_langkey_raise()
	{
		// goal (1000) >= raised (500)  =>  "help us raise X" message
		$stats = $this->get_stats(['ppde_goal' => 1000, 'ppde_raised' => 500]);
		$this->assertSame('PPDE_DONATE_GOAL_RAISE', $stats->get_ppde_goal_langkey('USD', '$', true));
	}
}
