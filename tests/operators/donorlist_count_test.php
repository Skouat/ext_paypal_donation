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
class donorlist_count_test extends \phpbb_database_test_case
{
	/** @var \skouat\ppde\operators\transactions */
	protected $operator;
	protected $table;

	static protected function setup_extensions()
	{
		return ['skouat/ppde'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/donorlist_count.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table = $table_prefix . 'ppde_txn_log';

		$db = $this->new_dbal();
		$this->operator = new \skouat\ppde\operators\transactions($db, $this->table);
	}

	/**
	 * The donors list counts distinct (user_id, mc_currency) pairs.
	 *
	 * This is the portability regression test: on SQLite/PostgreSQL/MSSQL the
	 * previous COUNT(DISTINCT a, b) syntax was rejected, so simply reaching a
	 * numeric result here proves the query executes on every DBMS.
	 */
	public function test_count_donors_groups_by_user_and_currency()
	{
		// Donor A (USD + EUR) + Donor B (USD) = 3 donor rows.
		// Anonymous, sandbox and non-completed transactions are excluded.
		$sql_ary = $this->operator->sql_donorlist_ary();

		$this->assertSame(3, $this->operator->query_sql_count($sql_ary, 'txn.user_id'));
	}

	/**
	 * Non-regression: without a GROUP_BY the else branch still performs a
	 * plain COUNT() over the selected field.
	 */
	public function test_count_without_group_by_counts_all_rows()
	{
		$sql_ary = [
			'SELECT' => '*',
			'FROM'   => [$this->table => 'txn'],
		];

		$this->assertSame(7, $this->operator->query_sql_count($sql_ary, 'txn.transaction_id'));
	}
}
