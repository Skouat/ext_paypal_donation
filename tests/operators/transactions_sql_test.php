<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\operators;

class transactions_sql_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\operators\transactions */
	protected $operator;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	protected function setUp(): void
	{
		parent::setUp();

		if (!defined('ANONYMOUS'))
		{
			define('ANONYMOUS', 1);
		}
		if (!defined('USERS_TABLE'))
		{
			define('USERS_TABLE', 'phpbb_users');
		}

		$this->db = $this->createMock(\phpbb\db\driver\driver_interface::class);

		$this->operator = new \skouat\ppde\operators\transactions($this->db, 'phpbb_ppde_txn_log');
	}

	public function test_sql_donorlist_ary_basic()
	{
		$ary = $this->operator->sql_donorlist_ary();

		$this->assertSame('txn.user_id, txn.mc_currency', $ary['SELECT']);
		$this->assertStringContainsString('txn.user_id <> ' . ANONYMOUS, $ary['WHERE']);
		$this->assertStringContainsString("payment_status = 'Completed'", $ary['WHERE']);
		$this->assertStringContainsString('txn.test_ipn = 0', $ary['WHERE']);
		$this->assertSame('txn.user_id, txn.mc_currency', $ary['GROUP_BY']);
		$this->assertArrayNotHasKey('ORDER_BY', $ary);
		$this->assertArrayNotHasKey('LEFT_JOIN', $ary);
	}

	public function test_sql_donorlist_ary_detailed_with_order()
	{
		$ary = $this->operator->sql_donorlist_ary(true, 'amount DESC');

		$this->assertStringNotContainsString('MAX(txn.transaction_id)', $ary['SELECT']);
		$this->assertStringContainsString('SUM(txn.mc_gross) AS amount', $ary['SELECT']);
		$this->assertArrayHasKey('LEFT_JOIN', $ary);
		$this->assertSame('amount DESC', $ary['ORDER_BY']);
	}

	public function test_sql_last_donation_ary()
	{
		$ary = $this->operator->sql_last_donation_ary('42abc');

		$this->assertSame('txn.payment_date, txn.mc_gross, txn.mc_currency', $ary['SELECT']);
		$this->assertSame('txn.transaction_id = 42', $ary['WHERE']);
	}

	public function test_get_logs_sql_ary_without_log_time()
	{
		$ary = $this->operator->get_logs_sql_ary('', 'txn.payment_date DESC', 0);

		$this->assertSame('txn.user_id = u.user_id ', $ary['WHERE']);
		$this->assertSame('txn.payment_date DESC', $ary['ORDER_BY']);
	}

	public function test_get_logs_sql_ary_with_log_time_prepends_date_filter()
	{
		$ary = $this->operator->get_logs_sql_ary('', 'txn.payment_date DESC', 1700000000);

		$this->assertStringContainsString('txn.payment_date >= 1700000000', $ary['WHERE']);
		$this->assertStringContainsString('txn.user_id = u.user_id', $ary['WHERE']);
	}

	public function test_build_marked_where_sql()
	{
		$this->db->method('sql_in_set')->willReturn('transaction_id IN (1, 2)');

		$sql = $this->operator->build_marked_where_sql([1, 2]);

		$this->assertSame(' WHERE transaction_id IN (1, 2)', $sql);
	}

	public function build_transaction_url_data()
	{
		return [
			'no txn id yields plain text' => [
				5, '', '', false,
				'',
			],
			'txn id, no custom url, coloured off => link' => [
				5, 'ABC', '', true,
				'<a href="ABC">ABC</a>',
			],
			'custom url builds view link with red colour' => [
				5, 'ABC', 'http://x', false,
				'<a href="http://x&amp;action=view&amp;id=5" style="color: #ff0000;">ABC</a>',
			],
		];
	}

	/** @dataProvider build_transaction_url_data */
	public function test_build_transaction_url($id, $txn_id, $custom_url, $colour, $expected)
	{
		$m = new \ReflectionMethod($this->operator, 'build_transaction_url');
		$m->setAccessible(true);

		$this->assertSame($expected, $m->invokeArgs($this->operator, [$id, $txn_id, $custom_url, $colour]));
	}
}
