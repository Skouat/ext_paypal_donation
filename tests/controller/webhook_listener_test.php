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

class webhook_listener_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\controller\webhook_listener */
	protected $listener;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $client_factory;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $operator;

	protected function setUp(): void
	{
		parent::setUp();

		if (!defined('ANONYMOUS'))
		{
			define('ANONYMOUS', 1);
		}

		$config = new \phpbb\config\config([
			'sitename'                     => 'My Board',
			'ppde_rest_client_id'          => 'LIVE_CLIENT',
			'ppde_sandbox_rest_client_id'  => 'SANDBOX_CLIENT',
		]);

		$language = $this->createMock(\phpbb\language\language::class);
		$language->method('lang')->willReturnArgument(0);

		$this->operator = $this->createMock(\skouat\ppde\operators\transactions::class);
		$this->client_factory = $this->createMock(\skouat\ppde\api\paypal\client_factory::class);

		$this->listener = new \skouat\ppde\controller\webhook_listener(
			$config,
			$language,
			$this->createMock(\phpbb\log\log::class),
			$this->createMock(\skouat\ppde\actions\core::class),
			$this->createMock(\skouat\ppde\entity\transactions::class),
			$this->operator,
			$this->createMock(\skouat\ppde\api\paypal\webhook_verify::class),
			$this->client_factory,
			$this->createMock(\phpbb\request\request::class),
			$this->createMock(\phpbb\user::class),
			$this->createMock(\skouat\ppde\actions\donation_recorder::class)
		);
	}

	public function test_map_refund_negates_amounts()
	{
		$resource = [
			'id'          => 'REFUND123',
			'amount'      => ['value' => '10.00', 'currency_code' => 'EUR'],
			'create_time' => '2026-01-01T00:00:00Z',
			'seller_payable_breakdown' => [
				'paypal_fee' => ['value' => '0.59'],
				'net_amount' => ['value' => '9.41'],
			],
		];

		$data = $this->invoke('map_refund', [$resource, 'PARENT_TXN', 'uid_42_1700000000', 'Refunded', false]);

		$this->assertSame(-10.0, $data['mc_gross']);
		$this->assertSame(-0.59, $data['mc_fee']);
		$this->assertSame(-9.41, $data['net_amount']);
		$this->assertSame('EUR', $data['mc_currency']);
		$this->assertSame('Refunded', $data['payment_status']);
		$this->assertSame('ppde_rest_refund', $data['txn_type']);
		$this->assertSame('REFUND123', $data['txn_id']);
		$this->assertSame('PARENT_TXN', $data['parent_txn_id']);
		$this->assertSame('uid_42_1700000000', $data['custom']);
	}

	public function test_map_capture_completed()
	{
		// order_id absent => fetch_parties() short-circuits (no network).
		$resource = [
			'id'                          => 'CAP123',
			'amount'                      => ['value' => '20.00', 'currency_code' => 'USD'],
			'custom_id'                   => 'uid_7_1700000000',
			'create_time'                 => '2026-01-01T00:00:00Z',
			'seller_receivable_breakdown' => [
				'paypal_fee'        => ['value' => '1.00'],
				'net_amount'        => ['value' => '19.00'],
				'receivable_amount' => ['value' => '19.00', 'currency_code' => 'USD'],
			],
		];

		$data = $this->invoke('map_capture', [$resource, 'Completed', false]);

		$this->assertSame(20.0, $data['mc_gross']);
		$this->assertSame(1.0, $data['mc_fee']);
		$this->assertSame(19.0, $data['net_amount']);
		$this->assertSame('USD', $data['mc_currency']);
		$this->assertSame(19.0, $data['settle_amount']);
		$this->assertSame('USD', $data['settle_currency']);
		$this->assertSame('Completed', $data['payment_status']);
		$this->assertTrue($data['confirmed']);
		$this->assertSame('CAP123', $data['txn_id']);
		$this->assertSame('ppde_rest_donation', $data['txn_type']);
	}

	public function parent_capture_data()
	{
		return [
			'with up link' => [
				[['rel' => 'self', 'href' => 'https://api.paypal.com/v2/payments/refunds/R1'],
				 ['rel' => 'up',   'href' => 'https://api.paypal.com/v2/payments/captures/CAPTURE999']],
				'CAPTURE999',
			],
			'no links'   => [[], ''],
			'no up rel'  => [[['rel' => 'self', 'href' => 'https://x/y/Z']], ''],
		];
	}

	/** @dataProvider parent_capture_data */
	public function test_extract_parent_capture_id($links, $expected)
	{
		$resource = empty($links) ? [] : ['links' => $links];
		$this->assertSame($expected, $this->invoke('extract_parent_capture_id', [$resource]));
	}

	public function test_resolve_refund_custom_prefers_parent()
	{
		$this->operator->method('get_custom_by_txn_id')->willReturn('uid_42_parent');

		$result = $this->invoke('resolve_refund_custom', [['custom_id' => 'uid_99_refund'], 'PARENT_TXN']);
		$this->assertSame('uid_42_parent', $result);
	}

	public function test_resolve_refund_custom_falls_back_to_resource()
	{
		$this->operator->method('get_custom_by_txn_id')->willReturn('');

		$result = $this->invoke('resolve_refund_custom', [['custom_id' => 'uid_99_refund'], 'PARENT_TXN']);
		$this->assertSame('uid_99_refund', $result);
	}

	/**
	 * Build a listener with a controlled body, signature headers and verification outcome.
	 *
	 * @param string         $raw_body
	 * @param array          $headers
	 * @param bool           $signature_valid
	 * @param \phpbb\log\log $log
	 * @param array          $config_data
	 *
	 * @return \skouat\ppde\controller\webhook_listener
	 * @access private
	 */
	private function make_handle_listener(string $raw_body, array $headers, bool $signature_valid, \phpbb\log\log $log, array $config_data = [])
	{
		$config = new \phpbb\config\config(array_merge([
			'ppde_webhook_sig_failed_last' => 0,
		], $config_data));

		$webhook_verify = $this->createMock(\skouat\ppde\api\paypal\webhook_verify::class);
		$webhook_verify->method('is_valid')->willReturn($signature_valid);

		$request = $this->createMock(\phpbb\request\request::class);
		$request->method('header')->willReturnCallback(static function ($name) use ($headers) {
			return $headers[$name] ?? '';
		});

		$user = $this->createMock(\phpbb\user::class);
		$user->ip = '127.0.0.1';

		// An anonymous subclass replaces the body reader without relying on version-specific partial-mock APIs.
		$listener = new class(
			$config,
			$this->createMock(\phpbb\language\language::class),
			$log,
			$this->createMock(\skouat\ppde\actions\core::class),
			$this->createMock(\skouat\ppde\entity\transactions::class),
			$this->createMock(\skouat\ppde\operators\transactions::class),
			$webhook_verify,
			$this->createMock(\skouat\ppde\api\paypal\client_factory::class),
			$request,
			$user,
			$this->createMock(\skouat\ppde\actions\donation_recorder::class)
		) extends \skouat\ppde\controller\webhook_listener
		{
			public $test_raw_body = '';

			protected function get_raw_input(): string
			{
				return $this->test_raw_body;
			}
		};
		$listener->test_raw_body = $raw_body;

		return $listener;
	}

	/**
	 * @return array
	 * @access private
	 */
	private function valid_headers(): array
	{
		return [
			'Paypal-Transmission-Id'   => 'tid-123',
			'Paypal-Transmission-Time' => '2026-10-25T00:00:00Z',
			'Paypal-Transmission-Sig'  => 'c2ln',
			'Paypal-Cert-Url'          => 'https://api.paypal.com/cert.pem',
			'Paypal-Auth-Algo'         => 'SHA256withRSA',
		];
	}

	public function missing_header_data()
	{
		return [
			['Paypal-Transmission-Id'],
			['Paypal-Transmission-Time'],
			['Paypal-Transmission-Sig'],
			['Paypal-Cert-Url'],
			['Paypal-Auth-Algo'],
		];
	}

	/**
	 * @dataProvider missing_header_data
	 */
	public function test_handle_rejects_missing_header_unlogged($missing_header)
	{
		$headers = $this->valid_headers();
		unset($headers[$missing_header]);

		$log = $this->createMock(\phpbb\log\log::class);
		$log->expects($this->never())->method('add');

		$listener = $this->make_handle_listener('{}', $headers, true, $log);

		$this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST, $listener->handle()->getStatusCode());
	}

	public function test_handle_logs_signature_failure_without_payload_data()
	{
		$log = $this->createMock(\phpbb\log\log::class);
		$log->expects($this->once())
			->method('add')
			->with('critical', ANONYMOUS, '127.0.0.1', 'LOG_PPDE_WEBHOOK_SIG_FAILED', $this->isType('int'), [])
		;

		$listener = $this->make_handle_listener('{"event_type":"<script>alert(1)</script>"}', $this->valid_headers(), false, $log);

		$this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN, $listener->handle()->getStatusCode());
	}

	public function test_handle_throttles_repeated_signature_failures()
	{
		$log = $this->createMock(\phpbb\log\log::class);
		$log->expects($this->once())->method('add');

		$listener = $this->make_handle_listener('{}', $this->valid_headers(), false, $log);

		$this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN, $listener->handle()->getStatusCode());
		$this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN, $listener->handle()->getStatusCode());
	}

	public function test_handle_skips_log_within_interval()
	{
		$log = $this->createMock(\phpbb\log\log::class);
		$log->expects($this->never())->method('add');

		$listener = $this->make_handle_listener('{}', $this->valid_headers(), false, $log, [
			'ppde_webhook_sig_failed_last' => time(),
		]);

		$this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN, $listener->handle()->getStatusCode());
	}

	public function invalid_event_data()
	{
		return [
			'not json'               => ['not json'],
			'missing event_type'     => ['{"resource":{"id":"1"}}'],
			'non-string event_type'  => ['{"event_type":["x"],"resource":{"id":"1"}}'],
			'missing resource'       => ['{"event_type":"PAYMENT.CAPTURE.COMPLETED"}'],
			'non-array resource'     => ['{"event_type":"PAYMENT.CAPTURE.COMPLETED","resource":"x"}'],
		];
	}

	/**
	 * @dataProvider invalid_event_data
	 */
	public function test_handle_rejects_invalid_event($raw_body)
	{
		$log = $this->createMock(\phpbb\log\log::class);
		$log->expects($this->never())->method('add');

		$listener = $this->make_handle_listener($raw_body, $this->valid_headers(), true, $log);

		$this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST, $listener->handle()->getStatusCode());
	}

	private function invoke(string $method, array $args)
	{
		$m = new \ReflectionMethod($this->listener, $method);
		$m->setAccessible(true);
		return $m->invokeArgs($this->listener, $args);
	}

	public function test_resolve_parties_reuses_stored_data_on_upgrade()
	{
		$this->operator->method('get_parties_by_txn_id')->willReturn([
			'first_name'        => 'John',
			'last_name'         => 'Doe',
			'payer_email'       => 'john@example.com',
			'payer_id'          => 'PID',
			'residence_country' => 'FR',
			'receiver_email'    => 'merchant@x.com',
			'receiver_id'       => 'MID',
		]);

		$this->client_factory->expects($this->never())->method('build');

		$resource = [
			'id'                => 'CAP1',
			'supplementary_data' => ['related_ids' => ['order_id' => 'ORDER1']],
		];

		$parties = $this->invoke('resolve_parties', [$resource, false]);

		$this->assertSame('John', $parties['payer']['first_name']);
		$this->assertSame('john@example.com', $parties['payer']['email']);
		$this->assertSame('FR', $parties['payer']['country']);
		$this->assertSame('MID', $parties['payee']['merchant_id']);
	}
}
