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

class main_controller_test extends \phpbb_test_case
{
	private function make_controller(array $config_data, int $user_type = 0)
	{
		if (!defined('USER_FOUNDER'))
		{
			define('USER_FOUNDER', 3);
		}

		$user = $this->createMock(\phpbb\user::class);
		$user->data = ['user_type' => $user_type];

		return new \skouat\ppde\controller\main_controller(
			new \phpbb\config\config($config_data),
			$this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class),
			$this->createMock(\phpbb\controller\helper::class),
			$this->createMock(\phpbb\language\language::class),
			$this->createMock(\skouat\ppde\actions\auth::class),
			$this->createMock(\skouat\ppde\actions\currency::class),
			$this->createMock(\phpbb\request\request::class),
			$this->createMock(\phpbb\template\template::class),
			$user,
			$this->createMock(\phpbb\user_loader::class),
			'./',
			'php'
		);
	}

	public function test_is_donation_active()
	{
		$this->assertTrue($this->make_controller(['ppde_enable' => 1])->is_donation_active());
		$this->assertFalse($this->make_controller(['ppde_enable' => 0])->is_donation_active());
	}

	public function donorlist_data()
	{
		return [
			'both on'        => [1, 1, true],
			'donation off'   => [0, 1, false],
			'donorlist off'  => [1, 0, false],
		];
	}

	/** @dataProvider donorlist_data */
	public function test_donorlist_is_enabled($enable, $donorlist, $expected)
	{
		$controller = $this->make_controller([
			'ppde_enable'               => $enable,
			'ppde_ipn_donorlist_enable' => $donorlist,
		]);

		$this->assertSame($expected, $controller->donorlist_is_enabled());
	}

	public function founder_data()
	{
		// founder_enable, user_type, expected
		return [
			'restricted, user is founder' => [1, USER_FOUNDER, true],
			'restricted, normal user'     => [1, 0,           false],
			'open, normal user'           => [0, 0,           true],
			'open, founder'               => [0, USER_FOUNDER, true],
		];
	}

	/** @dataProvider founder_data */
	public function test_is_sandbox_founder_enable($founder_enable, $user_type, $expected)
	{
		$controller = $this->make_controller(
			['ppde_sandbox_founder_enable' => $founder_enable],
			$user_type
		);

		$this->assertSame($expected, $controller->is_sandbox_founder_enable());
	}

	public function use_sandbox_data()
	{
		// enable, sandbox, founder_enable, user_type, expected
		return [
			'all conditions met (open)'      => [1, 1, 0, 0,            true],
			'founder-only, is founder'       => [1, 1, 1, USER_FOUNDER, true],
			'founder-only, not founder'      => [1, 1, 1, 0,            false],
			'sandbox disabled'               => [1, 0, 0, 0,            false],
			'donation disabled'              => [0, 1, 0, 0,            false],
		];
	}

	/** @dataProvider use_sandbox_data */
	public function test_use_sandbox($enable, $sandbox, $founder_enable, $user_type, $expected)
	{
		$controller = $this->make_controller([
			'ppde_enable'                 => $enable,
			'ppde_sandbox_enable'         => $sandbox,
			'ppde_sandbox_founder_enable' => $founder_enable,
		], $user_type);

		$this->assertSame($expected, $controller->use_sandbox());
	}
}
