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
		if (!function_exists('iconv'))
		{
			$this->markTestSkipped('iconv required for transliteration test.');
		}

		$result = $this->invoke('build_soft_descriptor', ['Forêt']);

		// Locale-dependent: 'ê' becomes 'e' or is dropped; only allowed chars survive.
		$this->assertSame($result, preg_replace('/[^A-Za-z0-9 .*-]/', '', $result));
		$this->assertContains($result, ['Foret', 'Fort']);
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
