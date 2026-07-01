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
			->disableOriginalConstructor()->getMock();
		$this->language->method('lang')->willReturnArgument(0);

		$this->currency = $this->getMockBuilder('\skouat\ppde\actions\currency')
			->disableOriginalConstructor()->getMock();
		$this->currency->method('format_currency')->willReturn('1,000.00');

		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()->getMock();
	}

	private function get_stats(array $config_data)
	{
		return new \skouat\ppde\controller\main_display_stats(
			new \phpbb\config\config($config_data),
			$this->language,
			$this->currency,
			$this->template
		);
	}

	public function test_goal_langkey_no_goal()
	{
		$stats = $this->get_stats(['ppde_goal' => 0, 'ppde_raised' => 0]);
		$this->assertSame('PPDE_DONATE_NO_GOAL', $stats->get_ppde_goal_langkey('USD', '$', true));
	}

	public function test_goal_langkey_reached()
	{
		$stats = $this->get_stats(['ppde_goal' => 100, 'ppde_raised' => 200]);
		$this->assertSame('PPDE_DONATE_GOAL_REACHED', $stats->get_ppde_goal_langkey('USD', '$', true));
	}

	public function test_goal_langkey_raise()
	{
		$stats = $this->get_stats(['ppde_goal' => 1000, 'ppde_raised' => 500]);
		$this->assertSame('PPDE_DONATE_GOAL_RAISE', $stats->get_ppde_goal_langkey('USD', '$', true));
	}

	public function test_raised_langkey_none()
	{
		$stats = $this->get_stats(['ppde_raised' => 0]);
		$this->assertSame('PPDE_DONATE_NOT_RECEIVED', $stats->get_ppde_raised_langkey('USD', '$', true));
	}

	public function test_raised_langkey_received()
	{
		$stats = $this->get_stats(['ppde_raised' => 250]);
		$this->assertSame('PPDE_DONATE_RECEIVED', $stats->get_ppde_raised_langkey('USD', '$', true));
	}

	public function test_used_langkey_none()
	{
		$stats = $this->get_stats(['ppde_used' => 0, 'ppde_raised' => 100]);
		$this->assertSame('PPDE_DONATE_NOT_USED', $stats->get_ppde_used_langkey('USD', '$', true));
	}

	public function test_used_langkey_partial()
	{
		$stats = $this->get_stats(['ppde_used' => 40, 'ppde_raised' => 100]);
		$this->assertSame('PPDE_DONATE_USED', $stats->get_ppde_used_langkey('USD', '$', true));
	}

	public function test_used_langkey_exceeded()
	{
		// used >= raised => all donations consumed.
		$stats = $this->get_stats(['ppde_used' => 100, 'ppde_raised' => 100]);
		$this->assertSame('PPDE_DONATE_USED_EXCEEDED', $stats->get_ppde_used_langkey('USD', '$', true));
	}
}
