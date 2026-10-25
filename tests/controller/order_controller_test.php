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

class order_controller_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\controller\order_controller */
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();

		$this->controller = new \skouat\ppde\controller\order_controller(
			new \phpbb\config\config([]),
			$this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class),
			$this->createMock(\phpbb\controller\helper::class),
			$this->createMock(\phpbb\language\language::class),
			$this->createMock(\skouat\ppde\actions\auth::class),
			$this->createMock(\skouat\ppde\actions\currency::class),
			$this->createMock(\phpbb\request\request::class),
			$this->createMock(\phpbb\template\template::class),
			$this->createMock(\phpbb\user::class),
			$this->createMock(\phpbb\user_loader::class),
			'./',
			'php'
		);
	}

	protected function tearDown(): void
	{
		unset($GLOBALS['user']);
		parent::tearDown();
	}

	/**
	 * Build a controller that passes the AJAX and link-hash checks of guard()
	 *
	 * @param bool $ppde_enable  Value of the ppde_enable config
	 * @param bool $can_use_ppde Result of the donation permission check
	 *
	 * @return \skouat\ppde\controller\order_controller
	 * @access protected
	 */
	protected function get_guarded_controller(bool $ppde_enable, bool $can_use_ppde): \skouat\ppde\controller\order_controller
	{
		$user = $this->createMock(\phpbb\user::class);
		$user->data = [
			'user_id'        => 2,
			'session_id'     => 'mock_session_id_123',
			'user_form_salt' => 'mock_form_salt_456',
		];
		$GLOBALS['user'] = $user;

		// Computed by the core helper so the test does not depend on the hash formula.
		$hash = generate_link_hash('ppde_donate');

		$language = $this->createMock(\phpbb\language\language::class);
		$language->method('lang')->willReturnArgument(0);

		$auth = $this->createMock(\skouat\ppde\actions\auth::class);
		$auth->method('can_use_ppde')->willReturn($can_use_ppde);

		$request = $this->createMock(\phpbb\request\request::class);
		$request->method('is_ajax')->willReturn(true);
		$request->method('variable')->willReturnCallback(function ($var_name, $default) use ($hash)
		{
			return ($var_name === 'hash') ? $hash : $default;
		});

		return new \skouat\ppde\controller\order_controller(
			new \phpbb\config\config(['ppde_enable' => (int) $ppde_enable]),
			$this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class),
			$this->createMock(\phpbb\controller\helper::class),
			$language,
			$auth,
			$this->createMock(\skouat\ppde\actions\currency::class),
			$request,
			$this->createMock(\phpbb\template\template::class),
			$user,
			$this->createMock(\phpbb\user_loader::class),
			'./',
			'php'
		);
	}

	public function test_create_reports_unavailable_when_donations_disabled()
	{
		$response = $this->get_guarded_controller(false, true)->create();

		$this->assertSame(\Symfony\Component\HttpFoundation\JsonResponse::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
		$this->assertSame(['error' => 'PPDE_DONATION_UNAVAILABLE'], json_decode($response->getContent(), true));
	}

	public function test_create_reports_forbidden_when_permission_missing()
	{
		$response = $this->get_guarded_controller(true, false)->create();

		$this->assertSame(\Symfony\Component\HttpFoundation\JsonResponse::HTTP_FORBIDDEN, $response->getStatusCode());
		$this->assertSame(['error' => 'NOT_AUTHORISED'], json_decode($response->getContent(), true));
	}

	public function test_soft_descriptor_keeps_allowed_chars()
	{
		$this->assertSame('A B * C', $this->invoke('build_soft_descriptor', ['A & B * C']));
	}

	public function test_soft_descriptor_truncates_to_22()
	{
		$result = $this->invoke('build_soft_descriptor', [str_repeat('a', 30)]);
		$this->assertSame(str_repeat('a', 22), $result);
	}

	public function test_soft_descriptor_handles_accents()
	{
		$this->assertSame('Foret', $this->invoke('build_soft_descriptor', ['Forêt']));
	}

	public function test_soft_descriptor_empty_when_all_stripped()
	{
		$this->assertSame('', $this->invoke('build_soft_descriptor', ['😀🚀🎉']));
	}

	public function test_truncate_description_short_unchanged()
	{
		$this->guard_utf8();
		$this->assertSame('Hello', $this->invoke('truncate_description', ['Hello']));
	}

	public function test_truncate_description_long_capped_at_127()
	{
		$this->guard_utf8();
		$result = $this->invoke('truncate_description', [str_repeat('a', 130)]);

		$this->assertSame(127, utf8_strlen($result));
		$this->assertStringEndsWith('...', $result);
	}

	private function guard_utf8()
	{
		if (!function_exists('utf8_strlen') || !function_exists('utf8_substr'))
		{
			$this->markTestSkipped('phpBB UTF-8 helpers are not loaded.');
		}
	}

	private function invoke(string $method, array $args)
	{
		$m = new \ReflectionMethod($this->controller, $method);
		$m->setAccessible(true);
		return $m->invokeArgs($this->controller, $args);
	}
}
