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

class webhook_verify_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\api\paypal\webhook_verify */
	protected $verify;

	protected function setUp(): void
	{
		parent::setUp();

		$cache  = $this->createMock(\phpbb\cache\driver\driver_interface::class);
		$config = new \phpbb\config\config([]);

		$this->verify = new \skouat\ppde\api\paypal\webhook_verify($cache, $config);
	}

	public function cert_url_data()
	{
		return [
			'apex paypal.com'   => ['https://api.paypal.com/cert.pem', true],
			'www subdomain'     => ['https://www.paypal.com/cert.pem', true],
			'sandbox subdomain' => ['https://www.sandbox.paypal.com/cert.pem', true],
			'http not https'    => ['http://www.paypal.com/cert.pem', false],
			'lookalike domain'  => ['https://paypal.com.evil.com/cert.pem', false],
			'prefixed domain'   => ['https://evilpaypal.com/cert.pem', false],
			'not paypal'        => ['https://example.com/cert.pem', false],
			'empty string'      => ['', false],
			'no scheme'         => ['www.paypal.com/cert.pem', false],
		];
	}

	/** @dataProvider cert_url_data */
	public function test_is_paypal_cert_url($url, $expected)
	{
		$method = new \ReflectionMethod($this->verify, 'is_paypal_cert_url');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($this->verify, $url));
	}
}
