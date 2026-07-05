<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\entity;

use skouat\ppde\exception\transaction_exception;

/**
 * @group database
 */
class transactions_insert_test extends \phpbb_database_test_case
{
	/** @var \skouat\ppde\entity\transactions */
	protected $entity;
	protected $table_prefix;

	static protected function setup_extensions()
	{
		return ['skouat/ppde'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/transactions_insert.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table_prefix = $table_prefix;

		$db = $this->new_dbal();

		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()->getMock();
		$language->method('lang')->willReturnArgument(0);

		$this->entity = new \skouat\ppde\entity\transactions(
			$db,
			$language,
			$table_prefix . 'ppde_txn_log'
		);
	}

	public function test_insert_new_transaction_succeeds()
	{
		$this->entity->set_txn_id('TXN_BRAND_NEW');
		$this->entity->set_payment_status('Completed');
		$this->entity->set_txn_errors('');

		$log_action = $this->entity->add_edit_data();

		$this->assertSame('ADDED', $log_action);
		$this->assertNotSame(0, $this->entity->transaction_exists());
	}

	/**
	 * Core idempotency guarantee: a duplicate txn_id is rejected by the UNIQUE
	 * index and converted into a transaction_exception.
	 */
	public function test_insert_duplicate_txn_id_throws()
	{
		$this->entity->set_txn_id('TXN_EXISTING');
		$this->entity->set_payment_status('Completed');
		$this->entity->set_txn_errors('');

		$this->expectException(transaction_exception::class);

		$this->entity->insert();
	}
}
