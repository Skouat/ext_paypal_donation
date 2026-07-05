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

	protected function setUp(): void
	{
		parent::setUp();

		$this->phpbb_auth = $this->createMock(\phpbb\auth\auth::class);

		$this->auth_service = new \skouat\ppde\actions\auth(
			$this->phpbb_auth,
			new \phpbb\config\config([]),
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
}
