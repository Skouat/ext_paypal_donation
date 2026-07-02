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

	private function valid_headers(): array
	{
		return [
			'transmission_id'   => 'tid-123',
			'transmission_time' => '2026-01-01T00:00:00Z',
			'transmission_sig'  => base64_encode('signature'),
			'cert_url'          => 'https://www.paypal.com/cert.pem',
			'auth_algo'         => 'SHA256withRSA',
		];
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

	public function test_is_valid_empty_webhook_id()
	{
		$this->assertFalse($this->verify->is_valid('body', $this->valid_headers(), ''));
	}

	public function missing_header_data()
	{
		return [
			['transmission_id'],
			['transmission_time'],
			['transmission_sig'],
			['cert_url'],
			['auth_algo'],
		];
	}

	/** @dataProvider missing_header_data */
	public function test_is_valid_missing_header($key)
	{
		$headers = $this->valid_headers();
		$headers[$key] = '';

		$this->assertFalse($this->verify->is_valid('body', $headers, 'WEBHOOK_ID'));
	}

	public function test_is_valid_rejects_non_paypal_cert_url()
	{
		$headers = $this->valid_headers();
		$headers['cert_url'] = 'https://evil.example.com/cert.pem';

		$this->assertFalse($this->verify->is_valid('body', $headers, 'WEBHOOK_ID'));
	}

	public function test_is_valid_rejects_wrong_auth_algo_before_any_io()
	{
		$cache = $this->createMock(\phpbb\cache\driver\driver_interface::class);
		$cache->expects($this->never())->method('get');
		$verify = new \skouat\ppde\api\paypal\webhook_verify($cache, new \phpbb\config\config([]));

		$headers = $this->valid_headers();
		$headers['auth_algo'] = 'WRONG_ALGO';

		$this->assertFalse($verify->is_valid('raw-body', $headers, 'WEBHOOK_ID'));
	}

	public function test_is_valid_rejects_bad_signature_with_valid_algo()
	{
		if (!extension_loaded('openssl'))
		{
			$this->markTestSkipped('openssl is required for this test.');
		}

		$res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
		if ($res === false)
		{
			$this->markTestSkipped('Unable to generate an RSA key pair.');
		}
		$public_pem = openssl_pkey_get_details($res)['key'];

		$cache = $this->createMock(\phpbb\cache\driver\driver_interface::class);
		$cache->method('get')->willReturn($public_pem);
		$verify = new \skouat\ppde\api\paypal\webhook_verify($cache, new \phpbb\config\config([]));

		$this->assertFalse($verify->is_valid('raw-body', $this->valid_headers(), 'WEBHOOK_ID'));
	}
}
