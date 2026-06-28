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
	/** @var \phpbb\db\tools\tools */
	protected $db_tools;
	protected $table_prefix;

	static protected function setup_extensions()
	{
		return ['skouat/ppde'];
	}

	public function getDataSet()
	{
		// An empty dataset is enough: we only check the SCHEMA, not the data.
		return $this->createXMLDataSet(__DIR__ . '/fixtures/empty.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table_prefix = $table_prefix;

		$db = $this->new_dbal();
		$this->db_tools = new \phpbb\db\tools\tools($db);
	}

	public function test_txn_log_table_exists()
	{
		$this->assertTrue(
			$this->db_tools->sql_table_exists($this->table_prefix . 'ppde_txn_log'),
			'The ppde_txn_log table should exist after migrations.'
		);
	}

	public function test_txn_id_unique_index_exists()
	{
		// This is the heart of PPDE idempotency: txn_id MUST be unique.
		$this->assertTrue(
			$this->db_tools->sql_unique_index_exists($this->table_prefix . 'ppde_txn_log', 'txn_uniq'),
			'The UNIQUE index "txn_uniq" on txn_id should exist.'
		);
	}
}
