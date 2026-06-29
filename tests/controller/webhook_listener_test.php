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

		$this->listener = new \skouat\ppde\controller\webhook_listener(
			$config,
			$language,
			$this->createMock(\phpbb\log\log::class),
			$this->createMock(\skouat\ppde\actions\core::class),
			$this->createMock(\skouat\ppde\entity\transactions::class),
			$this->operator,
			$this->createMock(\skouat\ppde\api\paypal\webhook_verify::class),
			$this->createMock(\skouat\ppde\api\paypal\client_factory::class),
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
		// No supplementary_data => order_id '' => fetch_parties() short-circuits (no network).
		$resource = [
			'id'          => 'CAP123',
			'amount'      => ['value' => '20.00', 'currency_code' => 'USD'],
			'custom_id'   => 'uid_7_1700000000',
			'create_time' => '2026-01-01T00:00:00Z',
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

	private function invoke(string $method, array $args)
	{
		$m = new \ReflectionMethod($this->listener, $method);
		$m->setAccessible(true);
		return $m->invokeArgs($this->listener, $args);
	}
}
