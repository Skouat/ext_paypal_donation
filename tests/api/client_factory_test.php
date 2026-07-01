<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\api;

class client_factory_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\api\paypal\client_factory */
	protected $factory;
	/** @var \phpbb\config\config */
	protected $config;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config([
			'ppde_rest_client_id'         => 'LIVE_CLIENT_ID_123',
			'ppde_rest_secret'            => 'LIVE_SECRET_456',
			'ppde_sandbox_rest_client_id' => 'SANDBOX_CLIENT_ID_789',
			'ppde_sandbox_rest_secret'    => 'SANDBOX_SECRET_abc',
		]);

		$language = $this->createMock(\phpbb\language\language::class);

		$this->factory = new \skouat\ppde\api\paypal\client_factory($this->config, $language);
	}

	public function test_is_configured_with_valid_credentials()
	{
		$this->assertTrue($this->factory->is_configured(false));
		$this->assertTrue($this->factory->is_configured(true));
	}

	public function test_is_not_configured_when_missing()
	{
		$this->config->set('ppde_rest_client_id', '');
		$this->config->set('ppde_rest_secret', '');
		$this->assertFalse($this->factory->is_configured(false));

		$this->config->set('ppde_sandbox_rest_client_id', 'SOMETHING');
		$this->config->set('ppde_sandbox_rest_secret', '');
		$this->assertFalse($this->factory->is_configured(true));
	}

	public function test_get_client_id()
	{
		$this->assertSame('LIVE_CLIENT_ID_123', $this->factory->get_client_id(false));
		$this->assertSame('SANDBOX_CLIENT_ID_789', $this->factory->get_client_id(true));
	}

	public function test_get_credentials_matching_env()
	{
		$method = new \ReflectionMethod($this->factory, 'get_credentials');
		$method->setAccessible(true);

		$live = $method->invoke($this->factory, false);
		$this->assertSame('LIVE_CLIENT_ID_123', $live[0]);
		$this->assertSame('LIVE_SECRET_456', $live[1]);

		$sandbox = $method->invoke($this->factory, true);
		$this->assertSame('SANDBOX_CLIENT_ID_789', $sandbox[0]);
		$this->assertSame('SANDBOX_SECRET_abc', $sandbox[1]);
	}
}
