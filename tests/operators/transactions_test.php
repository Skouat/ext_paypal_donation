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
class transactions_test extends \phpbb_database_test_case
{
	/** @var \skouat\ppde\operators\transactions */
	protected $operator;
	protected $table_prefix;

	static protected function setup_extensions()
	{
		return ['skouat/ppde'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/transactions.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table_prefix = $table_prefix;

		$db = $this->new_dbal();
		$this->operator = new \skouat\ppde\operators\transactions(
			$db,
			$table_prefix . 'ppde_txn_log'
		);
	}

	public function test_get_payment_status_found()
	{
		$this->assertSame('Completed', $this->operator->get_payment_status_by_txn_id('TXN_COMPLETED'));
		$this->assertSame('Pending',   $this->operator->get_payment_status_by_txn_id('TXN_PENDING'));
	}

	public function test_get_payment_status_unknown()
	{
		$this->assertSame('', $this->operator->get_payment_status_by_txn_id('DOES_NOT_EXIST'));
	}

	public function is_txn_completed_data()
	{
		return [
			'completed txn' => ['TXN_COMPLETED', true],
			'pending txn'   => ['TXN_PENDING',   false],
			'unknown txn'   => ['DOES_NOT_EXIST', false],
		];
	}

	/** @dataProvider is_txn_completed_data */
	public function test_is_txn_completed($txn_id, $expected)
	{
		$this->assertSame($expected, $this->operator->is_txn_completed($txn_id));
	}

	public function test_get_custom_by_txn_id()
	{
		$this->assertSame('uid_42_1700000000', $this->operator->get_custom_by_txn_id('TXN_COMPLETED'));
		$this->assertSame('', $this->operator->get_custom_by_txn_id('DOES_NOT_EXIST'));
	}
}
