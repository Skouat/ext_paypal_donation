<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\migrations;

/**
 * @group database
 */
class schema_test extends \phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\db\tools\tools */
	protected $db_tools;
	protected $table_prefix;

	static protected function setup_extensions()
	{
		return ['skouat/ppde'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/empty.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table_prefix = $table_prefix;

		$this->db = $this->new_dbal();
		$this->db_tools = new \phpbb\db\tools\tools($this->db);
	}

	public function test_txn_log_table_exists()
	{
		$this->assertTrue(
			$this->db_tools->sql_table_exists($this->table_prefix . 'ppde_txn_log'),
			'The ppde_txn_log table should exist after migrations.'
		);
	}

	/**
	 * Idempotency guarantee: a PayPal txn_id can only be stored once.
	 * Checked behaviourally (portable across all DBMS) rather than by index name.
	 */
	public function test_txn_id_is_unique()
	{
		$table = $this->table_prefix . 'ppde_txn_log';

		$row = [
			'txn_id'         => 'TXN_UNIQUE_CHECK',
			'payment_status' => 'Completed',
			'txn_errors'     => '',
		];

		// First insertion succeeds.
		$this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $row));

		// Duplicate is rejected by the UNIQUE index.
		$this->db->sql_return_on_error(true);
		$result = $this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $row));
		$this->db->sql_return_on_error(false);

		$this->assertFalse($result, 'A duplicate txn_id must be rejected by the UNIQUE constraint.');
	}

	public function test_exchange_rate_stores_paypal_precision()
	{
		$table = $this->table_prefix . 'ppde_txn_log';
		$rate  = '0.006018555670097';

		$row = [
			'txn_id'         => 'TXN_FX',
			'payment_status' => 'Completed',
			'exchange_rate'  => $rate,
			'txn_errors'     => '',
		];

		$this->db->sql_return_on_error(true);
		$result = $this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $row));
		$this->db->sql_return_on_error(false);

		$this->assertNotFalse($result, 'A 17-char exchange_rate must be accepted after widening the column.');

		$res = $this->db->sql_query('SELECT exchange_rate FROM ' . $table . " WHERE txn_id = 'TXN_FX'");
		$stored = $this->db->sql_fetchfield('exchange_rate');
		$this->db->sql_freeresult($res);

		$this->assertSame($rate, $stored, 'The exchange_rate must be stored untruncated.');
	}
}
