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

class currency_operator_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\operators\currency */
	protected $operator;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	protected function setUp(): void
	{
		parent::setUp();

		$cache = $this->createMock(\phpbb\cache\driver\driver_interface::class);
		$this->db = $this->createMock(\phpbb\db\driver\driver_interface::class);

		// We mock sql_build_query to see what sql_ary is passed to it
		$this->db->method('sql_build_query')->willReturnCallback(function($type, $sql_ary) {
			// Simply return a serialized representation of the query array for assertion
			return json_encode($sql_ary);
		});

		$this->operator = new \skouat\ppde\operators\currency($cache, $this->db, 'phpbb_ppde_currency');
	}

	public function test_build_sql_data_all_currencies()
	{
		$query = json_decode($this->operator->build_sql_data(0, false), true);

		$this->assertSame('*', $query['SELECT']);
		$this->assertSame(['phpbb_ppde_currency' => 'c'], $query['FROM']);
		$this->assertSame('c.currency_order', $query['ORDER_BY']);
		$this->assertArrayNotHasKey('WHERE', $query);
	}

	public function test_build_sql_data_specific_currency()
	{
		$query = json_decode($this->operator->build_sql_data(42, false), true);

		$this->assertSame('c.currency_id = 42', $query['WHERE']);
	}

	public function test_build_sql_data_only_enabled()
	{
		$query = json_decode($this->operator->build_sql_data(0, true), true);

		$this->assertSame('c.currency_enable = 1', $query['WHERE']);
	}

	public function test_build_sql_data_specific_and_enabled()
	{
		$query = json_decode($this->operator->build_sql_data(42, true), true);

		$this->assertSame('c.currency_id = 42 AND c.currency_enable = 1', $query['WHERE']);
	}
}
