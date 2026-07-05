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

/**
 * @group database
 */
class currency_move_test extends \phpbb_database_test_case
{
	/** @var \skouat\ppde\operators\currency */
	protected $operator;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $table_prefix;

	static protected function setup_extensions()
	{
		return ['skouat/ppde'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/currency.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table_prefix = $table_prefix;

		$this->db = $this->new_dbal();
		$cache = $this->createMock(\phpbb\cache\driver\driver_interface::class);

		$this->operator = new \skouat\ppde\operators\currency(
			$cache,
			$this->db,
			$table_prefix . 'ppde_currency'
		);
	}

	private function order_of(int $id): int
	{
		$sql = 'SELECT currency_order FROM ' . $this->table_prefix . 'ppde_currency
			WHERE currency_id = ' . $id;
		$result = $this->db->sql_query($sql);
		$order = (int) $this->db->sql_fetchfield('currency_order');
		$this->db->sql_freeresult($result);

		return $order;
	}

	public function test_move_swaps_two_orders()
	{
		// Move currency #1 (order 1) down, swapping with order 2.
		$executed = $this->operator->move(2, 1, 1);

		$this->assertTrue($executed);
		$this->assertSame(2, $this->order_of(1));
		$this->assertSame(1, $this->order_of(2));
		$this->assertSame(3, $this->order_of(3)); // untouched
	}

	public function test_fix_currency_order_renumbers_gaps()
	{
		// Introduce gaps: orders become 1, 5, 9.
		$this->db->sql_query('UPDATE ' . $this->table_prefix . 'ppde_currency SET currency_order = 5 WHERE currency_id = 2');
		$this->db->sql_query('UPDATE ' . $this->table_prefix . 'ppde_currency SET currency_order = 9 WHERE currency_id = 3');

		$this->operator->fix_currency_order();

		$this->assertSame(1, $this->order_of(1));
		$this->assertSame(2, $this->order_of(2));
		$this->assertSame(3, $this->order_of(3));
	}
}
