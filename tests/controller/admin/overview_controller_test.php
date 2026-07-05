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

class overview_controller_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\controller\admin\overview_controller */
	protected $controller;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $locale;

	protected function setUp(): void
	{
		parent::setUp();

		// Installed exactly 10 days ago, so per-day averages are deterministic.
		$install_date = time() - (10 * 86400);

		$this->config = new \phpbb\config\config([
			'ppde_install_date'           => $install_date,
			'ppde_transactions_count'     => 50,
			'ppde_known_donors_count'     => 30,
			'ppde_anonymous_donors_count' => 10,
			'ppde_first_start'            => 1,
		]);

		$auth = $this->createMock(\phpbb\auth\auth::class);
		$language = $this->createMock(\phpbb\language\language::class);
		$log = $this->createMock(\phpbb\log\log::class);
		$core_action = $this->createMock(\skouat\ppde\actions\core::class);
		$this->locale = $this->createMock(\skouat\ppde\actions\locale_icu::class);
		$main_controller = $this->createMock(\skouat\ppde\controller\main_controller::class);
		$transactions_controller = $this->createMock(\skouat\ppde\controller\admin\transactions_controller::class);
		$ext_manager = $this->createMock(\skouat\ppde\controller\extension_manager::class);
		$request = $this->createMock(\phpbb\request\request::class);
		$template = $this->createMock(\phpbb\template\template::class);
		$user = $this->createMock(\phpbb\user::class);

		$this->controller = new \skouat\ppde\controller\admin\overview_controller(
			$auth,
			$this->config,
			$language,
			$log,
			$core_action,
			$this->locale,
			$main_controller,
			$transactions_controller,
			$ext_manager,
			$request,
			$template,
			$user,
			'adm/',
			'./',
			'php'
		);
	}

	public function test_get_install_days()
	{
		$method = new \ReflectionMethod($this->controller, 'get_install_days');
		$method->setAccessible(true);

		$this->assertEquals(10.0, $method->invoke($this->controller), '', 0.01);
	}

	public function stats_data()
	{
		return [
			'transactions average' => ['ppde_transactions_count', '5.00'],
			'known donors average' => ['ppde_known_donors_count', '3.00'],
			'anonymous average'    => ['ppde_anonymous_donors_count', '1.00'],
		];
	}

	/** @dataProvider stats_data */
	public function test_per_day_stats($config_key, $expected_average)
	{
		$method = new \ReflectionMethod($this->controller, 'per_day_stats');
		$method->setAccessible(true);

		$this->assertSame($expected_average, $method->invoke($this->controller, $config_key));
	}

	public function test_ppde_first_start_runs_diagnostics_on_enable()
	{
		$this->locale->expects($this->once())->method('set_intl_info');
		$this->locale->expects($this->once())->method('set_intl_detected');

		$method = new \ReflectionMethod($this->controller, 'ppde_first_start');
		$method->setAccessible(true);
		$method->invoke($this->controller);

		$this->assertSame('0', $this->config['ppde_first_start']);
	}

	public function test_ppde_first_start_does_nothing_if_already_initialized()
	{
		$this->config->set('ppde_first_start', '0');

		$this->locale->expects($this->never())->method('set_intl_info');
		$this->locale->expects($this->never())->method('set_intl_detected');

		$method = new \ReflectionMethod($this->controller, 'ppde_first_start');
		$method->setAccessible(true);
		$method->invoke($this->controller);
	}

	public function test_prerequisites_detection_in_environment()
	{
		$this->assertTrue(extension_loaded('openssl'), 'OpenSSL extension must be loaded.');
		$this->assertTrue(extension_loaded('curl'), 'cURL extension must be loaded.');
	}
}
