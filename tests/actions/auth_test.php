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

class auth_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\actions\auth */
	protected $auth_service;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $phpbb_auth;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->phpbb_auth = $this->createMock(\phpbb\auth\auth::class);
		$this->user = $this->createMock(\phpbb\user::class);

		$this->auth_service = new \skouat\ppde\actions\auth(
			$this->phpbb_auth,
			new \phpbb\config\config([]),
			$this->user,
			'./',
			'php'
		);
	}

	public function acl_data()
	{
		return [
			'allowed'     => [true,  true],
			'not allowed' => [false, false],
		];
	}

	/** @dataProvider acl_data */
	public function test_can_use_ppde_delegates_to_acl($acl_return, $expected)
	{
		$this->phpbb_auth->expects($this->once())
			->method('acl_get')
			->with('u_ppde_use')
			->willReturn($acl_return);

		$this->assertSame($expected, $this->auth_service->can_use_ppde());
	}

	/** @dataProvider acl_data */
	public function test_can_view_ppde_donorlist_delegates_to_acl($acl_return, $expected)
	{
		$this->phpbb_auth->expects($this->once())
			->method('acl_get')
			->with('u_ppde_view_donorlist')
			->willReturn($acl_return);

		$this->assertSame($expected, $this->auth_service->can_view_ppde_donorlist());
	}

	/** @dataProvider acl_data */
	public function test_can_manage_ppde_delegates_to_acl($acl_return, $expected)
	{
		$this->phpbb_auth->expects($this->once())
			->method('acl_get')
			->with('a_ppde_manage')
			->willReturn($acl_return);

		$this->assertSame($expected, $this->auth_service->can_manage_ppde());
	}

	public function is_in_admin_data()
	{
		return [
			'IN_ADMIN undefined'                    => [false, null, false],
			'IN_ADMIN defined, session_admin unset'  => [true, null, false],
			'IN_ADMIN defined, session_admin false'  => [true, false, false],
			'IN_ADMIN defined, session_admin true'   => [true, true, true],
		];
	}

	/** @dataProvider is_in_admin_data */
	public function test_is_in_admin($in_admin_defined, $session_admin, $expected)
	{
		if ($in_admin_defined && !defined('IN_ADMIN'))
		{
			define('IN_ADMIN', true);
		}

		if (!$in_admin_defined && defined('IN_ADMIN'))
		{
			$this->markTestSkipped('IN_ADMIN already defined by a previous test; cannot unset a constant.');
		}

		$this->user->data = $session_admin !== null ? ['session_admin' => $session_admin] : [];

		$this->assertSame($expected, $this->auth_service->is_in_admin());
	}
}
