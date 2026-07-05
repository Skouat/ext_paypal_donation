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

class currency_controller_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\controller\admin\currency_controller */
	protected $controller;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $template;

	protected function setUp(): void
	{
		parent::setUp();

		$config = new \phpbb\config\config(['ppde_default_currency' => 1]);
		$container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
		$language = $this->createMock(\phpbb\language\language::class);
		$language->method('lang')->willReturnArgument(0);
		$log = $this->createMock(\phpbb\log\log::class);
		$locale = $this->createMock(\skouat\ppde\actions\locale_icu::class);
		$entity = $this->createMock(\skouat\ppde\entity\currency::class);
		$operator = $this->createMock(\skouat\ppde\operators\currency::class);
		$request = $this->createMock(\phpbb\request\request::class);
		$this->template = $this->createMock(\phpbb\template\template::class);

		$user = $this->createMock(\phpbb\user::class);
		$user->data = [
			'user_id'        => 2,
			'session_id'     => 'mock_session_id_123',
			'user_form_salt' => 'mock_form_salt_456',
		];
		$GLOBALS['user'] = $user;

		$this->controller = new \skouat\ppde\controller\admin\currency_controller(
			$config,
			$container,
			$language,
			$log,
			$locale,
			$entity,
			$operator,
			$request,
			$this->template,
			$user
		);

		$this->controller->set_page_url('adm/style/index.php');
	}

	protected function tearDown(): void
	{
		unset($GLOBALS['user']);
		parent::tearDown();
	}

	public function test_currency_assign_template_vars_enabled_non_default()
	{
		$data = [
			'currency_id'     => 2,
			'currency_name'   => 'Euro',
			'currency_enable' => 1,
		];

		$method = new \ReflectionMethod($this->controller, 'currency_assign_template_vars');
		$method->setAccessible(true);

		$this->template->expects($this->once())
			->method('assign_block_vars')
			->with('currency', $this->callback(function($vars) {
				return $vars['CURRENCY_NAME'] === 'Euro'
					&& $vars['CURRENCY_ENABLED'] === true
					&& $vars['L_ENABLE_DISABLE'] === 'DISABLE'
					&& $vars['S_DEFAULT'] === false;
			}));

		$method->invoke($this->controller, $data);
	}

	public function test_currency_assign_template_vars_disabled_default()
	{
		$data = [
			'currency_id'     => 1,
			'currency_name'   => 'U.S. Dollar',
			'currency_enable' => 0,
		];

		$method = new \ReflectionMethod($this->controller, 'currency_assign_template_vars');
		$method->setAccessible(true);

		$this->template->expects($this->once())
			->method('assign_block_vars')
			->with('currency', $this->callback(function($vars) {
				return $vars['CURRENCY_NAME'] === 'U.S. Dollar'
					&& $vars['CURRENCY_ENABLED'] === false
					&& $vars['L_ENABLE_DISABLE'] === 'ENABLE'
					&& $vars['S_DEFAULT'] === true;
			}));

		$method->invoke($this->controller, $data);
	}
}
